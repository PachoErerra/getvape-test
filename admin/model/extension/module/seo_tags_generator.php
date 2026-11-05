<?php

/**
 * @category   OpenCart
 * @package    SEO Tags Generator
 * @copyright  © Serge Tkach, 2017-2022, https://sergetkach.com/
 */

class ModelExtensionModuleSeoTagsGenerator extends Model {

	


	public function getGenerateMode() {
		return array(
			'nofollow', 'empty', 'forced'
		);
	}




	/* Category Declension
	--------------------------------------------------------------------------- */
	public function getCategoryDeclensionForEdit($category_id) {
		// is different from function getCategoryDeclension() for generate
		$sql = "SELECT * FROM `" . DB_PREFIX . "seo_tags_generator_category_declension` WHERE `category_id` = '" . (int)$category_id . "' ORDER BY `language_id` ASC";

		$result	 = $this->db->query($sql);

		$array = array();

		foreach ($result->rows as $value) {
			$array[$value['language_id']] = array(
				'category_name_singular_nominative'	 => $value['category_name_singular_nominative'],
				'category_name_singular_genitive'		 => $value['category_name_singular_genitive'],
				'category_name_plural_nominative'		 => $value['category_name_plural_nominative'],
				'category_name_plural_genitive'			 => $value['category_name_plural_genitive'],
			);
		}

		return $array;

		return false;
	}


	public function addCategoryDeclension($category_id, $data) {
		foreach ($data as $language_id => $value) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_tags_generator_category_declension` SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', `category_name_singular_nominative` = '" . $this->db->escape($value['category_name_singular_nominative']) . "', `category_name_singular_genitive` = '" . $this->db->escape($value['category_name_singular_genitive']) . "', `category_name_plural_nominative` = '" . $this->db->escape($value['category_name_plural_nominative']) . "', `category_name_plural_genitive` = '" . $this->db->escape($value['category_name_plural_genitive']) . "'");
		}
	}


	public function editCategoryDeclension($category_id, $data) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_tags_generator_category_declension` WHERE `category_id` = '" . (int)$category_id . "'");

		foreach ($data as $language_id => $value) {

			$sql = "INSERT INTO `" . DB_PREFIX . "seo_tags_generator_category_declension` SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', `category_name_singular_nominative` = '" . $this->db->escape($value['category_name_singular_nominative']) . "', `category_name_singular_genitive` = '" . $this->db->escape($value['category_name_singular_genitive']) . "', `category_name_plural_nominative` = '" . $this->db->escape($value['category_name_plural_nominative']) . "', `category_name_plural_genitive` = '" . $this->db->escape($value['category_name_plural_genitive']) . "'";

			$this->db->query($sql);
		}
	}





	/* Meta Tags Formulas In Category
	--------------------------------------------------------------------------- */
	public function getCategoryFormulas($category_id) {
		$final_array = array();

		$query	 = "SELECT `category_id`, `language_id`, `key`, `value` FROM `" . DB_PREFIX . "seo_tags_generator` WHERE `category_id` = '" . (int)$category_id . "'";
		$result	 = $this->db->query($query);

		if ($result->num_rows) {
			foreach ($result->rows as $row) {
				$value_array = json_decode($row['value'], true);

				foreach ($value_array as $key => $value) {
					$final_array[$row['language_id']][$row['key']][$key] = $value;
				}
			}
		}

		return $final_array;
	}


