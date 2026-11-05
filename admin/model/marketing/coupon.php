<?php
class ModelMarketingCoupon extends Model {
	public function addCoupon($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', conditions = '" . (isset($data['conditions'])?$this->db->escape($data['conditions']):'') . "', code = '" . $this->db->escape($data['code']) . "', discount = '" . (float)$data['discount'] . "', type = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['logged'] . "', shipping = '" . (int)$data['shipping'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '" . (int)$data['uses_customer'] . "', status = '" . (int)$data['status'] . "', date_added = NOW()");

		$coupon_id = $this->db->getLastId();

		if (isset($data['coupon_product'])) {
			foreach ($data['coupon_product'] as $product_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_product SET coupon_id = '" . (int)$coupon_id . "', product_id = '" . (int)$product_id . "'");
			}
		}

		if (isset($data['coupon_category'])) {
			foreach ($data['coupon_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_category SET coupon_id = '" . (int)$coupon_id . "', category_id = '" . (int)$category_id . "'");
			}
		}

		if (isset($data['coupon_category_required'])) {
			foreach ($data['coupon_category_required'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_category_required SET coupon_id = '" . (int)$coupon_id . "', category_id = '" . (int)$category_id . "'");
			}
		}
		return $coupon_id;
	}

	public function editCoupon($coupon_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', conditions = '" . (isset($data['conditions'])?$this->db->escape($data['conditions']):'') . "', code = '" . $this->db->escape($data['code']) . "', discount = '" . (float)$data['discount'] . "', type = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['logged'] . "', shipping = '" . (int)$data['shipping'] . "', date_start = '" . $this->db->escape($data['date_start']) . "', date_end = '" . $this->db->escape($data['date_end']) . "', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '" . (int)$data['uses_customer'] . "', status = '" . (int)$data['status'] . "' WHERE coupon_id = '" . (int)$coupon_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");

		if (isset($data['coupon_product'])) {
			foreach ($data['coupon_product'] as $product_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_product SET coupon_id = '" . (int)$coupon_id . "', product_id = '" . (int)$product_id . "'");
			}
		}

		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category WHERE coupon_id = '" . (int)$coupon_id . "'");

		if (isset($data['coupon_category'])) {
			foreach ($data['coupon_category'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_category SET coupon_id = '" . (int)$coupon_id . "', category_id = '" . (int)$category_id . "'");
			}
		}
		
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category_required WHERE coupon_id = '" . (int)$coupon_id . "'");

		if (isset($data['coupon_category_required'])) {
			foreach ($data['coupon_category_required'] as $category_id) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "coupon_category_required SET coupon_id = '" . (int)$coupon_id . "', category_id = '" . (int)$category_id . "'");
			}
		}
	}

	public function deleteCoupon($coupon_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int)$coupon_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category WHERE coupon_id = '" . (int)$coupon_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_history WHERE coupon_id = '" . (int)$coupon_id . "'");
	}

