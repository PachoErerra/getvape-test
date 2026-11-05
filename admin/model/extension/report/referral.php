<?php
class ModelExtensionReportReferral extends Model {
	private $complete_status_id = 5;
	private $cancelled_status_ids;

	private function getDateFilterSql($data, $column = 'c.referal_date') {
		$sql = '';

		if (!empty($data['filter_date_start'])) {
			$sql .= " AND " . $column . " >= '" . $this->db->escape($data['filter_date_start']) . " 00:00:00'";
		}

		if (!empty($data['filter_date_end'])) {
			$sql .= " AND " . $column . " <= '" . $this->db->escape($data['filter_date_end']) . " 23:59:59'";
		}

		return $sql;
	}

	private function getReferrerFilterSql($data, $prefix = 'r') {
		$sql = '';

		if (!empty($data['filter_referrer'])) {
			$filter = $this->db->escape($data['filter_referrer']);
			$sql .= " AND (" . $prefix . ".telephone LIKE '%" . $filter . "%' OR CONCAT(" . $prefix . ".firstname, ' ', " . $prefix . ".lastname) LIKE '%" . $filter . "%')";
		}

		return $sql;
	}

	private function getCancelledStatusIds() {
		if ($this->cancelled_status_ids !== null) {
			return $this->cancelled_status_ids;
		}

		$patterns = array('скас%', 'ануль%', 'cancel%', 'відмін%');
		$conditions = array();

		foreach ($patterns as $pattern) {
			$conditions[] = "LCASE(name) LIKE '" . $this->db->escape(utf8_strtolower($pattern)) . "'";
		}

		$query = $this->db->query("SELECT order_status_id FROM `" . DB_PREFIX . "order_status` WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' AND (" . implode(' OR ', $conditions) . ")");

		$this->cancelled_status_ids = array();

		foreach ($query->rows as $row) {
			$this->cancelled_status_ids[] = (int)$row['order_status_id'];
		}

		return $this->cancelled_status_ids;
	}

	public function isCancelledOrderStatus($order_status_id) {
		return in_array((int)$order_status_id, $this->getCancelledStatusIds());
	}

	private function getNotCancelledStatusSql($column = 'o.order_status_id') {
		$ids = $this->getCancelledStatusIds();

		if (!$ids) {
			return '1=1';
		}

		return $column . ' NOT IN (' . implode(',', $ids) . ')';
	}

	public function resolveBonusState($row) {
		if ((int)$row['referal_used'] || (int)$row['transaction_count'] > 0) {
			return 'paid';
		}

		if (empty($row['order_id'])) {
			return 'none';
		}

		if ($this->isCancelledOrderStatus((int)$row['order_status_id'])) {
			return 'cancelled';
		}

		return 'pending';
	}

	public function getSummary($data = array()) {
		$sql = "SELECT
			COUNT(*) AS bindings,
			SUM(CASE WHEN c.referal_used = 1 THEN 1 ELSE 0 END) AS bonuses_paid
			FROM `" . DB_PREFIX . "customer` c
			WHERE c.referal > 0 AND c.referal != c.customer_id";
		$sql .= $this->getDateFilterSql($data);

		$summary = $this->db->query($sql)->row;

		$sql = "SELECT
			COUNT(DISTINCT c.customer_id) AS orders,
			COUNT(DISTINCT CASE
				WHEN " . $this->getNotCancelledStatusSql('o.order_status_id') . "
				AND c.referal_used = 0
				AND NOT EXISTS (SELECT 1 FROM `" . DB_PREFIX . "customer_transaction` ct WHERE ct.order_id = o.order_id)
				THEN c.customer_id
			END) AS orders_pending
			FROM `" . DB_PREFIX . "customer` c
			INNER JOIN `" . DB_PREFIX . "order` o ON (o.customer_id = c.customer_id AND o.affiliate_id = c.referal)
			WHERE c.referal > 0 AND c.referal != c.customer_id";
		$sql .= $this->getDateFilterSql($data);

		$orders = $this->db->query($sql)->row;

		$sql = "SELECT COALESCE(SUM(ca.clicks), 0) AS clicks FROM `" . DB_PREFIX . "customer_affiliate` ca";
		$clicks = (int)$this->db->query($sql)->row['clicks'];

		$bindings = (int)$summary['bindings'];
		$bonuses_paid = (int)$summary['bonuses_paid'];

		return array(
			'clicks'         => $clicks,
			'bindings'       => $bindings,
			'orders'         => (int)$orders['orders'],
			'orders_pending' => (int)$orders['orders_pending'],
			'bonuses_paid'   => $bonuses_paid,
			'conversion'     => $bindings > 0 ? round(($bonuses_paid / $bindings) * 100, 1) : 0
		);
	}

