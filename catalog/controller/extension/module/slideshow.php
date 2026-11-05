<?php

class ControllerExtensionModuleSlideshow extends Controller
{
    public function index($setting)
    {
        static $module = 0;

        $this->load->language('extension/module/slideshow');

        $this->load->model('design/banner');
        $this->load->model('tool/image');

        //	$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/swiper.min.css');
        //	$this->document->addStyle('catalog/view/javascript/jquery/swiper/css/opencart.css');
        //	$this->document->addScript('catalog/view/javascript/jquery/swiper/js/swiper.jquery.js');

        if ($setting['banner_id'] == 10) {
            $this->document->addStyle('catalog/view/theme/default/stylesheet/style.min.css');
        }

        $data['banners'] = array();

        $data['instagram'] = $this->config->get('config_instagram');


        $data['instagramtext'] = trim(parse_url($data['instagram'], PHP_URL_PATH), '/');
//echo $data['instagramtext'];
        $results = $this->model_design_banner->getBanner($setting['banner_id']);

        if ($setting['banner_id'] == 10) {
            foreach ($results as $result) {

                $link = str_replace('[telegram]', $this->config->get('config_telegram'), $result['link']);

                $title_raw = html_entity_decode($result['title'], ENT_QUOTES, 'UTF-8');
                $safe_title = strip_tags($title_raw, '<small><b><strong><i><em><u><br>');


                $link = str_replace('[instagram]', $this->config->get('config_instagram'), $link);

                if (!empty($result['image'])) {
                    $data['banners'][] = array(
                        'title' => $safe_title,
                        'link' => $link,
                        'image' => HTTPS_SERVER . 'image/' . $result['image'],
                        //	'image' => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']),
                        'color' => $result['color'],
                        'newtab' => $result['newtab'],
                        'full_width' => (string)$result['full_width'],

                    );
                }
            }
        } else {
            foreach ($results as $result) {

                $link = str_replace('[telegram]', $this->config->get('config_telegram'), $result['link']);


                $link = str_replace('[instagram]', $this->config->get('config_instagram'), $link);
                if (is_file(DIR_IMAGE . $result['image'])) {
                    $data['banners'][] = array(
                        'title' => $result['title'],
                        'link' => $link,
                        'image' => $this->model_tool_image->resize($result['image'], $setting['width'], $setting['height']),
                        'image_mob' => $this->model_tool_image->resize($result['image_mob'], $setting['width_mob'], $setting['height_mob']),
                        'newtab' => $result['newtab'],
                        'full_width' => $result['full_width'],

                    );
                }
            }
        }

        $data['module'] = $module++;

        if ($setting['banner_id'] == 9) {
            return $this->load->view('extension/module/slideshow_insta', $data);
        } else if ($setting['banner_id'] == 10) {
            return $this->load->view('extension/module/slideshow_hello', $data);
        } else if ($setting['banner_id'] == 12) {
            return $this->load->view('extension/module/slideshow_promo', $data);
        } else {
            return $this->load->view('extension/module/slideshow', $data);
        }
    }
}