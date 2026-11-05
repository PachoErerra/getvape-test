<?php
class ControllerCatalogPromo extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('catalog/promo');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/promo');

		$this->getList();
	}

	public function add() {
		$this->load->language('catalog/promo');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/promo');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_promo->addPromo($this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/promo', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function edit() {
		$this->load->language('catalog/promo');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/promo');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validateForm()) {
			$this->model_catalog_promo->editPromo($this->request->get['promo_id'], $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/promo', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getForm();
	}

	public function delete() {
		$this->load->language('catalog/promo');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('catalog/promo');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $promo_id) {
				$this->model_catalog_promo->deletePromo($promo_id);
			}

			$this->session->data['success'] = $this->language->get('text_success');

			$url = '';

			if (isset($this->request->get['sort'])) {
				$url .= '&sort=' . $this->request->get['sort'];
			}

			if (isset($this->request->get['order'])) {
				$url .= '&order=' . $this->request->get['order'];
			}

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$this->response->redirect($this->url->link('catalog/promo', 'user_token=' . $this->session->data['user_token'] . $url, true));
		}

		$this->getList();
	}

	protected function getList() {
		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'pd.title';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = $this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/promo', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		$data['add'] = $this->url->link('catalog/promo/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		$data['delete'] = $this->url->link('catalog/promo/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

		$data['promos'] = array();

		$filter_data = array(
			'sort'  => $sort,
			'order' => $order,
			'start' => ($page - 1) * $this->config->get('config_limit_admin'),
			'limit' => $this->config->get('config_limit_admin')
		);

		$promo_total = $this->model_catalog_promo->getTotalPromos();

		$results = $this->model_catalog_promo->getPromos($filter_data);

		foreach ($results as $result) {
			$data['promos'][] = array(
				'promo_id'   => $result['promo_id'],
				'title'      => $result['title'],
				'status'     => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'edit'       => $this->url->link('catalog/promo/edit', 'user_token=' . $this->session->data['user_token'] . '&promo_id=' . $result['promo_id'] . $url, true)
			);
		}

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

		$url = '';

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_title'] = $this->url->link('catalog/promo', 'user_token=' . $this->session->data['user_token'] . '&sort=pd.title' . $url, true);

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $promo_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('catalog/promo', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();
		$data['results'] = sprintf($this->language->get('text_pagination'), ($promo_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($promo_total - $this->config->get('config_limit_admin'))) ? $promo_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $promo_total, ceil($promo_total / $this->config->get('config_limit_admin')));

		$data['sort'] = $sort;
		$data['order'] = $order;

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/promo_list', $data));
	}

	protected function getForm() {
		$data['text_form'] = !isset($this->request->get['promo_id']) ? $this->language->get('text_add') : $this->language->get('text_edit');
        
		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['title'])) {
			$data['error_title'] = $this->error['title'];
		} else {
			$data['error_title'] = array();
		}

		if (isset($this->error['meta_title'])) {
			$data['error_meta_title'] = $this->error['meta_title'];
		} else {
			$data['error_meta_title'] = array();
		}

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('catalog/promo', 'user_token=' . $this->session->data['user_token'] . $url, true)
		);

		if (!isset($this->request->get['promo_id'])) {
			$data['action'] = $this->url->link('catalog/promo/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
		} else {
			$data['action'] = $this->url->link('catalog/promo/edit', 'user_token=' . $this->session->data['user_token'] . '&promo_id=' . $this->request->get['promo_id'] . $url, true);
		}

		$data['cancel'] = $this->url->link('catalog/promo', 'user_token=' . $this->session->data['user_token'] . $url, true);

		if (isset($this->request->get['promo_id']) && ($this->request->server['REQUEST_METHOD'] != 'POST')) {
			$promo_info = $this->model_catalog_promo->getPromo($this->request->get['promo_id']);
		}

		$data['user_token'] = $this->session->data['user_token'];

		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();

		if (isset($this->request->post['promo_description'])) {
			$data['promo_description'] = $this->request->post['promo_description'];
		} elseif (isset($this->request->get['promo_id'])) {
			$data['promo_description'] = $this->model_catalog_promo->getPromoDescriptions($this->request->get['promo_id']);
		} else {
			$data['promo_description'] = array();
		}
		
		// Получаем список магазинов
		$this->load->model('setting/store');
		$data['stores'] = array();
		$data['stores'][] = array('store_id' => 0, 'name' => $this->config->get('config_name'));
		foreach ($this->model_setting_store->getStores() as $result) {
			$data['stores'][] = array('store_id' => $result['store_id'], 'name' => $result['name']);
		}

		// Загружаем существующие SEO URL
		$data['promo_seo_url'] = array();
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "seo_url WHERE query = 'promo_id=" . (int)$this->request->get['promo_id'] . "'");
		foreach ($query->rows as $result) {
			$data['promo_seo_url'][$result['store_id']][$result['language_id']] = $result['keyword'];
		}
		if (isset($this->request->post['status'])) {
			$data['status'] = $this->request->post['status'];
		} elseif (!empty($promo_info)) {
			$data['status'] = $promo_info['status'];
		} else {
			$data['status'] = true;
		}
		if (isset($this->request->post['noindex'])) {
			$data['noindex'] = $this->request->post['noindex'];
		} elseif (!empty($promo_info)) {
			$data['noindex'] = $promo_info['noindex'];
		} else {
			$data['noindex'] = true;
		}
		if (isset($this->request->post['sort_order'])) {
			$data['sort_order'] = $this->request->post['sort_order'];
		} elseif (!empty($promo_info)) {
			$data['sort_order'] = $promo_info['sort_order'];
		} else {
			$data['sort_order'] = 0;
		}

        // --- Categories ---
		$this->load->model('catalog/category');
		$data['promo_categories'] = array();
		
		if (isset($this->request->post['promo_category'])) {
			$promo_categories = $this->request->post['promo_category'];
		} elseif (isset($this->request->get['promo_id'])) {
			$promo_categories = $this->model_catalog_promo->getPromoCategories($this->request->get['promo_id']);
		} else {
			$promo_categories = array();
		}

		foreach ($promo_categories as $promo_category) {
			$category_info = $this->model_catalog_category->getCategory($promo_category['category_id']);
			if ($category_info) {
				$data['promo_categories'][] = array(
					'category_id' => $category_info['category_id'],
					'name'        => ($category_info['path']) ? $category_info['path'] . ' &gt; ' . $category_info['name'] : $category_info['name'],
					'discount'    => $promo_category['discount']
				);
			}
		}

        // --- Manufacturers ---
        $this->load->model('catalog/manufacturer');
		$data['promo_manufacturers'] = array();
		
		if (isset($this->request->post['promo_manufacturer'])) {
			$promo_manufacturers = $this->request->post['promo_manufacturer'];
		} elseif (isset($this->request->get['promo_id'])) {
			$promo_manufacturers = $this->model_catalog_promo->getPromoManufacturers($this->request->get['promo_id']);
		} else {
			$promo_manufacturers = array();
		}

		foreach ($promo_manufacturers as $promo_manufacturer) {
			$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($promo_manufacturer['manufacturer_id']);
			if ($manufacturer_info) {
				$data['promo_manufacturers'][] = array(
					'manufacturer_id' => $manufacturer_info['manufacturer_id'],
					'name'            => $manufacturer_info['name'],
					'discount'        => $promo_manufacturer['discount']
				);
			}
		}

        // --- Products ---
        $this->load->model('catalog/product');
		$data['promo_products'] = array();
		
		if (isset($this->request->post['promo_product'])) {
			$promo_products = $this->request->post['promo_product'];
		} elseif (isset($this->request->get['promo_id'])) {
			$promo_products = $this->model_catalog_promo->getPromoProducts($this->request->get['promo_id']);
		} else {
			$promo_products = array();
		}

		foreach ($promo_products as $promo_product) {
			$product_info = $this->model_catalog_product->getProduct($promo_product['product_id']);
			if ($product_info) {
				$data['promo_products'][] = array(
					'product_id' => $product_info['product_id'],
					'name'       => $product_info['name'],
					'discount'   => $promo_product['discount']
				);
			}
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('catalog/promo_form', $data));
	}

	protected function validateForm() {
		if (!$this->user->hasPermission('modify', 'catalog/promo')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		foreach ($this->request->post['promo_description'] as $language_id => $value) {
			if ((utf8_strlen($value['title']) < 3) || (utf8_strlen($value['title']) > 255)) {
				$this->error['title'][$language_id] = $this->language->get('error_title');
			}
			if ((utf8_strlen($value['meta_title']) < 3) || (utf8_strlen($value['meta_title']) > 255)) {
				$this->error['meta_title'][$language_id] = $this->language->get('error_meta_title');
			}
		}

		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	protected function validateDelete() {
		if (!$this->user->hasPermission('modify', 'catalog/promo')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}