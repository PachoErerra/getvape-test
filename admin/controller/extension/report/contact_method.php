<?php
class ControllerExtensionReportContactMethod extends Controller {
	public function index() {
		$this->load->language('extension/report/contact_method');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('report_contact_method', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report', true));
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/report/contact_method', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/report/contact_method', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report', true);

		if (isset($this->request->post['report_contact_method_status'])) {
			$data['report_contact_method_status'] = $this->request->post['report_contact_method_status'];
		} else {
			$data['report_contact_method_status'] = $this->config->get('report_contact_method_status');
		}

		if (isset($this->request->post['report_contact_method_sort_order'])) {
			$data['report_contact_method_sort_order'] = $this->request->post['report_contact_method_sort_order'];
		} else {
			$data['report_contact_method_sort_order'] = $this->config->get('report_contact_method_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/report/contact_method_form', $data));
	}

	public function install() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('report_contact_method', array(
			'report_contact_method_status'     => 1,
			'report_contact_method_sort_order' => 95
		));

		$this->grantPermissions();
	}

	public function uninstall() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('report_contact_method');
	}

	private function grantPermissions() {
		$this->load->model('user/user_group');

		$route = 'extension/report/contact_method';
		$groups = array(1);

		if ($this->user->isLogged()) {
			$groups[] = (int)$this->user->getGroupId();
		}

		$groups = array_unique($groups);

		foreach ($groups as $group_id) {
			$user_group = $this->model_user_user_group->getUserGroup($group_id);

			if (empty($user_group['permission']['access']) || !in_array($route, $user_group['permission']['access'])) {
				$this->model_user_user_group->addPermission($group_id, 'access', $route);
			}

			if (empty($user_group['permission']['modify']) || !in_array($route, $user_group['permission']['modify'])) {
				$this->model_user_user_group->addPermission($group_id, 'modify', $route);
			}
		}
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/report/contact_method')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function report() {
		$this->load->language('extension/report/contact_method');
		$this->load->model('extension/report/contact_method');

		if (isset($this->request->get['filter_date_start'])) {
			$filter_date_start = $this->request->get['filter_date_start'];
		} else {
			$filter_date_start = date('Y-m-d');
		}

		if (isset($this->request->get['filter_date_end'])) {
			$filter_date_end = $this->request->get['filter_date_end'];
		} else {
			$filter_date_end = date('Y-m-d');
		}

		$allowed_contacts = array('telegram', 'viber', 'telephone');

		if (isset($this->request->get['filter_contact']) && in_array($this->request->get['filter_contact'], $allowed_contacts, true)) {
			$filter_contact = $this->request->get['filter_contact'];
		} else {
			$filter_contact = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'date_added';
		}

		if (isset($this->request->get['order'])) {
			$order = strtoupper($this->request->get['order']) === 'ASC' ? 'ASC' : 'DESC';
		} else {
			$order = 'DESC';
		}

		$allowed_sorts = array('order_id', 'customer', 'contact', 'total', 'date_added');

		if (!in_array($sort, $allowed_sorts, true)) {
			$sort = 'date_added';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$filter_data = array(
			'filter_date_start' => $filter_date_start,
			'filter_date_end'   => $filter_date_end,
			'filter_contact'    => $filter_contact,
			'sort'              => $sort,
			'order'             => $order,
			'start'            => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'            => $this->config->get('config_limit_admin')
		);

		$summary = $this->model_extension_report_contact_method->getSummary($filter_data);
		$order_total = $summary['total'];

		$data['summary'] = array(
			'total'     => $summary['total'],
			'sum'       => $this->currency->format($summary['sum'], $this->config->get('config_currency')),
			'telegram'  => $summary['telegram'],
			'viber'     => $summary['viber'],
			'telephone' => $summary['telephone']
		);

		$ranking = $this->model_extension_report_contact_method->getContactRanking($filter_data);
		$data['contacts'] = array();
		$rank = 1;

		foreach ($ranking as $row) {
			$data['contacts'][] = array(
				'rank'    => $rank++,
				'contact' => $row['contact'],
				'orders'  => $row['orders'],
				'percent' => number_format($row['percent'], 1, '.', '') . '%',
				'sum'     => $this->currency->format($row['sum'], $this->config->get('config_currency'))
			);
		}

		$data['filter_contact_types'] = array();

		foreach ($this->model_extension_report_contact_method->getContactTypes() as $code => $name) {
			$data['filter_contact_types'][] = array(
				'code' => $code,
				'name' => $name
			);
		}

		$results = $this->model_extension_report_contact_method->getOrders($filter_data);
		$data['orders'] = array();

		foreach ($results as $result) {
			$data['orders'][] = array(
				'order_id'        => $result['order_id'],
				'customer'        => $result['customer'],
				'telephone'       => $result['telephone'],
				'contact'         => $result['contact_label'],
				'payment_method'  => $result['payment_method'],
				'shipping_method' => $result['shipping_method'],
				'total'           => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'date_added'      => date($this->language->get('datetime_format'), strtotime($result['date_added'])),
				'view'            => $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'], true)
			);
		}

		$data['user_token'] = $this->session->data['user_token'];
		$data['filter_date_start'] = $filter_date_start;
		$data['filter_date_end'] = $filter_date_end;
		$data['filter_contact'] = $filter_contact;
		$data['sort'] = $sort;
		$data['order'] = $order;

		$url = $this->buildFilterUrl($filter_date_start, $filter_date_end, $filter_contact);

		$data['preset_today'] = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=contact_method' . $this->buildFilterUrl(date('Y-m-d'), date('Y-m-d'), $filter_contact) . '&sort=' . $sort . '&order=' . $order, true);
		$data['preset_week'] = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=contact_method' . $this->buildFilterUrl(date('Y-m-d', strtotime('-6 days')), date('Y-m-d'), $filter_contact) . '&sort=' . $sort . '&order=' . $order, true);
		$data['preset_month'] = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=contact_method' . $this->buildFilterUrl(date('Y-m-d', strtotime('-29 days')), date('Y-m-d'), $filter_contact) . '&sort=' . $sort . '&order=' . $order, true);

		$sort_url = $url . '&sort=' . urlencode($sort) . '&order=' . urlencode($order);

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=contact_method' . $sort_url . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

		$data['sort_order_id'] = $this->buildSortLink('order_id', $sort, $order, $url);
		$data['sort_customer'] = $this->buildSortLink('customer', $sort, $order, $url);
		$data['sort_contact'] = $this->buildSortLink('contact', $sort, $order, $url);
		$data['sort_total'] = $this->buildSortLink('total', $sort, $order, $url);
		$data['sort_date_added'] = $this->buildSortLink('date_added', $sort, $order, $url);

		$data = array_merge($data, $this->getReportLanguage());

		return $this->load->view('extension/report/contact_method_info', $data);
	}

	private function buildFilterUrl($filter_date_start, $filter_date_end, $filter_contact) {
		$url = '';

		if ($filter_date_start) {
			$url .= '&filter_date_start=' . urlencode($filter_date_start);
		}

		if ($filter_date_end) {
			$url .= '&filter_date_end=' . urlencode($filter_date_end);
		}

		if ($filter_contact !== '') {
			$url .= '&filter_contact=' . urlencode($filter_contact);
		}

		return $url;
	}

	private function buildSortLink($column, $current_sort, $current_order, $filter_url) {
		$next_order = ($current_sort === $column && $current_order === 'ASC') ? 'DESC' : 'ASC';

		return $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=contact_method' . $filter_url . '&sort=' . $column . '&order=' . $next_order, true);
	}

	private function getReportLanguage() {
		$keys = array(
			'heading_title',
			'text_filter',
			'text_no_results',
			'text_orders_count',
			'text_sum',
			'text_telegram',
			'text_viber',
			'text_telephone',
			'text_period_hint',
			'text_contact_hint',
			'text_preset_today',
			'text_preset_week',
			'text_preset_month',
			'text_ranking',
			'text_all_contacts',
			'column_rank',
			'column_contact',
			'column_orders',
			'column_percent',
			'column_sum',
			'column_order_id',
			'column_customer',
			'column_telephone',
			'column_payment',
			'column_shipping',
			'column_total',
			'column_date',
			'column_action',
			'entry_date_start',
			'entry_date_end',
			'entry_contact',
			'button_filter',
			'button_view'
		);

		$language = array();

		foreach ($keys as $key) {
			$language[$key] = $this->language->get($key);
		}

		$language['datepicker'] = $this->language->get('code');

		return $language;
	}
}
