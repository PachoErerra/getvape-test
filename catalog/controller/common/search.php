<?php
class ControllerCommonSearch extends Controller {
	public function index() {
		$this->load->language('common/search');

		$data['text_search'] = $this->language->get('text_search');

		if (isset($this->request->get['search'])) {
			$data['search'] = $this->request->get['search'];
		} else {
			$data['search'] = '';
		}

		$data['search_placeholders'] = array();

		$raw_tags = (string)$this->config->get('config_search_tag');
		$searches = preg_split('/[\n,]+/', $raw_tags);
		$searches = array_values(array_filter(array_map('trim', $searches), 'strlen'));

		if ($searches) {
			$data['search_placeholders'] = $searches;
		}

		$data['search_placeholders_json'] = json_encode($data['search_placeholders'], JSON_UNESCAPED_UNICODE);

		return $this->load->view('common/search', $data);
	}
}
