<?php

class ControllerProductbf extends Controller
{
    public function index()
    {
        $this->load->language('product/bf');
        $this->document->addScript('catalog/view/theme/default/flipclockmaster/js/flip-clock.js');
        $this->document->addStyle('catalog/view/theme/default/flipclockmaster/css/flip-clock.css?v=1.3.8');
        $this->load->model('catalog/product');

        $this->load->model('tool/image');

        $data['bfstart'] = $this->config->get('config_bfstart');
        $data['bfend'] = $this->config->get('config_bfend');
		$data['bfmenu'] = $this->config->get('config_bfmenu');
        $today = date_create(date('Y-m-d H:i'));
        $start_date = date_create($this->config->get('config_bfstart'));
        $end_date = date_create($this->config->get('config_bfend'));

        if ($today > $start_date && $today < $end_date) {
            //			echo 111;
        }
		
		if (($start_date < $today) && $data['bfmenu']) {
			
	// 		$this->response->redirect($this->url->link('product/special', '', true));
		}
		
		$data['redirect'] = ($this->url->link('product/special', '', true));
		
$this->document->setRobots('noindex,follow');
        if ($this->config->get('config_noindex_disallow_params')) {
            $params = explode("\r\n", $this->config->get('config_noindex_disallow_params'));
            if (!empty($params)) {
                $disallow_params = $params;
            }
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
            if (!in_array('sort', $disallow_params, true) && $this->config->get('config_noindex_status')) {
                $this->document->setRobots('noindex,follow');
            }
        } else {
            $sort = 'default';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
            if (!in_array('order', $disallow_params, true) && $this->config->get('config_noindex_status')) {
                $this->document->setRobots('noindex,follow');
            }
        } else {
            $order = 'ASC';
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
            if (!in_array('page', $disallow_params, true) && $this->config->get('config_noindex_status')) {
                $this->document->setRobots('noindex,follow');
            }
        } else {
            $page = 1;
        }

        if (isset($this->request->get['limit'])) {
            $limit = (int)$this->request->get['limit'];
            if (!in_array('limit', $disallow_params, true) && $this->config->get('config_noindex_status')) {
                $this->document->setRobots('noindex,follow');
            }
        } else {
            $limit = $this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit');
        }

//		$this->document->setTitle($this->language->get('heading_title'));
$data['heading_title'] = $this->language->get('heading_title');
        $this->document->setTitle($this->language->get('title'));
        $this->document->setDescription($this->language->get('description'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

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

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('product/bf', $url)
        );

        $data['text_compare'] = sprintf($this->language->get('text_compare'), (isset($this->session->data['compare']) ? count($this->session->data['compare']) : 0));

        $data['compare'] = $this->url->link('product/compare');

        $data['products'] = array();

        $filter_data = array(
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $limit,
            'limit' => $limit
        );

        $product_total = $this->model_catalog_product->getTotalProductSpecialsBF();

        $results = $this->model_catalog_product->getProductSpecialsBF($filter_data);

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
                $rating = (int)$result['rating'];
            } else {
                $rating = false;
            }
            /*
            $today = date_create(date('Y-m-d H:i:s'));
            $added_date = date_create($result['date_added']);
            $interval = date_diff($today, $added_date);
            if ($interval->days <= 30) {
                $isnew = true;
            } else {
                $isnew = false;
            }
            */
            $isnew = $result['isnew'];
					$products_related = $this->model_catalog_product->getListingVariantRelated($result); 
            if ((float)$result['special']) {
                $economy = '-' . round((($result['price'] - $result['special']) / ($result['price'] + 0.01)) * 100) . '%';
            } else {
                $economy = false;
            }


            $data['products'][] = array(
                'products_related' => $products_related,
                'isnew' => $isnew,
                'economy' => $economy,
                'product_id' => $result['product_id'],
                'quantity' => $result['quantity'] + $result['quantity_sklad'],
                'quantity_shop' => $result['quantity'],
                'thumb' => $image,
                'name' => $result['name'],
				'reward'  => $result['reward'],
                'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                'price' => $price,
                'special' => $special,
                'tax' => $tax,
                'minimum' => $result['minimum'] > 0 ? $result['minimum'] : 1,
                'rating' => $result['rating'],
                'reviews'      => $result['reviews'],
                'href' => $this->url->link('product/product', 'product_id=' . $result['product_id'] . $url)
            );
        }
        // TODO: Добавить магазины на страницу ЧП
        $this->load->model('localisation/location');
        $data['warehouses'] = $this->model_localisation_location->getWarehouses();

