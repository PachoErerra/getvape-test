<?php
class ModelModuleShippingData extends Model {
	public function getNovaPoshtaCities($region, $search = '') {
        if (!$region && !$search) {
            return $this->getDefaultCities();
        }

        require_once(DIR_SYSTEM . 'helper/novaposhta.php');

        $novaposhta = new NovaPoshta($this->registry);

		$sql = "SELECT `" . $novaposhta->description_field . "` AS `description` FROM `" . DB_PREFIX . "novaposhta_cities` WHERE 1";

		if ($region) {
            $zone_info = $this->getZone($region);

            if ($zone_info) {
                $region = $novaposhta->getRegionRef($zone_info['name']);
            }

		    $sql .= " AND `Area` = '" . $this->db->escape($region) . "'";
        }

		if ($search) {
			$sql .= " AND (`Description` LIKE '" . $this->db->escape($search) . "%' OR `DescriptionRu` LIKE '" . $this->db->escape($search) . "%')";
		}
		
		$sql .= " ORDER BY  `" . $novaposhta->description_field . "` COLLATE utf8_unicode_ci";
		
		return $this->db->query($sql)->rows;
	}
	
	public function getNovaPoshtaDepartments($city, $search = '') {
        require_once(DIR_SYSTEM . 'helper/novaposhta.php');

        $novaposhta = new NovaPoshta($this->registry);

        if (version_compare(VERSION, '3', '>=')) {
            $settings = $this->config->get('shipping_novaposhta');
        } else {
            $settings = $this->config->get('novaposhta');
        }

		$sql = "SELECT `" . $novaposhta->description_field . "` AS `description` FROM `" . DB_PREFIX . "novaposhta_departments` WHERE (`CityDescription` = '" . $this->db->escape($city) . "' OR `CityDescriptionRu` = '" . $this->db->escape($city) . "')";

        if (!empty($settings['shipping_methods']['poshtomat']['status'])) {
            $sql .= " AND `CategoryOfWarehouse` <> 'Postomat'";
        }
			
        if ($search) {
            $sql .= " AND (`Description` LIKE '%" . $this->db->escape($search) . "%' OR `DescriptionRu` LIKE '%" . $this->db->escape($search) . "%')";
        }

		if (isset($settings['shipping_methods']['department']['department_types'])) {
			foreach ($settings['shipping_methods']['department']['department_types'] as $k => $v) {
                $settings['shipping_methods']['department']['department_types'][$k] = "'" . $v . "'";
			}

			$sql .= " AND `TypeOfWarehouse` IN (" . implode(',', $settings['shipping_methods']['department']['department_types']) . ")";
		}

        if (isset($settings['shipping_methods']['department']['department_statuses'])) {
            foreach ($settings['shipping_methods']['department']['department_statuses'] as $k => $v) {
                $settings['shipping_methods']['department']['department_statuses'][$k] = "'" . $v . "'";
            }

            $sql .= " AND `WarehouseStatus` IN (" . implode(',', $settings['shipping_methods']['department']['department_statuses']) . ")";
        }
		
		if (isset($this->session->data['shippingdata']['cart_weight']) && $settings['shipping_methods']['department']['filter_weight']) {
			$sql .= " AND (`TotalMaxWeightAllowed` >= '" . $this->session->data['shippingdata']['cart_weight'] . "' OR (`TotalMaxWeightAllowed` = 0 AND (`PlaceMaxWeightAllowed` >= '" . $this->session->data['shippingdata']['cart_weight'] . "' OR `PlaceMaxWeightAllowed` = 0)))";
		}

        if ($settings['shipping_methods']['department']['filter_dimensions'] && isset($this->session->data['shippingdata']['length'])) {
            $sql .= " AND (`ReceivingLimitationsOnDimensions_length` >= '" . $this->session->data['shippingdata']['length'] . "'
             AND `ReceivingLimitationsOnDimensions_width` >= '" . $this->session->data['shippingdata']['width'] . "'
             AND `ReceivingLimitationsOnDimensions_height` >= '" . $this->session->data['shippingdata']['height'] . "')";
        }

		$sql .= " ORDER BY `Number`+0";

