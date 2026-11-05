<?php
class ModelMarketingLottery extends Model {

	private function getFilterSql($data) {
		$date_now = new DateTime(date('Y-m-d H:i:s'));
		$sql = " WHERE lottery_history_id != 0 ";

		if (!empty($data['filter_prize'])) {
			$prizes = array();

			foreach ((array)$data['filter_prize'] as $prize) {
				if ($prize === '__empty__') {
					$prizes[] = '';
				} else {
					$prizes[] = $this->db->escape($prize);
				}
			}

			$sql .= " AND prize_type IN ('" . implode("','", $prizes) . "')";
		}

		if (!empty($data['filter_order_id'])) {
			$sql .= " AND order_id = '" . (int)$data['filter_order_id'] . "'";
		}

		if (!empty($data['filter_customer'])) {
			$sql .= " AND telephone LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
		}

		if (!empty($data['filter_date_added_start']) && !empty($data['filter_date_added_end'])) {
			$sql .= " AND date_added >= '" . $this->db->escape($data['filter_date_added_start']) . "' AND date_added <='" . $this->db->escape($data['filter_date_added_end']) . "'";
		}
		if (!empty($data['filter_date_added_start']) && empty($data['filter_date_added_end'])) {
			$sql .= " AND date_added >= '" . $this->db->escape($data['filter_date_added_start']) . "'";
		}
		if (empty($data['filter_date_added_start']) && !empty($data['filter_date_added_end'])) {
			$sql .= " AND date_added <='" . $this->db->escape($data['filter_date_added_end']) . "'";
		}

		if (!empty($data['filter_date_used_start']) && !empty($data['filter_date_used_end'])) {
			$sql .= " AND date_used >= '" . $this->db->escape($data['filter_date_used_start']) . "' AND date_used <='" . $this->db->escape($data['filter_date_used_end']) . "'";
		}
		if (!empty($data['filter_date_used_start']) && empty($data['filter_date_used_end'])) {
			$sql .= " AND date_used >= '" . $this->db->escape($data['filter_date_used_start']) . "'";
		}
		if (empty($data['filter_date_used_start']) && !empty($data['filter_date_used_end'])) {
			$sql .= " AND date_used <='" . $this->db->escape($data['filter_date_used_end']) . "'";
		}

		if (!empty($data['filter_date_modified'])) {
			$sql .= " AND DATE(date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
		}

		if (!empty($data['filter_used'])) {
			$sql .= " AND used >= '" . (float)$data['filter_used'] . "'";
		}

		if (!empty($data['filter_overdue'])) {
			$sql .= " AND date_valid <= '" . $date_now->format('Y-m-d H:i:s') . "'";
			$sql .= " AND used < 1 ";
		}
		if (!empty($data['filter_active'])) {
			$sql .= " AND date_valid >= '" . $date_now->format('Y-m-d H:i:s') . "'";
			$sql .= " AND used < 1 ";
		}

		return $sql;
	}

	public function getPrizeLabel($prize_type) {
		if ($prize_type === 'points') {
			return 'Бонусні бали';
		}

		if ($prize_type === 'ultra') {
			return 'Ультра';
		}

		if ($prize_type === 'none') {
			return 'Без призу (none)';
		}

		if ($prize_type === '' || $prize_type === null) {
			return 'Порожній запис (помилка)';
		}

		return (string)$prize_type;
	}

	public function getPrizes() {
		$prizes = array();
		$sql = "SELECT DISTINCT prize_type FROM `" . DB_PREFIX . "customer_lottery_history` ORDER BY prize_type ASC";
		$query = $this->db->query($sql);

		foreach ($query->rows as $row) {
			$prize_type = $row['prize_type'];
			$value = ($prize_type === '' || $prize_type === null) ? '__empty__' : $prize_type;

			$prizes[] = array(
				'value' => $value,
				'label' => $this->getPrizeLabel($prize_type)
			);
		}

		return $prizes;
	}

	public function getTotalWins($data) {
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "customer_lottery_history`" . $this->getFilterSql($data);
		$query = $this->db->query($sql);

		return $query->row['total'];
	}

	public function getTotals($data) {
		$totals = array(
			'claims' => 0,
			'points' => 0,
			'ultra' => 0,
			'empty' => 0,
			'other' => 0
		);

		$sql = "SELECT prize_type, COUNT(*) AS claims, COALESCE(SUM(amount), 0) AS total_amount
			FROM `" . DB_PREFIX . "customer_lottery_history`"
			. $this->getFilterSql($data) . "
			GROUP BY prize_type";

		$query = $this->db->query($sql);

		foreach ($query->rows as $row) {
			$claims = (int)$row['claims'];
			$amount = (float)$row['total_amount'];
			$totals['claims'] += $claims;

			if ($row['prize_type'] == 'points') {
				$totals['points'] += $amount;
			} elseif ($row['prize_type'] == 'ultra') {
				$totals['ultra'] += $amount;
			} elseif ($row['prize_type'] == 'none' || $row['prize_type'] === '' || $row['prize_type'] === null) {
				$totals['empty'] += $claims;
			} else {
				$totals['other'] += $amount;
			}
		}

		return $totals;
	}

	public function getWins($data) {
		$sql = "SELECT * FROM `" . DB_PREFIX . "customer_lottery_history`" . $this->getFilterSql($data);

		$sort_data = array(
			'lottery_history_id',
			'telephone',
			'customer',
			'prize_type',
			'date_added',
			'date_used',
			'lottery_history_id'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY lottery_history_id";
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
}