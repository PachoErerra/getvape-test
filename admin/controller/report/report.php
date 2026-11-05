<?php
class ControllerReportReport extends Controller {
	public function index() {
		$this->load->language('report/report');

		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['code'])) {
			$data['code'] = $this->request->get['code'];
		} else {
			$data['code'] = '';
		}

		$data['reports'] = array();
		$data['reports_custom'] = array();
		$data['reports_other'] = array();

		$this->load->model('setting/extension');

		$custom_reports = $this->config->get('_gv_custom_reports');

		if (!is_array($custom_reports)) {
			$custom_reports = array(
				'customer_repeat_order',
				'referral',
				'cod_processing_time',
				'cancelled_orders',
				'contact_method'
			);
		}

		$extensions = $this->model_setting_extension->getInstalled('report');

		foreach ($extensions as $code) {
			if ($this->config->get('report_' . $code . '_status') && $this->user->hasPermission('access', 'extension/report/' . $code)) {
				$this->load->language('extension/report/' . $code, 'extension');

				$title = $this->language->get('extension')->get('heading_title');
				$is_custom = in_array($code, $custom_reports, true);

				$item = array(
					'text'       => $title,
					'code'       => $code,
					'custom'     => $is_custom,
					'sort_order' => $this->config->get('report_' . $code . '_sort_order'),
					'href'       => $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=' . $code, true)
				);

				$data['reports'][] = $item;

				if ($is_custom) {
					$data['reports_custom'][] = $item;
				} else {
					$data['reports_other'][] = $item;
				}
			}
		}

		$sort_order = array();

		foreach ($data['reports_custom'] as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_DESC, $data['reports_custom']);

		$sort_order = array();

		foreach ($data['reports_other'] as $key => $value) {
			$sort_order[$key] = $value['sort_order'];
		}

		array_multisort($sort_order, SORT_ASC, $data['reports_other']);

		$data['reports'] = array_merge($data['reports_custom'], $data['reports_other']);

		if ($data['code'] === '' && !empty($data['reports'][0])) {
			$data['code'] = $data['reports'][0]['code'];
		}

		if ($data['code'] !== '') {
			$data['report'] = $this->load->controller('extension/report/' . $data['code'] . '/report');
		} else {
			$data['report'] = '';
		}

		$data['text_our_reports'] = $this->language->get('text_our_reports');
		$data['text_other_reports'] = $this->language->get('text_other_reports');
		$data['text_type'] = $this->language->get('text_type');
		$data['text_filter'] = $this->language->get('text_filter');
		$data['text_choose_other'] = $this->language->get('text_choose_other');

		if ($data['text_our_reports'] === 'text_our_reports') {
			$data['text_our_reports'] = 'Наши отчеты (GV)';
		}

		if ($data['text_other_reports'] === 'text_other_reports') {
			$data['text_other_reports'] = 'Остальные отчеты';
		}

		if ($data['text_choose_other'] === 'text_choose_other') {
			$data['text_choose_other'] = 'Выберите отчет...';
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('report/report', $data));
	}
}
