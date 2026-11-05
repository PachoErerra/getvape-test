<?php
class ControllerExtensionModuleMaxBrends extends Controller {
	public function index($setting) {
		static $module = 0;
		$this->load->language('module/max_brends');

		$this->load->model('catalog/manufacturer');

		$this->load->model('tool/image');

		$data['manufacturers'] = array();

		if (!$setting['limit']) {
			$setting['limit'] = 4;
		}
		if (isset($setting['manufacturer_description'][$this->config->get('config_language_id')])) {
			$data['heading_title'] = html_entity_decode($setting['manufacturer_description'][$this->config->get('config_language_id')]['title'], ENT_QUOTES, 'UTF-8');
			$data['html'] = html_entity_decode($setting['manufacturer_description'][$this->config->get('config_language_id')]['description'], ENT_QUOTES, 'UTF-8');
		}
		
		if (!empty($setting['manufacturer'])) {
			$manufacturers = array_slice($setting['manufacturer'], 0, (int)$setting['limit']);

			foreach ($manufacturers as $manufacturer_id) {
				$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($manufacturer_id);

				if ($manufacturer_info) {
					if ($manufacturer_info['image']) {
						$image = $this->model_tool_image->resize($manufacturer_info['image'], $setting['width'], $setting['height']);
					} else {
						$image = $this->model_tool_image->resize('placeholder.png', $setting['width'], $setting['height']);
					}


					$data['manufacturers'][] = array(
						'manufacturer_id'  => $manufacturer_info['manufacturer_id'],
						'thumb'       => $image,
						'name'        => $manufacturer_info['name'],
						'href'        => $this->url->link('product/manufacturer/info', 'manufacturer_id=' . $manufacturer_info['manufacturer_id'])
					);
				}
			}
		}
        $data['module'] = $module++;
		if ($data['manufacturers']) {

			return $this->load->view('extension/module/max_brends', $data);

		}
	}
}