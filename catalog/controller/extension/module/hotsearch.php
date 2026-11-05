<?php
class ControllerExtensionModuleHotSearch extends Controller {
  public function get(){

    $this->load->language('extension/module/hotsearch');

    $json = array();

    if (!isset($this->request->get['value'])) {
      $json['error'] = $this->language->get('error_empty');
    }

    if (!$json) {

      $categories_count = (int)$this->config->get('module_hotsearch_categories_count');
      $product_count = (int)$this->config->get('module_hotsearch_products_count');

      // Ищем по каталогу
      $this->load->model('extension/module/hotsearch');
      $products = $this->model_extension_module_hotsearch->getProducts(array(
        'filter_name' => $this->request->get['value'],
        'filter_tag' => $this->request->get['value'],
        'filter_description' => $this->request->get['value'],
        'sort'        => 'p.sort_order',
        'order'       => 'ASC',
        'start'       => 0,
        'limit'       => $product_count * $product_count
      ));

      $result = $this->getSearchResult($products);

      if (count($result['products']) === 0) {
        $json['error'] = $this->language->get('error_empty');
      }

    }

    if (!$json) {

      // Список товаров
      $json['products'] = $result['products'];

      // Список категорий
      $json['categories'] = $result['categories'];

      // Ссылка на стандартный поиск
      $json['all'] = array(
        'href' => '/index.php?route=product/search&search='.urlencode($this->request->get['value']),
        'title' => $this->language->get('button_allresult')
      );
    }

    $this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));

  }

  private function getSearchResult($products){

    $this->load->model('tool/image');
    $this->load->model('extension/module/hotsearch');
    $this->load->language('product/product');
    $this->load->language('extension/module/hotsearch');

    $json['products'] = array();
    $json['categories'] = array();

    // Чтобы правиьлно рассортировать товары по категориям
    $categoriesKeys = array();
    $catKey = 0;

    // Текстовые заготовки
    $text_model = $this->language->get('text_model');
    $text_sku = $this->language->get('text_sku');

    $image_width = $this->config->get('module_hotsearch_image_width');
    $image_height = $this->config->get('module_hotsearch_image_height');

    foreach($products as $product){

      // Изображение
      if ($product['image']) {
        $thumb = $this->model_tool_image->resize($product['image'], $image_width, $image_height);
      } else {
        $thumb = $this->model_tool_image->resize('placeholder.png', $image_width, $image_height);
      }

      // Цена
      if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
        $price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
      } else {
        $price = false;
      }

      // Акция
      if ((float)$product['special']) {
        $special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
      } else {
        $special = false;
      }

      // Наличие на складе
      $stock_class = 'instock';
      if ($product['quantity'] <= 0) {
				$stock = $product['stock_status'];
			  $stock_class = 'outstock';
			} elseif ($this->config->get('config_stock_display')) {
        $stock = $this->language->get('text_instock').': '.$product['quantity'].$this->language->get('text_unit');
			} else {
				$stock = $this->language->get('text_instock');
      }

      // Артикул
      if ($product['sku']) {
        $sku = $text_sku.' '.$product['sku'];
      } else {
        $sku = false;
      }

      // Модель
      if ($product['model']) {
        $model = $text_model.' '.$product['model'];
      } else {
        $model = false;
      }

      // Категории, к которым принадлежит товар
      $categories = $this->model_extension_module_hotsearch->getProductCategories($product['product_id']);
      $product_categories = array();

      // Формируем общий список категорий
      foreach($categories as $key => $category){

        $category_id = (int)$category['category_id'];

        // В список категоирй для товара
        $product_categories[] = $category_id;

        // Категория нет
        if (!isset($categoriesKeys[$category_id])) {

          $categoriesKeys[$category_id] = $catKey;
          $catKey++;

          $json['categories'][] = array(
            'name' => html_entity_decode($category['name'], ENT_QUOTES, 'UTF-8'),
            'count' => 1,
            'category_id' => (int)$category_id,
            'href' => $this->url->link('product/category', 'path='.$category_id)
          );

        // Категория есть
        } else {
          $categoryKey = $categoriesKeys[$category_id];
          $json['categories'][$categoryKey]['count']++;
        }

      }

      $json['products'][] = array(
        'product_id'  => $product['product_id'],
        'thumb'       => $thumb,
        'name'        => html_entity_decode($product['name'], ENT_QUOTES, 'UTF-8'),
        'price'       => $price,
        'special'     => $special,
        'href'        => $this->url->link('product/product', 'product_id=' . $product['product_id']),
        'model'       => $model,
        'sku'         => $sku,
        'stock'       => $stock,
        'stock_class' => $stock_class,
        'categories'  => $product_categories
      );

    }

    // Сортируем сформированные категории по количеству найденного товара
    $sort_order = array();
    foreach ($json['categories'] as $key => $value) {
      $sort_order[$key] = $value['count'];
    }
    array_multisort($sort_order, SORT_DESC, $json['categories']);

    // Добавляем общую категорию вперед батьки
    array_unshift($json['categories'], array(
      'name' => $this->language->get('text_allresult'),
      'count' => count($products),
      'category_id' => 0,
      'href' => '/'
    ));

    return $json;

  }

}

