<?php

/**
 * @category   OpenCart
 * @package    SEO Tags Generator
 * @copyright  © Serge Tkach, 2017-2022, https://sergetkach.com/
 */

class ModelExtensionModuleSeoTagsGenerator extends Controller {


	/* Common Helpers for admin & catalog
	--------------------------------------------------------------------------- */

	public function getSTGSettingsByCatId($category_id) {
		if (!$category_id) return false;

		// Запрашиваем формулы текущей категории
		$sql = "SELECT `setting` FROM `" . DB_PREFIX . "seo_tags_generator_category_setting` WHERE `category_id` = '" . (int)$category_id . "'";

		$result = $this->db->query($sql);

		if ($result) {
			if (isset($result->row['setting']) && !empty($result->row['setting'])) {
				// Для текущей категории, у которой заданы формулы просто возращаем эти формулы
				return json_decode($result->row['setting'], true);
			}
		}

		// Если настроек для текущей категории нет, то скрипт продолжается
		$limit = 5; // Лимит, на случай, если не будет настроек и для родителей
		$i		 = 1;

		$stop = false;

		$previous_category = $category_id;

		while ($limit >= $i && !$stop) {
			// Проверяем, есть ли у данной категории родитель
			$follow_category_id = $this->getParentCategoryByCategoryId($previous_category);

			if ($follow_category_id == $previous_category) {
				$stop = true;
			}

			if (!$stop) {
				// Родитель есть, запрашиваем его настройки
				$sql = "SELECT `setting` FROM `" . DB_PREFIX . "seo_tags_generator_category_setting` WHERE `category_id` = '" . (int)$follow_category_id . "'";

				$result = $this->db->query($sql);

				if ($result) {
					if (isset($result->row['setting']) && !empty($result->row['setting'])) {
						$stop = true;
						// Возращаем раннее полученные формулы
						return json_decode($result->row['setting'], true);
					}
				}
			}

			$previous_category = $follow_category_id;
			$i++;
		}

		return false;
	}

	public function getSTGFormulasByCatId($category_id, $language_id, $key) {
		// Сначала нам нужно понять, есть ли у этой категории свои формулы
		// Если формулы есть - выдаем их
		// Если формул нет нет - то нам теперь нужно отталкиваться настройки наследования
		// Настройка наследования есть:
		//  - глобальная в модулей
		//  - локальная в отдельной взятой категории
		// В любом случае, перед тем, как понять, есть ли формулы и настройки нам все равно нужно выяснить крайнего родителя...
		// Если у категории ( крайнего родителя ) нет прописанных формул, значит настройка наследования для нее не применяются - не будем же мы наследовать пустоту...
		// Отталкиваемся от глобальной формулы
		// Если у категории ( крайнего родителя ) есть формулы, то отталкиваемся от настройки наследования данной формулы
		// Запрашиваем первого родителя
		// Если родитель есть, но формул для него нет, запрашиваем прародителя и т.д.

		if (!$category_id)
			return false;
		if (!$language_id)
			return false;
		if (!$key)
			return false;

		// Запрашиваем формулы текущей категории
		$sql = "SELECT `value` FROM `" . DB_PREFIX . "seo_tags_generator` WHERE `category_id`=" . (int)$category_id . " AND `language_id`='" . (int)$language_id . "' AND `key`='" . $this->db->escape($key) . "';";

		$result = $this->db->query($sql);

		if ($result) {
			if (isset($result->row['value']) && !empty($result->row['value'])) {
				// Для текущей категории, у которой заданы формулы просто возращаем эти формулы
				return json_decode($result->row['value'], true);
			}
		}

		// Если формулы для текущей категории нет, то скрипт продолжается

		$limit = 5; // Лимит, на случай, если не будет настроек и для родителей
		$i		 = 1;

		$stop = false;

		$previous_category = $category_id;

		while ($limit >= $i && !$stop) {
			// Проверяем, есть ли у данной категории родитель
			$follow_category_id = $this->getParentCategoryByCategoryId($previous_category);

			if ($follow_category_id == $previous_category) {
				$stop = true;
			}

			if (!$stop) {
				// Родитель есть, запрашиваем его формулы
				$sql = "SELECT `value` FROM `" . DB_PREFIX . "seo_tags_generator` WHERE `category_id`=" . (int)$follow_category_id . " AND `language_id`='" . (int)$language_id . "' AND `key`='" . $this->db->escape($key) . "';";

				$result = $this->db->query($sql);

				if ($result) {
					if (isset($result->row['value']) && !empty($result->row['value'])) {
						// Хотя все равно выполнение прекратится после возрата...
						$stop = true;

						// Когда формула для текущей категории есть, то нас волнует настройка наследования
						$sql = "SELECT `setting` FROM `" . DB_PREFIX . "seo_tags_generator_category_setting` WHERE `category_id` = '" . (int)$follow_category_id . "'";
						$query2	= $this->db->query($sql);

						if ($query2->row) {
							$setting = json_decode($query2->row['setting'], true);
							if ($setting['inheritance']) {
								// Возращаем раннее полученные формулы
								return json_decode($result->row['value'], true);
							}
						}
					}
				}
			}

			$previous_category = $follow_category_id;
			$i++;
		}

		return false;
	}


