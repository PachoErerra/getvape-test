<?php
class ControllerExtensionReportCodProcessingTime extends Controller {
	public function index() {
		$this->load->language('extension/report/cod_processing_time');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('report_cod_processing_time', $this->request->post);

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
			'href' => $this->url->link('extension/report/cod_processing_time', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/report/cod_processing_time', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report', true);

		if (isset($this->request->post['report_cod_processing_time_status'])) {
			$data['report_cod_processing_time_status'] = $this->request->post['report_cod_processing_time_status'];
		} else {
			$data['report_cod_processing_time_status'] = $this->config->get('report_cod_processing_time_status');
		}

		if (isset($this->request->post['report_cod_processing_time_sort_order'])) {
			$data['report_cod_processing_time_sort_order'] = $this->request->post['report_cod_processing_time_sort_order'];
		} else {
			$data['report_cod_processing_time_sort_order'] = $this->config->get('report_cod_processing_time_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/report/cod_processing_time_form', $data));
	}

	public function install() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('report_cod_processing_time', array(
			'report_cod_processing_time_status'     => 1,
			'report_cod_processing_time_sort_order' => 97
		));

		$this->grantPermissions();
	}

	public function uninstall() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('report_cod_processing_time');
	}

	private function grantPermissions() {
		$this->load->model('user/user_group');

		$route = 'extension/report/cod_processing_time';
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
		if (!$this->user->hasPermission('modify', 'extension/report/cod_processing_time')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function report() {
		$this->load->language('extension/report/cod_processing_time');
		$this->load->model('extension/report/cod_processing_time');

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

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'end_date';
		}

		if (isset($this->request->get['order'])) {
			$order = strtoupper($this->request->get['order']) === 'ASC' ? 'ASC' : 'DESC';
		} else {
			$order = 'DESC';
		}

		$allowed_sorts = array('order_id', 'customer', 'manager', 'total', 'start_date', 'end_date', 'diff_seconds');

		if (!in_array($sort, $allowed_sorts, true)) {
			$sort = 'end_date';
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
			'sort'               => $sort,
			'order'              => $order,
			'status_start'      => ModelExtensionReportCodProcessingTime::STATUS_PROCESSING,
			'status_end'        => ModelExtensionReportCodProcessingTime::STATUS_READY,
			'payment_code'      => ModelExtensionReportCodProcessingTime::PAYMENT_CODE,
			'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'             => $this->config->get('config_limit_admin')
		);

		$summary = $this->model_extension_report_cod_processing_time->getSummary($filter_data);
		$order_total = $summary['total'];

		$data['summary'] = array(
			'total'       => $summary['total'],
			'avg_time'    => $this->formatDuration($summary['avg_seconds']),
			'min_time'    => $summary['total'] ? $this->formatDuration($summary['min_seconds']) : '—',
			'max_time'    => $summary['total'] ? $this->formatDuration($summary['max_seconds']) : '—'
		);

		$manager_results = $this->model_extension_report_cod_processing_time->getManagers($filter_data);
		$data['managers'] = array();
		$rank = 1;

		foreach ($manager_results as $manager) {
			$data['managers'][] = array(
				'rank'     => $rank++,
				'manager'  => $manager['manager'],
				'orders'   => $manager['orders'],
				'avg_time' => $this->formatDuration($manager['avg_seconds']),
				'min_time' => $this->formatDuration($manager['min_seconds']),
				'max_time' => $this->formatDuration($manager['max_seconds'])
			);
		}

		$data['filter_managers'] = $this->model_extension_report_cod_processing_time->getFilterManagers($filter_data);

		$results = $this->model_extension_report_cod_processing_time->getOrders($filter_data);

		$data['orders'] = array();

		foreach ($results as $result) {
			$data['orders'][] = array(
				'order_id'        => $result['order_id'],
				'customer'        => $result['customer'],
				'telephone'       => $result['telephone'],
				'manager'         => $result['manager'],
				'payment_method'  => $result['payment_method'],
				'shipping_method' => $result['shipping_method'],
				'total'           => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'start_date'      => date($this->language->get('datetime_format'), strtotime($result['start_date'])),
				'end_date'        => date($this->language->get('datetime_format'), strtotime($result['end_date'])),
				'duration'        => $this->formatDuration($result['diff_seconds']),
				'view'            => $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'], true)
			);
		}

		$data['user_token'] = $this->session->data['user_token'];
		$data['filter_date_start'] = $filter_date_start;
		$data['filter_date_end'] = $filter_date_end;
		$data['filter_user_id'] = $filter_user_id;
		$data['sort'] = $sort;
		$data['order'] = $order;

		$url = $this->buildFilterUrl($filter_date_start, $filter_date_end, $filter_user_id);

		$data['preset_today'] = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=cod_processing_time' . $this->buildFilterUrl(date('Y-m-d'), date('Y-m-d'), $filter_user_id) . '&sort=' . $sort . '&order=' . $order, true);
		$data['preset_week'] = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=cod_processing_time' . $this->buildFilterUrl(date('Y-m-d', strtotime('-6 days')), date('Y-m-d'), $filter_user_id) . '&sort=' . $sort . '&order=' . $order, true);
		$data['preset_month'] = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=cod_processing_time' . $this->buildFilterUrl(date('Y-m-d', strtotime('-29 days')), date('Y-m-d'), $filter_user_id) . '&sort=' . $sort . '&order=' . $order, true);

		$sort_url = $url . '&sort=' . urlencode($sort) . '&order=' . urlencode($order);

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=cod_processing_time' . $sort_url . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

		$data['sort_order_id'] = $this->buildSortLink('order_id', $sort, $order, $url);
		$data['sort_customer'] = $this->buildSortLink('customer', $sort, $order, $url);
		$data['sort_manager'] = $this->buildSortLink('manager', $sort, $order, $url);
		$data['sort_total'] = $this->buildSortLink('total', $sort, $order, $url);
		$data['sort_start_date'] = $this->buildSortLink('start_date', $sort, $order, $url);
		$data['sort_end_date'] = $this->buildSortLink('end_date', $sort, $order, $url);
		$data['sort_duration'] = $this->buildSortLink('diff_seconds', $sort, $order, $url);

		$data = array_merge($data, $this->getReportLanguage());

		return $this->load->view('extension/report/cod_processing_time_info', $data);
	}

	private function buildFilterUrl($filter_date_start, $filter_date_end, $filter_user_id) {
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

		return $url;
	}

	private function buildSortLink($column, $current_sort, $current_order, $filter_url) {
		$next_order = ($current_sort === $column && $current_order === 'ASC') ? 'DESC' : 'ASC';

		return $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=cod_processing_time' . $filter_url . '&sort=' . $column . '&order=' . $next_order, true);
	}

	private function formatDuration($seconds) {
		$seconds = (int)round($seconds);

		if ($seconds < 0) {
			$seconds = 0;
		}

		$hours = floor($seconds / 3600);
		$minutes = floor(($seconds % 3600) / 60);
		$secs = $seconds % 60;

		if ($hours > 0) {
			return sprintf($this->language->get('text_duration_hm'), $hours, $minutes);
		}

		if ($minutes > 0) {
			return sprintf($this->language->get('text_duration_ms'), $minutes, $secs);
		}

		return sprintf($this->language->get('text_duration_s'), $secs);
	}

	private function getReportLanguage() {
		$keys = array(
			'heading_title',
			'text_filter',
			'text_no_results',
			'text_summary',
			'text_avg_time',
			'text_min_time',
			'text_max_time',
			'text_orders_count',
			'text_period_hint',
			'text_preset_today',
			'text_preset_week',
			'text_preset_month',
			'text_managers',
			'text_manager_hint',
			'text_all_managers',
			'text_unknown_manager',
			'column_rank',
			'column_manager',
			'column_order_id',
			'column_customer',
			'column_telephone',
			'column_payment',
			'column_shipping',
			'column_total',
			'column_orders',
			'column_start',
			'column_end',
			'column_duration',
			'column_action',
			'column_avg_time',
			'column_min_time',
			'column_max_time',
			'entry_date_start',
			'entry_date_end',
			'entry_manager',
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
