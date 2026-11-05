<?php
class ControllerExtensionModuleFeatured extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/featured');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');
		
		$data['heading_title'] = $setting['name'];
		$data['heading_link'] = !empty($setting['heading_link']) ? trim($setting['heading_link']) : '';

		$data['products'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}
	
		$data['show_button'] = false;
		if ($setting['name'] == 'Home page') {
			$data['show_button'] = true;
			$data['category_url'] = $this->url->link('product/category', 'path=1526');
		}
		if (!empty($setting['product'])) {
			foreach ($setting['product'] as $product_id) {
				if (count($data['products']) >= (int)$setting['limit']) {
					break;
				}

				$product_info = $this->model_catalog_product->getProduct($product_id);

				if ($product_info && $this->model_catalog_product->isListingVisible($product_info) && ((int)$product_info['quantity'] + (int)$product_info['quantity_sklad']) > 0) {
					if ($product_info['image']) {
						$image = $this->model_tool_image->resize($product_info['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
						$special = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						$tax_price = (float)$product_info['special'];
					} else {
						$special = false;
						$tax_price = (float)$product_info['price'];
					}
		
					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format($tax_price, $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $product_info['rating'];
					} else {
						$rating = false;
					}
					/*
					$today = date_create(date('Y-m-d H:i:s'));
					$added_date = date_create($product_info['date_added']);
					$interval = date_diff($today, $added_date);
					if ($interval->days <= 30) {
						$isnew = true;
					} else {
						$isnew = false;
					}
					*/
					$isnew = $product_info['isnew'];
					
							$products_related = $this->model_catalog_product->getListingVariantRelated($product_info);
					if ((float)$product_info['special']) {
						$economy = '-' . round((($product_info['price'] - $product_info['special'])/($product_info['price'] + 0.01))*100) . '%';
					} else {
						$economy = false;
					}
					$data['products'][] = array(
						'products_related' => $products_related,
						'isnew'	=> $isnew,
						'economy'       => $economy,
						'product_id'  => $product_info['product_id'],
						'thumb'       => $image,
						'name'        => $product_info['name'],
						'reward'  => $product_info['reward'],
						'quantity'  => $product_info['quantity']+$product_info['quantity_sklad'],
						'quantity_shop'  => $product_info['quantity'],
						'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'manufacturer'       => $product_info['manufacturer'],
						'tax'         => $tax,
						'rating'      => $rating,
                        'reviews'      => $product_info['reviews'], //TODO: поправить
						'href'        => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
					);
				}
			}
		}

		if ($data['products']) {
			return $this->load->view('extension/module/featured', $data);
		}
	}
	
	private function sort_function($key){
		return function ($a, $b) use ($key) {
			return !empty($a[$key]) && !empty($b[$key]) ? strnatcmp($a[$key], $b[$key]) : false;
		};
	}
}