	public function getProductSales($product_id) {
		$sql = "SELECT COUNT(*) AS number FROM " . DB_PREFIX . "order_product WHERE product_id = '" . (int)$product_id . "' ";

		$query = $this->db->query($sql);

		return $query->row['number'];
	}


	/*
	 * Внимание!
	 * Бывает, что в магазине есть цена 0 - она не должна учитываться!
	 * 
	 * (AA!)
	 * MySQL-функция MIN() не подходит, так как я получаю вместе с максимальной ценой и product_id (дополнительный столбец!)
	 * 
	 * Также иметь ввиду, что иногда через Скидки (discount) реализуется разная цена для разных городов
	 * 
	 * *Примечание
	 * (А!) Ищем товары данной категории и всех ее дочерних категорий!
	 * 
	 * ++++++
	 * Тут мы ищем минимум возможной цены - все достаточно просто
	 */
	public function getMinPriceInCat($category_id) {	
		$res = false;
		
		$a_min_price = [
			'product_id' => false,
			'price' => false
		];

		// Самая дешевая базовая цена
		$sql = "SELECT"
			. " p.price,"
			. " p.product_id AS product_id,"
			. " p.tax_class_id"
			. " FROM " . DB_PREFIX . "product p"
			. " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)"
			. " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)"
			. " WHERE p2c.category_id IN (SELECT cp.category_id FROM " . DB_PREFIX . "category_path cp WHERE path_id = '" . (int)$category_id . "')"
			. " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'"
			. " AND p.date_available <= NOW()"
			. " AND p.status = '1' AND p.price > 0" // чтобы не отображать товары с ценой 0, как делают магазины, которые наполняются...
			. " ORDER BY p.price ASC"
			. " LIMIT 1";
		
		$query = $this->db->query($sql);		
	
		if ($query->num_rows > 0) {
			$res = $query->row['price'];

			$a_min_price['product_id'] = $query->row['product_id'];
			$a_min_price['price'] = $query->row['price'];

			$tax_class_id	= $query->row['tax_class_id'];
		} else {
			// all prices are 0...
		}				

		// Самая дешевая цена discount
		$sql = "SELECT"
			. " pd.price AS discount,"
			. " p.product_id AS product_id,"
			. " p.tax_class_id"
			. " FROM " . DB_PREFIX . "product_discount pd"
			. " LEFT JOIN " . DB_PREFIX . "product p ON (pd.product_id = p.product_id)"
			. " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (pd.product_id = p2c.product_id)"
			. " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (pd.product_id = p2s.product_id)"
			. " WHERE p2c.category_id IN (SELECT cp.category_id FROM " . DB_PREFIX . "category_path cp WHERE path_id = '" . (int)$category_id . "')"
			. " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'"
			. " AND pd.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((pd.date_start = '0000-00-00' OR pd.date_start < NOW()) AND (pd.date_end = '0000-00-00' OR pd.date_end > NOW()))"
			. " AND pd.quantity = '1'" // It is necessary for discount
			. " AND p.status = '1' AND pd.price > '0'" // чтобы отключенные товары не влияли на определение цены
			. " ORDER BY pd.priority ASC, pd.price ASC"
			. " LIMIT 1";

		$query = $this->db->query($sql);

		// Discount - замещает базовую цену по стандарту OpenCart
		if ($query->num_rows > 0) {
			if ((float)$query->row['discount'] < (float)$a_min_price['price']) {
				$res = $query->row['discount'];

				$a_min_price['product_id'] = $query->row['product_id'];
				$a_min_price['price'] = $query->row['discount'];

				$tax_class_id	= $query->row['tax_class_id'];
			}
		}

		// Самая дешевая цена special
		$sql = "SELECT"
			. " ps.price AS special,"
			. " p.product_id AS product_id,"
			. " p.tax_class_id"
			. " FROM " . DB_PREFIX . "product_special ps"
			. " LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id)"
			. " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (ps.product_id = p2c.product_id)"
			. " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (ps.product_id = p2s.product_id)"
			. " WHERE p2c.category_id IN (SELECT cp.category_id FROM " . DB_PREFIX . "category_path cp WHERE path_id = '" . (int)$category_id . "')"
			. " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'"
			. " AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))"
			. " AND p.status = '1' AND ps.price > '0'" // чтобы отключенные товары не влияли на определение цены
			. " ORDER BY ps.priority ASC, ps.price ASC"
			. " LIMIT 1";

		$query = $this->db->query($sql);

		if ($query->num_rows > 0) {
			// Сравнимаем минимальную цену special с минимальной ценой (price:discount)
			if ($query->row['special'] < $a_min_price['price']) {
				$res = $query->row['special'];

				// Если это разные товары, то нам нужен tax_class_id более дешевого товара со скидкой
				if ($query->row['product_id'] != $a_min_price['product_id']) {
					$tax_class_id = $query->row['tax_class_id'];
				}
			}
		}

		if ($res) {
			$res = $this->currency->format($this->tax->calculate($res, $tax_class_id, $this->config->get('config_tax')), $this->session->data['currency']);
		}

		return $res;
	}

	
	/*
	 * Внимание!
	 * Бывает, что в магазине есть цена 0 - она не должна учитываться!
	 * 
	 * Также иметь ввиду, что иногда через Скидки (discount) реализуется разная цена для разных городов
	 * 
	 * *Примечание
	 * (А!) Ищем товары данной категории и всех ее дочерних категорий!
	 * 
	 * (AA!)
	 * MySQL-функция MAX() не подходит, так как я получаю вместе с максимальной ценой и product_id (дополнительный столбец!)
	 * 
	 *  * ++++++
	 * Тут мы ищем максимум возможной цены - но не все так просто, как в getMinPriceInCat!
	 * DIFF WITH getMinPriceInCat()
	 * Цена товара зачасту выше любой скидки. И просто сравнение даст нам максимальную цену товара, но без учета скидки...
	 * (А!) То есть, каждый новый максимум НЕ является подходящим!
	 * Мы предполагаем, что акционная цена special по-разумному никогда не может быть больше базовой цены
	 * А вот цена скидки discount (может быть ценой городов) может быть больше базовой цены
	 */	
	public function getMaxPriceInCat($category_id) {	
		$res = false;
		
		$a_min_price = [
			'product_id' => false,
			'price' => false
		];

		// Самая дорогая базовая цена
		$sql = "SELECT"
			. " p.price,"
			. " p.product_id AS product_id,"
			. " p.tax_class_id"
			. " FROM " . DB_PREFIX . "product p"
			. " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)"
			. " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)"
			. " WHERE p2c.category_id IN (SELECT cp.category_id FROM " . DB_PREFIX . "category_path cp WHERE path_id = '" . (int)$category_id . "')"
			. " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'"
			. " AND p.date_available <= NOW()"
			. " AND p.status = '1' AND p.price > 0" // чтобы не отображать товары с ценой 0, как делают магазины, которые наполняются...
			. " ORDER BY p.price DESC"
			. " LIMIT 1";

		$query = $this->db->query($sql);

		if ($query->num_rows > 0) {
			$res = $query->row['price'];

			$a_max_price['product_id'] = $query->row['product_id'];
			$a_max_price['price'] = $query->row['price'];

			$tax_class_id	= $query->row['tax_class_id'];
		} else {
			// all prices are 0...
		}

		// Самая дорогая цена discount
		$sql = "SELECT"
			. " pd.price AS discount,"
			. " p.product_id AS product_id,"
			. " p.tax_class_id"
			. " FROM " . DB_PREFIX . "product_discount pd"
			. " LEFT JOIN " . DB_PREFIX . "product p ON (pd.product_id = p.product_id)"
			. " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (pd.product_id = p2c.product_id)"
			. " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (pd.product_id = p2s.product_id)"
			. " WHERE p2c.category_id IN (SELECT cp.category_id FROM " . DB_PREFIX . "category_path cp WHERE path_id = '" . (int)$category_id . "')"
			. " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'"
			. " AND pd.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((pd.date_start = '0000-00-00' OR pd.date_start < NOW()) AND (pd.date_end = '0000-00-00' OR pd.date_end > NOW()))"
			. " AND pd.quantity = '1'" // It is necessary for discount
			. " AND p.status = '1' AND pd.price > '0'" // чтобы отключенные товары не влияли на определение цены
			. " ORDER BY pd.priority ASC, pd.price DESC"
			. " LIMIT 1";

		$query = $this->db->query($sql);
		
		// Discount - замещает базовую цену по стандарту OpenCart
		if ($query->num_rows > 0) {
			// A!
			// DIFF WITH getMinPriceInCat()
			if ((float)$query->row['discount'] > (float)$a_max_price['price']) {
				$res = $query->row['discount'];
				
				if ($query->row['product_id'] != $a_max_price['product_id']) {
					$a_max_price['product_id'] = $query->row['product_id'];
					$a_max_price['price'] = $query->row['discount'];

					$tax_class_id	= $query->row['tax_class_id'];
				}				
			} else {
				// А если у самого дорого товара как раз и назначена скидка?
				// Запрос по product_id можно делать без некоторых условий, которые соблюдены 
				// в запросе по поиску самого дорогого товара
				$sql = "SELECT product_id, price AS discount FROM " . DB_PREFIX . "product_discount pd WHERE product_id = '" . (int)$a_max_price['product_id'] . "'"
					. " AND pd.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((pd.date_start = '0000-00-00' OR pd.date_start < NOW()) AND (pd.date_end = '0000-00-00' OR pd.date_end > NOW()))"
					. " AND pd.quantity = '1'" // It is necessary for discount"
					. " ORDER BY pd.priority ASC, pd.price"
					. " LIMIT 1";
				
				$query = $this->db->query($sql);
				
				if ($query->num_rows > 0) {
					if ($query->row['discount']) {
						$res = $query->row['discount'];
						
						$a_max_price['product_id'] = $query->row['product_id'];
						$a_max_price['price'] = $query->row['discount'];
					}
				}			
			}
		}

		// !!!!
		// В принципе, это абсурдно, чтобы special был больше, чем базовая цена и цена дисконта
		
		// Самая дорогая цена special
		$sql = "SELECT"
			. " ps.price AS special,"
			. " p.product_id AS product_id,"
			. " p.tax_class_id"
			. " FROM " . DB_PREFIX . "product_special ps"
			. " LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id)"
			. " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (ps.product_id = p2c.product_id)"
			. " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (ps.product_id = p2s.product_id)"
			. " WHERE p2c.category_id IN (SELECT cp.category_id FROM " . DB_PREFIX . "category_path cp WHERE path_id = '" . (int)$category_id . "')"
			. " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'"
			. " AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))"
			. " AND p.status = '1' AND ps.price > '0'" // чтобы отключенные товары не влияли на определение цены
			. " ORDER BY ps.priority ASC, ps.price"
			. " LIMIT 1";

		$query = $this->db->query($sql);

		if ($query->num_rows > 0) {
			// A!
			// DIFF WITH getMinPriceInCat()
			if ($query->row['special'] > $a_max_price['price']) {
				// Если это разные товары, то нам нужен tax_class_id более дорого товара со скидкой
				if ($query->row['product_id'] != $a_max_price['product_id']) {
					$res = $query->row['special'];
					
					//$a_max_price['price'] = $query->row['discount']; // unnecessary
					//$a_max_price['product_id'] = $query->row['product_id']; // unnecessary
					
					$tax_class_id = $query->row['tax_class_id'];
				}
			} else {
				// А если у самого дорого товара как раз и назначена скидка?
				// Запрос по product_id можно делать без некоторых условий, которые соблюдены 
				// в запросе по поиску самого дорогого товара
				$sql = "SELECT product_id, price AS special FROM " . DB_PREFIX . "product_special ps WHERE product_id = '" . (int)$a_max_price['product_id'] . "'"
					. " AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))"
					. " ORDER BY ps.priority ASC, ps.price DESC"
					. " LIMIT 1";
				
				$query = $this->db->query($sql);
				
				if ($query->num_rows > 0) {
					if ($query->row['special']) {
						$res = $query->row['special'];
						
						// $a_max_price['price'] = $query->row['special']; // unnecessary
						// $a_max_price['product_id'] = $query->row['product_id']; // unnecessary
					}
				}	
			}
		}

		if ($res) {
			$res = $this->currency->format($this->tax->calculate($res, $tax_class_id, $this->config->get('config_tax')), $this->session->data['currency']);
		}

		return $res;
	}

