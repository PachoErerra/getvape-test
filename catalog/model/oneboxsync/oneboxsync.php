<?php
class ModelOneboxsyncOneboxsync extends Model{
    public function getApiId ($token) {
        if (!$token) { return false;}
        try {
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "api_session` WHERE `token` = '" . $token . "' LIMIT 1");
        } catch (Exception $ex) {
            $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "api_session` WHERE `session_id` = '" . $token . "' LIMIT 1");
        }
        if ($query->num_rows) {
            return $query->rows[0]['api_id'];
        } else {
            return false;   
        }
    }
    public function getLanguageId ($code)
    {
        $lang_id = array();
        $query = $this->db->query("SELECT  `" . DB_PREFIX . "language`.`language_id` FROM `" . DB_PREFIX . "language` WHERE `" . DB_PREFIX . "language`.`code` = '" . $code . "'");
        foreach ($query->rows as $result) {
            $lang_id[] = $result;
        }
        return $lang_id;
    }
//----------------------------------------------------UPDATE CATEGORY---------------------------------------------------
    public function getCategoryId ($id) {
        $query = $this->db->query("SELECT  `" . DB_PREFIX . "category`.`category_id` FROM `" . DB_PREFIX . "category` WHERE `" . DB_PREFIX . "category`.`category_id` = '" . $id . "'");
        return $query;
    }

    public function getCategoryIdCode1c($ext_id) {
        $query = $this->db->query("SELECT  `" . DB_PREFIX . "category`.`category_id` FROM `" . DB_PREFIX . "category` WHERE `" . DB_PREFIX . "category`.`ext_id` = '" . $ext_id . "'");
        return $query;
    }

    public function getMaxCategoryId () {
        $query = $this->db->query("SELECT  MAX(`" . DB_PREFIX . "category`.`category_id`) FROM `" . DB_PREFIX . "category`");
        return $query;
    }

    public function newCategory ($id, $parentid, $name, $name_lang) {
        $top = 0;
        if($parentid == 0){
            $top = 1;
        }
        $this->db->query("INSERT INTO `" . DB_PREFIX . "category` (`category_id`, `parent_id`, `top`, `status`, `date_added`, `date_modified`) values ('" . $id . "', '" . $parentid . "', '" . $top . "', '1', NOW(), NOW())" );

        $this->db->query("INSERT INTO `" . DB_PREFIX . "category_to_store` (`category_id`, `store_id`) values ('" . $id . "', '0')" );
        $this->db->query("INSERT INTO `" . DB_PREFIX . "category_path` (`category_id`, `path_id`, `level`) values ('" . $id . "', '" . $id . "', '1')" );
        foreach ($name_lang as $value){
            if ($value['name'] == ''){
                $value['name'] = $name;
            }
            $this->db->query("INSERT INTO `" . DB_PREFIX . "category_description` (`category_id`, `language_id`, `name`, `description`) values ('" . $id . "', '" . $value['lang_id'] . "', '" . $value['name'] . "', '" . $value['name'] . "')" );
        }

        return 'new';
    }

    public function updateCategory ($id, $parentid, $name, $name_lang) {
        $top = 0;
        if($parentid == 0){
            $top = 1;
        }
        $this->db->query("REPLACE INTO `" . DB_PREFIX . "category_to_store` (`category_id`, `store_id`) values ('" . $id . "', '0')" );
        $this->db->query("UPDATE `" . DB_PREFIX . "category` SET `parent_id` = '" . $parentid . "', `top` = '" . $top . "', `date_modified` = NOW() WHERE `category_id` = " . $id  );
        $this->db->query("REPLACE INTO `" . DB_PREFIX . "category_path` (`category_id`, `path_id`, `level`) values ('" . $id . "', '" . $id . "', '1')" );
        foreach ($name_lang as $value){
            $name_base = $this->db->query("SELECT  `" . DB_PREFIX . "category_description`.`name` FROM `" . DB_PREFIX . "category_description` WHERE `" . DB_PREFIX . "category_description`.`category_id` = '" . $id . "' && `" . DB_PREFIX . "category_description`.`language_id` = '" . $value['lang_id'] . "'");
            if ($value['name'] == '') {
                if ($name_base->rows[0]['name'] == '') {
                    $value['name'] = $name;
                } else {
                    $value['name'] = $name_base->rows[0]['name'];
                }
            }
            if ($name_base->num_rows == 0){
                $this->db->query("INSERT INTO `" . DB_PREFIX . "category_description` (`category_id`, `language_id`, `name`, `description`) values ('" . $id . "', '" . $value['lang_id'] . "', '" . $value['name'] . "', '" . $value['name'] . "')" );
            }
            $this->db->query("UPDATE `" . DB_PREFIX . "category_description` SET `name` = '" . $name . "', `description` = '" . $name . "' WHERE `category_id` = " . $id ." &&  `language_id` = '" . $value['lang_id'] . "'" );
        }
        return 'updated';
    }

//----------------------------------------------------------------------------------------------------------------------
    
