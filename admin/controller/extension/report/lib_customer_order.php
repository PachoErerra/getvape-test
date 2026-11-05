<?php
require_once(DIR_SYSTEM . 'library/libexport/autoload.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ControllerExtensionReportLibCustomerOrder extends Controller {
	
	public function index() {
		$this->load->language('extension/report/lib_customer_order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('report_lib_customer_order', $this->request->post);

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
			'href' => $this->url->link('extension/report/lib_customer_order', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/report/lib_customer_order', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report', true);

		if (isset($this->request->post['report_lib_customer_order_status'])) {
			$data['report_lib_customer_order_status'] = $this->request->post['report_lib_customer_order_status'];
		} else {
			$data['report_lib_customer_order_status'] = $this->config->get('report_lib_customer_order_status');
		}

		if (isset($this->request->post['report_lib_customer_order_sort_order'])) {
			$data['report_lib_customer_order_sort_order'] = $this->request->post['report_lib_customer_order_sort_order'];
		} else {
			$data['report_lib_customer_order_sort_order'] = $this->config->get('report_lib_customer_order_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/report/lib_customer_order_form', $data));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/report/lib_customer_order')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
			
	public function report() {
		$this->load->language('extension/report/lib_customer_order');

		if (isset($this->request->get['filter_date_start'])) {
			$filter_date_start = $this->request->get['filter_date_start'];
		} else {
			$filter_date_start = '';
		}

		if (isset($this->request->get['filter_date_end'])) {
			$filter_date_end = $this->request->get['filter_date_end'];
		} else {
			$filter_date_end = '';
		}

		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
			$filter_customer = '';
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$filter_order_status_id = $this->request->get['filter_order_status_id'];
		} else {
			$filter_order_status_id = 0;
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$this->load->model('extension/report/libexport');

		$data['customers'] = array();

		$filter_data = array(
			'filter_date_start'			=> $filter_date_start,
			'filter_date_end'			=> $filter_date_end,
			'filter_customer'			=> $filter_customer,
			'filter_order_status_id'	=> $filter_order_status_id,
			'start'						=> ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'						=> $this->config->get('config_limit_admin')
		);

		$customer_total = $this->model_extension_report_libexport->getTotalOrders($filter_data);

		$results = $this->model_extension_report_libexport->getOrders($filter_data);

		foreach ($results as $result) {
			$data['customers'][] = array(
				'customer'       => $result['customer'],
				'email'          => $result['email'],
				'telephone'		 => $result['telephone'],
				'telephone'		 => $result['telephone'],
				'address'		 => $result['address'],
				'customer_group' => $result['customer_group'],
				'status'         => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'orders'         => $result['orders'],
				'products'       => $result['products'],
				'total'          => $this->currency->format($result['total'], $this->config->get('config_currency')),
				'edit'           => $this->url->link('customer/customer/edit', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $result['customer_id'], true)
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$url = '';

		if (isset($this->request->get['filter_date_start'])) {
			$url .= '&filter_date_start=' . $this->request->get['filter_date_start'];
		}

		if (isset($this->request->get['filter_date_end'])) {
			$url .= '&filter_date_end=' . $this->request->get['filter_date_end'];
		}

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . urlencode($this->request->get['filter_customer']);
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}

		$pagination = new Pagination();
		$pagination->total = $customer_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=lib_customer_order' . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($customer_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($customer_total - $this->config->get('config_limit_admin'))) ? $customer_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $customer_total, ceil($customer_total / $this->config->get('config_limit_admin')));

		$data['filter_date_start'] = $filter_date_start;
		$data['filter_date_end'] = $filter_date_end;
		$data['filter_customer'] = $filter_customer;
		$data['filter_order_status_id'] = $filter_order_status_id;
		
		// Export 
		if (isset($this->request->get['export'])) {
			$button_export = $this->request->get['export'];
		} else {
			$button_export = '';
		}
		
		$filter_data_export = array(
			'filter_date_start'			=> $filter_date_start,
			'filter_date_end'			=> $filter_date_end,
			'filter_customer'			=> $filter_customer,
			'filter_order_status_id'	=> $filter_order_status_id
			
		);

		if($button_export !="" && $customer_total > 0 && $customer_total < 50000){
			
		$spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue('A1', $this->language->get('column_customer'));
        $sheet->setCellValue('B1', $this->language->get('column_email'));
        $sheet->setCellValue('C1', $this->language->get('column_telephone'));
		$sheet->setCellValue('D1', $this->language->get('column_address'));
		$sheet->setCellValue('E1', $this->language->get('column_customer_group'));
		$sheet->setCellValue('F1', $this->language->get('column_orders'));
		$sheet->setCellValue('G1', $this->language->get('column_products'));
		$sheet->setCellValue('H1', $this->language->get('column_total'));
        $rows = 2;
		$customer_export = $this->model_extension_report_libexport->getOrders($filter_data_export);
		foreach ($customer_export as $key => $item){
            // Set style

        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setAutoSize(true);
		$sheet->getColumnDimension('D')->setAutoSize(true);

        // Get value
		$item_price = $this->currency->format($item['total'], $this->config->get('config_currency'));
		
        $sheet->setCellValue('A' . $rows, $item['customer']);
        $sheet->setCellValue('B' . $rows, $item['email']);
        $sheet->setCellValue('C' . $rows, $item['telephone']);
		$sheet->setCellValue('D' . $rows, $item['address']);
		$sheet->setCellValue('E' . $rows, $item['customer_group']);
		$sheet->setCellValue('F' . $rows, $item['orders']);
		$sheet->setCellValue('G' . $rows, $item['products']);
		$sheet->setCellValue('H' . $rows, $item_price);
        $rows++;        

        }
                
        $writer = new Xlsx($spreadsheet);
        $filename = 'ReportCustomerOrder-' . strtotime('now');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
        header('Cache-Control: max-age=0');ob_end_clean();
        $writer->save('php://output'); 
        exit; 
			 
		}

		// End export
		
		return $this->load->view('extension/report/lib_customer_order_info', $data);
	}
	

}