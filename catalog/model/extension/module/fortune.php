<?php
class ModelExtensionModuleFortune extends Model {
	
	public function checkFriend($fortune) {
		$date_start = new DateTime(date('Y-m-d H:i:s'));
		$date_start->setTime(00, 00, 01);
		$date_end = new DateTime(date('Y-m-d H:i:s'));
		$date_end->setTime(23, 59, 59);
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_fortune WHERE code = '" . $this->db->escape($fortune) . "' AND date_added > '" . $date_start->format('Y-m-d H:i:s') ."' AND date_added <= '" . $date_end->format('Y-m-d H:i:s') ."'");
		
		if ($query->num_rows) {
			return $query->row['customer_id'];
		} else {
			return false;
		}		
	}
	
	public function addCoupon($data, $day = false) {
		
		$date = new DateTime(date('Y-m-d H:i:s')); // Текущая дата
		$date->setTime(23, 59, 59); // Устанавливаем время на 00:00:00

		if ($day) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', conditions = '" . (isset($data['conditions'])?$this->db->escape($data['conditions']):'') . "', code = '" . $this->db->escape($data['code']) . "', discount = '" . (float)$data['discount'] . "', type = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['logged'] . "', shipping = '" . (int)$data['shipping'] . "', date_start = NOW(), date_end = NOW() + INTERVAL '" . (int)$day . "' DAY, uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '" . (int)$data['uses_customer'] . "', status = '" . (int)$data['status'] . "', date_added = NOW()");
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', conditions = '" . (isset($data['conditions'])?$this->db->escape($data['conditions']):'') . "', code = '" . $this->db->escape($data['code']) . "', discount = '" . (float)$data['discount'] . "', type = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['logged'] . "', shipping = '" . (int)$data['shipping'] . "', date_start = NOW(), date_end = '" . $date->format('Y-m-d H:i:s') ."', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '" . (int)$data['uses_customer'] . "', status = '" . (int)$data['status'] . "', date_added = NOW()");
		}
		
		

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