//----------------------------------------------------UPDATE PRODUCT ---------------------------------------------------
    
    public function getProductId ($id)
    {
        $query = $this->db->query("SELECT  " . DB_PREFIX ."product.product_id FROM " . DB_PREFIX . "product WHERE " . DB_PREFIX . "product.product_id = '". $id . "'");
        return $query;
    }

    public function getMaxProductId ()
    {
        $query = $this->db->query("SELECT  MAX(" . DB_PREFIX ."product.product_id) FROM " . DB_PREFIX . "product ");
        return $query;
    }

    public function getProductArticul($articul){
        $query = $this->db->query("SELECT  " . DB_PREFIX ."product.product_id FROM " . DB_PREFIX . "product WHERE  " . DB_PREFIX . "product.model = '". $articul . "'");
        return $query;
    }
    
    public function getCurrencyValue ($currencyCode)
    {
        $query = $this->db->query("SELECT value FROM " . DB_PREFIX . "currency WHERE code = '" . $currencyCode . "'");
        return $query;
    }
    
    public function newCurrencyValue ($title, $code, $value, $status)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "currency (title, code, decimal_place, value, status, date_modified) values ('" . $title . "', '" . $code . "', '2', '" . $value . "', '" . $status . "', NOW())");
        return 'new';
    }
    
    public function isImage ($id, $image)
    {
        $query = $this->db->query("SELECT  " . DB_PREFIX ."product_image.product_image_id FROM " . DB_PREFIX . "product_image WHERE " . DB_PREFIX . "product_image.product_id = ". $id ." && " . DB_PREFIX . "product_image.image = '". $image . "'");
        return $query;
    }

    public function newImageValue ($id, $image)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_image (product_id, image) values ('" . $id . "', '" . $image . "')");
        return 'new';
    }
    
    public function isBrand ($brandName)
    {
        $query = $this->db->query("SELECT manufacturer_id FROM " . DB_PREFIX . "manufacturer WHERE name = '". $brandName ."'");
        return $query;
    }
    public function newBrand ($brandName)
    {
        $query = $this->db->query("INSERT INTO " . DB_PREFIX . "manufacturer (name) values ('" . $brandName . "')");
        foreach ($query->rows as $brands){
            $this->db->query("INSERT INTO " . DB_PREFIX . "oc_manufacturer_description (manufacturer_id, language_id) values ('" . $brands['manufacturer_id'] . "', '1')");
        }

        return 'new';
    }

    public function isCategoryId ($id, $category) {
        $query = $this->db->query("SELECT product_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = " . $id . " && category_id = '". $category . "'");
        return $query;
    }
    
    public function newProductCategory ($id, $categoryid ){
        $is_main = $this->db->query("SHOW COLUMNS FROM  `" . DB_PREFIX . "product_to_category` LIKE 'main_category'");
        if ($is_main->num_rows == 0){
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category (product_id, category_id) values ('" . $id . "', '" . $categoryid . "')" );
        } else {
            $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_category (product_id, category_id, main_category) values ('" . $id . "', '" . $categoryid . "', '1')" );
        }
        return $is_main;
    }

    public function newProduct ($id, $articul, $model, $quantity, $image, $manufacturer, $price, $avail, $name, $name_lang, $description, $availtext){
        $this->db->query("INSERT INTO " . DB_PREFIX . "product (product_id, sku, model, quantity, image, manufacturer_id, price, status, date_added, date_modified, stock_status_id) values ('" . $id . "', '" . $articul . "', '" . $model . "', '" . $quantity . "', '" . $image ."', '" . $manufacturer . "', '" . $price . "', '" . $avail . "', NOW(), NOW(), '" . $availtext . "')" );
        $this->db->query("INSERT INTO " . DB_PREFIX . "product_to_store (product_id, store_id) values ('" . $id . "', '0')" );
        foreach ($name_lang as $value){
            if ($value['name'] == '') {
                $value['name'] = $name;
            }
            $this->db->query("INSERT INTO `" . DB_PREFIX . "product_description` (`product_id`, `language_id`, `name`, `description`) values ('" . $id . "', '" . $value['lang_id'] . "', '" . $this->db->escape($value['name']) . "', '" . $this->db->escape($description) . "')" );

        }
        return $id;
    }

    public function updateProductImage ($id, $image) {
        $this->db->query("UPDATE " . DB_PREFIX . "product SET image = '" . $image . "' WHERE product_id = '" . $id . "'");
    }

    public function updateCategoryImage ($id, $image) {
        $this->db->query("UPDATE " . DB_PREFIX . "category SET image = '" . $image . "' WHERE category_id = '" . $id . "'");
    }
    
    public function updateProduct ($id, $articul, $model, $quantity, $image, $manufacturer, $price, $avail, $name, $name_lang, $description, $availtext, $priceold, $priceoldend, $pricesCountArray)	{
        $this->db->query("UPDATE " . DB_PREFIX . "product SET sku = '" . $articul . "', model = '" . $model . "', quantity = '" . $quantity . "', image = '" . $image ."', manufacturer_id = '" . $manufacturer . "', price = '" . $price . "', status = '" . $avail . "', date_modified = NOW(), stock_status_id = '".$availtext."' WHERE  product_id = '" . $id . "'" );
        $this->db->query("REPLACE INTO " . DB_PREFIX . "product_to_store (product_id, store_id) values ('" . $id . "', '0')" );

        foreach ($name_lang as $value){
            $name_base = $this->db->query("SELECT  `" . DB_PREFIX . "product_description`.`name` FROM `" . DB_PREFIX . "product_description` WHERE `" . DB_PREFIX . "product_description`.`product_id` = '" . $id . "' && `" . DB_PREFIX . "product_description`.`language_id` = '" . $value['lang_id'] . "'");
            if ($value['name'] == '') {
                if ($name_base->rows[0]['name'] == '') {
                    $value['name'] = $name;
                } else {
                    $value['name'] = $name_base->rows[0]['name'];
                }
            }
            if ($name_base->num_rows == 0){
                $this->db->query("INSERT INTO " . DB_PREFIX . "product_description (product_id, language_id, name, description) values ('" . $id . "', '" . $value['lang_id'] . "', '" . $value['name'] . "', '" . $description . "')" );
            }

            $this->db->query("UPDATE " . DB_PREFIX . "product_description SET name = '" . $this->db->escape($value['name']) . "', description = '" . $this->db->escape($description) . "' WHERE  product_id = '" . $id . "' && language_id = '" . $value['lang_id'] . "'" );
            if ($priceold > 0) {
                try {
                    $name_base = $this->db->query("SELECT * FROM ".DB_PREFIX."product_special WHERE product_id = '{$id}' LIMIT 1");
                    if (@$name_base->rows[0]['product_special_id'] == '') {
                        $this->db->query("INSERT INTO " . DB_PREFIX . "product_special (product_id, customer_group_id, price, date_start, date_end) values ('" . $id . "', '1', '" . $priceold . "', '" . date('Y-m-d') . "', '".$priceoldend."')" );
                    } else {
                        $this->db->query("UPDATE " . DB_PREFIX . "product_special SET price = '" . $this->db->escape($priceold) . "', date_end = '" . $priceoldend . "' WHERE  product_id = '" . $id . "'");
                    }
                } catch (Exception $ex) {

                }
            } else {
                try {
                    $this->db->query("DELETE * FROM " . DB_PREFIX . "product_special WHERE  product_id = '" . $id . "'");
                } catch (Exception $ex) {

                }
            }

            // 	product_id 	customer_group_id 	quantity 	priority 	price 	date_start 	date_end
            if ($pricesCountArray) {
                foreach ($pricesCountArray as $item) {
                    try {
                        $item = (array) $item;
                        $name_base = $this->db->query("SELECT * FROM ".DB_PREFIX."product_discount WHERE product_id = '{$id}' AND quantity = '{$item['quantity']}' LIMIT 1");
                        if (@$name_base->rows[0]['product_discount_id'] == '') {
                            $this->db->query("INSERT INTO " . DB_PREFIX . "product_discount (product_id, customer_group_id, price, quantity) values ('" . $id . "', '1', '" . $item['price'] . "', '" . $item['quantity']."')" );
                        } else {
                            $this->db->query("UPDATE " . DB_PREFIX . "product_discount SET price = '" . $this->db->escape($item['price']) . "' WHERE  product_id = '" . $id . "' AND product_discount_id = '".$name_base->rows[0]['product_discount_id']."'");
                        }
                    } catch (Exception $ex) {

                    }
                }
            } else {
                $this->db->query("DELETE * FROM " . DB_PREFIX . "product_discount WHERE  product_id = '" . $id . "'");
            }
        }

		return 'updated';
	}

