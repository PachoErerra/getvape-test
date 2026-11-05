<?php
class ControllerExtensionFeedSetsales extends Controller {
	public function index() {
		
		//ALTER TABLE `oc_product` ADD `sales` INT(11) NOT NULL AFTER `viewed`;
		
		foreach($this->db->query("SELECT product_id FROM ".DB_PREFIX."product")->rows as $product){
			$allow[] = $product['product_id'];
		}
		
		foreach ($allow as $product_id) {
			$this->setSold($product_id);
		}
	}
	
	public function setSold($product_id) {
		$query = $this->db->query("SELECT SUM(op.quantity) AS total FROM `" . DB_PREFIX . "order_product` op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) WHERE o.order_status_id > '0' AND op.product_id = '" . (int)$product_id . "'");
		if ($query->row) {
//			return $query->row['total'];
			
			$this->db->query("UPDATE " . DB_PREFIX . "product SET sales = '" . (int)$query->row['total'] . "' WHERE product_id = '" . (int)$product_id . "'");
			
		}
	} 
	
	public function setRelated() {

		$http_pass = 'fgh436hfg23pofger34h65jt8Fk5jFht84GJt';
		if($http_pass != $_GET['http_pass']) die('Неверный ключ доступа!');
	
		$bestsellers = array();
		$allproducts = array();
		$products = array();
		//выбираем 3 бестселлера из рідину (органіка) 2810
		$sql = "SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE p2c.category_id = 2810 AND p.quantity > 0 AND p.price > 0 AND p.status > 0  AND p.image <> '' ORDER BY p.sales DESC LIMIT 3";

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$bestsellers[] = $result['product_id'];
		}

		//устанавливаем лимит
		$limit = 10 - count($bestsellers);

		//выбираем случайные товары до 10
		$sql = "SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE p2c.category_id = 2810 AND p.quantity > 0 AND p.price > 0 AND p.status > 0  AND p.image <> '' ";
		if (count($bestsellers) != 0) {
			$sql .= "AND p.product_id NOT IN (" . implode(',', $bestsellers) . ")";
		}
		//$sql .= " ORDER BY RAND() LIMIT " . $limit;
				
		$query = $this->db->query($sql);
				
		foreach ($query->rows as $result) {
		//	$bestsellers[] = $result['product_id'];
			$allproducts[] = $result['product_id'];
		}
				
		//выбираем товары < 0.6
		$sql = "SELECT p.product_id,pa.text FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) 
				LEFT JOIN " . DB_PREFIX . "product_attribute pa ON (p.product_id = pa.product_id) WHERE p2c.category_id = 1320 AND pa.attribute_id = 38 AND REPLACE(REPLACE(pa.text,',','.'),'Ohm','') < 0.6";
		$query = $this->db->query($sql);
			
		//устанавливаем связи
		foreach ($query->rows as $result) {
			shuffle($allproducts);
			$randomproducts = $allproducts;
			$products = array_merge($bestsellers, array_slice($randomproducts, 0, (int)$limit));
			$this->db->query("UPDATE " . DB_PREFIX . "product SET relateds = '" . (!empty($products)?$this->db->escape(json_encode($products, true)):'') . "' WHERE product_id = '" . (int)$result['product_id'] . "'");
		}			
		//конец из рідину (органіка) 2810 


		$bestsellers = array();
		$allproducts = array();
		$products = array();
		//выбираем 3 бестселлера из рідину (сіль) 3013
		$sql = "SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE p2c.category_id = 3013 AND p.quantity > 0 AND p.price > 0 AND p.status > 0  AND p.image <> '' ORDER BY p.sales DESC LIMIT 3";

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$bestsellers[] = $result['product_id'];
		}

		//устанавливаем лимит
		$limit = 10 - count($bestsellers);

		//выбираем случайные товары до 10
		$sql = "SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE p2c.category_id = 3013 AND p.quantity > 0 AND p.price > 0 AND p.status > 0  AND p.image <> '' ";
		if (count($bestsellers) != 0) {
			$sql .= "AND p.product_id NOT IN (" . implode(',', $bestsellers) . ")";
		}
		//$sql .= " ORDER BY RAND() LIMIT " . $limit;
				
		$query = $this->db->query($sql);
				
		foreach ($query->rows as $result) {
			//$bestsellers[] = $result['product_id'];
			$allproducts[] = $result['product_id'];
		}
				
		//выбираем товары >= 0.6
		$sql = "SELECT p.product_id,pa.text FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) 
				LEFT JOIN " . DB_PREFIX . "product_attribute pa ON (p.product_id = pa.product_id) WHERE p2c.category_id = 1320 AND pa.attribute_id = 38 AND REPLACE(REPLACE(pa.text,',','.'),'Ohm','') >= 0.6";
		$query = $this->db->query($sql);
			
		//устанавливаем связи
		foreach ($query->rows as $result) {
			shuffle($allproducts);
			$randomproducts = $allproducts;
			$products = array_merge($bestsellers, array_slice($randomproducts, 0, (int)$limit));
			$this->db->query("UPDATE " . DB_PREFIX . "product SET relateds = '" . (!empty($products)?$this->db->escape(json_encode($products, true)):'') . "' WHERE product_id = '" . (int)$result['product_id'] . "'");
		}	
		//конец из рідину (сіль) 3013
		
			
		//обрабатываем данные из админки
		$related = array();
		foreach($this->db->query("SELECT category_id, category_related FROM ".DB_PREFIX."category WHERE category_related <> ''")->rows as $category){
			$related[$category['category_id']] = json_decode($category['category_related'], true);
		}			
			
		foreach ($related as $category_id=>$relateds) {
			if ($category_id != 1320) {

				$bestsellers = array();
				$allproducts = array();
				$products = array();
				//выбираем 3 бестселлера из рекомендуемых категорий
				$sql = "SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE p2c.category_id IN (" . implode(',', $relateds) . ") AND p.quantity > 0 AND p.price > 0 AND p.status > 0  AND p.image <> '' ORDER BY p.sales DESC LIMIT 3";

				$query = $this->db->query($sql);

				foreach ($query->rows as $result) {
					$bestsellers[] = $result['product_id'];
				}

				//устанавливаем лимит
				$limit = 10 - count($bestsellers);

				//выбираем случайные товары до 10
				$sql = "SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE p2c.category_id IN (" . implode(',', $relateds) . ") AND p.quantity > 0 AND p.price > 0 AND p.status > 0  AND p.image <> '' ";
				if (count($bestsellers) != 0) {
					$sql .= "AND p.product_id NOT IN (" . implode(',', $bestsellers) . ")";
				}
				//$sql .= " ORDER BY RAND() LIMIT " . $limit;
				
				$query = $this->db->query($sql);
				
				foreach ($query->rows as $result) {
				//	$bestsellers[] = $result['product_id'];
					$allproducts[] = $result['product_id'];
				}

				//выбираем товары категории
				$sql = "SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE p2c.category_id = " . $category_id;
				
				$query = $this->db->query($sql);
				//устанавливаем связи
				foreach ($query->rows as $result) {
					shuffle($allproducts);
					$randomproducts = $allproducts;
					$products = array_merge($bestsellers, array_slice($randomproducts, 0, (int)$limit));
		
					$this->db->query("UPDATE " . DB_PREFIX . "product SET relateds = '" . (!empty($products)?$this->db->escape(json_encode($products, true)):'') . "' WHERE product_id = '" . (int)$result['product_id'] . "'");
				}
			}
		}
		
		echo 'Рекомендуемые товары обновлены';

	} 
}
		