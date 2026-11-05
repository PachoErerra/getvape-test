<?php
class ControllerExtensionReportCancelledOrders extends Controller {
	public function index() {
		$this->load->language('extension/report/cancelled_orders');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('report_cancelled_orders', $this->request->post);

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
			'href' => $this->url->link('extension/report/cancelled_orders', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/report/cancelled_orders', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report', true);

		if (isset($this->request->post['report_cancelled_orders_status'])) {
			$data['report_cancelled_orders_status'] = $this->request->post['report_cancelled_orders_status'];
		} else {
			$data['report_cancelled_orders_status'] = $this->config->get('report_cancelled_orders_status');
		}

		if (isset($this->request->post['report_cancelled_orders_sort_order'])) {
			$data['report_cancelled_orders_sort_order'] = $this->request->post['report_cancelled_orders_sort_order'];
		} else {
			$data['report_cancelled_orders_sort_order'] = $this->config->get('report_cancelled_orders_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/report/cancelled_orders_form', $data));
	}

	public function install() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('report_cancelled_orders', array(
			'report_cancelled_orders_status'     => 1,
			'report_cancelled_orders_sort_order' => 96
		));

		$this->grantPermissions();
	}

	public function uninstall() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('report_cancelled_orders');
	}

	private function grantPermissions() {
		$this->load->model('user/user_group');

		$route = 'extension/report/cancelled_orders';
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
		if (!$this->user->hasPermission('modify', 'extension/report/cancelled_orders')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function report() {
		$this->load->language('extension/report/cancelled_orders');
		$this->load->model('extension/report/cancelled_orders');

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

		if (isset($this->request->get['filter_user_id']) && $this->request->get['filter_user_id'] !== '') {
			$filter_user_id = (string)(int)$this->request->get['filter_user_id'];
		} else {
			$filter_user_id = '';
		}

		if (isset($this->request->get['filter_np_code']) && $this->request->get['filter_np_code'] !== '') {
			$filter_np_code = (string)(int)$this->request->get['filter_np_code'];
		} else {
			$filter_np_code = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'cancel_date';
		}

		if (isset($this->request->get['order'])) {
			$order = strtoupper($this->request->get['order']) === 'ASC' ? 'ASC' : 'DESC';
		} else {
			$order = 'DESC';
		}

		$allowed_sorts = array('order_id', 'customer', 'manager', 'status', 'total', 'cancel_date');

		if (!in_array($sort, $allowed_sorts, true)) {
			$sort = 'cancel_date';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$filter_data = array(
			'filter_date_start' => $filter_date_start,
			'filter_date_end'   => $filter_date_end,
			'filter_user_id'    => $filter_user_id,
			'filter_np_code'    => $filter_np_code,
			'sort'               => $sort,
			'order'              => $order,
			'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'             => $this->config->get('config_limit_admin')
		);

		$summary = $this->model_extension_report_cancelled_orders->getSummary($filter_data);
		$order_total = $summary['total'];

		$data['summary'] = array(
			'total'           => $summary['total'],
			'sum'             => $this->currency->format($summary['sum'], $this->config->get('config_currency')),
			'with_manager'    => $summary['with_manager'],
			'without_manager' => $summary['without_manager'],
			'managers_count'  => $summary['managers_count']
		);

		$manager_results = $this->model_extension_report_cancelled_orders->getManagers($filter_data);
		$data['managers'] = array();
		$rank = 1;

		foreach ($manager_results as $manager) {
			$data['managers'][] = array(
				'rank'     => $rank++,
				'manager'  => $manager['manager'],
				'orders'   => $manager['orders'],
				'sum'      => $this->currency->format($manager['sum'], $this->config->get('config_currency')),
				'percent'  => number_format($manager['percent'], 1, '.', '') . '%'
			);
		}

		$data['filter_managers'] = $this->model_extension_report_cancelled_orders->getFilterManagers($filter_data);
		$data['filter_np_codes'] = $this->model_extension_report_cancelled_orders->getFilterNpCodes();

		$results = $this->model_extension_report_cancelled_orders->getOrders($filter_data);

		$data['orders'] = array();

		foreach ($results as $result) {
			$data['orders'][] = array(
				'order_id'        => $result['order_id'],
				'customer'        => $result['customer'],
				'telephone'       => $result['telephone'],
				'manager'         => $result['manager'],
				'status'          => $result['status_name'],
				'comment'         => $result['comment'],
				'payment_method'  => $result['payment_method'],
				'shipping_method' => $result['shipping_method'],
				'total'           => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'cancel_date'     => date($this->language->get('datetime_format'), strtotime($result['cancel_date'])),
				'view'            => $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'], true)
			);
		}

		$data['user_token'] = $this->session->data['user_token'];
		$data['filter_date_start'] = $filter_date_start;
		$data['filter_date_end'] = $filter_date_end;
		$data['filter_user_id'] = $filter_user_id;
		$data['filter_np_code'] = $filter_np_code;
		$data['sort'] = $sort;
		$data['order'] = $order;

		$url = $this->buildFilterUrl($filter_date_start, $filter_date_end, $filter_user_id, $filter_np_code);

		$data['preset_today'] = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=cancelled_orders' . $this->buildFilterUrl(date('Y-m-d'), date('Y-m-d'), $filter_user_id, $filter_np_code) . '&sort=' . $sort . '&order=' . $order, true);
		$data['preset_week'] = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=cancelled_orders' . $this->buildFilterUrl(date('Y-m-d', strtotime('-6 days')), date('Y-m-d'), $filter_user_id, $filter_np_code) . '&sort=' . $sort . '&order=' . $order, true);
		$data['preset_month'] = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=cancelled_orders' . $this->buildFilterUrl(date('Y-m-d', strtotime('-29 days')), date('Y-m-d'), $filter_user_id, $filter_np_code) . '&sort=' . $sort . '&order=' . $order, true);

		$sort_url = $url . '&sort=' . urlencode($sort) . '&order=' . urlencode($order);

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=cancelled_orders' . $sort_url . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

		$data['sort_order_id'] = $this->buildSortLink('order_id', $sort, $order, $url);
		$data['sort_customer'] = $this->buildSortLink('customer', $sort, $order, $url);
		$data['sort_manager'] = $this->buildSortLink('manager', $sort, $order, $url);
		$data['sort_status'] = $this->buildSortLink('status', $sort, $order, $url);
		$data['sort_total'] = $this->buildSortLink('total', $sort, $order, $url);
		$data['sort_cancel_date'] = $this->buildSortLink('cancel_date', $sort, $order, $url);

		$data = array_merge($data, $this->getReportLanguage());

		return $this->load->view('extension/report/cancelled_orders_info', $data);
	}

	private function buildFilterUrl($filter_date_start, $filter_date_end, $filter_user_id, $filter_np_code = '') {
		$url = '';

		if ($filter_date_start) {
			$url .= '&filter_date_start=' . urlencode($filter_date_start);
		}

		if ($filter_date_end) {
			$url .= '&filter_date_end=' . urlencode($filter_date_end);
		}

		if ($filter_user_id !== '') {
			$url .= '&filter_user_id=' . urlencode($filter_user_id);
		}

		if ($filter_np_code !== '') {
			$url .= '&filter_np_code=' . urlencode($filter_np_code);
		}

		return $url;
	}

	private function buildSortLink($column, $current_sort, $current_order, $filter_url) {
		$next_order = ($current_sort === $column && $current_order === 'ASC') ? 'DESC' : 'ASC';

		return $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=cancelled_orders' . $filter_url . '&sort=' . $column . '&order=' . $next_order, true);
	}

	private function getReportLanguage() {
		$keys = array(
			'heading_title',
			'text_filter',
			'text_no_results',
			'text_summary',
			'text_orders_count',
			'text_sum',
			'text_with_manager',
			'text_without_manager',
			'text_managers_count',
			'text_period_hint',
			'text_manager_hint',
			'text_preset_today',
			'text_preset_week',
			'text_preset_month',
			'text_managers',
			'text_all_managers',
			'text_unknown_manager',
			'text_all_np_codes',
			'column_rank',
			'column_manager',
			'column_order_id',
			'column_customer',
			'column_telephone',
			'column_status',
			'column_comment',
			'column_payment',
			'column_shipping',
			'column_total',
			'column_orders',
			'column_percent',
			'column_sum',
			'column_cancel_date',
			'column_action',
			'entry_date_start',
			'entry_date_end',
			'entry_manager',
			'entry_np_code',
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