		return $coupon_id;
	}	
	
	public function addWin($data, $day = false) {

		$date = new DateTime(date('Y-m-d H:i:s')); // Текущая дата
		$date->setTime(23, 59, 59); // Устанавливаем время на 00:00:00
		
		if ($day) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_fortune SET customer_id = '" . $this->db->escape($data['customer_id']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', prize_type = '" . $this->db->escape($data['prize_type']) . "', date_added = NOW(), date_valid = NOW() + INTERVAL '" . (int)$day . "' DAY, prize = '" . $this->db->escape($data['name']) . "', code = '" . $this->db->escape($data['code']) . "'");
		} else {
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_fortune SET customer_id = '" . $this->db->escape($data['customer_id']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', prize_type = '" . $this->db->escape($data['prize_type']) . "', date_added = NOW(), date_valid = '" . $date->format('Y-m-d H:i:s') ."', prize = '" . $this->db->escape($data['name']) . "', code = '" . $this->db->escape($data['code']) . "'");
		}
		$prize_id = $this->db->getLastId();
		
		if ($data['name'] == 'double' || $data['name'] == 'Подвійний кешбек') {
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_fortune_prize SET prize_id = '" . (int)$prize_id . "', customer_id = '" . $this->db->escape($data['customer_id']) . "', date_added = NOW(), prize = 'double'");
		} else if ($data['name'] == 'socks') {
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_fortune_prize SET prize_id = '" . (int)$prize_id . "', customer_id = '" . $this->db->escape($data['customer_id']) . "', date_added = NOW(), prize = '" . $this->db->escape($data['name']) . "'");
		}
		
	}	

	public function CheckWinType($prize_type = false) {

		if ($prize_type == 'OneSock') {
			$date_now = new DateTime(date('Y-m-d H:i:s'));
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_fortune WHERE customer_id = '" . (int)$this->customer->getId() . "' AND prize_type = '" . $this->db->escape($prize_type) . "' AND date_valid > '" . $date_now->format('Y-m-d H:i:s') ."' ORDER BY date_added DESC ");
			
			if ($query->num_rows) {
				return strtotime($query->row['date_valid']);
			} else {
				return false;
			}
		}
		
		if ($prize_type == 'Wheel') {
			$date_now = new DateTime(date('Y-m-d H:i:s'));
			$date_now->modify('-15 days');
			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_fortune WHERE customer_id = '" . (int)$this->customer->getId() . "' AND prize_type = '" . $this->db->escape($prize_type) . "' AND date_added > '" . $date_now->format('Y-m-d H:i:s') ."' ORDER BY date_added DESC ");

			if ($query->num_rows) {
				return strtotime($query->row['date_added']);
			} else {
				return false;
			}			
		}
		
		$date_start = new DateTime(date('Y-m-d H:i:s'));
		$date_start->setTime(00, 00, 01);
		$date_end = new DateTime(date('Y-m-d H:i:s'));
		$date_end->setTime(23, 59, 59);
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_fortune WHERE customer_id = '" . $this->db->escape($customer_id) . "' AND date_added > '" . $date_start->format('Y-m-d H:i:s') ."' AND date_added <= '" . $date_end->format('Y-m-d H:i:s') ."'");
		
		if ($query->num_rows) {
			return true;
		} else {
			return false;
		}
	}
	
	public function checkwin($customer_id) {

		$date_start = new DateTime(date('Y-m-d H:i:s'));
		$date_start->setTime(00, 00, 01);
		$date_end = new DateTime(date('Y-m-d H:i:s'));
		$date_end->setTime(23, 59, 59);
		
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_fortune WHERE customer_id = '" . $this->db->escape($customer_id) . "' AND date_added > '" . $date_start->format('Y-m-d H:i:s') ."' AND date_added <= '" . $date_end->format('Y-m-d H:i:s') ."'");
		
		if ($query->num_rows) {
			return true;
		} else {
			return false;
		}
	}	

	public function checkwinsock($customer_id, $prize) {

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_fortune WHERE customer_id = '" . (int)$customer_id . "' AND prize = '" . $this->db->escape($prize) ."' ORDER BY date_added DESC");
		
		if ($query->num_rows) {
			return $query->row;
		} else {
			return false;
		}
	}	
		
	public function usefriendlink($code) {

		$this->db->query("UPDATE `" . DB_PREFIX . "customer_fortune` SET used = (used + 1), date_used = NOW() WHERE code = '" . $this->db->escape($code) . "'");
	}		

	public function checkwin2025($customer_id,$date_start,$date_end) {

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_fortune WHERE customer_id = '" . $this->db->escape($customer_id) . "' AND date_added > '" . $this->db->escape($date_start) ."' AND date_added <= '" . $this->db->escape($date_end) ."'");
		
		if ($query->num_rows) {
			return $query->rows;
		} else {
			return false;
		}
	}

	public function checkwincode($promo_code,$date_start,$date_end) {

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_fortune WHERE code = '" . $this->db->escape($promo_code) . "' AND date_added > '" . $this->db->escape($date_start) ."' AND date_added <= '" . $this->db->escape($date_end) ."'");
		
		if ($query->num_rows) {
			return $query->row;
		} else {
			return false;
		}
	}
	
	public function addWin2025($data,$date_end) {

		$this->db->query("INSERT INTO " . DB_PREFIX . "customer_fortune SET customer_id = '" . $this->db->escape($data['customer_id']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', date_added = NOW(), date_valid = '" . $this->db->escape($date_end) ."', prize = '" . $this->db->escape($data['name']) . "', code = '" . $this->db->escape($data['code']) . "'");
 
		$prize_id = $this->db->getLastId();
		
		if ($data['name'] == 'double' || $data['name'] == 'Подвійний кешбек') {
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_fortune_prize SET prize_id = '" . (int)$prize_id . "', customer_id = '" . $this->db->escape($data['customer_id']) . "', date_added = NOW(), prize = 'double'");
		} else if ($data['name'] == 'socks') {
			$this->db->query("INSERT INTO " . DB_PREFIX . "customer_fortune_prize SET prize_id = '" . (int)$prize_id . "', customer_id = '" . $this->db->escape($data['customer_id']) . "', date_added = NOW(), prize = '" . $this->db->escape($data['name']) . "'");
		}
		
	}	
	
	public function addCoupon2025($data,$date_end) {
		
		$this->db->query("INSERT INTO " . DB_PREFIX . "coupon SET name = '" . $this->db->escape($data['name']) . "', code = '" . $this->db->escape($data['code']) . "', discount = '" . (float)$data['discount'] . "', type = '" . $this->db->escape($data['type']) . "', total = '" . (float)$data['total'] . "', logged = '" . (int)$data['logged'] . "', shipping = '" . (int)$data['shipping'] . "', date_start = NOW(), date_end = '" . $this->db->escape($date_end) ."', uses_total = '" . (int)$data['uses_total'] . "', uses_customer = '" . (int)$data['uses_customer'] . "', status = '" . (int)$data['status'] . "', date_added = NOW()");

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

		return $coupon_id;
	}
	
	public function getTotalOrders() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` o WHERE customer_id = '" . (int)$this->customer->getId() . "' AND o.order_status_id = '5' AND o.store_id = '" . (int)$this->config->get('config_store_id') . "'");
		return $query->row['total'];
	}
	
	public function cronclearwinners() {
/*
		$this->db->query("TRUNCATE " . DB_PREFIX . "customer_fortune");
		
		$this->db->query("TRUNCATE " . DB_PREFIX . "customer_fortune_prize");
		
	//	$this->db->query("DELETE FROM " . DB_PREFIX . "customer_fortune_prize WHERE date_added < DATE_SUB(NOW(), INTERVAL 24 HOUR)"); 
*/
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "coupon WHERE date_end < DATE_SUB(NOW(), INTERVAL 48 HOUR)");
		if ($query->num_rows) {
			foreach ($query->rows as $result) {
				$this->db->query("DELETE FROM " . DB_PREFIX . "coupon WHERE coupon_id = '" . (int)$result['coupon_id'] . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE coupon_id = '" . (int)$result['coupon_id'] . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_category WHERE coupon_id = '" . (int)$result['coupon_id'] . "'");
				$this->db->query("DELETE FROM " . DB_PREFIX . "coupon_history WHERE coupon_id = '" . (int)$result['coupon_id'] . "'");				
			}
		}
		
	}
}