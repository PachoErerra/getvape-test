<?php
class ModelCatalogPromo extends Model {
	public function addPromo($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "promo SET status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', noindex = '" . (int)$data['noindex'] . "', date_added = NOW(), date_modified = NOW()");

		$promo_id = $this->db->getLastId();

		foreach ($data['promo_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "promo_description SET promo_id = '" . (int)$promo_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'promo_id=" . (int)$promo_id . "'");

		if (isset($this->request->post['promo_seo_url'])) {
			foreach ($this->request->post['promo_seo_url'] as $store_id => $languages) {
				foreach ($languages as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'promo_id=" . (int)$promo_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}

		$this->updatePromoLinks($promo_id, $data);
        if ($this->config->get('config_seo_pro')) {
            $this->cache->delete('seopro*');
        }
		return $promo_id;
	}

	public function editPromo($promo_id, $data) {
		$this->db->query("UPDATE " . DB_PREFIX . "promo SET status = '" . (int)$data['status'] . "', sort_order = '" . (int)$data['sort_order'] . "', noindex = '" . (int)$data['noindex'] . "', date_modified = NOW() WHERE promo_id = '" . (int)$promo_id . "'");

		$this->db->query("DELETE FROM " . DB_PREFIX . "promo_description WHERE promo_id = '" . (int)$promo_id . "'");

		foreach ($data['promo_description'] as $language_id => $value) {
			$this->db->query("INSERT INTO " . DB_PREFIX . "promo_description SET promo_id = '" . (int)$promo_id . "', language_id = '" . (int)$language_id . "', title = '" . $this->db->escape($value['title']) . "', description = '" . $this->db->escape($value['description']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
		}
		$this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'promo_id=" . (int)$promo_id . "'");

		if (isset($this->request->post['promo_seo_url'])) {
			foreach ($this->request->post['promo_seo_url'] as $store_id => $languages) {
				foreach ($languages as $language_id => $keyword) {
					if (!empty($keyword)) {
						$this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'promo_id=" . (int)$promo_id . "', keyword = '" . $this->db->escape($keyword) . "'");
					}
				}
			}
		}
		$this->db->query("DELETE FROM " . DB_PREFIX . "promo_product WHERE promo_id = '" . (int)$promo_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "promo_category WHERE promo_id = '" . (int)$promo_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "promo_manufacturer WHERE promo_id = '" . (int)$promo_id . "'");
        if ($this->config->get('config_seo_pro')) {
            $this->cache->delete('seopro*');
        }
		$this->updatePromoLinks($promo_id, $data);
		

	}

	public function deletePromo($promo_id) {
		$this->db->query("DELETE FROM " . DB_PREFIX . "promo WHERE promo_id = '" . (int)$promo_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "promo_description WHERE promo_id = '" . (int)$promo_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "promo_product WHERE promo_id = '" . (int)$promo_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "promo_category WHERE promo_id = '" . (int)$promo_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "promo_manufacturer WHERE promo_id = '" . (int)$promo_id . "'");
	}

	private function updatePromoLinks($promo_id, $data) {
		if (isset($data['promo_product'])) {
			foreach ($data['promo_product'] as $product) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "promo_product SET promo_id = '" . (int)$promo_id . "', product_id = '" . (int)$product['product_id'] . "', discount = '" . (int)$product['discount'] . "'");
			}
		}
		if (isset($data['promo_category'])) {
			foreach ($data['promo_category'] as $category) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "promo_category SET promo_id = '" . (int)$promo_id . "', category_id = '" . (int)$category['category_id'] . "', discount = '" . (int)$category['discount'] . "'");
			}
		}
		if (isset($data['promo_manufacturer'])) {
			foreach ($data['promo_manufacturer'] as $manufacturer) {
				$this->db->query("INSERT INTO " . DB_PREFIX . "promo_manufacturer SET promo_id = '" . (int)$promo_id . "', manufacturer_id = '" . (int)$manufacturer['manufacturer_id'] . "', discount = '" . (int)$manufacturer['discount'] . "'");
			}
		}
	}

	public function getPromo($promo_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "promo p LEFT JOIN " . DB_PREFIX . "promo_description pd ON (p.promo_id = pd.promo_id) WHERE p.promo_id = '" . (int)$promo_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");
		return $query->row;
	}

	public function getPromos($data = array()) {
		$sql = "SELECT * FROM " . DB_PREFIX . "promo p LEFT JOIN " . DB_PREFIX . "promo_description pd ON (p.promo_id = pd.promo_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		$sort_data = array(
			'pd.title',
			'p.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY pd.title";
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

	public function getPromoDescriptions($promo_id) {
		$promo_description_data = array();

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "promo_description WHERE promo_id = '" . (int)$promo_id . "'");

		foreach ($query->rows as $result) {
			$promo_description_data[$result['language_id']] = array(
				'title'            => $result['title'],
				'description'      => $result['description'],
				'meta_title'       => $result['meta_title'],
				'meta_description' => $result['meta_description'],
				'meta_keyword'     => $result['meta_keyword']
			);
		}

		return $promo_description_data;
	}

	public function getTotalPromos() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "promo");
		return $query->row['total'];
	}

	public function getPromoCategories($promo_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "promo_category WHERE promo_id = '" . (int)$promo_id . "'");
		return $query->rows;
	}

	public function getPromoManufacturers($promo_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "promo_manufacturer WHERE promo_id = '" . (int)$promo_id . "'");
		return $query->rows;
	}

	public function getPromoProducts($promo_id) {
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "promo_product WHERE promo_id = '" . (int)$promo_id . "'");
		return $query->rows;
	}
}