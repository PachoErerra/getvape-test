<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ModelCatalogProduct extends Model
{
private $moved_files = array();	
private function moveProductImage($image, $product_id, $force_unique = false) {
    if (!$image) return '';

    // --- Получаем ID и название производителя ---
    $query = $this->db->query("SELECT manufacturer_id FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
    $manufacturer_id = $query->num_rows ? (int)$query->row['manufacturer_id'] : 0;

    $manufacturer_name = 'Noname';
    if ($manufacturer_id) {
        $m = $this->db->query("SELECT name FROM " . DB_PREFIX . "manufacturer WHERE manufacturer_id = '" . (int)$manufacturer_id . "' LIMIT 1");
        if ($m->num_rows) $manufacturer_name = $m->row['name'];
    }

    // --- SEO URL товара ---
    $seo = $this->db->query("SELECT keyword FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "' LIMIT 1");
    $seo_url = $seo->num_rows ? $seo->row['keyword'] : 'product-' . $product_id;

    // --- Подготовка имени производителя (транслит/нижний регистр) ---
    $manufacturer_name = $this->translit($manufacturer_name);
    $manufacturer_name = mb_strtolower($manufacturer_name, 'UTF-8');
    $manufacturer_name = preg_replace('/[^\p{L}\p{N}_-]+/u', '_', $manufacturer_name);

    // --- Расширение и базовое имя ---
    $ext = pathinfo($image, PATHINFO_EXTENSION);
    if (!$ext) $ext = 'jpg';
    $base_name = $seo_url;
    $new_dir = 'catalog/' . $manufacturer_name . '/';

	// --- Проверяем, уже ли файл в правильной папке и с корректным именем ---
	$target_pattern = '#^catalog/' . preg_quote($manufacturer_name, '#') . '/' . preg_quote($seo_url, '#') . '(-\d+)?\.[a-z0-9]+$#ui';
	if (preg_match($target_pattern, $image)) {
		// если уже лежит в правильной папке и соответствует шаблону — возвращаем как есть
		return $image;
	}
    $full_target_base = DIR_IMAGE . $new_dir;

    // --- Убедимся в существовании директории (учитываем регистр, если есть похожая) ---
    if (!is_dir($full_target_base)) {
        $base_dir = DIR_IMAGE . 'catalog/';
        if (is_dir($base_dir)) {
            $dirs = scandir($base_dir);
            foreach ($dirs as $dir) {
                if ($dir === '.' || $dir === '..') continue;
                if (is_dir($base_dir . $dir) && mb_strtolower($dir, 'UTF-8') == $manufacturer_name) {
                    // используем найденную папку (с её реальным регистром)
                    $new_dir = 'catalog/' . $dir . '/';
                    $full_target_base = DIR_IMAGE . $new_dir;
                    break;
                }
            }
        }
    }

    if (!is_dir($full_target_base)) {
        mkdir($full_target_base, 0755, true);
    }

    $old_path = DIR_IMAGE . $image;
    if (!is_file($old_path)) return $image;

    // --- Проверяем, используется ли файл в других товарах ---
    $check = $this->db->query("
        SELECT COUNT(*) AS total FROM (
            SELECT image FROM " . DB_PREFIX . "product WHERE image = '" . $this->db->escape($image) . "' AND product_id != '" . (int)$product_id . "'
            UNION ALL
            SELECT image FROM " . DB_PREFIX . "product_image WHERE image = '" . $this->db->escape($image) . "' AND product_id != '" . (int)$product_id . "'
        ) AS tmp
    ");
    $used_elsewhere = (int)$check->row['total'] > 0;

    // --- Собираем список уже существующих файлов с этим базовым именем (включая любые расширения) ---
    $globPattern = $full_target_base . $base_name . '*.*';
    $files = glob($globPattern) ?: array();

    // Учесть файлы, которые мы уже переместили в этой сессии (чтобы не допустить коллизий)
    if (!isset($this->moved_files)) $this->moved_files = array();
    foreach ($this->moved_files as $mf) {
        // $mf — путь относительно DIR_IMAGE, например 'catalog/samsung/galaxy.jpg'
        if (strpos($mf, $new_dir . $base_name) === 0) {
            $files[] = DIR_IMAGE . $mf;
        }
    }

    // --- Вычислим существующие суффиксы ---
    $maxSuffix = -1;
    $baseExists = false;
    foreach ($files as $f) {
        $name = pathinfo($f, PATHINFO_FILENAME); // без расширения
        if ($name === $base_name) {
            $baseExists = true;
            $maxSuffix = max($maxSuffix, 0);
        } elseif (preg_match('/^' . preg_quote($base_name, '/') . '-(\d+)$/', $name, $m)) {
            $num = (int)$m[1];
            if ($num > $maxSuffix) $maxSuffix = $num;
        }
    }

    // --- Выбор имени: если force_unique (для доп. изображений) — даём суффикс; 
    // иначе (для основного) пытаемся без суффикса, но не перезаписываем существующие ---
    if ($force_unique) {
        $next = ($maxSuffix === -1) ? 1 : ($maxSuffix + 1);
        $new_file = $new_dir . $base_name . '-' . $next . '.' . $ext;
    } else {
        if (!$baseExists && $maxSuffix === -1) {
            $new_file = $new_dir . $base_name . '.' . $ext;
        } else {
            $next = ($maxSuffix === -1) ? 1 : ($maxSuffix + 1);
            $new_file = $new_dir . $base_name . '-' . $next . '.' . $ext;
        }
    }

    $full_new_path = DIR_IMAGE . $new_file;

    // --- На всякий случай: если файл оказался уже занят (гонка) — найдем следующий свободный номер ---
    if (is_file($full_new_path)) {
        $counter = ($maxSuffix === -1) ? 1 : ($maxSuffix + 1);
        do {
            $new_file = $new_dir . $base_name . '-' . $counter . '.' . $ext;
            $full_new_path = DIR_IMAGE . $new_file;
            $counter++;
        } while (is_file($full_new_path));
    }

    // --- Переносим или копируем (если используется в других товарах) ---
    if ($used_elsewhere) {
        if (!copy($old_path, $full_new_path)) {
            // не удалось скопировать — оставляем как есть
            return $image;
        }
    } else {
        if (!rename($old_path, $full_new_path)) {
            if (!copy($old_path, $full_new_path)) {
                return $image;
            }
            @unlink($old_path);
        }
    }

    // --- Запомнить, что мы создали/переместили этот файл (чтобы учесть при следующих перемещениях в том же запросе) ---
    $this->moved_files[] = $new_file;

    return $new_file;
}

    public function addProduct($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "product SET isnew = '" . $this->db->escape($data['isnew']) . "', isgift = '" . $this->db->escape($data['isgift']) . "', model = '" . $this->db->escape($data['model']) . "', use_in_feed = '" . $this->db->escape($data['use_in_feed']) . "', name_for_feed = '" . $this->db->escape($data['name_for_feed']) . "', description_for_feed = '" . $this->db->escape($data['description_for_feed']) . "', product_mrp = '" . (isset($data['product_mrp']) ? $this->db->escape(json_encode($data['product_mrp'], true)) : '') . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', noindex = '" . (int)$data['noindex'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_added = NOW(), date_modified = NOW()");

        $product_id = $this->db->getLastId();

        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
        }

        foreach ($data['product_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', description2 = '" . $this->db->escape($value['description2']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_h1 = '" . $this->db->escape($value['meta_h1']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
        }

        if (isset($data['product_store'])) {
            foreach ($data['product_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        if (isset($data['product_attribute'])) {
            foreach ($data['product_attribute'] as $product_attribute) {
                if ($product_attribute['attribute_id']) {
                    // Removes duplicates
                    $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

                    foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
                        $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "' AND language_id = '" . (int)$language_id . "'");

                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" . $this->db->escape($product_attribute_description['text']) . "'");
                    }
                }
            }
        }

        if (isset($data['product_option'])) {
            foreach ($data['product_option'] as $product_option) {
                if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
                    if (isset($product_option['product_option_value'])) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");

                        $product_option_id = $this->db->getLastId();

                        foreach ($product_option['product_option_value'] as $product_option_value) {
                            $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
                        }
                    }
                } else {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
                }
            }
        }

        if (isset($data['product_recurring'])) {
            foreach ($data['product_recurring'] as $recurring) {

                $query = $this->db->query("SELECT `product_id` FROM `" . DB_PREFIX . "product_recurring` WHERE `product_id` = '" . (int)$product_id . "' AND `customer_group_id = '" . (int)$recurring['customer_group_id'] . "' AND `recurring_id` = '" . (int)$recurring['recurring_id'] . "'");

                if (!$query->num_rows) {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "product_recurring` SET `product_id` = '" . (int)$product_id . "', customer_group_id = '" . (int)$recurring['customer_group_id'] . "', `recurring_id` = '" . (int)$recurring['recurring_id'] . "'");
                }
            }
        }

        if (isset($data['product_discount'])) {
            foreach ($data['product_discount'] as $product_discount) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
            }
        }

        if (isset($data['product_special'])) {
            foreach ($data['product_special'] as $product_special) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
            }
        }

        if (isset($data['product_image'])) {
            foreach ($data['product_image'] as $product_image) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
            }
        }

        if (isset($data['product_image_feed'])) {
            foreach ($data['product_image_feed'] as $product_image_feed) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_image_feed SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image_feed['image']) . "', sort_order = '" . (int)$product_image_feed['sort_order'] . "'");
            }
        }

        if (isset($data['product_download'])) {
            foreach ($data['product_download'] as $download_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
            }
        }

        if (isset($data['product_category'])) {
            foreach ($data['product_category'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
            }
        }

        if (isset($data['main_category_id']) && $data['main_category_id'] > 0) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id = '" . (int)$data['main_category_id'] . "'");
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$data['main_category_id'] . "', main_category = 1");
        } elseif (isset($data['product_category'][0])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET main_category = 1 WHERE product_id = '" . (int)$product_id . "' AND category_id = '" . (int)$data['product_category'][0] . "'");
        }

        if (isset($data['product_filter'])) {
            foreach ($data['product_filter'] as $filter_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
            }
        }

        if (isset($data['product_related'])) {
            foreach ($data['product_related'] as $related_id) {
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
            }
        }

        if (isset($data['product_related_article'])) {
            foreach ($data['product_related_article'] as $article_id) {
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_related_article WHERE product_id = '" . (int)$product_id . "' AND article_id = '" . (int)$article_id . "'");
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_related_article SET product_id = '" . (int)$product_id . "', article_id = '" . (int)$article_id . "'");
            }
        }

        if (isset($data['product_reward'])) {
            foreach ($data['product_reward'] as $customer_group_id => $product_reward) {
                if ((int)$product_reward['points'] > 0) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$product_reward['points'] . "'");
                }
            }
        }

        // SEO URL
        if (isset($data['product_seo_url'])) {
            foreach ($data['product_seo_url'] as $store_id => $language) {
                foreach ($language as $language_id => $keyword) {
                    if (!empty($keyword)) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape(trim($keyword)) . "'");
                    }
                }
            }
        }

        if (isset($data['product_layout'])) {
            foreach ($data['product_layout'] as $store_id => $layout_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
            }
        }


        $this->cache->delete('product');

        if ($this->config->get('config_seo_pro')) {
            $this->cache->delete('seopro*');
        }

        return $product_id;
    }

    public function editProduct($product_id, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "product SET isnew = '" . $this->db->escape($data['isnew']) . "', isgift = '" . $this->db->escape($data['isgift']) . "', model = '" . $this->db->escape($data['model']) . "', use_in_feed = '" . $this->db->escape($data['use_in_feed']) . "', name_for_feed = '" . $this->db->escape($data['name_for_feed']) . "', description_for_feed = '" . $this->db->escape($data['description_for_feed']) . "', product_mrp = '" . (isset($data['product_mrp']) ? $this->db->escape(json_encode($data['product_mrp'], true)) : '') . "', sku = '" . $this->db->escape($data['sku']) . "', upc = '" . $this->db->escape($data['upc']) . "', ean = '" . $this->db->escape($data['ean']) . "', jan = '" . $this->db->escape($data['jan']) . "', isbn = '" . $this->db->escape($data['isbn']) . "', mpn = '" . $this->db->escape($data['mpn']) . "', location = '" . $this->db->escape($data['location']) . "', quantity = '" . (int)$data['quantity'] . "', minimum = '" . (int)$data['minimum'] . "', subtract = '" . (int)$data['subtract'] . "', stock_status_id = '" . (int)$data['stock_status_id'] . "', date_available = '" . $this->db->escape($data['date_available']) . "', manufacturer_id = '" . (int)$data['manufacturer_id'] . "', shipping = '" . (int)$data['shipping'] . "', price = '" . (float)$data['price'] . "', points = '" . (int)$data['points'] . "', weight = '" . (float)$data['weight'] . "', weight_class_id = '" . (int)$data['weight_class_id'] . "', length = '" . (float)$data['length'] . "', width = '" . (float)$data['width'] . "', height = '" . (float)$data['height'] . "', length_class_id = '" . (int)$data['length_class_id'] . "', status = '" . (int)$data['status'] . "', noindex = '" . (int)$data['noindex'] . "', tax_class_id = '" . (int)$data['tax_class_id'] . "', sort_order = '" . (int)$data['sort_order'] . "', date_modified = NOW() WHERE product_id = '" . (int)$product_id . "'");

