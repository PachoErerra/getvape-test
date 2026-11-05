<?php
class ModelExtensionReportCodProcessingTime extends Model {
	const STATUS_PROCESSING = 2;
	const STATUS_READY = 18;
	const PAYMENT_CODE = 'cod';

	public function getSummary($data = array()) {
		$orders = $this->getMatchedOrders($data);

		if (!$orders) {
			return array(
				'total'       => 0,
				'avg_seconds' => 0,
				'min_seconds' => 0,
				'max_seconds' => 0
			);
		}

		$seconds = array();

		foreach ($orders as $order) {
			$seconds[] = (int)$order['diff_seconds'];
		}

		return array(
			'total'       => count($seconds),
			'avg_seconds' => array_sum($seconds) / count($seconds),
			'min_seconds' => min($seconds),
			'max_seconds' => max($seconds)
		);
	}

	public function getManagers($data = array()) {
		$orders = $this->getMatchedOrders($data);
		$managers = array();

		foreach ($orders as $order) {
			$user_id = (int)$order['user_id'];
			$key = $user_id > 0 ? (string)$user_id : '0';

			if (!isset($managers[$key])) {
				$managers[$key] = array(
					'user_id'   => $user_id,
					'manager'   => $order['manager'],
					'orders'    => 0,
					'seconds'   => array()
				);
			}

			$managers[$key]['orders']++;
			$managers[$key]['seconds'][] = (int)$order['diff_seconds'];
		}

		$result = array();

		foreach ($managers as $manager) {
			$avg = array_sum($manager['seconds']) / count($manager['seconds']);
			$result[] = array(
				'user_id'     => $manager['user_id'],
				'manager'     => $manager['manager'],
				'orders'      => $manager['orders'],
				'avg_seconds' => $avg,
				'min_seconds' => min($manager['seconds']),
				'max_seconds' => max($manager['seconds'])
			);
		}

		usort($result, function ($a, $b) {
			if ($a['avg_seconds'] == $b['avg_seconds']) {
				return $b['orders'] - $a['orders'];
			}

			return ($a['avg_seconds'] < $b['avg_seconds']) ? -1 : 1;
		});

		return $result;
	}

	public function getOrders($data = array()) {
		$orders = $this->getMatchedOrders($data);

		$sort = !empty($data['sort']) ? $data['sort'] : 'end_date';
		$order = (!empty($data['order']) && strtoupper($data['order']) === 'ASC') ? 'ASC' : 'DESC';

		$allowed = array(
			'order_id'      => 'order_id',
			'customer'      => 'customer',
			'manager'       => 'manager',
			'total'         => 'total',
			'start_date'    => 'start_date',
			'end_date'      => 'end_date',
			'diff_seconds'  => 'diff_seconds'
		);

		if (!isset($allowed[$sort])) {
			$sort = 'end_date';
		}

		$sort_key = $allowed[$sort];

		usort($orders, function ($a, $b) use ($sort_key, $order) {
			$left = isset($a[$sort_key]) ? $a[$sort_key] : '';
			$right = isset($b[$sort_key]) ? $b[$sort_key] : '';

			if (in_array($sort_key, array('order_id', 'total', 'diff_seconds'), true)) {
				$cmp = (float)$left - (float)$right;
			} else {
				$cmp = strcmp((string)$left, (string)$right);
			}

			if ($cmp == 0) {
				return 0;
			}

			if ($order === 'ASC') {
				return ($cmp < 0) ? -1 : 1;
			}

			return ($cmp < 0) ? 1 : -1;
		});

		$start = isset($data['start']) ? (int)$data['start'] : 0;
		$limit = isset($data['limit']) ? (int)$data['limit'] : 0;

		if ($start < 0) {
			$start = 0;
		}

		if ($limit > 0) {
			return array_slice($orders, $start, $limit);
		}

		return $orders;
	}

