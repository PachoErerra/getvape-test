<?php

class ModelExtensionTotalCoupon extends Model
{
	/**
	 * Remove promo reward gift from cart and clear related session markers.
	 * Used when coupon is invalid/used/expired so gift cannot be re-ordered
	 * from another browser session that still holds session.coupon.
	 */
	public function clearCouponGift()
	{
		if (!empty($this->session->data['api_id'])) {
			unset($this->session->data['coupon_gift']);
			unset($this->session->data['coupon_gift_addmore']);
			unset($this->session->data['noski']);
			return;
		}

		if (!empty($this->session->data['coupon_gift'])) {
			$this->cart->remove($this->session->data['coupon_gift']);
		}

		unset($this->session->data['coupon_gift']);
		unset($this->session->data['coupon_gift_addmore']);
		unset($this->session->data['noski']);

		// isgift products stay in DB even when hidden without session.coupon
		$gift_query = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "product` WHERE isgift = '1'");

		foreach ($gift_query->rows as $gift_product) {
			$this->cart->remove_productid($gift_product['product_id']);
		}
	}

	/**
	 * If applied coupon is no longer valid, drop gift + session coupon.
	 * Returns true when coupon is OK (or not set).
	 */
	public function syncCouponGiftState()
	{
		if (empty($this->session->data['coupon']) && empty($this->session->data['coupon_gift'])) {
			return true;
		}

		if (empty($this->session->data['coupon'])) {
			$this->clearCouponGift();
			return true;
		}

		$coupon_info = $this->getCoupon($this->session->data['coupon']);

		if (!$coupon_info) {
			$this->clearCouponGift();
			unset($this->session->data['coupon']);
			return false;
		}

		return true;
	}

    public function getCoupon($code)
    {
        $status = true;
		
		$SubTotal = $this->cart->getSubTotal();

        $coupon_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon` WHERE code = '" . $this->db->escape($code) . "' AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW())) AND status = '1'");

        if ($coupon_query->num_rows) {
            if ($coupon_query->row['total'] > $SubTotal && $coupon_query->row['type'] != 'M') {
                $status = false;
            }

            $coupon_total = $this->getTotalCouponHistoriesByCoupon($code);

            if ($coupon_query->row['uses_total'] > 0 && ($coupon_total >= $coupon_query->row['uses_total'])) {
                $status = false;
            }

            if ($coupon_query->row['logged'] && !$this->customer->getId()) {
                $status = false;
            }

            if ($this->customer->getId()) {
                $customer_total = $this->getTotalCouponHistoriesByCustomerId($code, $this->customer->getId());

                if ($coupon_query->row['uses_customer'] > 0 && ($customer_total >= $coupon_query->row['uses_customer'])) {
                    $status = false;
                }
            }

            // Products
            $coupon_product_data = array();

            $coupon_product_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon_product` WHERE coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "'");

            foreach ($coupon_product_query->rows as $product) {
                $coupon_product_data[] = $product['product_id'];
            }

            // Categories
            $coupon_category_data = array();

            $coupon_category_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon_category` cc LEFT JOIN `" . DB_PREFIX . "category_path` cp ON (cc.category_id = cp.path_id) WHERE cc.coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "'");

            foreach ($coupon_category_query->rows as $category) {
                $coupon_category_data[] = $category['category_id'];
            }

            $product_data = array();

            if (($coupon_product_data || $coupon_category_data) && $coupon_query->row['type'] != 'M') {
                foreach ($this->cart->getProducts() as $product) {
                    if (in_array($product['product_id'], $coupon_product_data)) {
                        $product_data[] = $product['product_id'];

                        continue;
                    }

                    foreach ($coupon_category_data as $category_id) {
                        $coupon_category_query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` = '" . (int)$product['product_id'] . "' AND category_id = '" . (int)$category_id . "'");

                        if ($coupon_category_query->row['total']) {
                            $product_data[] = $product['product_id'];

                            continue;
                        }
                    }
                }

                if (!$product_data) {
                    $status = false;
                }
            }

            $coupon_category_data_required = array();

            $coupon_category_query_required = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon_category_required` cc LEFT JOIN `" . DB_PREFIX . "category_path` cp ON (cc.category_id = cp.path_id) WHERE cc.coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "'");

            foreach ($coupon_category_query_required->rows as $category) {
                $coupon_category_data_required[] = $category['category_id'];
            }

            if ($coupon_category_data_required) {
                foreach ($this->cart->getProducts() as $product) {
                    foreach ($coupon_category_data_required as $category_id) {
                        $coupon_category_query_required = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "product_to_category` WHERE `product_id` = '" . (int)$product['product_id'] . "' AND category_id = '" . (int)$category_id . "'");

                        if ($coupon_category_query_required->row['total']) {
                            $product_data_required[] = $product['product_id'];
                            continue;
                        }
                    }
                }

                if (!$product_data_required) {
                    $status = false;
                }
            }

			// Gift (type M) only after all validity checks - otherwise multi-session
			// carts re-add the reward after uses_total/uses_customer are exhausted.
			if ($status && $coupon_product_data && $coupon_query->row['type'] == 'M' && $coupon_query->row['total'] < $SubTotal && $SubTotal != 0) {
				$target_product_id = (int)$coupon_product_data[0];

				// Switching type M promo (e.g. cosmetic -> glasses): drop the previous
				// bound gift so it cannot stay in cart at full catalog price.
				if (!empty($this->session->data['coupon_gift'])) {
					foreach ($this->cart->getProducts() as $product) {
						if ((string)$product['cart_id'] === (string)$this->session->data['coupon_gift']
							&& (int)$product['product_id'] !== $target_product_id) {
							$this->cart->remove($this->session->data['coupon_gift']);
							unset($this->session->data['coupon_gift']);
							unset($this->session->data['coupon_gift_addmore']);
							unset($this->session->data['noski']);
							break;
						}
					}
				}

				$incart = false;
				foreach ($this->cart->getProducts() as $product) {
					if (in_array($product['product_id'], $coupon_product_data)) {
                        $product_data[] = $product['product_id'];

						if ($product['quantity'] != 1) {
							$this->cart->update($product['cart_id'],1);
						}
						$incart = $product['cart_id'];
                    }
				}
			
				if (!$incart) {
					$incart =$this->cart->add($coupon_product_data[0],1);
				}
				$this->session->data['coupon_gift'] = $incart;
				unset($this->session->data['coupon_gift_addmore']);

				// Orphan isgift left after a promo switch (no longer bound / not gift_teaser).
				// Admin order edit keeps every line so a manager can delete one gift and leave the other.
				if (empty($this->session->data['api_id'])) {
				$gift_product_ids = array();
				$gift_query = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "product` WHERE isgift = '1'");
				foreach ($gift_query->rows as $gift_row) {
					$gift_product_ids[] = (int)$gift_row['product_id'];
				}
				$orphan_cart_ids = array();
				foreach ($this->cart->getProducts() as $product) {
					if (!in_array((int)$product['product_id'], $gift_product_ids, true)) {
						continue;
					}
					if ((int)$product['product_id'] === $target_product_id) {
						continue;
					}
					if (!empty($product['gift_teaser'])) {
						continue;
					}
					$orphan_cart_ids[] = $product['cart_id'];
				}
				foreach ($orphan_cart_ids as $orphan_cart_id) {
					$this->cart->remove($orphan_cart_id);
				}
				}

				if ($coupon_query->row['total'] == 500) {
					$this->session->data['noski'] = 500;
				}
			} else if ($status && $coupon_product_data && $coupon_query->row['type'] == 'M' && $SubTotal == 0) {
				$this->clearCouponGift();
				$this->session->data['coupon_gift_addmore'] = 'Додайте будь який товар, щоб отримати '.$coupon_query->row['name'].' за Вашим промокодом, його буде додано до кошика автоматично';

			} else if ($status && $coupon_product_data && $coupon_query->row['type'] == 'M') {
				$this->clearCouponGift();
				$this->session->data['coupon_gift_addmore'] = 'Додайте товар ще на '.($coupon_query->row['total'] - $SubTotal).' грн, щоб отримати '.$coupon_query->row['name'].' за Вашим промокодом, його буде додано до кошика автоматично';
				if ($coupon_query->row['total'] == 500) {
					$this->session->data['noski'] = 500;
				}
			} else {
				// Invalid / non-gift coupon: never leave a free reward item in cart
				$this->clearCouponGift();
			}

        } else {
            $status = false;
        }

        if ($status) {
            return array(
                'coupon_id' => $coupon_query->row['coupon_id'],
                'code' => $coupon_query->row['code'],
                'name' => $coupon_query->row['name'],
                'type' => $coupon_query->row['type'],
                'discount' => $coupon_query->row['discount'],
                'shipping' => $coupon_query->row['shipping'],
                'total' => $coupon_query->row['total'],
                'product' => $product_data,
                'date_start' => $coupon_query->row['date_start'],
                'date_end' => $coupon_query->row['date_end'],
                'uses_total' => $coupon_query->row['uses_total'],
                'uses_customer' => $coupon_query->row['uses_customer'],
                'status' => $coupon_query->row['status'],
                'date_added' => $coupon_query->row['date_added']
            );
        }
    }

    public function getTotal($total)
    {
        if (isset($this->session->data['coupon'])) {
            $this->load->language('extension/total/coupon', 'coupon');

            $coupon_info = $this->getCoupon($this->session->data['coupon']);

            $free_cartrige = false;
            $this->session->data['shipping_coupon'] = false;
            $this->session->data['double_cashback_coupon'] = false;
            $this->session->data['free_cartrige_add'] = false;
            $this->session->data['add1'] = false;
            $this->session->data['add2'] = false;
            $ranvo2 = false;
            $ranvo3 = false;
            $ranvoF = false;

            if ($coupon_info) {
                $discount_total = 0;

                if (!$coupon_info['product']) {
                    $sub_total = $this->cart->getSubTotal();
                } else {
                    $sub_total = 0;

                    foreach ($this->cart->getProducts() as $product) {
                        if (in_array($product['product_id'], $coupon_info['product'])) {
                            $sub_total += $product['total'];
                        }
                    }
                }

                if ($coupon_info['type'] == 'F') {
                    $coupon_info['discount'] = min($coupon_info['discount'], $sub_total);
                }

                foreach ($this->cart->getProducts() as $product) {
                    $discount = 0;

                    if (!$coupon_info['product']) {
                        $status = true;
                    } else {
                        $status = in_array($product['product_id'], $coupon_info['product']);

                        if ($status) {
                            $find_ranvo2 = 'Fortune1=2-';
                            $find_ranvo3 = 'Fortune1plus1=3-';
                            $find_free_cartrige = 'FortuneFree-';

                            $pos2 = stripos($coupon_info['code'], $find_ranvo2);
                            $pos3 = stripos($coupon_info['code'], $find_ranvo3);
                            $posF = stripos($coupon_info['code'], $find_free_cartrige);

                            if ($pos2 !== false) {
                                $ranvo2 += $product['quantity'];
                            }

                            if ($pos3 !== false) {
                                $ranvo3 += $product['quantity'];
                            }

                            if ($posF !== false) {
                                $ranvoF = true;
                            }

                            $price_arr[$product['name']] = $product['price'];
                            $quantity_arr[$product['name']] = $product['quantity'];
                        }
                    }

                    if ($status) {
                        if ($coupon_info['type'] == 'F') {
                            $discount = $coupon_info['discount'] * ($product['total'] / $sub_total);
                        } elseif ($coupon_info['type'] == 'P') {
                            if ($coupon_info['discount'] == 100 && !$free_cartrige && !$ranvo2 && !$ranvo3) {
                                if ($this->cart->countProducts() >= 2) {
                                    $free_cartrige = ' <br><small style="font-weight: normal;">Безкоштовний картридж ' . $product['name'] . '</small>';
                                    $this->session->data['free_cartrige'] = $product['name'];
                                    $discount = $product['price'] / 100 * $coupon_info['discount'];
                                } else if ($this->cart->countProducts() == 1 && $ranvoF) {
                                    $this->session->data['free_cartrige_add'] = true;
                                }
                            } else if ($coupon_info['discount'] == 100 && $free_cartrige) {
                                $discount = 0;

                            } else if ($coupon_info['discount'] == 100 && ($ranvo2 || $ranvo3)) {
                                //	$discount = $product['total'] / 100 * $coupon_info['discount'];

                            } else {
                                $discount = $product['total'] / 100 * $coupon_info['discount'];
                            }
                        }

                        if ($product['tax_class_id']) {
                            $tax_rates = $this->tax->getRates($product['total'] - ($product['total'] - $discount), $product['tax_class_id']);

                            foreach ($tax_rates as $tax_rate) {
                                if ($tax_rate['type'] == 'P') {
                                    $total['taxes'][$tax_rate['tax_rate_id']] -= $tax_rate['amount'];
                                }
                            }
                        }
                    }

                    $discount_total += $discount;
                }
                $this->session->data['minproduct'] = false;
                $this->session->data['minquantity'] = false;
                $this->session->data['minproduct2'] = false;
                $this->session->data['minquantity2'] = false;
                $this->session->data['minproduct3'] = false;
                $this->session->data['minquantity3'] = false;
                if ($ranvo2 == 1) {
                    $this->session->data['add1'] = true;
                }
                if ($ranvo2 >= 2 && $ranvo2 < 4) {
                    $min_price = min($price_arr);
                    $minProduct = array_keys($price_arr, $min_price);
                    if (isset($minProduct[0]) && ($minProduct[0])) {
                        $this->session->data['minproduct'] = $minProduct[0];
                        $this->session->data['minquantity'] = 1;
                        $discount_total = $min_price;
                    }
                }
                if ($ranvo2 >= 4 && $ranvo2 < 6) {
                    $min_price = min($price_arr);
                    $minProduct = array_keys($price_arr, $min_price);
                    if (isset($minProduct[0]) && ($minProduct[0])) {
                        $this->session->data['minproduct'] = $minProduct[0];
                        if ($quantity_arr[$minProduct[0]] == 1) {
                            $this->session->data['minquantity'] = $quantity_arr[$minProduct[0]];
                            $discount_total = $min_price;

                            unset($price_arr[$minProduct[0]]);
                            unset($quantity_arr[$minProduct[0]]);
                            $min_price2 = min($price_arr);
                            $minProduct2 = array_keys($price_arr, $min_price2);
                            if (isset($minProduct2[0]) && ($minProduct2[0])) {
                                $this->session->data['minproduct2'] = $minProduct2[0];
                                $this->session->data['minquantity2'] = 1;
                                $discount_total += $min_price2;
                            }

                        } else if ($quantity_arr[$minProduct[0]] >= 2) {
                            $this->session->data['minquantity'] = 2;
                            $discount_total = min($price_arr) * 2;
                        }
                    }
                }
                if ($ranvo2 >= 6) {
                    $min_price = min($price_arr);
                    $minProduct = array_keys($price_arr, $min_price);
                    if (isset($minProduct[0]) && ($minProduct[0])) {
                        $this->session->data['minproduct'] = $minProduct[0];
                        if ($quantity_arr[$minProduct[0]] == 1) {
                            $this->session->data['minquantity'] = $quantity_arr[$minProduct[0]];
                            $discount_total = $min_price;

                            unset($price_arr[$minProduct[0]]);
                            unset($quantity_arr[$minProduct[0]]);
                            $min_price2 = min($price_arr);
                            $minProduct2 = array_keys($price_arr, $min_price2);
                            if (isset($minProduct2[0]) && ($minProduct2[0])) {
                                if ($quantity_arr[$minProduct2[0]] == 1) {
                                    $this->session->data['minproduct2'] = $minProduct2[0];
                                    $this->session->data['minquantity2'] = 1;
                                    $discount_total += $min_price2;

                                    unset($price_arr[$minProduct2[0]]);
                                    unset($quantity_arr[$minProduct2[0]]);
                                    $min_price3 = min($price_arr);
                                    $minProduct3 = array_keys($price_arr, $min_price3);
                                    $this->session->data['minproduct3'] = $minProduct3[0];
                                    $this->session->data['minquantity3'] = 1;
                                    $discount_total += $min_price3;

                                }
                                if ($quantity_arr[$minProduct2[0]] == 2) {
                                    $this->session->data['minproduct2'] = $minProduct2[0];
                                    $this->session->data['minquantity2'] = 2;
                                    $discount_total += $min_price2 * 2;
                                }
                            }
                        } else if ($quantity_arr[$minProduct[0]] == 2) {
                            $this->session->data['minquantity'] = 2;
                            $discount_total = min($price_arr) * 2;

                            unset($price_arr[$minProduct[0]]);
                            unset($quantity_arr[$minProduct[0]]);
                            $min_price2 = min($price_arr);
                            $minProduct2 = array_keys($price_arr, $min_price2);
                            if (isset($minProduct2[0]) && ($minProduct2[0])) {
                                $this->session->data['minproduct2'] = $minProduct2[0];
                                $this->session->data['minquantity2'] = 1;
                                $discount_total += $min_price2;
                            }
                        } else if ($quantity_arr[$minProduct[0]] >= 3) {
                            $this->session->data['minquantity'] = 3;
                            $discount_total = min($price_arr) * 3;
                        }
                    }
                }

                if ($ranvo3 == 1) {
                    $this->session->data['add2'] = true;
                }
                if ($ranvo3 == 2) {
                    $this->session->data['add1'] = true;
                }
                if ($ranvo3 >= 3 && $ranvo3 < 6) {
                    $min_price = min($price_arr);
                    $minProduct = array_keys($price_arr, $min_price);
                    if (isset($minProduct[0]) && ($minProduct[0])) {
                        $this->session->data['minproduct'] = $minProduct[0];
                        $this->session->data['minquantity'] = 1;
                        $discount_total = $min_price;
                    }
                }


                if ($ranvo3 >= 6 && $ranvo3 < 9) {
                    $min_price = min($price_arr);
                    $minProduct = array_keys($price_arr, $min_price);
                    if (isset($minProduct[0]) && ($minProduct[0])) {
                        $this->session->data['minproduct'] = $minProduct[0];
                        if ($quantity_arr[$minProduct[0]] == 1) {
                            $this->session->data['minquantity'] = $quantity_arr[$minProduct[0]];
                            $discount_total = $min_price;

                            unset($price_arr[$minProduct[0]]);
                            unset($quantity_arr[$minProduct[0]]);
                            $min_price2 = min($price_arr);
                            $minProduct2 = array_keys($price_arr, $min_price2);
                            if (isset($minProduct2[0]) && ($minProduct2[0])) {
                                $this->session->data['minproduct2'] = $minProduct2[0];
                                $this->session->data['minquantity2'] = 1;
                                $discount_total += $min_price2;
                            }

                        } else if ($quantity_arr[$minProduct[0]] >= 2) {
                            $this->session->data['minquantity'] = 2;
                            $discount_total = min($price_arr) * 2;
                        }
                    }
                }

                if ($ranvo3 >= 9) {
                    $min_price = min($price_arr);
                    $minProduct = array_keys($price_arr, $min_price);
                    if (isset($minProduct[0]) && ($minProduct[0])) {
                        $this->session->data['minproduct'] = $minProduct[0];
                        if ($quantity_arr[$minProduct[0]] == 1) {
                            $this->session->data['minquantity'] = $quantity_arr[$minProduct[0]];
                            $discount_total = $min_price;

                            unset($price_arr[$minProduct[0]]);
                            unset($quantity_arr[$minProduct[0]]);
                            $min_price2 = min($price_arr);
                            $minProduct2 = array_keys($price_arr, $min_price2);
                            if (isset($minProduct2[0]) && ($minProduct2[0])) {
                                if ($quantity_arr[$minProduct2[0]] == 1) {
                                    $this->session->data['minproduct2'] = $minProduct2[0];
                                    $this->session->data['minquantity2'] = 1;
                                    $discount_total += $min_price2;

                                    unset($price_arr[$minProduct2[0]]);
                                    unset($quantity_arr[$minProduct2[0]]);
                                    $min_price3 = min($price_arr);
                                    $minProduct3 = array_keys($price_arr, $min_price3);
                                    $this->session->data['minproduct3'] = $minProduct3[0];
                                    $this->session->data['minquantity3'] = 1;
                                    $discount_total += $min_price3;

                                }
                                if ($quantity_arr[$minProduct2[0]] == 2) {
                                    $this->session->data['minproduct2'] = $minProduct2[0];
                                    $this->session->data['minquantity2'] = 2;
                                    $discount_total += $min_price2 * 2;
                                }
                            }
                        } else if ($quantity_arr[$minProduct[0]] == 2) {
                            $this->session->data['minquantity'] = 2;
                            $discount_total = min($price_arr) * 2;

                            unset($price_arr[$minProduct[0]]);
                            unset($quantity_arr[$minProduct[0]]);
                            $min_price2 = min($price_arr);
                            $minProduct2 = array_keys($price_arr, $min_price2);
                            if (isset($minProduct2[0]) && ($minProduct2[0])) {
                                $this->session->data['minproduct2'] = $minProduct2[0];
                                $this->session->data['minquantity2'] = 1;
                                $discount_total += $min_price2;
                            }
                        } else if ($quantity_arr[$minProduct[0]] >= 3) {
                            $this->session->data['minquantity'] = 3;
                            $discount_total = min($price_arr) * 3;
                        }
                    }
                }

                if ($coupon_info['shipping']) {
                    $this->session->data['shipping_coupon'] = 1;
                    if (isset($this->session->data['shipping_method'])) {
                        if (!empty($this->session->data['shipping_method']['tax_class_id'])) {
                            $tax_rates = $this->tax->getRates($this->session->data['shipping_method']['cost'], $this->session->data['shipping_method']['tax_class_id']);

                            foreach ($tax_rates as $tax_rate) {
                                if ($tax_rate['type'] == 'P') {
                                    $total['taxes'][$tax_rate['tax_rate_id']] -= $tax_rate['amount'];
                                }
                            }
                        }
						$discount_total += $this->session->data['shipping_method']['cost'];
                    }
                    
                }

                if ($coupon_info['type'] == 'B') {
					$discount_total = 0;
                    $total['totals'][] = array(
                        'code' => 'coupon',
                        'title' => sprintf($this->language->get('coupon')->get('text_coupon'), $this->session->data['coupon']) . '<br> Вітаємо, ви виграли 100грн бонусів, вони будуть нараховані протягом дня після замовлення',
                        'value' => $discount_total,
                        'sort_order' => $this->config->get('total_coupon_sort_order')
                    );
					$discount_total = 0;
                }

                if ($coupon_info['type'] == 'D') {
					$discount_total = 0;
                    $this->session->data['double_cashback_coupon'] = $this->session->data['coupon'];
                    $total['totals'][] = array(
                        'code' => 'coupon',
                        'title' => sprintf($this->language->get('coupon')->get('text_coupon'), $this->session->data['coupon']) . '<br> Подвійний кешбек з колеса фортуни. Бонуси нараховуються о 00:00 після оплати замовлення',
                        'value' => $discount_total,
                        'sort_order' => $this->config->get('total_coupon_sort_order')
                    );
					$discount_total = 0;
                }

                if ($coupon_info['type'] == 'T') {
					$discount_total = 0;
                    $total['totals'][] = array(
                        'code' => 'coupon',
                        'title' => sprintf($this->language->get('coupon')->get('text_coupon'), $this->session->data['coupon']) . '<br> Вітаємо, ви виграли мерч від наших партнерів, подарунок буде вкладений до вашого замовлення',
                        'value' => $discount_total,
                        'sort_order' => $this->config->get('total_coupon_sort_order')
                    );
					$discount_total = 0;
                }

                if ($coupon_info['type'] == 'M' && isset($this->session->data['coupon_gift']) && $this->session->data['coupon_gift']) {
					$discount_total = 0;
                    $total['totals'][] = array(
                        'code' => 'coupon',
                        'title' => sprintf($this->language->get('coupon')->get('text_coupon'), $this->session->data['coupon']) . '<br> Вітаємо, ви виграли '.$coupon_info['name'],
                        'value' => $discount_total,
                        'sort_order' => $this->config->get('total_coupon_sort_order')
                    );
					$discount_total = 0;
                }
				
                // If discount greater than total
                if ($discount_total > $total['total']) {
                    $discount_total = $total['total'];
                }

                if ($discount_total > 0) {
                    $total['totals'][] = array(
                        'code' => 'coupon',
                        'title' => sprintf($this->language->get('coupon')->get('text_coupon'), $this->session->data['coupon']) . $free_cartrige . ($free_cartrige?'':' ' . $coupon_info['name']),
                        'value' => -round($discount_total),
                        'sort_order' => $this->config->get('total_coupon_sort_order')
                    );

                    $total['total'] -= round($discount_total);
                }
            } else {
                // Applied coupon no longer valid (used/expired): drop gift in this session
                $this->clearCouponGift();
                unset($this->session->data['coupon']);
                unset($this->session->data['shipping_coupon']);
                unset($this->session->data['double_cashback_coupon']);
                unset($this->session->data['free_cartrige_add']);
                unset($this->session->data['add1']);
                unset($this->session->data['add2']);
            }
        } elseif (!empty($this->session->data['coupon_gift'])) {
            $this->clearCouponGift();
        }
    }

    public function confirm($order_info, $order_total)
    {
        $code = '';

        $start = strpos($order_total['title'], '(') + 1;
        $end = strrpos($order_total['title'], ')');

        if ($start && $end) {
            $code = substr($order_total['title'], $start, $end - $start);
        }

        if ($code) {
            $status = true;

            $coupon_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon` WHERE code = '" . $this->db->escape($code) . "' AND status = '1'");

            if ($coupon_query->num_rows) {
                $coupon_total = $this->getTotalCouponHistoriesByCoupon($code);

                if ($coupon_query->row['uses_total'] > 0 && ($coupon_total >= $coupon_query->row['uses_total'])) {
                    $status = false;
                }

                if ($order_info['customer_id']) {
                    $customer_total = $this->getTotalCouponHistoriesByCustomerId($code, $order_info['customer_id']);

                    if ($coupon_query->row['uses_customer'] > 0 && ($customer_total >= $coupon_query->row['uses_customer'])) {
                        $status = false;
                    }
                }
            } else {
                $status = false;
            }

            if ($status) {
                unset($this->session->data['free_cartrige']);
                $this->db->query("INSERT INTO `" . DB_PREFIX . "coupon_history` SET coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "', order_id = '" . (int)$order_info['order_id'] . "', customer_id = '" . (int)$order_info['customer_id'] . "', amount = '" . (float)$order_total['value'] . "', date_added = NOW()");

                $this->db->query("UPDATE `" . DB_PREFIX . "customer_fortune` SET used = (used + 1), date_used = NOW() WHERE code = '" . $this->db->escape($code) . "'");
				$this->db->query("UPDATE `" . DB_PREFIX . "customer_battlepass_coupons` SET used = (used + 1), date_used = NOW() WHERE code = '" . $this->db->escape($code) . "'");
            } else {
                return $this->config->get('config_fraud_status_id');
            }
        }
    }

    public function unconfirm($order_id)
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "coupon_history` WHERE order_id = '" . (int)$order_id . "'");
    }

    public function getTotalCouponHistoriesByCoupon($coupon)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "coupon_history` ch LEFT JOIN `" . DB_PREFIX . "coupon` c ON (ch.coupon_id = c.coupon_id) WHERE c.code = '" . $this->db->escape($coupon) . "'");

        return $query->row['total'];
    }

    public function getTotalCouponHistoriesByCustomerId($coupon, $customer_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "coupon_history` ch LEFT JOIN `" . DB_PREFIX . "coupon` c ON (ch.coupon_id = c.coupon_id) WHERE c.code = '" . $this->db->escape($coupon) . "' AND ch.customer_id = '" . (int)$customer_id . "'");

        return $query->row['total'];
    }
}
