<?php
class ModelExtensionReportCancelledOrders extends Model {
	private $cancelled_status_ids;

	public function getSummary($data = array()) {
		$orders = $this->getMatchedOrders($data);

		$total = count($orders);
		$sum = 0;
		$with_manager = 0;
		$managers = array();

		foreach ($orders as $order) {
			$sum += (float)$order['total'];

			if ((int)$order['user_id'] > 0) {
				$with_manager++;
				$managers[(int)$order['user_id']] = true;
			}
		}

		return array(
			'total'            => $total,
			'sum'              => $sum,
			'with_manager'     => $with_manager,
			'without_manager'  => $total - $with_manager,
			'managers_count'   => count($managers)
		);
	}

	public function getManagers($data = array()) {
		$orders = $this->getMatchedOrders($data);
		$managers = array();
		$total = count($orders);

		foreach ($orders as $order) {
			$key = !empty($order['manager_key']) ? $order['manager_key'] : '0';

			if (!isset($managers[$key])) {
				$managers[$key] = array(
					'user_id' => (int)$order['user_id'],
					'manager' => $order['manager'],
					'orders'  => 0,
					'sum'     => 0
				);
			}

			$managers[$key]['orders']++;
			$managers[$key]['sum'] += (float)$order['total'];
		}

		$result = array();

		foreach ($managers as $manager) {
			$result[] = array(
				'user_id'  => $manager['user_id'],
				'manager'  => $manager['manager'],
				'orders'   => $manager['orders'],
				'sum'      => $manager['sum'],
				'percent'  => $total > 0 ? ($manager['orders'] * 100 / $total) : 0
			);
		}

		usort($result, function ($a, $b) {
			if ($a['orders'] == $b['orders']) {
				if ($a['sum'] == $b['sum']) {
					return strcmp($a['manager'], $b['manager']);
				}

				return ($a['sum'] > $b['sum']) ? -1 : 1;
			}

			return ($a['orders'] > $b['orders']) ? -1 : 1;
		});

		return $result;
	}

