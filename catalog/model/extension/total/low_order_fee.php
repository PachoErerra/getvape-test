<?php
class ModelExtensionTotalLowOrderFee extends Model {
	public function getTotal($total) {
		
		$discount = $this->customer->getDiscount();
		
		if ($discount && $this->cart->getSubTotal() && isset($this->session->data['discount']) && $this->session->data['discount']) {

				$this->load->language('extension/total/low_order_fee');
				
				$p_discount = - $this->cart->getSubTotal() / 100 * $discount;

				$total['totals'][] = array(
					'code'       => 'low_order_fee',
					'title'      => sprintf($this->language->get('text_low_order_fee'), $discount.'%'),
					'value'      => $p_discount,
					'sort_order' => $this->config->get('total_low_order_fee_sort_order')
				);

				$total['total'] += $p_discount;

		}
	}
}