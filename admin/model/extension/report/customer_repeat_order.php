<?php
class ModelExtensionReportCustomerRepeatOrder extends Model {

	private function getCompleteStatusIds() {
		$ids = array();

		$config_value = $this->config->get('config_complete_status');

		if (is_array($config_value)) {
			$ids = array_map('intval', $config_value);
		}

		$name_patterns = array('заверш%', 'complete%', 'виконан%');
		$conditions = array();

		foreach ($name_patterns as $pattern) {
			$conditions[] = "LCASE(name) LIKE '" . $this->db->escape(utf8_strtolower($pattern)) . "'";
		}

		if ($conditions) {
			$query = $this->db->query("SELECT order_status_id FROM " . DB_PREFIX . "order_status WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' AND (" . implode(' OR ', $conditions) . ")");

			foreach ($query->rows as $row) {
				$ids[] = (int)$row['order_status_id'];
			}
		}

		return array_values(array_unique(array_filter($ids)));
	}

	private function getCompleteTotalSql($alias = 'o') {
		$ids = $this->getCompleteStatusIds();

		if (!$ids) {
			return '0';
		}

		return "CASE WHEN " . $alias . ".order_status_id IN (" . implode(',', $ids) . ") THEN t.total ELSE 0 END";
	}

	private function getOrderFilterSql($data, $alias = 'o') {
		$sql = '';

		if (!empty($data['filter_date_start'])) {
			$sql .= " AND DATE(" . $alias . ".date_added) >= DATE('" . $this->db->escape($data['filter_date_start']) . "')";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND DATE(" . $alias . ".date_added) <= DATE('" . $this->db->escape($data['filter_date_end']) . "')";
		}

		if (!empty($data['filter_order_status_id'])) {
			$sql .= " AND " . $alias . ".order_status_id = '" . (int)$data['filter_order_status_id'] . "'";
		} else {
			$sql .= " AND " . $alias . ".order_status_id > '0'";
		}

		return $sql;
	}

	private function getCustomerFilterSql($data, $alias = 'c', $stats_alias = 's') {
		$sql = '';

		if (!empty($data['filter_customer'])) {
			$sql .= " AND CONCAT(" . $alias . ".firstname, ' ', " . $alias . ".lastname) LIKE '" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_telephone'])) {
			$sql .= " AND " . $alias . ".telephone LIKE '%" . $this->db->escape($data['filter_telephone']) . "%'";
		}

		if (!empty($data['filter_orders_bucket'])) {
			$bucket = (int)$data['filter_orders_bucket'];

			if ($bucket >= 5) {
				$sql .= " AND " . $stats_alias . ".orders >= 5";
			} else {
				$sql .= " AND " . $stats_alias . ".orders = '" . $bucket . "'";
			}
		}

		return $sql;
	}

	private function getCustomerStatsSubquery($data) {
		$sql = "SELECT o.customer_id, COUNT(DISTINCT o.order_id) AS orders, SUM(t.products) AS products, SUM(" . $this->getCompleteTotalSql('o') . ") AS total"
			. " FROM `" . DB_PREFIX . "order` o"
			. " INNER JOIN ("
			. " SELECT o2.order_id, SUM(op.quantity) AS products, o2.total"
			. " FROM `" . DB_PREFIX . "order` o2"
			. " LEFT JOIN `" . DB_PREFIX . "order_product` op ON (o2.order_id = op.order_id)"
			. " WHERE o2.customer_id > 0"
			. $this->getOrderFilterSql($data, 'o2')
			. " GROUP BY o2.order_id"
			. " ) t ON (o.order_id = t.order_id)"
			. " WHERE o.customer_id > 0"
			. $this->getOrderFilterSql($data, 'o')
			. " GROUP BY o.customer_id";

		return $sql;
	}

	public function getDistribution($data = array()) {
		$sql = "SELECT"
			. " SUM(CASE WHEN s.orders = 1 THEN 1 ELSE 0 END) AS bucket_1,"
			. " SUM(CASE WHEN s.orders = 2 THEN 1 ELSE 0 END) AS bucket_2,"
			. " SUM(CASE WHEN s.orders = 3 THEN 1 ELSE 0 END) AS bucket_3,"
			. " SUM(CASE WHEN s.orders = 4 THEN 1 ELSE 0 END) AS bucket_4,"
			. " SUM(CASE WHEN s.orders >= 5 THEN 1 ELSE 0 END) AS bucket_5_plus"
			. " FROM (" . $this->getCustomerStatsSubquery($data) . ") AS s"
			. " LEFT JOIN `" . DB_PREFIX . "customer` c ON (s.customer_id = c.customer_id)"
			. " WHERE 1 = 1"
			. $this->getCustomerFilterSql($data);

		$query = $this->db->query($sql);

		return $query->row;
	}

	public function getCustomers($data = array()) {
		$sql = "SELECT c.customer_id, CONCAT(c.firstname, ' ', c.lastname) AS customer, c.email, c.telephone,"
			. " c.status, s.orders, s.products, s.total"
			. " FROM (" . $this->getCustomerStatsSubquery($data) . ") AS s"
			. " LEFT JOIN `" . DB_PREFIX . "customer` c ON (s.customer_id = c.customer_id)"
			. " WHERE 1 = 1"
			. $this->getCustomerFilterSql($data);

		$sql .= " ORDER BY s.orders DESC, s.total DESC";

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalCustomers($data = array()) {
		$sql = "SELECT COUNT(*) AS total"
			. " FROM (" . $this->getCustomerStatsSubquery($data) . ") AS s"
			. " LEFT JOIN `" . DB_PREFIX . "customer` c ON (s.customer_id = c.customer_id)"
			. " WHERE 1 = 1"
			. $this->getCustomerFilterSql($data);

		$query = $this->db->query($sql);

		return (int)$query->row['total'];
	}

	public function getOrderStatusCountsByCustomerIds($customer_ids, $data = array()) {
		if (!$customer_ids) {
			return array();
		}

		$ids = array();

		foreach ($customer_ids as $customer_id) {
			$ids[] = (int)$customer_id;
		}

		$sql = "SELECT o.customer_id, o.order_status_id, os.name AS order_status, COUNT(*) AS total"
			. " FROM `" . DB_PREFIX . "order` o"
			. " LEFT JOIN `" . DB_PREFIX . "order_status` os ON (o.order_status_id = os.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "')"
			. " WHERE o.customer_id IN (" . implode(',', $ids) . ")"
			. $this->getOrderFilterSql($data, 'o')
			. " GROUP BY o.customer_id, o.order_status_id"
			. " ORDER BY total DESC, os.name ASC";

		$query = $this->db->query($sql);

		$statuses = array();

		foreach ($query->rows as $row) {
			$statuses[$row['customer_id']][] = array(
				'name'  => $row['order_status'],
				'count' => (int)$row['total']
			);
		}

		return $statuses;
	}

	public function formatOrderStatusSummary($statuses) {
		if (!$statuses) {
			return '';
		}

		$parts = array();

		foreach ($statuses as $status) {
			$parts[] = $status['name'] . ': ' . $status['count'];
		}

		return implode('; ', $parts);
	}
}
 