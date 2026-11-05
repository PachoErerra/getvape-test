<?php
class ModelExtensionDashboardOrdersDay extends Model {
	public function getTotalOrdersByHalfHour($date_start, $date_end = null) {
		$order_data = array();

		for ($i = 0; $i < 48; $i++) {
			$order_data[$i] = array(
				'slot'  => $i,
				'total' => 0
			);
		}

		$date_start = $this->db->escape($date_start);
		$date_end = $this->db->escape($date_end ? $date_end : $date_start);

		$query = $this->db->query("SELECT COUNT(*) AS total, (HOUR(date_added) * 2 + IF(MINUTE(date_added) > 30, 1, 0)) AS slot FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND DATE(date_added) >= DATE('" . $date_start . "') AND DATE(date_added) <= DATE('" . $date_end . "') GROUP BY slot ORDER BY slot ASC");

		foreach ($query->rows as $result) {
			$slot = (int)$result['slot'];

			if ($slot >= 0 && $slot < 48) {
				$order_data[$slot] = array(
					'slot'  => $slot,
					'total' => (int)$result['total']
				);
			}
		}

		return $order_data;
	}

	public function getIntervalLabel($slot) {
		$slot = (int)$slot;
		$hour = (int)floor($slot / 2);

		if ($slot % 2 == 0) {
			return sprintf('%02d:01-%02d:30', $hour, $hour);
		}

		$next_hour = ($hour + 1) % 24;

		return sprintf('%02d:31-%02d:00', $hour, $next_hour);
	}
}
