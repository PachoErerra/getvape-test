<?php
class ControllerExtensionFeedSetcachecategory extends Controller {
	public function index() {
		
		$file = DIR_LOGS . 'cache.header.categories' . (int)$this->config->get('config_language_id');
		
		$cache_categories = json_decode(@file_get_contents($file), true);

		if ($cache_categories) {

			$data['categories'] = $cache_categories;

		} else {

			// Menu
            $this->load->model('catalog/category');

            $this->load->model('catalog/product');

            $data['categories'] = array();

            $categories = $this->model_catalog_category->getCategories(0);

            foreach ($categories as $key=>$category) {
                if ($category['top']) {
                    // Level 2
                    /*
                    $children_data = array();

                    $children = $this->model_catalog_category->getCategories($category['category_id']);

                    foreach ($children as $child) {


                        $children_data[] = array(
                            'name'  => $child['name'] . ($this->config->get('config_product_count') ? ' (' . $this->model_catalog_product->getTotalProducts($filter_data) . ')' : ''),
                            'href'  => $this->url->link('product/category', 'path=' . $category['category_id'] . '_' . $child['category_id'])
                        );
                    }
                    */
                    $filter_data = array(
                        'filter_category_id' => $category['category_id'],
                        'filter_sub_category' => true
                    );
                    // Level 1

                    //проверка что б не выводить пустые категории
                    if (!$this->model_catalog_product->categoryHasProducts($category['category_id'])) {
                        continue;
                    }

                    $category_name = $category['name'];
                    if ($this->config->get('config_product_count')) {
                        $category_name .= ' <span>(' . $this->model_catalog_product->getTotalProducts($filter_data) . ')</span>';
                    }

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
                    $data['categories'][] = array(
                        'category_id' => $category['category_id'],
                        'name' => $category_name,
                        //	'children' => $children_data,
                        'column' => $category['column'] ? $category['column'] : 1,
					//	'image' => $category['image'] ? file_get_contents($this->model_tool_image->resize($category['image'], 16, 17)) : false,
                        'image' => $category['image'] ? $this->model_tool_image->resize($category['image'], 48, 48) : false,
                        'href' => $this->url->link('product/category', 'path=' . $category['category_id'])
                    );
                }
            }

		//	$this->cache->set('header.categories' . (int)$this->config->get('config_language_id'), $data['categories']);
			
			file_put_contents($file, json_encode($data['categories']));
//			echo 'Меню оновлено';
		}
	}
}