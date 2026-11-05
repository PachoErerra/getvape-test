<?php
class ModelExtensionTotalReward extends Model {

	public function getTotal($total) {
		if (isset($this->session->data['reward'])) {
			$this->load->language('extension/total/reward', 'reward');

			$points = $this->customer->getRewardPoints();

			if ($this->session->data['reward'] <= $points) {
				$discount_total = 0;
				$points_total = 0;

				// --- НАСТРОЙКИ ОГРАНИЧЕНИЙ ---
				$restricted_categories = [4237]; 
				$restricted_products = [18565];    
				$exception_products = [18404, 18405, 18406]; 
				
				// ИСКЛЮЧАЕМ ПОДАРКИ: считаем только товары с ценой > 0
				$total_quantity = 0;
				foreach ($this->cart->getProducts() as $product) {
					if ((float)$product['price'] > 0) {
						$total_quantity += $product['quantity'];
					}
				}
				$apply_restriction = ($total_quantity < 3);
				// ------------------------------

				$this->load->model('catalog/product');

				$restricted_cart_ids = [];

				// Первый цикл: классифицируем товары
				foreach ($this->cart->getProducts() as $product) {
					
					// Если это подарок — блокируем для него участие в бонусах и пропускаем проверки
					if ((float)$product['price'] <= 0) {
						$restricted_cart_ids[$product['cart_id']] = true;
						continue;
					}

					$is_restricted = false;

					if ($apply_restriction) {
						if (in_array($product['product_id'], $exception_products)) {
							$is_restricted = false;
						}
						elseif (in_array($product['product_id'], $restricted_products)) {
							$is_restricted = true;
						} 
						else {
							$product_categories = $this->model_catalog_product->getCategories($product['product_id']);
							foreach ($product_categories as $category) {
								if (in_array($category['category_id'], $restricted_categories)) {
									$is_restricted = true;
									break;
								}
							}
						}
					}

					if ($is_restricted) {
						$restricted_cart_ids[$product['cart_id']] = true;
					} else {
						$restricted_cart_ids[$product['cart_id']] = false;
						
						if ($product['points']) {
							$points_total += $product['points'];
						}
					}
				}

				if ($points_total <= 0) {
					return;
				}

				$points = min($points, $points_total);
				$reward_applied = min($this->session->data['reward'], $points_total);

				// Второй цикл: распределение скидки
				foreach ($this->cart->getProducts() as $product) {
					$discount = 0;

					if (!$restricted_cart_ids[$product['cart_id']] && $product['points']) {
						$discount = $product['total'] * ($reward_applied / $points_total);

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
				
				$discount_total = $reward_applied;

				$total['totals'][] = array(
					'code'       => 'reward',
					'title'      => sprintf($this->language->get('reward')->get('text_reward'), $reward_applied),
					'value'      => -$discount_total,
					'sort_order' => $this->config->get('total_reward_sort_order')
				);

				$total['total'] -= $discount_total;
			}
		}
	}

	public function confirm($order_info, $order_total) {
		// Бонуси списує і нараховує лише 1С (часто наступного дня).
		// Сайт тільки пише рядок знижки в order_total на чекауті.
		// НЕ чіпаємо oc_customer_reward і НЕ повертаємо config_fraud_status_id —
		// інакше при «Доставлено/Завершено» замовлення помилково стає «Анульоване».
		return;
	}

	public function unconfirm($order_id) {
		// Повернення бонусів теж на боці 1С — сайт нічого не відкочує.
		return;
	}
}
