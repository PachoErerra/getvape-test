<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerAccountPromocodes extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/promocodes', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}


		$this->load->language('account/promocodes');

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
			'href' => $this->url->link('account/promocodes', $url, true)
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
		$data['promocodes'] = array();

		$this->load->model('account/order');

		$results = $this->model_account_order->getactivepromocodes();
		
		$resultsBP = $this->model_account_order->getactivepromocodesBP();
		
		$resultsall = array_merge($results, $resultsBP);

		$date_now = new DateTime(date('Y-m-d H:i:s'));
		
		foreach ($resultsall as $result) {
			$date_valid = new DateTime($result['date_valid']);
			$now   = new DateTime();
			
			if ($result['used'] || $date_valid < $now) {
				$status = 'used';
				$substatus = 'Використаний';
			} else {
				$status = 'active';
				$substatus = 'Активний';
			}
			if (!$result['used'] && $date_valid < $now) {
				$substatus = 'Прострочений';
			}
			
			$dt = new DateTime($result['date_valid']);

			$data['promocodes'][$status][] = array(
				'date_added'   => $result['date_added'],
				'date_valid'   => $dt->format('H:i:s d.m.Y'),
				'prize'       => $result['prize'],
				'code'     => $result['code'],
				'conditions'     => $result['conditions'],
				'substatus'     => $substatus,
			);
		}

		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/promocodes', $data));
	}

}