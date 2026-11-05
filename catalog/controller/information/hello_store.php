<?php

class ControllerInformationHelloStore extends Controller {
	const HELLO_INFORMATION_ID = 8;
	const PROMO_CODE = 'OKSANA10';

	public function index() {
		$pretty_url = $this->url->link('information/hello_store', '', true);

		if (strpos($pretty_url, 'index.php') === false) {
			$request_uri = isset($this->request->server['REQUEST_URI']) ? $this->request->server['REQUEST_URI'] : '';

			if (strpos($request_uri, 'index.php') !== false) {
				$this->response->redirect($pretty_url, 301);
				return;
			}
		}

		$this->load->language('information/information');
		$this->load->language('information/information_hello');

		$this->load->model('catalog/information');

		$information_info = $this->model_catalog_information->getInformation(self::HELLO_INFORMATION_ID);

		if (!$information_info) {
			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
			$this->response->redirect($this->url->link('error/not_found'));
			return;
		}

		if ($information_info['meta_title']) {
			$this->document->setTitle($information_info['meta_title']);
		} else {
			$this->document->setTitle($information_info['title']);
		}

		$this->document->setRobots('noindex, nofollow');
		$this->response->addHeader('X-Robots-Tag: noindex, nofollow');

		if ($information_info['meta_h1']) {
			$data['heading_title'] = $information_info['meta_h1'];
		} else {
			$data['heading_title'] = $information_info['title'];
		}

		$this->document->setDescription($information_info['meta_description']);
		$this->document->setKeywords($information_info['meta_keyword']);

		$data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');

		$data['tiktok'] = $this->config->get('config_tiktok');
		$data['instagram'] = $this->config->get('config_instagram');
		$data['youtube'] = $this->config->get('config_youtube');
		$data['telegram'] = $this->config->get('config_telegram');
		$data['year'] = date('Y');
		$data['hello_css_version'] = filemtime(DIR_TEMPLATE . 'default/stylesheet/hello.min.css');
		$data['page_url'] = $pretty_url;

		$data['promo_code'] = self::PROMO_CODE;
		$data['buy_url'] = 'https://getvape.com.ua/';

		$data = array_merge($data, $this->buildHelloPageMeta($data['heading_title'], $information_info));

		$data['content_top'] = $this->load->controller('extension/module/slideshow', array(
			'banner_id' => 10,
			'width' => 0,
			'height' => 0,
			'status' => 1
		));

		$this->response->setOutput($this->load->view('information/information_hello_store', $data));
	}

	private function buildHelloPageMeta($heading_title, $information_info) {
		$title_suffix = ' | Електронні сигарети, вейпи: купити в Україні - інтернет магазин Vapors';
		$description_suffix = ' | вейпи, електронні сигарети та аксесуари до них ✅️ Великий вибір ✅️ Офіційна гарантія ✅️ Швидка доставка';

		$document_title = trim((string)(isset($information_info['meta_title']) ? $information_info['meta_title'] : '')) !== ''
			? $information_info['meta_title']
			: $heading_title . $title_suffix;

		$meta_description = trim((string)(isset($information_info['meta_description']) ? $information_info['meta_description'] : '')) !== ''
			? $information_info['meta_description']
			: $heading_title . $description_suffix;

		return array(
			'hello_document_title' => $document_title,
			'hello_meta_description' => $meta_description,
			'hello_meta_og_title' => $document_title,
		);
	}
}
