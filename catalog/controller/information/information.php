<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerInformationInformation extends Controller {
	public function index() {
		$this->load->language('information/information');

		$this->load->model('catalog/information');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/home')
		);

		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}
        $data['tiktok'] = $this->config->get('config_tiktok');
        $data['instagram'] = $this->config->get('config_instagram');
        $data['youtube'] = $this->config->get('config_youtube');
        $data['telegram'] = $this->config->get('config_telegram');
		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			
			if ($information_info['meta_title']) {
				$this->document->setTitle($information_info['meta_title']);
			} else {
				$this->document->setTitle($information_info['title']);
			}
			
			if ($information_info['noindex'] <= 0 && $this->config->get('config_noindex_status')) {
				$this->document->setRobots('noindex,follow');
			}
			
			if ($information_info['meta_h1']) {
				$data['heading_title'] = $information_info['meta_h1'];
			} else {
				$data['heading_title'] = $information_info['title'];
			}
			
			$this->document->setDescription($information_info['meta_description']);
			$this->document->setKeywords($information_info['meta_keyword']);

			$data['breadcrumbs'][] = array(
				'text' => $information_info['title'],
				'href' => $this->url->link('information/information', 'information_id=' .  $information_id)
			);

$this->document->addLink($this->url->link('information/information', 'information_id=' .  $information_id), 'canonical');

			$data['description'] = html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8');

			$data['continue'] = $this->url->link('common/home');

			if ($information_id == 10) {
				$data['regsms'] = $this->load->controller('extension/module/regsms');
				$data['logged'] = $this->customer->isLogged();
				
				$data['used'] = false;
				$data['code'] = false;
				if ($data['logged']) {
					$this->load->model('extension/module/fortune');
						$win =  $this->load->controller('extension/module/fortune/checkwinserversock');
						if ($win) {
							if ($win['used']) {
								$data['used'] = true;
								//выводим что подарок уже использован
							} else if (strtotime($win['date_valid']) < time()) {
								//если просрочен даем еще раз
								$data['used'] = false;
							} else {
								//если действует направляем в купоны
								$data['code'] = $this->url->link('account/promocodes', '', true);
								$data['used'] = false;
							}
						} else {
							$data['used'] = false;
						}
						$data['addsock'] = $this->url->link('extension/module/fortune/addsock');
				}
			}

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			if ($information_id == 8) {
				$this->load->language('information/information_hello');
				$data = array_merge($data, $this->buildHelloPageMeta($data['heading_title'], $information_info));

				$data['content_top'] = $this->load->controller('extension/module/slideshow', array(
					'banner_id' => 10,
					'width' => 0,
					'height' => 0,
					'status' => 1
				));
			}
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');
            $data['hello_css_version'] = filemtime(DIR_TEMPLATE . 'default/stylesheet/hello.min.css');

			if ($information_id == 8) {
				$data['year'] = date('Y', time());				
				$this->response->setOutput($this->load->view('information/information_hello', $data));
			} else if ($information_id == 10) {
				$this->response->setOutput($this->load->view('information/information_socks', $data));
			} else {
				$this->response->setOutput($this->load->view('information/information', $data));
			}
		} else {
			$data['breadcrumbs'][] = array(
				'text' => $this->language->get('text_error'),
				'href' => $this->url->link('information/information', 'information_id=' . $information_id)
			);

			$this->document->setTitle($this->language->get('text_error'));

			$data['heading_title'] = $this->language->get('text_error');

			$data['text_error'] = $this->language->get('text_error');

			$data['continue'] = $this->url->link('common/home');

			$this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

			$data['column_left'] = $this->load->controller('common/column_left');
			$data['column_right'] = $this->load->controller('common/column_right');
			$data['content_top'] = $this->load->controller('common/content_top');
			$data['content_bottom'] = $this->load->controller('common/content_bottom');
			$data['footer'] = $this->load->controller('common/footer');
			$data['header'] = $this->load->controller('common/header');

			$this->response->setOutput($this->load->view('error/not_found', $data));
		}
	}

	public function agree() {
		$this->load->model('catalog/information');

		if (isset($this->request->get['information_id'])) {
			$information_id = (int)$this->request->get['information_id'];
		} else {
			$information_id = 0;
		}

		$output = '';

		$information_info = $this->model_catalog_information->getInformation($information_id);

		if ($information_info) {
			$output .= html_entity_decode($information_info['description'], ENT_QUOTES, 'UTF-8') . "\n";
		}

		$this->response->addHeader('X-Robots-Tag: noindex');

		$this->response->setOutput($output);
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
