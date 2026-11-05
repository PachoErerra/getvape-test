<?php
class ControllerExtensionDashboardChart extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/dashboard/chart');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('dashboard_chart', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true));
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
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/dashboard/chart', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/dashboard/chart', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true);

		if (isset($this->request->post['dashboard_chart_width'])) {
			$data['dashboard_chart_width'] = $this->request->post['dashboard_chart_width'];
		} else {
			$data['dashboard_chart_width'] = $this->config->get('dashboard_chart_width');
		}
	
		$data['columns'] = array();
		
		for ($i = 3; $i <= 12; $i++) {
			$data['columns'][] = $i;
		}
				
		if (isset($this->request->post['dashboard_chart_status'])) {
			$data['dashboard_chart_status'] = $this->request->post['dashboard_chart_status'];
		} else {
			$data['dashboard_chart_status'] = $this->config->get('dashboard_chart_status');
		}

		if (isset($this->request->post['dashboard_chart_sort_order'])) {
			$data['dashboard_chart_sort_order'] = $this->request->post['dashboard_chart_sort_order'];
		} else {
			$data['dashboard_chart_sort_order'] = $this->config->get('dashboard_chart_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/dashboard/chart_form', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/dashboard/chart')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}	
	
	public function dashboard() {
		$this->load->language('extension/dashboard/chart');

		$data['user_token'] = $this->session->data['user_token'];
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_today'] = $this->language->get('text_today');
		$data['text_yesterday'] = $this->language->get('text_yesterday');
		$data['text_week'] = $this->language->get('text_week');
		$data['text_month'] = $this->language->get('text_month');
		$data['text_custom'] = $this->language->get('text_custom');
		$data['text_filter'] = $this->language->get('text_filter');
		$data['text_date_start'] = $this->language->get('text_date_start');
		$data['text_date_end'] = $this->language->get('text_date_end');
		$data['text_period'] = $this->language->get('text_period');
		$data['text_order'] = $this->language->get('text_order');
		$data['text_customer'] = $this->language->get('text_customer');
		$data['text_total'] = $this->language->get('text_total');

		return $this->load->view('extension/dashboard/chart_info', $data);
	}

	public function chart() {
		$this->load->language('extension/dashboard/chart');

		$json = array();

		$this->load->model('extension/dashboard/chart');

		$json['order'] = array(
			'label' => $this->language->get('text_order'),
			'data'  => array()
		);
		$json['customer'] = array(
			'label' => $this->language->get('text_customer'),
			'data'  => array()
		);
		$json['xaxis'] = array();

		$range = isset($this->request->get['range']) ? $this->request->get['range'] : 'month';
		$date_start = isset($this->request->get['date_start']) ? $this->request->get['date_start'] : '';
		$date_end = isset($this->request->get['date_end']) ? $this->request->get['date_end'] : '';

		switch ($range) {
			case 'yesterday':
				$date_start = date('Y-m-d', strtotime('-1 day'));
				$date_end = $date_start;
				break;
			case 'week':
				$date_start = date('Y-m-d', strtotime('-6 days'));
				$date_end = date('Y-m-d');
				break;
			case 'today':
				$date_start = date('Y-m-d');
				$date_end = $date_start;
				break;
			case 'custom':
				if (!$this->isValidDate($date_start)) {
					$date_start = date('Y-m-d');
				}

				if (!$this->isValidDate($date_end)) {
					$date_end = $date_start;
				}

				if (strtotime($date_start) > strtotime($date_end)) {
					$tmp = $date_start;
					$date_start = $date_end;
					$date_end = $tmp;
				}
				break;
			case 'day':
			case 'month':
			default:
				$range = 'month';
				$date_start = date('Y-m-01');
				$date_end = date('Y-m-d');
				break;
		}

		if ($date_start == $date_end) {
			$results = $this->model_extension_dashboard_chart->getTotalOrdersByHour($date_start);

			foreach ($results as $key => $value) {
				$json['order']['data'][] = array($key, (int)$value['total']);
			}

			$results = $this->model_extension_dashboard_chart->getTotalCustomersByHour($date_start);

			foreach ($results as $key => $value) {
				$json['customer']['data'][] = array($key, (int)$value['total']);
			}

			for ($i = 0; $i < 24; $i++) {
				$json['xaxis'][] = array($i, sprintf('%02d:00', $i));
			}
		} else {
			list($order_results, $days) = $this->model_extension_dashboard_chart->getTotalOrdersByDateRange($date_start, $date_end);
			list($customer_results) = $this->model_extension_dashboard_chart->getTotalCustomersByDateRange($date_start, $date_end);

			foreach ($days as $index => $day) {
				$json['order']['data'][] = array($index, isset($order_results[$day]) ? (int)$order_results[$day]['total'] : 0);
				$json['customer']['data'][] = array($index, isset($customer_results[$day]) ? (int)$customer_results[$day]['total'] : 0);
				$json['xaxis'][] = array($index, date('d.m', strtotime($day)));
			}
		}

		$json['range'] = $range;
		$json['date_start'] = $date_start;
		$json['date_end'] = $date_end;
		$json['period_label'] = ($date_start == $date_end) ? $date_start : ($date_start . ' - ' . $date_end);

		$order_total = 0;
		$customer_total = 0;

		foreach ($json['order']['data'] as $point) {
			$order_total += (int)$point[1];
		}

		foreach ($json['customer']['data'] as $point) {
			$customer_total += (int)$point[1];
		}

		$json['order_total'] = $order_total;
		$json['customer_total'] = $customer_total;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	private function isValidDate($date) {
		if (!$date || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
			return false;
		}

		$parts = explode('-', $date);

		return checkdate((int)$parts[1], (int)$parts[2], (int)$parts[0]);
	}
}