<?php
class ControllerExtensionModuleBanner extends Controller {
	public function index($setting) {
		static $module = 0;

		$this->load->model('design/banner');
		$this->load->model('tool/image');

		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
		$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
		$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');

		$data['banners'] = array();

		$results = $this->model_design_banner->getBanner($setting['banner_id']);

		foreach ($results as $result) {
			if (!empty($result['image'])) { // ТЕСТ: картинок немає на тестовому хостингу, беремо з getvape.com.ua
				
			$link = str_replace('[telegram]',$this->config->get('config_telegram'),$result['link']);
			
			$link = str_replace('[instagram]',$this->config->get('config_instagram'),$link);				
				
				
				$data['banners'][] = array(
					'title' => $result['title'],
					'link'  => $link,
					'image' => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height'])
				);
			}
		}

		$data['module'] = $module++;

		return $this->load->view('extension/module/banner', $data);
	}
}