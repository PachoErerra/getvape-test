<?php
class ControllerExtensionFeedSettopfilterbrand extends Controller {
	public function index() {
		
		//ALTER TABLE `oc_product` ADD `sales` INT(11) NOT NULL AFTER `viewed`;
/*		
		$query = $this->db->query("SELECT m.name AS manufacturer FROM ".DB_PREFIX."product p LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) ORDER BY p.sales DESC LIMIT 0,10");
		
		foreach ($query->rows as $row) {
			$brands[] = $row['manufacturer'];
		}
		$topbrands = array_unique($brands);

//		print_r($topbrands);
*/


		$query = $this->db->query("SELECT * FROM ".DB_PREFIX."manufacturer");

		foreach ($query->rows as $row) {
			
			$query_sales = $this->db->query("SELECT SUM(sales) AS total FROM " . DB_PREFIX . "product WHERE manufacturer_id = '".$row['manufacturer_id']."'");
		
			if ($query_sales->row) {
				$newbrands[(int)$query_sales->row['total']] = $row['name'];
			}

		}

		krsort($newbrands);

//		print_r($newbrands);

		$this->db->query("UPDATE " . DB_PREFIX . "ocfilter_filter_value SET sort_order = 0 WHERE filter_id = 43");
		
		$i = -8;
		foreach ($newbrands as $brand) {
			$query = $this->db->query("SELECT value_id FROM ".DB_PREFIX."ocfilter_filter_value_description WHERE filter_id = 43 AND name = '".$brand."'");
			if ($query->row) {
				$this->db->query("UPDATE " . DB_PREFIX . "ocfilter_filter_value SET sort_order = '".$i."' WHERE filter_id = 43 AND value_id = '".$query->row['value_id']."'");
				$i++;				
			}
			if ($i == 0) break;
		}

	}
}
		