	public function getReferrals($data = array()) {
		$language_id = (int)$this->config->get('config_language_id');

		$sql = "SELECT c.customer_id, c.telephone AS invited_telephone, c.firstname AS invited_firstname, c.lastname AS invited_lastname,
			c.referal_date, c.referal_used, c.referal AS referrer_id,
			r.telephone AS referrer_telephone, r.firstname AS referrer_firstname, r.lastname AS referrer_lastname,
			ca.clicks AS referrer_clicks,
			ro.order_id, ro.date_added AS order_date, ro.order_status_id,
			os.name AS order_status,
			COALESCE(ct.transaction_count, 0) AS transaction_count
			FROM `" . DB_PREFIX . "customer` c
			LEFT JOIN `" . DB_PREFIX . "customer` r ON (c.referal = r.customer_id)
			LEFT JOIN `" . DB_PREFIX . "customer_affiliate` ca ON (c.referal = ca.customer_id)
			LEFT JOIN (
				SELECT o.customer_id, o.affiliate_id, MIN(o.order_id) AS order_id
				FROM `" . DB_PREFIX . "order` o
				WHERE o.affiliate_id > 0
				GROUP BY o.customer_id, o.affiliate_id
			) fro ON (fro.customer_id = c.customer_id AND fro.affiliate_id = c.referal)
			LEFT JOIN `" . DB_PREFIX . "order` ro ON (ro.order_id = fro.order_id)
			LEFT JOIN `" . DB_PREFIX . "order_status` os ON (ro.order_status_id = os.order_status_id AND os.language_id = '" . $language_id . "')
			LEFT JOIN (
				SELECT order_id, COUNT(*) AS transaction_count
				FROM `" . DB_PREFIX . "customer_transaction`
				GROUP BY order_id
			) ct ON (ct.order_id = ro.order_id)
			WHERE c.referal > 0 AND c.referal != c.customer_id";

		$sql .= $this->getDateFilterSql($data);
		$sql .= $this->getReferrerFilterSql($data);

		$sql .= " ORDER BY c.referal_date DESC";

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

	public function getTotalReferrals($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "customer` c
			LEFT JOIN `" . DB_PREFIX . "customer` r ON (c.referal = r.customer_id)
			WHERE c.referal > 0 AND c.referal != c.customer_id";

		$sql .= $this->getDateFilterSql($data);
		$sql .= $this->getReferrerFilterSql($data);

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTopReferrers($data = array()) {
		$sql = "SELECT ic.referal AS customer_id,
			MAX(r.telephone) AS telephone,
			MAX(r.firstname) AS firstname,
			MAX(r.lastname) AS lastname,
			MAX(ca.clicks) AS clicks,
			COUNT(ic.customer_id) AS invited_count,
			SUM(CASE WHEN ic.referal_used = 1 THEN 1 ELSE 0 END) AS converted_count,
			COUNT(DISTINCT CASE WHEN o.order_id IS NOT NULL THEN ic.customer_id END) AS orders_count,
			COUNT(DISTINCT CASE
				WHEN o.order_id IS NOT NULL
				AND " . $this->getNotCancelledStatusSql('o.order_status_id') . "
				AND ic.referal_used = 0
				THEN ic.customer_id
			END) AS pending_count,
			COALESCE(MAX(rb.total_bonus), 0) AS total_bonus
			FROM `" . DB_PREFIX . "customer` ic
			LEFT JOIN `" . DB_PREFIX . "customer` r ON (ic.referal = r.customer_id)
			LEFT JOIN `" . DB_PREFIX . "customer_affiliate` ca ON (ic.referal = ca.customer_id)
			LEFT JOIN `" . DB_PREFIX . "order` o ON (o.customer_id = ic.customer_id AND o.affiliate_id = ic.referal)
			LEFT JOIN (
				SELECT o.affiliate_id, SUM(ct.amount) AS total_bonus
				FROM `" . DB_PREFIX . "order` o
				INNER JOIN `" . DB_PREFIX . "customer_transaction` ct ON (ct.order_id = o.order_id)
				WHERE o.affiliate_id > 0
				GROUP BY o.affiliate_id
			) rb ON (rb.affiliate_id = ic.referal)
			WHERE ic.referal > 0 AND ic.referal != ic.customer_id";

		$sql .= $this->getDateFilterSql($data, 'ic.referal_date');

		$sql .= " GROUP BY ic.referal ORDER BY invited_count DESC LIMIT 10";

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function ensureClicksColumn() {
		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "customer_affiliate` LIKE 'clicks'");

		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "customer_affiliate` ADD `clicks` int(11) NOT NULL DEFAULT 0 AFTER `tracking`");
		}
	}

	public function ensureIndexes() {
		$indexes = array(
			array('table' => 'customer', 'name' => 'idx_referal', 'sql' => "ALTER TABLE `" . DB_PREFIX . "customer` ADD INDEX `idx_referal` (`referal`)"),
			array('table' => 'customer', 'name' => 'idx_referal_date', 'sql' => "ALTER TABLE `" . DB_PREFIX . "customer` ADD INDEX `idx_referal_date` (`referal_date`)"),
			array('table' => 'order', 'name' => 'idx_affiliate_id', 'sql' => "ALTER TABLE `" . DB_PREFIX . "order` ADD INDEX `idx_affiliate_id` (`affiliate_id`)"),
			array('table' => 'order', 'name' => 'idx_customer_affiliate', 'sql' => "ALTER TABLE `" . DB_PREFIX . "order` ADD INDEX `idx_customer_affiliate` (`customer_id`, `affiliate_id`)"),
			array('table' => 'customer_transaction', 'name' => 'idx_order_id', 'sql' => "ALTER TABLE `" . DB_PREFIX . "customer_transaction` ADD INDEX `idx_order_id` (`order_id`)")
		);

		foreach ($indexes as $index) {
			$query = $this->db->query("SHOW INDEX FROM `" . DB_PREFIX . $index['table'] . "` WHERE Key_name = '" . $this->db->escape($index['name']) . "'");

			if (!$query->num_rows) {
				$this->db->query($index['sql']);
			}
		}
	}

	public function ensureSchema() {
		$this->ensureClicksColumn();
		$this->ensureIndexes();
	}
}