//----------------------------------------------------------------------------------------------------------------------    
     
//----------------------------------------------------UPDATE FILTRE NAME------------------------------------------------
      public function isOcfilter()
    {
        $query = $this->db->query("SHOW TABLES LIKE  '" . DB_PREFIX . "ocfilter_option'");
        return $query;
    }
    
    public function getFilterId($id)
    {
        $query = $this->db->query("SELECT `option_id` FROM `" . DB_PREFIX . "ocfilter_option` WHERE `option_id` = '" . $id . "'");
        return $query;
    }

    public function newFiltreName($id, $name, $name_lang)
    {

        $is_ocfilter = $this->db->query("SHOW TABLES LIKE  '" . DB_PREFIX . "ocfilter_option'");
        if ($is_ocfilter->num_rows != 0) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "ocfilter_option` (`option_id`, `type`, `keyword`, `selectbox`, `status`) values ('" . $id . "', 'radio','" . $name . "', '1', '1')");
            $this->db->query("INSERT INTO `" . DB_PREFIX . "ocfilter_option_to_store` (`option_id`, `store_id`) values ('" . $id . "', '0')");
            foreach ($name_lang as $value) {
                if ($value['name'] == '') {
                    $value['name'] = $name;
                }
                $this->db->query("INSERT INTO `" . DB_PREFIX . "ocfilter_option_description` (`option_id`, `language_id`, `name`) values ('" . $id . "', '" . $value['lang_id'] . "', '" . $value['name'] . "' )");
            }
        }

        return '$group_id';
    }

    public function updateStandartFiltreName($id, $name, $name_lang){
        $is_filter = $this->db->query("SELECT `filter_id` FROM `" . DB_PREFIX . "filter` WHERE `filter_id` = '" . $id . "'");

        if ($is_filter->num_rows == 0){
             $group_id = $id;
            foreach ($name_lang as $value) {
                if ($value['name'] == '') {
                    $value['name'] = $name;
                }

                $this->db->query("INSERT INTO `" . DB_PREFIX . "filter_group_description` (`filter_group_id`, `language_id`, `name`) values ('" . $group_id . "', '" . $value['lang_id'] . "','" . $value['name'] . "')");
            }
            $this->db->query("INSERT INTO `" . DB_PREFIX . "filter_group` (`filter_group_id`, `sort_order`) values ('" . $group_id . "', '0')");

        } else {
            $group_id = $id;
            foreach ($name_lang as $value) {
                if ($value['name'] == '') {
                    $value['name'] = $name;
                }

                $this->db->query("REPLACE INTO `" . DB_PREFIX . "filter_group_description` (`filter_group_id`, `language_id`, `name`) values ('" . $group_id . "', '" . $value['lang_id'] . "','" . $value['name'] . "')");
            }
            $this->db->query("REPLACE INTO `" . DB_PREFIX . "filter_group` (`filter_group_id`, `sort_order`) values ('" . $group_id . "', '0')");
        }

    }

    public function updateFiltreName($id, $name, $name_lang)
    {
	$is_ocfilter = $this->db->query("SHOW TABLES LIKE  '" . DB_PREFIX . "ocfilter_option'");
        if ($is_ocfilter->num_rows != 0) {
            $this->db->query("UPDATE `" . DB_PREFIX . "ocfilter_option` SET `keyword` = '" . $name . "', `status` = '1' WHERE `option_id` = $id");
            foreach ($name_lang as $value) {
                if ($value['name'] == '') {
                    $value['name'] = $name;
                }
                $this->db->query("UPDATE `" . DB_PREFIX . "ocfilter_option_description` SET `name` = '" . $value['name'] . "' WHERE  `option_id` = '" . $id . "' && `language_id` = '" . $value['lang_id'] . "'");
            }
        }
        return 'Updated';
    }   
