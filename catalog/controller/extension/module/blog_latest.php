<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerExtensionModuleBlogLatest extends Controller {
	public function index($setting) {
		$this->load->language('extension/module/blog_latest');

		$this->load->model('blog/article');

		$this->load->model('tool/image');

		$data['articles'] = array();
		
		$data['blog'] = $this->url->link('blog/latest');

//        $this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
//        $this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
//        $this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');


        $data['sprite_version'] = $this->registry->get('sprite_version');

        $filter_data = array(
			'sort'  => 'p.date_added',
			'order' => 'DESC',
			'start' => 0,
			'limit' => $setting['limit']
		);

		$results = $this->model_blog_article->getArticles2($filter_data);

		if ($results) {
			foreach ($results as $result) {
                

				if ($result['image']) {
					$image = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
                    $imageLg = $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', 800, 800);
                    $imageLg = $this->model_tool_image->resize('placeholder.png', 800, 800);
				}

				if ($this->config->get('configblog_review_status')) {
					$rating = $result['rating'];
				} else {
					$rating = false;
				}

				$data['articles'][] = array(
					'article_id'  => $result['article_id'],
					'thumb'       => $image,
					'imageLg'     => $imageLg,
					'name'        => $result['name'],
					'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('configblog_article_description_length')) . '..',
					'rating'      => $rating,
					'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
					'viewed'      => $result['viewed'],
					'href'        => $this->url->link('blog/article', 'article_id=' . $result['article_id'])
				);
			}

			return $this->load->view('extension/module/blog_latest', $data);
		}
	}
}
