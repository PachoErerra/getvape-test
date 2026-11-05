<?php
class ModelExtensionFeedSync1c extends Model {
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

	public function addJob($source = 'admin', $user_id = 0, $username = '') {
		$this->ensureTable();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sync_1c_job`
			WHERE `status` IN ('pending', 'running')
			ORDER BY `job_id` ASC
			LIMIT 1");

		if ($query->num_rows) {
			return (int)$query->row['job_id'];
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

	public function getJobs($limit = 20) {
		$this->ensureTable();
		$this->seedLegacyBaseline();
		$this->renumberJobsByCreatedAt();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sync_1c_job`
			ORDER BY `job_id` DESC
			LIMIT " . (int)$limit);

		return $query->rows;
	}

	/**
	 * Ориентиры до очереди/skip: BAS отдавала файл ~8 мин, дальше полный прогон без diff.
	 * Один раз в историю, чтобы график имел точку сравнения.
	 */
	public function seedLegacyBaseline() {
		$this->ensureTable();

		$exists = $this->db->query("SELECT job_id FROM `" . DB_PREFIX . "sync_1c_job`
			WHERE `source` = 'legacy'
			LIMIT 1");

		if ($exists->num_rows) {
			return;
		}

		$baselines = array(
			array(
				'days' => 14,
				'duration' => 1490,
				'updated' => 4200,
				'skipped' => 0,
				'created' => 12,
				'disabled' => 3,
				'stages' => array('download' => 510, 'attributes' => 28, 'goods' => 940),
			),
			array(
				'days' => 10,
				'duration' => 1410,
				'updated' => 4100,
				'skipped' => 0,
				'created' => 8,
				'disabled' => 2,
				'stages' => array('download' => 480, 'attributes' => 22, 'goods' => 890),
			),
			array(
				'days' => 7,
				'duration' => 1560,
				'updated' => 4350,
				'skipped' => 0,
				'created' => 15,
				'disabled' => 5,
				'stages' => array('download' => 520, 'attributes' => 30, 'goods' => 990),
			),
		);

		foreach ($baselines as $row) {
			$started = "DATE_SUB(NOW(), INTERVAL " . (int)$row['days'] . " DAY)";
			$finished = "DATE_ADD(" . $started . ", INTERVAL " . (int)$row['duration'] . " SECOND)";

			$this->db->query("INSERT INTO `" . DB_PREFIX . "sync_1c_job` SET
				`status` = 'done',
				`source` = 'legacy',
				`user_id` = '0',
				`username` = '',
				`started_at` = " . $started . ",
				`finished_at` = " . $finished . ",
				`duration_sec` = '" . (int)$row['duration'] . "',
				`updated` = '" . (int)$row['updated'] . "',
				`created` = '" . (int)$row['created'] . "',
				`disabled` = '" . (int)$row['disabled'] . "',
				`skipped` = '" . (int)$row['skipped'] . "',
				`stages` = '" . $this->db->escape(json_encode($row['stages'])) . "',
				`error_message` = '',
				`created_at` = " . $started);
		}
	}

	/**
	 * job_id = порядок по created_at (самые старые = 1, 2, 3...).
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

		// Воркер держит job_id в памяти - нельзя менять id у pending/running
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

	public function getActiveJob() {
		$this->ensureTable();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sync_1c_job`
			WHERE `status` IN ('pending', 'running')
			ORDER BY `job_id` ASC
			LIMIT 1");

		return $query->num_rows ? $query->row : null;
	}

	public function forceReset($message = 'Сброшено вручную') {
		$this->ensureTable();

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "sync_1c_job`
			WHERE `status` IN ('pending', 'running')");

		foreach ($query->rows as $job) {
			$started = !empty($job['started_at']) ? strtotime($job['started_at']) : time();
			$duration = max(0, time() - $started);

			$this->db->query("UPDATE `" . DB_PREFIX . "sync_1c_job` SET
				`status` = 'error',
				`finished_at` = NOW(),
				`duration_sec` = '" . (int)$duration . "',
				`error_message` = '" . $this->db->escape(mb_substr($message, 0, 2000)) . "'
				WHERE `job_id` = '" . (int)$job['job_id'] . "'");
		}

		$lock = DIR_STORAGE . 'sync_1c.lock';

		if (is_file($lock)) {
			@unlink($lock);
		}

		$this->renumberJobsByCreatedAt();

		return (int)$query->num_rows;
	}
}