		return $this->db->query($sql)->rows;	
	}

    public function getNovaPoshtaPoshtomats($city, $search = '') {
        require_once(DIR_SYSTEM . 'helper/novaposhta.php');

        $novaposhta = new NovaPoshta($this->registry);

        if (version_compare(VERSION, '3', '>=')) {
            $settings = $this->config->get('shipping_novaposhta');
        } else {
            $settings = $this->config->get('novaposhta');
        }

        $sql = "SELECT `" . $novaposhta->description_field . "` AS `description` FROM `" . DB_PREFIX . "novaposhta_departments` WHERE `CategoryOfWarehouse` = 'Postomat' AND (`CityDescription` = '" . $this->db->escape($city) . "' OR `CityDescriptionRu` = '" . $this->db->escape($city) . "')";

        if ($search) {
            $sql .= " AND (`Description` LIKE '%" . $this->db->escape($search) . "%' OR `DescriptionRu` LIKE '%" . $this->db->escape($search) . "%')";
        }

        if (isset($this->session->data['shippingdata']['cart_weight']) && $settings['shipping_methods']['poshtomat']['filter_weight']) {
            $sql .= " AND (`TotalMaxWeightAllowed` >= '" . $this->session->data['shippingdata']['cart_weight'] . "' OR (`TotalMaxWeightAllowed` = 0 AND (`PlaceMaxWeightAllowed` >= '" . $this->session->data['shippingdata']['cart_weight'] . "' OR `PlaceMaxWeightAllowed` = 0)))";
        }

        if ($settings['shipping_methods']['poshtomat']['filter_dimensions'] && isset($this->session->data['shippingdata']['length'])) {
            $sql .= " AND (`ReceivingLimitationsOnDimensions_length` >= '" . $this->session->data['shippingdata']['length'] . "'
             AND `ReceivingLimitationsOnDimensions_width` >= '" . $this->session->data['shippingdata']['width'] . "'
             AND `ReceivingLimitationsOnDimensions_height` >= '" . $this->session->data['shippingdata']['height'] . "')";
        }

        $sql .= " ORDER BY `Number`+0";

        return $this->db->query($sql)->rows;
    }

    public function getNovaPoshtaSettlements($region, $search) {
        $data = array();

        if (version_compare(VERSION, '3', '>=')) {
            $settings = $this->config->get('shipping_novaposhta');
        } else {
            $settings = $this->config->get('novaposhta');
        }

        if (empty($settings['settlements'])) {
            return false;
        }

        require_once(DIR_SYSTEM . 'helper/novaposhta.php');

        $novaposhta = new NovaPoshta($this->registry);

        if ($region) {
            $zone_info = $this->getZone($region);

            if ($zone_info) {
                $region = $novaposhta->getRegionName($zone_info['name']);
            }
        }

        $result = $novaposhta->searchSettlements($search);

        foreach ($result as $v) {
            if (!$region || $region == $v['Area']) {
                $data[] = array(
                    'id' => $v['Ref'],
                    'description' => $v['MainDescription'],
                    'full_description' => $v['Present']
                );
            }
        }

        return $data;
    }

    public function getNovaPoshtaStreets($settlement, $search) {
        $data = array();

        if (version_compare(VERSION, '3', '>=')) {
            $settings = $this->config->get('shipping_novaposhta');
        } else {
            $settings = $this->config->get('novaposhta');
        }

        if (empty($settings['streets'])) {
            return false;
        }

        require_once(DIR_SYSTEM . 'helper/novaposhta.php');

        $novaposhta = new NovaPoshta($this->registry);

        $result = $novaposhta->searchSettlementStreets($settlement, $search);

        foreach ($result as $v) {
            $data[] = array(
                'description' => $v['Present']
            );
        }

        return $data;
    }

    public function getRozetkaDeliveryCities($search = '') {
	    if ($this->language->get('code') == 'en' || $this->language->get('code') == 'en-gb') {
            $district_text = ' dist.';
            $region_text = ' reg.';
        } else {
            $district_text = ' р-н.';
            $region_text = ' обл.';
        }

        $sql = "SELECT IF(`district_name` <> '', CONCAT(`name`, ', ', `district_name`, '" . $district_text . "', ', ', `region_name`, '" . $region_text . "'), `name`) as `description` FROM `" . DB_PREFIX . "rozetka_delivery_cities` WHERE 1";

        if ($search) {
            $sql .= " AND (`name` LIKE '" . $this->db->escape($search) . "%' OR SUBSTRING_INDEX(`name`, ' ', -1) LIKE '" . $this->db->escape($search) . "%')";
        }

        $sql .= " ORDER BY `population` DESC";

        return $this->db->query($sql)->rows;
    }

    public function getRozetkaDeliveryDepartments($city, $search = '') {
        if (version_compare(VERSION, '3', '>=')) {
            $settings = $this->config->get('shipping_rozetka_delivery');
        } else {
            $settings = $this->config->get('rozetka_delivery');
        }

        $city_parts = explode(', ', $city);

        $sql = "SELECT `name` as `description` FROM `" . DB_PREFIX . "rozetka_delivery_departments` WHERE `can_give_out_tracks` = 1 AND `city_name` = '" . $this->db->escape($city_parts[0]) . "'";

        if (!empty($city_parts[1])) {
            $district = trim(strstr($city_parts[1], ' ', true));

            $sql .= " AND `district_name` = '" . $this->db->escape($district) . "'";
        }

        if (!empty($city_parts[2])) {
            $region = trim(strstr($city_parts[2], ' ', true));

            $sql .= " AND `region_name` = '" . $this->db->escape($region) . "'";
        }

        if ($search) {
            $sql .= " AND `name` LIKE '%" . $this->db->escape($search) . "%'";
        }

        if (isset($this->session->data['shippingdata']['cart_weight']) && $settings['shipping_methods']['department']['filter_weight']) {
            $sql .= " AND (`volumeWeight` >= '" . $this->session->data['shippingdata']['cart_weight'] . "' OR `volumeWeight` = 0)";
        }

        $sql .= " ORDER BY `name`";
        
        return $this->db->query($sql)->rows;
    }

    public function getUkrPoshtaCities($region, $search = '') {
        if (!$region && !$search) {
            return $this->getDefaultCities();
        }

        require_once(DIR_SYSTEM . 'helper/ukrposhta.php');

        $ukrposhta = new UkrPoshta($this->registry);

        if ($ukrposhta->lang == 'EN') {
            $district_text = ' dist.';
            $region_text = ' reg.';
        } else {
            $district_text = ' р-н.';
            $region_text = ' обл.';
        }

        if ($region) {
            $zone_info = $this->getZone($region);

            if ($zone_info) {
                $region = $ukrposhta->getRegionID($zone_info['name']);
            }

            $sql = "SELECT *, CONCAT(`CITY_" . $ukrposhta->lang . "`, ', ', `DISTRICT_" . $ukrposhta->lang . "`, '" . $district_text . "') as `description`, CONCAT(`SHORTCITYTYPE_" . $ukrposhta->lang . "`, ' ', `CITY_" . $ukrposhta->lang . "`, ', ', `DISTRICT_" . $ukrposhta->lang . "`, '" . $district_text . "') as `full_description` FROM `" . DB_PREFIX . "ukrposhta_cities` WHERE `REGION_ID` = '" . $this->db->escape($region) . "'";
        } else {
            $sql = "SELECT *, CONCAT(`CITY_" . $ukrposhta->lang . "`, ', ', `DISTRICT_" . $ukrposhta->lang . "`, '" . $district_text . "', ', ', `REGION_" . $ukrposhta->lang . "`, '" . $region_text . "') as `description`, CONCAT(`SHORTCITYTYPE_" . $ukrposhta->lang . "`, ' ', `CITY_" . $ukrposhta->lang . "`, ', ', `DISTRICT_" . $ukrposhta->lang . "`, '" . $district_text . "', ', ', `REGION_" . $ukrposhta->lang . "`, '" . $region_text . "') as `full_description` FROM `" . DB_PREFIX . "ukrposhta_cities` WHERE 1";
        }

        if ($search) {
            $sql .= " AND (`CITY_UA` LIKE '" . $this->db->escape($search) . "%' OR `CITY_EN` LIKE '" . $this->db->escape($search) . "%' OR `CITY_RU`  LIKE '" . $this->db->escape($search) . "%')";
        }

        $sql .= " ORDER BY `POPULATION` DESC";

        return $this->db->query($sql)->rows;
    }

    public function getUkrPoshtaDepartments($city, $search = '', $method = 'standard_department') {
        if (version_compare(VERSION, '3', '>=')) {
            $settings = $this->config->get('shipping_ukrposhta');
        } else {
            $settings = $this->config->get('ukrposhta');
        }

        $city_parts = explode(', ', $city);

        $sql = "SELECT *, `POSTINDEX` as `postcode`, CONCAT(`POSTINDEX`, ', ', `ADDRESS`) as `description` FROM `" . DB_PREFIX . "ukrposhta_departments` WHERE (`CITY_UA` = '" . $this->db->escape($city_parts[0]) . "' OR `CITY_EN` = '" . $this->db->escape($city_parts[0]) . "' OR `CITY_RU` = '" . $this->db->escape($city_parts[0]) . "')";

        if (isset($settings['shipping_methods'][$method]['department_types'])) {
            foreach ($settings['shipping_methods'][$method]['department_types'] as $k => $v) {
                $settings['shipping_methods'][$method]['department_types'][$k] = "'" . $v . "'";
            }

            $sql .= " AND `TYPE_ACRONYM` IN (" . implode(',', $settings['shipping_methods'][$method]['department_types']) . ")";
        }

        if (isset($settings['shipping_methods'][$method]['department_statuses'])) {
            foreach ($settings['shipping_methods'][$method]['department_statuses'] as $k => $v) {
                $settings['shipping_methods'][$method]['department_statuses'][$k] = "'" . $v . "'";
            }

            $sql .= " AND `LOCK_CODE` IN (" . implode(',', $settings['shipping_methods'][$method]['department_statuses']) . ")";
        }

        if (!empty($city_parts[1])) {
            $district = trim(strstr($city_parts[1], ' ', true));

            $sql .= " AND (`DISTRICT_UA` = '" . $this->db->escape($district) . "' OR `DISTRICT_EN` = '" . $this->db->escape($district) . "' OR `DISTRICT_RU` = '" . $this->db->escape($district) . "')";
        }

        if (!empty($city_parts[2])) {
            $region = trim(strstr($city_parts[2], ' ', true));

            $sql .= " AND (`REGION_UA` = '" . $this->db->escape($region) . "' OR `REGION_EN` = '" . $this->db->escape($region) . "' OR `REGION_RU` = '" . $this->db->escape($region) . "')";
        }

        if ($search) {
            $sql .= " AND (`POSTINDEX` LIKE '" . $this->db->escape($search) . "%' OR `ADDRESS` LIKE '%" . $this->db->escape($search) . "%')";
        }

        $sql .= " ORDER BY `POSTINDEX`";

        return $this->db->query($sql)->rows;
    }

    public function getUkrPoshtaPostCodes($search = '', $method = 'standard_department') {
        require_once(DIR_SYSTEM . 'helper/ukrposhta.php');

        $ukrposhta = new UkrPoshta($this->registry);

        if (version_compare(VERSION, '3', '>=')) {
            $settings = $this->config->get('shipping_ukrposhta');
        } else {
            $settings = $this->config->get('ukrposhta');
        }

        if ($ukrposhta->lang == 'EN') {
            $district_text = ' dist.';
            $region_text = ' reg.';
        } else {
            $district_text = ' р-н.';
            $region_text = ' обл.';
        }

        $sql = "SELECT `POSTINDEX` as `description`, CONCAT(`POSTINDEX`, ', ', `ADDRESS`, ', ', `SHORTCITYTYPE_" . $ukrposhta->lang . "`, ' ', `CITY_" . $ukrposhta->lang . "`, ', ', `DISTRICT_" . $ukrposhta->lang . "`, '" . $district_text . "', ', ', `REGION_" . $ukrposhta->lang . "`, '" . $region_text . "') as `full_description`, CONCAT(`CITY_" . $ukrposhta->lang . "`, ', ', `DISTRICT_" . $ukrposhta->lang . "`, '" . $district_text . "', ', ', `REGION_" . $ukrposhta->lang . "`, '" . $region_text . "') as `city`, CONCAT(`POSTINDEX`, ', ', `ADDRESS`) as `department` FROM `" . DB_PREFIX . "ukrposhta_departments` WHERE 1";

        if (isset($settings['shipping_methods'][$method]['department_types'])) {
            foreach ($settings['shipping_methods'][$method]['department_types'] as $k => $v) {
                $settings['shipping_methods'][$method]['department_types'][$k] = "'" . $v . "'";
            }

            $sql .= " AND `TYPE_ACRONYM` IN (" . implode(',', $settings['shipping_methods'][$method]['department_types']) . ")";
        }

        if (isset($settings['shipping_methods'][$method]['department_statuses'])) {
            foreach ($settings['shipping_methods'][$method]['department_statuses'] as $k => $v) {
                $settings['shipping_methods'][$method]['department_statuses'][$k] = "'" . $v . "'";
            }

            $sql .= " AND `LOCK_CODE` IN (" . implode(',', $settings['shipping_methods'][$method]['department_statuses']) . ")";
        }

        if ($search) {
            $sql .= " AND (`POSTINDEX` LIKE '" . $this->db->escape($search) . "%' OR `ADDRESS` LIKE '%" . $this->db->escape($search) . "%')";
        }

        $sql .= " ORDER BY `POSTINDEX`";

        return $this->db->query($sql)->rows;
    }

    public function getJustinCities($region, $search = '') {
        if (!$region && !$search) {
            return $this->getDefaultCities();
        }

        require_once(DIR_SYSTEM . 'helper/justin.php');

        $justin = new Justin($this->registry);

        if ($region) {
            $zone_info = $this->getZone($region);

            if ($zone_info) {
                $region = $justin->getRegionUUID($zone_info['name']);
            }

            $sql = "SELECT `descr` AS `description` FROM `" . DB_PREFIX . "justin_cities` WHERE `region_uuid` = '" . $this->db->escape($region) . "'";
        } else {
            $sql = "SELECT CONCAT(`descr`, ' (', `region_descr`, ' обл.)') AS `description` FROM `" . DB_PREFIX . "justin_cities` WHERE 1";
        }

        if ($search) {
            $sql .= " AND `descr` LIKE '" . $this->db->escape($search) . "%'";
        } else {
            $sql .= " AND `language` = '" . $justin->lang . "'";
        }

        $sql .= " GROUP BY `uuid` ORDER BY `descr` ";

        return $this->db->query($sql)->rows;
    }

    public function getJustinDepartments($city, $search = '') {
        if (version_compare(VERSION, '3', '>=')) {
            $settings = $this->config->get('shipping_justin');
        } else {
            $settings = $this->config->get('justin');
        }

        $t_array = explode('(', $city);

        $sql = "SELECT `descr` AS `description` FROM `" . DB_PREFIX . "justin_departments` WHERE `city_descr` = '" . $this->db->escape(trim($t_array[0])) . "'";

        if ($search) {
            $sql .= " AND `descr` LIKE '%" . $this->db->escape($search) . "%'";
        }

        if (isset($settings['shipping_methods']['department']['department_types'])) {
            foreach ($settings['shipping_methods']['department']['department_types'] as $k => $v) {
                $settings['shipping_methods']['department']['department_types'][$k] = "'" . $v . "'";
            }

            $sql .= " AND `branchType_uuid` IN (" . implode(',', $settings['shipping_methods']['department']['department_types']) . ")";
        }

        if (isset($settings['shipping_methods']['department']['department_statuses'])) {
            $sql .= " AND `StatusDepart` IN (" . implode(',', $settings['shipping_methods']['department']['department_statuses']) . ")";
        }

        if (isset($this->session->data['shippingdata']['cart_weight']) && $settings['shipping_methods']['department']['filter_weight']) {
            $sql .= " AND `weight_limit` >= '" . $this->session->data['shippingdata']['cart_weight'] . "'";
        }

        $sql .= " ORDER BY `departNumber`+0";

        return $this->db->query($sql)->rows;
    }

	private function getDefaultCities() {
        $data = array();

        if ($this->language->get('code') == 'ru' || $this->language->get('code') == 'ru-ru') {
            $description = 'description_ru';
        } else {
            $description = 'description';
        }

        $cities = array(
            array(
                'description'   => 'Київ',
                'description_ru' => 'Киев',
                'district_description' => ''
            ),
            array(
                'description'   => 'Харків',
                'description_ru' => 'Харьков',
                'district_description' => ''
            ),
            array(
                'description'   => 'Дніпро',
                'description_ru' => 'Днепр',
                'district_description' => ''
            ),
            array(
                'description'   => 'Одеса',
                'description_ru' => 'Одесса',
                'district_description' => ''

            ),
            /* Delivery is temporarily not carried out
            array(
                'description'   => 'Донецьк',
                'description_ru' => 'Донецк',
                'district_description' => ''
            ),
            */
            array(
                'description'   => 'Запоріжжя',
                'description_ru' => 'Запорожье',
                'district_description' => ''
            ),
            array(
                'description'   => 'Львів',
                'description_ru' => 'Львов',
                'district_description' => ''
            ),
            array(
                'description'   => 'Кривий Ріг',
                'description_ru' => 'Кривой Рог',
                'district_description' => ''
            ),
            array(
                'description'   => 'Миколаїв',
                'description_ru' => 'Николаев',
                'district_description' => ''
            ),
            array(
                'description'   => 'Маріуполь',
                'description_ru' => 'Мариуполь',
                'district_description' => ''
            ),
            /* Delivery is temporarily not carried out
           array(
               'description'   => 'Луганськ',
               'description_ru' => 'Луганск',
                'district_description' => ''
           ),
           */
            array(
                'description'   => 'Вінниця',
                'description_ru' => 'Винница',
                'district_description' => ''
            ),
            /* Delivery is temporarily not carried out
           array(
               'description'   => 'Севастополь',
               'description_ru' => 'Севастополь',
                'district_description' => ''
           ),
           */
            /* Delivery is temporarily not carried out
          array(
              'description'   => 'Сімферополь',
              'description_ru' => 'Симферополь',
                'district_description' => ''
          ),
          */
            array(
                'description'   => 'Херсон',
                'description_ru' => 'Херсон',
                'district_description' => ''
            ),
            array(
                'description'   => 'Полтава',
                'description_ru' => 'Полтава',
                'district_description' => ''
            ),
            array(
                'description'   => 'Чернігів',
                'description_ru' => 'Чернигов',
                'district_description' => ''
            ),
            array(
                'description'   => 'Черкаси',
                'description_ru' => 'Черкассы',
                'district_description' => ''
            ),
            array(
                'description'   => 'Суми',
                'description_ru' => 'Сумы',
                'district_description' => ''
            ),
            array(
                'description'   => 'Хмельницький',
                'description_ru' => 'Хмельницкий',
                'district_description' => ''
            ),
            array(
                'description'   => 'Житомир',
                'description_ru' => 'Житомир',
                'district_description' => ''
            ),
            array(
                'description'   => 'Кропивницький',
                'description_ru' => 'Кропивницкий',
                'district_description' => ''
            ),
            array(
                'description'   => 'Рівне',
                'description_ru' => 'Ровно',
                'district_description' => ''
            ),
            array(
                'description'   => 'Чернівці',
                'description_ru' => 'Черновцы',
                'district_description' => ''
            ),
            array(
                'description'   => 'Тернопіль',
                'description_ru' => 'Тернополь',
                'district_description' => ''
            ),
            array(
                'description'   => 'Івано-Франківськ',
                'description_ru' => 'Ивано-Франковск',
                'district_description' => ''
            ),
            array(
                'description'   => 'Луцьк',
                'description_ru' => 'Луцк',
                'district_description' => ''
            ),
            array(
                'description'   => 'Ужгород',
                'description_ru' => 'Ужгород',
                'district_description' => ''
            )
        );

        foreach ($cities as $city) {
            $data[] = array(
                'description' => $city[$description],
                'district_description' => ''
            );
        }

        return $data;
    }

    public function getZone($zone_id) {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE `zone_id` = '" . (int)$zone_id . "' AND `status` = '1'");

        return $query->row;
    }

    /**
     * Resolve free-form / Present / Description city text to canonical CityDescription.
     */
    public function resolveNovaPoshtaCityName($city, $sub_method = 'department') {
        $city = trim((string)$city);

        if ($city === '') {
            return '';
        }

        $candidates = array($city);

        // Settlements Present: "Коцюбинське, Київська обл., Бучанський р-н"
        if (strpos($city, ',') !== false) {
            $candidates[] = trim(explode(',', $city, 2)[0]);
        }

        // Cities Description with region: "Козятин (місто, Вінницька обл.)"
        if (preg_match('/^(.+?)\s*\(/u', $city, $m)) {
            $candidates[] = trim($m[1]);
        }

        $candidates = array_values(array_unique(array_filter(array_map('trim', $candidates))));

        foreach ($candidates as $candidate) {
            foreach ($this->getDefaultCities() as $default) {
                if (!empty($default['description']) && $this->npNamesEqual($default['description'], $candidate)) {
                    return $default['description'];
                }
            }

            $sql = "SELECT `Description` FROM `" . DB_PREFIX . "novaposhta_cities`
                WHERE `Description` = '" . $this->db->escape($candidate) . "'
                   OR `DescriptionRu` = '" . $this->db->escape($candidate) . "'
                LIMIT 1";
            $query = $this->db->query($sql);

            if ($query->num_rows) {
                return $query->row['Description'];
            }

            // Short name against "Name (region...)" rows in cities directory
            $sql = "SELECT `Description` FROM `" . DB_PREFIX . "novaposhta_cities`
                WHERE `Description` LIKE '" . $this->db->escape($candidate) . " (%'
                   OR `DescriptionRu` LIKE '" . $this->db->escape($candidate) . " (%'
                ORDER BY CHAR_LENGTH(`Description`) ASC
                LIMIT 1";
            $query = $this->db->query($sql);

            if ($query->num_rows) {
                return $query->row['Description'];
            }
        }

        // Settlements API (doors and restored Present values for department/poshtomat)
        foreach ($candidates as $candidate) {
            $settlements = $this->getNovaPoshtaSettlements('', $candidate);

            if (!is_array($settlements)) {
                continue;
            }

            foreach ($settlements as $row) {
                if (!empty($row['full_description']) && $this->npNamesEqual($row['full_description'], $city)) {
                    return !empty($row['description']) ? $row['description'] : $candidate;
                }
                if (!empty($row['description']) && $this->npNamesEqual($row['description'], $candidate)) {
                    return $row['description'];
                }
                if (!empty($row['full_description']) && $this->npNamesEqual($row['full_description'], $candidate)) {
                    return !empty($row['description']) ? $row['description'] : $candidate;
                }
            }
        }

        return '';
    }

    /**
     * Match against Nova Poshta directory (city / settlement), including Present format.
     */
    public function isValidNovaPoshtaCity($city, $sub_method = 'department') {
        return $this->resolveNovaPoshtaCityName($city, $sub_method) !== '';
    }

    /**
     * Match warehouse / poshtomat against directory for a city.
     * Accepts full Description, ShortAddress, department number, and common free-text variants.
     */
    public function isValidNovaPoshtaWarehouse($city, $warehouse, $sub_method = 'department') {
        $city = trim((string)$city);
        $warehouse = trim((string)$warehouse);

        if ($city === '' || $warehouse === '') {
            return false;
        }

        $canonical_city = $this->resolveNovaPoshtaCityName($city, $sub_method);

        if ($canonical_city === '') {
            return false;
        }

        $city_names = array_values(array_unique(array($canonical_city, $city)));
        // Prefer short city name for CityDescription lookups
        if (strpos($canonical_city, '(') !== false && preg_match('/^(.+?)\s*\(/u', $canonical_city, $m)) {
            $city_names[] = trim($m[1]);
        }
        if (strpos($canonical_city, ',') !== false) {
            $city_names[] = trim(explode(',', $canonical_city, 2)[0]);
        }
        $city_names = array_values(array_unique(array_filter(array_map('trim', $city_names))));

        foreach ($city_names as $city_name) {
            if ($sub_method === 'poshtomat') {
                $rows = $this->getNovaPoshtaPoshtomats($city_name, '');
            } else {
                $rows = $this->getNovaPoshtaDepartments($city_name, '');
            }

            if (!is_array($rows)) {
                continue;
            }

            foreach ($rows as $row) {
                if (!empty($row['description']) && $this->npNamesEqual($row['description'], $warehouse)) {
                    return true;
                }
            }
        }

        $category_sql = '';
        if ($sub_method === 'poshtomat') {
            $category_sql = " AND `CategoryOfWarehouse` = 'Postomat'";
        } elseif ($sub_method === 'department') {
            $category_sql = " AND `CategoryOfWarehouse` <> 'Postomat'";
        }

        $city_sql_parts = array();
        foreach ($city_names as $city_name) {
            $city_sql_parts[] = "`CityDescription` = '" . $this->db->escape($city_name) . "'";
            $city_sql_parts[] = "`CityDescriptionRu` = '" . $this->db->escape($city_name) . "'";
        }
        $city_sql = '(' . implode(' OR ', $city_sql_parts) . ')';

        // Exact Description / ShortAddress
        $sql = "SELECT 1 FROM `" . DB_PREFIX . "novaposhta_departments`
            WHERE " . $city_sql . "
              AND (
                    `Description` = '" . $this->db->escape($warehouse) . "'
                 OR `DescriptionRu` = '" . $this->db->escape($warehouse) . "'
                 OR `ShortAddress` = '" . $this->db->escape($warehouse) . "'
                 OR `ShortAddressRu` = '" . $this->db->escape($warehouse) . "'
              )"
            . $category_sql . " LIMIT 1";

        if ($this->db->query($sql)->num_rows) {
            return true;
        }

        // Department / poshtomat number: "2", "№2", "відділення номер 2", "поштомат 41421"
        $number = '';
        if (preg_match('/^\d+$/u', $warehouse)) {
            $number = $warehouse;
        } elseif (preg_match('/(?:№|номер|number|N[oо]?\.?)\s*(\d+)/ui', $warehouse, $m)) {
            $number = $m[1];
        } elseif (preg_match('/(?:поштомат|почтомат)\s*(\d+)/ui', $warehouse, $m)) {
            $number = $m[1];
        }

        if ($number !== '') {
            $sql = "SELECT `Description`, `ShortAddress`, `Number` FROM `" . DB_PREFIX . "novaposhta_departments`
                WHERE " . $city_sql . "
                  AND `Number` = '" . (int)$number . "'"
                . $category_sql . " LIMIT 5";
            $query = $this->db->query($sql);

            if ($query->num_rows === 1) {
                return true;
            }

            // Several rows with same Number are rare; accept if street token overlaps
            if ($query->num_rows > 1) {
                $warehouse_norm = $this->npNormalize($warehouse);
                foreach ($query->rows as $row) {
                    $hay = $this->npNormalize(
                        (!empty($row['Description']) ? $row['Description'] : '') . ' ' .
                        (!empty($row['ShortAddress']) ? $row['ShortAddress'] : '')
                    );
                    if ($this->npTextOverlap($warehouse_norm, $hay)) {
                        return true;
                    }
                }
            }
        }

        // Soft match: long free-text that clearly refers to a real department street/address
        $sql = "SELECT `Description`, `DescriptionRu`, `ShortAddress`, `ShortAddressRu` FROM `" . DB_PREFIX . "novaposhta_departments`
            WHERE " . $city_sql
            . $category_sql . " LIMIT 300";
        $query = $this->db->query($sql);

        if (!$query->num_rows) {
            return false;
        }

        $warehouse_norm = $this->npNormalize($warehouse);
        $warehouse_len = function_exists('mb_strlen') ? mb_strlen($warehouse_norm, 'UTF-8') : strlen($warehouse_norm);

        foreach ($query->rows as $row) {
            foreach (array('Description', 'DescriptionRu', 'ShortAddress', 'ShortAddressRu') as $field) {
                if (empty($row[$field])) {
                    continue;
                }
                $hay = $this->npNormalize($row[$field]);
                if ($hay === $warehouse_norm) {
                    return true;
                }
                if ($warehouse_len >= 8 && ($this->npContains($hay, $warehouse_norm) || $this->npContains($warehouse_norm, $hay))) {
                    return true;
                }
                if ($this->npTextOverlap($warehouse_norm, $hay)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function npContains($haystack, $needle) {
        if ($needle === '') {
            return false;
        }

        if (function_exists('mb_strpos')) {
            return mb_strpos($haystack, $needle, 0, 'UTF-8') !== false;
        }

        return strpos($haystack, $needle) !== false;
    }

    private function npNormalize($value) {
        $value = html_entity_decode((string)$value, ENT_QUOTES, 'UTF-8');
        $value = preg_replace('/\s+/u', ' ', trim($value));

        if (function_exists('mb_strtolower')) {
            return mb_strtolower($value, 'UTF-8');
        }

        return strtolower($value);
    }

    private function npNamesEqual($a, $b) {
        return $this->npNormalize($a) === $this->npNormalize($b);
    }

    private function npTextOverlap($a, $b) {
        // Compare significant tokens (street names / house numbers), ignore service words
        $stop = array(
            'вул', 'вулиця', 'ул', 'улица', 'просп', 'проспект', 'бул', 'бульвар',
            'відділення', 'отделение', 'номер', 'поштомат', 'почтомат', 'нова', 'пошта',
            'новая', 'почта', 'до', 'кг', 'на', 'одне', 'место', 'місце', 'пункт',
            'приймання', 'видачі', 'выдачи'
        );

        $tokenize = function ($text) use ($stop) {
            $text = preg_replace('/[^\p{L}\p{N}]+/u', ' ', $text);
            $parts = preg_split('/\s+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
            $out = array();

            foreach ($parts as $part) {
                if (function_exists('mb_strlen')) {
                    if (mb_strlen($part, 'UTF-8') < 2) {
                        continue;
                    }
                } elseif (strlen($part) < 2) {
                    continue;
                }
                if (in_array($part, $stop, true)) {
                    continue;
                }
                $out[] = $part;
            }

            return array_values(array_unique($out));
        };

        $ta = $tokenize($a);
        $tb = $tokenize($b);

        if (!$ta || !$tb) {
            return false;
        }

        $intersect = array_intersect($ta, $tb);

        // Need at least 2 shared tokens (e.g. street + house) or 1 long unique token
        if (count($intersect) >= 2) {
            return true;
        }

        foreach ($intersect as $token) {
            $len = function_exists('mb_strlen') ? mb_strlen($token, 'UTF-8') : strlen($token);
            if ($len >= 5) {
                return true;
            }
        }

        return false;
    }

    public function getShippingMethod() {
        if (!empty($this->request->post['shipping_method']) && is_string($this->request->post['shipping_method'])) {
            $data = explode('.', $this->request->post['shipping_method']);
        } elseif (!empty($this->request->post['shipping']) && is_string($this->request->post['shipping'])) {
            $data = explode('.', $this->request->post['shipping']);
        } elseif (isset($this->session->data['shipping_method'], $this->session->data['shipping_method']['code']) && is_string($this->session->data['shipping_method']['code'])) {
            $data = explode('.', $this->session->data['shipping_method']['code']);
        } else {
            $data = array('', '');
        }

        return array (
            'method'     => $data[0],
            'sub_method' => $data[1]
        );
    }
}

class ModelExtensionModuleShippingData extends ModelModuleShippingData {

}