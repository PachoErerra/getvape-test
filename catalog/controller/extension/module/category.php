<?php
class ControllerExtensionModuleCategory extends Controller {
	public function index() {
		$this->load->language('extension/module/category');

		if (isset($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
		} else {
			$parts = array();
		}

		if (isset($parts[0])) {
			$data['category_id'] = $parts[0];
		} else {
			$data['category_id'] = 0;
		}

		if (isset($parts[1])) {
			$data['child_id'] = $parts[1];
		} else {
			$data['child_id'] = 0;
		}
        $cache_categories = $this->cache->get('left.categories' . (int)$this->config->get('config_language_id'));

        if ($cache_categories) {

            $data['categories'] = $cache_categories;

        } else {
			$this->load->model('catalog/category');

			$this->load->model('catalog/product');
			
			$this->load->model('tool/image');

			$data['categories'] = array();

			$categories = $this->model_catalog_category->getCategories(0);

			foreach ($categories as $key=>$category) {
				/*
				$children_data = array();

				if ($category['category_id'] == $data['category_id']) {
					$children = $this->model_catalog_category->getCategories($category['category_id']);

					foreach($children as $child) {
						$filter_data = array('filter_category_id' => $child['category_id'], 'filter_sub_category' => true);

						$children_data[] = array(
							'category_id' => $child['category_id'],
							'name' => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
							'href' => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
						);
					}
				}
				*/
				$filter_data = array(
					'filter_category_id'  => $category['category_id'],
					'filter_sub_category' => true
				);

				//проверка что б не выводить пустые категории
				$total_products = $this->model_catalog_product->getTotalProducts($filter_data);

if ($key==1) {
					$data['categories'][] = array(
						'category_id' => $category['category_id'],
						'name' => 'Знижки' . ($this->config->get('config_product_count') ? ' <span>(' . $this->model_catalog_product->getTotalProductSpecials() . ')</span>' : ''),
						//	'children'    => $children_data,
					//    'image' => $category['image'] ? file_get_contents($this->model_tool_image->resize($category['image'], 16, 17)) : false,
						'image' => $this->model_tool_image->resize('catalog/menu/salel-vapors.png', 48, 48),
						'href' => $this->url->link('product/special')
					);
}
	

				if ($total_products > 0) {
					$data['categories'][] = array(
						'category_id' => $category['category_id'],
						'name' => $category['name'] . ($this->config->get('config_product_count') ? ' <span>(' . $this->model_catalog_product->getTotalProducts($filter_data) . ')</span>' : ''),
						//	'children'    => $children_data,
					//    'image' => $category['image'] ? file_get_contents($this->model_tool_image->resize($category['image'], 16, 17)) : false,
						'image' => $category['image'] ? $this->model_tool_image->resize($category['image'], 48, 48) : false,
						'href' => $this->url->link('product/category', 'path=' . $category['category_id'])
					);
				}
			}
            $this->cache->set('left.categories' . (int)$this->config->get('config_language_id'), $data['categories']);

        }		
        if (!empty($data['categories'])) {
            $this->load->model('catalog/category');
            enrich_menu_categories($data['categories'], $this->url, $this->model_catalog_category);
        }

		$data['super_button_link'] = $this->config->get('config_super_button_link');
		$data['super_button_text'] = $this->config->get('config_super_button_text');

		return $this->load->view('extension/module/category', $data);
	}
}