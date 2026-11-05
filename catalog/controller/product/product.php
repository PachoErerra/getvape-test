<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerProductProduct extends Controller
{
    private $error = array();

    public function index()
    {

        $this->load->language('product/product');

        $data['telegram'] = $this->config->get('config_telegram');

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $this->load->model('catalog/category');

        if ($this->customer->isLogged()) {
            $data['logged'] = true;
        } else {
            $data['logged'] = false;
        }

        if (isset($this->request->get['path'])) {
            $path = '';

            $parts = explode('_', (string)$this->request->get['path']);

            $category_id = (int)array_pop($parts);

            $data['showpink'] = false;
            $pink_caterories = array(1528, 2407);
            if (in_array($category_id, $pink_caterories)) {
                $data['showpink'] = true;
            }
            foreach ($parts as $path_id) {
                if (in_array($path_id, $pink_caterories)) {
                    $data['showpink'] = true;
                }
                if (!$path) {
                    $path = $path_id;
                } else {
                    $path .= '_' . $path_id;
                }

                $category_info = $this->model_catalog_category->getCategory($path_id);

                if ($category_info) {
                    $data['breadcrumbs'][] = array(
                        'text' => $category_info['name'],
                        'href' => $this->url->link('product/category', 'path=' . $path)
                    );
                }
            }

            // Set the last category breadcrumb
            $category_info = $this->model_catalog_category->getCategory($category_id);

            if ($category_info) {
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
                    'text' => $category_info['name'],
                    'href' => $this->url->link('product/category', 'path=' . $this->request->get['path'] . $url)
                );
            }
        }

        $this->load->model('catalog/manufacturer');

        if (isset($this->request->get['manufacturer_id'])) {
            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_brand'),
                'href' => $this->url->link('product/manufacturer')
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

            $manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($this->request->get['manufacturer_id']);

            if ($manufacturer_info) {
                $data['breadcrumbs'][] = array(
                    'text' => $manufacturer_info['name'],
                    'href' => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $this->request->get['manufacturer_id'] . $url)
                );
            }
        }

        if (isset($this->request->get['search']) || isset($this->request->get['tag'])) {
            $url = '';

            if (isset($this->request->get['search'])) {
                $url .= '&search=' . $this->request->get['search'];
            }

            if (isset($this->request->get['tag'])) {
                $url .= '&tag=' . $this->request->get['tag'];
            }

            if (isset($this->request->get['description'])) {
                $url .= '&description=' . $this->request->get['description'];
            }

            if (isset($this->request->get['category_id'])) {
                $url .= '&category_id=' . $this->request->get['category_id'];
            }

            if (isset($this->request->get['sub_category'])) {
                $url .= '&sub_category=' . $this->request->get['sub_category'];
            }

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
                'text' => $this->language->get('text_search'),
                'href' => $this->url->link('product/search', $url)
            );
        }

        if (isset($this->request->get['product_id'])) {
            $product_id = (int)$this->request->get['product_id'];
        } else {
            $product_id = 0;
        }

        $this->load->model('catalog/product');

        $product_info = $this->model_catalog_product->getProduct($product_id);

        if ($product_info) {

            $products_category_best = $product_info['relateds'];
            $buster_products = $product_info['buster_products'];
            //	shuffle($products_category_best);

            $this->load->model('tool/image');

            $data['variants'] = $this->prepareProductVariantGroups($product_id, $product_info);

            $url = '';

            if (isset($this->request->get['path'])) {
                $url .= '&path=' . $this->request->get['path'];
            }

            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
            }

            if (isset($this->request->get['manufacturer_id'])) {
                $url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
            }

            if (isset($this->request->get['search'])) {
                $url .= '&search=' . $this->request->get['search'];
            }

            if (isset($this->request->get['tag'])) {
                $url .= '&tag=' . $this->request->get['tag'];
            }

            if (isset($this->request->get['description'])) {
                $url .= '&description=' . $this->request->get['description'];
            }

            if (isset($this->request->get['category_id'])) {
                $url .= '&category_id=' . $this->request->get['category_id'];
            }

            if (isset($this->request->get['sub_category'])) {
                $url .= '&sub_category=' . $this->request->get['sub_category'];
            }

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
                'text' => $product_info['name'],
                'href' => $this->url->link('product/product', $url . '&product_id=' . $this->request->get['product_id'])
            );

            if ($product_info['meta_title']) {
                $this->document->setTitle($product_info['meta_title']);
            } else {
                $this->document->setTitle($product_info['name']);
            }

            if ($product_info['noindex'] <= 0 && $this->config->get('config_noindex_status')) {
                $this->document->setRobots('noindex,follow');
            }

            if ($product_info['meta_h1']) {
                $data['heading_title'] = $product_info['meta_h1'];
            } else {
                $data['heading_title'] = $product_info['name'];
            }

            $this->document->setDescription($product_info['meta_description']);
            $this->document->setKeywords($product_info['meta_keyword']);
            $this->document->addLink($this->url->link('product/product', 'product_id=' . $this->request->get['product_id']), 'canonical');
            //	$this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
            //	$this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
            //	$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
            //	$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
            //	$this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
            //	$this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

            $data['text_minimum'] = sprintf($this->language->get('text_minimum'), $product_info['minimum']);
            $data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));

            $this->load->model('catalog/review');

            $data['tab_review'] = sprintf($this->language->get('tab_review'), $product_info['reviews']);

            $data['product_id'] = (int)$this->request->get['product_id'];
            $data['manufacturer'] = $product_info['manufacturer'];
            $data['manufacturers'] = $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $product_info['manufacturer_id']);
            $data['model'] = $product_info['model'];
            $data['reward'] = $product_info['reward'];
            $data['points'] = $product_info['points'];
            $data['description'] = str_replace('src=', ' loading="lazy" src=', html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'));
            $data['description'] = preg_replace('/<iframe(.*?)>(.*?)<\/iframe>/', '<div class="product-video-block"><iframe$1>$2</iframe></div>', $data['description']);;


            $data['complect'] = str_replace('src=', ' loading="lazy" src=', html_entity_decode($product_info['description2'], ENT_QUOTES, 'UTF-8'));

            if ($product_info['quantity'] <= 0) {
                $data['stock'] = $product_info['stock_status'];
                $data['stock_label'] = 'out_of_stock';
            } elseif ($this->config->get('config_stock_display')) {
                $data['stock'] = $product_info['quantity'];
                $data['stock_label'] = 'in_stock';
            } elseif ($product_info['quantity'] < 5) {
                $data['stock'] = 'закінчується';
                $data['stock_label'] = 'in_stock';
            } else {
                $data['stock'] = $this->language->get('text_instock');
                $data['stock_label'] = 'in_stock';
            }

            $data['quantity'] = $product_info['quantity'];

            $this->load->model('tool/image');


            if ($product_info['image']) {
                $data['popup'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
                $this->document->setOgImage($data['popup']);
            } else {
                $data['popup'] = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height'));
            }

            if ($product_info['image']) {
                $data['thumb'] = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
            } else {
                $data['thumb'] = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_thumb_height'));
            }


            $data['images'] = array();

            $results = $this->model_catalog_product->getProductImages($this->request->get['product_id']);

            foreach ($results as $result) {
                $data['images'][] = array(
                    'popup' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_popup_height')),
                    'thumb' => $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_additional_height'))
                );
            }

            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $data['price'] = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
            } else {
                $data['price'] = false;
            }

            if (!is_null($product_info['special']) && (float)$product_info['special'] >= 0) {
                $data['special'] = $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                $tax_price = (float)$product_info['special'];
            } else {
                $data['special'] = false;
                $tax_price = (float)$product_info['price'];
            }

            if ((float)$product_info['special']) {
                $data['economy'] = '-' . round((($product_info['price'] - $product_info['special']) / ($product_info['price'] + 0.01)) * 100) . '%';
            } else {
                $data['economy'] = false;
            }

            if ($this->config->get('config_tax')) {
                $data['tax'] = $this->currency->format($tax_price, $this->session->data['currency']);
            } else {
                $data['tax'] = false;
            }

            $discounts = $this->model_catalog_product->getProductDiscounts($this->request->get['product_id']);

            $data['discounts'] = array();

            foreach ($discounts as $discount) {
                $data['discounts'][] = array(
                    'quantity' => $discount['quantity'],
                    'price' => $this->currency->format($this->tax->calculate($discount['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency'])
                );
            }

            $data['options'] = array();

            foreach ($this->model_catalog_product->getProductOptions($this->request->get['product_id']) as $option) {
                $product_option_value_data = array();

                foreach ($option['product_option_value'] as $option_value) {
                    if (!$option_value['subtract'] || ($option_value['quantity'] > 0)) {
                        if ((($this->config->get('config_customer_price') && $this->customer->isLogged()) || !$this->config->get('config_customer_price')) && (float)$option_value['price']) {
                            $price = $this->currency->format($this->tax->calculate($option_value['price'], $product_info['tax_class_id'], $this->config->get('config_tax') ? 'P' : false), $this->session->data['currency']);
                        } else {
                            $price = false;
                        }

                        $product_option_value_data[] = array(
                            'product_option_value_id' => $option_value['product_option_value_id'],
                            'option_value_id' => $option_value['option_value_id'],
                            'name' => $option_value['name'],
                            'apo_reward' => $option_value['reward'],
                            'image' => $this->model_tool_image->resize($option_value['image'], 96, 96),
                            'price' => $price,
                            'price_prefix' => $option_value['price_prefix'],
                            'reward_prefix' => $option_value['points_prefix'],
                        );
                    }
                }

                $data['options'][] = array(
                    'product_option_id' => $option['product_option_id'],
                    'product_option_value' => $product_option_value_data,
                    'option_id' => $option['option_id'],
                    'name' => $option['name'],
                    'type' => $option['type'],
                    'value' => $option['value'],
                    'required' => $option['required']
                );
            }

            if ($product_info['minimum']) {
                $data['minimum'] = $product_info['minimum'];
            } else {
                $data['minimum'] = 1;
            }

            $data['review_status'] = $this->config->get('config_review_status');

            if ($this->config->get('config_review_guest') || $this->customer->isLogged()) {
                $data['review_guest'] = true;
            } else {
                $data['review_guest'] = false;
            }

            if ($this->customer->isLogged()) {
                $data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
            } else {
                $data['customer_name'] = '';
            }

            $data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$product_info['reviews']);
            $data['rating'] = (int)$product_info['rating'];

            // Captcha
            if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
                $data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
            } else {
                $data['captcha'] = '';
            }

            $data['share'] = $this->url->link('product/product', 'product_id=' . (int)$this->request->get['product_id']);

            $data['attribute_groups'] = $this->model_catalog_product->getProductAttributes($this->request->get['product_id']);

            if (is_liquid_line_category($this->model_catalog_product->getCategoriesId($this->request->get['product_id']))) {
                $line_label = get_liquid_line_label($this->config->get('config_language'));

                foreach ($data['attribute_groups'] as &$attribute_group) {
                    if (empty($attribute_group['attribute'])) {
                        continue;
                    }

                    foreach ($attribute_group['attribute'] as &$attribute) {
                        if ($attribute['name'] === 'Модель') {
                            $attribute['name'] = $line_label;
                        }
                    }
                    unset($attribute);
                }
                unset($attribute_group);
            }

            $data['warehouses'] = $this->model_catalog_product->getWarehouses($this->request->get['product_id']);


            // === РЕЗАЙЗ КАРТИНОК СКЛАДОВ (только по ширине, высота пропорционально) ===
            $this->load->model('tool/image');

            $large_w = 500;  // ширина большой версии

            // безопасный ресайз по ширине с расчётом высоты
            $resizeByWidth = function ($filename, $target_w) {
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


            if ($product_info['product_mrp']) {
                $data['modules'] = array();
                foreach ($product_info['product_mrp'] as $module) {


                    $title = false;
                    $products = array();

                    if ($module['product_mrp_description'][$this->config->get('config_language_id')]['title']) {
                        $title = $module['product_mrp_description'][$this->config->get('config_language_id')]['title'];
                    }

                    foreach ($module['product_mrp_product'] as $mrp_product) {
                        $product_info = $this->model_catalog_product->getProduct($mrp_product['related_id']);

                        if ($product_info) {
                            if ($product_info['image']) {
                                $image = $this->model_tool_image->resize($product_info['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
                            } else {
                                $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
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

                            $today = date_create(date('Y-m-d H:i:s'));
                            $added_date = date_create($product_info['date_added']);
                            $interval = date_diff($today, $added_date);
                            if ($interval->days <= 30) {
                                $isnew = true;
                            } else {
                                $isnew = false;
                            }
                            $products_related = $this->model_catalog_product->getListingVariantRelated($product_info);
                            $products[] = array(
                                'products_related' => $products_related,
                                'isnew' => $isnew,
                                'product_id' => $product_info['product_id'],
                                'thumb' => $image,
                                'name' => $product_info['name'],
                                'reward' => $product_info['reward'],
                                'quantity' => $product_info['quantity'] + $product_info['quantity_sklad'],
                                'quantity_shop' => $product_info['quantity'],
                                'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                                'price' => $price,
                                'special' => $special,
                                'manufacturer' => $product_info['manufacturer'],
                                'tax' => $tax,
                                'rating' => $rating,
                                'href' => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
                            );
                        }
                    }
                    $data['modules'][] = array(
                        'title' => $title,
                        'products' => $products


                    );

                }
            }

            $data['products'] = array();

            $results = $this->model_catalog_product->getProductRelated($this->request->get['product_id']);

            foreach ($results as $result) {
                if ($result['image']) {
                    $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
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

                $today = date_create(date('Y-m-d H:i:s'));
                $added_date = date_create($result['date_added']);
                $interval = date_diff($today, $added_date);
                if ($interval->days <= 30) {
                    $isnew = true;
                } else {
                    $isnew = false;
                }

                $products_related = $this->model_catalog_product->getListingVariantRelated($result);


                $data['products'][] = array(
                    'products_related' => $products_related,
                    'isnew' => $isnew,
                    'product_id' => $result['product_id'],
                    'thumb' => $image,
                    'name' => $result['name'],
                    'reward' => $result['reward'],
                    'quantity' => $result['quantity'] + $result['quantity_sklad'],
                    'quantity_shop' => $result['quantity'],
                    'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                    'price' => $price,
                    'special' => $special,
                    'manufacturer' => $result['manufacturer'],
                    'tax' => $tax,
                    'minimum' => $result['minimum'] > 0 ? $result['minimum'] : 1,
                    'rating' => $rating,
                    'reviews' => $result['reviews'],
                    'href' => $this->url->link('product/product', 'product_id=' . $result['product_id'])
                );
            }

//рекомендуемые из 1с
            $data['products_related_1c'] = array();

            $results = $this->model_catalog_product->getProductRelated1C($this->request->get['product_id']);

            foreach ($results as $result) {
                if ($result['image']) {
                    $image = $this->model_tool_image->resize($result['image'], 96, 96);
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', 96, 96);
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

                $data['products_related_1c'][] = array(
                    'product_id' => $result['product_id'],
                    'thumb' => $image,
                    'name' => $result['name'],
                    'quantity' => $result['quantity'] + $result['quantity_sklad'],
                    'quantity_shop' => $result['quantity'],
                    'price' => $price,
                    'special' => $special,
                    'minimum' => $result['minimum'] > 0 ? $result['minimum'] : 1,
                    'href' => $this->url->link('product/product', 'product_id=' . $result['product_id'])
                );
            }

//лучшие из категорий
            $data['products_category_best'] = array();

            if ($products_category_best && is_array($products_category_best)) {
                foreach ($products_category_best as $result_id) {

                    $result = $this->model_catalog_product->getProduct($result_id);
                    if (is_array($result) && $result['status'] && $this->model_catalog_product->isListingVisible($result)) {
                        if ($result['image']) {
                            $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
                        } else {
                            $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_related_height'));
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

                        $today = date_create(date('Y-m-d H:i:s'));
                        $added_date = date_create($result['date_added']);
                        $interval = date_diff($today, $added_date);
                        if ($interval->days <= 30) {
                            $isnew = true;
                        } else {
                            $isnew = false;
                        }
                        $products_related = $this->model_catalog_product->getListingVariantRelated($result);
                        $data['products_category_best'][] = array(
                            'products_related' => $products_related,
                            'isnew' => $isnew,
                            'product_id' => $result['product_id'],
                            'thumb' => $image,
                            'name' => $result['name'],
                            'reward' => $result['reward'],
                            'quantity' => $result['quantity'] + $result['quantity_sklad'],
                            'quantity_shop' => $result['quantity'],
                            'description' => utf8_substr(trim(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                            'price' => $price,
                            'special' => $special,
                            'manufacturer' => $result['manufacturer'],
                            'tax' => $tax,
                            'minimum' => $result['minimum'] > 0 ? $result['minimum'] : 1,
                            'rating' => $rating,
                            'reviews' => $result['reviews'],
                            'href' => $this->url->link('product/product', 'product_id=' . $result['product_id'])
                        );
                    }
                }
            }
//лучшие из категорий

            $data['buster_products'] = array();

            if ($buster_products) {
                foreach ($buster_products as $result_id) {

                    $result = $this->model_catalog_product->getProduct($result_id);

                    if (!$result || !$this->model_catalog_product->isListingVisible($result)) {
                        continue;
                    }

                    if ($result['image']) {
                        $image = $this->model_tool_image->resize($result['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
                    } else {
                        $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
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

                    $data['buster_products'][] = array(
                        'product_id' => $result['product_id'],
                        'product_option_value_id' => $result['product_id'],
                        'image' => $image,
                        'name' => $result['name'],
                        'apo_reward' => $result['reward'],
                        'quantity' => $result['quantity'] + $result['quantity_sklad'],
                        'quantity_shop' => $result['quantity'],
                        'price' => $price,
                        'special' => $special,
                        'apo_points_value' => $result['points'],
                        'apo_price_value' => ($result['special'] ? $result['special'] : $result['price']),
                        'price_prefix' => '+',
                        'href' => $this->url->link('product/product', 'product_id=' . $result['product_id'])
                    );
                }
            }

            $data['tags'] = array();
            /*
                        if ($product_info['tag']) {
                            $tags = explode(',', $product_info['tag']);

                            foreach ($tags as $tag) {
                                $data['tags'][] = array(
                                    'tag'  => trim($tag),
                                    'href' => $this->url->link('product/search', 'tag=' . trim($tag))
                                );
                            }
                        }
            */
            $data['recurrings'] = $this->model_catalog_product->getProfiles($this->request->get['product_id']);

            $this->model_catalog_product->updateViewed($this->request->get['product_id']);

            $data['reviews_data'] = $this->load->controller('product/product/review', $this->request->get['product_id']);

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('product/product', $data));
        } else {
            $url = '';

            if (isset($this->request->get['path'])) {
                $url .= '&path=' . $this->request->get['path'];
            }

            if (isset($this->request->get['filter'])) {
                $url .= '&filter=' . $this->request->get['filter'];
            }

            if (isset($this->request->get['manufacturer_id'])) {
                $url .= '&manufacturer_id=' . $this->request->get['manufacturer_id'];
            }

            if (isset($this->request->get['search'])) {
                $url .= '&search=' . $this->request->get['search'];
            }

            if (isset($this->request->get['tag'])) {
                $url .= '&tag=' . $this->request->get['tag'];
            }

            if (isset($this->request->get['description'])) {
                $url .= '&description=' . $this->request->get['description'];
            }

            if (isset($this->request->get['category_id'])) {
                $url .= '&category_id=' . $this->request->get['category_id'];
            }

            if (isset($this->request->get['sub_category'])) {
                $url .= '&sub_category=' . $this->request->get['sub_category'];
            }

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
                'text' => $this->language->get('text_error'),
                'href' => $this->url->link('product/product', $url . '&product_id=' . $product_id)
            );

            $this->document->setTitle($this->language->get('text_error'));

            $data['continue'] = $this->url->link('common/home');

            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
            $this->load->model('setting/module');
            $setting_info = $this->model_setting_module->getModule(28);
            $data['featured'] = str_replace("\n", '', $this->load->controller('extension/module/featured', $setting_info));
            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }

    public function review($product_id = false) {

        $defaultview = true;
        if ($product_id) {
            $defaultview = false;
        }

        if (isset($this->request->get['product_id']) && $this->request->get['product_id']) {
            $product_id = $this->request->get['product_id'];
        }

        $this->load->language('product/product');
        $this->load->language('product/review');

        $this->load->model('catalog/review');

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['reviews'] = array();

        $review_total = $this->model_catalog_review->getTotalReviewsByProductId($product_id);

        $results = $this->model_catalog_review->getReviewsByProductId($product_id, ($page - 1) * 5, 5);

        foreach ($results as $result) {
            $data['reviews'][] = array(
                'author'     => $result['author'],
                'text'       => nl2br($result['text']),
                'answer'       => nl2br($result['answer']),
                'rating'     => (int)$result['rating'],
                'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
            );
        }

        $pagination = new Pagination();
        $pagination->total = $review_total;
        $pagination->page = $page;
        $pagination->limit = 5;
        $pagination->url = $this->url->link('product/product/review', 'product_id=' . $this->request->get['product_id'] . '&page={page}');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));

        if ($defaultview) {
            $this->response->setOutput($this->load->view('product/review', $data));
        } else {
            return $this->load->view('product/review', $data);
        }
    }

    public function write()
    {
        $this->load->language('product/product');

        $json = array();

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
                $json['error'] = $this->language->get('error_name');
            }

            if (($this->request->post['rating'] < 4) && ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000))) {
                $json['error'] = $this->language->get('error_text');
            }

            if (empty($this->request->post['rating']) || $this->request->post['rating'] < 0 || $this->request->post['rating'] > 5) {
                $json['error'] = $this->language->get('error_rating');
            }

            // Captcha
            if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
                $captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

                if ($captcha) {
                    $json['error'] = $captcha;
                }
            }

            if (!isset($json['error'])) {
                $this->load->model('catalog/review');

                $this->model_catalog_review->addReview($this->request->get['product_id'], $this->request->post);

                $json['success'] = $this->language->get('text_success');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function notify()
    {
        $this->load->language('product/product');

        $this->load->model('catalog/product');

        $product_info = $this->model_catalog_product->getProduct($this->request->get['product_id']);

        $product_guid = $this->model_catalog_product->getProductguid($this->request->get['product_id']);

        $json = array();


        $mail = new Mail($this->config->get('config_mail_engine'));
        $mail->parameter = $this->config->get('config_mail_parameter');
        $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
        $mail->smtp_username = $this->config->get('config_mail_smtp_username');
        $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
        $mail->smtp_port = $this->config->get('config_mail_smtp_port');
        $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

        $mail->setTo($this->config->get('config_email'));
        $mail->setFrom($this->config->get('config_email'));
        $mail->setReplyTo($this->config->get('config_email'));
        $mail->setSender(html_entity_decode($this->config->get('config_owner'), ENT_QUOTES, 'UTF-8'));
        $mail->setSubject(html_entity_decode('Повідомити коли з’явиться ' . $product_info['name']));
        $mail->setText('Повідомити коли з’явиться ' . $product_info['name'] . ' за телефоном ' . $this->customer->getTelephone() . ' GUID ' . $product_guid);
        $mail->send();

        $json['success'] = true;


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function getRecurringDescription()
    {
        $this->load->language('product/product');
        $this->load->model('catalog/product');

        if (isset($this->request->post['product_id'])) {
            $product_id = $this->request->post['product_id'];
        } else {
            $product_id = 0;
        }

        if (isset($this->request->post['recurring_id'])) {
            $recurring_id = $this->request->post['recurring_id'];
        } else {
            $recurring_id = 0;
        }

        if (isset($this->request->post['quantity'])) {
            $quantity = $this->request->post['quantity'];
        } else {
            $quantity = 1;
        }

        $product_info = $this->model_catalog_product->getProduct($product_id);

        $recurring_info = $this->model_catalog_product->getProfile($product_id, $recurring_id);

        $json = array();

        if ($product_info && $recurring_info) {
            if (!$json) {
                $frequencies = array(
                    'day' => $this->language->get('text_day'),
                    'week' => $this->language->get('text_week'),
                    'semi_month' => $this->language->get('text_semi_month'),
                    'month' => $this->language->get('text_month'),
                    'year' => $this->language->get('text_year'),
                );

                if ($recurring_info['trial_status'] == 1) {
                    $price = $this->currency->format($this->tax->calculate($recurring_info['trial_price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    $trial_text = sprintf($this->language->get('text_trial_description'), $price, $recurring_info['trial_cycle'], $frequencies[$recurring_info['trial_frequency']], $recurring_info['trial_duration']) . ' ';
                } else {
                    $trial_text = '';
                }

                $price = $this->currency->format($this->tax->calculate($recurring_info['price'] * $quantity, $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);

                if ($recurring_info['duration']) {
                    $text = $trial_text . sprintf($this->language->get('text_payment_description'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
                } else {
                    $text = $trial_text . sprintf($this->language->get('text_payment_cancel'), $price, $recurring_info['cycle'], $frequencies[$recurring_info['frequency']], $recurring_info['duration']);
                }

                $json['success'] = $text;
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function prepareProductVariantGroups($product_id, $product_info)
    {
        $groups = $this->model_catalog_product->getProductVariantGroups((int)$product_id);

        if (!$groups) {
            $groups = $this->getLegacyProductVariantGroups($product_info);
        }

        $prepared = array();

        foreach ($groups as $group) {
            if (empty($group['items']) || count($group['items']) <= 1) {
                continue;
            }

            $items = array();

            foreach ($group['items'] as $item) {
                $thumb = !empty($item['image']) ? $item['image'] : 'placeholder.png';
                $items[] = array(
                    'name' => $item['name'],
                    'thumb' => $this->model_tool_image->resize($thumb, 96, 96),
                    'product_id' => (int)$item['product_id'],
                    'href' => $this->url->link('product/product', 'product_id=' . (int)$item['product_id']),
                    'selected' => (int)$item['product_id'] === (int)$product_id,
                    'in_stock' => !empty($item['in_stock']),
                    'alt' => !empty($item['alt']) ? $item['alt'] : $item['name'],
                    'title' => !empty($item['title']) ? $item['title'] : $item['name']
                );
            }

            $prepared[] = array(
                'key' => $group['label'],
                'display' => !empty($group['display']) ? $group['display'] : 'flavor',
                'items' => $items
            );
        }

        return $prepared;
    }

    private function getLegacyProductVariantGroups($product_info)
    {
        if (empty($product_info['main_category']) || empty($product_info['variants'][(int)$this->config->get('config_language_id')])) {
            return array();
        }

        $groups = array();
        $variants = $product_info['variants'][(int)$this->config->get('config_language_id')];

        foreach ($variants as $name => $type) {
            $items = array();

            foreach ($type as $key => $item) {
                $items[] = array(
                    'product_id' => (int)$key,
                    'name' => $item['name'],
                    'image' => $this->model_catalog_product->getProductimage($key),
                    'in_stock' => true,
                    'alt' => $item['name'],
                    'title' => $item['name']
                );
            }

            if (count($items) <= 1) {
                continue;
            }

            $groups[] = array(
                'label' => $name,
                'display' => 'flavor',
                'items' => $items
            );
        }

        return $groups;
    }

    private function sort_function($key)
    {
        return function ($a, $b) use ($key) {
            return !empty($a[$key]) && !empty($b[$key]) ? strnatcmp($a[$key], $b[$key]) : false;
        };
    }
}