        // === РЕЗАЙЗ КАРТИНОК СКЛАДОВ (только по ширине, высота пропорционально) ===
        $this->load->model('tool/image');

        $large_w = 500;  // ширина большой версии

// безопасный ресайз по ширине с расчётом высоты
        $resizeByWidth = function($filename, $target_w) {
            // нормализуем относительный путь (без ведущего /)
            $filename = ltrim((string)$filename, '/');
            if (!$filename || !is_file(DIR_IMAGE . $filename)) {
                $filename = 'placeholder.png';
            }

            $full = DIR_IMAGE . $filename;
            $size = @getimagesize($full);

            if (!$size) {
                // если что-то совсем не так — уйдём на плейсхолдер
                $filename = 'placeholder.png';
                $full = DIR_IMAGE . $filename;
                $size = @getimagesize($full);
                if (!$size) {
                    // последняя защита от падения
                    return $this->model_tool_image->resize($filename, (int)$target_w, (int)$target_w);
                }
            }

            list($orig_w, $orig_h) = $size;
            if ($orig_w <= 0) $orig_w = 1;
            $calc_h = (int)round($target_w * $orig_h / $orig_w);
            if ($calc_h < 1) $calc_h = 1;

            return $this->model_tool_image->resize($filename, (int)$target_w, (int)$calc_h);
        };

// поддержим как плоский массив, так и сгруппированный (по городам)
        $processItem = function (&$item) use ($resizeByWidth, $large_w) {
            $img = isset($item['image']) ? $item['image'] : '';
            $item['image_original'] = ltrim((string)$img, '/');
            $item['thumb'] = $resizeByWidth($item['image_original'], $large_w);
        };

        if (is_array($data['warehouses'])) {
            // если это сгруппировано по ключам-строкам (например, городам) — пройдёмся глубже
            $is_grouped = false;
            foreach ($data['warehouses'] as $k => $v) {
                if ($k !== 'count' && is_array($v) && isset($v[0]) && is_array($v[0]) && array_key_exists('image', $v[0])) {
                    $is_grouped = true;
                    break;
                }
            }

            if ($is_grouped) {
                foreach ($data['warehouses'] as $group_key => &$group) {
                    if ($group_key === 'count') continue;
                    if (!is_array($group)) continue;
                    foreach ($group as &$wh) {
                        if (is_array($wh)) $processItem($wh);
                    }
                    unset($wh);
                }
                unset($group);
            } else {
                // обычный плоский массив
                foreach ($data['warehouses'] as &$wh) {
                    if (is_array($wh)) $processItem($wh);
                }
                unset($wh);
            }
        }
// === /РЕЗАЙЗ КАРТИНОК СКЛАДОВ ===


        foreach((array)$this->config->get('config_location') as $location_id) {
            $location_info = $this->model_localisation_location->getLocation($location_id);

            if ($location_info) {
                if ($location_info['image']) {
                    $image = $this->model_tool_image->resize($location_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_location_height'));
                } else {
                    $image = false;
                }

                $data['locations'][] = array(
                    'location_id' => $location_info['location_id'],
                    'name'        => $location_info['name'],
                    'address'     => nl2br( $location_info['address']),
                    'geocode'     => $location_info['geocode'],
                    'telephone'   => $location_info['telephone'],
                    'fax'         => $location_info['fax'],
                    'image'       => $image,
                    'open'        => nl2br($location_info['open']),
                    'openBF'        => nl2br($location_info['openBF']),
                    'comment'     => $location_info['comment']
                );
            }
        }
        // end

        $url = '';

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }


        $data['sorts'] = array();

        $data['sorts'][] = array(
            'text' => $this->language->get('text_default'),
            'value' => 'default-ASC',
            'href' => $this->url->link('product/bf', 'sort=default&order=ASC' . $url)
        );

        $data['sorts'][] = array(
            'text' => $this->language->get('text_percent_desc'),
            'value' => 'special_percent-DESC',
            'href' => $this->url->link('product/bf', 'sort=special_percent&order=DESC' . $url)
        );

        $data['sorts'][] = array(
            'text' => $this->language->get('text_name_asc'),
            'value' => 'pd.name-ASC',
            'href' => $this->url->link('product/bf', 'sort=pd.name&order=ASC' . $url)
        );

        $data['sorts'][] = array(
            'text' => $this->language->get('text_name_desc'),
            'value' => 'pd.name-DESC',
            'href' => $this->url->link('product/bf', 'sort=pd.name&order=DESC' . $url)
        );