//----------------------------------------------------------------------------------------------------------------------    
     
//----------------------------------------------------UPDATE FILTRE VALUE ----------------------------------------------
    
    public  function  updateFiltreProductValueModel($filterid, $productid, $filtervalue){

        $is_ocfilter = $this->isOcfilter();

        $cat1 = $this->db->query("SELECT category_id FROM " . DB_PREFIX . "product_to_category WHERE product_id = '" . $productid . "'" );
        $lang = $this->db->query("SELECT  `language_id` FROM `" . DB_PREFIX . "language`");
        $filter_id_max = $this->db->query ("SELECT MAX(  `filter_id` ) as maxfilter FROM  `" . DB_PREFIX . "filter_description`");

        $filter_id_next = $filter_id_max->row['maxfilter'] + 1;

        foreach ($lang->rows as $lang_id){
            $language_id = $lang_id['language_id'];

            $filter_id = $this->db->query("SELECT  `filter_id` FROM `" . DB_PREFIX . "filter_description` WHERE language_id = '" . $language_id . "' && filter_group_id = '" . $filterid . "' && name = '" . $filtervalue . "' ");

            if (($filter_id->num_rows) == 0){
                $this->db->query("INSERT INTO `" . DB_PREFIX . "filter_description` (`filter_id`, `language_id`, `filter_group_id`,  `name`) values ('" . $filter_id_next . "', '" . $language_id . "', '" . $filterid . "','" . $filtervalue . "')");
            } else {
                $filter_id_next = $filter_id->row['filter_id'];
            }
        }

        $filter_id = $this->db->query("SELECT  `filter_id` FROM `" . DB_PREFIX . "filter_description` WHERE filter_group_id = '" . $filterid . "' && name = '" . $filtervalue . "' ");
        $filter_id = $filter_id->row['filter_id'];

        $this->db->query("REPLACE INTO `" . DB_PREFIX . "filter` (`filter_id`, `filter_group_id`, `sort_order`) values ('" . $filter_id . "', '" . $filterid . "', '0')");

        $this->db->query("REPLACE INTO `" . DB_PREFIX . "product_filter` (`product_id`, filter_id) values('" . $productid . "', '" . $filter_id . "')" );

        foreach ($cat1->rows as $result) {
            $cat = $result;
            $this->db->query("REPLACE INTO " . DB_PREFIX . "category_filter (category_id, filter_id) values ('" . $cat['category_id'] ."', '" . $filter_id . "')" );

        }

        if ($is_ocfilter->num_rows){
            foreach ($cat1->rows as $result) {
                $cat = $result;
                $this->db->query("REPLACE INTO " . DB_PREFIX . "ocfilter_option_to_category (option_id, category_id) values ('" . $filterid . "','" . $cat['category_id'] ."')" );

            }

            $valueId = $this->db->query("SELECT value_id FROM " . DB_PREFIX . "ocfilter_option_value WHERE option_id = '" . $filterid . "' && keyword = '" . $filtervalue . "'");
            if ($valueId->num_rows == 0){
                $this->db->query("REPLACE INTO " . DB_PREFIX . "ocfilter_option_value (value_id, option_id, keyword) values ('" . $valueId->row['value_id'] . "','" . $filterid . "','" . $filtervalue ."')" );
                $valueId = $this->db->query("SELECT value_id FROM " . DB_PREFIX . "ocfilter_option_value WHERE option_id = '" . $filterid . "' && keyword = '" . $filtervalue . "'");
            }

            $this->db->query("REPLACE INTO " . DB_PREFIX . "ocfilter_option_value_to_product (product_id, value_id, option_id) values ('" . $productid . "', '" . $valueId->row['value_id'] . "','" . $filterid ."')" );
            $this->db->query("REPLACE INTO " . DB_PREFIX . "ocfilter_option_value_description (value_id, option_id, language_id, name) values ('" . $valueId->row['value_id'] . "', '" . $filterid . "', '1', '" . $filtervalue . "')" );
        }
            return 'UPDATED';
    }

//----------------------------------------------------------------------------------------------------------------------

