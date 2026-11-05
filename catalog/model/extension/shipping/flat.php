<?php
class ModelExtensionShippingFlat extends Model {
	function getQuote($address) {
		$this->load->language('extension/shipping/flat');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('shipping_flat_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('shipping_flat_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$cost = (float)$this->config->get('shipping_flat_cost');
			$sub_total = get_cart_amount_without_shipping($this->registry);
			$free_boy = (float)$this->config->get('config_free_boy');

			$is_free = false;

			if ($free_boy > 0 && $sub_total >= $free_boy) {
				$is_free = true;
			}

			if ($this->config->get('config_hh_freedelivery_on')) {
				$today = date_create(date('Y-m-d H:i'));
				$start_hh_date = date_create($this->config->get('config_hh_freedelivery_start'));
				$end_hh_date = date_create($this->config->get('config_hh_freedelivery_end'));

				if ($today >= $start_hh_date && $today <= $end_hh_date) {
					$is_free = true;
				}
			}

			if ($is_free) {
				$cost = 0;
			}

			$title = $this->language->get('text_description');
			$method_title = $this->language->get('text_title');

			$this->load->language('checkout/simplecheckout_summary');
			$text = $is_free
				? $this->language->get('text_bezkoshtovno')
				: $this->currency->format($this->tax->calculate($cost, $this->config->get('shipping_flat_tax_class_id'), $this->config->get('config_tax')), $this->session->data['currency']);

			$quote_data = array();

			$quote_data['flat'] = array(
				'code'         => 'flat.flat',
				'title'        => $title,
				'cost'         => $cost,
				'tax_class_id' => $this->config->get('shipping_flat_tax_class_id'),
				'text'         => $text
			);

			$method_data = array(
				'code'       => 'flat',
				'title'      => $method_title,
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('shipping_flat_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
}
