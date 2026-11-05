<?php
class ControllerExtensionFeedUpdateOptions extends Controller {

	public function index() {
		set_time_limit(0);
		ini_set( 'memory_limit', '1024M');
		$this->load->model('catalog/product');
		//айди категории солей
		$category_id = 3013;
		
		//айди категории глицерина
//		$glicerin_category_id = 4403;

		//айди никотина
		$nikotin_id = 18565;
		
		//айди категории бустеров
		$buster_category_id = 2444;
		$buster_products = $this->getProducts($buster_category_id);

		$buster_products = array_diff($buster_products, [$nikotin_id]);

		//цена глицерина
		$glicerin_price = 30;
		
		$nikotin_info = $this->model_catalog_product->getProduct($nikotin_id);
		//цена никотина
		if (!is_null($nikotin_info['special']) && (float)$nikotin_info['special'] >= 0) {
			$nikotin_price = (float)$nikotin_info['special'];
		} else {
			$nikotin_price = (float)$nikotin_info['price'];
		}

//		$products_glicerin = $this->getProducts($glicerin_category_id);
//		$total_glicerin = $this->getTotalGlicerin($glicerin_category_id);

		$products = $this->getProducts($category_id);
		$file = DIR_LOGS . 'sol.products';
		file_put_contents($file, json_encode($products));


		foreach ($products as $product_id) {

			$ProductVolume = (int)$this->getProductVolume($product_id);

			if ($ProductVolume) {
				if ($ProductVolume >= 30) {
					$nikotin_price_corrected = $nikotin_price;
				} else if ($ProductVolume >= 15) {
					$nikotin_price_corrected = $nikotin_price*0.7;
				} else {
					$nikotin_price_corrected = $nikotin_price*0.4;
				}

				$this->updateOptions($product_id,$glicerin_price,$nikotin_price_corrected,$buster_products);
			}
		}		


	}
	
	public function updateOptions($product_id,$glicerin_price,$nikotin_price_corrected,$buster_products) {

		$this->db->query("UPDATE " . DB_PREFIX . "product SET price = (price + '" . (float)($glicerin_price+$nikotin_price_corrected) . "'), buster_products = '" . (isset($buster_products) ? $this->db->escape(json_encode($buster_products, true)) : '') . "' WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("UPDATE " . DB_PREFIX . "product_special SET price = (price + '" . (float)($glicerin_price+$nikotin_price_corrected) . "')  WHERE product_id = '" . (int)$product_id . "'");
		
		$product_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
		$price = $product_query->row['price'];

		$product_special_query = $this->db->query("SELECT price FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY priority ASC, price ASC LIMIT 1");

		if ($product_special_query->num_rows) {
			$price = $product_special_query->row['price'];
		}

		$product_reward = array(
			1 => round($price*0.05),
			);
		$bonus = 5;
		$this->updateproductreward($product_id,$product_reward,$bonus);	

		//проверяем опцию глицерин
		$query = $this->db->query("SELECT product_option_id, option_id FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "' AND option_id = 3");
		if (!$query->num_rows) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = 3, required = 0");	
			$product_option_id = $this->db->getLastId();
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = 3, option_value_id = 25, quantity = 100, subtract = 1, price = '" . (float)$glicerin_price . "', price_prefix = '-', points = '" . (int)round($glicerin_price*0.05) . "', points_prefix = '-', weight = 0, weight_prefix = '+'");
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET
				quantity = 100,
				price = '" . (float)$glicerin_price . "',
				points = '" . (int)round($glicerin_price*0.05) . "',
				points_prefix = '-'
				WHERE product_option_id = '" . (int)$query->row['product_option_id'] . "' AND product_id = '" . (int)$product_id . "'");		
		}

		//проверяем опцию никотин
		$query = $this->db->query("SELECT product_option_id, option_id FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "' AND option_id = 4");
		if (!$query->num_rows) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = 4, required = 0");	
			$product_option_id = $this->db->getLastId();
			$this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = 4, option_value_id = 26, quantity = 100, subtract = 1, price = '" . (float)$nikotin_price_corrected . "', price_prefix = '-', points = '" . (int)round($nikotin_price_corrected*0.05) . "', points_prefix = '-', weight = 0, weight_prefix = '+'");
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "product_option_value SET
				quantity = 100,
				price = '" . (float)$nikotin_price_corrected . "',
				points = '" . (int)round($nikotin_price_corrected*0.05) . "',
				points_prefix = '-'
				WHERE product_option_id = '" . (int)$query->row['product_option_id'] . "' AND product_id = '" . (int)$product_id . "'");		
		}
		
	}

	public function updateproductreward($product_id, $product_reward,$bonus) {
		
			$this->db->query("UPDATE " . DB_PREFIX . "product SET bonus = '" . (int)$bonus . "' WHERE product_id = '" . (int)$product_id . "'");
			
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");
			
			foreach ($product_reward as $customer_group_id => $product_reward) {
				if ((int)$product_reward > 0) {
					$this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$product_reward . "', bonus = '" . (int)$bonus . "'");
				}
			}
	}
	
	public function getProducts($category_id) {
		$product_data = array();
/*		
		$query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product_to_category p2c
		LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)
		WHERE category_id = '" . $category_id . "' AND status = 1
		");
*/
		$query = $this->db->query("
		SELECT DISTINCT p.product_id
		FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)
		LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)
		WHERE
		  cp.path_id = '" . $category_id . "'
		  AND p.status = 1
		;");

		
		foreach ($query->rows as $result) {
			$product_data[] = $result['product_id'];
		}
		
		return $product_data;
	}

	public function getTotalGlicerin($category_id) {
		$product_data = array();
		$query = $this->db->query("SELECT SUM(quantity) as total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category pc ON (p.product_id = pc.product_id) WHERE pc.category_id = '" . $category_id . "' AND p.status = 1");
		
		return $query->row['total'];
	}
	
	public function getProductVolume($product_id) {

		$query = $this->db->query("SELECT text FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . $product_id . "' AND attribute_id = 41 AND language_id = 1");
		
		if ($query->num_rows) {
			return $query->row['text'];
		} else {
			return false;
		}
	}

}
