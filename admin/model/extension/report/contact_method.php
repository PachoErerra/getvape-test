<?php
class ModelExtensionReportContactMethod extends Model {
	const CODE_TELEGRAM = 'telegram';
	const CODE_VIBER = 'viber';
	const CODE_TELEPHONE = 'telephone';
	const CODE_UNKNOWN = 'unknown';

	public function getContactTypes() {
		return array(
			self::CODE_TELEGRAM  => 'Telegram',
			self::CODE_VIBER     => 'Viber',
			self::CODE_TELEPHONE => 'Зателефонуйте'
		);
	}

	public function getSummary($data = array()) {
		$orders = $this->getMatchedOrders($data);
		$types = $this->getContactTypes();
		$counts = array(
			self::CODE_TELEGRAM  => 0,
			self::CODE_VIBER     => 0,
			self::CODE_TELEPHONE => 0
		);

		$sum = 0;

		foreach ($orders as $order) {
			$code = $order['contact_code'];

			if (isset($counts[$code])) {
				$counts[$code]++;
			}

			$sum += (float)$order['total'];
		}

		$total = count($orders);

		return array(
			'total'     => $total,
			'sum'       => $sum,
			'telegram'  => $counts[self::CODE_TELEGRAM],
			'viber'     => $counts[self::CODE_VIBER],
			'telephone' => $counts[self::CODE_TELEPHONE]
		);
	}

	public function getContactRanking($data = array()) {
		$orders = $this->getMatchedOrders($data);
		$types = $this->getContactTypes();
		$stats = array();
		$total = count($orders);

		foreach ($orders as $order) {
			$code = $order['contact_code'];

			if (!isset($types[$code])) {
				continue;
			}

			if (!isset($stats[$code])) {
				$stats[$code] = array(
					'contact_code' => $code,
					'contact'      => $types[$code],
					'orders'       => 0,
					'sum'          => 0
				);
			}

			$stats[$code]['orders']++;
			$stats[$code]['sum'] += (float)$order['total'];
		}

		$result = array();

		foreach ($stats as $row) {
			$result[] = array(
				'contact_code' => $row['contact_code'],
				'contact'      => $row['contact'],
				'orders'       => $row['orders'],
				'sum'          => $row['sum'],
				'percent'      => $total > 0 ? ($row['orders'] * 100 / $total) : 0
			);
		}

		usort($result, function ($a, $b) {
			if ($a['orders'] == $b['orders']) {
				return strcmp($a['contact'], $b['contact']);
			}

			return ($a['orders'] > $b['orders']) ? -1 : 1;
		});

		return $result;
	}

	public function getOrders($data = array()) {
		$orders = $this->getMatchedOrders($data);

		$sort = !empty($data['sort']) ? $data['sort'] : 'date_added';
		$order = (!empty($data['order']) && strtoupper($data['order']) === 'ASC') ? 'ASC' : 'DESC';

		$allowed = array(
			'order_id'   => 'order_id',
			'customer'   => 'customer',
			'contact'    => 'contact_label',
			'total'      => 'total',
			'date_added' => 'date_added'
		);

		if (!isset($allowed[$sort])) {
			$sort = 'date_added';
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

	private function getMatchedOrders($data = array()) {
		static $cache = array();

		$cache_key = md5(json_encode(array(
			isset($data['filter_date_start']) ? $data['filter_date_start'] : '',
			isset($data['filter_date_end']) ? $data['filter_date_end'] : '',
			isset($data['filter_contact']) ? $data['filter_contact'] : ''
		)));

		if (isset($cache[$cache_key])) {
			return $cache[$cache_key];
		}

		if (!$this->hasOrderSimpleFieldsTable() || !$this->hasCallmeColumn()) {
			$cache[$cache_key] = array();
			return $cache[$cache_key];
		}

		$date_start = !empty($data['filter_date_start']) ? $data['filter_date_start'] . ' 00:00:00' : '';
		$date_end = !empty($data['filter_date_end']) ? $data['filter_date_end'] . ' 23:59:59' : '';
		$filter_contact = array_key_exists('filter_contact', $data) && $data['filter_contact'] !== '' ? (string)$data['filter_contact'] : '';
		$sql = "SELECT o.order_id,
				CONCAT(o.firstname, ' ', o.lastname) AS customer,
				o.telephone,
				o.total,
				o.currency_code,
				o.currency_value,
				o.payment_method,
				o.shipping_method,
				o.date_added,
				COALESCE(osf.callme, '') AS callme_raw
			FROM `" . DB_PREFIX . "order` o
			LEFT JOIN `" . DB_PREFIX . "order_simple_fields` osf ON (osf.order_id = o.order_id)
			WHERE o.order_status_id > '0'";

		if ($date_start) {
			$sql .= " AND o.date_added >= '" . $this->db->escape($date_start) . "'";
		}

		if ($date_end) {
			$sql .= " AND o.date_added <= '" . $this->db->escape($date_end) . "'";
		}

		$query = $this->db->query($sql);
		$types = $this->getContactTypes();
		$orders = array();

		foreach ($query->rows as $row) {
			$code = $this->normalizeContactCode($row['callme_raw']);

			// Старі/порожні значення не показуємо
			if ($code === self::CODE_UNKNOWN) {
				continue;
			}

			if ($filter_contact !== '' && $code !== $filter_contact) {
				continue;
			}

			$row['contact_code'] = $code;
			$row['contact_label'] = isset($types[$code]) ? $types[$code] : $code;
			$orders[] = $row;
		}

		$cache[$cache_key] = $orders;

		return $cache[$cache_key];
	}

	private function normalizeContactCode($raw) {
		$value = strtolower(trim((string)$raw));

		if ($value === self::CODE_TELEGRAM || $value === 'tg') {
			return self::CODE_TELEGRAM;
		}

		if ($value === self::CODE_VIBER) {
			return self::CODE_VIBER;
		}

		if ($value === self::CODE_TELEPHONE || $value === 'phone' || $value === 'call' || $value === '1') {
			return self::CODE_TELEPHONE;
		}

		return self::CODE_UNKNOWN;
	}

	private function hasOrderSimpleFieldsTable() {
		static $exists = null;

		if ($exists !== null) {
			return $exists;
		}

		$query = $this->db->query("SHOW TABLES LIKE '" . DB_PREFIX . "order_simple_fields'");
		$exists = (bool)$query->num_rows;

		return $exists;
	}

	private function hasCallmeColumn() {
		static $exists = null;

		if ($exists !== null) {
			return $exists;
		}

		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order_simple_fields` LIKE 'callme'");
		$exists = (bool)$query->num_rows;

		return $exists;
	}
}