/*
        if (isset($data['image'])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($data['image']) . "' WHERE product_id = '" . (int)$product_id . "'");
        }
*/

		if (isset($data['image']) && $data['image']) {
			$new_image = $this->moveProductImage($data['image'], $product_id /*, false */);
			$this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $this->db->escape($new_image) . "' WHERE product_id = '" . (int)$product_id . "'");
		}

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

        foreach ($data['product_description'] as $language_id => $value) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_description SET product_id = '" . (int)$product_id . "', language_id = '" . (int)$language_id . "', name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($value['description']) . "', description2 = '" . $this->db->escape($value['description2']) . "', tag = '" . $this->db->escape($value['tag']) . "', meta_title = '" . $this->db->escape($value['meta_title']) . "', meta_h1 = '" . $this->db->escape($value['meta_h1']) . "', meta_description = '" . $this->db->escape($value['meta_description']) . "', meta_keyword = '" . $this->db->escape($value['meta_keyword']) . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_store'])) {
            foreach ($data['product_store'] as $store_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");

        if (!empty($data['product_attribute'])) {
            foreach ($data['product_attribute'] as $product_attribute) {
                if ($product_attribute['attribute_id']) {
                    // Removes duplicates
                    $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

                    foreach ($product_attribute['product_attribute_description'] as $language_id => $product_attribute_description) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_attribute SET product_id = '" . (int)$product_id . "', attribute_id = '" . (int)$product_attribute['attribute_id'] . "', language_id = '" . (int)$language_id . "', text = '" . $this->db->escape($product_attribute_description['text']) . "'");
                    }
                }
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_option'])) {
            foreach ($data['product_option'] as $product_option) {
                if ($product_option['type'] == 'select' || $product_option['type'] == 'radio' || $product_option['type'] == 'checkbox' || $product_option['type'] == 'image') {
                    if (isset($product_option['product_option_value'])) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', required = '" . (int)$product_option['required'] . "'");

                        $product_option_id = $this->db->getLastId();

                        foreach ($product_option['product_option_value'] as $product_option_value) {
                            $this->db->query("INSERT INTO " . DB_PREFIX . "product_option_value SET product_option_value_id = '" . (int)$product_option_value['product_option_value_id'] . "', product_option_id = '" . (int)$product_option_id . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', option_value_id = '" . (int)$product_option_value['option_value_id'] . "', quantity = '" . (int)$product_option_value['quantity'] . "', subtract = '" . (int)$product_option_value['subtract'] . "', price = '" . (float)$product_option_value['price'] . "', price_prefix = '" . $this->db->escape($product_option_value['price_prefix']) . "', points = '" . (int)$product_option_value['points'] . "', points_prefix = '" . $this->db->escape($product_option_value['points_prefix']) . "', weight = '" . (float)$product_option_value['weight'] . "', weight_prefix = '" . $this->db->escape($product_option_value['weight_prefix']) . "'");
                        }
                    }
                } else {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_option SET product_option_id = '" . (int)$product_option['product_option_id'] . "', product_id = '" . (int)$product_id . "', option_id = '" . (int)$product_option['option_id'] . "', value = '" . $this->db->escape($product_option['value']) . "', required = '" . (int)$product_option['required'] . "'");
                }
            }
        }

        $this->db->query("DELETE FROM `" . DB_PREFIX . "product_recurring` WHERE product_id = " . (int)$product_id);

        if (isset($data['product_recurring'])) {
            foreach ($data['product_recurring'] as $product_recurring) {
                $query = $this->db->query("SELECT `product_id` FROM `" . DB_PREFIX . "product_recurring` WHERE `product_id` = '" . (int)$product_id . "' AND `customer_group_id` = '" . (int)$product_recurring['customer_group_id'] . "' AND `recurring_id` = '" . (int)$product_recurring['recurring_id'] . "'");

                if (!$query->num_rows) {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "product_recurring` SET `product_id` = '" . (int)$product_id . "', `customer_group_id` = '" . (int)$product_recurring['customer_group_id'] . "', `recurring_id` = '" . (int)$product_recurring['recurring_id'] . "'");
                }
            }
        }

        
        if (isset($data['product_discount'])) {
			$this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");

            foreach ($data['product_discount'] as $product_discount) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_discount SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_discount['customer_group_id'] . "', quantity = '" . (int)$product_discount['quantity'] . "', priority = '" . (int)$product_discount['priority'] . "', price = '" . (float)$product_discount['price'] . "', date_start = '" . $this->db->escape($product_discount['date_start']) . "', date_end = '" . $this->db->escape($product_discount['date_end']) . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_special'])) {
            foreach ($data['product_special'] as $product_special) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_special SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$product_special['customer_group_id'] . "', priority = '" . (int)$product_special['priority'] . "', price = '" . (float)$product_special['price'] . "', date_start = '" . $this->db->escape($product_special['date_start']) . "', date_end = '" . $this->db->escape($product_special['date_end']) . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");

/*
        if (isset($data['product_image'])) {
            foreach ($data['product_image'] as $product_image) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_image SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image['image']) . "', sort_order = '" . (int)$product_image['sort_order'] . "'");
            }
        }
*/

		if (isset($data['product_image'])) {
			foreach ($data['product_image'] as $product_image) {
				$new_image = $this->moveProductImage($product_image['image'], $product_id, true);
				$this->db->query("INSERT INTO " . DB_PREFIX . "product_image 
					SET product_id = '" . (int)$product_id . "', 
						image = '" . $this->db->escape($new_image) . "', 
						sort_order = '" . (int)$product_image['sort_order'] . "'");
			}
		}
		

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_image_feed WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_image_feed'])) {
            foreach ($data['product_image_feed'] as $product_image_feed) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_image_feed SET product_id = '" . (int)$product_id . "', image = '" . $this->db->escape($product_image_feed['image']) . "', sort_order = '" . (int)$product_image_feed['sort_order'] . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_download'])) {
            foreach ($data['product_download'] as $download_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_download SET product_id = '" . (int)$product_id . "', download_id = '" . (int)$download_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_category'])) {
            foreach ($data['product_category'] as $category_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$category_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['main_category_id']) && $data['main_category_id'] > 0) {
            $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND category_id = '" . (int)$data['main_category_id'] . "'");
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category SET product_id = '" . (int)$product_id . "', category_id = '" . (int)$data['main_category_id'] . "', main_category = 1");
        } elseif (isset($data['product_category'][0])) {
            $this->db->query("UPDATE " . DB_PREFIX . "product_to_category SET main_category = 1 WHERE product_id = '" . (int)$product_id . "' AND category_id = '" . (int)$data['product_category'][0] . "'");
        }

        if (isset($data['product_filter'])) {
            foreach ($data['product_filter'] as $filter_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_filter SET product_id = '" . (int)$product_id . "', filter_id = '" . (int)$filter_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");

        if (isset($data['product_related'])) {
            foreach ($data['product_related'] as $related_id) {
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "' AND related_id = '" . (int)$related_id . "'");
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$product_id . "', related_id = '" . (int)$related_id . "'");
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$related_id . "' AND related_id = '" . (int)$product_id . "'");
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_related SET product_id = '" . (int)$related_id . "', related_id = '" . (int)$product_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related_article WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_related_article'])) {
            foreach ($data['product_related_article'] as $article_id) {
                $this->db->query("DELETE FROM " . DB_PREFIX . "product_related_article WHERE product_id = '" . (int)$product_id . "' AND article_id = '" . (int)$article_id . "'");
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_related_article SET product_id = '" . (int)$product_id . "', article_id = '" . (int)$article_id . "'");
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_reward'])) {
            foreach ($data['product_reward'] as $customer_group_id => $value) {
                if ((int)$value['points'] > 0) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "product_reward SET product_id = '" . (int)$product_id . "', customer_group_id = '" . (int)$customer_group_id . "', points = '" . (int)$value['points'] . "'");
                }
            }
        }

        // SEO URL
        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");

        if (isset($data['product_seo_url'])) {
            foreach ($data['product_seo_url'] as $store_id => $language) {
                foreach ($language as $language_id => $keyword) {
                    if (!empty($keyword)) {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = '" . (int)$store_id . "', language_id = '" . (int)$language_id . "', query = 'product_id=" . (int)$product_id . "', keyword = '" . $this->db->escape(trim($keyword)) . "'");
                    }
                }
            }
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");

        if (isset($data['product_layout'])) {
            foreach ($data['product_layout'] as $store_id => $layout_id) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_layout SET product_id = '" . (int)$product_id . "', store_id = '" . (int)$store_id . "', layout_id = '" . (int)$layout_id . "'");
            }
        }

        $this->cache->delete('product*');
		$this->cache->delete('module*');

        if ($this->config->get('config_seo_pro')) {
            $this->cache->delete('seopro*');
        }
    }

    public function editProductStatus($product_id, $status)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "product SET status = '" . (int)$status . "', date_modified = NOW() WHERE product_id = '" . (int)$product_id . "'");

        $this->cache->delete('product');

        if ($this->config->get('config_seo_pro')) {
            $this->cache->delete('seopro');
        }

        return $product_id;
    }

    public function copyProduct($product_id)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p WHERE p.product_id = '" . (int)$product_id . "'");

        if ($query->num_rows) {
            $data = $query->row;

            $data['sku'] = '';
            $data['upc'] = '';
            $data['viewed'] = '0';
            $data['keyword'] = '';
            $data['status'] = '0';
            $data['noindex'] = '0';
            $data['use_in_feed'] = '0';

            $data['product_attribute'] = $this->getProductAttributes($product_id);
            $data['product_description'] = $this->getProductDescriptions($product_id);
            $data['product_discount'] = $this->getProductDiscounts($product_id);
            $data['product_filter'] = $this->getProductFilters($product_id);
            $data['product_image'] = $this->getProductImages($product_id);
            $data['product_image_feed'] = $this->getProductImagesFeed($product_id);
            $data['product_option'] = $this->getProductOptions($product_id);
            $data['product_related'] = $this->getProductRelated($product_id);
            $data['product_related_article'] = $this->getArticleRelated($product_id);
            $data['product_reward'] = $this->getProductRewards($product_id);
            $data['product_special'] = $this->getProductSpecials($product_id);
            $data['product_category'] = $this->getProductCategories($product_id);
            $data['product_download'] = $this->getProductDownloads($product_id);
            $data['product_layout'] = $this->getProductLayouts($product_id);
            $data['product_store'] = $this->getProductStores($product_id);
            $data['product_recurrings'] = $this->getRecurrings($product_id);

            $this->addProduct($data);
        }
    }

    public function deleteProduct($product_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "product WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_option_value WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related WHERE related_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_related_article WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "product_recurring WHERE product_id = " . (int)$product_id);
        $this->db->query("DELETE FROM " . DB_PREFIX . "review WHERE product_id = '" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "coupon_product WHERE product_id = '" . (int)$product_id . "'");
		$this->db->query("DELETE FROM " . DB_PREFIX . "product_to_1c WHERE product_id = '" . (int)$product_id . "'");

        $this->cache->delete('product');

        if ($this->config->get('config_seo_pro')) {
            $this->cache->delete('seopro');
        }
    }

    public function getProduct($product_id)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->row;
    }

    public function getProducts($data = array())
    {
        $sql = "SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (isset($data['filter_category']) && !is_null($data['filter_category'])) {
            preg_match('/(.*)(WHERE pd\.language_id.*)/', $sql, $sql_crutch_matches);
            if (isset($sql_crutch_matches[2])) {
                $sql = $sql_crutch_matches[1] . " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)" . $sql_crutch_matches[2];
            } else {
                $data['filter_category'] = null;
            }
        }

        if (!empty($data['filter_name'])) {
            $sql .= " AND pd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%' OR p.model LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_model'])) {
            $sql .= " AND p.model LIKE '%" . $this->db->escape($data['filter_model']) . "%'";
        }


        if (isset($data['filter_use_in_feed']) && !is_null($data['filter_use_in_feed'])) {
            $sql .= " AND p.use_in_feed = '" . $this->db->escape($data['filter_use_in_feed']) . "'";
        }

        if (isset($data['filter_category']) && !is_null($data['filter_category'])) {
            if (!empty($data['filter_category']) && !empty($data['filter_sub_category'])) {
                $implode_data = array();

                $this->load->model('catalog/category');

                $categories = $this->model_catalog_category->getCategoriesChildren($data['filter_category']);

                foreach ($categories as $category) {
                    $implode_data[] = "p2c.category_id = '" . (int)$category['category_id'] . "'";
                }

                $sql .= " AND (" . implode(' OR ', $implode_data) . ")";
            } else {
                if ((int)$data['filter_category'] > 0) {
                    $sql .= " AND p2c.category_id = '" . (int)$data['filter_category'] . "'";
                } else {
                    $sql .= " AND p2c.category_id IS NULL";
                }
            }
        }

        if (isset($data['filter_manufacturer_id']) && !is_null($data['filter_manufacturer_id'])) {
            $sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
        }

        if (!empty($data['filter_price'])) {
            $sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
        }

        if (isset($data['filter_price_min']) && !is_null($data['filter_price_min'])) {
            $sql .= " AND p.price >= '" . (float)$data['filter_price_min'] . "'";
        }

        if (isset($data['filter_price_max']) && !is_null($data['filter_price_max'])) {
            $sql .= " AND p.price <= '" . (float)$data['filter_price_max'] . "'";
        }

        if (isset($data['filter_quantity']) && $data['filter_quantity'] !== '') {
            $sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
        }

        if (isset($data['filter_quantity_min']) && !is_null($data['filter_quantity_min'])) {
            $sql .= " AND p.quantity >= '" . (int)$data['filter_quantity_min'] . "'";
        }

        if (isset($data['filter_quantity_max']) && !is_null($data['filter_quantity_max'])) {
            $sql .= " AND p.quantity <= '" . (int)$data['filter_quantity_max'] . "'";
        }

        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
        }

        if (isset($data['filter_noindex']) && $data['filter_noindex'] !== '') {
            $sql .= " AND p.noindex = '" . (int)$data['filter_noindex'] . "'";
        }

        $sql .= $this->getFilterNoImageSql($data);

        $sql .= " GROUP BY p.product_id";

        $sort_data = array(
            'pd.name',
            'p.model',
            'p.price',
            'p.quantity',
            'p.status',
			'p.sales',
            'p.noindex',
            'p.sort_order'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY pd.name";
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

    public function getProductsByCategoryId($category_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p2c.category_id = '" . (int)$category_id . "' ORDER BY pd.name ASC");

        return $query->rows;
    }

    public function getProductDescriptions($product_id)
    {
        $product_description_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_description WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_description_data[$result['language_id']] = array(
                'name' => $result['name'],
                'description' => $result['description'],
                'description2' => $result['description2'],
                'meta_title' => $result['meta_title'],
                'meta_h1' => $result['meta_h1'],
                'meta_description' => $result['meta_description'],
                'meta_keyword' => $result['meta_keyword'],
                'tag' => $result['tag']
            );
        }

        return $product_description_data;
    }

    public function getProductCategories($product_id)
    {
        $product_category_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_category_data[] = $result['category_id'];
        }

        return $product_category_data;
    }

    public function getProductMainCategoryId($product_id)
    {
        $query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND main_category = '1' LIMIT 1");

        return ($query->num_rows ? (int)$query->row['category_id'] : 0);
    }

    public function getProductFilters($product_id)
    {
        $product_filter_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_filter WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_filter_data[] = $result['filter_id'];
        }

        return $product_filter_data;
    }

    public function getProductAttributes($product_id)
    {
        $product_attribute_data = array();

        $product_attribute_query = $this->db->query("SELECT attribute_id FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' GROUP BY attribute_id");

        foreach ($product_attribute_query->rows as $product_attribute) {
            $product_attribute_description_data = array();

            $product_attribute_description_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_attribute WHERE product_id = '" . (int)$product_id . "' AND attribute_id = '" . (int)$product_attribute['attribute_id'] . "'");

            foreach ($product_attribute_description_query->rows as $product_attribute_description) {
                $product_attribute_description_data[$product_attribute_description['language_id']] = array('text' => $product_attribute_description['text']);
            }

            $product_attribute_data[] = array(
                'attribute_id' => $product_attribute['attribute_id'],
                'product_attribute_description' => $product_attribute_description_data
            );
        }

        return $product_attribute_data;
    }

    public function getProductOptions($product_id)
    {
        $product_option_data = array();

        $product_option_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_option` po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN `" . DB_PREFIX . "option_description` od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order ASC");

        foreach ($product_option_query->rows as $product_option) {
            $product_option_value_data = array();

            $product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON(pov.option_value_id = ov.option_value_id) WHERE pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' ORDER BY ov.sort_order ASC");

            foreach ($product_option_value_query->rows as $product_option_value) {
                $product_option_value_data[] = array(
                    'product_option_value_id' => $product_option_value['product_option_value_id'],
                    'option_value_id' => $product_option_value['option_value_id'],
                    'quantity' => $product_option_value['quantity'],
                    'subtract' => $product_option_value['subtract'],
                    'price' => $product_option_value['price'],
                    'price_prefix' => $product_option_value['price_prefix'],
                    'points' => $product_option_value['points'],
                    'points_prefix' => $product_option_value['points_prefix'],
                    'weight' => $product_option_value['weight'],
                    'weight_prefix' => $product_option_value['weight_prefix']
                );
            }

            $product_option_data[] = array(
                'product_option_id' => $product_option['product_option_id'],
                'product_option_value' => $product_option_value_data,
                'option_id' => $product_option['option_id'],
                'name' => $product_option['name'],
                'type' => $product_option['type'],
                'value' => $product_option['value'],
                'required' => $product_option['required']
            );
        }

        return $product_option_data;
    }

    public function getProductOptionValue($product_id, $product_option_value_id)
    {
        $query = $this->db->query("SELECT pov.option_value_id, ovd.name, pov.quantity, pov.subtract, pov.price, pov.price_prefix, pov.points, pov.points_prefix, pov.weight, pov.weight_prefix FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_value_id = '" . (int)$product_option_value_id . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

        return $query->row;
    }

    public function getProductImages($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC");

        return $query->rows;
    }

    public function getProductImagesFeed($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image_feed WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC");

        return $query->rows;
    }

    public function getProductDiscounts($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' ORDER BY quantity, priority, price");

        return $query->rows;
    }

    public function getProductSpecials($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_special WHERE product_id = '" . (int)$product_id . "' ORDER BY priority, price");

        return $query->rows;
    }

    public function getProductRewards($product_id)
    {
        $product_reward_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_reward_data[$result['customer_group_id']] = array('points' => $result['points']);
        }

        return $product_reward_data;
    }

    public function getProductDownloads($product_id)
    {
        $product_download_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_download WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_download_data[] = $result['download_id'];
        }

        return $product_download_data;
    }

    public function getProductStores($product_id)
    {
        $product_store_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_store WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_store_data[] = $result['store_id'];
        }

        return $product_store_data;
    }

    public function getProductSeoUrls($product_id)
    {
        $product_seo_url_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'product_id=" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_seo_url_data[$result['store_id']][$result['language_id']] = $result['keyword'];
        }

        return $product_seo_url_data;
    }

    public function getProductLayouts($product_id)
    {
        $product_layout_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_layout_data[$result['store_id']] = $result['layout_id'];
        }

        return $product_layout_data;
    }

    public function getProductRelated($product_id)
    {
        $product_related_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $product_related_data[] = $result['related_id'];
        }

        return $product_related_data;
    }

    public function getArticleRelated($product_id)
    {
        $article_related_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related_article WHERE product_id = '" . (int)$product_id . "'");

        foreach ($query->rows as $result) {
            $article_related_data[] = $result['article_id'];
        }

        return $article_related_data;
    }

    public function getRecurrings($product_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "product_recurring` WHERE product_id = '" . (int)$product_id . "'");

        return $query->rows;
    }

    public function getTotalProducts($data = array())
    {
        $sql = "SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id)";

        if (isset($data['filter_category']) && !is_null($data['filter_category'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)";
        }

        $sql .= " WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        if (!empty($data['filter_name'])) {
            $sql .= " AND pd.name LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_model'])) {
            $sql .= " AND p.model LIKE '%" . $this->db->escape($data['filter_model']) . "%'";
        }

        if (!empty($data['filter_use_in_feed'])) {
            $sql .= " AND p.use_in_feed ='" . $this->db->escape($data['filter_use_in_feed']) . "'";
        }

        if (isset($data['filter_category']) && !is_null($data['filter_category'])) {
            if (!empty($data['filter_category']) && !empty($data['filter_sub_category'])) {
                $implode_data = array();

                $this->load->model('catalog/category');

                $categories = $this->model_catalog_category->getCategoriesChildren($data['filter_category']);

                foreach ($categories as $category) {
                    $implode_data[] = "p2c.category_id = '" . (int)$category['category_id'] . "'";
                }

                $sql .= " AND (" . implode(' OR ', $implode_data) . ")";
            } else {
                if ((int)$data['filter_category'] > 0) {
                    $sql .= " AND p2c.category_id = '" . (int)$data['filter_category'] . "'";
                } else {
                    $sql .= " AND p2c.category_id IS NULL";
                }
            }
        }

        if (isset($data['filter_manufacturer_id']) && !is_null($data['filter_manufacturer_id'])) {
            $sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
        }

        if (isset($data['filter_price']) && !is_null($data['filter_price'])) {
            $sql .= " AND p.price LIKE '" . $this->db->escape($data['filter_price']) . "%'";
        }

        if (isset($data['filter_price_min']) && !is_null($data['filter_price_min'])) {
            $sql .= " AND p.price >= '" . (float)$data['filter_price_min'] . "'";
        }

        if (isset($data['filter_price_max']) && !is_null($data['filter_price_max'])) {
            $sql .= " AND p.price <= '" . (float)$data['filter_price_max'] . "'";
        }

        if (isset($data['filter_quantity']) && $data['filter_quantity'] !== '') {
            $sql .= " AND p.quantity = '" . (int)$data['filter_quantity'] . "'";
        }

        if (isset($data['filter_quantity_min']) && !is_null($data['filter_quantity_min'])) {
            $sql .= " AND p.quantity >= '" . (int)$data['filter_quantity_min'] . "'";
        }

        if (isset($data['filter_quantity_max']) && !is_null($data['filter_quantity_max'])) {
            $sql .= " AND p.quantity <= '" . (int)$data['filter_quantity_max'] . "'";
        }

        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $sql .= " AND p.status = '" . (int)$data['filter_status'] . "'";
        }

        if (isset($data['filter_noindex']) && $data['filter_noindex'] !== '') {
            $sql .= " AND p.noindex = '" . (int)$data['filter_noindex'] . "'";
        }

        $sql .= $this->getFilterNoImageSql($data);

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    private function getFilterNoImageSql($data)
    {
        if (!isset($data['filter_no_image']) || $data['filter_no_image'] === '') {
            return '';
        }

        $no_image = "(p.image IS NULL OR p.image = '' OR p.image IN ('no_image.jpg', 'no_image.png', 'placeholder.png', 'placeholder.jpg'))";

        if ((int)$data['filter_no_image'] === 1) {
            return " AND " . $no_image;
        }

        return " AND NOT " . $no_image;
    }

    public function getTotalProductsByTaxClassId($tax_class_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE tax_class_id = '" . (int)$tax_class_id . "'");

        return $query->row['total'];
    }

    public function getTotalProductsByStockStatusId($stock_status_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE stock_status_id = '" . (int)$stock_status_id . "'");

        return $query->row['total'];
    }

    public function getTotalProductsByWeightClassId($weight_class_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE weight_class_id = '" . (int)$weight_class_id . "'");

        return $query->row['total'];
    }

    public function getTotalProductsByLengthClassId($length_class_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE length_class_id = '" . (int)$length_class_id . "'");

        return $query->row['total'];
    }

    public function getTotalProductsByDownloadId($download_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_download WHERE download_id = '" . (int)$download_id . "'");

        return $query->row['total'];
    }

    public function getTotalProductsByManufacturerId($manufacturer_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product WHERE manufacturer_id = '" . (int)$manufacturer_id . "'");

        return $query->row['total'];
    }

    public function getTotalProductsByAttributeId($attribute_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_attribute WHERE attribute_id = '" . (int)$attribute_id . "'");

        return $query->row['total'];
    }

    public function getTotalProductsByOptionId($option_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_option WHERE option_id = '" . (int)$option_id . "'");

        return $query->row['total'];
    }

    public function getTotalProductsByProfileId($recurring_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_recurring WHERE recurring_id = '" . (int)$recurring_id . "'");

        return $query->row['total'];
    }

    public function getTotalProductsByLayoutId($layout_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "product_to_layout WHERE layout_id = '" . (int)$layout_id . "'");

        return $query->row['total'];
    }
	
    public function setnew($product_id, $isnew)
    {
		
		if ($isnew) {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET isnew = '" . (int)$isnew . "', date_new = NOW() WHERE product_id = '" . (int)$product_id . "'");
		} else {
			$this->db->query("UPDATE " . DB_PREFIX . "product SET isnew = '" . (int)$isnew . "', date_new = '0000-00-00 00:00:00' WHERE product_id = '" . (int)$product_id . "'");
		}
    }
    /*
        public function seturl() {

            $query = $this->db->query("SELECT p.product_id, p.model, pd.name FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ");


            foreach ($query->rows as $result) {
                $keyword = $this->translit($result['name'] . '-' . $result['model']);


                $this->db->query("INSERT INTO " . DB_PREFIX . "seo_url SET store_id = 0, language_id = 1, query = 'product_id=" . (int)$result['product_id'] . "', keyword = '" . $this->db->escape(trim($keyword)) . "'");
            }


        //	return $product_data;

        }


        private function translit($s) {
          $s = (string) $s; // преобразуем в строковое значение
          $s = strip_tags($s); // убираем HTML-теги
          $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
          $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
          $s = trim($s); // убираем пробелы в начале и конце строки
          $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
          $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
          $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
          $s = str_replace(" ", "-", $s); // заменяем пробелы знаком минус
          return $s; // возвращаем результат
        }

    */

        private function translit($s) {
			
    // Преобразуем все символы с диакритикой в ближайшие ASCII-аналоги
    $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);

    // Удалим лишние невидимые/непечатаемые символы
    $s = preg_replace('/[^\PC\s]/u', '', $s);

    // Уберем двойные пробелы
    $s = preg_replace('/\s+/', ' ', $s);

    // Обрежем пробелы по краям
			
			
          $s = (string) $s; // преобразуем в строковое значение
          $s = strip_tags($s); // убираем HTML-теги
          $s = str_replace(array("\n", "\r"), " ", $s); // убираем перевод каретки
          $s = preg_replace("/\s+/", ' ', $s); // удаляем повторяющие пробелы
          $s = trim($s); // убираем пробелы в начале и конце строки
          $s = function_exists('mb_strtolower') ? mb_strtolower($s) : strtolower($s); // переводим строку в нижний регистр (иногда надо задать локаль)
          $s = strtr($s, array('а'=>'a','б'=>'b','в'=>'v','г'=>'g','д'=>'d','е'=>'e','ё'=>'e','ж'=>'j','з'=>'z','и'=>'i','й'=>'y','к'=>'k','л'=>'l','м'=>'m','н'=>'n','о'=>'o','п'=>'p','р'=>'r','с'=>'s','т'=>'t','у'=>'u','ф'=>'f','х'=>'h','ц'=>'c','ч'=>'ch','ш'=>'sh','щ'=>'shch','ы'=>'y','э'=>'e','ю'=>'yu','я'=>'ya','ъ'=>'','ь'=>''));
          $s = preg_replace("/[^0-9a-z-_ ]/i", "", $s); // очищаем строку от недопустимых символов
          $s = str_replace(" ", "-", $s); // заменяем пробелы знаком минус
          return $s; // возвращаем результат
        }
}