	public function getCategoryLevel($category_id) {
		$sql = "SELECT level FROM " . DB_PREFIX . "category_path WHERE category_id = '" . (int)$category_id . "' AND path_id = '" . (int)$category_id . "'";

		$query = $this->db->query($sql);

		if ($query->row) {
			return $query->row['level'];
		}

		return false;
	}

	public function getCategoryName($category_id, $lang_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "' AND language_id = '" . (int)$lang_id . "'";

		$query = $this->db->query($sql);
		
		if (isset($query->row['name'])) {
			return $query->row['name'];
		}

		return false;
	}


	public function getCategoriesNestedNames($current_category_id, $lang_id) {
		// is different from admin getCategoryNested() without lang parameter ?? так ли это??
		$sql = "SELECT cp.*, cd.name FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "category_description cd ON (cd.category_id = cp.path_id)WHERE cp.category_id = '" . (int)$current_category_id . "' AND cd.language_id = '" . (int)$lang_id . "' ORDER BY cp.level DESC";

		$query = $this->db->query($sql);

		if ($query->rows) {
			return $query->rows;
		}

		return array();
	}
	
	public function getCategoryDescriptionByIdAndLang($category_id, $lang_id) {
		// is different from admin getCategoryName() without lang parameter
		$sql	 = "SELECT * FROM  " . DB_PREFIX . "category_description WHERE`category_id` = '" . (int)$category_id . "' AND language_id = '" . (int)$lang_id . "'";
		$query = $this->db->query($sql);

		if ($query->row) {
			$result = array();

			$result['name'] = $query->row['name'];

			if (isset($query->row['h1'])) {
				$result['h1'] = $query->row['h1'];
			} elseif (isset($query->row['meta_h1'])) {
				$result['meta_h1'] = $query->row['meta_h1'];
			} else {
				$result['h1'] = '';
			}

			return $result;
		}

		return false;
	}