	public function addCategoryFormulas($category_id, $data) {
		// Check if not empty formual !!
		if ($this->isEmptyFormulas($data['formulas'])) {
			// Чистить настройки еще нечего
		} else {
			$this->saveFormulasToCategory($category_id, $data['formulas']);

			// Настройки меняются только при наличии формул. Иначе они бессмысленны...
			$this->addCategorySetting($category_id, $data['setting']);

			// To descendants copy
			if (isset($data['setting']['inheritance_copy'])) {
				$descendants_categories = $this->getDescendantsLinear($category_id);

				foreach ($descendants_categories as $descendants_category_id) {
					$this->saveFormulasToCategory($descendants_category_id, $data['formulas']);
				}
			}

			// copy formulas to others catgories
			if (isset($data['setting']['copy_to_others'])) {

				// Могут галочку отметить, а категории не выбрать
				if (isset($data['copy_to_categories'])) {
					foreach ($data['copy_to_categories'] as $copy_category_id) {
						$this->saveFormulasToCategory($copy_category_id, $data['formulas']);

						// save dependens
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_tags_generator_category_copy` SET category_id = '" . (int)$category_id . "', copy_category_id = '" . (int)$copy_category_id . "'");
					}
				} else {
					// Если категории не выбраны, значит ли это, что необходимо удалить все формулы ?
					// Нет не значит!
					// Может случиться так, что категорий было выбрано 10, а теперь только 5 - как я буду знать, не хочет ли пользователь удалить те 5 категорий ?
					// Функционал предназначен для облегчения копирования однотипной информации, но не для полноценного менеджмента !
				}
			}
		}
	}


	/*
	 * Хотя это немного странно, что из модели категории вызывается именно editCategoryFormulas()
	 * но от наличия формул много зависит.
	 * А остальное наращивалось по ходу
	 * При ревью, конечно, лучше вызывать editCategorySetting(), в котором будут вызывать отдельные дочерние методы.
	 * Хотя, при ревью там надо хорошо подумать, как реорганизовать таблицы, чтобы черт ногу не ломал
	 */
	public function editCategoryFormulas($category_id, $data) {
		// reset all data for this category
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_tags_generator` WHERE category_id = '" . (int)$category_id . "'");

		// Check if not empty formulas !!
		if ($this->isEmptyFormulas($data['formulas'])) {
			$this->editCategorySetting($category_id, $data['setting'], 'empty');
			
		} else {
			$this->saveFormulasToCategory($category_id, $data['formulas']);

			// Настройки меняются только при наличии формул. Иначе они бессмысленны...
			$this->editCategorySetting($category_id, $data['setting']);

			// To descendants copy
			if (isset($data['setting']['inheritance_copy'])) {
				$descendants_categories = $this->getDescendantsLinear($category_id);

				foreach ($descendants_categories as $descendants_category_id) {
					$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_tags_generator` WHERE category_id = '" . (int)$descendants_category_id . "'");
					$this->saveFormulasToCategory($descendants_category_id, $data['formulas']);
				}
			}

			// copy formulas to others catgories
			if (isset($data['setting']['copy_to_others'])) {
				// delete old dependence
				$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_tags_generator_category_copy` WHERE category_id = '" . (int)$category_id . "'");

				// Могут галочку отметить, а категории не выбрать. Или наоборот снять категории
				if (isset($data['copy_to_categories'])) {
					foreach ($data['copy_to_categories'] as $copy_category_id) {
						$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_tags_generator` WHERE category_id = '" . (int)$copy_category_id . "'");
						$this->saveFormulasToCategory($copy_category_id, $data['formulas']);

						// save dependens
						$this->db->query("INSERT INTO `" . DB_PREFIX . "seo_tags_generator_category_copy` SET category_id = '" . (int)$category_id . "', copy_category_id = '" . (int)$copy_category_id . "'");
					}
				} else {
					// Если категории не выбраны, значит ли это, что необходимо удалить все формулы ?
					// Нет не значит!
					// Может случиться так, что категорий было выбрано 10, а теперь только 5 - как я буду знать, не хочет ли пользователь удалить те 5 категорий ?
					// Функционал предназначен для облегчения копирования однотипной информации, но не для полноценного менеджмента !
				}
			}
		}
	}


	public function isEmptyFormulas($data) {
		foreach ($data as $language_id => $value) {
			foreach ($value as $key => $item) {
				foreach ($item as $result) {
					if ($result) {
						return false; // not empty
					}
				}
			}
		}
		return true; // empty
	}


	public function saveFormulasToCategory($category_id, $data) {
		foreach ($data as $language_id => $value) {
			foreach ($value as $key => $item) {
				$sql = "INSERT INTO `" . DB_PREFIX . "seo_tags_generator` SET category_id = '" . (int)$category_id . "', language_id = '" . (int)$language_id . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape(json_encode($item)) . "'";

				$this->db->query($sql);
			}
		}
	}


	public function getCategorySetting($category_id) {
		$query	 = "SELECT `setting` FROM `" . DB_PREFIX . "seo_tags_generator_category_setting` WHERE `category_id` = '" . (int)$category_id . "'";
		$result	 = $this->db->query($query);

		if ($result->num_rows) {
			return json_decode($result->row['setting'], true);
		}

		return false;
	}


	public function addCategorySetting($category_id, $data) {
		$sql = "INSERT INTO `" . DB_PREFIX . "seo_tags_generator_category_setting` SET category_id = '" . (int)$category_id . "', `setting` = '" . $this->db->escape(json_encode($data)) . "'";

		$this->db->query($sql);
	}


	/*
	 * A!
	 * Важно, чтобы не было радиопереключатель наследования не попадал в базу при 0, если никаких других настроек не задано
	 * Потому что попадание только этой настройки ломает малину на витрине, когда идет поиск настроек для родительской категории
	 * и там формально есть настройки наследования, которые нафиг ни на что не годятся...
	 */
	public function editCategorySetting($category_id, $data, $formulas_is = 'completed') {
		// Учитывать, если однажды уже применялось копировани формул в дочерние или другие категории
		$data_was = $this->getCategorySetting($category_id);

		// $data['inheritance'] - наследовать или нет
		// не вмешиваемся
		// копировать в дочерние
		if (isset($data_was['inheritance_copy'])) {
			$data['inheritance_copy'] = 1;
		}

		// копировать в другие категори
		if (isset($data_was['copy_to_others'])) {
			$data['copy_to_others'] = 1;
		}

		// Delete old setting		
		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_tags_generator_category_setting` WHERE category_id = '" . (int)$category_id . "'");

		// Хм, а есть ли смысл сохранять атрибуты, при пустых формулах?
		// Может ли быть такое, что будет 2-3 атрибута, которые будут меняться в зависимости от категории
		// и при этом для товаров вполне себе подходит общая формула??
		
		// Это когда сохраняются только настройка наследования...
		if ($formulas_is == 'empty' && count($data) < 2 && isset($data['inheritance'])) {
			// Нету смысла сохранять такие настройки для категории!!
			// Q? А как я знаю, может там формулы есть?????????
		} else {
			$sql = "INSERT INTO `" . DB_PREFIX . "seo_tags_generator_category_setting` SET category_id = '" . (int)$category_id . "', `setting` = '" . $this->db->escape(json_encode($data)) . "'";

			$this->db->query($sql);
		}
	}

	public function getCategoryCopy($category_id) {
		$array = array();
		$sql	 = "SELECT `copy_category_id` FROM `" . DB_PREFIX . "seo_tags_generator_category_copy` WHERE `category_id` = '" . (int)$category_id . "'";
		$query = $this->db->query($sql);

		if ($query->rows) {
			foreach ($query->rows as $result) {
				$array[] = $result['copy_category_id'];
			}
			return $array;
		}

		return false;
	}


	public function getCategoryCopyExist($a_exclude) {
		$array = array();
		$sql	 = "SELECT `copy_category_id` FROM `" . DB_PREFIX . "seo_tags_generator_category_copy`";
		$query = $this->db->query($sql);

		if ($query->rows) {
			foreach ($query->rows as $result) {
				if (is_array($a_exclude)) {
					if (!in_array($result['copy_category_id'], $a_exclude)) {
						$array[] = $result['copy_category_id'];
					}
				} else {
					$array[] = $result['copy_category_id'];
				}
			}
		}

		return $array;
	}




	/* Copy to Categories
	--------------------------------------------------------------------------- */
	public function getCopyCategories($category_id) {
		$copy_to_categories_data = array();

		$query = $this->db->query("SELECT `copy_category_id` FROM " . DB_PREFIX . "seo_tags_generator_category_copy WHERE category_id = '" . (int)$category_id . "'");

		foreach ($query->rows as $result) {
			$copy_to_categories_data[] = $result['copy_category_id'];
		}

		return $copy_to_categories_data;
	}





	/* Category Dauthers
	--------------------------------------------------------------------------- */
	public function getDescendantsTreeForCategory($category_id) {
		$array = array(
			'category_id'		 => $category_id,
			'category_name'	 => $this->getCategoryName($category_id, $this->config->get('config_language_id'))
		);

		// dauthers
		$sql = "SELECT `category_id` FROM " . DB_PREFIX . "category WHERE parent_id = '" . (int)$category_id . "'";

		$query = $this->db->query($sql);

		if ($query->num_rows > 0) {
			$array['has_children'] = 1;

			foreach ($query->rows as $result) {
				$array['children'][] = $this->getDescendantsTreeForCategory($result['category_id']);
			}
		} else {
			$array['has_children'] = false;
		}

		return $array;
	}


	public function getDescendantsLinear($category_id) {
		$array = array();

		$sql = "SELECT `category_id` FROM `" . DB_PREFIX . "category_path` WHERE `path_id` = '" . (int)$category_id . "' AND `category_id` != '" . (int)$category_id . "'";

		$query = $this->db->query($sql);

		if ($query->num_rows > 0) {
			foreach ($query->rows as $result) {
				$array[] = $result['category_id'];
			}
		}

		return $array;
	}


	public function getCategoriesMain() {
		$array = array();

		$sql = "SELECT `category_id` FROM `" . DB_PREFIX . "category` WHERE `parent_id` = '0' ";

		$query = $this->db->query($sql);

		foreach ($query->rows as $result) {
			$array[] = $result['category_id'];
		}

		return $array;
	}

	public function getSpecificFormulas() {
		$final_array = array();
		// Сортируем по категории по возрастанию, ведь категории у нас будут использоваться для ключей ??
		$query			 = "SELECT `category_id`, `language_id`, `key`, `value` FROM `" . DB_PREFIX . "seo_tags_generator` ORDER BY `category_id` ASC";
		$result			 = $this->db->query($query);

		if ($result->num_rows) {
			$i = 0;
			foreach ($result->rows as $row) {
				$value_array = json_decode($row['value'], true);
				foreach ($value_array as $key => $value) {
					if (isset($final_array[$i]) && $row['category_id'] != $final_array[$i]['category_id']) {
						$i++; // Прибавлять ключ к массиву, когда увеличивается меняется значение id категории
					}

					$final_array[$i]['category_id']																		 = $row['category_id'];
					$final_array[$i]['category_name']																	 = $this->getCategoryNameById($row['category_id']);
					$final_array[$i]['langs'][$row['language_id']][$row['key']][$key]	 = $value;
				}
			}
		}

		return $final_array;
	}


	public function getCategoryNameById($category_id) {
		$sql		 = "SELECT `name` FROM `" . DB_PREFIX . "category_description` WHERE `category_id`='" . (int)$category_id . "' AND `language_id` = '" . $this->config->get('config_language_id') . "'";
		$result	 = $this->db->query($sql);
		if ($result->row) {
			return $result->row['name'];
		}
		return false;
	}


	public function setSpecificFormulas($data) {
		$final_array = array();
		$i					 = 0;

		if (count($data) > 0) {
			foreach ($data as $key => $item) {
				foreach ($item['langs'] as $lang_id => $value_array_for_lang) {
					foreach ($value_array_for_lang as $key_entity => $value_entity) {
						if (!empty($value_entity['title']) && !empty($value_entity['description'])) {
							$final_array[$i]['category_id']					 = $item['category_id'];
							$final_array[$i]['language_id']					 = $lang_id;
							$final_array[$i]['key']									 = $key_entity;
							$final_array[$i]['value']['title']			 = $value_entity['title'];
							$final_array[$i]['value']['description'] = $value_entity['description'];
							$final_array[$i]['value']['keyword']		 = $value_entity['keyword'];
							if ('product' == $key_entity) {
								if (!empty($value_entity['h1'])) {
									$final_array[$i]['value']['h1'] = $value_entity['h1']; // for products only
								}
							}
							$i++;
						}
					}
				}
			}
		}

		$this->db->query("DELETE FROM `" . DB_PREFIX . "seo_tags_generator`");

		foreach ($final_array as $item) {
			$sql = "INSERT INTO `" . DB_PREFIX . "seo_tags_generator` SET
        `category_id` = '" . (int)$item['category_id'] . "',
        `language_id` = '" . (int)$item['language_id'] . "',
        `key` = '" . $this->db->escape($item['key']) . "',
        `value` = '" . $this->db->escape(json_encode($item['value'])) . "'
      ";

			$this->db->query($sql);
		}
	}


	public function setStgNotUse($id, $essence) {
		if ('product' == $essence) {
			$essence_id = 1;
		} elseif ('category' == $essence) {
			$essence_id = 2;
		} else {
			return false;
		}
		$sql = "INSERT IGNORE INTO `" . DB_PREFIX . "seo_tags_generator_not_use` SET `id`='" . (int)$id . "', `essence_id` = '" . (int)$essence_id . "'";
		return $this->db->query($sql);
	}


	public function setStgNotUseOff($id, $essence) {
		if ('product' == $essence) {
			$essence_id = 1;
		} elseif ('category' == $essence) {
			$essence_id = 2;
		} else {
			return false;
		}
		$sql = "DELETE FROM `" . DB_PREFIX . "seo_tags_generator_not_use` WHERE `id`='" . (int)$id . "' AND `essence_id` = '" . (int)$essence_id . "'";
		return $this->db->query($sql);
	}



	/* Helpers
	--------------------------------------------------------------------------- */

	// for admin only
	public function getRating($product_id) {
		$sql = "SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = '" . (int)$product_id . "' AND r1.status = '1' GROUP BY r1.product_id";

		$query = $this->db->query($sql);

		if ($query->num_rows > 0) {
			return round($query->row['total']);
		} else {
			return 0;
		}
	}

	// for admin only
	public function getReviews($product_id) {
		$sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = '" . (int)$product_id . "' AND r2.status = '1' GROUP BY r2.product_id";

		$query = $this->db->query($sql);

		if ($query->num_rows > 0) {
			return round($query->row['total']);
		} else {
			return 0;
		}
	}


	// for admin only
	public function getAllAttributeValues($test_mode = false) {
		$sql = "SELECT DISTINCT text, attribute_id, language_id FROM " . DB_PREFIX . "product_attribute WHERE text != '' ORDER BY attribute_id ASC";

		$query = $this->db->query($sql);

		return $query->rows;
	}


	// for admin only
	public function getAttributes($data = array(), $test_mode = false) {
		$sql = "SELECT "
			. "a.attribute_id, "
			. "a.attribute_group_id, "
			. "ad.language_id, "
			. "ad.name, "
			//. "pa.product_id, "
			. "(SELECT agd.name FROM " . DB_PREFIX . "attribute_group_description agd WHERE agd.attribute_group_id = a.attribute_group_id AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS attribute_group "
			. "FROM " . DB_PREFIX . "attribute a "
			//. "LEFT JOIN " . DB_PREFIX . "product_attribute pa ON (pa.attribute_id = a.attribute_id) "
			. "LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (ad.attribute_id = a.attribute_id) "
			. "WHERE ad.language_id = '" . (int)$this->config->get('config_language_id') . "' ";

		if (!empty($data['filter_name'])) {
			$sql .= " AND ad.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_attribute_group_id'])) {
			$sql .= " AND a.attribute_group_id = '" . $this->db->escape($data['filter_attribute_group_id']) . "'";
		}

		$sort_data = array(
			'ad.name',
			'attribute_group',
			'a.sort_order'
		);

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY attribute_group, ad.name";
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


	// for admin only
	// A!
	// $this->model_catalog_product->getProductAttributes($this->request->get['product_id']) дает разные результаты в админке и на витрине!!
	// Поэтому в админке товара использую $this->model_extension_module_seo_tags_generator->getProductAttributes($this->request->get['product_id'])
	public function getProductAttributes($product_id) {
		$product_attribute_group_data = array();

		$product_attribute_group_query = $this->db->query("SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE pa.product_id = '" . (int)$product_id . "' AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY ag.attribute_group_id ORDER BY ag.sort_order, agd.name");

		foreach ($product_attribute_group_query->rows as $product_attribute_group) {
			$product_attribute_data = array();

			$product_attribute_query = $this->db->query("SELECT a.attribute_id, ad.name, pa.text FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.attribute_group_id = '" . (int)$product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pa.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY a.sort_order, ad.name");

			foreach ($product_attribute_query->rows as $product_attribute) {
				$product_attribute_data[] = array(
					'attribute_id' => $product_attribute['attribute_id'],
					'name'         => $product_attribute['name'],
					'text'         => $product_attribute['text']
				);
			}

			$product_attribute_group_data[] = array(
				'attribute_group_id' => $product_attribute_group['attribute_group_id'],
				'name'               => $product_attribute_group['name'],
				'attribute'          => $product_attribute_data
			);
		}

		return $product_attribute_group_data;
	}


	// for admin only
	public function getSpecial($product_id) {
		$sql = "SELECT price AS special FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = " . (int)$product_id . " AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1 ";

		$query = $this->db->query($sql);

		if (isset($query->row['special'])) {
			return $query->row['special'];
		} else {
			return false;
		}
	}


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
	 * В админке не доступно $this->config->get('config_store_id')
	 * return NULL
	 * 
	 * $this->config->get('config_customer_group_id')
	 * return 1 (default)
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
			. " WHERE p2c.category_id IN (SELECT cp.category_id FROM " . DB_PREFIX . "category_path cp WHERE path_id = '" . (int)$category_id . "')"
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
			. " WHERE p2c.category_id IN (SELECT cp.category_id FROM " . DB_PREFIX . "category_path cp WHERE path_id = '" . (int)$category_id . "')"
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
			. " WHERE p2c.category_id IN (SELECT cp.category_id FROM " . DB_PREFIX . "category_path cp WHERE path_id = '" . (int)$category_id . "')"
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
	 * В админке не доступно $this->config->get('config_store_id')
	 * return NULL
	 * 
	 * $this->config->get('config_customer_group_id')
	 * return 1 (default)
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
			. " WHERE p2c.category_id IN (SELECT cp.category_id FROM " . DB_PREFIX . "category_path cp WHERE path_id = '" . (int)$category_id . "')"
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
			. " WHERE p2c.category_id IN (SELECT cp.category_id FROM " . DB_PREFIX . "category_path cp WHERE path_id = '" . (int)$category_id . "')"
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
			. " WHERE p2c.category_id IN (SELECT cp.category_id FROM " . DB_PREFIX . "category_path cp WHERE path_id = '" . (int)$category_id . "')"
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
		// It is necessary to recieve lang_id to have different names for different languages on demonstation in admin
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "category_description WHERE category_id = '" . (int)$category_id . "' AND language_id = '" . (int)$lang_id . "'");

		if (isset($query->row['name'])) {
			return $query->row['name'];
		}

		return 'No Category Name';
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
	 * В админке не доступно $this->config->get('config_store_id')
	 * return NULL
	 * 
	 * $this->config->get('config_customer_group_id')
	 * return 1 (default)
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
			. " WHERE p.manufacturer_id = '" . (int)$manufacturer_id . "'"
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
			. " WHERE p.manufacturer_id = '" . (int)$manufacturer_id . "'"
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
			. " WHERE p.manufacturer_id = '" . (int)$manufacturer_id . "'"
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
	 * В админке не доступно $this->config->get('config_store_id')
	 * return NULL
	 * 
	 * $this->config->get('config_customer_group_id')
	 * return 1 (default)
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
			. " WHERE p.manufacturer_id = '" . (int)$manufacturer_id . "'"
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
			. " WHERE p.manufacturer_id = '" . (int)$manufacturer_id . "'"
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
			. " WHERE p.manufacturer_id = '" . (int)$manufacturer_id . "'"
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


	public function existFieldCategoryH1() {
		$sql = "SHOW COLUMNS FROM " . DB_PREFIX . "category_description";
		$query = $this->db->query($sql);

		foreach ($query->rows as $key => $field) {
			if ('meta_h1' == $field['Field']) {
				return true;
			}

			if ('h1' == $field['Field']) {
				return true;
			}
		}

		return false;
	}


	public function existFieldProductH1() {
		$sql = "SHOW COLUMNS FROM " . DB_PREFIX . "product_description";
		$query = $this->db->query($sql);

		foreach ($query->rows as $key => $field) {
			if ('meta_h1' == $field['Field']) {
				return true;
			}

			if ('h1' == $field['Field']) {
				return true;
			}
		}

		return false;
	}

	public function existFieldManufacturerH1() {
		$sql = "SHOW COLUMNS FROM " . DB_PREFIX . "product_description";
		$query = $this->db->query($sql);

		foreach ($query->rows as $key => $field) {
			// My modificator for h1 not hase h1 for manufacturer
			if ('meta_h1' == $field['Field']) {
				return true;
			}
		}

		return false;
	}


}
