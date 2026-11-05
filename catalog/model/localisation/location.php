<?php
class ModelLocalisationLocation extends Model {
	public function getLocation($location_id) {
		$query = $this->db->query("SELECT location_id, name, address, geocode, telephone, fax, image, open, comment FROM " . DB_PREFIX . "location WHERE location_id = '" . (int)$location_id . "'");

		return $query->row;
	}
	public function getWarehouses() {
		$warehouse_data = array();

			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "location ORDER BY city ASC");

			$warehouse_data['count'] = count($query->rows);
			foreach ($query->rows as $result) {
				$warehouse_data[$result['city']][] = $result;
			}


		return $warehouse_data;
	}
}