	public function getCategoryDeclension($category_id, $lang_id) {
		// is different from admin getCategoryDeclensionForEdit() without lang parameter
		$query = "SELECT"
			. " `category_name_singular_nominative`,"
			. " `category_name_singular_genitive`,"
			. " `category_name_plural_nominative`,"
			. " `category_name_plural_genitive`"
			. " FROM `" . DB_PREFIX . "seo_tags_generator_category_declension`"
			. " WHERE `category_id` = '" . (int)$category_id . "' AND `language_id` = '" . (int)$lang_id . "' ";

		$result = $this->db->query($query);

		if ($result->row) {
			// Проверяем падежи на пустоту
			if (empty($result->row['category_name_singular_nominative']) && empty($result->row['category_name_singular_genitive']) && empty($result->row['category_name_plural_nominative']) && empty($result->row['category_name_plural_genitive'])) {
				return false;
			}

			return $result->row;
		}

		return false;
	}


	public function getProductModelSynonym($product_id) {
		// model_synonym присутствует в $product_info, если установлен модификатор!
		if (!$this->existFieldModelSynonym()) {
			return false;
		}

		$query = "SELECT model_synonym FROM " . DB_PREFIX . "product WHERE product_id ='" . (int)$product_id . "'";
		$result = $this->db->query($query);

		if ($result->rows) {
			if ($result->row) {
				return $result->row['model_synonym'];
			}
		}

		return false;
	}


