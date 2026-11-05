<?php
class ControllerMarketingCoupon extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('marketing/coupon');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('marketing/coupon');

		$this->getList();
	}

	public function add() {
		$this->load->language('marketing/coupon');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('marketing/coupon');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_marketing_coupon->addCoupon($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->getListUrl();

			$this->response->redirect($this->url->link('marketing/coupon', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function addgenerate() {
		$this->load->language('marketing/coupon');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('marketing/coupon');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {

			$coupon = $this->request->post;
			$chars = 'abdefhiknrstyz';
			$numChars = strlen($chars);
			$length = $this->request->post['quantity'];
			for ($i = 1; $i <= $length; $i++) {

				$string = '';
				for ($k = 0; $k < 5; $k++) {
					$string .= substr($chars, rand(1, $numChars) - 1, 1);
				}
				$newcoupon = $coupon;
				$newcoupon['code'] = $coupon['code'] . $string;
				$this->model_marketing_coupon->addCoupon($newcoupon);

			}

		//	$this->model_marketing_coupon->addCoupon($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$json['success'] = 'Промокоди додано';
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		} else {
			$json = $this->validateForm2();
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
		}

	//	$this->getForm();
	}

	public function edit() {
		$this->load->language('marketing/coupon');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('marketing/coupon');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_marketing_coupon->editCoupon($this->request->get['coupon_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->getListUrl();

			$this->response->redirect($this->url->link('marketing/coupon', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('marketing/coupon');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('marketing/coupon');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $coupon_id) {
				$this->model_marketing_coupon->deleteCoupon($coupon_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = $this->getListUrl();

			$this->response->redirect($this->url->link('marketing/coupon', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getListUrl($exclude = array()) {
		$url = '';

		$params = array(
			'filter_name',
			'filter_code',
			'filter_date_added_start',
			'filter_date_added_end',
			'filter_used',
			'filter_status',
			'sort',
			'order',
			'page'
		);

		foreach ($params as $param) {
			if (in_array($param, $exclude) || !isset($this->request->get[$param])) {
				continue;
			}

			if (in_array($param, array('filter_name', 'filter_code'))) {
				$url .= '&' . $param . '=' . urlencode(html_entity_decode($this->request->get[$param], ENT_QUOTES, 'UTF-8'));
			} else {
				$url .= '&' . $param . '=' . $this->request->get[$param];
			}
		}

		return $url;
	}

	protected function getList() {
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['filter_code'])) {
			$filter_code = $this->request->get['filter_code'];
		} else {
			$filter_code = '';
		}

		if (isset($this->request->get['filter_date_added_start'])) {
			$filter_date_added_start = $this->request->get['filter_date_added_start'];
		} else {
			$filter_date_added_start = '';
		}

		if (isset($this->request->get['filter_date_added_end'])) {
			$filter_date_added_end = $this->request->get['filter_date_added_end'];
		} else {
			$filter_date_added_end = '';
		}

		if (isset($this->request->get['filter_used'])) {
			$filter_used = $this->request->get['filter_used'];
		} else {
			$filter_used = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'date_added';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'DESC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = $this->getListUrl();

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketing/coupon', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('marketing/coupon/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('marketing/coupon/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['coupons'] = array();

		$filter_data = array(
			'filter_name'              => $filter_name,
			'filter_code'              => $filter_code,
			'filter_date_added_start'  => $filter_date_added_start,
			'filter_date_added_end'    => $filter_date_added_end,
			'filter_used'              => $filter_used,
			'filter_status'            => $filter_status,
			'sort'                     => $sort,
			'order'                    => $order,
			'start'                    => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit'                    => $this->config->get('config_limit_admin')
		);

		$coupon_total = $this->model_marketing_coupon->getTotalCoupons($filter_data);

		$results = $this->model_marketing_coupon->getCoupons($filter_data);

		foreach ($results as $result) {
			$uses = (int)$result['uses'];
			$uses_total = (int)$result['uses_total'];
			$now = time();
			$date_start = strtotime($result['date_start']);
			$date_end = strtotime($result['date_end']);
			$is_disabled = !(int)$result['status'];
			$is_pending = $date_start > $now;
			$is_expired = $date_end < $now;
			$is_exhausted = ($uses_total > 0 && $uses >= $uses_total);
			$is_used = ($uses_total == 1 && $uses > 0) || $is_exhausted;

			if ($is_used) {
				if ($uses_total == 1) {
					$uses_text = $this->language->get('text_used');
				} else {
					$uses_text = $this->language->get('text_exhausted') . ' ' . $uses . '/' . $uses_total;
				}
				$uses_class = 'success';
			} elseif ($uses_total != 1 && $uses > 0) {
				$limit_text = $uses_total > 0 ? $uses_total : $this->language->get('text_unlimited');
				$uses_text = $uses . ' / ' . $limit_text;
				$uses_class = 'primary';
			} else {
				$uses_text = $this->language->get('text_not_used');
				$uses_class = 'info';
			}

			if ($is_disabled) {
				$status_text = $this->language->get('text_disabled');
				$status_class = 'default';
			} elseif ($is_expired) {
				$status_text = $this->language->get('text_expired');
				$status_class = 'default';
			} elseif ($is_pending) {
				$status_text = $this->language->get('text_pending');
				$status_class = 'warning';
			} else {
				$status_text = $this->language->get('text_active');
				$status_class = 'success';
			}

			$data['coupons'][] = array(
				'coupon_id'     => $result['coupon_id'],
				'name'          => $result['name'],
				'code'          => $result['code'],
				'type'          => $result['type'],
				'discount'      => $result['discount'],
				'date_start'    => date('d.m.y H:i:s', strtotime($result['date_start'])),
				'date_end'      => date('d.m.y H:i:s', strtotime($result['date_end'])),
				'date_added'    => date('d.m.y H:i:s', strtotime($result['date_added'])),
				'uses'          => $uses_text,
				'uses_class'    => $uses_class,
				'status'        => $status_text,
				'status_class'  => $status_class,
				'edit'          => $this->url->link('marketing/coupon/edit', 'user_token=' . $this->session->data['user_token'] . '&coupon_id=' . $result['coupon_id'] . $url, true)
			);
		}

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$data['success'] = $this->session->data['success'];

			unset($this->session->data['success']);
		} else {
			$data['success'] = '';
		}

		if (isset($this->request->post['selected'])) {
			$data['selected'] = (array)$this->request->post['selected'];
		} else {
			$data['selected'] = array();
		}

		$url = $this->getListUrl(array('sort', 'order'));

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('marketing/coupon', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_code'] = $this->url->link('marketing/coupon', 'user_token=' . $this->session->data['user_token'] . '&sort=code' . $url, true);
		$data['sort_discount'] = $this->url->link('marketing/coupon', 'user_token=' . $this->session->data['user_token'] . '&sort=discount' . $url, true);
		$data['sort_date_start'] = $this->url->link('marketing/coupon', 'user_token=' . $this->session->data['user_token'] . '&sort=date_start' . $url, true);
		$data['sort_date_end'] = $this->url->link('marketing/coupon', 'user_token=' . $this->session->data['user_token'] . '&sort=date_end' . $url, true);
		$data['sort_date_added'] = $this->url->link('marketing/coupon', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url, true);
		$data['sort_uses'] = $this->url->link('marketing/coupon', 'user_token=' . $this->session->data['user_token'] . '&sort=uses' . $url, true);
		$data['sort_status'] = $this->url->link('marketing/coupon', 'user_token=' . $this->session->data['user_token'] . '&sort=status' . $url, true);

		$url = $this->getListUrl(array('page'));

		$pagination = new Pagination();
		$pagination->total = $coupon_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('marketing/coupon', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($coupon_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($coupon_total - $this->config->get('config_limit_admin'))) ? $coupon_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $coupon_total, ceil($coupon_total / $this->config->get('config_limit_admin')));

		$data['filter_name'] = $filter_name;
		$data['filter_code'] = $filter_code;
		$data['filter_date_added_start'] = $filter_date_added_start;
		$data['filter_date_added_end'] = $filter_date_added_end;
		$data['filter_used'] = $filter_used;
		$data['filter_status'] = $filter_status;

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketing/coupon_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['coupon_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');

		$data['user_token'] = $this->session->data['user_token'];

		if (isset($this->request->get['coupon_id'])) {
			$data['coupon_id'] = (int)$this->request->get['coupon_id'];
		} else {
			$data['coupon_id'] = 0;
		}

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['name'])) {
			$data['error_name'] = $this->error['name'];
		} else {
			$data['error_name'] = '';
		}

		if (isset($this->error['code'])) {
			$data['error_code'] = $this->error['code'];
		} else {
			$data['error_code'] = '';
		}

		if (isset($this->error['date_start'])) {
			$data['error_date_start'] = $this->error['date_start'];
		} else {
			$data['error_date_start'] = '';
		}

		if (isset($this->error['date_end'])) {
			$data['error_date_end'] = $this->error['date_end'];
		} else {
			$data['error_date_end'] = '';
		}

		$url = $this->getListUrl();

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketing/coupon', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['coupon_id'])) {
			$data['action'] = $this->url->link('marketing/coupon/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('marketing/coupon/edit', 'user_token=' . $this->session->data['user_token'] . '&coupon_id=' . $this->request->get['coupon_id'] . $url, true);
		}

		$data['actiongenerate'] = $this->url->link('marketing/coupon/addgenerate', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['cancel'] = $this->url->link('marketing/coupon', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['coupon_id']) && (!$this->request->server['REQUEST_METHOD'] != 'POST')) {
			$coupon_info = $this->model_marketing_coupon->getCoupon($this->request->get['coupon_id']);
		}

		if (isset($this->request->post['name'])) {
			$data['name'] = $this->request->post['name'];
		} elseif (!empty($coupon_info)) {
			$data['name'] = $coupon_info['name'];
		} else {
			$data['name'] = '';
		}

		if (isset($this->request->post['conditions'])) {
			$data['conditions'] = $this->request->post['conditions'];
		} elseif (!empty($coupon_info)) {
			$data['conditions'] = $coupon_info['conditions'];
		} else {
			$data['conditions'] = '';
		}

		if (isset($this->request->post['code'])) {
			$data['code'] = $this->request->post['code'];
		} elseif (!empty($coupon_info)) {
			$data['code'] = $coupon_info['code'];
		} else {
			$data['code'] = '';
		}

		if (isset($this->request->post['type'])) {
			$data['type'] = $this->request->post['type'];
		} elseif (!empty($coupon_info)) {
			$data['type'] = $coupon_info['type'];
		} else {
			$data['type'] = '';
		}

		if (isset($this->request->post['discount'])) {
			$data['discount'] = $this->request->post['discount'];
		} elseif (!empty($coupon_info)) {
			$data['discount'] = $coupon_info['discount'];
		} else {
			$data['discount'] = '';
		}

		if (isset($this->request->post['logged'])) {
			$data['logged'] = $this->request->post['logged'];
		} elseif (!empty($coupon_info)) {
			$data['logged'] = $coupon_info['logged'];
		} else {
			$data['logged'] = '';
		}

		if (isset($this->request->post['shipping'])) {
			$data['shipping'] = $this->request->post['shipping'];
		} elseif (!empty($coupon_info)) {
			$data['shipping'] = $coupon_info['shipping'];
		} else {
			$data['shipping'] = '';
		}

		if (isset($this->request->post['total'])) {
			$data['total'] = $this->request->post['total'];
		} elseif (!empty($coupon_info)) {
			$data['total'] = $coupon_info['total'];
		} else {
			$data['total'] = '';
		}

		if (isset($this->request->post['coupon_product'])) {
			$products = $this->request->post['coupon_product'];
		} elseif (isset($this->request->get['coupon_id'])) {
			$products = $this->model_marketing_coupon->getCouponProducts($this->request->get['coupon_id']);
		} else {
			$products = array();
		}

		$this->load->model('catalog/product');

		$data['coupon_product'] = array();

		foreach ($products as $product_id) {
			$product_info = $this->model_catalog_product->getProduct($product_id);

			if ($product_info) {
				$data['coupon_product'][] = array(
					'product_id' => $product_info['product_id'],
					'name'       => $product_info['name']
				);
			}
		}

		if (isset($this->request->post['coupon_category'])) {
			$categories = $this->request->post['coupon_category'];
		} elseif (isset($this->request->get['coupon_id'])) {
			$categories = $this->model_marketing_coupon->getCouponCategories($this->request->get['coupon_id']);
		} else {
			$categories = array();
		}

		$this->load->model('catalog/category');

		$data['coupon_category'] = array();

		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['coupon_category'][] = array(
					'category_id' => $category_info['category_id'],
					'name'        => ($category_info['path'] ? $category_info['path'] . ' &gt; ' : '') . $category_info['name']
				);
			}
		}

		if (isset($this->request->post['coupon_category_required'])) {
			$categories = $this->request->post['coupon_category_required'];
		} elseif (isset($this->request->get['coupon_id'])) {
			$categories = $this->model_marketing_coupon->getCouponCategoriesrequired($this->request->get['coupon_id']);
		} else {
			$categories = array();
		}

		$this->load->model('catalog/category');

		$data['coupon_category_required'] = array();

		foreach ($categories as $category_id) {
			$category_info = $this->model_catalog_category->getCategory($category_id);

			if ($category_info) {
				$data['coupon_category_required'][] = array(
					'category_id' => $category_info['category_id'],
					'name'        => ($category_info['path'] ? $category_info['path'] . ' &gt; ' : '') . $category_info['name']
				);
			}
		}

		if (isset($this->request->post['date_start'])) {
			$data['date_start'] = $this->request->post['date_start'];
		} elseif (!empty($coupon_info)) {
			$data['date_start'] = ($coupon_info['date_start'] != '0000-00-00' ? $coupon_info['date_start'] : '');
		} else {
			$data['date_start'] = date('Y-m-d', time());
		}

		if (isset($this->request->post['date_end'])) {
			$data['date_end'] = $this->request->post['date_end'];
		} elseif (!empty($coupon_info)) {
			$data['date_end'] = ($coupon_info['date_end'] != '0000-00-00' ? $coupon_info['date_end'] : '');
		} else {
			$data['date_end'] = date('Y-m-d', strtotime('+1 month'));
		}

		if (isset($this->request->post['uses_total'])) {
			$data['uses_total'] = $this->request->post['uses_total'];
		} elseif (!empty($coupon_info)) {
			$data['uses_total'] = $coupon_info['uses_total'];
		} else {
			$data['uses_total'] = 1;
		}

		if (isset($this->request->post['uses_customer'])) {
			$data['uses_customer'] = $this->request->post['uses_customer'];
		} elseif (!empty($coupon_info)) {
			$data['uses_customer'] = $coupon_info['uses_customer'];
		} else {
			$data['uses_customer'] = 1;
		}

		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($coupon_info)) {
			$data['status'] = $coupon_info['status'];
		} else {
			$data['status'] = true;
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketing/coupon_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'marketing/coupon')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 128)) {
			$this->error['name'] = $this->language->get('error_name');
		}

		if ((utf8_strlen($this->request->post['code']) < 3) || (utf8_strlen($this->request->post['code']) > 20)) {
			$this->error['code'] = $this->language->get('error_code');
		}

		$coupon_info = $this->model_marketing_coupon->getCouponByCode($this->request->post['code']);

		if ($coupon_info) {
			if (!isset($this->request->get['coupon_id'])) {
				$this->error['warning'] = $this->language->get('error_exists');
			} elseif ($coupon_info['coupon_id'] != $this->request->get['coupon_id']) {
				$this->error['warning'] = $this->language->get('error_exists');
			}
		}

		return !$this->error;
	}

	protected function validateForm2() {

		if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 128)) {
			$this->error['error1'] = $this->language->get('error_name');
		}

		if ((utf8_strlen($this->request->post['code']) < 3) || (utf8_strlen($this->request->post['code']) > 20)) {
			$this->error['error1'] = $this->language->get('error_code');
		}

		$coupon_info = $this->model_marketing_coupon->getCouponByCode($this->request->post['code']);

		if ($coupon_info) {
			if (!isset($this->request->get['coupon_id'])) {
				$this->error['error1'] = $this->language->get('error_exists');
			} elseif ($coupon_info['coupon_id'] != $this->request->get['coupon_id']) {
				$this->error['error1'] = $this->language->get('error_exists');
			}
		}

		return $this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'marketing/coupon')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function history() {
		$this->load->language('marketing/coupon');

		$this->load->model('marketing/coupon');

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$data['histories'] = array();

		$results = $this->model_marketing_coupon->getCouponHistories($this->request->get['coupon_id'], ($page - 1) * 10, 10);

		foreach ($results as $result) {
			$data['histories'][] = array(
				'order_id'   => $result['order_id'],
				'customer'   => $result['customer'],
				'amount'     => $result['amount'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}

		$history_total = $this->model_marketing_coupon->getTotalCouponHistories($this->request->get['coupon_id']);

		$pagination = new Pagination();
		$pagination->total = $history_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('marketing/coupon/history', 'user_token=' . $this->session->data['user_token'] . '&coupon_id=' . $this->request->get['coupon_id'] . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($history_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($history_total - 10)) ? $history_total : ((($page - 1) * 10) + 10), $history_total, ceil($history_total / 10));

		$this->response->setOutput($this->load->view('marketing/coupon_history', $data));
	}
}