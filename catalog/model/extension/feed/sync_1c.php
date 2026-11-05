<?php
class ModelExtensionFeedSync1c extends Model {
	const CRON_KEY = 'fa42dbcacde4af9f577f688cd6e24f9a3bc421664bc8694e61a20b3e3230e711';
	const LOCK_TTL = 1800;
	const STUCK_SEC = 900; // 15 мин - считать зависшим

	public function ensureTable() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "sync_1c_job` (
			`job_id` int(11) NOT NULL AUTO_INCREMENT,
			`status` varchar(16) NOT NULL DEFAULT 'pending',
			`source` varchar(16) NOT NULL DEFAULT 'cron',
			`user_id` int(11) NOT NULL DEFAULT 0,
			`username` varchar(64) NOT NULL DEFAULT '',
			`started_at` datetime DEFAULT NULL,
			`finished_at` datetime DEFAULT NULL,
			`duration_sec` int(11) DEFAULT NULL,
			`updated` int(11) NOT NULL DEFAULT 0,
			`created` int(11) NOT NULL DEFAULT 0,
			`disabled` int(11) NOT NULL DEFAULT 0,
			`skipped` int(11) NOT NULL DEFAULT 0,
			`stages` text,
			`error_message` text,
			`created_at` datetime NOT NULL,
			PRIMARY KEY (`job_id`),
			KEY `idx_status` (`status`),
			KEY `idx_created` (`created_at`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

		$col = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "sync_1c_job` LIKE 'skipped'");

