<?php
class ControllerExtensionReportReferral extends Controller {
	public function index() {
		$this->load->language('extension/report/referral');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('report_referral', $this->request->post);

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
			'href' => $this->url->link('extension/report/referral', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/report/referral', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=report', true);

		if (isset($this->request->post['report_referral_status'])) {
			$data['report_referral_status'] = $this->request->post['report_referral_status'];
		} else {
			$data['report_referral_status'] = $this->config->get('report_referral_status');
		}

		if (isset($this->request->post['report_referral_sort_order'])) {
			$data['report_referral_sort_order'] = $this->request->post['report_referral_sort_order'];
		} else {
			$data['report_referral_sort_order'] = $this->config->get('report_referral_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/report/referral_form', $data));
	}

	public function install() {
		$this->load->model('extension/report/referral');
		$this->model_extension_report_referral->ensureSchema();

		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('report_referral', array(
			'report_referral_status'     => 1,
			'report_referral_sort_order' => 98
		));
	}

	public function uninstall() {
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('report_referral');
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/report/referral')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function report() {
		$this->load->language('extension/report/referral');

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

		if (isset($this->request->get['filter_referrer'])) {
			$filter_referrer = $this->request->get['filter_referrer'];
		} else {
			$filter_referrer = '';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$this->load->model('extension/report/referral');

		if (!$this->config->get('report_referral_schema')) {
			$this->model_extension_report_referral->ensureSchema();
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('report_referral_schema', array('report_referral_schema' => 1));
		}

		$filter_data = array(
			'filter_date_start' => $filter_date_start,
			'filter_date_end'   => $filter_date_end,
			'filter_referrer'   => $filter_referrer,
			'start'             => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'             => $this->config->get('config_limit_admin')
		);

		$data['summary'] = $this->model_extension_report_referral->getSummary($filter_data);
		$data['top_referrers'] = array();

		$top_results = $this->model_extension_report_referral->getTopReferrers($filter_data);

		foreach ($top_results as $result) {
			$data['top_referrers'][] = array(
				'name'             => trim($result['firstname'] . ' ' . $result['lastname']),
				'telephone'        => $result['telephone'],
				'clicks'           => (int)$result['clicks'],
				'invited_count'    => (int)$result['invited_count'],
				'converted_count'  => (int)$result['converted_count'],
				'orders_count'     => (int)$result['orders_count'],
				'pending_count'    => (int)$result['pending_count'],
				'total_bonus'      => $this->currency->format($result['total_bonus'], $this->config->get('config_currency')),
				'edit'             => $this->url->link('customer/customer/edit', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $result['customer_id'], true)
			);
		}

		$referral_total = $this->model_extension_report_referral->getTotalReferrals($filter_data);
		$results = $this->model_extension_report_referral->getReferrals($filter_data);

		$data['referrals'] = array();

		foreach ($results as $result) {
			switch ($this->model_extension_report_referral->resolveBonusState($result)) {
				case 'paid':
					$bonus_status = $this->language->get('text_bonus_paid');
					$bonus_class = 'label-success';
					break;
				case 'cancelled':
					$bonus_status = $this->language->get('text_bonus_cancelled');
					$bonus_class = 'label-danger';
					break;
				case 'pending':
					$bonus_status = $this->language->get('text_bonus_pending');
					$bonus_class = 'label-warning';
					break;
				default:
					$bonus_status = $this->language->get('text_bonus_none');
					$bonus_class = 'label-default';
					break;
			}

			$data['referrals'][] = array(
				'referal_date'       => $result['referal_date'],
				'invited_name'       => trim($result['invited_firstname'] . ' ' . $result['invited_lastname']),
				'invited_telephone'  => $result['invited_telephone'],
				'invited_edit'       => $this->url->link('customer/customer/edit', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $result['customer_id'], true),
				'referrer_name'      => trim($result['referrer_firstname'] . ' ' . $result['referrer_lastname']),
				'referrer_telephone' => $result['referrer_telephone'],
				'referrer_edit'      => $this->url->link('customer/customer/edit', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $result['referrer_id'], true),
				'bonus_status'       => $bonus_status,
				'bonus_class'        => $bonus_class,
				'order_id'           => $result['order_id'] ? $result['order_id'] : '—',
				'order_date'         => $result['order_date'] ? date($this->language->get('date_format_short'), strtotime($result['order_date'])) : '—',
				'order_status'       => $result['order_status'] ? $result['order_status'] : '—',
				'order_view'         => $result['order_id'] ? $this->url->link('sale/order/info', 'user_token=' . $this->session->data['user_token'] . '&order_id=' . $result['order_id'], true) : ''
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$url = '';

		if (isset($this->request->get['filter_date_start'])) {
			$url .= '&filter_date_start=' . $this->request->get['filter_date_start'];
		}

		if (isset($this->request->get['filter_date_end'])) {
			$url .= '&filter_date_end=' . $this->request->get['filter_date_end'];
		}

		if (isset($this->request->get['filter_referrer'])) {
			$url .= '&filter_referrer=' . urlencode($this->request->get['filter_referrer']);
		}

		$pagination = new Pagination();
		$pagination->total = $referral_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('report/report', 'user_token=' . $this->session->data['user_token'] . '&code=referral' . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($referral_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($referral_total - $this->config->get('config_limit_admin'))) ? $referral_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $referral_total, ceil($referral_total / $this->config->get('config_limit_admin')));

		$data['filter_date_start'] = $filter_date_start;
		$data['filter_date_end'] = $filter_date_end;
		$data['filter_referrer'] = $filter_referrer;

		return $this->load->view('extension/report/referral_info', $data);
	}
}

