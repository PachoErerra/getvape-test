<?php
class ControllerExtensionFeedUpdatesearch extends Controller {

	public function index() {

				if($this->config->get('config_search')) {
					$search_lines = explode(PHP_EOL, $this->config->get('config_search'));
					
				//	print_r($search_lines);
					$url_array = array();
					foreach($search_lines as $line) {
						$line_array = explode("\t", $line);	
						
						$url = trim(parse_url($line_array[0], PHP_URL_PATH), '/');
						
						$category_id = $this->getCategoryId($url);
						
						$url_array[$category_id] .= ' ' . trim($line_array[1]);
					}

					foreach ($url_array as $key=>$value) {
						$products = $this->getProducts($key);
						foreach ($products as $product) {
							$this->updateSearch($product, $value);
						}
					}
				}
				echo 'Done';
				
	}
	
	public function getCategoryId($url) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE keyword = '" . $url . "'");
		
		if ($query->num_rows) {
			return str_replace('category_id=','', $query->row['query']);
		}
	}
	
	public function getProducts($category_id) {
		$product_data = array();
		$query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product_to_category WHERE category_id = '" . $category_id . "'");
		
		foreach ($query->rows as $result) {
			$product_data[] = $result['product_id'];
		}
		
		return $product_data;
	}
	
	public function updateSearch($product_id, $text) {
		$this->db->query("UPDATE " . DB_PREFIX . "product_description SET search = '" . $this->db->escape($text) . "' WHERE product_id = '" . (int)$product_id . "'");
	}
}
