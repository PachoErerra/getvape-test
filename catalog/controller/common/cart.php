<?php

class ControllerCommonCart extends Controller
{
    public function index()
    {
        $this->load->language('common/cart');

        $this->load->model('extension/module/vapors_punch_gift');
        $this->model_extension_module_vapors_punch_gift->sync();

        // Totals
        $this->load->model('setting/extension');

        $totals = array();
        $taxes = $this->cart->getTaxes();
        $total = 0;

        // Because __call can not keep var references so we put them into an array.
        $total_data = array(
            'totals' => &$totals,
            'taxes' => &$taxes,
            'total' => &$total
        );

        // Display prices
        if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
            $sort_order = array();

            $results = $this->model_setting_extension->getExtensions('total');

            foreach ($results as $key => $value) {
                $sort_order[$key] = $this->config->get('total_' . $value['code'] . '_sort_order');
            }

            array_multisort($sort_order, SORT_ASC, $results);

            foreach ($results as $result) {
                if ($this->config->get('total_' . $result['code'] . '_status')) {
                    $this->load->model('extension/total/' . $result['code']);

                    // We have to put the totals in an array so that they pass by reference.
                    $this->{'model_extension_total_' . $result['code']}->getTotal($total_data);
                }
            }

            $sort_order = array();

            foreach ($totals as $key => $value) {
                $sort_order[$key] = $value['sort_order'];
            }

            array_multisort($sort_order, SORT_ASC, $totals);
        }

        $data['text_items'] = sprintf($this->language->get('text_items'), $this->cart->countProducts() + (isset($this->session->data['vouchers']) ? count($this->session->data['vouchers']) : 0), $this->currency->format($total, $this->session->data['currency']));

        $this->load->model('tool/image');
        $this->load->model('tool/upload');
        $this->load->model('catalog/product');

        $vapors_punch_active = $this->model_extension_module_vapors_punch_gift->isActive();
        $vapors_punch_gift_ids = $vapors_punch_active
            ? $this->model_extension_module_vapors_punch_gift->getGiftCartIds()
            : array();

        $vapors_punch_gifts = $vapors_punch_active
            ? $this->model_extension_module_vapors_punch_gift->getCartGiftRows(!empty($this->session->data['vapors_punch_gift_invalid']))
            : array();
        $vapors_punch_gifts_by_parent = $vapors_punch_active
            ? $this->model_extension_module_vapors_punch_gift->groupGiftRowsByParent($vapors_punch_gifts)
            : array();

        $data['vapors_punch_gifts'] = $vapors_punch_gifts;
        $data['vapors_punch_gift_invalid'] = !empty($this->session->data['vapors_punch_gift_invalid']);
        unset($this->session->data['vapors_punch_gift_invalid']);

        $data['products'] = array();
		$relateds = array();
        $cart_products = array();

        $data['total_reward'] = 0;
        $data['total_special'] = 0;

        $cart_items = $this->cart->getProducts();

        foreach ($cart_items as $product) {
            if (in_array((int)$product['cart_id'], $vapors_punch_gift_ids, true)) {
                continue;
            }

            if ($product['image']) {
                $image = $this->model_tool_image->resize($product['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_cart_height'));
            }

            $option_data = array();

            foreach ($product['option'] as $option) {
                if ($option['type'] != 'file') {
                    $value = $option['value'];
                } else {
                    $upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

                    if ($upload_info) {
                        $value = $upload_info['name'];
                    } else {
                        $value = '';
                    }
                }

                $option_data[] = array(
                    'name' => $option['name'],
                    'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value),
                    'type' => $option['type']
                );
            }

            // Display prices
            if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                $unit_price = $this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax'));

                $price = $this->currency->format($unit_price, $this->session->data['currency']);
                $total = $this->currency->format($unit_price * $product['quantity'], $this->session->data['currency']);
            } else {
                $price = false;
                $total = false;
            }

            $product_info = $this->model_catalog_product->getProduct($product['product_id']);

            $max_quantity = 99;
            if ($product_info) {
                // Online orders only from the 4 warehouses (product.quantity)
                $stock = (int)$product_info['quantity'];
                if (!empty($product_info['subtract']) && $stock > 0) {
                    $max_quantity = min($max_quantity, $stock);
                }
            }
            if ($max_quantity < 1) {
                $max_quantity = 1;
            }

            if (isset($product_info['special']) && !is_null($product_info['special']) && (float)$product_info['special'] > 0) {
                $special = $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                $special_int = round(($product_info['price'] - $product_info['special']),0);
            } else {
                $special = false;
                $special_int = 0;
            }

            $cart_products[] = $product['product_id'];

            $parent_cart_id = (int)$product['cart_id'];

