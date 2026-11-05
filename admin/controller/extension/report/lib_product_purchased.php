<?php
require_once(DIR_SYSTEM . 'library/libexport/autoload.php');
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ControllerExtensionReportLibProductPurchased extends Controller {

	public function index() {
		$this->load->language('extension/report/lib_product_purchased');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('report_lib_product_purchased', $this->request->post);

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
			'href' => $this->url->link('extension/report/lib_product_purchased', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/report/lib_product_purchased', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report', true);

		if (isset($this->request->post['report_lib_product_purchased_status'])) {
			$data['report_lib_product_purchased_status'] = $this->request->post['report_lib_product_purchased_status'];
		} else {
			$data['report_lib_product_purchased_status'] = $this->config->get('report_lib_product_purchased_status');
		}

		if (isset($this->request->post['report_lib_product_purchased_sort_order'])) {
			$data['report_lib_product_purchased_sort_order'] = $this->request->post['report_lib_product_purchased_sort_order'];
		} else {
			$data['report_lib_product_purchased_sort_order'] = $this->config->get('report_lib_product_purchased_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/report/lib_product_purchased_form', $data));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/report/lib_product_purchased')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
		
	public function report() {

		$this->load->language('extension/report/lib_product_purchased');

		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
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

		$data['products'] = array();

		$filter_data = array(
			'filter_name'	     	 => $filter_name,
			'filter_date_start'	     => $filter_date_start,
			'filter_date_end'	     => $filter_date_end,
			'filter_order_status_id' => $filter_order_status_id,
			'start'                  => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                  => $this->config->get('config_limit_admin')
		);

		$product_total = $this->model_extension_report_libexport->getTotalPurchased($filter_data);

		$results = $this->model_extension_report_libexport->getPurchased($filter_data);

		foreach ($results as $result) {
			$data['products'][] = array(
				'name'     => $result['name'],
				'model'    => $result['model'],
				'quantity' => $result['quantity'],
				'total_viewed' => $result['total_viewed'],
				'total'    => $this->currency->format($result['total'], $this->config->get('config_currency'))
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		$url = '';

		
		if (isset($this->request->get['filter_name'])) {
				$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
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
		$pagination->total = $product_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=lib_product_purchased' . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));

		$data['filter_name'] = $filter_name;
		$data['filter_date_start'] = $filter_date_start;
		$data['filter_date_end'] = $filter_date_end;
		$data['filter_order_status_id'] = $filter_order_status_id;

		// Export 
		
		$filter_data_export = array(
			'filter_name'	     	 => $filter_name,
			'filter_date_start'	     => $filter_date_start,
			'filter_date_end'	     => $filter_date_end,
			'filter_order_status_id' => $filter_order_status_id
		);

		if (isset($this->request->get['export'])) {
			$button_export = $this->request->get['export'];
		} else {
			$button_export = '';
		}
		if($button_export !="" && $product_total > 0 && $product_total < 50000){
			
		$spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
		$sheet->setCellValue('A1', $this->language->get('column_name'));
        $sheet->setCellValue('B1', $this->language->get('column_model'));
        $sheet->setCellValue('C1', $this->language->get('column_quantity'));
		$sheet->setCellValue('D1', $this->language->get('column_total'));
		$sheet->setCellValue('E1', $this->language->get('column_viewed'));
        $rows = 2;
		$product_export = $this->model_extension_report_libexport->getPurchased($filter_data_export);
		
		foreach ($product_export as $key => $item){
            // Set style
        $sheet->getColumnDimension('A')->setAutoSize(true);
        $sheet->getColumnDimension('B')->setAutoSize(true);
		$sheet->getColumnDimension('C')->setAutoSize(true);
		$sheet->getColumnDimension('D')->setAutoSize(true);

        // Get value
		$item_total = $this->currency->format($item['total'], $this->config->get('config_currency'));
		
        $sheet->setCellValue('A' . $rows, $item['name']);
        $sheet->setCellValue('B' . $rows, $item['model']);
        $sheet->setCellValue('C' . $rows, $item['quantity']);
		$sheet->setCellValue('D' . $rows, $item_total);
		$sheet->setCellValue('E' . $rows, $item['total_viewed']);
        $rows++;        

        }
                
        $writer = new Xlsx($spreadsheet);
        $filename = 'ReportProductPurchased-' . strtotime('now');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'. $filename .'.xlsx"'); 
        header('Cache-Control: max-age=0');ob_end_clean();
        $writer->save('php://output'); 
        exit; 
			 
		}
		// End export

		return $this->load->view('extension/report/lib_product_purchased_info', $data);
	}
}