<?php
class ControllerExtensionDashboardOrdersDay extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/dashboard/orders_day');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('dashboard_orders_day', $this->request->post);

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
			'href' => $this->url->link('extension/dashboard/orders_day', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/dashboard/orders_day', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=dashboard', true);

		if (isset($this->request->post['dashboard_orders_day_width'])) {
			$data['dashboard_orders_day_width'] = $this->request->post['dashboard_orders_day_width'];
		} else {
			$data['dashboard_orders_day_width'] = $this->config->get('dashboard_orders_day_width');
		}

		$data['columns'] = array();

		for ($i = 3; $i <= 12; $i++) {
			$data['columns'][] = $i;
		}

		if (isset($this->request->post['dashboard_orders_day_status'])) {
			$data['dashboard_orders_day_status'] = $this->request->post['dashboard_orders_day_status'];
		} else {
			$data['dashboard_orders_day_status'] = $this->config->get('dashboard_orders_day_status');
		}

		if (isset($this->request->post['dashboard_orders_day_sort_order'])) {
			$data['dashboard_orders_day_sort_order'] = $this->request->post['dashboard_orders_day_sort_order'];
		} else {
			$data['dashboard_orders_day_sort_order'] = $this->config->get('dashboard_orders_day_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/dashboard/orders_day_form', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/dashboard/orders_day')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function install() {
		$this->load->model('setting/setting');

		$this->model_setting_setting->editSetting('dashboard_orders_day', array(
			'dashboard_orders_day_status'     => 1,
			'dashboard_orders_day_width'      => 12,
			'dashboard_orders_day_sort_order' => 9
		));
	}

	public function uninstall() {
		$this->load->model('setting/setting');

		$this->model_setting_setting->deleteSetting('dashboard_orders_day');
	}

	public function dashboard() {
		$this->load->language('extension/dashboard/orders_day');

		$data['user_token'] = $this->session->data['user_token'];
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_subtitle'] = $this->language->get('text_subtitle');
		$data['text_today'] = $this->language->get('text_today');
		$data['text_yesterday'] = $this->language->get('text_yesterday');
		$data['text_week'] = $this->language->get('text_week');
		$data['text_month'] = $this->language->get('text_month');
		$data['text_custom'] = $this->language->get('text_custom');
		$data['text_filter'] = $this->language->get('text_filter');
		$data['text_date_start'] = $this->language->get('text_date_start');
		$data['text_date_end'] = $this->language->get('text_date_end');
		$data['text_period'] = $this->language->get('text_period');
		$data['text_total'] = $this->language->get('text_total');
		$data['text_orders'] = $this->language->get('text_orders');
		$data['text_xaxis'] = $this->language->get('text_xaxis');
		$data['text_yaxis'] = $this->language->get('text_yaxis');
		$data['text_click_orders'] = $this->language->get('text_click_orders');
		$data['datepicker'] = $this->language->get('code');
		$data['order_list'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'], true);

		return $this->load->view('extension/dashboard/orders_day_info', $data);
	}

	public function chart() {
		$this->load->language('extension/dashboard/orders_day');

		$json = array();

		$this->load->model('extension/dashboard/orders_day');

		$range = isset($this->request->get['range']) ? $this->request->get['range'] : 'today';
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
			case 'month':
				$date_start = date('Y-m-01');
				$date_end = date('Y-m-d');
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
			default:
				$range = 'today';
				$date_start = date('Y-m-d');
				$date_end = $date_start;
				break;
		}

		$results = $this->model_extension_dashboard_orders_day->getTotalOrdersByHalfHour($date_start, $date_end);

		$json['order'] = array(
			'label' => $this->language->get('text_orders'),
			'data'  => array()
		);
		$json['labels'] = array();
		$json['xaxis'] = array();

		for ($i = 0; $i < 48; $i++) {
			$total = isset($results[$i]) ? (int)$results[$i]['total'] : 0;
			$label = $this->model_extension_dashboard_orders_day->getIntervalLabel($i);

			$json['order']['data'][] = array($i, $total);
			$json['labels'][$i] = $label;
			$json['xaxis'][] = array($i, $label);
		}

		$json['range'] = $range;
		$json['date_start'] = $date_start;
		$json['date_end'] = $date_end;
		$json['period_label'] = ($date_start == $date_end) ? $date_start : ($date_start . ' - ' . $date_end);
		$json['text_orders'] = $this->language->get('text_orders_count');

		$total = 0;

		foreach ($json['order']['data'] as $point) {
			$total += (int)$point[1];
		}

		$json['total'] = $total;

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