	public function getFilterManagers($data = array()) {
		$filter = $data;
		$filter['filter_user_id'] = '';

		unset($filter['start'], $filter['limit']);

		$orders = $this->getMatchedOrders($filter);
		$managers = array();

		foreach ($orders as $order) {
			$user_id = (int)$order['user_id'];

			if ($user_id < 1) {
				continue;
			}

			$key = (string)$user_id;

			if (!isset($managers[$key])) {
				$managers[$key] = array(
					'user_id' => $user_id,
					'name'    => $order['manager']
				);
			}
		}

		$managers = array_values($managers);

		usort($managers, function ($a, $b) {
			return strcmp($a['name'], $b['name']);
		});

		return $managers;
	}

	/**
	 * Менеджер (пріоритет):
	 * 1) хто створив ТТН (novaposhta_cn_user_id / ukrposhta_cn_user_id),
	 * 2) хто в історії поставив «Готовий» (після фіксу - менеджер ТТН через cron),
	 * 3) хто поставив «В обробці»,
	 * 4) остання ручна зміна до «Готовий».
	 */
	private function getMatchedOrders($data = array()) {
		static $cache = array();

		$cache_key = md5(json_encode(array(
			isset($data['filter_date_start']) ? $data['filter_date_start'] : '',
			isset($data['filter_date_end']) ? $data['filter_date_end'] : '',
			isset($data['filter_user_id']) ? $data['filter_user_id'] : '',
			isset($data['status_start']) ? $data['status_start'] : self::STATUS_PROCESSING,
			isset($data['status_end']) ? $data['status_end'] : self::STATUS_READY,
			isset($data['payment_code']) ? $data['payment_code'] : self::PAYMENT_CODE
		)));

		if (isset($cache[$cache_key])) {
			return $cache[$cache_key];
		}

		$this->ensureOrderHistoryUserIdColumn();
		$this->ensureCnUserIdColumns();

		$status_start = !empty($data['status_start']) ? (int)$data['status_start'] : self::STATUS_PROCESSING;
		$status_end = !empty($data['status_end']) ? (int)$data['status_end'] : self::STATUS_READY;
		$payment_code = !empty($data['payment_code']) ? $data['payment_code'] : self::PAYMENT_CODE;
		$date_start = !empty($data['filter_date_start']) ? $data['filter_date_start'] . ' 00:00:00' : '';
		$date_end = !empty($data['filter_date_end']) ? $data['filter_date_end'] . ' 23:59:59' : '';
		$filter_user_id = array_key_exists('filter_user_id', $data) && $data['filter_user_id'] !== '' ? (string)(int)$data['filter_user_id'] : '';

		$sql = "SELECT o.order_id,
				CONCAT(o.firstname, ' ', o.lastname) AS customer,
				o.telephone,
				o.total,
				o.currency_code,
				o.currency_value,
				o.payment_method,
				o.shipping_method,
				o.novaposhta_cn_user_id,
				o.ukrposhta_cn_user_id,
				ready.end_date,
				ready.user_id AS ready_user_id
			FROM `" . DB_PREFIX . "order` o
			INNER JOIN (
				SELECT oh.order_id, oh.date_added AS end_date, oh.user_id
				FROM `" . DB_PREFIX . "order_history` oh
				INNER JOIN (
					SELECT order_id, MIN(date_added) AS end_date
					FROM `" . DB_PREFIX . "order_history`
					WHERE order_status_id = '" . (int)$status_end . "'";

		if ($date_start) {
			$sql .= " AND date_added >= '" . $this->db->escape($date_start) . "'";
		}

		if ($date_end) {
			$sql .= " AND date_added <= '" . $this->db->escape($date_end) . "'";
		}

		$sql .= " GROUP BY order_id
				) first_ready ON (first_ready.order_id = oh.order_id AND first_ready.end_date = oh.date_added)
				WHERE oh.order_status_id = '" . (int)$status_end . "'
			) ready ON (ready.order_id = o.order_id)
			WHERE o.order_status_id > '0'
			AND o.payment_code = '" . $this->db->escape($payment_code) . "'";

		$ready_query = $this->db->query($sql);

		if (!$ready_query->num_rows) {
			$cache[$cache_key] = array();
			return $cache[$cache_key];
		}

		$ready_map = array();
		$order_ids = array();

		foreach ($ready_query->rows as $row) {
			$order_id = (int)$row['order_id'];
			$order_ids[] = $order_id;
			$ready_map[$order_id] = $row;
		}

		// Перше «В обробці» + user_id менеджера, який його поставив
		$start_query = $this->db->query("SELECT oh.order_id, oh.date_added AS start_date, oh.user_id AS start_user_id
			FROM `" . DB_PREFIX . "order_history` oh
			INNER JOIN (
				SELECT order_id, MIN(date_added) AS start_date
				FROM `" . DB_PREFIX . "order_history`
				WHERE order_status_id = '" . (int)$status_start . "'
				AND order_id IN (" . implode(',', $order_ids) . ")
				GROUP BY order_id
			) first_start ON (first_start.order_id = oh.order_id AND first_start.start_date = oh.date_added)
			WHERE oh.order_status_id = '" . (int)$status_start . "'
			AND oh.order_id IN (" . implode(',', $order_ids) . ")");

		$user_ids = array();

		foreach ($ready_map as $row) {
			if ((int)$row['ready_user_id'] > 0) {
				$user_ids[(int)$row['ready_user_id']] = (int)$row['ready_user_id'];
			}

			$cn_user_id = $this->resolveCnUserId($row);

			if ($cn_user_id > 0) {
				$user_ids[$cn_user_id] = $cn_user_id;
			}
		}

		foreach ($start_query->rows as $start_row) {
			if ((int)$start_row['start_user_id'] > 0) {
				$user_ids[(int)$start_row['start_user_id']] = (int)$start_row['start_user_id'];
			}
		}

		$users = array();

		if ($user_ids) {
			$user_query = $this->db->query("SELECT user_id, username, TRIM(CONCAT(COALESCE(firstname, ''), ' ', COALESCE(lastname, ''))) AS name
				FROM `" . DB_PREFIX . "user`
				WHERE user_id IN (" . implode(',', $user_ids) . ")");

			foreach ($user_query->rows as $user_row) {
				$name = trim($user_row['name']);

				if ($name === '') {
					$name = $user_row['username'];
				}

				$users[(int)$user_row['user_id']] = $name ? $name : $user_row['username'];
			}
		}

		$orders = array();
		$missing_manager_ids = array();

		foreach ($start_query->rows as $start_row) {
			$order_id = (int)$start_row['order_id'];

			if (!isset($ready_map[$order_id])) {
				continue;
			}

			$end_date = $ready_map[$order_id]['end_date'];
			$start_date = $start_row['start_date'];

			if (strtotime($end_date) < strtotime($start_date)) {
				continue;
			}

			$ready_user_id = (int)$ready_map[$order_id]['ready_user_id'];
			$cn_user_id = $this->resolveCnUserId($ready_map[$order_id]);
			$start_user_id = (int)$start_row['start_user_id'];

			// ТТН-менеджер важливіший за cron "Готовий" (часто user_id=0 або підставлений пізніше)
			if ($cn_user_id > 0) {
				$user_id = $cn_user_id;
			} elseif ($ready_user_id > 0) {
				$user_id = $ready_user_id;
			} else {
				$user_id = $start_user_id;
			}

			$row = $ready_map[$order_id];
			$row['start_date'] = $start_date;
			$row['diff_seconds'] = $this->getWorkingSeconds(strtotime($start_date), strtotime($end_date));
			$row['user_id'] = $user_id;
			$row['manager'] = ($user_id > 0 && !empty($users[$user_id])) ? $users[$user_id] : '—';
			$orders[$order_id] = $row;

			if ($user_id < 1) {
				$missing_manager_ids[] = $order_id;
			}
		}

		// Якщо «Готовий»/«В обробці» без user_id — остання ручна зміна до «Готовий»
		if ($missing_manager_ids) {
			$end_date_cases = '';

			foreach ($missing_manager_ids as $order_id) {
				$end_date_cases .= " WHEN '" . (int)$order_id . "' THEN '" . $this->db->escape($orders[$order_id]['end_date']) . "'";
			}

			$fallback_query = $this->db->query("SELECT oh.order_id, oh.user_id
				FROM `" . DB_PREFIX . "order_history` oh
				INNER JOIN (
					SELECT h.order_id, MAX(h.date_added) AS last_date
					FROM `" . DB_PREFIX . "order_history` h
					WHERE h.order_id IN (" . implode(',', $missing_manager_ids) . ")
					AND h.user_id > 0
					AND h.date_added <= CASE h.order_id" . $end_date_cases . " END
					GROUP BY h.order_id
				) last_manual ON (last_manual.order_id = oh.order_id AND last_manual.last_date = oh.date_added AND oh.user_id > 0)");

			$extra_user_ids = array();

			foreach ($fallback_query->rows as $fallback_row) {
				$uid = (int)$fallback_row['user_id'];

				if ($uid > 0 && empty($users[$uid])) {
					$extra_user_ids[$uid] = $uid;
				}
			}

			if ($extra_user_ids) {
				$user_query = $this->db->query("SELECT user_id, username, TRIM(CONCAT(COALESCE(firstname, ''), ' ', COALESCE(lastname, ''))) AS name
					FROM `" . DB_PREFIX . "user`
					WHERE user_id IN (" . implode(',', $extra_user_ids) . ")");

				foreach ($user_query->rows as $user_row) {
					$name = trim($user_row['name']);

					if ($name === '') {
						$name = $user_row['username'];
					}

					$users[(int)$user_row['user_id']] = $name ? $name : $user_row['username'];
				}
			}

			foreach ($fallback_query->rows as $fallback_row) {
				$order_id = (int)$fallback_row['order_id'];
				$user_id = (int)$fallback_row['user_id'];

				if (!isset($orders[$order_id]) || $user_id < 1) {
					continue;
				}

				$orders[$order_id]['user_id'] = $user_id;
				$orders[$order_id]['manager'] = !empty($users[$user_id]) ? $users[$user_id] : '—';
			}
		}

		$orders = array_values($orders);

		if ($filter_user_id !== '') {
			$orders = array_values(array_filter($orders, function ($order) use ($filter_user_id) {
				return (string)(int)$order['user_id'] === $filter_user_id;
			}));
		}

		$cache[$cache_key] = $orders;

		return $cache[$cache_key];
	}

	/**
	 * Робочий час менеджера: 09:00-22:00.
	 * Інтервал 22:00-09:00 не враховується (йде як 0).
	 */
	private function getWorkingSeconds($start_ts, $end_ts) {
		$start_ts = (int)$start_ts;
		$end_ts = (int)$end_ts;

		if ($end_ts <= $start_ts) {
			return 0;
		}

		$work_start_hour = 9;
		$work_end_hour = 22;
		$total = 0;

		$day = strtotime(date('Y-m-d 00:00:00', $start_ts));
		$last_day = strtotime(date('Y-m-d 00:00:00', $end_ts));

		while ($day <= $last_day) {
			$day_work_start = $day + ($work_start_hour * 3600);
			$day_work_end = $day + ($work_end_hour * 3600);

			$segment_start = max($start_ts, $day_work_start);
			$segment_end = min($end_ts, $day_work_end);

			if ($segment_end > $segment_start) {
				$total += $segment_end - $segment_start;
			}

			$day = strtotime('+1 day', $day);
		}

		return $total;
	}

	private function resolveCnUserId($row) {
		$np = isset($row['novaposhta_cn_user_id']) ? (int)$row['novaposhta_cn_user_id'] : 0;
		$up = isset($row['ukrposhta_cn_user_id']) ? (int)$row['ukrposhta_cn_user_id'] : 0;

		if ($np > 0) {
			return $np;
		}

		if ($up > 0) {
			return $up;
		}

		return 0;
	}

	private function ensureCnUserIdColumns() {
		static $checked = false;

		if ($checked) {
			return;
		}

		$checked = true;

		$this->load->model('extension/module/cn_manager_track');
		$this->model_extension_module_cn_manager_track->ensureCnUserIdColumn('novaposhta');
		$this->model_extension_module_cn_manager_track->ensureCnUserIdColumn('ukrposhta');
	}

	private function ensureOrderHistoryUserIdColumn() {
		static $checked = false;

		if ($checked) {
			return;
		}

		$checked = true;

		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order_history` LIKE 'user_id'");

		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "order_history` ADD COLUMN `user_id` INT(11) NOT NULL DEFAULT 0 AFTER `notify`, ADD INDEX `idx_order_history_user_id` (`user_id`)");
		}
	}
}
 
 
