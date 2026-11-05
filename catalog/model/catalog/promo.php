<?php
class ModelCatalogPromo extends Model {
	public function getPromo($promo_id) {
		$query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "promo p LEFT JOIN " . DB_PREFIX . "promo_description pd ON (p.promo_id = pd.promo_id) WHERE p.promo_id = '" . (int)$promo_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1'");
		return $query->row;
	}

	// Получает все ID товаров акции для базового SQL запроса в getProducts
	public function getPromoProductIds($promo_id) {
		// Собираем ID товаров, проверяя, что они включены (status = 1) и есть в наличии (quantity > 0)
		$sql = "
			SELECT p.product_id FROM " . DB_PREFIX . "promo_product pp LEFT JOIN " . DB_PREFIX . "product p ON (pp.product_id = p.product_id) WHERE pp.promo_id = '" . (int)$promo_id . "' AND p.quantity > 0 AND p.status = '1'
			UNION
			SELECT p2c.product_id FROM " . DB_PREFIX . "promo_category pc LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (pc.category_id = p2c.category_id) LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id) WHERE pc.promo_id = '" . (int)$promo_id . "' AND p.quantity > 0 AND p.status = '1'
			UNION
			SELECT p.product_id FROM " . DB_PREFIX . "promo_manufacturer pm LEFT JOIN " . DB_PREFIX . "product p ON (pm.manufacturer_id = p.manufacturer_id) WHERE pm.promo_id = '" . (int)$promo_id . "' AND p.quantity > 0 AND p.status = '1'
		";

		$query = $this->db->query($sql);
		
		$product_ids = array();
		foreach ($query->rows as $row) {
			if (!empty($row['product_id'])) {
				$product_ids[] = (int)$row['product_id'];
			}
		}

		return $product_ids;
	}

	// Эффективно высчитывает максимальную скидку только для товаров на текущей странице пагинации
	public function getProductsDiscounts($promo_id, $product_ids) {
		if (!$product_ids) return array();

		$imploded_ids = implode(',', array_map('intval', $product_ids));

		$sql = "
			SELECT product_id, MAX(discount) as discount FROM (
				SELECT product_id, discount FROM " . DB_PREFIX . "promo_product WHERE promo_id = '" . (int)$promo_id . "' AND product_id IN (" . $imploded_ids . ")
				UNION ALL
				SELECT p2c.product_id, pc.discount FROM " . DB_PREFIX . "promo_category pc JOIN " . DB_PREFIX . "product_to_category p2c ON pc.category_id = p2c.category_id WHERE pc.promo_id = '" . (int)$promo_id . "' AND p2c.product_id IN (" . $imploded_ids . ")
				UNION ALL
				SELECT p.product_id, pm.discount FROM " . DB_PREFIX . "promo_manufacturer pm JOIN " . DB_PREFIX . "product p ON pm.manufacturer_id = p.manufacturer_id WHERE pm.promo_id = '" . (int)$promo_id . "' AND p.product_id IN (" . $imploded_ids . ")
			) AS t GROUP BY product_id
		";

		$query = $this->db->query($sql);

		$discounts = array();
		foreach ($query->rows as $row) {
			$discounts[$row['product_id']] = (float)$row['discount'];
		}

		return $discounts;
	}
}