            $data['products'][] = array(
                'cart_id' => $product['cart_id'],
                'thumb' => $image,
                'name' => $product['name'],
                'model' => $product['model'],
				'gift_teaser' => isset($product['gift_teaser']) ? $product['gift_teaser'] : false,
                'option' => $option_data,
                'recurring' => ($product['recurring'] ? $product['recurring']['name'] : ''),
                'quantity' => $product['quantity'],
                'max_quantity' => $max_quantity,
                'price' => $price,
                'special' => $special,
                'reward' => ($product['reward'] ? '<b style="font-weight: 600;">+' . $product['reward'] . ' ₴</b>' : ''),
                'total' => $total,
                'href' => $this->url->link('product/product', 'product_id=' . $product['product_id']),
                'product_id' => $product['product_id'],
                'vapors_punch_gifts' => isset($vapors_punch_gifts_by_parent[$parent_cart_id]) ? $vapors_punch_gifts_by_parent[$parent_cart_id] : array()
            );
            $data['total_reward'] = $data['total_reward'] + $product['reward'];
            $data['total_special'] = $data['total_special'] + ($special_int * $product['quantity']);
			
			if ($product['relateds']) {
				$relateds[] = $product['relateds'];
			}
        }

		$file = DIR_LOGS . 'sol.products';
		$data['sol_products'] = json_decode(file_get_contents($file), true);
		
        // Gift Voucher
        $data['vouchers'] = array();

        if (!empty($this->session->data['vouchers'])) {
            foreach ($this->session->data['vouchers'] as $key => $voucher) {
                $data['vouchers'][] = array(
                    'key' => $key,
                    'description' => $voucher['description'],
                    'amount' => $this->currency->format($voucher['amount'], $this->session->data['currency'])
                );
            }
        }

        $data['totals'] = array();
        $data['totalcart'] = false;
        foreach ($totals as $total) {
            if ($total['code'] == 'total') {
                $data['totalcart'] = $total['value'];
            }
            $data['totals'][] = array(
                'title' => $total['title'],
                'code' => $total['code'],
                'text' => $this->currency->format($total['value'], $this->session->data['currency']),
            );
        }
        if (isset($this->session->data['free_cartrige_add']) && $this->session->data['free_cartrige_add']) {
            $data['free_cartrige_add'] = 'Додайте ще один товар, <span>щоб виграний картридж став безкоштовним.</span>';
        } else {
            $data['free_cartrige_add'] = false;
        }

        if (isset($this->session->data['add1']) && $this->session->data['add1']) {
            $data['add1'] = 'Додайте ще одну рідину, <span>щоб одна с них стала безкоштовною.</span>';
        } else {
            $data['add1'] = false;
        }

        if (isset($this->session->data['add2']) && $this->session->data['add2']) {
            $data['add2'] = 'Додайте ще дві рідини, <span>щоб одна с них стала безкоштовною.</span>';
        } else {
            $data['add2'] = false;
        }

        if (isset($this->session->data['shipping_coupon']) && $this->session->data['shipping_coupon']) {
            $data['shipping_coupon'] = 'Безкоштовна доставка за промокодом.';
        } else {
            $data['shipping_coupon'] = false;
        }
		
		if (isset($this->session->data['coupon_gift_addmore']) && $this->session->data['coupon_gift_addmore']) {
			$data['coupon_gift_addmore'] = $this->session->data['coupon_gift_addmore'];
		} else {
			$data['coupon_gift_addmore'] = false;
		}
		
        $data['featured_products'] = array();

        if ($this->customer->isLogged() && $cart_items) {
            $data['featured_products'] = $this->flattenDontForgetProducts(
                $this->load->controller('checkout/simplecheckout_dont_forget_to_add', $cart_items)
            );
        }

        if (count($data['featured_products']) < 2) {
            $data['featured_products'] = $this->mergeFeaturedProducts(
                $data['featured_products'],
                $this->getFallbackFeaturedProducts($cart_products, $relateds)
            );
        }
        $data['cart'] = $this->url->link('checkout/cart');
        $data['checkout'] = $this->url->link('checkout/checkout', '', true);

        $data['sprite_version'] = $this->registry->get('sprite_version');
        $data['free_post'] = (float)$this->config->get('config_free_post');
        $data['free_boy'] = (float)$this->config->get('config_free_boy');
        $data['error_quantity_max'] = $this->language->get('error_quantity_max');
        $data['error_quantity_stock'] = $this->language->get('error_quantity_stock');

        //HappyHOur
        $data['hh_freedelivery'] = false;
        if ($this->config->get('config_hh_freedelivery_on')) {
            $today = date_create(date('Y-m-d H:i'));
            $start_hh_date = date_create($this->config->get('config_hh_freedelivery_start'));
            $end_hh_date = date_create($this->config->get('config_hh_freedelivery_end'));
            if ($today >= $start_hh_date && $today <= $end_hh_date) {
                $data['hh_freedelivery'] = true;
            }
        }
        //HappyHOur

        $goods_amount = 0;
        foreach ($totals as $total_row) {
            if (in_array($total_row['code'], array('shipping', 'total', 'credit'), true)) {
                continue;
            }
            $goods_amount += (float)$total_row['value'];
        }
        $cart_total = round($goods_amount);
        $free_post = $data['free_post'];
        $free_boy = $data['free_boy'] > 0 ? $data['free_boy'] : 1;
        $reached_post = $data['hh_freedelivery'] || $cart_total >= $free_post;
        $reached_boy = $data['hh_freedelivery'] || $cart_total >= $free_boy;

        $data['delivery_reached_post'] = $reached_post;
        $data['delivery_reached_boy'] = $reached_boy;
        $data['delivery_progress'] = $data['hh_freedelivery']
            ? 100
            : min(100, round(($cart_total / $free_boy) * 100, 3));
        $data['delivery_mid_percent'] = min(100, round(($free_post / $free_boy) * 100, 3));
        $data['delivery_amount_current'] = (int)round($cart_total);

        if ($reached_boy) {
            $data['delivery_title'] = $this->language->get('text_delivery_title_boy');
            $data['delivery_amount_target'] = (int)round($free_boy);
            $data['delivery_hint'] = $this->language->get('text_delivery_hint_done');
        } elseif ($reached_post) {
            $data['delivery_title'] = $this->language->get('text_delivery_title_post');
            $data['delivery_amount_target'] = (int)round($free_boy);
            $need = max(0, (int)round($free_boy - $cart_total));
            $data['delivery_hint'] = sprintf($this->language->get('text_delivery_hint_boy'), $need);
        } else {
            $data['delivery_title'] = $this->language->get('text_delivery_title_start');
            $data['delivery_amount_target'] = (int)round($free_post);
            $need = max(0, (int)round($free_post - $cart_total));
            $data['delivery_hint'] = sprintf($this->language->get('text_delivery_hint_post'), $need);
        }

        return $this->load->view('common/cart', $data);
    }

    public function info()
    {
        $this->response->setOutput($this->index());
    }

    private function flattenDontForgetProducts($groups)
    {
        $products = array();

        if (!is_array($groups) || !$groups) {
            return $products;
        }

        foreach ($groups as $group) {
            if (!is_array($group)) {
                continue;
            }

            foreach ($group as $product) {
                if (!empty($product['product_id'])) {
                    $products[] = $product;
                }
            }
        }

        return $products;
    }

    private function mergeFeaturedProducts($primary, $extra)
    {
        $products = is_array($primary) ? $primary : array();
        $seen = array();

        foreach ($products as $product) {
            if (!empty($product['product_id'])) {
                $seen[(int)$product['product_id']] = true;
            }
        }

        if (!is_array($extra)) {
            return $products;
        }

        foreach ($extra as $product) {
            $product_id = !empty($product['product_id']) ? (int)$product['product_id'] : 0;

            if (!$product_id || isset($seen[$product_id])) {
                continue;
            }

            $seen[$product_id] = true;
            $products[] = $product;
        }

        return $products;
    }

    private function getFallbackFeaturedProducts($cart_products, $relateds)
    {
        $this->load->model('setting/module');
        $setting_info = $this->model_setting_module->getModule(53);

        $featured_products = array();

        if (empty($setting_info['product'])) {
            return $featured_products;
        }

        if (empty($setting_info['limit'])) {
            $setting_info['limit'] = 4;
        }

        $new_cart = array();
        foreach ($setting_info['product'] as $cart_product) {
            if (!in_array($cart_product, $cart_products)) {
                $new_cart[] = $cart_product;
            }
        }

        $products = array_slice(array_unique($new_cart), 0, (int)$setting_info['limit']);

        if (!empty($relateds)) {
            $total = 12;
            $productsr = array();
            $added = 0;
            $index = 0;

            while ($added < $total) {
                $addedSomething = false;
                foreach ($relateds as $arr) {
                    if ($added >= $total) {
                        break;
                    }
                    if (isset($arr[$index]) && !in_array($arr[$index], $productsr, true)) {
                        $productsr[] = $arr[$index];
                        $added++;
                        $addedSomething = true;
                    }
                }
                if (!$addedSomething) {
                    break;
                }
                $index++;
            }

            $products = $productsr;
        }

        foreach ($products as $product_id) {
            $product_info = $this->model_catalog_product->getProduct($product_id);

            if (!$product_info) {
                continue;
            }

            if ($product_info['image']) {
                $image = $this->model_tool_image->resize($product_info['image'], $setting_info['width'], $setting_info['height']);
            } else {
                $image = $this->model_tool_image->resize('placeholder.png', $setting_info['width'], $setting_info['height']);
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

            $featured_products[] = array(
                'product_id' => $product_info['product_id'],
                'thumb' => $image,
                'name' => $product_info['name'],
                'quantity' => $product_info['quantity'] + $product_info['quantity_sklad'],
                'quantity_shop' => $product_info['quantity'],
                'description' => utf8_substr(strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..',
                'price' => $price,
                'special' => $special,
                'manufacturer' => $product_info['manufacturer'],
                'tax' => $tax,
                'rating' => $this->config->get('config_review_status') ? $product_info['rating'] : false,
                'href' => $this->url->link('product/product', 'product_id=' . $product_info['product_id'])
            );
        }

        return $featured_products;
    }
}