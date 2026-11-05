<?php
class ControllerExtensionFeedUpdateVarints extends Controller {

	public function index() {
		set_time_limit(0);
		ini_set( 'memory_limit', '1024M');

		$products = $this->getProducts();
		
		$variants = array();
		foreach ($products as $product_id) {
			$query_category_id = $this->db->query("SELECT DISTINCT category_id FROM " . DB_PREFIX . "product_types WHERE product_id = '" . (int)$product_id . "' AND attribute_id = 40");

			if ($query_category_id->num_rows) {
				$category_id = $query_category_id->row['category_id'];	
				$query_attribute = $this->db->query("SELECT * FROM " . DB_PREFIX . "attribute_description WHERE attribute_id = 40");
					if ($query_attribute->num_rows) {
						$variant = array();
						foreach ($query_attribute->rows as $attribute) {
							$query_ids = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product_types WHERE category_id = '" . (int)$category_id . "' AND attribute_id = 40");
								if ($query_ids->num_rows) {
									foreach ($query_ids->rows as $id) {
										$query_attribute_text = $this->db->query("SELECT text FROM " . DB_PREFIX . "product_attribute WHERE attribute_id = 40 AND language_id = '" . $attribute['language_id'] . "' AND product_id = '" . $id['product_id'] . "'");
											if (isset($query_attribute_text->row['text']) && $query_attribute_text->row['text']) {
												$variant[$id['product_id']] = array(
													'name' => $query_attribute_text->row['text'],
												);
											}
									}
								}
							$variants[$attribute['language_id']][$attribute['name']] = $variant;
						}
						uasort($variants[$attribute['language_id']][$attribute['name']], $this->sort_function('name'));
						if (count($variants[$attribute['language_id']][$attribute['name']]) <= 1) {
							unset($variants[$attribute['language_id']][$attribute['name']]);
						}						
						$this->db->query("UPDATE " . DB_PREFIX . "product SET variants = '" . $this->db->escape(json_encode($variants ?? [], JSON_UNESCAPED_UNICODE)) . "', main_category = '" . (int)$category_id . "' WHERE product_id = '" . (int)$product_id . "'");
					} else {
						return false;
					}					
			}		
		}
		$removed = $this->cache->delete('product*');
		$this->log->write('Варианты обновлены. Удалено ключей товаров: '.  $removed);	
	}

	private function sort_function($key){
		return function ($a, $b) use ($key) {
			return !empty($a[$key]) && !empty($b[$key]) ? strnatcmp($a[$key], $b[$key]) : false;
		};
	}
	
	public function getProducts() {
		$product_data = array();
		$query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product WHERE status = 1");

		foreach ($query->rows as $result) {
			$product_data[] = $result['product_id'];
		}
		return $product_data;
	}
}
