<?php

class ModelExtensionTotalCustomSpecials extends Model
{
    public function getTotal($total)
    {

        $cart_products = $this->cart->getProducts();

        if ($this->cart->countProducts() > 1) {


            $liquids_enabled = ((int)$this->config->get('total_custom_specials_liquids_status') === 1);
            $pods_enabled = ((int)$this->config->get('total_custom_specials_pods_status') === 1);
            $liquid_and_pod_enabled = ((int)$this->config->get('total_custom_specials_liquid_and_pod_status') === 1);

            if (!isset($total) || !is_array($total)) {
                $total = array();
            }
            if (!isset($total['totals']) || !is_array($total['totals'])) {
                $total['totals'] = array();
            }

            $add_items = function (&$map, $name, $qty = 1) {
                $name = trim((string)$name);
                if ($name === '') $name = 'Без назви';
                if (!isset($map[$name])) $map[$name] = 0;
                $map[$name] += (int)$qty;
            };

            $items_to_string = function ($map) {
                if (empty($map)) return '';
                arsort($map);
                $parts = array();
                foreach ($map as $name => $qty) {
                    $parts[] = $name . ' ×' . $qty . '. ';
                }
                return implode(', ', $parts);
            };

            $fmt_money = function ($v) {
                return number_format((float)$v, 2, '.', '');
            };

            $sol_products = array();
            $org_products = array();
            $pod_products = array();

            $need_sol = ($liquids_enabled || $liquid_and_pod_enabled);
            $need_org = ($liquids_enabled);
            $need_pod = ($pods_enabled || $liquid_and_pod_enabled);

            if ($need_sol) {
                $sol = 3013;
                $sol_products = $this->getProducts($sol);
            }
            if ($need_org) {
                $org = 2810;
                $org_products = $this->getProducts($org);
            }
            if ($need_pod) {
                $pod = 1122;
                $pod_products = $this->getProducts($pod);
            }

            if (!$need_sol && !$need_org && !$need_pod) {
                return;
            }


            $discount = 0.0;
            $applied_actions = array(); // элементы: ['title','details','items','amount']


            $pod_model_by_product = array();
            $pod_ids_in_cart = array();

            if ($need_pod) {
                foreach ($cart_products as $p) {
                    $pid = (int)$p['product_id'];
                    if (in_array($pid, $pod_products)) {
                        $pod_ids_in_cart[$pid] = $pid;
                    }
                }

                if (!empty($pod_ids_in_cart)) {
                    $language_id = (int)$this->config->get('config_language_id');

                    $sql = "SELECT product_id, text
							FROM " . DB_PREFIX . "product_attribute
							WHERE attribute_id = 37
							  AND language_id = " . $language_id . "
							  AND product_id IN (" . implode(',', $pod_ids_in_cart) . ")";

                    $query = $this->db->query($sql);

                    foreach ($query->rows as $row) {
                        $pod_model_by_product[(int)$row['product_id']] = trim((string)$row['text']);
                    }
                }
            }

            $sol_units = array(); // ['price','name','used']
            $org_units = array(); // ['price','name']
            $pod_units = array(); // ['price','name','model','used']

            foreach ($cart_products as $product) {
                $pid = (int)$product['product_id'];
                $qty = (int)$product['quantity'];
                $price = (float)$product['price'];
                $name = isset($product['name']) ? (string)$product['name'] : ('product_id=' . $pid);

                if ($qty <= 0) continue;

                // SOL (солевые жижи)
                if ($need_sol && in_array($pid, $sol_products)) {
                    for ($i = 0; $i < $qty; $i++) {
                        $sol_units[] = array('price' => $price, 'name' => $name, 'used' => false);
                    }
                }

                // ORG (органика)
                if ($need_org && in_array($pid, $org_products)) {
                    for ($i = 0; $i < $qty; $i++) {
                        $org_units[] = array('price' => $price, 'name' => $name);
                    }
                }

                // POD
                if ($need_pod && in_array($pid, $pod_products)) {
                    $model = isset($pod_model_by_product[$pid]) ? $pod_model_by_product[$pid] : '';
                    if ($model === '') {
                        $model = '__NO_MODEL__:' . $pid;
                    }
                    for ($i = 0; $i < $qty; $i++) {
                        $pod_units[] = array('price' => $price, 'name' => $name, 'model' => $model, 'used' => false);
                    }
                }
            }

            $pod_count = count($pod_units);
            $sol_count = count($sol_units);

            if ($liquid_and_pod_enabled && $sol_count > 0) {

                if ($pod_count > 0 && $sol_count <= $pod_count) {

                    $pairs = $sol_count;

                    $action_discount = 0.0;
                    $items_sol = array();
                    $items_pod = array();

                    for ($i = 0; $i < $pairs; $i++) {
                        $sol_units[$i]['used'] = true;

                        $d = $sol_units[$i]['price'] * 0.30;
                        $discount += $d;
                        $action_discount += $d;

                        $add_items($items_sol, $sol_units[$i]['name'], 1);
                    }

                    for ($i = 0; $i < $pairs; $i++) {
                        $pod_units[$i]['used'] = true;
                        $add_items($items_pod, $pod_units[$i]['name'], 1);
                    }

                    $applied_actions[] = array(
                        'title'   => 'Акція «Pod + Набір». ',
                        'details' => '<b>Умови:</b> при купівлі pod-системи разом із набором ароматизаторів. ',
                        'items'   => trim(
                            $items_to_string($items_pod) .
                            ($items_pod && $items_sol ? ', ' : '') .
                            $items_to_string($items_sol)
                        ),
                        'amount'  => $action_discount
                    );



                } else {

                    $pairs = intdiv($sol_count, 2);
                    $take = $pairs * 2;

                    if ($pairs > 0) {
                        $action_discount = 0.0;
                        $items_sol = array();

                        for ($i = 0; $i < $take; $i++) {
                            $sol_units[$i]['used'] = true;

                            $d = $sol_units[$i]['price'] * 0.15;
                            $discount += $d;
                            $action_discount += $d;

                            $add_items($items_sol, $sol_units[$i]['name'], 1);
                        }

                        $applied_actions[] = array(
                            'title' => 'Акція «Набір + Набір». ',
                            'details' => '<b>Умови:</b> при купівлі двох наборів ароматизаторів з однієї категорії. ',
                            'items' => $items_to_string($items_sol),
                            'amount' => $action_discount
                        );

                    }
                }
            }

            if ($liquids_enabled) {
                $sol_left_units = array();
                foreach ($sol_units as $u) {
                    if (!$u['used']) $sol_left_units[] = $u;
                }

                $sol_left_pairs = intdiv(count($sol_left_units), 2);
                if ($sol_left_pairs > 0) {
                    $take = $sol_left_pairs * 2;

                    $action_discount = 0.0;
                    $items_sol = array();

                    for ($i = 0; $i < $take; $i++) {
                        $d = $sol_left_units[$i]['price'] * 0.15;
                        $discount += $d;
                        $action_discount += $d;

                        $add_items($items_sol, $sol_left_units[$i]['name'], 1);
                    }

                    $applied_actions[] = array(
                        'title' => 'Акція «Набір + Набір». ',
                        'details' => '<b>Умови:</b> при купівлі двох наборів ароматизаторів з однієї категорії. ',
                        'items' => $items_to_string($items_sol),
                        'amount' => $action_discount
                    );

                }
            }

            if ($liquids_enabled) {
                $org_pairs = intdiv(count($org_units), 2);
                if ($org_pairs > 0) {
                    $take = $org_pairs * 2;

                    $action_discount = 0.0;
                    $items_org = array();

                    for ($i = 0; $i < $take; $i++) {
                        $d = $org_units[$i]['price'] * 0.15;
                        $discount += $d;
                        $action_discount += $d;

                        $add_items($items_org, $org_units[$i]['name'], 1);
                    }
                    $applied_actions[] = array(
                        'title' => 'Акція «Набір + Набір» ',
                        'details' => '<b>Умови:</b> при купівлі двох наборів ароматизаторів з однієї категорії. ',
                        'items' => $items_to_string($items_org),
                        'amount' => $action_discount
                    );

                }
            }

            if ($pods_enabled) {
                $pod_by_model = array(); // model => units[]
                foreach ($pod_units as $u) {
                    if ($u['used']) continue;
                    $m = $u['model'];
                    if (!isset($pod_by_model[$m])) $pod_by_model[$m] = array();
                    $pod_by_model[$m][] = $u;
                }

                $pod_any_pairs = false;
                $model_pairs_count = 0;

                $action_discount = 0.0;
                $items_pod_all = array();
                $by_models_info = array(); // model => qty_discounted

                foreach ($pod_by_model as $model => $units) {
                    $pairs = intdiv(count($units), 2);
                    if ($pairs <= 0) continue;

                    $pod_any_pairs = true;
                    $model_pairs_count += $pairs;

                    if (!isset($by_models_info[$model])) $by_models_info[$model] = 0;
                    $by_models_info[$model] += $pairs * 2;

                    $take = $pairs * 2;
                    for ($i = 0; $i < $take; $i++) {
                        $d = $units[$i]['price'] * 0.15;
                        $discount += $d;
                        $action_discount += $d;

                        $add_items($items_pod_all, $units[$i]['name'], 1);
                    }
                }

                if ($pod_any_pairs) {
                    $models_parts = array();
                    foreach ($by_models_info as $model => $qty) {
                        $models_parts[] = $model . ' ×' . $qty . '. ';
                    }
                    $models_str = implode(', ', $models_parts);

                    $applied_actions[] = array(
                        'title' => 'Акція «Под + Под». ',
                        'details' => '<b>Умови:</b> при купівлі двох і більше pod-систем однієї моделі. ',
                        'items' => $items_to_string($items_pod_all),
                        'amount' => $action_discount
                    );

                }
            }

            if ($discount > 0 && !empty($applied_actions)) {

//                $lines = array();
//                foreach ($applied_actions as $a) {
//                    $line = '<b>' . $a['title'] . '</b><br>';
//                    $line .= $a['details'] . '<br>';
//
//                    if (!empty($a['items'])) {
//                        $line .= $a['items'] . '<br>';
//                    }
//
//                    $line .= '<b>Сума знижки:</b> ' . $fmt_money($a['amount']) . ' грн';
//                    $lines[] = $line;
//                }
//
//                $title_text = implode('<br><br>', $lines);

                $lines = array();


                foreach ($applied_actions as $a) {

                    $line  = '<div class="cart-discount__action-row">';

                    // TITLE
                    $line .= '<div class="cart-discount__action-title">'
                        . $a['title']
                        . '</div>';

                    // CONDITIONS
                    $line .= '<div class="cart-discount__action-body"><div class="cart-discount__action-conditions">'
                        . $a['details']
                        . '</div>';

                    // PRODUCTS
                    if (!empty($a['items'])) {

                        $line .= '<div class="cart-discount__products">';

                        $line .= '<div class="cart-discount__products-title">'
                            . 'Товари в акції: '
                            . '</div>';

                        $products = explode(', ', $a['items']);
                        foreach ($products as $product) {
                            $line .= '<div class="cart-discount__product">'
                                . $product
                                . '</div>';
                        }

                        $line .= '</div>';
                    }

                    // AMOUNT
                    $line .= '<div class="cart-discount__amount">'
                        . '<span class="cart-discount__amount-label">Знижка за акцією: </span> '
                        . '–' . number_format($a['amount'], 2, ',', ' ') . ' грн '
                        . '<span class="cart-discount__note">';

                    if (mb_stripos($a['title'], 'Pod + Набір') !== false) {
                        $line .= '(знижка застосована до набору)';
                    } else {
                        $line .= '(по 15% на кожну позицію). ';
                    }

                    $line .= '</span></div>';

                    $line .= '</div></div>';

                    $lines[] = $line;
                }

                $title_text = implode('', $lines);

				$discount = round($discount, 0);
				
                $total['totals'][] = array(
                    'code' => 'custom_specials',
                    'title' => $title_text,
                    'value' => -$discount,
                    'sort_order' => 9
                );
            }

            $total['total'] -= $discount;
        }
    }

    public function getProducts($category_id)
    {
        $product_data = array();

        $query = $this->db->query("
		SELECT DISTINCT p.product_id
		FROM " . DB_PREFIX . "category_path cp LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (cp.category_id = p2c.category_id)
		LEFT JOIN " . DB_PREFIX . "product p ON (p2c.product_id = p.product_id)
		WHERE
		  cp.path_id = '" . $category_id . "'
		  AND p.status = 1
		;");

        foreach ($query->rows as $result) {
            $product_data[] = $result['product_id'];
        }

        return $product_data;
    }
}