	public function getOrders($data = array()) {
		$orders = $this->getMatchedOrders($data);

		$sort = !empty($data['sort']) ? $data['sort'] : 'cancel_date';
		$order = (!empty($data['order']) && strtoupper($data['order']) === 'ASC') ? 'ASC' : 'DESC';

		$allowed = array(
			'order_id'     => 'order_id',
			'customer'     => 'customer',
			'manager'      => 'manager',
			'status'       => 'status_name',
			'total'        => 'total',
			'cancel_date'  => 'cancel_date'
		);

		if (!isset($allowed[$sort])) {
			$sort = 'cancel_date';
		}

		$sort_key = $allowed[$sort];

		usort($orders, function ($a, $b) use ($sort_key, $order) {
			$left = isset($a[$sort_key]) ? $a[$sort_key] : '';
			$right = isset($b[$sort_key]) ? $b[$sort_key] : '';

			if (in_array($sort_key, array('order_id', 'total'), true)) {
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

	public function getFilterNpCodes() {
		$codes = array('102', '103');
		$result = array();

		foreach ($codes as $code) {
			$text = $this->getNpTrackingStatusText($code);

			$result[] = array(
				'code' => $code,
				'name' => '(Код ' . $code . ') ' . $text
			);
		}

		return $result;
	}

	public function getCancelledStatusIds() {
		if ($this->cancelled_status_ids !== null) {
			return $this->cancelled_status_ids;
		}

		$patterns = array('скас%', 'ануль%', 'cancel%', 'відмін%', 'отмен%');
		$conditions = array();

		foreach ($patterns as $pattern) {
			$conditions[] = "LCASE(name) LIKE '" . $this->db->escape(utf8_strtolower($pattern)) . "'";
		}

		$query = $this->db->query("SELECT DISTINCT order_status_id FROM `" . DB_PREFIX . "order_status` WHERE (" . implode(' OR ', $conditions) . ")");

		$this->cancelled_status_ids = array();

		foreach ($query->rows as $row) {
			$this->cancelled_status_ids[] = (int)$row['order_status_id'];
		}

		return $this->cancelled_status_ids;
	}

	private function getMatchedOrders($data = array()) {
		static $cache = array();

		$cache_key = md5(json_encode(array(
			isset($data['filter_date_start']) ? $data['filter_date_start'] : '',
			isset($data['filter_date_end']) ? $data['filter_date_end'] : '',
			isset($data['filter_user_id']) ? $data['filter_user_id'] : '',
			isset($data['filter_np_code']) ? $data['filter_np_code'] : ''
		)));

		if (isset($cache[$cache_key])) {
			return $cache[$cache_key];
		}

		$this->ensureOrderHistoryUserIdColumn();

		$status_ids = $this->getCancelledStatusIds();

		if (!$status_ids) {
			$cache[$cache_key] = array();
			return $cache[$cache_key];
		}

		$date_start = !empty($data['filter_date_start']) ? $data['filter_date_start'] . ' 00:00:00' : '';
		$date_end = !empty($data['filter_date_end']) ? $data['filter_date_end'] . ' 23:59:59' : '';
		$filter_user_id = array_key_exists('filter_user_id', $data) && $data['filter_user_id'] !== '' ? (string)(int)$data['filter_user_id'] : '';
		$filter_np_code = array_key_exists('filter_np_code', $data) && $data['filter_np_code'] !== '' ? (string)(int)$data['filter_np_code'] : '';
		$language_id = (int)$this->config->get('config_language_id');

		$sql = "SELECT o.order_id,
				CONCAT(o.firstname, ' ', o.lastname) AS customer,
				o.telephone,
				o.total,
				o.currency_code,
				o.currency_value,
				o.payment_method,
				o.shipping_method,
				cancel.cancel_date,
				cancel.user_id,
				cancel.comment,
				cancel.order_status_id,
				COALESCE(os.name, '') AS status_name
			FROM `" . DB_PREFIX . "order` o
			INNER JOIN (
				SELECT oh.order_id, oh.date_added AS cancel_date, oh.user_id, oh.comment, oh.order_status_id
				FROM `" . DB_PREFIX . "order_history` oh
				INNER JOIN (
					SELECT order_id, MIN(date_added) AS cancel_date
					FROM `" . DB_PREFIX . "order_history`
					WHERE order_status_id IN (" . implode(',', array_map('intval', $status_ids)) . ")";

		if ($date_start) {
			$sql .= " AND date_added >= '" . $this->db->escape($date_start) . "'";
		}

		if ($date_end) {
			$sql .= " AND date_added <= '" . $this->db->escape($date_end) . "'";
		}

		$sql .= " GROUP BY order_id
				) first_cancel ON (first_cancel.order_id = oh.order_id AND first_cancel.cancel_date = oh.date_added)
				WHERE oh.order_status_id IN (" . implode(',', array_map('intval', $status_ids)) . ")
			) cancel ON (cancel.order_id = o.order_id)
			LEFT JOIN `" . DB_PREFIX . "order_status` os ON (os.order_status_id = cancel.order_status_id AND os.language_id = '" . $language_id . "')
			WHERE o.order_status_id > '0'";

		$query = $this->db->query($sql);

		if (!$query->num_rows) {
			$cache[$cache_key] = array();
			return $cache[$cache_key];
		}

		$user_ids = array();

		foreach ($query->rows as $row) {
			$comment = trim(strip_tags(html_entity_decode($row['comment'], ENT_QUOTES, 'UTF-8')));

			if ($this->isCarrierAutoStatusComment($comment)) {
				continue;
			}

			if ((int)$row['user_id'] > 0) {
				$user_ids[(int)$row['user_id']] = (int)$row['user_id'];
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

		foreach ($query->rows as $row) {
			$user_id = (int)$row['user_id'];
			$comment = trim(strip_tags(html_entity_decode($row['comment'], ENT_QUOTES, 'UTF-8')));
			$status_name = trim((string)$row['status_name']);
			$manager_label = '';
			$manager_key = '';
			$np_code = $this->extractCarrierStatusCode($comment);

			// Автостатуси НП/УП: замість менеджера показуємо код трекінгу + текст статусу
			if ($this->isCarrierAutoStatusComment($comment)) {
				$user_id = 0;
				$manager_label = $this->formatCarrierAutoManager($comment, $status_name);
				$manager_key = 'auto:' . $manager_label;
			}

			if ($filter_user_id !== '' && (string)$user_id !== $filter_user_id) {
				continue;
			}

			if ($filter_np_code !== '' && $np_code !== $filter_np_code) {
				continue;
			}

			if ($manager_label === '') {
				if ($user_id > 0 && !empty($users[$user_id])) {
					$manager_label = $users[$user_id];
					$manager_key = (string)$user_id;
				} else {
					$manager_label = '—';
					$manager_key = '0';
				}
			}

			$row['user_id'] = $user_id;
			$row['np_code'] = $np_code;
			$row['manager'] = $manager_label;
			$row['manager_key'] = $manager_key;
			$row['comment'] = $comment;
			$orders[] = $row;
		}

		$cache[$cache_key] = $orders;

		return $cache[$cache_key];
	}

	private function isCarrierAutoStatusComment($comment) {
		$comment = (string)$comment;

		if ($comment === '') {
			return false;
		}

		return (bool)preg_match('/^\[(NP|UP)\]:\s*автостатус/ui', $comment);
	}

	private function extractCarrierStatusCode($comment) {
		if (preg_match('/^\[(NP|UP)\]:\s*автостатус(?:\s+за\s+трекінгом)?(?:\s*\(код\s*(\d+)\))?/ui', (string)$comment, $match) && !empty($match[2])) {
			return (string)(int)$match[2];
		}

		return '';
	}

	private function formatCarrierAutoManager($comment, $status_name = '') {
		$carrier = 'НП';
		$code = $this->extractCarrierStatusCode($comment);

		if (preg_match('/^\[(NP|UP)\]:/ui', (string)$comment, $match)) {
			$carrier = (strtoupper($match[1]) === 'UP') ? 'УП' : 'НП';
		}

		// Для НП беремо офіційний текст трекінгу за кодом, а не статус магазину
		$text = '';

		if ($carrier === 'НП' && $code !== '') {
			$text = $this->getNpTrackingStatusText($code);
		}

		if ($text === '' && $status_name !== '') {
			$text = $status_name;
		}

		if ($code !== '' && $text !== '') {
			return '(Код ' . $code . ') ' . $text;
		}

		if ($code !== '') {
			return $carrier . ' (Код ' . $code . ')';
		}

		if ($text !== '') {
			return $carrier . ' · ' . $text;
		}

		return $carrier;
	}

	private function getNpTrackingStatusText($code) {
		$map = array(
			'102' => 'Відмова від отримання (Відправником створено замовлення на повернення)',
			'103' => 'Відмова одержувача (Отримувач відмовився від відправлення)',
			'104' => 'Змінено адресу',
			'105' => 'Припинено зберігання',
			'108' => 'Відмова одержувача',
			'111' => 'Невдала спроба вручення через відсутність Одержувача на адресі або зв\'язку з ним',
			'112' => 'Дата доставки перенесена Одержувачем'
		);

		$code = (string)$code;

		return isset($map[$code]) ? $map[$code] : '';
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
