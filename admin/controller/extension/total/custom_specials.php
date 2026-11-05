<?php
class ControllerExtensionTotalcustomSpecials extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('extension/total/custom_specials');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('total_custom_specials', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true));
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
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/total/custom_specials', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['action'] = $this->url->link('extension/total/custom_specials', 'user_token=' . $this->session->data['user_token'], true);

		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=total', true);

		if (isset($this->request->post['total_custom_specials_tax_class_id'])) {
			$data['total_custom_specials_tax_class_id'] = $this->request->post['total_custom_specials_tax_class_id'];
		} else {
			$data['total_custom_specials_tax_class_id'] = $this->config->get('total_custom_specials_tax_class_id');
		}

		$this->load->model('localisation/tax_class');

		$data['tax_classes'] = $this->model_localisation_tax_class->getTaxClasses();

		if (isset($this->request->post['total_custom_specials_liquids_status'])) {
			$data['total_custom_specials_liquids_status'] = $this->request->post['total_custom_specials_liquids_status'];
		} else {
			$data['total_custom_specials_liquids_status'] = $this->config->get('total_custom_specials_liquids_status');
		}
		
		if (isset($this->request->post['total_custom_specials_pods_status'])) {
			$data['total_custom_specials_pods_status'] = $this->request->post['total_custom_specials_pods_status'];
		} else {
			$data['total_custom_specials_pods_status'] = $this->config->get('total_custom_specials_pods_status');
		}
		
		if (isset($this->request->post['total_custom_specials_liquid_and_pod_status'])) {
			$data['total_custom_specials_liquid_and_pod_status'] = $this->request->post['total_custom_specials_liquid_and_pod_status'];
		} else {
			$data['total_custom_specials_liquid_and_pod_status'] = $this->config->get('total_custom_specials_liquid_and_pod_status');
		}
		
		if (isset($this->request->post['total_custom_specials_status'])) {
			$data['total_custom_specials_status'] = $this->request->post['total_custom_specials_status'];
		} else {
			$data['total_custom_specials_status'] = $this->config->get('total_custom_specials_status');
		}

		if (isset($this->request->post['total_custom_specials_sort_order'])) {
			$data['total_custom_specials_sort_order'] = $this->request->post['total_custom_specials_sort_order'];
		} else {
			$data['total_custom_specials_sort_order'] = $this->config->get('total_custom_specials_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/total/custom_specials', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/total/custom_specials')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}