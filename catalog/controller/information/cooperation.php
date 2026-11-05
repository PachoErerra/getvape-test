<?php
class ControllerInformationCooperation extends Controller {
    public function index() {
        $this->load->language('information/cooperation');

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
            'href' => $this->url->link('information/cooperation')
        ];



        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
		$data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $data['continue'] = $this->url->link('common/home');

        $data['sprite_version'] = $this->registry->get('sprite_version');

        $this->response->setOutput($this->load->view('information/cooperation', $data));
    }
	
}
