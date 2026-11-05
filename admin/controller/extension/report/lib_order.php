<?php
require_once(DIR_SYSTEM . 'library/libexport/autoload.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ControllerExtensionReportLibOrder extends Controller {

	public function index() {
		$this->load->language('extension/report/lib_order');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('report_lib_order', $this->request->post);

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
			'href' => $this->url->link('extension/report/lib_order', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/report/lib_order', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report', true);

		if (isset($this->request->post['report_lib_order_status'])) {
			$data['report_lib_order_status'] = $this->request->post['report_lib_order_status'];
		} else {
			$data['report_lib_order_status'] = $this->config->get('report_lib_order_status');
		}

		if (isset($this->request->post['report_lib_order_sort_order'])) {
			$data['report_lib_order_sort_order'] = $this->request->post['report_lib_order_sort_order'];
		} else {
			$data['report_lib_order_sort_order'] = $this->config->get('report_lib_order_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/report/lib_order_form', $data));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/report/lib_order')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
		
	public function report() {

		$this->load->language('extension/report/lib_order');


		if (isset($this->request->get['filter_customer'])) {
			$filter_customer = $this->request->get['filter_customer'];
		} else {
			$filter_customer = '';
		}

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

		$data['orders'] = array();

		$filter_data = array(
			'filter_customer'	     => $filter_customer,
			'filter_date_start'	     => $filter_date_start,
			'filter_date_end'	     => $filter_date_end,
			'filter_order_status_id' => $filter_order_status_id,
			'start'                  => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                  => $this->config->get('config_limit_admin')
		);

		$order_total = $this->model_extension_report_libexport->getTotalSaleOrder($filter_data);

		$results = $this->model_extension_report_libexport->getSaleOrders($filter_data);

		foreach ($results as $result) {
			$data['orders'][] = array(
				'name'     => $result['name'],
				'quantity' => $result['quantity'],
				'customer' => $result['customer'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
				
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$url = '';

		if (isset($this->request->get['filter_customer'])) {
			$url .= '&filter_customer=' . $this->request->get['filter_customer'];
		}

		if (isset($this->request->get['filter_date_start'])) {
			$url .= '&filter_date_start=' . $this->request->get['filter_date_start'];
		}

		if (isset($this->request->get['filter_date_end'])) {
			$url .= '&filter_date_end=' . $this->request->get['filter_date_end'];
		}

		if (isset($this->request->get['filter_order_status_id'])) {
			$url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
		}

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=lib_order' . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

		$data['filter_customer'] = $filter_customer;
		$data['filter_date_start'] = $filter_date_start;
		$data['filter_date_end'] = $filter_date_end;
		$data['filter_order_status_id'] = $filter_order_status_id;

		// Get Data Export 
		
		$filter_data_export = array(
			'filter_customer'	     => $filter_customer,
			'filter_date_start'	     => $filter_date_start,
			'filter_date_end'	     => $filter_date_end,
			'filter_order_status_id' => $filter_order_status_id
		);


		if (isset($this->request->get['export'])) {
			$button_export = $this->request->get['export'];
		} else {
			$button_export = '';
		}

		$data['order_total'] = $order_total;

		if($button_export !="" && $order_total > 0 && $order_total < 50000 ){
			
		$spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue('A1', $this->language->get('column_order_invoice'));
        $sheet->setCellValue('B1', $this->language->get('column_customer'));
        $sheet->setCellValue('C1', $this->language->get('column_name'));
		$sheet->setCellValue('D1', $this->language->get('column_model'));
		$sheet->setCellValue('E1', $this->language->get('column_quantity'));
		$sheet->setCellValue('F1', $this->language->get('column_total'));
		$sheet->setCellValue('G1', $this->language->get('column_tax'));
		$sheet->setCellValue('H1', $this->language->get('column_date_added'));
        $rows = 2;
		$lib_order = $this->model_extension_report_libexport->getSaleOrders($filter_data_export);
		
		foreach ($lib_order as $key => $item){
            // Set style
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setAutoSize(true);
		$sheet->getColumnDimension('D')->setAutoSize(true);

        // Get value
		$item_total = $this->currency->format($item['total'], $this->config->get('config_currency'));
		$item_tax = $this->currency->format($item['tax'], $this->config->get('config_currency'));
		$item_date_added = date($this->language->get('date_format_short'), strtotime($item['date_added']));
		
        $sheet->setCellValue('A' . $rows, $item['invoice_prefix']);
        $sheet->setCellValue('B' . $rows, $item['customer']);
        $sheet->setCellValue('C' . $rows, $item['name']);
		$sheet->setCellValue('D' . $rows, $item['model']);
		$sheet->setCellValue('E' . $rows, $item['quantity']);
		$sheet->setCellValue('F' . $rows, $item_total);
		$sheet->setCellValue('G' . $rows, $item_tax);
		$sheet->setCellValue('H' . $rows, $item_date_added);
        $rows++;        

        } 
                
        $writer = new Xlsx($spreadsheet);
        $filename = 'ReportOrder-' . strtotime('now');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
        header('Cache-Control: max-age=0');ob_end_clean();
        $writer->save('php://output'); 
        exit; 
			 
		}

		// End export

		return $this->load->view('extension/report/lib_order_info', $data);
	}
}