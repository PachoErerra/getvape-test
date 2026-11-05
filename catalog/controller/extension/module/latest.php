<?php
class ControllerExtensionModuleLatest extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/latest');

		$this->load->model('catalog/product');

		$this->load->model('tool/image');

		$data['heading_link'] = !empty($setting['heading_link']) ? trim($setting['heading_link']) : '';

		$data['products'] = array();
		
		$data['show_button'] = false;
		if ($setting['name'] == 'Новинки') {
			$data['show_button'] = true;
			$data['category_url'] = $this->url->link('product/latest', '');
			$data['heading_link'] = $data['category_url'];
		}

		$cache_products = $this->cache->get('module.latest.img.vis' . (int)$this->config->get('config_language_id'));
        $results = $this->cache->get('module.latest.results.img.vis' . (int)$this->config->get('config_language_id'));

        if ($cache_products) {

            $data['products'] = $cache_products;

        } else {
		
		$results = $this->model_catalog_product->getLatestProductsNew($setting['limit']);

			if ($results) {
				foreach ($results as $result) {
					if (empty($result['image'])) {
						continue;
					}

					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);

					if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
						$price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
					} else {
						$price = false;
					}

					if (!is_null($result['special']) && (float)$result['special'] >= 0) {
						$special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
						$tax_price = (float)$result['special'];
					} else {
						$special = false;
						$tax_price = (float)$result['price'];
					}
		
					if ($this->config->get('config_tax')) {
						$tax = $this->currency->format($tax_price, $this->session->data['currency']);
					} else {
						$tax = false;
					}

					if ($this->config->get('config_review_status')) {
						$rating = $result['rating'];
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
						'product_id'  => $result['product_id'],
						'thumb'       => $image,
						'name'        => $result['name'],
						'reward'  => $result['reward'],
						'quantity'  => $result['quantity']+$result['quantity_sklad'],
						'quantity_shop'  => $result['quantity'],
						'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
						'price'       => $price,
						'special'     => $special,
						'tax'         => $tax,
						'rating'      => $rating,
						'reviews'      => $result['reviews'],
						'href'        => $this->url->link('product/product', 'product_id=' . $result['product_id'])
					);
				}
				
				$this->cache->set('module.latest.img.vis' . (int)$this->config->get('config_language_id'), $data['products']);
				
			}
			$this->cache->set('module.latest.results.img.vis' . (int)$this->config->get('config_language_id'), $results);

		}
		$data['remarketing_code'] = '';
	  // remarketing all in one  
	      $this->load->model('tool/remarketing');
	      if ($this->config->get('remarketing_status') && !$this->model_tool_remarketing->isBot() && !empty($results)) {
		  	  if (empty($data['heading_title'])) $data['heading_title'] = $this->language->get('heading_title'); 
		  	  $data = array_merge($data, $this->model_tool_remarketing->processCategory((!empty($category_info) ? $category_info : []), $setting['name'], $results));
	      }  

		return $this->load->view('extension/module/featured', $data);		
	}
	
	private function sort_function($key){
		return function ($a, $b) use ($key) {
			return !empty($a[$key]) && !empty($b[$key]) ? strnatcmp($a[$key], $b[$key]) : false;
		};
	}
}