	public function existFieldModelSynonym() {
		$exist = false;

		$sql = "SHOW COLUMNS FROM " . DB_PREFIX . "product";
		$result = $this->db->query($sql);

		foreach ($result->rows as $field) {
			if ('model_synonym' == $field['Field']) {
				$exist = true;
				break;
			}
		}

		return $exist;
	}


		public function getManufacturerDescription($manufacturer_id, $language_id) {
		$sql = "SELECT * FROM " . DB_PREFIX . "manufacturer_description WHERE manufacturer_id = '" . (int)$manufacturer_id . "' AND language_id = '" . (int) $language_id . "'";
		
		$query = $this->db->query($sql);

		if ($query->num_rows) {
			return $query->row;
		}

		return false;
	}
	
	// A-M! В ocStore 3 есть таблица `manufacturer_description`, но там нету поля `name`...	
	public function isNameInManufacturerDescription() {
		$sql = "SHOW TABLES FROM `" . DB_DATABASE . "` like '" . DB_PREFIX . "manufacturer_description'";
		
		$query = $this->db->query($sql);

		if ($query->num_rows > 0) {
			$sql2 = "SHOW COLUMNS FROM " . DB_PREFIX . "manufacturer_description";
			
			$query2 = $this->db->query($sql2);
			
			foreach ($query2->rows as $field) {
				if ('name' == $field['Field']) {
					return true;
				}
			}			
		}
		
		return false;
	}

