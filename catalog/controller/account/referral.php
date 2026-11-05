<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerAccountReferral extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/referral', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}


		$this->load->language('account/referral');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->setRobots('noindex,follow');
		
		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
        $data['sprite_version'] = $this->registry->get('sprite_version');
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/referral', $url, true)
		);

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}
			if (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}
		$data['referral'] = array();

		$this->load->model('account/transaction');
		$this->load->model('account/customer');
		
		$affiliate_info = $this->model_account_customer->getAffiliate($this->customer->getId());
		if (!$affiliate_info) {	
			$affiliate = array();
			$affiliate['company'] = 'default';
			$affiliate['website'] = 'default';
			$affiliate['tax'] = '1';
			$affiliate['payment'] = 'bank';
			$affiliate['cheque'] = '1';
			$affiliate['paypal'] = '1';
			$affiliate['bank_name'] = 'bank';
			$affiliate['bank_branch_number'] = 'bank';
			$affiliate['bank_swift_code'] = 'bank';
			$affiliate['bank_account_name'] = 'bank';
			$affiliate['bank_account_number'] = 'bank';
			$affiliate['bank_name'] = 'bank';
			$this->model_account_customer->addAffiliate($this->customer->getId(), $affiliate);
			$affiliate_info = $this->model_account_customer->getAffiliate($this->customer->getId());
		}
		$data['code'] = HTTPS_SERVER . '?tracking=' . $affiliate_info['tracking'];
		$data['transaction_total'] = $this->model_account_transaction->getTotalTransactions();
		$data['transaction_summ'] = $this->currency->format($this->customer->getBalance(), $this->session->data['currency']);;


		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/referral', $data));
	}

}