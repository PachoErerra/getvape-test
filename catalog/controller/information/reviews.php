<?php
class ControllerInformationReviews extends Controller {
    public function index() {
        $this->load->language('information/reviews');

        $this->document->setTitle($this->language->get('title'));
        $this->document->setDescription($this->language->get('description'));

        $data['heading_title'] = $this->language->get('heading_title');

        // Загрузка шаблона
        $data['breadcrumbs'] = [];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        ];

        $data['breadcrumbs'][] = [
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('information/reviews')
        ];

		$this->load->model('catalog/review_shop');

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

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


		$results = $this->model_catalog_review_shop->getReviews(($page - 1) * 12, 12);

		foreach ($results as $result) {
			
			$rating = ((int)$result['quality'] + (int)$result['price'] + (int)$result['delivery'] + (int)$result['price']) * 5;

            $date_added = strtotime($result['date_added']);
            $today = strtotime('today'); // сегодняшняя дата

// Если дата добавления не сегодняшняя, добавляем 1 день
            if ($date_added != $today) {
                $date_answered = strtotime('+1 day', $date_added);
            } else {
                $date_answered = $date_added; // оставляем сегодняшнюю дату
            }


            $data['reviews'][] = array(
				'author'     => $result['author'],
				'text'       => nl2br($result['text']),
				'answer'       => nl2br($result['answer']),
				'rating'     => $rating,
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_answered' => date('d.m.Y', $date_answered)
			);
		}

		$pagination = new Pagination();
		$pagination->total = $review_total;
		$pagination->page = $page;
		$pagination->limit = 12;
		$pagination->url = $this->url->link('information/reviews', '&page={page}');

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));
		
		$data['logged'] = $this->customer->isLogged();
		
		$data['name'] = $this->customer->getFirstName();
		
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['continue'] = $this->url->link('common/home');

        $data['sprite_version'] = $this->registry->get('sprite_version');

        $this->response->setOutput($this->load->view('information/reviews', $data));
    }
	
	public function write() {
		$this->load->language('information/reviews');

		$json = array();

		if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			
			if (empty($this->request->post['support']) || $this->request->post['support'] < 0 || $this->request->post['support'] > 5) {
				$json['error'] = $this->language->get('error_support');
			}
			
			if (empty($this->request->post['price']) || $this->request->post['price'] < 0 || $this->request->post['price'] > 5) {
				$json['error'] = $this->language->get('error_price');
			}
			
			if (empty($this->request->post['delivery']) || $this->request->post['delivery'] < 0 || $this->request->post['delivery'] > 5) {
				$json['error'] = $this->language->get('error_delivery');
			}
			
			if (empty($this->request->post['quality']) || $this->request->post['quality'] < 0 || $this->request->post['quality'] > 5) {
				$json['error'] = $this->language->get('error_quality');
			}
			
			if (($this->request->post['text'] < 4) && ((utf8_strlen($this->request->post['text']) < 25) || (utf8_strlen($this->request->post['text']) > 1000))) {
				$json['error'] = $this->language->get('error_text');
			}
			
			if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
				$json['error'] = $this->language->get('error_name');
			}


			// Captcha
			if ($this->config->get('captcha_' . $this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
				$captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

				if ($captcha) {
					$json['error'] = $captcha;
				}
			}

			if (!isset($json['error'])) {
				$this->load->model('catalog/review_shop');

				$this->model_catalog_review_shop->addReview($this->request->post);

				$json['success'] = $this->language->get('text_success');
			}
		}



		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}
