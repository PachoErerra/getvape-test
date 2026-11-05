<?php
class ModelLocalisationLocation extends Model {
	// Классические 4 онлайн-склада (сброс / дефолт)
	const DEFAULT_ONLINE_GUIDS = array(
		'ef889057-fba5-11ed-aac0-000c29a71cec', // Офис GetVape
		'd1550f2c-871d-11ef-a4b0-000c298c303a', // Бандери
		'e3dab2d7-d4fe-11ec-aa96-000c292a686a', // Торгова
		'e3dab2d6-d4fe-11ec-aa96-000c292a686a', // Чорновола
	);

	const DEFAULT_ONLINE_IDS = array(230, 77, 2, 1);

	public function addLocation($data) {
		$this->ensureOnlineOrderColumn();

		// online_order ставится только в списке (тумблер / массово / seed)
		$this->db->query("INSERT INTO " . DB_PREFIX . "location SET name = '" . $this->db->escape($data['name']) . "', city = '" . $this->db->escape($data['city']) . "', address = '" . $this->db->escape($data['address']) . "', geocode = '" . $this->db->escape($data['geocode']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', image = '" . $this->db->escape($data['image']) . "', open = '" . $this->db->escape($data['open']) . "', openBF = '" . $this->db->escape($data['openBF']) . "', comment = '" . $this->db->escape($data['comment']) . "', online_order = 0");

		return $this->db->getLastId();
	}

	public function editLocation($location_id, $data) {
		$this->ensureOnlineOrderColumn();

		// online_order не трогаем из формы редактирования
		$this->db->query("UPDATE " . DB_PREFIX . "location SET name = '" . $this->db->escape($data['name']) . "', city = '" . $this->db->escape($data['city']) . "', address = '" . $this->db->escape($data['address']) . "', geocode = '" . $this->db->escape($data['geocode']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', image = '" . $this->db->escape($data['image']) . "', open = '" . $this->db->escape($data['open']) . "', openBF = '" . $this->db->escape($data['openBF']) . "', comment = '" . $this->db->escape($data['comment']) . "' WHERE location_id = '" . (int)$location_id . "'");
	}

	public function deleteLocation($location_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "location WHERE location_id = " . (int)$location_id);
	}

	public function getLocation($location_id) {
		$this->ensureOnlineOrderColumn();

		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "location WHERE location_id = '" . (int)$location_id . "'");

		return $query->row;
	}

	public function getLocations($data = array()) {
		$this->ensureOnlineOrderColumn();

		$sql = "SELECT location_id, name, guid, address, online_order FROM " . DB_PREFIX . "location";

		$sort_data = array(
			'name',
			'address',
			'online_order',
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY name";
		}

		if (isset($data['order']) && ($data['order'] == 'DESC')) {
			$sql .= " DESC";
		} else {
			$sql .= " ASC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			if ($data['start'] < 0) {
				$data['start'] = 0;
			}

			if ($data['limit'] < 1) {
				$data['limit'] = 20;
			}

			$sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalLocations() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "location");

		return $query->row['total'];
	}

	public function setOnlineOrder($location_id, $online_order) {
		$this->ensureOnlineOrderColumn();

		$this->db->query("UPDATE " . DB_PREFIX . "location SET online_order = '" . (int)!empty($online_order) . "' WHERE location_id = '" . (int)$location_id . "'");
	}

	public function ensureOnlineOrderColumn() {
		static $checked = false;

		if ($checked) {
			return;
		}

		$checked = true;

		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "location` LIKE 'online_order'");

		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "location` ADD COLUMN `online_order` TINYINT(1) NOT NULL DEFAULT 0 AFTER `guid`, ADD INDEX `idx_location_online_order` (`online_order`)");
			$this->resetToDefaultOnlineWarehouses();
		}
	}

	/**
	 * Выключить все, оставить онлайн только классические 4 склада.
	 */
	public function resetToDefaultOnlineWarehouses() {
		$this->ensureOnlineOrderColumn();

		$this->db->query("UPDATE " . DB_PREFIX . "location SET online_order = 0");

		$escaped = array();

		foreach (self::DEFAULT_ONLINE_GUIDS as $guid) {
			$escaped[] = "'" . $this->db->escape($guid) . "'";
		}

		$this->db->query("UPDATE " . DB_PREFIX . "location SET online_order = 1 WHERE guid IN (" . implode(',', $escaped) . ") OR location_id IN (" . implode(',', array_map('intval', self::DEFAULT_ONLINE_IDS)) . ")");

		// Винница никогда не в онлайн
		$this->db->query("UPDATE " . DB_PREFIX . "location SET online_order = 0 WHERE
			LOWER(CONCAT(IFNULL(city,''), ' ', IFNULL(name,''), ' ', IFNULL(address,''))) LIKE '%вінниц%'
			OR LOWER(CONCAT(IFNULL(city,''), ' ', IFNULL(name,''), ' ', IFNULL(address,''))) LIKE '%винниц%'
			OR LOWER(CONCAT(IFNULL(city,''), ' ', IFNULL(name,''), ' ', IFNULL(address,''))) LIKE '%vinnyts%'
			OR LOWER(CONCAT(IFNULL(city,''), ' ', IFNULL(name,''), ' ', IFNULL(address,''))) LIKE '%vinnits%'
		");

		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "location WHERE online_order = 1");

		return (int)$query->row['total'];
	}
}