	public function getCoupon($coupon_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int)$coupon_id . "'");

		return $query->row;
	}

	public function getCouponByCode($code) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "coupon WHERE code = '" . $this->db->escape($code) . "'");

		return $query->row;
	}

	public function getCoupons($data = array()) {
		$sql = "SELECT c.coupon_id, c.name, c.code, c.type, c.discount, c.date_start, c.date_end, c.date_added, c.status, c.uses_total, (SELECT COUNT(*) FROM `" . DB_PREFIX . "coupon_history` ch WHERE ch.coupon_id = c.coupon_id) AS `uses` FROM `" . DB_PREFIX . "coupon` c";

		$implode = $this->getCouponFilters($data);

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$sort_data = array(
			'name',
			'code',
			'discount',
			'date_start',
			'date_end',
			'date_added',
			'status',
			'uses'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			if ($data['sort'] == 'uses') {
				$sql .= " ORDER BY `uses`";
			} else {
				$sql .= " ORDER BY c." . $data['sort'];
			}
		} else {
			$sql .= " ORDER BY c.date_added";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

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

	public function getCouponProducts($coupon_id) {
		$coupon_product_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$coupon_id . "'");

		foreach ($query->rows as $result) {
			$coupon_product_data[] = $result['product_id'];
		}

		return $coupon_product_data;
	}

	public function getCouponCategories($coupon_id) {
		$coupon_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_category WHERE coupon_id = '" . (int)$coupon_id . "'");

		foreach ($query->rows as $result) {
			$coupon_category_data[] = $result['category_id'];
		}

		return $coupon_category_data;
	}

	public function getCouponCategoriesrequired($coupon_id) {
		$coupon_category_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon_category_required WHERE coupon_id = '" . (int)$coupon_id . "'");

		foreach ($query->rows as $result) {
			$coupon_category_data[] = $result['category_id'];
		}

		return $coupon_category_data;
	}
	
	public function getTotalCoupons($data = array()) {
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "coupon` c";

		$implode = $this->getCouponFilters($data);

		if ($implode) {
			$sql .= " WHERE " . implode(" AND ", $implode);
		}

		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	private function getCouponFilters($data = array()) {
		$implode = array();

		if (!empty($data['filter_name'])) {
			$implode[] = "c.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_code'])) {
			$implode[] = "c.code LIKE '" . $this->db->escape($data['filter_code']) . "%'";
		}

		if (!empty($data['filter_date_added_start'])) {
			$implode[] = "c.date_added >= '" . $this->db->escape($data['filter_date_added_start']) . " 00:00:00'";
		}

		if (!empty($data['filter_date_added_end'])) {
			$implode[] = "c.date_added <= '" . $this->db->escape($data['filter_date_added_end']) . " 23:59:59'";
		}

		if (isset($data['filter_used']) && $data['filter_used'] !== '') {
			if ($data['filter_used'] == '1' || $data['filter_used'] == 'used') {
				$implode[] = "((c.uses_total = '1' AND EXISTS (SELECT 1 FROM `" . DB_PREFIX . "coupon_history` ch WHERE ch.coupon_id = c.coupon_id)) OR (c.uses_total > '0' AND (SELECT COUNT(*) FROM `" . DB_PREFIX . "coupon_history` ch WHERE ch.coupon_id = c.coupon_id) >= c.uses_total))";
			} elseif ($data['filter_used'] == '0' || $data['filter_used'] == 'unused') {
				$implode[] = "NOT EXISTS (SELECT 1 FROM `" . DB_PREFIX . "coupon_history` ch WHERE ch.coupon_id = c.coupon_id)";
			} elseif ($data['filter_used'] == 'partial') {
				$implode[] = "EXISTS (SELECT 1 FROM `" . DB_PREFIX . "coupon_history` ch WHERE ch.coupon_id = c.coupon_id)";
				$implode[] = "(c.uses_total = '0' OR (c.uses_total > '1' AND (SELECT COUNT(*) FROM `" . DB_PREFIX . "coupon_history` ch WHERE ch.coupon_id = c.coupon_id) < c.uses_total))";
			}
		}

		if (isset($data['filter_status']) && $data['filter_status'] !== '') {
			if ($data['filter_status'] === '1' || $data['filter_status'] === 'active') {
				$implode[] = "c.status = '1'";
				$implode[] = "c.date_start <= NOW()";
				$implode[] = "c.date_end >= NOW()";
			} elseif ($data['filter_status'] === '0') {
				$implode[] = "c.status = '0'";
			} elseif ($data['filter_status'] === 'expired') {
				$implode[] = "c.date_end < NOW()";
				$implode[] = "c.status = '1'";
			} elseif ($data['filter_status'] === 'pending') {
				$implode[] = "c.date_start > NOW()";
				$implode[] = "c.status = '1'";
			}
		}

		return $implode;
	}

	public function getCouponHistories($coupon_id, $start = 0, $limit = 10) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 10;
		}

		$query = $this->db->query("SELECT ch.order_id, CONCAT(c.firstname, ' ', c.lastname) AS customer, ch.amount, ch.date_added FROM " . DB_PREFIX . "coupon_history ch LEFT JOIN " . DB_PREFIX . "customer c ON (ch.customer_id = c.customer_id) WHERE ch.coupon_id = '" . (int)$coupon_id . "' ORDER BY ch.date_added ASC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalCouponHistories($coupon_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "coupon_history WHERE coupon_id = '" . (int)$coupon_id . "'");

		return $query->row['total'];
	}
}