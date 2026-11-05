<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerAccountOrder extends Controller {
	public function index() {
		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order', '', true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->language('account/order');

		$this->document->setTitle($this->language->get('heading_title'));
		$this->document->setRobots('noindex,follow');
		
		$url = '';

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}
		
		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_account'),
			'href' => $this->url->link('account/account', '', true)
		);
		
		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('account/order', $url, true)
		);

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}
			if (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}
		$data['orders'] = array();

		$this->load->model('account/order');
		$this->load->model('catalog/product');

		$order_total = $this->model_account_order->getTotalOrders();

		$results = $this->model_account_order->getOrders(($page - 1) * 10, 10);
		
		$discount = $this->customer->getDiscount();

		foreach ($results as $result) {
			$product_total = $this->model_account_order->getTotalOrderProductsByOrderId($result['order_id']);
			$voucher_total = $this->model_account_order->getTotalOrderVouchersByOrderId($result['order_id']);
				$products = array();
				$reorderproduct = 0;
				$rewardtotal = 0;
				$personalreward = false;
				
				$totals = $this->model_account_order->getOrderTotals($result['order_id']);

				foreach ($totals as $total) {
					if ($total['code'] == 'low_order_fee') {
						$personalreward = true;
					}
				}
				
				foreach ($this->model_account_order->getOrderProducts($result['order_id']) as $product) {
					
					$option_data = array();

					$options = $this->model_account_order->getOrderOptions($result['order_id'], $product['order_product_id']);

					foreach ($options as $option) {
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
							'name'  => $option['name'],
							'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
						);
					}
					
					$product_data = $this->model_catalog_product->getProduct($product['product_id']);
					
					if ($product_data && !empty($product_data['image'])) {
						$image = $this->model_tool_image->resize($product_data['image'], $this->config->get('theme_' . $this->config->get('config_theme') . '_image_compare_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_compare_height'));
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $this->config->get('theme_' . $this->config->get('config_theme') . '_image_compare_width'), $this->config->get('theme_' . $this->config->get('config_theme') . '_image_compare_height'));
					}
					
					$product_info = $product_data;
					
					if ($product_info && $this->isReorderableProduct($product, $product_info)) {
						$reorderproduct++;
					}
			
					// Reward Points
					$product_reward_query = $this->db->query("SELECT points,bonus FROM " . DB_PREFIX . "product_reward WHERE product_id = '" . (int)$product['product_id'] . "' AND customer_group_id = '" . (int)$this->customer->getGroupId() . "'");

					if ($product_reward_query->num_rows) {
						if ($discount && $personalreward) {
						//	$percent = $product_reward_query->row['points'] / $product['price'] * 100;
						//	$reward = (($product['price'] - ($product['price'] / 100 * $discount)) / 100 * $product_reward_query->row['bonus']);
							$reward = ($product['reward'] - ($product['reward'] / 100 * $discount));
						} else {
						//	$reward = $product['price'] / 100 * $product_reward_query->row['bonus'];
							$reward = $product['reward'];
						}
						
					} else {
						$reward = 0;
					}
						
					$rewardtotal += $reward;
					
					$products[] = array(
						'name'     => $product['name'],
						'model'    => $product['model'],
						'option'   => $option_data,
						'quantity' => $product['quantity'],
						'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $result['currency_code'], $result['currency_value']),
						'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $result['currency_code'], $result['currency_value']),
						'return'   => $this->url->link('account/return/add', 'order_id=' . $result['order_id'] . '&product_id=' . $product['product_id'], true),
						'image'		=> $image,
					);	
				}	
		
				$totals = array();
				$ordertotals = array();

				$totals = $this->model_account_order->getOrderTotals($result['order_id']);

				foreach ($totals as $total) {
					$ordertotals[] = array(
						'title' => $total['title'],
						'code' => $total['code'],
						'text'  => $this->currency->format($total['value'], $result['currency_code'], $result['currency_value']),
					);
					
					if ($total['code'] == 'reward') {
						$rewardtotal = 0;
					}
					
				}
				
				if (!in_array($result['order_status_id'], $this->config->get('config_complete_status'))) {
					$rewardtotal = 0;
				}
				
				

				if ($reorderproduct) {
					$reorder = $this->url->link('account/order/reorderALL', 'order_id=' . $result['order_id'], true);
				} else {
					$reorder = false;
				}

			
			$data['orders'][] = array(
				'rewardtotal'     => round($rewardtotal),
				'reorder'     => $reorder,
				'order_id'   => $result['order_id'],
				'name'       => $result['firstname'] . ' ' . $result['lastname'],
				'status'     => $result['status'],
				'b_color'     => $result['b_color'],
				't_color'     => $result['t_color'],
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'products'   => ($product_total + $voucher_total),
				'total'      => $this->currency->format($result['total'], $result['currency_code'], $result['currency_value']),
				'view'       => $this->url->link('account/order/info', 'order_id=' . $result['order_id'], true),
				'products_data' => $products,
				'ordertotals'	=> $ordertotals
			);
		}

		$pagination = new Pagination();
		$pagination->total = $order_total;
		$pagination->page = $page;
		$pagination->limit = 10;
		$pagination->url = $this->url->link('account/order', 'page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * 10) + 1 : 0, ((($page - 1) * 10) > ($order_total - 10)) ? $order_total : ((($page - 1) * 10) + 10), $order_total, ceil($order_total / 10));

		$data['continue'] = $this->url->link('account/account', '', true);

		$data['column_left'] = $this->load->controller('common/column_left');
		$data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
		$data['content_bottom'] = $this->load->controller('common/content_bottom');
		$data['footer'] = $this->load->controller('common/footer');
		$data['header'] = $this->load->controller('common/header');

		$this->response->setOutput($this->load->view('account/order_list', $data));
	}

	public function info() {
		$this->load->language('account/order');

		if (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		if (!$this->customer->isLogged()) {
			$this->session->data['redirect'] = $this->url->link('account/order/info', 'order_id=' . $order_id, true);

			$this->response->redirect($this->url->link('account/login', '', true));
		}

		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id);

		if ($order_info) {
			$this->document->setTitle($this->language->get('text_order'));

			$url = '';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}

			$data['breadcrumbs'] = array();

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_home'),
				'href' => $this->url->link('common/home')
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_account'),
				'href' => $this->url->link('account/account', '', true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('heading_title'),
				'href' => $this->url->link('account/order', $url, true)
			);

			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_order'),
				'href' => $this->url->link('account/order/info', 'order_id=' . $this->request->get['order_id'] . $url, true)
			);

			if (isset($this->session->data['error'])) {
				$data['error_warning'] = $this->session->data['error'];

				unset($this->session->data['error']);
			} else {
				$data['error_warning'] = '';
			}

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];

				unset($this->session->data['success']);
			} else {
				$data['success'] = '';
			}

			if ($order_info['invoice_no']) {
				$data['invoice_no'] = $order_info['invoice_prefix'] . $order_info['invoice_no'];
			} else {
				$data['invoice_no'] = '';
			}

			$data['order_id'] = (int)$this->request->get['order_id'];
			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($order_info['date_added']));

			if ($order_info['payment_address_format']) {
				$format = $order_info['payment_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['payment_firstname'],
				'lastname'  => $order_info['payment_lastname'],
				'company'   => $order_info['payment_company'],
				'address_1' => $order_info['payment_address_1'],
				'address_2' => $order_info['payment_address_2'],
				'city'      => $order_info['payment_city'],
				'postcode'  => $order_info['payment_postcode'],
				'zone'      => $order_info['payment_zone'],
				'zone_code' => $order_info['payment_zone_code'],
				'country'   => $order_info['payment_country']
			);

			$data['payment_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$data['payment_method'] = $order_info['payment_method'];

			if ($order_info['shipping_address_format']) {
				$format = $order_info['shipping_address_format'];
			} else {
				$format = '{firstname} {lastname}' . "\n" . '{company}' . "\n" . '{address_1}' . "\n" . '{address_2}' . "\n" . '{city} {postcode}' . "\n" . '{zone}' . "\n" . '{country}';
			}

			$find = array(
				'{firstname}',
				'{lastname}',
				'{company}',
				'{address_1}',
				'{address_2}',
				'{city}',
				'{postcode}',
				'{zone}',
				'{zone_code}',
				'{country}'
			);

			$replace = array(
				'firstname' => $order_info['shipping_firstname'],
				'lastname'  => $order_info['shipping_lastname'],
				'company'   => $order_info['shipping_company'],
				'address_1' => $order_info['shipping_address_1'],
				'address_2' => $order_info['shipping_address_2'],
				'city'      => $order_info['shipping_city'],
				'postcode'  => $order_info['shipping_postcode'],
				'zone'      => $order_info['shipping_zone'],
				'zone_code' => $order_info['shipping_zone_code'],
				'country'   => $order_info['shipping_country']
			);

			$data['shipping_address'] = str_replace(array("\r\n", "\r", "\n"), '<br />', preg_replace(array("/\s\s+/", "/\r\r+/", "/\n\n+/"), '<br />', trim(str_replace($find, $replace, $format))));

			$data['shipping_method'] = $order_info['shipping_method'];

			$this->load->model('catalog/product');
			$this->load->model('tool/upload');

			// Products
			$data['products'] = array();

			$products = $this->model_account_order->getOrderProducts($this->request->get['order_id']);

			foreach ($products as $product) {
				$option_data = array();

				$options = $this->model_account_order->getOrderOptions($this->request->get['order_id'], $product['order_product_id']);

				foreach ($options as $option) {
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
						'name'  => $option['name'],
						'value' => (utf8_strlen($value) > 20 ? utf8_substr($value, 0, 20) . '..' : $value)
					);
				}

				$product_info = $this->model_catalog_product->getProduct($product['product_id']);

				if ($product_info) {
					$reorder = $this->url->link('account/order/reorder', 'order_id=' . $order_id . '&order_product_id=' . $product['order_product_id'], true);
				} else {
					$reorder = '';
				}

				$data['products'][] = array(
					'name'     => $product['name'],
					'model'    => $product['model'],
					'option'   => $option_data,
					'quantity' => $product['quantity'],
					'price'    => $this->currency->format($product['price'] + ($this->config->get('config_tax') ? $product['tax'] : 0), $order_info['currency_code'], $order_info['currency_value']),
					'total'    => $this->currency->format($product['total'] + ($this->config->get('config_tax') ? ($product['tax'] * $product['quantity']) : 0), $order_info['currency_code'], $order_info['currency_value']),
					'reorder'  => $reorder,
					'return'   => $this->url->link('account/return/add', 'order_id=' . $order_info['order_id'] . '&product_id=' . $product['product_id'], true)
				);
			}

			// Voucher
			$data['vouchers'] = array();

			$vouchers = $this->model_account_order->getOrderVouchers($this->request->get['order_id']);

			foreach ($vouchers as $voucher) {
				$data['vouchers'][] = array(
					'description' => $voucher['description'],
					'amount'      => $this->currency->format($voucher['amount'], $order_info['currency_code'], $order_info['currency_value'])
				);
			}

			// Totals
			$data['totals'] = array();

			$totals = $this->model_account_order->getOrderTotals($this->request->get['order_id']);

			foreach ($totals as $total) {
				$data['totals'][] = array(
					'title' => $total['title'],
					'text'  => $this->currency->format($total['value'], $order_info['currency_code'], $order_info['currency_value']),
				);
			}

			$data['comment'] = nl2br($order_info['comment']);

			// History
			$data['histories'] = array();

			$results = $this->model_account_order->getOrderHistories($this->request->get['order_id']);

			foreach ($results as $result) {
				$data['histories'][] = array(
					'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'status'     => $result['status'],
					'comment'    => $result['notify'] ? nl2br($result['comment']) : ''
				);
			}

			$data['continue'] = $this->url->link('account/order', '', true);

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('account/order_info', $data));
		} else {
			return new Action('error/not_found');
		}
	}

	public function reorder($orderid = null, $orderproductid = null, $clear_cart = false) {
		$this->load->language('account/order');

		$result = array(
			'added' => false,
			'skipped_gift' => false,
			'unavailable' => false,
			'name' => ''
		);

		if (!empty($orderid)) {
			$order_id = $orderid;
		} elseif (isset($this->request->get['order_id'])) {
			$order_id = $this->request->get['order_id'];
		} else {
			$order_id = 0;
		}

		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id);

		if ($order_info) {
			if (!empty($orderproductid)) {
				$order_product_id = $orderproductid;
			} elseif (isset($this->request->get['order_product_id'])) {
				$order_product_id = $this->request->get['order_product_id'];
			} else {
				$order_product_id = 0;
			}

			$order_product_info = $this->model_account_order->getOrderProduct($order_id, $order_product_id);

			if ($order_product_info) {
				$result['name'] = $order_product_info['name'];

				$this->load->model('catalog/product');

				$product_info = $this->model_catalog_product->getProduct($order_product_info['product_id']);

				if ($this->isOrderProductGift($order_product_info, $order_id, $order_product_id)) {
					$result['skipped_gift'] = true;
				} elseif (!$product_info || !$this->isProductInStock($product_info)) {
					$result['unavailable'] = true;
				} else {
					if ($clear_cart) {
						$this->cart->clear();
					}

					$option_data = array();
					$order_options = $this->model_account_order->getOrderOptions($order_product_info['order_id'], $order_product_id);

					foreach ($order_options as $order_option) {
						// Gift / promo cart options must not be re-added as paid lines
						if ($order_option['type'] == 'giftTeaser' || $order_option['name'] == 'giftTeaser' || $order_option['value'] == 'giftTeaser') {
							continue;
						}
						if ($order_option['type'] == 'select' || $order_option['type'] == 'radio' || $order_option['type'] == 'image') {
							$option_data[$order_option['product_option_id']] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'checkbox') {
							$option_data[$order_option['product_option_id']][] = $order_option['product_option_value_id'];
						} elseif ($order_option['type'] == 'text' || $order_option['type'] == 'textarea' || $order_option['type'] == 'date' || $order_option['type'] == 'datetime' || $order_option['type'] == 'time') {
							$option_data[$order_option['product_option_id']] = $order_option['value'];
						} elseif ($order_option['type'] == 'file') {
							$option_data[$order_option['product_option_id']] = $this->encryption->encrypt($this->config->get('config_encryption'), $order_option['value']);
						}
					}

					$qty = (int)$order_product_info['quantity'];
					if ($qty < 1) {
						$qty = 1;
					}

					// Online orders only from the 4 warehouses (product.quantity)
					$stock = (int)$product_info['quantity'];
					if (!empty($product_info['subtract']) && $stock > 0) {
						$qty = min($qty, $stock, 99);
					} else {
						$qty = min($qty, 99);
					}

					$this->cart->add($order_product_info['product_id'], $qty, $option_data);

					unset($this->session->data['shipping_method']);
					unset($this->session->data['shipping_methods']);
					unset($this->session->data['payment_method']);
					unset($this->session->data['payment_methods']);

					$result['added'] = true;
				}
			}
		}

		// Direct link from order info: one product, then stay on order page
		if (empty($orderid)) {
			if ($result['added']) {
				$this->session->data['success'] = sprintf($this->language->get('text_success'), $this->url->link('checkout/simplecheckout'));
			} elseif ($result['skipped_gift']) {
				$this->session->data['error'] = $this->language->get('error_reorder_gift');
			} else {
				$this->session->data['error'] = sprintf($this->language->get('error_reorder_item'), $result['name'] ? $result['name'] : '');
			}

			$this->response->redirect($this->url->link('account/order/info', 'order_id=' . $order_id));
		}

		return $result;
	}

	public function reorderALL() {
		$this->load->language('account/order');

		if (!isset($this->request->get['order_id'])) {
			$this->response->redirect($this->url->link('account/order'));
			return;
		}

		$order_id = (int)$this->request->get['order_id'];
		$this->load->model('account/order');

		$order_info = $this->model_account_order->getOrder($order_id);

		if (!$order_info) {
			$this->response->redirect($this->url->link('account/order'));
			return;
		}

		$results = $this->model_account_order->getProductIds($order_id);

		// Do not clear current cart: add available order items on top
		$added = 0;
		$unavailable = array();
		$skipped_gifts = array();

		foreach ($results as $result) {
			$item = $this->reorder($order_id, $result['order_product_id'], false);

			if (!empty($item['added'])) {
				$added++;
			} elseif (!empty($item['skipped_gift'])) {
				if ($item['name']) {
					$skipped_gifts[] = $item['name'];
				}
			} elseif ($item['name']) {
				$unavailable[] = $item['name'];
			}
		}

		if ($added > 0) {
			$messages = array();

			if ($unavailable) {
				$messages[] = sprintf($this->language->get('text_reorder_partial'), implode(', ', $unavailable));
			}

			if ($skipped_gifts) {
				$messages[] = sprintf($this->language->get('text_reorder_skipped_gifts'), implode(', ', $skipped_gifts));
			}

			if ($messages) {
				$this->session->data['reorder_warning'] = implode(' ', $messages);
			}

			$this->response->redirect($this->url->link('checkout/simplecheckout'));
		}

		if ($skipped_gifts && !$unavailable) {
			$this->session->data['error'] = $this->language->get('error_reorder_only_gifts');
		} elseif ($unavailable) {
			$this->session->data['error'] = sprintf($this->language->get('error_reorder_partial'), implode(', ', $unavailable));
		} else {
			$this->session->data['error'] = $this->language->get('error_reorder');
		}

		$this->response->redirect($this->url->link('account/order'));
	}

	private function isReorderableProduct($order_product, $product_info) {
		if ($this->isOrderProductGift($order_product, isset($order_product['order_id']) ? $order_product['order_id'] : 0, isset($order_product['order_product_id']) ? $order_product['order_product_id'] : 0)) {
			return false;
		}

		return $this->isProductInStock($product_info);
	}

	private function isProductInStock($product_info) {
		if (!$product_info) {
			return false;
		}

		if (empty($product_info['subtract'])) {
			return true;
		}

		// Online orders only from the 4 warehouses (product.quantity)
		$stock = (int)$product_info['quantity'];

		return $stock > 0;
	}

	private function isOrderProductGift($order_product, $order_id = 0, $order_product_id = 0) {
		// Free / promo gift lines from the original order
		if (isset($order_product['price']) && (float)$order_product['price'] <= 0) {
			return true;
		}

		if ($order_id && $order_product_id) {
			if (!isset($this->model_account_order)) {
				$this->load->model('account/order');
			}

			$options = $this->model_account_order->getOrderOptions($order_id, $order_product_id);

			foreach ($options as $option) {
				$value = isset($option['value']) ? (string)$option['value'] : '';
				$name = isset($option['name']) ? (string)$option['name'] : '';
				$type = isset($option['type']) ? (string)$option['type'] : '';

				if ($value === 'giftTeaser' || $name === 'giftTeaser' || $type === 'giftTeaser'
					|| (stripos($name, 'gift') !== false && stripos($name, 'teaser') !== false)) {
					return true;
				}

				if ($name === 'vapors_punch_gift' || strpos($value, 'vapors_punch') !== false) {
					return true;
				}
			}
		}

		if (!empty($order_product['product_id'])) {
			$this->load->model('extension/module/vapors_punch_gift');

			if ($this->model_extension_module_vapors_punch_gift->isActive()
				&& $this->model_extension_module_vapors_punch_gift->isGiftProduct((int)$order_product['product_id'])) {
				return true;
			}
		}

		return false;
	}
}