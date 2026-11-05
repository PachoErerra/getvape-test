<?php
class ModelExtensionDashboardChart extends Model {
	public function getTotalOrdersByHour($date) {
		$order_data = array();

		for ($i = 0; $i < 24; $i++) {
			$order_data[$i] = array(
				'hour'  => $i,
				'total' => 0
			);
		}

		$query = $this->db->query("SELECT COUNT(*) AS total, HOUR(date_added) AS hour FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND DATE(date_added) = DATE('" . $this->db->escape($date) . "') GROUP BY HOUR(date_added) ORDER BY hour ASC");

		foreach ($query->rows as $result) {
			$order_data[(int)$result['hour']] = array(
				'hour'  => (int)$result['hour'],
				'total' => (int)$result['total']
			);
		}

		return $order_data;
	}

	public function getTotalCustomersByHour($date) {
		$customer_data = array();

		for ($i = 0; $i < 24; $i++) {
			$customer_data[$i] = array(
				'hour'  => $i,
				'total' => 0
			);
		}

		$query = $this->db->query("SELECT COUNT(*) AS total, HOUR(date_added) AS hour FROM `" . DB_PREFIX . "customer` WHERE DATE(date_added) = DATE('" . $this->db->escape($date) . "') GROUP BY HOUR(date_added) ORDER BY hour ASC");

		foreach ($query->rows as $result) {
			$customer_data[(int)$result['hour']] = array(
				'hour'  => (int)$result['hour'],
				'total' => (int)$result['total']
			);
		}

		return $customer_data;
	}

	public function getTotalOrdersByDateRange($date_start, $date_end) {
		$order_data = array();
		$days = array();

		$start = strtotime($date_start);
		$end = strtotime($date_end);

		for ($time = $start; $time <= $end; $time += 86400) {
			$key = date('Y-m-d', $time);
			$days[] = $key;
			$order_data[$key] = array(
				'day'   => $key,
				'total' => 0
			);
		}

		if (!$days) {
			return array($order_data, $days);
		}

		$query = $this->db->query("SELECT COUNT(*) AS total, DATE(date_added) AS date_key FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND DATE(date_added) >= DATE('" . $this->db->escape($date_start) . "') AND DATE(date_added) <= DATE('" . $this->db->escape($date_end) . "') GROUP BY DATE(date_added) ORDER BY date_key ASC");

		foreach ($query->rows as $result) {
			$key = $result['date_key'];

			if (isset($order_data[$key])) {
				$order_data[$key]['total'] = (int)$result['total'];
			}
		}

		return array($order_data, $days);
	}

	public function getTotalCustomersByDateRange($date_start, $date_end) {
		$customer_data = array();
		$days = array();

		$start = strtotime($date_start);
		$end = strtotime($date_end);

		for ($time = $start; $time <= $end; $time += 86400) {
			$key = date('Y-m-d', $time);
			$days[] = $key;
			$customer_data[$key] = array(
				'day'   => $key,
				'total' => 0
			);
		}

		if (!$days) {
			return array($customer_data, $days);
		}

		$query = $this->db->query("SELECT COUNT(*) AS total, DATE(date_added) AS date_key FROM `" . DB_PREFIX . "customer` WHERE DATE(date_added) >= DATE('" . $this->db->escape($date_start) . "') AND DATE(date_added) <= DATE('" . $this->db->escape($date_end) . "') GROUP BY DATE(date_added) ORDER BY date_key ASC");

		foreach ($query->rows as $result) {
			$key = $result['date_key'];

			if (isset($customer_data[$key])) {
				$customer_data[$key]['total'] = (int)$result['total'];
			}
		}

		return array($customer_data, $days);
	}

	public function getTotalOrdersByDay() {
		return $this->getTotalOrdersByHour(date('Y-m-d'));
	}

	public function getTotalCustomersByDay() {
		return $this->getTotalCustomersByHour(date('Y-m-d'));
	}

	public function getTotalOrdersByWeek() {
		$date_start = date('Y-m-d', strtotime('-6 days'));
		$date_end = date('Y-m-d');
		list($order_data) = $this->getTotalOrdersByDateRange($date_start, $date_end);

		return $order_data;
	}

	public function getTotalCustomersByWeek() {
		$date_start = date('Y-m-d', strtotime('-6 days'));
		$date_end = date('Y-m-d');
		list($customer_data) = $this->getTotalCustomersByDateRange($date_start, $date_end);

		return $customer_data;
	}

	public function getTotalOrdersByMonth() {
		$date_start = date('Y-m-01');
		$date_end = date('Y-m-d');
		list($order_data) = $this->getTotalOrdersByDateRange($date_start, $date_end);

		return $order_data;
	}

	public function getTotalCustomersByMonth() {
		$date_start = date('Y-m-01');
		$date_end = date('Y-m-d');
		list($customer_data) = $this->getTotalCustomersByDateRange($date_start, $date_end);

		return $customer_data;
	}

	public function getTotalOrdersByYear() {
		$order_data = array();

		for ($i = 1; $i <= 12; $i++) {
			$order_data[$i] = array(
				'month' => date('M', mktime(0, 0, 0, $i)),
				'total' => 0
			);
		}

		$query = $this->db->query("SELECT COUNT(*) AS total, date_added FROM `" . DB_PREFIX . "order` WHERE order_status_id > '0' AND YEAR(date_added) = YEAR(NOW()) GROUP BY MONTH(date_added)");

		foreach ($query->rows as $result) {
			$order_data[date('n', strtotime($result['date_added']))] = array(
				'month' => date('M', strtotime($result['date_added'])),
				'total' => $result['total']
			);
		}

		return $order_data;
	}

	public function getTotalCustomersByYear() {
		$customer_data = array();

		for ($i = 1; $i <= 12; $i++) {
			$customer_data[$i] = array(
				'month' => date('M', mktime(0, 0, 0, $i)),
				'total' => 0
			);
		}

		$query = $this->db->query("SELECT COUNT(*) AS total, date_added FROM `" . DB_PREFIX . "customer` WHERE YEAR(date_added) = YEAR(NOW()) GROUP BY MONTH(date_added)");

		foreach ($query->rows as $result) {
			$customer_data[date('n', strtotime($result['date_added']))] = array(
				'month' => date('M', strtotime($result['date_added'])),
				'total' => $result['total']
			);
		}

		return $customer_data;
	}
}
