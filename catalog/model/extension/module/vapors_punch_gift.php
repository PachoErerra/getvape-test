<?php

class ModelExtensionModuleVaporsPunchGift extends Model
{
	const ATTR_MODEL = 37;
	const ATTR_VOLUME = 41;
	const ATTR_TASTE = 40;
	const MANUFACTURER_PUNCH_LAB = 58;
	const MODEL_NAME = 'Vapors X Punch';

	public function isActive()
	{
		return (bool)$this->config->get('config_vapors_punch_gift');
	}

	public function isTriggerProduct($product_id)
	{
		$product_id = (int)$product_id;

		if (!$product_id) {
			return false;
		}

		$query = $this->db->query("SELECT
			(SELECT text FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . $product_id . "' AND attribute_id = '" . (int)self::ATTR_MODEL . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1) AS model,
			(SELECT text FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . $product_id . "' AND attribute_id = '" . (int)self::ATTR_VOLUME . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1) AS volume
		");

		if (!$query->num_rows) {
			return false;
		}

		$model = trim((string)$query->row['model']);
		$volume = strtolower(str_replace(' ', '', (string)$query->row['volume']));

		return ($model === self::MODEL_NAME) && (strpos($volume, '30ml') !== false);
	}

	public function isGiftProduct($product_id)
	{
		$product_id = (int)$product_id;

		if (!$product_id) {
			return false;
		}

		$query = $this->db->query("SELECT p.manufacturer_id,
			(SELECT text FROM " . DB_PREFIX . "product_attribute WHERE product_id = p.product_id AND attribute_id = '" . (int)self::ATTR_MODEL . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1) AS model,
			(SELECT text FROM " . DB_PREFIX . "product_attribute WHERE product_id = p.product_id AND attribute_id = '" . (int)self::ATTR_VOLUME . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1) AS volume
			FROM " . DB_PREFIX . "product p
			WHERE p.product_id = '" . $product_id . "' AND p.status = '1' LIMIT 1");

		if (!$query->num_rows) {
			return false;
		}

		$model = trim((string)$query->row['model']);
		$volume = strtolower(str_replace(' ', '', (string)$query->row['volume']));

		return ((int)$query->row['manufacturer_id'] === self::MANUFACTURER_PUNCH_LAB)
			&& ($model === self::MODEL_NAME)
			&& (strpos($volume, '10ml') !== false);
	}

	public function getTriggerQty()
	{
		$qty = 0;

		foreach ($this->getTriggerLines() as $line) {
			$qty += (int)$line['quantity'];
		}

		return $qty;
	}

	public function getTriggerLines()
	{
		$gift_cart_ids = $this->getGiftCartIds();
		$lines = array();

		foreach ($this->cart->getProducts() as $product) {
			if (in_array((int)$product['cart_id'], $gift_cart_ids, true)) {
				continue;
			}

			if ($this->isTriggerProduct($product['product_id'])) {
				$lines[] = array(
					'cart_id' => (int)$product['cart_id'],
					'product_id' => (int)$product['product_id'],
					'quantity' => (int)$product['quantity']
				);
			}
		}

		return $lines;
	}

	public function getGiftProducts()
	{
		$this->load->model('tool/image');

		$width = (int)$this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width');
		$height = (int)$this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height');

		if (!$width) {
			$width = 47;
		}

		if (!$height) {
			$height = 47;
		}

		$query = $this->db->query("SELECT p.product_id, p.image, pd.name,
			(SELECT text FROM " . DB_PREFIX . "product_attribute WHERE product_id = p.product_id AND attribute_id = '" . (int)self::ATTR_TASTE . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1) AS taste
			FROM " . DB_PREFIX . "product p
			INNER JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')
			INNER JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "')
			INNER JOIN " . DB_PREFIX . "product_attribute model ON (model.product_id = p.product_id AND model.attribute_id = '" . (int)self::ATTR_MODEL . "' AND model.language_id = '" . (int)$this->config->get('config_language_id') . "' AND model.text = '" . $this->db->escape(self::MODEL_NAME) . "')
			INNER JOIN " . DB_PREFIX . "product_attribute volume ON (volume.product_id = p.product_id AND volume.attribute_id = '" . (int)self::ATTR_VOLUME . "' AND volume.language_id = '" . (int)$this->config->get('config_language_id') . "' AND REPLACE(LOWER(volume.text), ' ', '') LIKE '%10ml%')
			WHERE p.manufacturer_id = '" . (int)self::MANUFACTURER_PUNCH_LAB . "'
				AND p.status = '1'
				AND p.date_available <= NOW()
				AND p.quantity > 0
			ORDER BY pd.name ASC");

		$products = array();

		foreach ($query->rows as $row) {
			if ($row['image']) {
				$thumb = $this->model_tool_image->resize($row['image'], $width, $height);
			} else {
				$thumb = $this->model_tool_image->resize('placeholder.png', $width, $height);
			}

			$products[] = array(
				'product_id' => (int)$row['product_id'],
				'name' => $row['name'],
				'label' => $this->getFlavorLabel($row['name'], $row['taste']),
				'thumb' => $thumb,
				'image' => $row['image']
			);
		}

		return $products;
	}

	public function getFlavorLabel($name, $taste = '')
	{
		if (preg_match('/\(([^)]+)\)/', (string)$name, $matches)) {
			return trim($matches[1]);
		}

		$taste = trim((string)$taste);

		if ($taste !== '') {
			return $taste;
		}

		return (string)$name;
	}

	public function getSlots()
	{
		if (!isset($this->session->data['vapors_punch_slots']) || !is_array($this->session->data['vapors_punch_slots'])) {
			return array();
		}

		return $this->session->data['vapors_punch_slots'];
	}

	public function getGiftCartIds()
	{
		if (empty($this->session->data['vapors_punch_gift']) || !is_array($this->session->data['vapors_punch_gift'])) {
			return array();
		}

		return array_map('intval', $this->session->data['vapors_punch_gift']);
	}

	public function sync()
	{
		if (!$this->isActive()) {
			$this->clearAll();
			return array();
		}

		$old_slots = $this->getSlots();
		$gift_cart_ids_in_cart = array();

		foreach ($this->cart->getProducts() as $product) {
			$gift_cart_ids_in_cart[(int)$product['cart_id']] = true;
		}

		$by_parent = array();

		foreach ($old_slots as $slot) {
			$parent_cart_id = !empty($slot['parent_cart_id']) ? (int)$slot['parent_cart_id'] : 0;
			$gift_cart_id = !empty($slot['cart_id']) ? (int)$slot['cart_id'] : 0;

			if ($gift_cart_id && !isset($gift_cart_ids_in_cart[$gift_cart_id])) {
				$slot['cart_id'] = null;
				$slot['product_id'] = null;
				$gift_cart_id = 0;
			}

			if (!$parent_cart_id) {
				if ($gift_cart_id) {
					$this->cart->remove($gift_cart_id);
				}
				continue;
			}

			if (!isset($by_parent[$parent_cart_id])) {
				$by_parent[$parent_cart_id] = array();
			}

			$by_parent[$parent_cart_id][] = array(
				'parent_cart_id' => $parent_cart_id,
				'product_id' => !empty($slot['product_id']) ? (int)$slot['product_id'] : null,
				'cart_id' => $gift_cart_id ? $gift_cart_id : null
			);
		}

		$new_slots = array();

		foreach ($this->getTriggerLines() as $line) {
			$parent_cart_id = (int)$line['cart_id'];
			$needed = (int)$line['quantity'];
			$existing = isset($by_parent[$parent_cart_id]) ? $by_parent[$parent_cart_id] : array();

			while (count($existing) > $needed) {
				$slot = array_pop($existing);

				if (!empty($slot['cart_id'])) {
					$this->cart->remove((int)$slot['cart_id']);
				}
			}

			while (count($existing) < $needed) {
				$existing[] = array(
					'parent_cart_id' => $parent_cart_id,
					'product_id' => null,
					'cart_id' => null
				);
			}

			foreach ($existing as $slot) {
				$slot['parent_cart_id'] = $parent_cart_id;
				$new_slots[] = $slot;
			}

			unset($by_parent[$parent_cart_id]);
		}

		foreach ($by_parent as $orphans) {
			foreach ($orphans as $slot) {
				if (!empty($slot['cart_id'])) {
					$this->cart->remove((int)$slot['cart_id']);
				}
			}
		}

		$this->session->data['vapors_punch_slots'] = array_values($new_slots);
		$this->rebuildGiftCartIds();

		return $this->session->data['vapors_punch_slots'];
	}

	public function selectGift($slot_index, $product_id)
	{
		if (!$this->isActive()) {
			return false;
		}

		$slot_index = (int)$slot_index;
		$product_id = (int)$product_id;

		$this->sync();

		$slots = $this->getSlots();

		if (!isset($slots[$slot_index]) || !$this->isGiftProduct($product_id)) {
			return false;
		}

		if (!empty($slots[$slot_index]['cart_id'])) {
			$this->cart->remove((int)$slots[$slot_index]['cart_id']);
		}

		$option = array('vapors_punch_gift' => (string)$slot_index);
		$cart_id = $this->cart->add($product_id, 1, $option);

		if (!$cart_id) {
			$cart_id = $this->findCartId($product_id, $option);
		}

		$slots[$slot_index] = array(
			'parent_cart_id' => !empty($slots[$slot_index]['parent_cart_id']) ? (int)$slots[$slot_index]['parent_cart_id'] : null,
			'product_id' => $product_id,
			'cart_id' => $cart_id ? (int)$cart_id : null
		);

		$this->session->data['vapors_punch_slots'] = $slots;
		$this->rebuildGiftCartIds();

		return true;
	}

	public function clearSlot($slot_index)
	{
		if (!$this->isActive()) {
			return false;
		}

		$slot_index = (int)$slot_index;
		$slots = $this->getSlots();

		if (!isset($slots[$slot_index])) {
			return false;
		}

		if (!empty($slots[$slot_index]['cart_id'])) {
			$this->cart->remove((int)$slots[$slot_index]['cart_id']);
		}

		$slots[$slot_index] = array(
			'parent_cart_id' => !empty($slots[$slot_index]['parent_cart_id']) ? (int)$slots[$slot_index]['parent_cart_id'] : null,
			'product_id' => null,
			'cart_id' => null
		);

		$this->session->data['vapors_punch_slots'] = $slots;
		$this->rebuildGiftCartIds();

		return true;
	}

	public function validate()
	{
		if (!$this->isActive()) {
			return true;
		}

		$slots = $this->sync();

		if (!$slots) {
			return true;
		}

		foreach ($slots as $slot) {
			if (empty($slot['product_id']) || empty($slot['cart_id'])) {
				return false;
			}
		}

		return true;
	}

	public function getCartGiftRows($invalid = false)
	{
		if (!$this->isActive()) {
			return array();
		}

		$slots = $this->sync();
		$gift_products = $this->getGiftProducts();
		$gift_map = array();

		foreach ($gift_products as $gift) {
			$gift_map[$gift['product_id']] = $gift;
		}

		$placeholder = 'catalog/view/theme/default/image/vapors-punch-gift.png';
		$rows = array();

		foreach ($slots as $index => $slot) {
			$selected = null;

			if (!empty($slot['product_id']) && isset($gift_map[(int)$slot['product_id']])) {
				$selected = $gift_map[(int)$slot['product_id']];
			} elseif (!empty($slot['product_id'])) {
				$selected = $this->buildGiftProductRow((int)$slot['product_id']);
			}

			$rows[] = array(
				'slot_index' => (int)$index,
				'parent_cart_id' => !empty($slot['parent_cart_id']) ? (int)$slot['parent_cart_id'] : null,
				'cart_id' => !empty($slot['cart_id']) ? (int)$slot['cart_id'] : null,
				'product_id' => !empty($slot['product_id']) ? (int)$slot['product_id'] : null,
				'selected' => $selected,
				'options' => $gift_products,
				'thumb' => $selected ? $selected['thumb'] : $placeholder,
				'name' => $selected ? $selected['name'] : 'Ароматизатор Punch 10ml',
				'invalid' => $invalid && empty($slot['product_id'])
			);
		}

		return $rows;
	}

	public function groupGiftRowsByParent($rows)
	{
		$grouped = array();

		foreach ($rows as $row) {
			$parent_cart_id = !empty($row['parent_cart_id']) ? (int)$row['parent_cart_id'] : 0;

			if (!$parent_cart_id) {
				continue;
			}

			if (!isset($grouped[$parent_cart_id])) {
				$grouped[$parent_cart_id] = array();
			}

			$grouped[$parent_cart_id][] = $row;
		}

		return $grouped;
	}

	public function clearAll()
	{
		$slots = $this->getSlots();

		foreach ($slots as $slot) {
			if (!empty($slot['cart_id'])) {
				$this->cart->remove((int)$slot['cart_id']);
			}
		}

		unset($this->session->data['vapors_punch_slots']);
		unset($this->session->data['vapors_punch_gift']);
	}

	private function rebuildGiftCartIds()
	{
		$ids = array();

		foreach ($this->getSlots() as $slot) {
			if (!empty($slot['cart_id'])) {
				$ids[] = (int)$slot['cart_id'];
			}
		}

		$this->session->data['vapors_punch_gift'] = $ids;
	}

	private function findCartId($product_id, $option)
	{
		$query = $this->db->query("SELECT cart_id FROM " . DB_PREFIX . "cart WHERE api_id = '" . (isset($this->session->data['api_id']) ? (int)$this->session->data['api_id'] : 0) . "' AND customer_id = '" . (int)$this->customer->getId() . "' AND session_id = '" . $this->db->escape($this->session->getId()) . "' AND product_id = '" . (int)$product_id . "' AND `option` = '" . $this->db->escape(json_encode($option)) . "' ORDER BY cart_id DESC LIMIT 1");

		return $query->num_rows ? (int)$query->row['cart_id'] : null;
	}

	private function buildGiftProductRow($product_id)
	{
		$this->load->model('tool/image');
		$this->load->model('catalog/product');

		$product = $this->model_catalog_product->getProduct($product_id);

		if (!$product) {
			return null;
		}

		$width = (int)$this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width');
		$height = (int)$this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height');

		if (!$width) {
			$width = 47;
		}

		if (!$height) {
			$height = 47;
		}

		if ($product['image']) {
			$thumb = $this->model_tool_image->resize($product['image'], $width, $height);
		} else {
			$thumb = $this->model_tool_image->resize('placeholder.png', $width, $height);
		}

		$taste_query = $this->db->query("SELECT text FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)self::ATTR_TASTE . "' AND language_id = '" . (int)$this->config->get('config_language_id') . "' LIMIT 1");
		$taste = $taste_query->num_rows ? $taste_query->row['text'] : '';

		return array(
			'product_id' => (int)$product_id,
			'name' => $product['name'],
			'label' => $this->getFlavorLabel($product['name'], $taste),
			'thumb' => $thumb,
			'image' => $product['image']
		);
	}
}
