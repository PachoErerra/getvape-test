<?php
class ControllerProductPromo extends Controller {
	public function info() {
		$this->load->language('product/promo');
		$this->load->model('catalog/promo');
		$this->load->model('catalog/product');
		$this->load->model('tool/image');

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
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		if (isset($this->request->get['limit'])) {
			$limit = (int)$this->request->get['limit'];
		} else {
			$limit = (int)$this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
		}

		$promo_id = isset($this->request->get['promo_id']) ? (int)$this->request->get['promo_id'] : 0;

		$promo_info = $this->model_catalog_promo->getPromo($promo_id);

		if ($promo_info) {
			
			if ($promo_info['noindex']) {
				$this->document->setRobots('noindex, follow');

				$this->response->addHeader('X-Robots-Tag: noindex, follow');
			} else {
				$this->document->setRobots('index, follow');
			}
			$this->document->setTitle($promo_info['meta_title']);
			$this->document->setDescription($promo_info['meta_description']);
			$this->document->setKeywords($promo_info['meta_keyword']);

			$data['breadcrumbs'] = array();
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);
			$data['breadcrumbs'][] = array(
				'text' => $promo_info['title'],
				'href' => $this->url->link('product/promo/info', 'promo_id=' . $promo_id)
			);

			$data['heading_title'] = $promo_info['title'];
			$data['description'] = html_entity_decode($promo_info['description'], ENT_QUOTES, 'UTF-8');
			$data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));
			$data['compare'] = $this->url->link('product/compare');

			$data['products'] = array();

			$filter_data = array(
				'filter_promo_id'    => $promo_id,
				'filter_ocfilter'    => isset($this->request->get['filter_ocfilter']) ? $this->request->get['filter_ocfilter'] : '',
				'sort'               => $sort,
				'order'              => $order,
				'start'              => ($page - 1) * $limit,
				'limit'              => $limit
			);

			$product_total = $this->model_catalog_product->getTotalProducts($filter_data);
			$results = $this->model_catalog_product->getProducts($filter_data);

			// Собираем ID и получаем % скидки
			$product_ids = array_column($results, 'product_id');
			$promo_discounts = $this->model_catalog_promo->getProductsDiscounts($promo_id, $product_ids);

			foreach ($results as $result) {
				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_product_height'));
				}

				if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
					$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$price = false;
				}

				// Расчет цены со скидкой из модуля (Переопределяет стандартный Special, если задан процент)
				$discount_percent = isset($promo_discounts[$result['product_id']]) ? $promo_discounts[$result['product_id']] : 0;
				
				if ((float)$discount_percent > 0 && $price) {
					$calculated_special = $result['price'] - ($result['price'] * ($discount_percent / 100));
					$special = $this->currency->format($this->tax->calculate($calculated_special, $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} elseif ((float)$result['special']) {
					// Фоллбэк на стандартную акцию товара
					$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
				} else {
					$special = false;
				}

				if ($this->config->get('config_tax')) {
					$tax = $this->currency->format((float)$special ? $calculated_special : $result['price'], $this->session->data['currency']);
				} else {
					$tax = false;
				}
				
				if ($this->config->get('config_review_status')) {
					$rating = (int)$result['rating'];
				} else {
					$rating = false;
				}
					$isnew = $result['isnew'];
					
					$products_related = $this->model_catalog_product->getListingVariantRelated($result); 	
					if ((float)$result['special']) {
						$economy = '-' . round((($result['price'] - $result['special'])/($result['price'] + 0.01))*100) . '%';
					} else {
						$economy = false;
					}
				$data['products'][] = array(
				'products_related' => $products_related,
				'isnew'	=> $isnew,
				'economy'       => $economy,
				'quantity'  => $result['quantity']+$result['quantity_sklad'],
				'quantity_shop'  => $result['quantity'],
					'product_id'  => $result['product_id'],
					'thumb'       => $image,
					'name'        => $result['name'],
					'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
					'price'       => $price,
					'special'     => $special,
					'tax'         => $tax,
					'minimum'     => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $result['rating'],
					'reviews'      => $result['reviews'],
					'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
				);
			}

			// ... (Здесь стандартная генерация ссылок для Сортировки и Пагинации)
			// Чтобы код контроллера не был сверхдлинным, переменные $url формируются так же, как в product/category
			
			$url = '&promo_id=' . $promo_id;
			if (isset($this->request->get['filter_ocfilter'])) { $url .= '&filter_ocfilter=' . $this->request->get['filter_ocfilter']; }

			$pagination = new Pagination();
			$pagination->total = $product_total;
			$pagination->page = $page;
			$pagination->limit = $limit;
			$pagination->url = $this->url->link('product/promo/info', $url . '&page={page}');
			$data['pagination'] = $pagination->render();
			$data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

			// URL для сортировок
			$data['sorts'] = array();
			$data['sorts'][] = array('text' => $this->language->get('text_default'), 'value' => 'p.sort_order-ASC', 'href' => $this->url->link('product/promo/info', 'promo_id=' . $promo_id . '&sort=p.sort_order&order=ASC'));
			$data['sorts'][] = array('text' => $this->language->get('text_name_asc'), 'value' => 'pd.name-ASC', 'href' => $this->url->link('product/promo/info', 'promo_id=' . $promo_id . '&sort=pd.name&order=ASC'));
			$data['sorts'][] = array('text' => $this->language->get('text_price_asc'), 'value' => 'p.price-ASC', 'href' => $this->url->link('product/promo/info', 'promo_id=' . $promo_id . '&sort=p.price&order=ASC'));
			// (добавьте остальные нужные сортировки аналогично)

			$data['sort'] = $sort;
			$data['order'] = $order;
			$data['limit'] = $limit;

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('product/promo', $data));
		} else {
			// Логика 404 (копия стандартной)
			$this->load->language('error/not_found');
			$this->document->setTitle($this->language->get('text_error'));
			$data['heading_title'] = $this->language->get('text_error');
			$data['text_error'] = $this->language->get('text_error');
			$data['button_continue'] = $this->language->get('button_continue');
			$data['continue'] = $this->url->link('common/home');
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
			$data['header'] = $this->load->controller('common/header');
			$data['footer'] = $this->load->controller('common/footer');
			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}
	
	
	private function sort_function($key){
		return function ($a, $b) use ($key) {
			return !empty($a[$key]) && !empty($b[$key]) ? strnatcmp($a[$key], $b[$key]) : false;
		};
	}
}