		if (!$col->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "sync_1c_job` ADD COLUMN `skipped` int(11) NOT NULL DEFAULT 0 AFTER `disabled`");
		}

		$col_user = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "sync_1c_job` LIKE 'username'");

		if (!$col_user->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "sync_1c_job`
				ADD COLUMN `user_id` int(11) NOT NULL DEFAULT 0 AFTER `source`,
				ADD COLUMN `username` varchar(64) NOT NULL DEFAULT '' AFTER `user_id`");
		}
	}

	public function checkKey($key) {
		return is_string($key) && hash_equals(self::CRON_KEY, $key);
	}

	public function getActiveJob() {
		$this->ensureTable();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sync_1c_job`
			WHERE `status` IN ('pending', 'running')
			ORDER BY `job_id` ASC
			LIMIT 1");

		return $query->num_rows ? $query->row : null;
	}

	public function addJob($source = 'cron', $user_id = 0, $username = '') {
		$this->ensureTable();

		$active = $this->getActiveJob();

		if ($active) {
			return (int)$active['job_id'];
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "sync_1c_job` SET
			`status` = 'pending',
			`source` = '" . $this->db->escape($source) . "',
			`user_id` = '" . (int)$user_id . "',
			`username` = '" . $this->db->escape($username) . "',
			`stages` = '{}',
			`created_at` = NOW()");

		return (int)$this->db->getLastId();
	}

	public function getPendingJob() {
		$this->ensureTable();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sync_1c_job`
			WHERE `status` = 'pending'
			ORDER BY `job_id` ASC
			LIMIT 1");

		return $query->num_rows ? $query->row : null;
	}

	public function markRunning($job_id) {
		$this->db->query("UPDATE `" . DB_PREFIX . "sync_1c_job` SET
			`status` = 'running',
			`started_at` = NOW(),
			`error_message` = NULL
			WHERE `job_id` = '" . (int)$job_id . "'
			AND `status` = 'pending'");

		return $this->db->countAffected() > 0;
	}

	public function saveStages($job_id, $stages) {
		$this->db->query("UPDATE `" . DB_PREFIX . "sync_1c_job` SET
			`stages` = '" . $this->db->escape(json_encode($stages, JSON_UNESCAPED_UNICODE)) . "'
			WHERE `job_id` = '" . (int)$job_id . "'");
	}

	public function markDone($job_id, $stats, $stages) {
		$started = $this->getJob($job_id);
		$duration = 0;

		if ($started && !empty($started['started_at'])) {
			$duration = max(0, time() - strtotime($started['started_at']));
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "sync_1c_job` SET
			`status` = 'done',
			`finished_at` = NOW(),
			`duration_sec` = '" . (int)$duration . "',
			`updated` = '" . (int)$stats['updated'] . "',
			`created` = '" . (int)$stats['created'] . "',
			`disabled` = '" . (int)$stats['disabled'] . "',
			`skipped` = '" . (int)(isset($stats['skipped']) ? $stats['skipped'] : 0) . "',
			`stages` = '" . $this->db->escape(json_encode($stages, JSON_UNESCAPED_UNICODE)) . "',
			`error_message` = NULL
			WHERE `job_id` = '" . (int)$job_id . "'");

		$this->renumberJobsByCreatedAt();
	}

	/**
	 * job_id = порядок по created_at (старые = 1, 2, 3...).
	 */
	public function renumberJobsByCreatedAt() {
		$jobs = $this->db->query("SELECT `job_id`, `status` FROM `" . DB_PREFIX . "sync_1c_job`
			ORDER BY `created_at` ASC, `job_id` ASC");

		if (!$jobs->num_rows) {
			return false;
		}

		$map = array();
		$next = 1;
		$needs = false;

		foreach ($jobs->rows as $job) {
			$old = (int)$job['job_id'];
			$map[$old] = $next;

			if ($old !== $next) {
				$needs = true;
			}

			$next++;
		}

		if (!$needs) {
			return false;
		}

		foreach ($jobs->rows as $job) {
			if (in_array($job['status'], array('pending', 'running'), true)) {
				$old = (int)$job['job_id'];

				if ($map[$old] !== $old) {
					return false;
				}
			}
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "sync_1c_job` SET `job_id` = `job_id` + 100000");

		foreach ($map as $old => $new) {
			$this->db->query("UPDATE `" . DB_PREFIX . "sync_1c_job` SET
				`job_id` = '" . (int)$new . "'
				WHERE `job_id` = '" . ((int)$old + 100000) . "'");
		}

		$this->db->query("ALTER TABLE `" . DB_PREFIX . "sync_1c_job` AUTO_INCREMENT = " . (int)$next);

		return true;
	}

	public function markError($job_id, $message, $stages = array(), $stats = array()) {
		$started = $this->getJob($job_id);
		$duration = 0;

		if ($started && !empty($started['started_at'])) {
			$duration = max(0, time() - strtotime($started['started_at']));
		}

		$this->db->query("UPDATE `" . DB_PREFIX . "sync_1c_job` SET
			`status` = 'error',
			`finished_at` = NOW(),
			`duration_sec` = '" . (int)$duration . "',
			`updated` = '" . (int)(isset($stats['updated']) ? $stats['updated'] : 0) . "',
			`created` = '" . (int)(isset($stats['created']) ? $stats['created'] : 0) . "',
			`disabled` = '" . (int)(isset($stats['disabled']) ? $stats['disabled'] : 0) . "',
			`skipped` = '" . (int)(isset($stats['skipped']) ? $stats['skipped'] : 0) . "',
			`stages` = '" . $this->db->escape(json_encode($stages, JSON_UNESCAPED_UNICODE)) . "',
			`error_message` = '" . $this->db->escape(mb_substr($message, 0, 2000)) . "'
			WHERE `job_id` = '" . (int)$job_id . "'");
	}

	public function getJob($job_id) {
		$this->ensureTable();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sync_1c_job`
			WHERE `job_id` = '" . (int)$job_id . "' LIMIT 1");

		return $query->num_rows ? $query->row : null;
	}

	public function getJobs($limit = 20) {
		$this->ensureTable();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sync_1c_job`
			ORDER BY `job_id` DESC
			LIMIT " . (int)$limit);

		return $query->rows;
	}

	public function getLockPath() {
		return DIR_STORAGE . 'sync_1c.lock';
	}

	public function acquireLock() {
		$lock = $this->getLockPath();

		if (is_file($lock)) {
			$age = time() - filemtime($lock);
			$pid = (int)trim((string)@file_get_contents($lock));
			$alive = ($pid > 0 && function_exists('posix_kill')) ? @posix_kill($pid, 0) : ($age < self::LOCK_TTL);

			if ($alive && $age < self::LOCK_TTL) {
				return false;
			}

			@unlink($lock);
		}

		file_put_contents($lock, (string)getmypid());

		return true;
	}

	public function releaseLock() {
		$lock = $this->getLockPath();

		if (is_file($lock)) {
			@unlink($lock);
		}
	}

	public function touchLock() {
		$lock = $this->getLockPath();

		if (is_file($lock)) {
			@touch($lock);
		}
	}

	/**
	 * Сбрасывает зависшие running/pending и lock.
	 */
	public function forceReset($message = 'Сброшено вручную') {
		$this->ensureTable();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sync_1c_job`
			WHERE `status` IN ('pending', 'running')");

		foreach ($query->rows as $job) {
			$this->markError((int)$job['job_id'], $message, json_decode($job['stages'], true) ?: array(), array(
				'updated' => (int)$job['updated'],
				'created' => (int)$job['created'],
				'disabled' => (int)$job['disabled'],
			));
		}

		$this->releaseLock();

		return (int)$query->num_rows;
	}

	public function recoverStuckJobs() {
		$this->ensureTable();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sync_1c_job`
			WHERE `status` = 'running'
			AND `started_at` IS NOT NULL
			AND `started_at` < (NOW() - INTERVAL " . (int)self::STUCK_SEC . " SECOND)");

		foreach ($query->rows as $job) {
			$this->markError(
				(int)$job['job_id'],
				'Зависло: нет завершения более ' . (int)(self::STUCK_SEC / 60) . ' мин',
				json_decode($job['stages'], true) ?: array(),
				array(
					'updated' => (int)$job['updated'],
					'created' => (int)$job['created'],
					'disabled' => (int)$job['disabled'],
				)
			);
		}

		if ($query->num_rows) {
			$this->releaseLock();
		}

		return (int)$query->num_rows;
	}
}