//------------------------------------------------------ ORDERS --------------------------------------------------------

    public function getOrderValue ()
    {
        $order = array();
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` ORDER BY order_id DESC LIMIT 100");

        foreach ($query->rows as $result) {
            $order[] = $result;
        }
        return $order;
    }

    public function updateBrandImage ($id, $image) {
        $this->db->query("UPDATE " . DB_PREFIX . "manufacturer SET image = '" . $image . "' WHERE manufacturer_id = '" . $id . "'");
    }
    
    public function getOrderProduct ($order_id)
    {
        $order_product = array();
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_product` WHERE `order_id` = '" . $order_id . "' ");

        foreach ($query->rows as $result) {
            $order_product[] = $result;
        }
        return $order_product;
    }

    public function getOrderCoupon($order_id)
    {
        $coupon = array(
            'coupon_code' => '',
            'coupon_name' => ''
        );

        $query = $this->db->query("SELECT c.code, c.name FROM `" . DB_PREFIX . "coupon_history` ch LEFT JOIN `" . DB_PREFIX . "coupon` c ON (c.coupon_id = ch.coupon_id) WHERE ch.order_id = '" . (int)$order_id . "' LIMIT 1");

        if ($query->num_rows && !empty($query->row['code'])) {
            $coupon['coupon_code'] = $query->row['code'];
            $coupon['coupon_name'] = $query->row['name'];

            return $coupon;
        }

        $query = $this->db->query("SELECT title FROM `" . DB_PREFIX . "order_total` WHERE order_id = '" . (int)$order_id . "' AND code = 'coupon' LIMIT 1");

        if ($query->num_rows && preg_match('/\(([^)]+)\)/', html_entity_decode($query->row['title'], ENT_QUOTES, 'UTF-8'), $matches)) {
            $code = trim($matches[1]);
            $coupon['coupon_code'] = $code;

            $coupon_query = $this->db->query("SELECT name FROM `" . DB_PREFIX . "coupon` WHERE code = '" . $this->db->escape($code) . "' LIMIT 1");

            if ($coupon_query->num_rows) {
                $coupon['coupon_name'] = $coupon_query->row['name'];
            }
        }

        return $coupon;
    }

    public function getOrderProductOptions($order_id, $order_product_id)
    {
        $order_product_option = array();
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order_option` WHERE `order_id` = '" . $order_id . "' && `order_product_id` = '" . $order_product_id . "' ");

        foreach ($query->rows as $result) {
            $order_product_option[] = $result;
        }
        return $order_product_option;
    }

//----------------------------------------------------------------------------------------------------------------------

//------------------------------------------------------ Update ORDER status -------------------------------------------

    public function updateStatus ($status_id, $order_id)
    {
        $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '" . $status_id . "' WHERE order_id = '". $order_id . "'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "order_history (order_id, order_status_id, date_added) values ('" . $order_id . "', '" . $status_id . "', NOW())");
        return 'updated';
    }


//************************************************************************************************************************      

    public function getCategory ()
	{
		$category = array();
		$query = $this->db->query(" SELECT " . DB_PREFIX . "category_description.category_id, " . DB_PREFIX . "category_description.name, parent_id, code, " . DB_PREFIX . "language.language_id FROM " . DB_PREFIX . "category_description, " . DB_PREFIX . "category, " . DB_PREFIX . "language WHERE " . DB_PREFIX . "category_description.category_id = " . DB_PREFIX . "category.category_id AND  " . DB_PREFIX . "category_description.language_id = " . DB_PREFIX . "language.language_id ORDER BY " . DB_PREFIX . "category_description.category_id");
		//SELECT oc_category_description.category_id, oc_category_description.name, parent_id, code, oc_language.language_id FROM oc_category_description, oc_category, oc_language WHERE oc_category_description.category_id = oc_category.category_id AND  oc_category_description.language_id = oc_language.language_id ORDER BY oc_category_description.category_id
		foreach ($query->rows as $result) {
			$category[] = $result;
		}
		return $category;
	}

	public function getProducts ()
	{
		$products = array();
//        $query = $this->db->query("SELECT  " . DB_PREFIX . "product.product_id, " . DB_PREFIX . "product.image as image_osn, " . DB_PREFIX . "product.status, " . DB_PREFIX . "product.price, " . DB_PREFIX . "product.model, " . DB_PREFIX . "product_description.name, " . DB_PREFIX . "product_description.description, " . DB_PREFIX . "product_to_category.category_id, " . DB_PREFIX . "product_image.image, code FROM " . DB_PREFIX . "product, " . DB_PREFIX . "product_description, " . DB_PREFIX . "product_to_category, " . DB_PREFIX . "product_image, " . DB_PREFIX . "language WHERE " . DB_PREFIX . "product.product_id = " . DB_PREFIX . "product_description.product_id && " . DB_PREFIX . "product_to_category.product_id = " . DB_PREFIX . "product.product_id && " . DB_PREFIX . "product_image.product_id = " . DB_PREFIX . "product.product_id && " . DB_PREFIX . "product_description.language_id = " . DB_PREFIX . "language.language_id  ORDER BY " . DB_PREFIX . "product.product_id");
		$query = $this->db->query("SELECT  " . DB_PREFIX . "product.product_id, " . DB_PREFIX . "product.image as image_osn, " . DB_PREFIX . "product.status, " . DB_PREFIX . "product.price, " . DB_PREFIX . "product.model, " . DB_PREFIX . "product_description.name, " . DB_PREFIX . "product_description.description, " . DB_PREFIX . "product_to_category.category_id, " . DB_PREFIX . "product_image.image, " . DB_PREFIX . "manufacturer.name as manufacturername, code FROM " . DB_PREFIX . "product, " . DB_PREFIX . "product_description, " . DB_PREFIX . "product_to_category, " . DB_PREFIX . "product_image, " . DB_PREFIX . "language, " . DB_PREFIX . "manufacturer WHERE " . DB_PREFIX . "product.product_id = " . DB_PREFIX . "product_description.product_id && " . DB_PREFIX . "product_to_category.product_id = " . DB_PREFIX . "product.product_id && " . DB_PREFIX . "product_image.product_id = " . DB_PREFIX . "product.product_id && " . DB_PREFIX . "product_description.language_id = " . DB_PREFIX . "language.language_id && " . DB_PREFIX . "manufacturer.manufacturer_id = " . DB_PREFIX . "product.manufacturer_id ORDER BY " . DB_PREFIX . "product.product_id");
//		SELECT  oc_product.product_id, oc_product.image as image_osn, oc_product.status, oc_product.price, oc_product.model, oc_product_description.name, oc_product_description.description, oc_product_to_category.category_id, oc_product_image.image, oc_manufacturer.name as manufacturername, code FROM oc_product, oc_product_description, oc_product_to_category, oc_product_image, oc_language, oc_manufacturer WHERE oc_product.product_id = oc_product_description.product_id && oc_product_to_category.product_id = oc_product.product_id && oc_product_image.product_id = oc_product.product_id && oc_product_description.language_id = oc_language.language_id && oc_manufacturer.manufacturer_id = oc_product.manufacturer_id ORDER BY oc_product.product_id
		foreach ($query->rows as $result) {
			$products[] = $result;
		}
		return $products;
	}

	public function getCurrency ()
	{
		$currency = array();
		$query = $this->db->query("SELECT code FROM " . DB_PREFIX . "currency WHERE value = 1");
		//SELECT code FROM oc_currency WHERE value = 1
		foreach ($query->rows as $result) {
			$currency[] = $result;
		}
        $currency = $currency['0']['code'];
		return $currency;
	}

	public function getFiltre ()
	{
		$filters = array();
		$query = $this->db->query("SELECT " . DB_PREFIX . "ocfilter_option_description.option_id, " . DB_PREFIX . "ocfilter_option_description.name, " . DB_PREFIX . "language.code FROM " . DB_PREFIX . "ocfilter_option_description, " . DB_PREFIX . "language WHERE " . DB_PREFIX . "ocfilter_option_description.language_id = " . DB_PREFIX . "language.language_id ORDER BY " . DB_PREFIX . "ocfilter_option_description.option_id");
		//SELECT oc_ocfilter_option_description.option_id, oc_ocfilter_option_description.name, oc_language.code FROM oc_ocfilter_option_description, oc_language WHERE oc_ocfilter_option_description.language_id = oc_language.language_id ORDER BY oc_ocfilter_option_description.option_id
        
        foreach ($query->rows as $result) {
			$filters[] = $result;
		}

		return $filters;
	}

    public function getFiltrValue ()
    {
        $filtersValue = array();
        $query = $this->db->query("SELECT " . DB_PREFIX . "ocfilter_option_to_category.option_id, " . DB_PREFIX . "ocfilter_option_to_category.category_id, " . DB_PREFIX . "ocfilter_option_description.name, " . DB_PREFIX . "language.code FROM " . DB_PREFIX . "ocfilter_option_description, " . DB_PREFIX . "ocfilter_option_to_category, " . DB_PREFIX . "language WHERE " . DB_PREFIX . "ocfilter_option_to_category.option_id = " . DB_PREFIX . "ocfilter_option_description.option_id && " . DB_PREFIX . "language.language_id = " . DB_PREFIX . "ocfilter_option_description.language_id ORDER BY " . DB_PREFIX . "ocfilter_option_to_category.option_id");
        //SELECT oc_ocfilter_option_to_category.option_id, oc_ocfilter_option_to_category.category_id, oc_ocfilter_option_description.name, oc_language.code FROM oc_ocfilter_option_description, oc_ocfilter_option_to_category, oc_language WHERE oc_ocfilter_option_to_category.option_id = oc_ocfilter_option_description.option_id && oc_language.language_id = oc_ocfilter_option_description.language_id ORDER BY oc_ocfilter_option_to_category.option_id

        foreach ($query->rows as $result) {
            $filtersValue[] = $result;
        }

        return $filtersValue;
    }

	public function getStatus ()
	{
		$status = array();
		$query = $this->db->query("SELECT order_id, order_status_id, date_modified as modified FROM " . DB_PREFIX . "order ORDER BY order_id");
		foreach ($query->rows as $result) {
		    $status[] = $result;
		}
		return $status;
	}



    public function getStatusLimit ()
    {
        $status = array();
        $query = $this->db->query("SELECT order_id, order_status_id, date_modified as modified FROM " . DB_PREFIX . "order ORDER BY  modified DESC LIMIT 100");
        foreach ($query->rows as $result) {
            $status[] = $result;
        }
        return $status;
    }

    public function getStatusName ()
    {
        $statusName = array();
        $query = $this->db->query("SELECT " . DB_PREFIX . "order_status.order_status_id, " . DB_PREFIX . "order_status.name, " . DB_PREFIX . "language.code FROM " . DB_PREFIX . "order_status, " . DB_PREFIX . "language  WHERE " . DB_PREFIX . "order_status.language_id = " . DB_PREFIX . "language.language_id ORDER BY order_status_id");
        //SELECT oc_order_status.order_status_id, oc_order_status.name, oc_language.code FROM oc_order_status, oc_language  WHERE oc_order_status.language_id = oc_language.language_id ORDER BY order_status_id
        
        foreach ($query->rows as $result) {
            $statusName[] = $result;
        }
        return $statusName;
    }

    public function getStatusByOrder ($orderid)
    {
        $status = array();
        $query = $this->db->query("SELECT order_id, order_status_id FROM " . DB_PREFIX . "order WHERE order_id = ".$orderid." LIMIT 1");
        foreach ($query->rows as $result) {
            $status[] = $result;
        }
        return $status;
    }






    public function updateFiltreValue ($id, $category_id, $value)
    {
        $this->db->query("REPLACE INTO " . DB_PREFIX . "ocfilter_option_to_category (option_id, category_id) values ('" . $id . "', '" . $category_id . "')");
        $this->db->query("REPLACE INTO " . DB_PREFIX . "ocfilter_option (option_id, keyword) values ('" . $id . "', '" . $value . "')");
        $this->db->query("REPLACE INTO " . DB_PREFIX . "ocfilter_option_to_store (option_id, store_id) values ('" . $id . "', '0')");
        return 'updated';
    }


    public function updateOrderValue ($order_id, $firstname, $lastname, $email, $telephone, $payment_firstname, $payment_lastname, $payment_company, $payment_address_1, $payment_address_2, $payment_city, $payment_postcode, $payment_country, $payment_country_id, $payment_zone, $payment_zone_id, $payment_address_format, $payment_custom_field, $payment_method, $payment_code, $shipping_firstname, $shipping_lastname, $shipping_company, $shipping_address_1, $shipping_address_2, $shipping_city, $shipping_postcode, $shipping_country, $shipping_country_id, $shipping_zone, $shipping_zone_id, $shipping_address_format, $shipping_custom_field, $shipping_method, $shipping_code, $comment, $total, $order_status_id, $affiliate_id, $commission, $marketing_id, $tracking, $language_id, $currency_id, $currency_code, $currency_value, $customer_ip, $date_added, $product_id, $product_name, $product_model, $product_quantity, $product_price, $product_total, $product_tax)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "order SET firstname = '" . $firstname . "', lastname = '" . $lastname . "', email = '" . $email . "', telephone = '" . $telephone . "', payment_firstname = '" . $payment_firstname . "', payment_lastname = '" . $payment_lastname . "', payment_company = '" . $payment_company . "', payment_address_1 = '" . $payment_address_1 . "', payment_address_2 = '" . $payment_address_2 . "', payment_city = '" . $payment_city . "', payment_postcode = '" . $payment_postcode . "', payment_country = '" . $payment_country . "', payment_country_id = '" . $payment_country_id . "', payment_zone = '" . $payment_zone . "', payment_zone_id = '" . $payment_zone_id . "', payment_address_format = '" . $payment_address_format . "', payment_custom_field = '" . $payment_custom_field . "', payment_method = '" . $payment_method . "', payment_code = '" . $payment_code . "', shipping_firstname = '" . $shipping_firstname . "', shipping_lastname = '" . $shipping_lastname . "', shipping_company = '" . $shipping_company . "', shipping_address_1 = '" . $shipping_address_1 . "', shipping_address_2 = '" . $shipping_address_2 . "', shipping_city = '" . $shipping_city . "', shipping_postcode = '" . $shipping_postcode . "', shipping_country = '" . $shipping_country . "', shipping_country_id = '" . $shipping_country_id . "', shipping_zone = '" . $shipping_zone . "', shipping_zone_id = '" . $shipping_zone_id . "', shipping_address_format = '" . $shipping_address_format . "', shipping_custom_field = '" . $shipping_custom_field . "', shipping_method = '" . $shipping_method . "', shipping_code = '" . $shipping_code . "', comment = '" . $comment . "', total = '" . $total . "', order_status_id = '" . $order_status_id . "', affiliate_id = '" . $affiliate_id . "', commission = '" . $commission . "', marketing_id = '" . $marketing_id . "', tracking = '" . $tracking . "', language_id = '" . $language_id . "', currency_id = '" . $currency_id . "', currency_code = '" . $currency_code . "', currency_value = '" . $currency_value . "', ip = '" . $customer_ip . "', date_added = '" . $date_added . "' WHERE  order_id = '" . $order_id . "'");
        $this->db->query("UPDATE " . DB_PREFIX . "order_product SET product_id = '" . $product_id . "', name = '" . $product_name . "', model = '" . $product_model . "', quantity = '" . $product_quantity . "', price = '" . $product_price . "', total = '" . $product_total . "', tax = '" . $product_tax . "' WHERE  order_id = '" . $order_id . "'");
        return 'updated';
    }

    public function getOrderHistoryStatus ($id){
        $query = $this->db->query("SELECT order_status_id FROM " . DB_PREFIX . "order_history WHERE order_id = " . $id . " ORDER BY  date_added DESC LIMIT 1");
        return $query;
    }

    public function updateOrderHistoryStatus ($order_id, $status_id){
        $this->db->query("INSERT INTO " . DB_PREFIX . "order_history (order_id, order_status_id, date_added) values ('" . $order_id . "', '" . $status_id . "', NOW())");
        return 'updated';
    }

    public function isOrder ($order_id)
    {
        $query = $this->db->query("SELECT  " . DB_PREFIX ."order.order_id FROM " . DB_PREFIX . "order WHERE " . DB_PREFIX . "order.order_id = '" . $order_id . "'");
        return $query;
    }

    public function newOrderValue ($order_id, $firstname, $lastname, $email, $telephone, $payment_firstname, $payment_lastname, $payment_company, $payment_address_1, $payment_address_2, $payment_city, $payment_postcode, $payment_country, $payment_country_id, $payment_zone, $payment_zone_id, $payment_address_format, $payment_custom_field, $payment_method, $payment_code, $shipping_firstname, $shipping_lastname, $shipping_company, $shipping_address_1, $shipping_address_2, $shipping_city, $shipping_postcode, $shipping_country, $shipping_country_id, $shipping_zone, $shipping_zone_id, $shipping_address_format, $shipping_custom_field, $shipping_method, $shipping_code, $comment, $total, $order_status_id, $affiliate_id, $commission, $marketing_id, $tracking, $language_id, $currency_id, $currency_code, $currency_value, $customer_ip, $date_added, $product_id, $product_name, $product_model, $product_quantity, $product_price, $product_total, $product_tax)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "order (order_id, firstname, lastname, email, telephone, payment_firstname, payment_lastname, payment_company, payment_address_1, payment_address_2, payment_city, payment_postcode, payment_country, payment_country_id , payment_zone, payment_zone_id, payment_address_format, payment_custom_field, payment_method, payment_code, shipping_firstname, shipping_lastname, shipping_company, shipping_address_1, shipping_address_2, shipping_city, shipping_postcode, shipping_country, shipping_country_id, shipping_zone, shipping_zone_id, shipping_address_format, shipping_custom_field, shipping_method, shipping_code, comment, total, order_status_id, affiliate_id, commission, marketing_id, tracking, language_id, currency_id, currency_code, currency_value, ip, date_added) values ('" . $order_id . "', '" . $firstname . "', '" . $lastname . "', '" . $email . "', '" . $telephone . "', '" . $payment_firstname . "', '" . $payment_lastname . "', '" . $payment_company . "', '" . $payment_address_1 . "', '" . $payment_address_2 . "', '" . $payment_city . "', '" . $payment_postcode . "', '" . $payment_country . "', '" . $payment_country_id . "', '" . $payment_zone . "', '" . $payment_zone_id . "', '" . $payment_address_format . "', '" . $payment_custom_field . "', '" . $payment_method . "', '" . $payment_code . "', '" . $shipping_firstname . "', '" . $shipping_lastname . "', '" . $shipping_company . "', '" . $shipping_address_1 . "', '" . $shipping_address_2 . "', '" . $shipping_city . "', '" . $shipping_postcode . "', '" . $shipping_country . "', '" . $shipping_country_id . "', '" . $shipping_zone . "', '" . $shipping_zone_id . "', '" . $shipping_address_format . "', '" . $shipping_custom_field . "', '" . $shipping_method . "', '" . $shipping_code . "', '" . $comment . "', '" . $total . "', '" . $order_status_id . "', '" . $affiliate_id . "', '" . $commission . "', '" . $marketing_id . "', '" . $tracking . "', '" . $language_id . "', '" . $currency_id . "', '" . $currency_code . "', '" . $currency_value . "', '" . $customer_ip . "', '" . $date_added . "')");
        $this->db->query("INSERT INTO " . DB_PREFIX . "order_product (order_id, product_id, name, model, quantity, price, total, tax) values (' " . $order_id  . "', '" . $product_id . "', '" . $product_name . "', '" . $product_model . "', '" . $product_quantity . "', '" . $product_price . "', '" . $product_total . "', '" . $product_tax ." ')");
        return 'new';
    }

    public function getFiltrProduct ()
    {
        $filterProduct = array();
        $query = $this->db->query("SELECT " . DB_PREFIX . "ocfilter_option_value_to_product.product_id as productid, " . DB_PREFIX . "ocfilter_option_value_to_product.option_id as filterid, " . DB_PREFIX . "ocfilter_option_value.keyword as filtervalue FROM " . DB_PREFIX . "ocfilter_option_value_to_product, " . DB_PREFIX . "ocfilter_option_value WHERE " . DB_PREFIX . "ocfilter_option_value_to_product.value_id = " . DB_PREFIX . "ocfilter_option_value.value_id");

        foreach ($query->rows as $result) {
            $filterProduct[] = $result;
        }

        return $filterProduct;
    }

    public function getFiltrValueId ($keyword, $filterid)
    {
         $query = $this->db->query("SELECT value_id FROM " . DB_PREFIX . "ocfilter_option_value WHERE keyword = '". $keyword . "' && option_id = '" . $filterid . "'");
        return $query;
    }

    public function updateFiltreProductValue ($productid, $filterid, $value_id)
    {
        $this->db->query("REPLACE INTO " . DB_PREFIX . "ocfilter_option_value_to_product (product_id, value_id, option_id) values ('" . $productid . "', '" . $value_id . "', '" . $filterid . "')");
        return 'updated';
    }

    public function newFiltrValueId($filtervalue, $filterid) {
        $this->db->query("REPLACE INTO " . DB_PREFIX . "ocfilter_option_value (keyword, option_id) values ('" . $filtervalue  . "', '" . $filterid ."')");
        $this->db->query("REPLACE INTO " . DB_PREFIX . "ocfilter_option_value_description (option_id, language_id, name) values ('" . $filterid  . "', '1', '" . $filtervalue ."')");
        return 'new';
    }

    public function getProductToFiltr ($id)
    {
        $filterProduct = array();
        $query = $this->db->query("SELECT " . DB_PREFIX . "ocfilter_option_value_to_product.option_id, " . DB_PREFIX . "ocfilter_option_value.keyword FROM " . DB_PREFIX . "ocfilter_option_value_to_product, " . DB_PREFIX . "ocfilter_option_value WHERE " . DB_PREFIX . "ocfilter_option_value_to_product.product_id = '" . $id . "' && " . DB_PREFIX . "ocfilter_option_value_to_product.value_id = " . DB_PREFIX . "ocfilter_option_value.value_id");

        foreach ($query->rows as $result) {
            $filterProduct[] = $result;
        }

        return $filterProduct;
    }




}
?>
