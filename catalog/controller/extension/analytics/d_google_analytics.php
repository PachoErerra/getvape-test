<?php
class ControllerExtensionAnalyticsDGoogleAnalytics extends Controller {
	private $codename = 'd_google_analytics';
	private $route = 'extension/analytics/d_google_analytics';
	private $config_file = 'd_google_analytics';
	private $prefix = '';
	
	public function __construct($registry) {
        parent::__construct($registry);

        $this->prefix = (VERSION > '3.0.0.0') ? 'analytics_' : '';
    }
	
	public function index() {
		$setting = $this->config->get($this->prefix . $this->codename . '_setting');
					
		if (isset($this->request->get['route']) && ($this->request->get['route'] == 'product/product')) {
			if (isset($this->request->get['product_id'])) {
				$product_id = (int)$this->request->get['product_id'];
			} else {
				$product_id = 0;
			}

			$this->load->model('catalog/product');

			$product_info = $this->model_catalog_product->getProduct($product_id);
					
			if ($product_info) {
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], true), $this->session->data['currency'], '', false);
				} else {
					$price = false;
				}

				if ((float)$product_info['special']) {
					$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], true), $this->session->data['currency'], '', false);
				} else {
					$special = false;
				}
			
				$data['product'] = array(
					'product_id' 	=> $product_info['product_id'],
					'name' 			=> $product_info['name'],
					'price' 		=> (!$special) ? $price : $special,
					'currency'		=> $this->session->data['currency']
				);
				
				$data_layer = $this->load->view($this->route, $data);
				$fbq = $this->load->view($this->route . '_fbq', $data);
				
				$setting['code'] = str_replace('[data_layer]', $data_layer, $setting['code']);
				$setting['code'] = str_replace('[fbq]', $fbq, $setting['code']);
			}
		}
		
		if (isset($this->request->get['route']) && ($this->request->get['route'] == 'product/category')) {
			if (isset($this->request->get['filter'])) {
				$filter = $this->request->get['filter'];
			} else {
				$filter = '';
			}

			if (isset($this->request->get['sort'])) {
				$sort = $this->request->get['sort'];
			} else {
				$sort = 'p.sort_order';
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

			if (isset($this->request->get['limit'])) {
				$limit = (int)$this->request->get['limit'];
			} else {
				$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
			}
			
			if (isset($this->request->get['path'])) {
				$path = '';

				$parts = explode('_', (string)$this->request->get['path']);

				$category_id = (int)array_pop($parts);

				foreach ($parts as $path_id) {
					if (!$path) {
						$path = (int)$path_id;
					} else {
						$path .= '_' . (int)$path_id;
					}
				}
			} else {
				$category_id = 0;
			}
			
			$data['category'] = array(
				'price' => 0,
				'products' => array()
			);
			
			$filter_data = array(
				'filter_category_id' => $category_id,
				'filter_filter'      => $filter,
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);
			
			$results = $this->model_catalog_product->getProducts($filter_data);

			foreach ($results as $result) {
				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], true), $this->session->data['currency'], '', false);
				} else {
					$price = false;
				}

				if ((float)$result['special']) {
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], true), $this->session->data['currency'], '', false);
				} else {
					$special = false;
				}
								
				$data['category']['price'] += (!$special) ? $price : $special;
				
				$data['category']['products'][] = array(
					'product_id'  => $result['product_id']					
				);
			}
			
			$data_layer = $this->load->view($this->route, $data);
				
			$setting['code'] = str_replace('[data_layer]', $data_layer, $setting['code']);
		}
		
		if (isset($this->request->get['route']) && ($this->request->get['route'] == 'product/search')) {
			if (isset($this->request->get['search'])) {
				$search = $this->request->get['search'];
			} else {
				$search = '';
			}

			if (isset($this->request->get['tag'])) {
				$tag = $this->request->get['tag'];
			} elseif (isset($this->request->get['search'])) {
				$tag = $this->request->get['search'];
			} else {
				$tag = '';
			}

			if (isset($this->request->get['description'])) {
				$description = $this->request->get['description'];
			} else {
				$description = '';
			}

			if (isset($this->request->get['category_id'])) {
				$category_id = $this->request->get['category_id'];
			} else {
				$category_id = 0;
			}

			if (isset($this->request->get['sub_category'])) {
				$sub_category = $this->request->get['sub_category'];
			} else {
				$sub_category = '';
			}

			if (isset($this->request->get['sort'])) {
				$sort = $this->request->get['sort'];
			} else {
				$sort = 'p.sort_order';
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

			if (isset($this->request->get['limit'])) {
				$limit = (int)$this->request->get['limit'];
			} else {
				$limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
			}
			
			if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
				$data['search'] = array(
					'price' => 0,
					'products' => array()
				);
				
				$filter_data = array(
					'filter_name'         => $search,
					'filter_tag'          => $tag,
					'filter_description'  => $description,
					'filter_category_id'  => $category_id,
					'filter_sub_category' => $sub_category,
					'sort'                => $sort,
					'order'               => $order,
					'start'               => ($page - 1) * $limit,
					'limit'               => $limit
				);

				$results = $this->model_catalog_product->getProducts($filter_data);
				
				foreach ($results as $result) {
					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], true), $this->session->data['currency'], '', false);
					} else {
						$price = false;
					}

					if ((float)$result['special']) {
						$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], true), $this->session->data['currency'], '', false);
					} else {
						$special = false;
					}
								
					$data['search']['price'] += (!$special) ? $price : $special;
				
					$data['search']['products'][] = array(
						'product_id'  => $result['product_id']					
					);
				}
			
				$data_layer = $this->load->view($this->route, $data);
				
				$setting['code'] = str_replace('[data_layer]', $data_layer, $setting['code']);
			}
		}
		
		if (isset($this->request->get['route']) && ($this->request->get['route'] == 'checkout/cart')) {
			$data['checkout_cart'] = array(
				'price' => 0,
				'currency' => $this->session->data['currency'],
				'products' => array()
			);
					
			$products = $this->cart->getProducts();
					
			foreach ($products as $product) {						
				$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], true), $this->session->data['currency'], '', false);
				
				$data['checkout_cart']['price'] += $price * $product['quantity'];
				
				$data['checkout_cart']['products'][] = array(
					'product_id' => $product['product_id'],
					'name' => $product['name'],
					'quantity' => $product['quantity'],
					'price' => $price
				);
			}
			
			$data_layer = $this->load->view($this->route, $data);
			$fbq = $this->load->view($this->route . '_fbq', $data);
				
			$setting['code'] = str_replace('[data_layer]', $data_layer, $setting['code']);
			$setting['code'] = str_replace('[fbq]', $fbq, $setting['code']);
		}
		
		if (isset($this->request->get['route']) && ($this->request->get['route'] == 'checkout/simplecheckout')) {
			$data['checkout'] = array(
				'price' => 0,
				'currency' => $this->session->data['currency'],
				'products' => array()
			);
			
			$products = $this->cart->getProducts();
					
			foreach ($products as $product) {						
				$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], true), $this->session->data['currency'], '', false);
				
				$data['checkout']['price'] += $price * $product['quantity'];
				
				$data['checkout']['products'][] = array(
					'product_id' => $product['product_id'],
					'name' => $product['name'],
					'quantity' => $product['quantity'],
					'price' => $price
				);
			}
			
			$data_layer = $this->load->view($this->route, $data);
			$fbq = $this->load->view($this->route . '_fbq', $data);
				
			$setting['code'] = str_replace('[data_layer]', $data_layer, $setting['code']);
			$setting['code'] = str_replace('[fbq]', $fbq, $setting['code']);
		}
		
		if (isset($this->request->get['route']) && ($this->request->get['route'] == 'checkout/success') && isset($this->session->data[$this->codename])) {
			$data = $this->session->data[$this->codename];

			$data_layer = $this->load->view($this->route, $data);
			$fbq = $this->load->view($this->route . '_fbq', $data);
				
			$setting['code'] = str_replace('[data_layer]', $data_layer, $setting['code']);
			$setting['code'] = str_replace('[fbq]', $fbq, $setting['code']);
						
			unset($this->session->data[$this->codename]);
		}
				
		$setting['code'] = str_replace('[data_layer]', '', $setting['code']);
		$setting['code'] = str_replace('[fbq]', '', $setting['code']);
				
		return html_entity_decode($setting['code'], ENT_QUOTES, 'UTF-8');
    }
	
	public function checkout_success_before(&$route, &$args) {
		$this->load->model('checkout/order');
		$this->load->model('catalog/product');
		$this->load->model('catalog/category');
						
		if (isset($this->session->data['order_id'])) {
			$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
			if ($order_info) {
				$data['order'] = array(
					'order_id' => $this->session->data['order_id'],
					'price' => 0,
					'currency' => $this->session->data['currency'],
					'products' => array()
				);
					
				$products = $this->cart->getProducts();
					
				foreach ($products as $product) {						
					$price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], true), $this->session->data['currency'], '', false);
				
					$data['order']['price'] += $price * $product['quantity'];
				
					$data['order']['products'][] = array(
						'product_id' => $product['product_id'],
						'name' => $product['name'],
						'quantity' => $product['quantity'],
						'price' => $price
					);
				}
					
				$this->session->data[$this->codename] = $data;
			}
		}
	}
}
