<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ModelCatalogProduct extends Model
{
    // Catalog perf optimization 2026-07-15: JOIN pricing, no N+1 getProduct(), COUNT cache
    public function updateViewed($product_id)
    {
        //$this->db->query("UPDATE " . DB_PREFIX . "product SET viewed = (viewed + 1) WHERE product_id = '" . (int)$product_id . "'");
        return; // отключаем счётчик
    }

    public function getProduct($product_id)
    {
        // Учитываем язык и магазин в ключе кеша
        $cache_key = 'product.id.' . (int)$product_id . '.' . (int)$this->config->get('config_store_id') . '.' . (int)$this->config->get('config_language_id');

        // Попробуем получить данные из кеша
        $product_data = $this->cache->get($cache_key);

        if ($product_data) {
            return $product_data;
        }
        // Если в кеше нет — делаем запрос в БД
        $query = $this->db->query("SELECT DISTINCT *, pd.name AS name, p.image, p.noindex AS noindex, m.name AS manufacturer, (SELECT price FROM " . DB_PREFIX . "product_discount pd2 WHERE pd2.product_id = p.product_id AND pd2.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND pd2.quantity = '1' AND ((pd2.date_start = '0000-00-00' OR pd2.date_start < NOW()) AND (pd2.date_end = '0000-00-00' OR pd2.date_end > NOW())) ORDER BY pd2.priority ASC, pd2.price ASC LIMIT 1) AS discount, (SELECT price FROM " . DB_PREFIX . "product_special ps WHERE ps.product_id = p.product_id AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW())) ORDER BY ps.priority ASC, ps.price ASC LIMIT 1) AS special, (SELECT points FROM " . DB_PREFIX . "product_reward pr WHERE pr.product_id = p.product_id AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "') AS reward, (SELECT ss.name FROM " . DB_PREFIX . "stock_status ss WHERE ss.stock_status_id = p.stock_status_id AND ss.language_id = '" . (int)$this->config->get('config_language_id') . "') AS stock_status, (SELECT wcd.unit FROM " . DB_PREFIX . "weight_class_description wcd WHERE p.weight_class_id = wcd.weight_class_id AND wcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS weight_class, (SELECT lcd.unit FROM " . DB_PREFIX . "length_class_description lcd WHERE p.length_class_id = lcd.length_class_id AND lcd.language_id = '" . (int)$this->config->get('config_language_id') . "') AS length_class, (SELECT AVG(rating) AS total FROM " . DB_PREFIX . "review r1 WHERE r1.product_id = p.product_id AND r1.status = '1' GROUP BY r1.product_id) AS rating, (SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review r2 WHERE r2.product_id = p.product_id AND r2.status = '1' GROUP BY r2.product_id) AS reviews, p.sort_order FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) WHERE p.product_id = '" . (int)$product_id . "' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.product_id NOT IN (" . implode(',', $this->config->get('config_march_products')) . ") AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");

        if ($query->num_rows) {
            $product_data = array(
                'product_id' => $query->row['product_id'],
                'name' => $query->row['name'],
                'description' => $query->row['description'],
                'description2' => $query->row['description2'],
                'meta_title' => $query->row['meta_title'],
                'noindex' => $query->row['noindex'],
                'meta_h1' => $query->row['meta_h1'],
                'meta_description' => $query->row['meta_description'],
                'meta_keyword' => $query->row['meta_keyword'],
                'tag' => $query->row['tag'],
                'model' => $query->row['model'],
                'sku' => $query->row['sku'],
                'upc' => $query->row['upc'],
                'ean' => $query->row['ean'],
                'jan' => $query->row['jan'],
                'isbn' => $query->row['isbn'],
                'mpn' => $query->row['mpn'],
                'location' => $query->row['location'],
                'quantity' => $query->row['quantity'],
                'quantity_sklad' => $query->row['quantity_sklad'],
                'stock_status' => $query->row['stock_status'],
                'stock_status_id' => $query->row['stock_status_id'],
                'image' => $query->row['image'],
                'manufacturer_id' => $query->row['manufacturer_id'],
                'manufacturer' => $query->row['manufacturer'],
                'price' => ($query->row['discount'] ? $query->row['discount'] : $query->row['price']),
                'special' => $query->row['special'],
                'reward' => $query->row['reward'],
                'points' => $query->row['points'],
                'tax_class_id' => $query->row['tax_class_id'],
                'date_available' => $query->row['date_available'],
                'weight' => $query->row['weight'],
                'weight_class_id' => $query->row['weight_class_id'],
                'length' => $query->row['length'],
                'width' => $query->row['width'],
                'height' => $query->row['height'],
                'length_class_id' => $query->row['length_class_id'],
                'subtract' => $query->row['subtract'],
                'rating' => round($query->row['rating']),
                'reviews' => $query->row['reviews'] ? $query->row['reviews'] : 0,
                'minimum' => $query->row['minimum'],
                'sort_order' => $query->row['sort_order'],
                'status' => $query->row['status'],
                'date_added' => $query->row['date_added'],
                'date_modified' => $query->row['date_modified'],
                'viewed' => $query->row['viewed'],
                'isnew' => $query->row['isnew'],
                'product_mrp' => json_decode($query->row['product_mrp'] ?? '[]', true) ?: [],
                'relateds' => json_decode($query->row['relateds'] ?? '[]', true) ?: [],
                'buster_products' => json_decode($query->row['buster_products'] ?? '[]', true) ?: [],
                'variants' => json_decode($query->row['variants'] ?? '[]', true) ?: [],
                'main_category' => $query->row['main_category'],
            );

            // Сохраняем в кеш
            $this->cache->set($cache_key, $product_data);
            return $product_data;
        } else {
            return false;
        }
    }

    public function getProducts($data = array())
    {
        $sql = "SELECT p.product_id, p.manufacturer_id, p.model, p.sku, p.upc, p.ean, p.jan, p.isbn, p.mpn, p.image, p.price, p.tax_class_id, p.quantity, p.quantity_sklad, p.minimum, p.isnew, p.main_category, p.variants, p.date_added, pd.name, pd.description, m.name AS manufacturer, catalog_reward.points AS reward, " . $this->getCatalogRatingReviewsSelectFields() . ", catalog_discount.price AS discount, catalog_special.price AS special";
        $sql .= $this->getCatalogProductsFromSql($data);
        $sql .= " INNER JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')";
        $sql .= " INNER JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "')";
        $sql .= " LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id)";
        $sql .= " LEFT JOIN " . DB_PREFIX . "product_reward catalog_reward ON (catalog_reward.product_id = p.product_id AND catalog_reward.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "')";
        $sql .= $this->getCatalogSortJoinsSql();
        $sql .= $this->getCatalogPriceJoinsSql();
        $sql .= " WHERE p.status = '1' AND p.date_available <= NOW()";
        $sql .= $this->getListingVisibilitySql();

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
            } else {
                $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
            }

            if (!empty($data['filter_filter'])) {
                $implode = array();

                $filters = explode(',', $data['filter_filter']);

                foreach ($filters as $filter_id) {
                    $implode[] = (int)$filter_id;
                }

                $sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
            }
        }

        if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
            $sql .= " AND (";

            if (!empty($data['filter_name'])) {
                $implode = array();

                $words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

                foreach ($words as $word) {
                    $implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }

                if (!empty($data['filter_description'])) {
                    $sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
                }
                $sql .= " OR pd.search LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
                $sql .= " OR pd.tag LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
            }

            if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
                $sql .= " OR ";
            }

            if (!empty($data['filter_tag'])) {
                $implode = array();

                $words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

                foreach ($words as $word) {
                    $implode[] = "pd.tag LIKE '%" . $this->db->escape($word) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }
            }

            if (!empty($data['filter_name'])) {
                $sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
            }

            $sql .= ")";
        }

        $sql .= " AND p.product_id NOT IN (" . implode(',', $this->config->get('config_march_products')) . ") ";

        if (!empty($data['filter_manufacturer_id'])) {
            $sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
        }

        $sql .= " GROUP BY p.product_id";

        $sort_data = array(
            'pd.name',
            'p.model',
            'p.quantity',
            'p.price',
            'rating',
            'p.sort_order',
            'p.viewed',
            'p.sales',
            'p.date_added'
        );

        if (isset($data['sort']) && $data['sort'] == 'rating' && in_array($data['sort'], $sort_data)) {
            $sql .= $this->getCatalogRatingOrderSql($data);
        } else {
            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
                    $sql .= " ORDER BY (p.quantity>0) DESC,(p.quantity_sklad>0) DESC, LCASE(" . $data['sort'] . ")";
                } elseif ($data['sort'] == 'p.price') {
                    $sql .= " ORDER BY (p.quantity>0) DESC,(p.quantity_sklad>0) DESC, (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
                } elseif ($data['sort'] == 'p.sort_order') {
                    $sql .= " ORDER BY (p.quantity>0) DESC,(p.quantity_sklad>0) DESC, " . $this->getVariantGroupSortOrderField();
                } else {
                    $sql .= " ORDER BY (p.quantity>0) DESC,(p.quantity_sklad>0) DESC," . $data['sort'];
                }
            } else {
                $sql .= " ORDER BY (p.quantity>0) DESC,(p.quantity_sklad>0) DESC, " . $this->getVariantGroupSortOrderField();
            }

            $sql .= $this->getCatalogOrderSuffix($data);
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

        $product_data = array();

        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $this->mapCatalogListingRow($result);
        }

        return $product_data;
    }

    public function getProductsForSitemap()
    {
        $query = $this->db->query("SELECT p.product_id, p.image, p.date_modified FROM " . DB_PREFIX . "product p INNER JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "') WHERE p.status = '1' AND p.date_available <= NOW() AND p.product_id NOT IN (" . implode(',', $this->config->get('config_march_products')) . ")" . $this->getListingVisibilitySql());

        return $query->rows;
    }

    public function categoryHasProducts($category_id)
    {
        $query = $this->db->query("SELECT 1 FROM " . DB_PREFIX . "product_to_category p2c INNER JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id) INNER JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "') WHERE p2c.category_id = '" . (int)$category_id . "' AND p.status = '1' AND p.date_available <= NOW() AND p.product_id NOT IN (" . implode(',', $this->config->get('config_march_products')) . ")" . $this->getListingVisibilitySql() . " LIMIT 1");

        return (bool)$query->num_rows;
    }

    public function getProductsForNeedAddByCategory(
        int   $categoryId,
        array $addProductsIds = [],
        array $excludeProductIds = []
    ): array
    {
        // Без ORDER BY RAND(): берём пул id и перемешиваем в PHP — иначе full scan на каждый ajax checkout.
        $sql = "
        SELECT DISTINCT p.product_id
        FROM " . DB_PREFIX . "product p
        INNER JOIN " . DB_PREFIX . "product_to_category p2c
            ON p.product_id = p2c.product_id
        INNER JOIN " . DB_PREFIX . "product_to_store p2s
            ON p.product_id = p2s.product_id
        WHERE p2c.category_id = " . (int)$categoryId . "
            AND p.status = 1
            AND p.stock_status_id IN (5, 7)
            AND p.date_available <= NOW()
            AND p.quantity > 0
            AND p2s.store_id = " . (int)$this->config->get('config_store_id') . $this->getListingVisibilitySql();

        if (!empty($excludeProductIds)) {
            $excludeProductIds = array_map('intval', $excludeProductIds);

            $sql .= "
            AND p.product_id NOT IN (" . implode(',', $excludeProductIds) . ")";
        }

        $sql .= "
        ORDER BY p.product_id DESC
        LIMIT 40
    ";

        $query = $this->db->query($sql);
        $productIds = array_column($query->rows, 'product_id');

        if ($productIds) {
            shuffle($productIds);
            $productIds = array_slice($productIds, 0, 10);
        }

        $products = [];

        foreach ($productIds as $productId) {
            $product = $this->getProduct((int)$productId);

            if ($product) {
                $products[(int)$productId] = $product;
            }
        }

        if ($addProductsIds) {
            foreach ($addProductsIds as $productId) {
                $productId = (int)$productId;

                if (isset($products[$productId])) {
                    continue;
                }

                $product = $this->getProduct($productId);

                if ($product && in_array((int)$product['stock_status_id'], [5, 7], true) && (int)$product['quantity'] > 0) {
                    $products[$productId] = $product;
                }
            }
        }

        return $products;
    }

    public function getProductSpecials($data = array())
    {
        $sql = "SELECT DISTINCT ps.product_id, p.manufacturer_id, p.model, p.sku, p.upc, p.ean, p.jan, p.isbn, p.mpn, p.image, p.price, p.tax_class_id, p.quantity, p.quantity_sklad, p.minimum, p.isnew, p.main_category, p.variants, p.date_added, pd.name, pd.description, m.name AS manufacturer, catalog_reward.points AS reward, " . $this->getCatalogRatingReviewsSelectFields() . ", catalog_discount.price AS discount, ps.price AS special FROM " . DB_PREFIX . "product_special ps INNER JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) INNER JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "') INNER JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "') LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) LEFT JOIN " . DB_PREFIX . "product_reward catalog_reward ON (catalog_reward.product_id = p.product_id AND catalog_reward.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "')";
        $sql .= $this->getCatalogSortJoinsSql();
        $sql .= $this->getCatalogDiscountJoinSql();
        $sql .= " WHERE p.status = '1' AND (p.quantity > '0' OR p.quantity_sklad > '0') AND p.date_available <= NOW() AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND p.product_id NOT IN (" . implode(',', $this->config->get('config_march_products')) . ") AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))" . $this->getListingVisibilitySql() . " GROUP BY ps.product_id";

        $sort_data = array(
            'pd.name',
            'p.model',
            'ps.price',
            'rating',
            'p.sort_order'
        );
        if (isset($data['sort']) && $data['sort'] == 'rating' && in_array($data['sort'], $sort_data)) {
            $sql .= $this->getCatalogRatingOrderSql($data);
        } else {
            if ($data['sort'] == 'default') {
                $sql .= " ORDER BY p.sort_special";
            } elseif ($data['sort'] == 'special_percent') {
                $sql .= " ORDER BY ((p.price - ps.price) / (p.price / 100))";
            } elseif (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
                    $sql .= " ORDER BY (p.quantity>0) DESC,(p.quantity_sklad>0) DESC, LCASE(" . $data['sort'] . ")";
                } elseif ($data['sort'] == 'p.price') {
                    $sql .= " ORDER BY (p.quantity>0) DESC,(p.quantity_sklad>0) DESC, (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
                } elseif ($data['sort'] == 'p.sort_order') {
                    $sql .= " ORDER BY (p.quantity>0) DESC,(p.quantity_sklad>0) DESC, " . $this->getVariantGroupSortOrderField();
                } else {
                    $sql .= " ORDER BY (p.quantity>0) DESC,(p.quantity_sklad>0) DESC," . $data['sort'];
                }
            } else {
                $sql .= " ORDER BY (p.quantity>0) DESC,(p.quantity_sklad>0) DESC, " . $this->getVariantGroupSortOrderField();
            }

            $sql .= $this->getCatalogOrderSuffix($data);
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

        $product_data = array();

        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $this->mapCatalogListingRow($result);
        }

        return $product_data;
    }

    public function getProductlatests($data = array())
    {
        $sql = "SELECT p.product_id, p.manufacturer_id, p.model, p.sku, p.upc, p.ean, p.jan, p.isbn, p.mpn, p.image, p.price, p.tax_class_id, p.quantity, p.quantity_sklad, p.minimum, p.isnew, p.main_category, p.variants, p.date_added, pd.name, pd.description, m.name AS manufacturer, catalog_reward.points AS reward, " . $this->getCatalogRatingReviewsSelectFields() . ", catalog_discount.price AS discount, catalog_special.price AS special FROM " . DB_PREFIX . "product p INNER JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "') INNER JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "') LEFT JOIN " . DB_PREFIX . "manufacturer m ON (p.manufacturer_id = m.manufacturer_id) LEFT JOIN " . DB_PREFIX . "product_reward catalog_reward ON (catalog_reward.product_id = p.product_id AND catalog_reward.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "')";
        $sql .= $this->getCatalogSortJoinsSql();
        $sql .= $this->getCatalogPriceJoinsSql();
        $sql .= " WHERE p.status = '1' AND p.isnew = '1' AND p.product_id NOT IN (" . implode(',', $this->config->get('config_march_products')) . ") AND p.date_available <= NOW()" . $this->getListingVisibilitySql() . " GROUP BY p.product_id";

        $sort_data = array(
            'pd.name',
            'p.model',
            'p.price',
            'rating',
            'p.sort_order'
        );

        if (isset($data['sort']) && $data['sort'] == 'rating' && in_array($data['sort'], $sort_data)) {
            $sql .= $this->getCatalogRatingOrderSql($data);
        } else {
            if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
                if ($data['sort'] == 'pd.name' || $data['sort'] == 'p.model') {
                    $sql .= " ORDER BY (p.quantity>0) DESC,(p.quantity_sklad>0) DESC, LCASE(" . $data['sort'] . ")";
                } elseif ($data['sort'] == 'p.price') {
                    $sql .= " ORDER BY (p.quantity>0) DESC,(p.quantity_sklad>0) DESC, (CASE WHEN special IS NOT NULL THEN special WHEN discount IS NOT NULL THEN discount ELSE p.price END)";
                } elseif ($data['sort'] == 'p.sort_order') {
                    $sql .= " ORDER BY (p.quantity>0) DESC,(p.quantity_sklad>0) DESC, " . $this->getVariantGroupSortOrderField();
                } else {
                    $sql .= " ORDER BY (p.quantity>0) DESC,(p.quantity_sklad>0) DESC," . $data['sort'];
                }
            } else {
                $sql .= " ORDER BY (p.quantity>0) DESC,(p.quantity_sklad>0) DESC, " . $this->getVariantGroupSortOrderField();
            }

            $sql .= $this->getCatalogOrderSuffix($data);
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

        $product_data = array();

        $query = $this->db->query($sql);

        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $this->mapCatalogListingRow($result);
        }

        return $product_data;
    }

    public function getLatestProducts($limit)
    {
        $product_data = $this->cache->get('product.latest.vis.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit);

        if (!$product_data) {
            $query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1'  AND p.product_id NOT IN (" . implode(',', $this->config->get('config_march_products')) . ") AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'" . $this->getListingVisibilitySql() . " ORDER BY p.date_added DESC LIMIT " . (int)$limit);

            foreach ($query->rows as $result) {
                $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
            }

            $this->cache->set('product.latest.vis.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit, $product_data);
        }

        return $product_data;
    }

    public function getLatestProductsNew($limit)
    {
        //	$product_data = $this->cache->get('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit);

        //	if (!$product_data) {
        $product_data = array();

        $query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1'  AND p.product_id NOT IN (" . implode(',', $this->config->get('config_march_products')) . ") AND p.isnew = '1' AND p.quantity > '0' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'" . $this->getListingVisibilitySql() . " ORDER BY p.date_added DESC LIMIT " . (int)$limit);

        foreach ($query->rows as $result) {
            $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
        }

        //	$this->cache->set('product.latest.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit, $product_data);
        //	}

        return $product_data;
    }

    public function getPopularProducts($limit)
    {
        $product_data = $this->cache->get('product.popular.vis.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit);

        if (!$product_data) {
            $query = $this->db->query("SELECT p.product_id FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1'  AND p.product_id NOT IN (" . implode(',', $this->config->get('config_march_products')) . ") AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'" . $this->getListingVisibilitySql() . " ORDER BY p.viewed DESC, p.date_added DESC LIMIT " . (int)$limit);

            foreach ($query->rows as $result) {
                $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
            }

            $this->cache->set('product.popular.vis.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit, $product_data);
        }

        return $product_data;
    }

    public function getBestSellerProducts($limit)
    {
        $product_data = $this->cache->get('product.bestseller.vis.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit);

        if (!$product_data) {
            $product_data = array();

            $query = $this->db->query("SELECT op.product_id, SUM(op.quantity) AS total FROM " . DB_PREFIX . "order_product op LEFT JOIN `" . DB_PREFIX . "order` o ON (op.order_id = o.order_id) LEFT JOIN `" . DB_PREFIX . "product` p ON (op.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE o.order_status_id > '0' AND p.status = '1' AND p.date_available <= NOW()  AND p.product_id NOT IN (" . implode(',', $this->config->get('config_march_products')) . ") AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'" . $this->getListingVisibilitySql() . " GROUP BY op.product_id ORDER BY total DESC LIMIT " . (int)$limit);

            foreach ($query->rows as $result) {
                $product_data[$result['product_id']] = $this->getProduct($result['product_id']);
            }

            $this->cache->set('product.bestseller.vis.' . (int)$this->config->get('config_language_id') . '.' . (int)$this->config->get('config_store_id') . '.' . $this->config->get('config_customer_group_id') . '.' . (int)$limit, $product_data);
        }

        return $product_data;
    }

    public function getProductAttributes($product_id)
    {
        $product_attribute_group_data = array();

        $product_attribute_group_query = $this->db->query("SELECT ag.attribute_group_id, agd.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_group ag ON (a.attribute_group_id = ag.attribute_group_id) LEFT JOIN " . DB_PREFIX . "attribute_group_description agd ON (ag.attribute_group_id = agd.attribute_group_id) WHERE pa.product_id = '" . (int)$product_id . "' AND agd.language_id = '" . (int)$this->config->get('config_language_id') . "' GROUP BY ag.attribute_group_id ORDER BY ag.sort_order, agd.name");

        foreach ($product_attribute_group_query->rows as $product_attribute_group) {
            $product_attribute_data = array();

            $product_attribute_query = $this->db->query("SELECT a.attribute_id, ad.name, pa.text FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute a ON (pa.attribute_id = a.attribute_id) LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (a.attribute_id = ad.attribute_id) WHERE pa.product_id = '" . (int)$product_id . "' AND a.attribute_group_id = '" . (int)$product_attribute_group['attribute_group_id'] . "' AND ad.language_id = '" . (int)$this->config->get('config_language_id') . "' AND pa.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY a.sort_order, ad.name");

            foreach ($product_attribute_query->rows as $product_attribute) {
                $product_attribute_data[] = array(
                    'attribute_id' => $product_attribute['attribute_id'],
                    'name' => $product_attribute['name'],
                    'text' => $product_attribute['text']
                );
            }

            $product_attribute_group_data[] = array(
                'attribute_group_id' => $product_attribute_group['attribute_group_id'],
                'name' => $product_attribute_group['name'],
                'attribute' => $product_attribute_data
            );
        }

        return $product_attribute_group_data;
    }

    public function getProductOptions($product_id)
    {
        $product_option_data = array();

        $product_option_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option po LEFT JOIN `" . DB_PREFIX . "option` o ON (po.option_id = o.option_id) LEFT JOIN " . DB_PREFIX . "option_description od ON (o.option_id = od.option_id) WHERE po.product_id = '" . (int)$product_id . "' AND od.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY o.sort_order");

        foreach ($product_option_query->rows as $product_option) {
            $product_option_value_data = array();

            $product_option_value_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_option_value pov LEFT JOIN " . DB_PREFIX . "option_value ov ON (pov.option_value_id = ov.option_value_id) LEFT JOIN " . DB_PREFIX . "option_value_description ovd ON (ov.option_value_id = ovd.option_value_id) WHERE pov.product_id = '" . (int)$product_id . "' AND pov.product_option_id = '" . (int)$product_option['product_option_id'] . "' AND ovd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY ov.sort_order");

            foreach ($product_option_value_query->rows as $product_option_value) {
                $product_option_value_data[] = array(
                    'product_option_value_id' => $product_option_value['product_option_value_id'],
                    'option_value_id' => $product_option_value['option_value_id'],
                    'name' => $product_option_value['name'],
                    'image' => $product_option_value['image'],
                    'quantity' => $product_option_value['quantity'],
                    'subtract' => $product_option_value['subtract'],
                    'price' => $product_option_value['price'],
                    'reward' => $product_option_value['points'],
                    'reward_prefix' => $product_option_value['points_prefix'],
                    'price_prefix' => $product_option_value['price_prefix'],
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

    public function getProductDiscounts($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_discount WHERE product_id = '" . (int)$product_id . "' AND customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' AND quantity > 1 AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) ORDER BY quantity ASC, priority ASC, price ASC");

        return $query->rows;
    }

    public function getProductImages($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_image WHERE product_id = '" . (int)$product_id . "' ORDER BY sort_order ASC");

        return $query->rows;
    }

    public function getProductRelated($product_id)
    {
        $product_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related pr LEFT JOIN " . DB_PREFIX . "product p ON (pr.related_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pr.product_id = '" . (int)$product_id . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'" . $this->getListingVisibilitySql());

        foreach ($query->rows as $result) {
            $product_data[$result['related_id']] = $this->getProduct($result['related_id']);
        }

        return $product_data;
    }

    public function getProductRelated1C($product_id)
    {
        $product_data = array();

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_related_1c pr LEFT JOIN " . DB_PREFIX . "product p ON (pr.related_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE pr.product_id = '" . (int)$product_id . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'" . $this->getListingVisibilitySql());

        foreach ($query->rows as $result) {
            $product_data[$result['related_id']] = $this->getProduct($result['related_id']);
        }

        return $product_data;
    }

    public function getProductLayoutId($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_layout WHERE product_id = '" . (int)$product_id . "' AND store_id = '" . (int)$this->config->get('config_store_id') . "'");

        if ($query->num_rows) {
            return (int)$query->row['layout_id'];
        } else {
            return 0;
        }
    }

    public function getCategories($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

        return $query->rows;
    }

    public function getCategoriesId($product_id)
    {
        $query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "'");

        $category_data = array();

        foreach ($query->rows as $result) {
            $category_data[] = $result['category_id'];
        }
        return $category_data;
    }

    public function getTotalProducts($data = array())
    {
        if ($this->canCacheTotalProducts($data)) {
            $cache_key = $this->getTotalProductsCacheKey($data);
            $cached_total = $this->cache->get($cache_key);

            if ($cached_total !== false && $cached_total !== null) {
                return (int)$cached_total;
            }
        }

        $needs_description = !empty($data['filter_name']) || !empty($data['filter_tag']);
        $sql = "SELECT COUNT(DISTINCT p.product_id) AS total";
        $sql .= $this->getCatalogProductsFromSql($data);

        if ($needs_description) {
            $sql .= " INNER JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "')";
        }

        $sql .= " INNER JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "')";
        $sql .= " WHERE p.status = '1' AND p.date_available <= NOW()";
        $sql .= $this->getListingVisibilitySql();

        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $sql .= " AND cp.path_id = '" . (int)$data['filter_category_id'] . "'";
            } else {
                $sql .= " AND p2c.category_id = '" . (int)$data['filter_category_id'] . "'";
            }

            if (!empty($data['filter_filter'])) {
                $implode = array();

                $filters = explode(',', $data['filter_filter']);

                foreach ($filters as $filter_id) {
                    $implode[] = (int)$filter_id;
                }

                $sql .= " AND pf.filter_id IN (" . implode(',', $implode) . ")";
            }
        }

        if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
            $sql .= " AND (";

            if (!empty($data['filter_name'])) {
                $implode = array();

                $words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_name'])));

                foreach ($words as $word) {
                    $implode[] = "pd.name LIKE '%" . $this->db->escape($word) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }

                if (!empty($data['filter_description'])) {
                    $sql .= " OR pd.description LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
                }
                $sql .= " OR pd.search LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
                $sql .= " OR pd.tag LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
            }

            if (!empty($data['filter_name']) && !empty($data['filter_tag'])) {
                $sql .= " OR ";
            }

            if (!empty($data['filter_tag'])) {
                $implode = array();

                $words = explode(' ', trim(preg_replace('/\s+/', ' ', $data['filter_tag'])));

                foreach ($words as $word) {
                    $implode[] = "pd.tag LIKE '%" . $this->db->escape($word) . "%'";
                }

                if ($implode) {
                    $sql .= " " . implode(" AND ", $implode) . "";
                }
            }

            if (!empty($data['filter_name'])) {
                $sql .= " OR LCASE(p.model) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.sku) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.upc) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.ean) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.jan) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.isbn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
                $sql .= " OR LCASE(p.mpn) = '" . $this->db->escape(utf8_strtolower($data['filter_name'])) . "'";
            }

            $sql .= ")";
        }
        $sql .= " AND p.product_id NOT IN (" . implode(',', $this->config->get('config_march_products')) . ") ";
        if (!empty($data['filter_manufacturer_id'])) {
            $sql .= " AND p.manufacturer_id = '" . (int)$data['filter_manufacturer_id'] . "'";
        }

        $query = $this->db->query($sql);
        $total = (int)$query->row['total'];

        if ($this->canCacheTotalProducts($data)) {
            $this->cache->set($this->getTotalProductsCacheKey($data), $total);
        }

        return $total;
    }

    public function getProfile($product_id, $recurring_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "recurring r JOIN " . DB_PREFIX . "product_recurring pr ON (pr.recurring_id = r.recurring_id AND pr.product_id = '" . (int)$product_id . "') WHERE pr.recurring_id = '" . (int)$recurring_id . "' AND status = '1' AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'");

        return $query->row;
    }

    public function getProfiles($product_id)
    {
        $query = $this->db->query("SELECT rd.* FROM " . DB_PREFIX . "product_recurring pr JOIN " . DB_PREFIX . "recurring_description rd ON (rd.language_id = " . (int)$this->config->get('config_language_id') . " AND rd.recurring_id = pr.recurring_id) JOIN " . DB_PREFIX . "recurring r ON r.recurring_id = rd.recurring_id WHERE pr.product_id = " . (int)$product_id . " AND status = '1' AND pr.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "' ORDER BY sort_order ASC");

        return $query->rows;
    }

    public function getTotalProductSpecials()
    {
        // OCFilter: OCMOD search `getTotalProductSpecials() {` does not match this brace style,
        // so keep the hook in source - otherwise pagination ignores selected filters.
        if ($this->registry->get('ocfilter') && $this->ocfilter->startup() && $this->ocfilter->api->isSelected()) {
            return $this->ocfilter->api->getTotalProductSpecials();
        }

        $query = $this->db->query("SELECT COUNT(DISTINCT ps.product_id) AS total FROM " . DB_PREFIX . "product_special ps LEFT JOIN " . DB_PREFIX . "product p ON (ps.product_id = p.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.date_available <= NOW() AND (p.quantity > '0' OR p.quantity_sklad > '0') AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND ps.customer_group_id = '" . (int)$this->config->get('config_customer_group_id') . "'  AND p.product_id NOT IN (" . implode(',', $this->config->get('config_march_products')) . ") AND ((ps.date_start = '0000-00-00' OR ps.date_start < NOW()) AND (ps.date_end = '0000-00-00' OR ps.date_end > NOW()))" . $this->getListingVisibilitySql());

        if (isset($query->row['total'])) {
            return $query->row['total'];
        } else {
            return 0;
        }
    }

    public function getTotalProductlatests()
    {
        $query = $this->db->query("SELECT COUNT(DISTINCT p.product_id) AS total FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.status = '1' AND p.quantity > '0'AND p.status = '1' AND p.date_available <= NOW()  AND p.product_id NOT IN (" . implode(',', $this->config->get('config_march_products')) . ") AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "' AND p.isnew = '1'" . $this->getListingVisibilitySql());

        if (isset($query->row['total'])) {
            return $query->row['total'];
        } else {
            return 0;
        }
    }

    public function getWarehouses($product_id)
    {
        $warehouse_data = array();
        $warehouses = array();

        $query = $this->db->query("SELECT warehouse_id FROM " . DB_PREFIX . "product_quantity WHERE product_id = " . (int)$product_id . "");

        foreach ($query->rows as $result) {
            $warehouses[] = $result['warehouse_id'];
        }

        if ($query->num_rows) {
            $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "location WHERE location_id IN (" . implode(',', $warehouses) . ") ORDER BY city ASC");

            $warehouse_data['count'] = count($query->rows);
            foreach ($query->rows as $result) {
                $warehouse_data[$result['city']][] = $result;
            }
        }

        return $warehouse_data;
    }


    public function getTypeattribute($category_id, $sql)
    {


        $query = $this->db->query("SELECT product_id,attribute_text FROM " . DB_PREFIX . "product_types WHERE category_id = " . (int)$category_id . " " . $sql . " ");

        //	$query = $this->db->query("SELECT product_id,attribute_text FROM " . DB_PREFIX . "product_types WHERE category_id IN(568,571) " . $sql . "");

        return $query->rows;
    }

    public function getType($category_id, $product_id)
    {

        $array = array();
        $query = $this->db->query("SELECT DISTINCT pt.attribute_id FROM " . DB_PREFIX . "product_types pt LEFT JOIN " . DB_PREFIX . "attribute pa ON (pt.attribute_id = pa.attribute_id) WHERE pt.category_id = " . (int)$category_id . " AND pt.product_id = " . (int)$product_id . " AND pt.attribute_id !=42 ORDER BY pa.sort_order ASC");

        if ($query->num_rows) {

            $queryattrall = $this->db->query("SELECT text, attribute_id FROM " . DB_PREFIX . "product_attribute WHERE product_id = " . (int)$product_id . " AND attribute_id !=42 ORDER BY attribute_id ASC");

            foreach ($queryattrall->rows as $result) {
                $attributes[$result['attribute_id']] = $result['text'];
            }

            if ($query->num_rows == 1) {
                foreach ($query->rows as $result) {
                    $sql = array();
                    foreach ($attributes as $key => $attr) {
                        $sql[] = "'" . $attr . "'";
                    }

                    $queryattr = $this->db->query("SELECT text FROM " . DB_PREFIX . "product_attribute WHERE product_id = " . (int)$product_id . " AND attribute_id = " . (int)$result['attribute_id'] . " AND attribute_id !=42 ");

                    if ($queryattr->row['text']) {

                        $query2 = $this->db->query("SELECT DISTINCT pa.product_id,pa.text FROM " . DB_PREFIX . "product_attribute pa 
						LEFT JOIN " . DB_PREFIX . "product_types pt ON (pa.product_id = pt.product_id) WHERE pt.category_id = " . (int)$category_id . " AND pa.attribute_id !=42 
						 GROUP BY pa.product_id 
						 ");

                        foreach ($query2->rows as $result2) {
                            $array[$result['attribute_id']][] = $result2['product_id'];
                        }
                    }
                }
            } else {
                foreach ($query->rows as $result) {
                    $sql = array();
                    foreach ($attributes as $key => $attr) {
                        if ($key != $result['attribute_id']) {
                            //	$sql[] = "'".$attr."'";
                            $sql[] = "'" . str_replace("'", "''", $attr) . "'";
                        }
                    }

                    $queryattr = $this->db->query("SELECT text FROM " . DB_PREFIX . "product_attribute WHERE product_id = " . (int)$product_id . " AND attribute_id = " . (int)$result['attribute_id'] . " AND attribute_id !=42 ");

                    if ($queryattr->row['text']) {


                        //радобраться с категориями
                        $query2 = $this->db->query("SELECT DISTINCT pa.product_id,pa.text FROM " . DB_PREFIX . "product_attribute pa 
						LEFT JOIN " . DB_PREFIX . "product_types pt ON (pa.product_id = pt.product_id) WHERE pt.category_id = " . (int)$category_id . " AND pa.attribute_id !=42 AND
						 pa.text IN (" . implode(',', $sql) . ")   
						 GROUP BY pa.product_id 
						having count(distinct pa.text) = " . (count($query->rows) - 1) . "

						 ");
                        foreach ($query2->rows as $result2) {
                            $array[$result['attribute_id']][] = $result2['product_id'];
                        }
                    }
                }
            }
        }

        return $array;

    }

    public function getProductVariantGroups($product_id)
    {
        $product_id = (int)$product_id;
        $language_id = (int)$this->config->get('config_language_id');
        $cache_key = 'product.variant_groups.' . $language_id . '.' . $product_id;
        $cached = $this->cache->get($cache_key);

        if ($cached !== false && $cached !== null) {
            return $cached;
        }

        $category_id = $this->getVariantGroupCategoryId($product_id);

        if (!$category_id) {
            $this->cache->set($cache_key, array());
            return array();
        }

        $sibling_ids = $this->getVariantSiblingIds($category_id);

        if (!$sibling_ids || !in_array($product_id, $sibling_ids, true)) {
            $sibling_ids[] = $product_id;
        }

        $sibling_ids = array_values(array_unique(array_map('intval', $sibling_ids)));
        $products = $this->getVariantSiblingProducts($sibling_ids);

        if (count($products) < 2 || empty($products[$product_id])) {
            $this->cache->set($cache_key, array());
            return array();
        }

        $attributes = $this->getVariantSiblingAttributes($sibling_ids, $language_id);
        $current_attrs = isset($attributes[$product_id]) ? $attributes[$product_id] : array();

        if (!$current_attrs) {
            $this->cache->set($cache_key, array());
            return array();
        }

        $lock_keys = array();
        $show_attrs = array();

        foreach ($current_attrs as $attribute_id => $attr) {
            $kind = $this->classifyVariantAttribute($attribute_id, $attr['name']);

            if (!$kind) {
                continue;
            }

            $lock_keys[$kind] = $attribute_id;

            if (in_array($kind, array('color', 'volume', 'flavor'), true)) {
                $show_attrs[$kind] = array(
                    'attribute_id' => $attribute_id,
                    'label' => $attr['name'],
                    'kind' => $kind
                );
            }
        }

        $groups = array();
        $order = array('color', 'volume', 'flavor');

        foreach ($order as $kind) {
            if (empty($show_attrs[$kind])) {
                continue;
            }

            $attribute_id = $show_attrs[$kind]['attribute_id'];
            $seen_values = array();
            $items = array();

            foreach ($products as $sibling_id => $product) {
                if (empty($attributes[$sibling_id][$attribute_id]['text'])) {
                    continue;
                }

                if (!$this->variantLocksMatch($current_attrs, isset($attributes[$sibling_id]) ? $attributes[$sibling_id] : array(), $lock_keys, $kind)) {
                    continue;
                }

                $value = trim($attributes[$sibling_id][$attribute_id]['text']);
                $value_key = utf8_strtolower($value);
                $in_stock = ((int)$product['quantity'] + (int)$product['quantity_sklad']) > 0;

                if (isset($seen_values[$value_key])) {
                    $existing_index = $seen_values[$value_key];

                    if ((int)$sibling_id === $product_id || ($in_stock && empty($items[$existing_index]['in_stock']))) {
                        $items[$existing_index] = $this->buildVariantItem($product, $value, $kind, $current_attrs);
                        $seen_values[$value_key] = $existing_index;
                    }

                    continue;
                }

                $seen_values[$value_key] = count($items);
                $items[] = $this->buildVariantItem($product, $value, $kind, $current_attrs);
            }

            if (count($items) <= 1) {
                continue;
            }

            usort($items, array($this, 'sortVariantItems'));

            $groups[] = array(
                'label' => $show_attrs[$kind]['label'],
                'display' => $kind,
                'items' => $items
            );
        }

        $this->cache->set($cache_key, $groups);

        return $groups;
    }

    public function getListingVariantRelated($product)
    {
        $language_id = (int)$this->config->get('config_language_id');
        $related = array();

        if (!empty($product['main_category'])) {
            $cache_key = 'product.variants.' . $language_id . '.' . (int)$product['main_category'];
            $related = $this->cache->get($cache_key);

            if (!$related && !empty($product['variants'][$language_id])) {
                $related = $this->formatListingVariantsFromJson($product['variants'][$language_id]);
                $this->cache->set($cache_key, $related ?: array());
            }
        }

        if ($related) {
            return $related;
        }

        $groups = $this->getProductVariantGroups((int)$product['product_id']);
        $picked = null;

        foreach ($groups as $group) {
            if ($group['display'] === 'flavor') {
                $picked = $group;
                break;
            }
        }

        if (!$picked) {
            foreach ($groups as $group) {
                if ($group['display'] === 'color') {
                    $picked = $group;
                    break;
                }
            }
        }

        if (!$picked) {
            return array();
        }

        return $this->formatListingVariantsFromGroup($picked);
    }

    private function formatListingVariantsFromJson($variants)
    {
        $related = array();

        foreach ($variants as $name => $type) {
            foreach ($type as $product_id => $item) {
                $related[$name][] = $this->formatListingVariantItem((int)$product_id, $item['name'], $this->getProductimage($product_id));
            }

            if (!empty($related[$name])) {
                usort($related[$name], array($this, 'sortListingVariantItems'));
            }

            if (empty($related[$name]) || count($related[$name]) <= 1) {
                unset($related[$name]);
            }
        }

        return $related;
    }

    private function formatListingVariantsFromGroup($group)
    {
        $items = array();

        foreach ($group['items'] as $item) {
            $items[] = $this->formatListingVariantItem(
                (int)$item['product_id'],
                $item['name'],
                !empty($item['image']) ? $item['image'] : '',
                !empty($item['alt']) ? $item['alt'] : $item['name']
            );
        }

        if (count($items) <= 1) {
            return array();
        }

        return array(
            $group['label'] => $items
        );
    }

    private function formatListingVariantItem($product_id, $name, $image, $alt = '')
    {
        $this->load->model('tool/image');

        if ($image) {
            $thumb = $this->model_tool_image->resize($image, 96, 96);
            $bigthumb = $this->model_tool_image->resize(
                $image,
                $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
                $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')
            );
        } else {
            $thumb = $this->model_tool_image->resize('placeholder.png', 96, 96);
            $bigthumb = $this->model_tool_image->resize(
                'placeholder.png',
                $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'),
                $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height')
            );
        }

        return array(
            'name' => $name,
            'thumb' => $thumb,
            'bigthumb' => $bigthumb,
            'product_id' => $product_id,
            'href' => $this->url->link('product/product', 'product_id=' . $product_id),
            'alt' => $alt ? $alt : $name
        );
    }

    private function sortListingVariantItems($a, $b)
    {
        return strnatcasecmp((string)$a['name'], (string)$b['name']);
    }

    private function getVariantGroupCategoryId($product_id)
    {
        $query = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "product_types WHERE product_id = '" . (int)$product_id . "' LIMIT 1");

        if ($query->num_rows) {
            return (int)$query->row['category_id'];
        }

        return (int)$this->getMainCategory($product_id);
    }

    private function getVariantSiblingIds($category_id)
    {
        $query = $this->db->query("SELECT DISTINCT product_id FROM " . DB_PREFIX . "product_types WHERE category_id = '" . (int)$category_id . "'");

        if ($query->num_rows) {
            $ids = array();

            foreach ($query->rows as $row) {
                $ids[] = (int)$row['product_id'];
            }

            return $ids;
        }

        $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product_to_category WHERE category_id = '" . (int)$category_id . "'");
        $ids = array();

        foreach ($query->rows as $row) {
            $ids[] = (int)$row['product_id'];
        }

        return $ids;
    }

    private function getVariantSiblingProducts($product_ids)
    {
        $product_ids = array_filter(array_map('intval', $product_ids));

        if (!$product_ids) {
            return array();
        }

        $query = $this->db->query("SELECT p.product_id, p.image, p.quantity, p.quantity_sklad, pd.name FROM " . DB_PREFIX . "product p LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) LEFT JOIN " . DB_PREFIX . "product_to_store p2s ON (p.product_id = p2s.product_id) WHERE p.product_id IN (" . implode(',', $product_ids) . ") AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' AND p.status = '1' AND p.date_available <= NOW() AND p2s.store_id = '" . (int)$this->config->get('config_store_id') . "'");
        $products = array();

        foreach ($query->rows as $row) {
            $products[(int)$row['product_id']] = $row;
        }

        return $products;
    }

    private function getVariantSiblingAttributes($product_ids, $language_id)
    {
        $product_ids = array_filter(array_map('intval', $product_ids));

        if (!$product_ids) {
            return array();
        }

        $query = $this->db->query("SELECT pa.product_id, pa.attribute_id, pa.text, ad.name FROM " . DB_PREFIX . "product_attribute pa LEFT JOIN " . DB_PREFIX . "attribute_description ad ON (pa.attribute_id = ad.attribute_id AND ad.language_id = pa.language_id) WHERE pa.product_id IN (" . implode(',', $product_ids) . ") AND pa.language_id = '" . (int)$language_id . "' AND pa.attribute_id != '42' AND pa.text <> ''");
        $attributes = array();

        foreach ($query->rows as $row) {
            $attributes[(int)$row['product_id']][(int)$row['attribute_id']] = array(
                'name' => $row['name'],
                'text' => trim($row['text'])
            );
        }

        return $attributes;
    }

    private function classifyVariantAttribute($attribute_id, $name)
    {
        $attribute_id = (int)$attribute_id;

        if ($attribute_id === 40) {
            return 'flavor';
        }

        if ($attribute_id === 41) {
            return 'volume';
        }

        if ($attribute_id === 38) {
            return 'ohm';
        }

        $n = utf8_strtolower(trim((string)$name));

        if ($n === 'колір' || $n === 'цвет' || $n === 'color' || utf8_strpos($n, 'колір') !== false || utf8_strpos($n, 'цвет') !== false) {
            return 'color';
        }

        if ($n === 'модель' || $n === 'model' || $n === 'лінійка' || $n === 'линейка') {
            return 'model';
        }

        if ($n === 'об\'єм' || $n === 'объем' || $n === 'обʼєм' || $n === 'volume' || $n === 'мілілітраж' || $n === 'миллилитраж') {
            return 'volume';
        }

        return '';
    }

    private function variantLocksMatch($current_attrs, $sibling_attrs, $lock_keys, $vary_kind)
    {
        foreach ($lock_keys as $kind => $attribute_id) {
            if ($kind === $vary_kind) {
                continue;
            }

            $current_value = isset($current_attrs[$attribute_id]['text']) ? $current_attrs[$attribute_id]['text'] : '';

            if ($current_value === '') {
                continue;
            }

            $sibling_value = isset($sibling_attrs[$attribute_id]['text']) ? $sibling_attrs[$attribute_id]['text'] : '';

            if ($sibling_value === '') {
                if ($kind === 'model') {
                    continue;
                }

                return false;
            }

            if (utf8_strtolower($sibling_value) !== utf8_strtolower($current_value)) {
                return false;
            }
        }

        return true;
    }

    private function buildVariantItem($product, $value, $kind, $current_attrs)
    {
        $model_name = '';

        foreach ($current_attrs as $attribute_id => $attr) {
            if ($this->classifyVariantAttribute($attribute_id, $attr['name']) === 'model') {
                $model_name = $attr['text'];
                break;
            }
        }

        if ($model_name === '' && !empty($product['name'])) {
            $model_name = $product['name'];
        }

        if ($kind === 'volume') {
            $label = $this->formatVariantVolumeLabel($value);
        } else {
            $label = $value;
        }

        $caption = trim($model_name . ' ' . $label);

        return array(
            'product_id' => (int)$product['product_id'],
            'name' => $label,
            'value' => $value,
            'product_name' => $product['name'],
            'image' => $product['image'],
            'in_stock' => ((int)$product['quantity'] + (int)$product['quantity_sklad']) > 0,
            'alt' => $caption,
            'title' => $caption,
            'sort_name' => $label
        );
    }

    private function formatVariantVolumeLabel($value)
    {
        if (preg_match('/(\d+(?:[.,]\d+)?)/', (string)$value, $match)) {
            $number = str_replace(',', '.', $match[1]);

            if ((float)$number == (int)$number) {
                $number = (int)$number;
            }

            return $number . ' мл';
        }

        return $value;
    }

    private function sortVariantItems($a, $b)
    {
        $a_stock = !empty($a['in_stock']) ? 0 : 1;
        $b_stock = !empty($b['in_stock']) ? 0 : 1;

        if ($a_stock !== $b_stock) {
            return $a_stock - $b_stock;
        }

        return strnatcasecmp((string)$a['sort_name'], (string)$b['sort_name']);
    }

    public function getMainCategory($product_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . (int)$product_id . "' AND main_category = 1");

        if ($query->num_rows) {
            return $query->row['category_id'];
        } else {
            return 0;
        }
    }

    public function getProductname($attribute_id, $product_id)
    {


        $query = $this->db->query("SELECT text FROM " . DB_PREFIX . "product_attribute WHERE product_id = " . (int)$product_id . " AND attribute_id = " . (int)$attribute_id . "");

        if ($query->num_rows) {
            return $query->row['text'];
        } else {
            return false;
        }
    }

    public function getAttributename($attribute_id)
    {


        $query = $this->db->query("SELECT name FROM " . DB_PREFIX . "attribute_description WHERE attribute_id = " . (int)$attribute_id . " AND attribute_id = " . (int)$attribute_id . "");

        if ($query->num_rows) {
            return $query->row['name'];
        } else {
            return false;
        }
    }

    public function getProductimage($product_id)
    {


        $query = $this->db->query("SELECT image FROM " . DB_PREFIX . "product WHERE product_id = " . (int)$product_id . "");

        if ($query->num_rows) {
            return $query->row['image'];
        } else {
            return false;
        }
    }

    public function getProductguid($product_id)
    {


        $query = $this->db->query("SELECT guid FROM " . DB_PREFIX . "product_to_1c WHERE product_id = " . (int)$product_id . "");

        if ($query->num_rows) {
            return $query->row['guid'];
        } else {
            return false;
        }
    }

    public function productCategoryRelationExists(array $productIds, array $categoryIds): bool
    {
        $productIds = array_filter(array_map('intval', $productIds));
        $categoryIds = array_filter(array_map('intval', $categoryIds));

        if (!$productIds || !$categoryIds) {
            return false;
        }

        $sql = "SELECT 1 FROM " . DB_PREFIX . "product_to_category WHERE product_id IN (" . implode(',', $productIds) . ") AND category_id IN (" . implode(',', $categoryIds) . ") LIMIT 1";

        $query = $this->db->query($sql);

        return $query->num_rows > 0;
    }

    public function getProductsForNeedAddIfOrderLiquids($customerId, array $catCartridges, array $catPodSystem, array $addProductsIds = [], array $excludeProductIds = [])
    {
        $categoriesCartridges = implode(',', array_map('intval', $catCartridges));

        $sqlCartridges = "
    SELECT DISTINCT
        op.product_id
    FROM `" . DB_PREFIX . "order` o
    INNER JOIN `" . DB_PREFIX . "order_product` op
        ON op.order_id = o.order_id
    INNER JOIN `" . DB_PREFIX . "product_to_category` p2c
        ON p2c.product_id = op.product_id
    INNER JOIN `" . DB_PREFIX . "product` p
        ON p.product_id = op.product_id
    WHERE o.customer_id = " . (int)$customerId . "
        AND p2c.category_id IN (" . $categoriesCartridges . ")
        AND p.status = 1
        AND p.quantity > 0
        AND p.stock_status_id IN (5, 7)
" . $this->getListingVisibilitySql();

        if (!empty($excludeProductIds)) {

            $excludeProductIds = array_map('intval', $excludeProductIds);

            $sqlCartridges .= "
            AND p.product_id NOT IN (" . implode(',', $excludeProductIds) . ")";
        }


        $products = [];

        $rowsCartridges = $this->db->query($sqlCartridges)->rows;

        if (!$rowsCartridges) {

            foreach ($rowsCartridges as $item) {

                $products[(int)$item['product_id']] = $this->getProduct((int)$item['product_id']);
            }

        } else {
            $categoriesPodSystem = implode(',', array_map('intval', $catPodSystem));

            $sqlPodSystem = "
    SELECT DISTINCT
        op.product_id
    FROM `" . DB_PREFIX . "order` o
    INNER JOIN `" . DB_PREFIX . "order_product` op
        ON op.order_id = o.order_id
    INNER JOIN `" . DB_PREFIX . "product_to_category` p2c
        ON p2c.product_id = op.product_id
    INNER JOIN `" . DB_PREFIX . "product` p
        ON p.product_id = op.product_id
    WHERE o.customer_id = " . (int)$customerId . "
        AND p2c.category_id IN (" . $categoriesPodSystem . ")
        AND p.status = 1
        AND p.quantity > 0
        AND p.stock_status_id IN (5, 7)
" . $this->getListingVisibilitySql();
            if (!empty($excludeProductIds)) {

                $excludeProductIds = array_map('intval', $excludeProductIds);

                $sqlPodSystem .= "
            AND p.product_id NOT IN (" . implode(',', $excludeProductIds) . ")";
            }

            $rowsPodSystem = $this->db->query($sqlPodSystem)->rows;

            foreach ($rowsPodSystem as $item) {

                $products_related_1c = $this->getProductRelated1c($item['product_id']);

                foreach ($products_related_1c as $product_related_1c) {
                    $products[(int)$product_related_1c['product_id']] = $product_related_1c;
                }
            }
        }


        if ($addProductsIds) {
            foreach ($addProductsIds as $productId) {
                $productId = (int)$productId;

                if (isset($products[$productId])) {
                    continue;
                }

                $product = $this->getProduct($productId);

                if ($product && in_array((int)$product['stock_status_id'], [5, 7], true) && (int)$product['quantity'] > 0) {
                    $products[$productId] = $product;
                }
            }
        }

        return $products;
    }

    private function getCatalogProductsFromSql($data)
    {
        if (!empty($data['filter_category_id'])) {
            if (!empty($data['filter_sub_category'])) {
                $sql = " FROM " . DB_PREFIX . "category_path cp INNER JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)";
            } else {
                $sql = " FROM " . DB_PREFIX . "product_to_category p2c";
            }

            if (!empty($data['filter_filter'])) {
                $sql .= " INNER JOIN " . DB_PREFIX . "product_filter pf ON (p2c.product_id = pf.product_id) INNER JOIN " . DB_PREFIX . "product p ON (pf.product_id = p.product_id)";
            } else {
                $sql .= " INNER JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)";
            }

            return $sql;
        }

        return " FROM " . DB_PREFIX . "product p";
    }

    private function getCatalogActivePriceDateSql($alias = '')
    {
        $prefix = $alias ? $alias . '.' : '';

        return "((" . $prefix . "date_start = '0000-00-00' OR " . $prefix . "date_start < NOW()) AND (" . $prefix . "date_end = '0000-00-00' OR " . $prefix . "date_end > NOW()))";
    }

    private function getCatalogDiscountJoinSql()
    {
        $customer_group_id = (int)$this->config->get('config_customer_group_id');

        return " LEFT JOIN (
            SELECT product_id, SUBSTRING_INDEX(GROUP_CONCAT(price ORDER BY priority ASC, price ASC SEPARATOR ','), ',', 1) + 0 AS price
            FROM " . DB_PREFIX . "product_discount
            WHERE customer_group_id = '" . $customer_group_id . "' AND quantity = '1' AND " . $this->getCatalogActivePriceDateSql() . "
            GROUP BY product_id
        ) catalog_discount ON catalog_discount.product_id = p.product_id";
    }

    private function getCatalogPriceJoinsSql()
    {
        $customer_group_id = (int)$this->config->get('config_customer_group_id');

        return $this->getCatalogDiscountJoinSql() . " LEFT JOIN (
            SELECT product_id, SUBSTRING_INDEX(GROUP_CONCAT(price ORDER BY priority ASC, price ASC SEPARATOR ','), ',', 1) + 0 AS price
            FROM " . DB_PREFIX . "product_special
            WHERE customer_group_id = '" . $customer_group_id . "' AND " . $this->getCatalogActivePriceDateSql() . "
            GROUP BY product_id
        ) catalog_special ON catalog_special.product_id = p.product_id";
    }

    private function mapCatalogListingRow(array $row)
    {
        return array(
            'product_id' => $row['product_id'],
            'manufacturer_id' => $row['manufacturer_id'] ?? 0,
            'model' => $row['model'] ?? '',
            'sku' => $row['sku'] ?? '',
            'upc' => $row['upc'] ?? '',
            'ean' => $row['ean'] ?? '',
            'jan' => $row['jan'] ?? '',
            'isbn' => $row['isbn'] ?? '',
            'mpn' => $row['mpn'] ?? '',
            'name' => $row['name'],
            'description' => $row['description'],
            'image' => $row['image'],
            'price' => !empty($row['discount']) ? $row['discount'] : $row['price'],
            'special' => $row['special'] ?? null,
            'tax_class_id' => $row['tax_class_id'],
            'quantity' => $row['quantity'],
            'quantity_sklad' => $row['quantity_sklad'],
            'minimum' => $row['minimum'],
            'isnew' => $row['isnew'],
            'main_category' => $row['main_category'],
            'variants' => json_decode($row['variants'] ?? '[]', true) ?: [],
            'manufacturer' => $row['manufacturer'] ?? '',
            'reward' => $row['reward'] ?? 0,
            'rating' => round($row['rating'] ?? 0),
            'reviews' => !empty($row['reviews']) ? $row['reviews'] : 0,
            'date_added' => $row['date_added'] ?? '',
        );
    }

    private function canCacheTotalProducts($data)
    {
        if (!empty($data['filter_name']) || !empty($data['filter_tag'])) {
            return false;
        }

        // OCFilter injects params into SQL outside $data; caching by category alone
        // would return unfiltered totals (wrong pagination) and could poison the cache.
        if ($this->registry->get('ocfilter') && $this->ocfilter->startup() && $this->ocfilter->api->isSelected()) {
            return false;
        }

        return true;
    }

    private function getTotalProductsCacheKey($data)
    {
        return 'product.total.vis.' . (int)$this->config->get('config_store_id') . '.' . (int)$this->config->get('config_language_id') . '.' . (int)($data['filter_category_id'] ?? 0) . '.' . (!empty($data['filter_sub_category']) ? '1' : '0') . '.' . md5((string)($data['filter_filter'] ?? '')) . '.' . (int)($data['filter_manufacturer_id'] ?? 0);
    }

    public function getListingVisibilitySql($prefix = 'p')
    {
        $p = $prefix ? $prefix . '.' : '';

        return " AND " . $p . "price > 0 AND " . $p . "image IS NOT NULL AND " . $p . "image != '' AND " . $p . "image NOT IN ('no_image.jpg', 'no_image.png', 'placeholder.png', 'placeholder.jpg')";
    }

    public function isListingVisible($product)
    {
        if (!$product || !is_array($product)) {
            return false;
        }

        $image = isset($product['image']) ? trim((string)$product['image']) : '';

        if ($image === '' || in_array($image, array('no_image.jpg', 'no_image.png', 'placeholder.png', 'placeholder.jpg'), true)) {
            return false;
        }

        return (float)($product['price'] ?? 0) > 0;
    }

    private function getCatalogRatingReviewsSelectFields()
    {
        return "product_review.rating AS rating, product_review.reviews AS reviews";
    }

    private function getCatalogSortJoinsSql()
    {
        return " LEFT JOIN (
            SELECT product_id, AVG(rating) AS rating, COUNT(*) AS reviews
            FROM " . DB_PREFIX . "review
            WHERE status = '1'
            GROUP BY product_id
        ) product_review ON product_review.product_id = p.product_id
        LEFT JOIN " . DB_PREFIX . "product_types pt_self ON pt_self.product_id = p.product_id AND pt_self.attribute_id = 40
        LEFT JOIN (
            SELECT pt.category_id, MIN(p_vg.sort_order) AS variant_sort_order
            FROM " . DB_PREFIX . "product_types pt
            INNER JOIN " . DB_PREFIX . "product p_vg ON p_vg.product_id = pt.product_id
            WHERE pt.attribute_id = 40
            GROUP BY pt.category_id
        ) variant_group ON variant_group.category_id = pt_self.category_id";
    }

    private function getVariantGroupSortOrderField()
    {
        return "COALESCE(variant_group.variant_sort_order, p.sort_order)";
    }

    private function getCatalogOrderSuffix($data)
    {
        if (!$this->config->get('config_review_status')) {
            if (isset($data['order']) && ($data['order'] == 'DESC')) {
                return " DESC, LCASE(pd.name) DESC";
            }

            return " ASC, LCASE(pd.name) ASC";
        }

        $in_stock = "(p.quantity > 0 OR p.quantity_sklad > 0)";
        $in_stock_rating = ", (CASE WHEN " . $in_stock . " THEN IFNULL(rating, 0) ELSE 0 END) DESC";
        $in_stock_reviews = ", (CASE WHEN " . $in_stock . " THEN IFNULL(reviews, 0) ELSE 0 END) DESC";

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            return " DESC" . $in_stock_rating . $in_stock_reviews . ", LCASE(pd.name) DESC";
        }

        return " ASC" . $in_stock_rating . $in_stock_reviews . ", LCASE(pd.name) ASC";
    }

    /**
     * Rating sort must stay sequential by displayed stars.
     * Stock is only a tiebreaker so warehouse-only items do not break the rating sequence.
     * Among equal stars, more reviews rank higher (both ASC and DESC).
     * Products without reviews always go last.
     */
    private function getCatalogRatingOrderSql($data)
    {
        $dir = (isset($data['order']) && ($data['order'] == 'DESC')) ? 'DESC' : 'ASC';

        return " ORDER BY (product_review.rating IS NULL) ASC"
            . ", ROUND(IFNULL(product_review.rating, 0)) " . $dir
            . ", IFNULL(product_review.reviews, 0) DESC"
            . ", IFNULL(product_review.rating, 0) " . $dir
            . ", (p.quantity > 0) DESC"
            . ", (p.quantity_sklad > 0) DESC"
            . ", LCASE(pd.name) ASC";
    }
}
