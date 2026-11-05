<?php
class ControllerStartupStartup extends Controller {
	public function index() {
		// Settings
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "setting WHERE store_id = '0'");
		
		foreach ($query->rows as $setting) {
			if (!$setting['serialized']) {
				$this->config->set($setting['key'], $setting['value']);
			} else {
				$this->config->set($setting['key'], json_decode($setting['value'], true));
			}
		}

		// Set time zone
		if ($this->config->get('config_timezone')) {
			date_default_timezone_set($this->config->get('config_timezone'));

			// Sync PHP and DB time zones.
			$this->db->query("SET time_zone = '" . $this->db->escape(date('P')) . "'");
		}

		// Theme
		$this->config->set('template_cache', $this->config->get('developer_theme'));
				
		// Language
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "language` WHERE code = '" . $this->db->escape($this->config->get('config_admin_language')) . "'");
		
		if ($query->num_rows) {
			$this->config->set('config_language_id', $query->row['language_id']);
		}
		
		// Language
		$language = new Language($this->config->get('config_admin_language'));
		$language->load($this->config->get('config_admin_language'));
		$this->registry->set('language', $language);
		
		// Customer
		$this->registry->set('customer', new Cart\Customer($this->registry));

		// Currency
		$this->registry->set('currency', new Cart\Currency($this->registry));
	
		// Tax
		$this->registry->set('tax', new Cart\Tax($this->registry));
		
		if ($this->config->get('config_tax_default') == 'shipping') {
			$this->tax->setShippingAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
		}

		if ($this->config->get('config_tax_default') == 'payment') {
			$this->tax->setPaymentAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
		}

		$this->tax->setStoreAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));

		// Weight
		$this->registry->set('weight', new Cart\Weight($this->registry));
		
		// Length
		$this->registry->set('length', new Cart\Length($this->registry));
		
		// Cart
		$this->registry->set('cart', new Cart\Cart($this->registry));
		
		// Encryption
		$this->registry->set('encryption', new Encryption($this->config->get('config_encryption')));

		$this->registerCustomReports();
	}

	private function registerCustomReports() {
		$custom_reports = array(
			'customer_repeat_order' => 99,
			'referral'              => 98,
			'cod_processing_time'   => 97,
			'cancelled_orders'      => 96,
			'contact_method'        => 95
		);

		$this->config->set('_gv_custom_reports', array_keys($custom_reports));

		foreach ($custom_reports as $code => $sort_order) {
			if ($code === 'referral') {
				$this->ensureReportSortOrder($code, $sort_order);
				continue;
			}

			$this->registerCustomReport($code, $sort_order);
		}

		$this->ensureCnManagerTrackColumns();
		$this->registerCnManagerTrackEvents();
	}

	private function registerCnManagerTrackEvents() {
		// Серверная фиксация менеджера ТТН (не зависит от JS).
		// before: сразу пишем user_id, даже до появления номера.
		// after: ещё раз + история с номером ТТН.
		$hooks = array(
			'controller/extension/shipping/novaposhta/saveCN/before',
			'controller/extension/shipping/novaposhta/saveCN/after',
			'controller/extension/shipping/novaposhta/addCNToOrder/before',
			'controller/extension/shipping/novaposhta/addCNToOrder/after',
			'controller/extension/shipping/ukrposhta/saveCN/before',
			'controller/extension/shipping/ukrposhta/saveCN/after',
			'controller/extension/shipping/ukrposhta/addCNToOrder/before',
			'controller/extension/shipping/ukrposhta/addCNToOrder/after',
		);

		foreach ($hooks as $trigger) {
			$action = (substr($trigger, -6) === 'before')
				? 'event/cn_manager_track/before'
				: 'event/cn_manager_track/after';

			$this->event->register($trigger, new Action($action), 0);
		}

		$this->event->register(
			'model/extension/shipping/novaposhta/addCNToOrder/after',
			new Action('event/cn_manager_track/modelAfter'),
			0
		);
		$this->event->register(
			'model/extension/shipping/ukrposhta/addCNToOrder/after',
			new Action('event/cn_manager_track/modelAfter'),
			0
		);
	}

	private function ensureCnManagerTrackColumns() {
		try {
			$this->load->model('extension/module/cn_manager_track');
			$this->model_extension_module_cn_manager_track->ensureCnUserIdColumn('novaposhta');
			$this->model_extension_module_cn_manager_track->ensureCnUserIdColumn('ukrposhta');
		} catch (Exception $e) {
			// не ламаємо адмінку, якщо таблиця ще не готова
		}
	}

	private function registerCustomReport($code, $sort_order) {
		if (!is_file(DIR_APPLICATION . 'controller/extension/report/' . $code . '.php')) {
			return;
		}

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "extension` WHERE `type` = 'report' AND `code` = '" . $this->db->escape($code) . "'");

		if (!$query->num_rows) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "extension` SET `type` = 'report', `code` = '" . $this->db->escape($code) . "'");
		}

		$status_key = 'report_' . $code . '_status';
		$sort_key = 'report_' . $code . '_sort_order';

		if ($this->config->get($status_key) === null) {
			$settings = array(
				$status_key => '1',
				$sort_key   => (string)(int)$sort_order
			);

			foreach ($settings as $key => $value) {
				$this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET store_id = '0', `code` = 'report_" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $this->db->escape($value) . "'");
				$this->config->set($key, $value);
			}
		}

		$this->ensureReportSortOrder($code, $sort_order);

		$this->load->model('user/user_group');

		$route = 'extension/report/' . $code;
		$groups = $this->db->query("SELECT user_group_id, permission FROM `" . DB_PREFIX . "user_group`");

		foreach ($groups->rows as $group) {
			$permission = json_decode($group['permission'], true);

			if (!is_array($permission)) {
				$permission = array();
			}

			$should_grant = ((int)$group['user_group_id'] === 1);

			if (!$should_grant && !empty($permission['access'])) {
				foreach ($permission['access'] as $access_route) {
					if (strpos($access_route, 'extension/report/') === 0) {
						$should_grant = true;
						break;
					}
				}
			}

			if (!$should_grant) {
				continue;
			}

			if (empty($permission['access']) || !in_array($route, $permission['access'])) {
				$this->model_user_user_group->addPermission($group['user_group_id'], 'access', $route);
			}

			if (empty($permission['modify']) || !in_array($route, $permission['modify'])) {
				$this->model_user_user_group->addPermission($group['user_group_id'], 'modify', $route);
			}
		}
	}

	private function ensureReportSortOrder($code, $sort_order) {
		$key = 'report_' . $code . '_sort_order';
		$query = $this->db->query("SELECT setting_id, value FROM `" . DB_PREFIX . "setting` WHERE store_id = '0' AND `key` = '" . $this->db->escape($key) . "'");

		if ($query->num_rows) {
			if ((int)$query->row['value'] !== (int)$sort_order) {
				$this->db->query("UPDATE `" . DB_PREFIX . "setting` SET `value` = '" . (int)$sort_order . "' WHERE setting_id = '" . (int)$query->row['setting_id'] . "'");
			}
		}

		$this->config->set($key, (int)$sort_order);
	}
} 
 
