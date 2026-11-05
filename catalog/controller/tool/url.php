<?php
class ControllerToolUrl extends Controller {
	function index(){

		$result = $this->db->query("SELECT keyword FROM ".DB_PREFIX."seo_url GROUP BY keyword HAVING count(*)>1;");

		foreach ($result->rows as $result) {
			echo $result['keyword'].'<br>';
		}


	//	print_r($result->rows);
		
//		$res = $this->db->query("SELECT query FROM ".DB_PREFIX."url_alias WHERE query LIKE 'product_id=%'")->rows
		
/*
		foreach($this->db->query("SELECT url_alias_id, query FROM ".DB_PREFIX."url_alias WHERE query LIKE 'product_id=%'")->rows as $product){
			list($type, $product_id) = explode('=', $product['query']);

			if(!in_array($product_id, $allow)){
				$this->db->query("DELETE FROM ".DB_PREFIX."url_alias WHERE query = 'product_id=".$product_id."'");
			}
		}
*/
	}
}