	/*
	 * Внимание!
	 * Бывает, что в магазине есть цена 0 - она не должна учитываться!
	 * 
	 * (AA!)
	 * MySQL-функция MIN() не подходит, так как я получаю вместе с максимальной ценой и product_id (дополнительный столбец!)
	 * 
	 * Также иметь ввиду, что иногда через Скидки (discount) реализуется разная цена для разных городов
	 * 
	 * ++++++
	 * Тут мы ищем минимум возможной цены - все достаточно просто
	 */
	public function getMinPriceOfManufacturer($manufacturer_id) {	
		$res = false;

		$a_min_price = [
			'product_id' => false,
			'price' => false
		];

		// Самая дешевая базовая цена
		$sql = "SELECT"
			. " p.price,"
			. " p.product_id AS product_id,"
			. " p.tax_class_id"
			. " FROM " . DB_PREFIX . "product p"
			. " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)"
			. " WHERE p.manufacturer_id = '" . (int)$manufacturer_id . "'"
			. " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'"
			. " AND p.date_available <= NOW()"
			. " AND p.status = '1' AND p.price > 0" // чтобы не отображать товары с ценой 0, как делают магазины, которые наполняются...
			. " ORDER BY p.price ASC"
			. " LIMIT 1";
		
		$query = $this->db->query($sql);

		if ($query->num_rows > 0) {
			$res = $query->row['price'];

			$a_min_price['product_id'] = $query->row['product_id'];
			$a_min_price['price'] = $query->row['price'];

			$tax_class_id	= $query->row['tax_class_id'];
		} else {
			// all prices are 0...
		}				

		// Самая дешевая цена discount
		$sql = "SELECT"
			. " pd.price AS discount,"
			. " p.product_id AS product_id,"
			. " p.tax_class_id"
			. " FROM " . DB_PREFIX . "product_discount pd"
			. " LEFT JOIN " . DB_PREFIX . "product p ON (pd.product_id = p.product_id)"
			. " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (pd.product_id = p2s.product_id)"
			. " WHERE p.manufacturer_id = '" . (int)$manufacturer_id . "'"
			. " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'"
			. " AND pd.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((pd.date_start = '0000-00-00' OR pd.date_start < NOW()) AND (pd.date_end = '0000-00-00' OR pd.date_end > NOW()))"
			. " AND pd.quantity = '1'" // It is necessary for discount
			. " AND p.status = '1' AND pd.price > '0'" // чтобы отключенные товары не влияли на определение цены
			. " ORDER BY pd.priority ASC, pd.price ASC"
			. " LIMIT 1";

		$query = $this->db->query($sql);

		// Discount - замещает базовую цену по стандарту OpenCart
		if ($query->num_rows > 0) {
			if ((float)$query->row['discount'] < (float)$a_min_price['price']) {
				$res = $query->row['discount'];

				$a_min_price['product_id'] = $query->row['product_id'];
				$a_min_price['price'] = $query->row['discount'];

				$tax_class_id	= $query->row['tax_class_id'];
			}
		}

		// Самая дешевая цена special
		$sql = "SELECT"
			. " ps.price AS special,"
			. " p.product_id AS product_id,"
			. " p.tax_class_id"
			. " FROM " . DB_PREFIX . "product_special ps"
			. " LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id)"
			. " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (ps.product_id = p2s.product_id)"
			. " WHERE p.manufacturer_id = '" . (int)$manufacturer_id . "'"
			. " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'"
			. " AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))"
			. " AND p.status = '1' AND ps.price > '0'" // чтобы отключенные товары не влияли на определение цены
			. " ORDER BY ps.priority ASC, ps.price ASC"
			. " LIMIT 1";

		$query = $this->db->query($sql);
		
		if ($query->num_rows > 0) {
			// Сравнимаем минимальную цену special с минимальной ценой (price:discount)
			if ($query->row['special'] < $a_min_price['price']) {
				$res = $query->row['special'];

				// Если это разные товары, то нам нужен tax_class_id более дешевого товара со скидкой
				if ($query->row['product_id'] != $a_min_price['product_id']) {
					$tax_class_id = $query->row['tax_class_id'];
				}
			}
		}

		if ($res) {
			$res = $this->currency->format($this->tax->calculate($res, $tax_class_id, $this->config->get('config_tax')), $this->session->data['currency']);
		}

		return $res;
	}

	
	/*
	 * Внимание!
	 * Бывает, что в магазине есть цена 0 - она не должна учитываться!
	 * 
	 * Также иметь ввиду, что иногда через Скидки (discount) реализуется разная цена для разных городов
	 * 
	 * (AA!)
	 * MySQL-функция MAX() не подходит, так как я получаю вместе с максимальной ценой и product_id (дополнительный столбец!)
	 * 
	 *  * ++++++
	 * Тут мы ищем максимум возможной цены - но не все так просто, как в getMinPriceOfManufacturer!
	 * DIFF WITH getMinPriceOfManufacturer()
	 * Цена товара зачасту выше любой скидки. И просто сравнение даст нам максимальную цену товара, но без учета скидки...
	 * (А!) То есть, каждый новый максимум НЕ является подходящим!
	 * Мы предполагаем, что акционная цена special по-разумному никогда не может быть больше базовой цены
	 * А вот цена скидки discount (может быть ценой городов) может быть больше базовой цены
	 */	
	public function getMaxPriceOfManufacturer($manufacturer_id) {	
		$res = false;
		
		$a_min_price = [
			'product_id' => false,
			'price' => false
		];

		// Самая дорогая базовая цена
		$sql = "SELECT"
			. " p.price,"
			. " p.product_id AS product_id,"
			. " p.tax_class_id"
			. " FROM " . DB_PREFIX . "product p"
			. " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id)"
			. " WHERE p.manufacturer_id = '" . (int)$manufacturer_id . "'"
			. " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'"
			. " AND p.date_available <= NOW()"
			. " AND p.status = '1' AND p.price > 0" // чтобы не отображать товары с ценой 0, как делают магазины, которые наполняются...
			. " ORDER BY p.price DESC"
			. " LIMIT 1";

		$query = $this->db->query($sql);

		if ($query->num_rows > 0) {
			$res = $query->row['price'];

			$a_max_price['product_id'] = $query->row['product_id'];
			$a_max_price['price'] = $query->row['price'];

			$tax_class_id	= $query->row['tax_class_id'];
		} else {
			// all prices are 0...
		}

		// Самая дорогая цена discount
		$sql = "SELECT"
			. " pd.price AS discount,"
			. " p.product_id AS product_id,"
			. " p.tax_class_id"
			. " FROM " . DB_PREFIX . "product_discount pd"
			. " LEFT JOIN " . DB_PREFIX . "product p ON (pd.product_id = p.product_id)"
			. " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (pd.product_id = p2s.product_id)"
			. " WHERE p.manufacturer_id = '" . (int)$manufacturer_id . "'"
			. " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'"
			. " AND pd.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((pd.date_start = '0000-00-00' OR pd.date_start < NOW()) AND (pd.date_end = '0000-00-00' OR pd.date_end > NOW()))"
			. " AND pd.quantity = '1'" // It is necessary for discount
			. " AND p.status = '1' AND pd.price > '0'" // чтобы отключенные товары не влияли на определение цены
			. " ORDER BY pd.priority ASC, pd.price DESC"
			. " LIMIT 1";

		$query = $this->db->query($sql);
		
		// Discount - замещает базовую цену по стандарту OpenCart
		if ($query->num_rows > 0) {
			// A!
			// DIFF WITH getMinPriceOfManufacturer()
			if ((float)$query->row['discount'] > (float)$a_max_price['price']) {
				$res = $query->row['discount'];
				
				if ($query->row['product_id'] != $a_max_price['product_id']) {
					$a_max_price['product_id'] = $query->row['product_id'];
					$a_max_price['price'] = $query->row['discount'];

					$tax_class_id	= $query->row['tax_class_id'];
				}				
			} else {
				// А если у самого дорого товара как раз и назначена скидка?
				// Запрос по product_id можно делать без некоторых условий, которые соблюдены 
				// в запросе по поиску самого дорогого товара
				$sql = "SELECT product_id, price AS discount FROM " . DB_PREFIX . "product_discount pd WHERE product_id = '" . (int)$a_max_price['product_id'] . "'"
					. " AND pd.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((pd.date_start = '0000-00-00' OR pd.date_start < NOW()) AND (pd.date_end = '0000-00-00' OR pd.date_end > NOW()))"
					. " AND pd.quantity = '1'" // It is necessary for discount"
					. " ORDER BY pd.priority ASC, pd.price"
					. " LIMIT 1";
				
				$query = $this->db->query($sql);
				
				if ($query->num_rows > 0) {
					if ($query->row['discount']) {
						$res = $query->row['discount'];
						
						$a_max_price['product_id'] = $query->row['product_id'];
						$a_max_price['price'] = $query->row['discount'];
					}
				}			
			}
		}

		// !!!!
		// В принципе, это абсурдно, чтобы special был больше, чем базовая цена и цена дисконта
		
		// Самая дорогая цена special
		$sql = "SELECT"
			. " ps.price AS special,"
			. " p.product_id AS product_id,"
			. " p.tax_class_id"
			. " FROM " . DB_PREFIX . "product_special ps"
			. " LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id)"
			. " LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (ps.product_id = p2s.product_id)"
			. " WHERE p.manufacturer_id = '" . (int)$manufacturer_id . "'"
			. " AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'"
			. " AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))"
			. " AND p.status = '1' AND ps.price > '0'" // чтобы отключенные товары не влияли на определение цены
			. " ORDER BY ps.priority ASC, ps.price"
			. " LIMIT 1";

		$query = $this->db->query($sql);

		if ($query->num_rows > 0) {
			// A!
			// DIFF WITH getMinPriceOfManufacturer()
			if ($query->row['special'] > $a_max_price['price']) {
				// Если это разные товары, то нам нужен tax_class_id более дорого товара со скидкой
				if ($query->row['product_id'] != $a_max_price['product_id']) {
					$res = $query->row['special'];
					
					//$a_max_price['price'] = $query->row['discount']; // unnecessary
					//$a_max_price['product_id'] = $query->row['product_id']; // unnecessary
					
					$tax_class_id = $query->row['tax_class_id'];
				}
			} else {
				// А если у самого дорого товара как раз и назначена скидка?
				// Запрос по product_id можно делать без некоторых условий, которые соблюдены 
				// в запросе по поиску самого дорогого товара
				$sql = "SELECT product_id, price AS special FROM " . DB_PREFIX . "product_special ps WHERE product_id = '" . (int)$a_max_price['product_id'] . "'"
					. " AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))"
					. " ORDER BY ps.priority ASC, ps.price DESC"
					. " LIMIT 1";
				
				$query = $this->db->query($sql);
				
				if ($query->num_rows > 0) {
					if ($query->row['special']) {
						$res = $query->row['special'];
						
						// $a_max_price['price'] = $query->row['special']; // unnecessary
						// $a_max_price['product_id'] = $query->row['product_id']; // unnecessary
					}
				}	
			}
		}

		if ($res) {
			$res = $this->currency->format($this->tax->calculate($res, $tax_class_id, $this->config->get('config_tax')), $this->session->data['currency']);
		}

		return $res;
	}


