<?php

class ControllerExtensionModuleVaporsPunchGift extends Controller
{
	public function select()
	{
		$this->load->model('extension/module/vapors_punch_gift');

		$json = array(
			'success' => false,
			'valid' => true
		);

		if (!$this->model_extension_module_vapors_punch_gift->isActive()) {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
			return;
		}

		$slot_index = isset($this->request->post['slot_index']) ? (int)$this->request->post['slot_index'] : -1;
		$product_id = isset($this->request->post['product_id']) ? (int)$this->request->post['product_id'] : 0;

		if ($this->model_extension_module_vapors_punch_gift->selectGift($slot_index, $product_id)) {
			$json['success'] = true;
			$json['valid'] = $this->model_extension_module_vapors_punch_gift->validate();
			$json['total'] = $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function clear()
	{
		$this->load->model('extension/module/vapors_punch_gift');

		$json = array(
			'success' => false,
			'valid' => true
		);

		if (!$this->model_extension_module_vapors_punch_gift->isActive()) {
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
			return;
		}

		$slot_index = isset($this->request->post['slot_index']) ? (int)$this->request->post['slot_index'] : -1;

		if ($this->model_extension_module_vapors_punch_gift->clearSlot($slot_index)) {
			$json['success'] = true;
			$json['valid'] = $this->model_extension_module_vapors_punch_gift->validate();
			$json['total'] = $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function validate()
	{
		$this->load->model('extension/module/vapors_punch_gift');

		$json = array(
			'active' => $this->model_extension_module_vapors_punch_gift->isActive(),
			'valid' => true,
			'open_cart' => false
		);

		if ($json['active']) {
			$json['valid'] = $this->model_extension_module_vapors_punch_gift->validate();
			$json['open_cart'] = !$json['valid'];

			if (!$json['valid']) {
				$this->session->data['vapors_punch_gift_invalid'] = true;
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
