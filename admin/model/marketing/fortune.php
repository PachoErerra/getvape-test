<?php
class ModelMarketingFortune extends Model {
	
	public function getPrizes() {
		
		$prizes = array();
		$sql = "SELECT DISTINCT prize FROM `" . DB_PREFIX . "customer_fortune`";
		
		$query = $this->db->query($sql);
		
		foreach ($query->rows as $row) {
			$prizes[] = $row['prize'];
		}
		return $prizes;	
		
	}
	public function getTotalWins($data) {
		
		$date_now = new DateTime(date('Y-m-d H:i:s'));
		
		$sql = "SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "customer_fortune` WHERE fortune_id != 0 ";

		if (!empty($data['filter_prize'])) {
			$sql .= " AND prize IN ('" . implode("','", $data['filter_prize']) . "')";
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
			$sql .= " AND date_valid <= '" . $date_now->format('Y-m-d H:i:s') ."'";
			$sql .= " AND used < 1 ";
		}
		if (!empty($data['filter_active'])) {
			$sql .= " AND date_valid >= '" . $date_now->format('Y-m-d H:i:s') ."'";
			$sql .= " AND used < 1 ";
		}

		$query = $this->db->query($sql);

		return $query->row['total'];		
	}
	
	public function getWins($data) {

		$date_now = new DateTime(date('Y-m-d H:i:s'));
		
		$sql = "SELECT * FROM `" . DB_PREFIX . "customer_fortune` WHERE fortune_id != 0 ";

		if (!empty($data['filter_prize'])) {
			$sql .= " AND prize IN ('" . implode("','", $data['filter_prize']) . "')";
		}
		if (!empty($data['filter_order_id'])) {
			$sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
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

		
		if (!empty($data['filter_used'])) {
			$sql .= " AND used >= '" . (float)$data['filter_used'] . "'";
		}

		if (!empty($data['filter_overdue'])) {
			$sql .= " AND date_valid <= '" . $date_now->format('Y-m-d H:i:s') ."'";
			$sql .= " AND used < 1 ";
		}
		if (!empty($data['filter_active'])) {
			$sql .= " AND date_valid >= '" . $date_now->format('Y-m-d H:i:s') ."'";
			$sql .= " AND used < 1 ";
		}

		$sort_data = array(
			'fortune_id',
			'telephone',
			'customer',
			'prize',
			'date_added',
			'date_used',
			'fortune_id'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY fortune_id";
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
	
    public function deleteFortune($fortune_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_fortune WHERE fortune_id = '" . (int)$fortune_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_fortune_prize WHERE prize_id = '" . (int)$fortune_id . "'");

    }
}