	public function getParentCategoryByCategoryId($category_id) {
		$sql = "SELECT parent_id FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$category_id . "'";

		$result = $this->db->query($sql);

		if (isset($result->row['parent_id']) && !empty($result->row['parent_id'])) {
			return $result->row['parent_id'];
		} else {
			return $category_id; // Если никуда не вложена
		}

		return false;
	}

	
	public function getParentCategoryByProductId($product_id) {
		$category_id					 = false;
		$exist_main_cat_field	 = false;

		$sql		 = "SHOW COLUMNS FROM " . DB_PREFIX . "product_to_category;";
		$result	 = $this->db->query($sql);

		// Изначально в таблице 2 поля
		if ($result->num_rows > 2) {
			foreach ($result->rows as $field) {
				if ('main_category' == $field['Field']) {
					$exist_main_cat_field = true;
				}
			}
		}

		if (true === $exist_main_cat_field) {
			$sql		 = "SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND main_category = '1'";
			$result	 = $this->db->query($sql);

			if ($result->num_rows) {
				$category_id = (int)$result->row['category_id'];
			}
		}

		if (!$category_id) {
			$sql		 = "SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'";
			$result	 = $this->db->query($sql);

			// Если только 1 родительская категория, то возвращаем ее
			if (1 == $result->num_rows) {
				$category_id = $result->row['category_id'];
			}

			if ($result->num_rows > 1) {
				// Эмпирически определяем главную категорию товара
				// Главнее та, что глубже уровня вложенности
				foreach ($result->rows as $item) {
					if ($item['category_id'] > $category_id) {
						$category_id = $item['category_id'];
					}
				}
			}
		}

		return $category_id;
	}


	public function notUseAutoCategory($id) {
		$res = $this->db->query("SELECT `id` FROM `" . DB_PREFIX . "seo_tags_generator_not_use` WHERE `id` = '" . (int)$id . "' AND `essence_id` = '2'");
		if ($res) {
			if (0 < $res->num_rows) {
				return true;
			}
		}
		return false;
	}


	public function notUseAutoProduct($id) {
		$res = $this->db->query("SELECT `id` FROM `" . DB_PREFIX . "seo_tags_generator_not_use` WHERE `id` = '" . (int)$id . "' AND `essence_id` = '1'");
		if ($res) {
			if (0 < $res->num_rows) {
				return true;
			}
		}
		return false;
	}


}
