<?php
class ModelReportStatistics extends Model {
	public function getStatistics() {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "statistics");

		return $query->rows;
	}
	
	public function getStatus($array) {
		$query = $this->db->query("SELECT COUNT(order_status_id)  AS total FROM " . DB_PREFIX . "order WHERE `order_status_id` IN (" . implode(',', $array) . ") AND `order_status_id` != '0'");

		return $query->row['total'];
	}
	
	public function getStatusAny() {
		$query = $this->db->query("SELECT COUNT(order_status_id)  AS total FROM " . DB_PREFIX . "order WHERE `order_status_id` NOT IN (" . implode(',',  array_merge($this->config->get('config_processing_status'), $this->config->get('config_complete_status'))) . ") AND `order_status_id` != '0'");

		return $query->row['total'];
	}
	
	public function getValue($code) {
		$query = $this->db->query("SELECT value FROM " . DB_PREFIX . "statistics WHERE `code` = '" . $this->db->escape($code) . "'");

		if ($query->num_rows) {
			return $query->row['value'];
		} else {
			return null;	
		}
	}
	
	public function addValue($code, $value) {
		$this->db->query("UPDATE " . DB_PREFIX . "statistics SET `value` = (`value` + '" . (float)$value . "') WHERE `code` = '" . $this->db->escape($code) . "'");
	}
	
	public function editValue($code, $value) {
		$this->db->query("UPDATE " . DB_PREFIX . "statistics SET `value` = '" . (float)$value . "' WHERE `code` = '" . $this->db->escape($code) . "'");
	}
		
	public function removeValue($code, $value) {
		$this->db->query("UPDATE " . DB_PREFIX . "statistics SET `value` = (`value` - '" . (float)$value . "') WHERE `code` = '" . $this->db->escape($code) . "'");
	}	
}
