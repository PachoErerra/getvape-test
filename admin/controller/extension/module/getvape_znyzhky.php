<?php
/**
 * GetVape Знижки сайту
 * Author: Not The Same (https://ntsame.agency/)
 * Contact: hoffmanxyz@gmail.com
 */
class ControllerExtensionModuleGetvapeZnyzhky extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/module/getvape_znyzhky');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/module/getvape_znyzhky');
		$this->getList();
	}

	public function add() {
		$this->load->language('extension/module/getvape_znyzhky');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/module/getvape_znyzhky');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_module_getvape_znyzhky->addRule($this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('extension/module/getvape_znyzhky', 'user_token=' . $this->session->data['user_token'] . $this->getListUrl(), true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('extension/module/getvape_znyzhky');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/module/getvape_znyzhky');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_extension_module_getvape_znyzhky->editRule($this->request->get['rule_id'], $this->request->post);
			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('extension/module/getvape_znyzhky', 'user_token=' . $this->session->data['user_token'] . $this->getListUrl(), true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('extension/module/getvape_znyzhky');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/module/getvape_znyzhky');

		if (isset($this->request->post['selected']) && $this->validateModify()) {
			foreach ($this->request->post['selected'] as $rule_id) {
				$this->model_extension_module_getvape_znyzhky->deleteRule($rule_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');
			$this->response->redirect($this->url->link('extension/module/getvape_znyzhky', 'user_token=' . $this->session->data['user_token'] . $this->getListUrl(), true));
		}

		$this->getList();
	}

	public function apply() {
		$this->load->language('extension/module/getvape_znyzhky');
		$this->load->model('extension/module/getvape_znyzhky');

		if ($this->validateModify()) {
			$this->model_extension_module_getvape_znyzhky->applyAll();
			$this->session->data['success'] = $this->language->get('text_applied');
		}

		$this->response->redirect($this->url->link('extension/module/getvape_znyzhky', 'user_token=' . $this->session->data['user_token'] . $this->getListUrl(), true));
	}

	public function install() {
		$this->load->model('extension/module/getvape_znyzhky');
		$this->model_extension_module_getvape_znyzhky->install();

		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('module_getvape_znyzhky', array('module_getvape_znyzhky_status' => 1));

		$this->load->model('user/user_group');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/getvape_znyzhky');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/getvape_znyzhky');
	}

	public function uninstall() {
		$this->load->model('extension/module/getvape_znyzhky');
		$this->model_extension_module_getvape_znyzhky->uninstall();

		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('module_getvape_znyzhky');
	}

	protected function getListUrl() {
		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		return $url;
	}

	protected function getList() {
		$data = array();

		foreach ($this->language->all() as $key => $value) {
			$data[$key] = $value;
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'r.date_modified';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = $this->getListUrl();

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/getvape_znyzhky', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('extension/module/getvape_znyzhky/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('extension/module/getvape_znyzhky/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['apply'] = $this->url->link('extension/module/getvape_znyzhky/apply', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['rules'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$rule_total = $this->model_extension_module_getvape_znyzhky->getTotalRules();
		$results = $this->model_extension_module_getvape_znyzhky->getRules($filter_data);

		$scope_map = array(
			'category'     => $this->language->get('text_scope_category'),
			'manufacturer' => $this->language->get('text_scope_manufacturer'),
			'product'      => $this->language->get('text_scope_product'),
			'mixed'        => $this->language->get('text_scope_mixed')
		);

		foreach ($results as $result) {
			if ($result['discount_type'] == 'percent') {
				$value = rtrim(rtrim(number_format((float)$result['discount_value'], 2, '.', ''), '0'), '.') . '%';
			} else {
				$value = '-' . number_format((float)$result['discount_value'], 2, '.', ' ') . ' грн';
			}

			$scope_labels = $this->model_extension_module_getvape_znyzhky->getScopeLabels($result['rule_id']);
			$scope_text_parts = array();

			foreach ($scope_labels as $scope_key) {
				if (isset($scope_map[$scope_key])) {
					$scope_text_parts[] = $scope_map[$scope_key];
				}
			}

			if (!$scope_text_parts) {
				$scope_text_parts[] = isset($scope_map[$result['scope']]) ? $scope_map[$result['scope']] : $result['scope'];
			}

			$data['rules'][] = array(
				'rule_id'      => $result['rule_id'],
				'name'         => $result['name'],
				'scope'        => implode(', ', $scope_text_parts),
				'value'        => $value,
				'date_start'   => ($result['date_start'] != '0000-00-00') ? $result['date_start'] : '-',
				'date_end'     => ($result['date_end'] != '0000-00-00') ? $result['date_end'] : '-',
				'locked_count' => (int)$result['locked_count'],
				'status'       => $result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled'),
				'selected'     => isset($this->request->post['selected']) && in_array($result['rule_id'], $this->request->post['selected']),
				'edit'         => $this->url->link('extension/module/getvape_znyzhky/edit', 'user_token=' . $this->session->data['user_token'] . '&rule_id=' . $result['rule_id'] . $url, true)
			);
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		$url_order = ($order == 'ASC') ? 'DESC' : 'ASC';

		$data['sort_name'] = $this->url->link('extension/module/getvape_znyzhky', 'user_token=' . $this->session->data['user_token'] . '&sort=r.name&order=' . $url_order, true);
		$data['sort_scope'] = $this->url->link('extension/module/getvape_znyzhky', 'user_token=' . $this->session->data['user_token'] . '&sort=r.scope&order=' . $url_order, true);
		$data['sort_status'] = $this->url->link('extension/module/getvape_znyzhky', 'user_token=' . $this->session->data['user_token'] . '&sort=r.status&order=' . $url_order, true);

		$pagination = new Pagination();
		$pagination->total = $rule_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('extension/module/getvape_znyzhky', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($rule_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($rule_total - $this->config->get('config_limit_admin'))) ? $rule_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $rule_total, ceil($rule_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;
		$data['user_token'] = $this->session->data['user_token'];
		$data['author'] = $this->language->get('text_author');
		$data['author_link'] = 'https://ntsame.agency/';
		$data['author_email'] = 'hoffmanxyz@gmail.com';
		$data['button_add'] = $this->language->get('button_add');
		$data['button_delete'] = $this->language->get('button_delete');
		$data['button_edit'] = $this->language->get('button_edit');
		$data['button_apply'] = $this->language->get('button_apply');
		$data['text_confirm'] = $this->language->get('text_confirm');
		$data['heading_title'] = $this->language->get('heading_title');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/getvape_znyzhky_list', $data));
	}

	protected function getForm() {
		$data = array();

		foreach ($this->language->all() as $key => $value) {
			$data[$key] = $value;
		}

		$data['text_form'] = !isset($this->request->get['rule_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['discount_value'])) {
			$data['error_discount_value'] = $this->error['discount_value'];
		} else {
			$data['error_discount_value'] = '';
		}

		if (isset($this->error['target'])) {
			$data['error_target'] = $this->error['target'];
		} else {
			$data['error_target'] = '';
		}

		$url = $this->getListUrl();

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/getvape_znyzhky', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['rule_id'])) {
			$data['action'] = $this->url->link('extension/module/getvape_znyzhky/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('extension/module/getvape_znyzhky/edit', 'user_token=' . $this->session->data['user_token'] . '&rule_id=' . $this->request->get['rule_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('extension/module/getvape_znyzhky', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['rule_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$rule_info = $this->model_extension_module_getvape_znyzhky->getRule($this->request->get['rule_id']);
			$targets = $this->model_extension_module_getvape_znyzhky->getTargetsGrouped($this->request->get['rule_id']);
		} else {
			$rule_info = array();
			$targets = array(
				'category'     => array(),
				'manufacturer' => array(),
				'product'      => array()
			);
		}

		$fields = array('name', 'status', 'discount_type', 'discount_value', 'date_start', 'date_end', 'sort_order');

		foreach ($fields as $field) {
			if (isset($this->request->post[$field])) {
				$data[$field] = $this->request->post[$field];
			} elseif (!empty($rule_info)) {
				$data[$field] = $rule_info[$field];
			} else {
				$defaults = array(
					'name'           => '',
					'status'         => 1,
					'discount_type'  => 'percent',
					'discount_value' => '',
					'date_start'     => '',
					'date_end'       => '',
					'sort_order'     => 0
				);
				$data[$field] = $defaults[$field];
			}
		}

		if ($data['date_start'] == '0000-00-00') {
			$data['date_start'] = '';
		}

		if ($data['date_end'] == '0000-00-00') {
			$data['date_end'] = '';
		}

		if (isset($this->request->post['target_category']) || isset($this->request->post['target_manufacturer']) || isset($this->request->post['target_product'])) {
			$targets = array(
				'category'     => isset($this->request->post['target_category']) ? $this->request->post['target_category'] : array(),
				'manufacturer' => isset($this->request->post['target_manufacturer']) ? $this->request->post['target_manufacturer'] : array(),
				'product'      => isset($this->request->post['target_product']) ? $this->request->post['target_product'] : array()
			);
		}

		$data['target_categories'] = array();
		$data['target_manufacturers'] = array();
		$data['target_products'] = array();

		if (!empty($targets['category'])) {
			$this->load->model('catalog/category');

			foreach ($targets['category'] as $target_id) {
				$category_info = $this->model_catalog_category->getCategory($target_id);

				if ($category_info) {
					$data['target_categories'][] = array(
						'target_id' => $target_id,
						'name'      => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name']
					);
				}
			}
		}

		if (!empty($targets['manufacturer'])) {
			$this->load->model('catalog/manufacturer');

			foreach ($targets['manufacturer'] as $target_id) {
				$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($target_id);

				if ($manufacturer_info) {
					$data['target_manufacturers'][] = array(
						'target_id' => $target_id,
						'name'      => $manufacturer_info['name']
					);
				}
			}
		}

		if (!empty($targets['product'])) {
			$this->load->model('catalog/product');

			foreach ($targets['product'] as $target_id) {
				$product_info = $this->model_catalog_product->getProduct($target_id);

				if ($product_info) {
					$data['target_products'][] = array(
						'target_id' => $target_id,
						'name'      => $product_info['name']
					);
				}
			}
		}

		$data['user_token'] = $this->session->data['user_token'];
		$data['datepicker'] = $this->language->get('code');
		$data['author'] = $this->language->get('text_author');
		$data['author_link'] = 'https://ntsame.agency/';
		$data['author_email'] = 'hoffmanxyz@gmail.com';

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/getvape_znyzhky_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'extension/module/getvape_znyzhky')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!isset($this->request->post['name']) || (utf8_strlen($this->request->post['name']) < 1) || (utf8_strlen($this->request->post['name']) > 255)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if (!isset($this->request->post['discount_value']) || (float)$this->request->post['discount_value'] <= 0) {
			$this->error['discount_value'] = $this->language->get('error_discount_value');
		}

		if (!isset($this->request->post['discount_type']) || !in_array($this->request->post['discount_type'], array('percent', 'fixed'))) {
			$this->error['warning'] = $this->language->get('error_discount_type');
		}

		$has_category = !empty($this->request->post['target_category']) && is_array($this->request->post['target_category']);
		$has_manufacturer = !empty($this->request->post['target_manufacturer']) && is_array($this->request->post['target_manufacturer']);
		$has_product = !empty($this->request->post['target_product']) && is_array($this->request->post['target_product']);

		if (!$has_category && !$has_manufacturer && !$has_product) {
			$this->error['target'] = $this->language->get('error_target');
		}

		if (!isset($this->request->post['date_start']) || $this->request->post['date_start'] == '') {
			$this->request->post['date_start'] = '0000-00-00';
		}

		if (!isset($this->request->post['date_end']) || $this->request->post['date_end'] == '') {
			$this->request->post['date_end'] = '0000-00-00';
		}

		return !$this->error;
	}

	protected function validateModify() {
		if (!$this->user->hasPermission('modify', 'extension/module/getvape_znyzhky')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
