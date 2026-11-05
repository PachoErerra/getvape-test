<?php
require_once(DIR_SYSTEM . 'library/libexport/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ControllerExtensionReportCustomerRepeatOrder extends Controller {
	public function index() {
		$this->load->language('extension/report/customer_repeat_order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('report_customer_repeat_order', $this->request->post);

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
			'href' => $this->url->link('extension/report/customer_repeat_order', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/report/customer_repeat_order', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report', true);

		if (isset($this->request->post['report_customer_repeat_order_status'])) {
			$data['report_customer_repeat_order_status'] = $this->request->post['report_customer_repeat_order_status'];
		} else {
			$data['report_customer_repeat_order_status'] = $this->config->get('report_customer_repeat_order_status');
		}

		if (isset($this->request->post['report_customer_repeat_order_sort_order'])) {
			$data['report_customer_repeat_order_sort_order'] = $this->request->post['report_customer_repeat_order_sort_order'];
		} else {
			$data['report_customer_repeat_order_sort_order'] = $this->config->get('report_customer_repeat_order_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/report/customer_repeat_order_form', $data));
	}

	public function install() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('report_customer_repeat_order', array(
			'report_customer_repeat_order_status'     => 1,
			'report_customer_repeat_order_sort_order' => 99
		));

		$this->grantPermissions();
	}

	public function uninstall() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('report_customer_repeat_order');
	}

	private function grantPermissions() {
		$this->load->model('user/user_group');

		$route = 'extension/report/customer_repeat_order';
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
		if (!$this->user->hasPermission('modify', 'extension/report/customer_repeat_order')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function report() {
		$this->load->language('extension/report/customer_repeat_order');

		$filter_date_start = isset($this->request->get['filter_date_start']) ? $this->request->get['filter_date_start'] : '';
		$filter_date_end = isset($this->request->get['filter_date_end']) ? $this->request->get['filter_date_end'] : '';
		$filter_customer = isset($this->request->get['filter_customer']) ? $this->request->get['filter_customer'] : '';
		$filter_telephone = isset($this->request->get['filter_telephone']) ? $this->request->get['filter_telephone'] : '';
		$filter_order_status_id = isset($this->request->get['filter_order_status_id']) ? (int)$this->request->get['filter_order_status_id'] : 0;
		$filter_orders_bucket = isset($this->request->get['filter_orders_bucket']) ? (int)$this->request->get['filter_orders_bucket'] : 0;
		$page = isset($this->request->get['page']) ? (int)$this->request->get['page'] : 1;

		$this->load->model('extension/report/customer_repeat_order');

		$filter_data = array(
			'filter_date_start'      => $filter_date_start,
			'filter_date_end'        => $filter_date_end,
			'filter_customer'        => $filter_customer,
			'filter_telephone'       => $filter_telephone,
			'filter_order_status_id' => $filter_order_status_id,
			'filter_orders_bucket'   => $filter_orders_bucket,
			'start'                  => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                  => $this->config->get('config_limit_admin')
		);

		$distribution = $this->model_extension_report_customer_repeat_order->getDistribution($filter_data);

		$data['distribution'] = array(
			array(
				'bucket'  => 1,
				'label'   => $this->language->get('text_bucket_1'),
				'count'   => (int)$distribution['bucket_1'],
				'active'  => ($filter_orders_bucket === 1),
				'filter'  => $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=customer_repeat_order' . $this->buildFilterUrl($filter_date_start, $filter_date_end, $filter_customer, $filter_telephone, $filter_order_status_id, 1), true)
			),
			array(
				'bucket'  => 2,
				'label'   => $this->language->get('text_bucket_2'),
				'count'   => (int)$distribution['bucket_2'],
				'active'  => ($filter_orders_bucket === 2),
				'filter'  => $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=customer_repeat_order' . $this->buildFilterUrl($filter_date_start, $filter_date_end, $filter_customer, $filter_telephone, $filter_order_status_id, 2), true)
			),
			array(
				'bucket'  => 3,
				'label'   => $this->language->get('text_bucket_3'),
				'count'   => (int)$distribution['bucket_3'],
				'active'  => ($filter_orders_bucket === 3),
				'filter'  => $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=customer_repeat_order' . $this->buildFilterUrl($filter_date_start, $filter_date_end, $filter_customer, $filter_telephone, $filter_order_status_id, 3), true)
			),
			array(
				'bucket'  => 4,
				'label'   => $this->language->get('text_bucket_4'),
				'count'   => (int)$distribution['bucket_4'],
				'active'  => ($filter_orders_bucket === 4),
				'filter'  => $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=customer_repeat_order' . $this->buildFilterUrl($filter_date_start, $filter_date_end, $filter_customer, $filter_telephone, $filter_order_status_id, 4), true)
			),
			array(
				'bucket'  => 5,
				'label'   => $this->language->get('text_bucket_5_plus'),
				'count'   => (int)$distribution['bucket_5_plus'],
				'active'  => ($filter_orders_bucket === 5),
				'filter'  => $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=customer_repeat_order' . $this->buildFilterUrl($filter_date_start, $filter_date_end, $filter_customer, $filter_telephone, $filter_order_status_id, 5), true)
			)
		);

		$customer_total = $this->model_extension_report_customer_repeat_order->getTotalCustomers($filter_data);

		if (!empty($this->request->get['export']) && $customer_total > 0 && $customer_total < 50000) {
			$this->exportCustomers($filter_data);
		}

		$results = $this->model_extension_report_customer_repeat_order->getCustomers($filter_data);
		$customer_ids = array();

		foreach ($results as $result) {
			$customer_ids[] = $result['customer_id'];
		}

		$statuses_by_customer = $this->model_extension_report_customer_repeat_order->getOrderStatusCountsByCustomerIds($customer_ids, $filter_data);

		$data['customers'] = array();

		foreach ($results as $result) {
			$order_statuses = !empty($statuses_by_customer[$result['customer_id']]) ? $statuses_by_customer[$result['customer_id']] : array();

			$data['customers'][] = array(
				'customer'       => $result['customer'],
				'email'          => $result['email'],
				'telephone'      => $result['telephone'],
				'orders'         => (int)$result['orders'],
				'products'       => (int)$result['products'],
				'total'          => $this->currency->format($result['total'], $this->config->get('config_currency')),
				'edit'           => $this->url->link('customer/customer/edit', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $result['customer_id'], true),
				'orders_view'    => $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&filter_customer=' . urlencode($result['customer']), true),
				'order_statuses' => $order_statuses
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/order_status');
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$url = $this->buildFilterUrl($filter_date_start, $filter_date_end, $filter_customer, $filter_telephone, $filter_order_status_id, $filter_orders_bucket);

		$pagination = new Pagination();
		$pagination->total = $customer_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=customer_repeat_order' . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($customer_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($customer_total - $this->config->get('config_limit_admin'))) ? $customer_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $customer_total, ceil($customer_total / $this->config->get('config_limit_admin')));

		$data['filter_date_start'] = $filter_date_start;
		$data['filter_date_end'] = $filter_date_end;
		$data['filter_customer'] = $filter_customer;
		$data['filter_telephone'] = $filter_telephone;
		$data['filter_order_status_id'] = $filter_order_status_id;
		$data['filter_orders_bucket'] = $filter_orders_bucket;
		$data['clear_bucket_url'] = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=customer_repeat_order' . $this->buildFilterUrl($filter_date_start, $filter_date_end, $filter_customer, $filter_telephone, $filter_order_status_id, 0), true);

		$data = array_merge($data, $this->getReportLanguage());

		return $this->load->view('extension/report/customer_repeat_order_info', $data);
	}

	private function getReportLanguage() {
		$keys = array(
			'heading_title',
			'text_filter',
			'text_all_status',
			'text_all_buckets',
			'text_enabled',
			'text_disabled',
			'text_bucket_1',
			'text_bucket_2',
			'text_bucket_3',
			'text_bucket_4',
			'text_bucket_5_plus',
			'text_no_results',
			'column_customer',
			'column_email',
			'column_telephone',
			'column_orders',
			'column_products',
			'column_total',
			'column_order_statuses',
			'column_action',
			'entry_date_start',
			'entry_date_end',
			'entry_customer',
			'entry_telephone',
			'entry_order_status',
			'entry_orders_bucket',
			'button_filter',
			'button_export',
			'button_edit',
			'button_view_orders'
		);

		$language = array();

		foreach ($keys as $key) {
			$language[$key] = $this->language->get($key);
		}

		$language['datepicker'] = $this->language->get('code');

		return $language;
	}

	private function buildFilterUrl($filter_date_start, $filter_date_end, $filter_customer, $filter_telephone, $filter_order_status_id, $filter_orders_bucket) {
		$url = '';

		if ($filter_date_start) {
			$url .= '&filter_date_start=' . urlencode($filter_date_start);
		}

		if ($filter_date_end) {
			$url .= '&filter_date_end=' . urlencode($filter_date_end);
		}

		if ($filter_customer) {
			$url .= '&filter_customer=' . urlencode($filter_customer);
		}

		if ($filter_telephone) {
			$url .= '&filter_telephone=' . urlencode($filter_telephone);
		}

		if ($filter_order_status_id) {
			$url .= '&filter_order_status_id=' . (int)$filter_order_status_id;
		}

		if ($filter_orders_bucket) {
			$url .= '&filter_orders_bucket=' . (int)$filter_orders_bucket;
		}

		return $url;
	}

	private function exportCustomers($filter_data) {
		unset($filter_data['start'], $filter_data['limit']);

		$results = $this->model_extension_report_customer_repeat_order->getCustomers($filter_data);
		$customer_ids = array();

		foreach ($results as $result) {
			$customer_ids[] = $result['customer_id'];
		}

		$statuses_by_customer = $this->model_extension_report_customer_repeat_order->getOrderStatusCountsByCustomerIds($customer_ids, $filter_data);

		$spreadsheet = new Spreadsheet();
		$sheet = $spreadsheet->getActiveSheet();

		$sheet->setCellValue('A1', $this->language->get('column_customer'));
		$sheet->setCellValue('B1', $this->language->get('column_email'));
		$sheet->setCellValue('C1', $this->language->get('column_telephone'));
		$sheet->setCellValue('D1', $this->language->get('column_orders'));
		$sheet->setCellValue('E1', $this->language->get('column_products'));
		$sheet->setCellValue('F1', $this->language->get('column_total'));
		$sheet->setCellValue('G1', $this->language->get('column_order_statuses'));

		$row = 2;

		foreach ($results as $item) {
			$order_statuses = !empty($statuses_by_customer[$item['customer_id']]) ? $statuses_by_customer[$item['customer_id']] : array();

			$sheet->setCellValue('A' . $row, $item['customer']);
			$sheet->setCellValue('B' . $row, $item['email']);
			$sheet->setCellValue('C' . $row, $item['telephone']);
			$sheet->setCellValue('D' . $row, (int)$item['orders']);
			$sheet->setCellValue('E' . $row, (int)$item['products']);
			$sheet->setCellValue('F' . $row, $this->currency->format($item['total'], $this->config->get('config_currency')));
			$sheet->setCellValue('G' . $row, $this->model_extension_report_customer_repeat_order->formatOrderStatusSummary($order_statuses));
			$row++;
		}

		foreach (range('A', 'G') as $column) {
			$sheet->getColumnDimension($column)->setAutoSize(true);
		}

		$writer = new Xlsx($spreadsheet);
		$filename = 'ReportCustomerRepeatOrder-' . time();

		header('Content-Type: application/vnd.ms-excel');
		header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
		header('Cache-Control: max-age=0');

		if (ob_get_length()) {
			ob_end_clean();
		}

		$writer->save('php://output');
		exit;
	}
}
 