        $data['sorts'][] = array(
            'text' => $this->language->get('text_price_asc'),
            'value' => 'ps.price-ASC',
            'href' => $this->url->link('product/bf', 'sort=ps.price&order=ASC' . $url)
        );

        $data['sorts'][] = array(
            'text' => $this->language->get('text_price_desc'),
            'value' => 'ps.price-DESC',
            'href' => $this->url->link('product/bf', 'sort=ps.price&order=DESC' . $url)
        );

        if ($this->config->get('config_review_status')) {
            $data['sorts'][] = array(
                'text' => $this->language->get('text_rating_desc'),
                'value' => 'rating-DESC',
                'href' => $this->url->link('product/bf', 'sort=rating&order=DESC' . $url)
            );

            $data['sorts'][] = array(
                'text' => $this->language->get('text_rating_asc'),
                'value' => 'rating-ASC',
                'href' => $this->url->link('product/bf', 'sort=rating&order=ASC' . $url)
            );
        }

        $data['sorts'][] = array(
            'text' => $this->language->get('text_model_asc'),
            'value' => 'p.model-ASC',
            'href' => $this->url->link('product/bf', 'sort=p.model&order=ASC' . $url)
        );

        $data['sorts'][] = array(
            'text' => $this->language->get('text_model_desc'),
            'value' => 'p.model-DESC',
            'href' => $this->url->link('product/bf', 'sort=p.model&order=DESC' . $url)
        );


        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $data['limits'] = array();

        $limits = array_unique(array($this->config->get('theme_' . $this->config->get('config_theme') . '_product_limit'), 25, 50, 75, 100));

        sort($limits);

        foreach ($limits as $value) {
            $data['limits'][] = array(
                'text' => $value,
                'value' => $value,
                'href' => $this->url->link('product/bf', $url . '&limit=' . $value)
            );
        }

        $url = '';

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['limit'])) {
            $url .= '&limit=' . $this->request->get['limit'];
        }

        $pagination = new Pagination();
        $pagination->total = $product_total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('product/bf', $url . '&page={page}');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($product_total - $limit)) ? $product_total : ((($page - 1) * $limit) + $limit), $product_total, ceil($product_total / $limit));

        if (!$this->config->get('config_canonical_method')) {
            // http://googlewebmastercentral.blogspot.com/2011/09/pagination-with-relnext-and-relprev.html
            if ($page == 1) {
                $this->document->addLink($this->url->link('product/bf', ''), 'canonical');
            } elseif ($page == 2) {
                $this->document->addLink($this->url->link('product/bf', ''), 'prev');
            } else {
                $this->document->addLink($this->url->link('product/bf', 'page=' . ($page - 1)), 'prev');
            }

            if ($limit && ceil($product_total / $limit) > $page) {
                $this->document->addLink($this->url->link('product/bf', 'page=' . ($page + 1)), 'next');
            }
        } else {
            if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
                $server = $this->config->get('config_ssl');
            } else {
                $server = $this->config->get('config_url');
            };

            $request_url = rtrim($server, '/') . $this->request->server['REQUEST_URI'];
            $canonical_url = $this->url->link('product/bf', '');


            if (($request_url != $canonical_url) || $this->config->get('config_canonical_self')) {
                //       $this->document->addLink($canonical_url, 'canonical');
            }
            if ($page == 1) {
                $this->document->addLink($this->url->link('product/bf', ''), 'canonical');
            } else {
                $this->document->addLink($this->url->link('product/bf', '&page=' . $page), 'canonical');
            }
            if ($this->config->get('config_add_prevnext')) {

                if ($page == 2) {
                    $this->document->addLink($this->url->link('product/bf', ''), 'prev');
                } elseif ($page > 2) {
                    $this->document->addLink($this->url->link('product/bf', 'page=' . ($page - 1)), 'prev');
                }

                if ($limit && ceil($product_total / $limit) > $page) {
                    $this->document->addLink($this->url->link('product/bf', 'page=' . ($page + 1)), 'next');
                }
            }
        }

        $data['sort'] = $sort;
        $data['order'] = $order;
        $data['limit'] = $limit;

        $data['continue'] = $this->url->link('common/home');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('product/bf', $data));
    }

    private function sort_function($key)
    {
        return function ($a, $b) use ($key) {
            return !empty($a[$key]) && !empty($b[$key]) ? strnatcmp($a[$key], $b[$key]) : false;
        };
    }
}
