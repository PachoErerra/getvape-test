<?php
class ControllerExtensionModulereviews extends Controller {
	public function index() {
		$this->load->language('extension/module/reviews');

		$this->load->model('catalog/review_shop');
		
		$data['reviews'] = array();

		$review_total = $data['review_total'] = $this->model_catalog_review_shop->getTotalReviews();
		
		$data['review_data'] = $this->model_catalog_review_shop->getTotalReviewsCount();
		
		$sum_review = 0;
		foreach ($data['review_data']['r'] as $mark=>$qtty) {
			$sum_review += $qtty;
			
			$data['quality_data'][$mark] = round($qtty / $review_total * 20);
		}

		$data['mark'] = round(($sum_review / ($review_total * 4)) , 1);

		foreach ($data['review_data']['m'] as $mark=>$qtty) {
			$data['mark_data'][$mark] = round(100 / $data['review_data']['m']['t'] * $qtty);
		}
		unset($data['mark_data']['t']);

		
		$results = $this->model_catalog_review_shop->getReviews(0,6);

		foreach ($results as $result) {
			
			$rating = ((int)$result['quality'] + (int)$result['price'] + (int)$result['delivery'] + (int)$result['price']) * 5;
			
			$data['reviews'][] = array(
				'author'     => $result['author'],
				'text'       => nl2br($result['text']),
				'answer'       => nl2br($result['answer']),
				'rating'     => $rating,
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added']))
			);
		}
		$data['logged'] = $this->customer->isLogged();
		$data['name'] = $this->customer->getFirstName();
		$data['all'] = $this->url->link('information/reviews');

		return $this->load->view('extension/module/reviews', $data);
	}
}