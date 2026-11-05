<?php
class ControllerExtensionModuleRegsms extends Controller {
	private $error = array();
	private $host = 'https://sms.targetsms.ru/sendsmsjson.php'; 

	public function index() {
		$this->load->language('extension/module/regsms');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {			

			$this->model_setting_setting->editSetting('module_regsms', $this->request->post);
				
			

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
		}

		$data['action'] = $this->url->link('extension/module/regsms', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);

		$data['token'] = $this->session->data['user_token'];

		

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/regsms', 'user_token=' . $this->session->data['user_token'], true)
		);	

		if (isset($this->request->post['module_regsms_status'])) {
	    	$data['module_regsms_status'] = $this->request->post['module_regsms_status'];
	    } else {
	    	$data['module_regsms_status'] = $this->config->get('module_regsms_status'); 
	    }	

		if (isset($this->request->post['module_regsms_login'])) {
	    	$data['module_regsms_login'] = $this->request->post['module_regsms_login'];
	    } else {
	    	$data['module_regsms_login'] = $this->config->get('module_regsms_login'); 
	    }

	    if (isset($this->request->post['module_regsms_password'])) {
	    	$data['module_regsms_password'] = $this->request->post['module_regsms_password'];
	    } else {
	    	$data['module_regsms_password'] = $this->config->get('module_regsms_password');
	    }

	    if (isset($this->request->post['module_regsms_from'])) {
	    	$data['module_regsms_from'] = $this->request->post['module_regsms_from'];
	    } else {
	    	$data['module_regsms_from'] = $this->config->get('module_regsms_from');
	    }

	    if ($data['module_regsms_login'] && $data['module_regsms_password']) {
	    	$data['senders'] = $this->getSenders($data['module_regsms_login'],$data['module_regsms_password']);
	    	$data['balance'] = $this->getBalance($data['module_regsms_login'],$data['module_regsms_password']);	
	    	if (!$data['balance']) {
	    		$data['balance'] = '0.00 руб.';
	    	} 
	    } else {
	    	$data['senders'] = array();
	    	$data['balance'] = '0.00 руб.';
	    }

	    if (isset($this->request->post['module_regsms_text_button'])) {
	    	$data['module_regsms_text_button'] = $this->request->post['module_regsms_text_button'];
	    } else if ($this->config->get('module_regsms_text_button')) {
	    	$data['module_regsms_text_button'] = $this->config->get('module_regsms_text_button');
	    } else {
	    	$data['module_regsms_text_button'] = $this->language->get('entry_regsms_text_button_default');
	    }

	    if (isset($this->request->post['module_regsms_count_code'])) {
	    	$data['module_regsms_count_code'] = $this->request->post['module_regsms_count_code'];
	    } else if ($this->config->get('module_regsms_count_code')) {
	    	$data['module_regsms_count_code'] = $this->config->get('module_regsms_count_code');
	    } else {
	    	$data['module_regsms_count_code'] = 4;
	    }

	    if (isset($this->request->post['module_regsms_time_session'])) {
	    	$data['module_regsms_time_session'] = $this->request->post['module_regsms_time_session'];
	    } else if ($this->config->get('module_regsms_time_session')) {
	    	$data['module_regsms_time_session'] = $this->config->get('module_regsms_time_session');
	    } else {
	    	$data['module_regsms_time_session'] = 120;
	    }

	    if (isset($this->request->post['module_regsms_text_sms'])) {
	    	$data['module_regsms_text_sms'] = $this->request->post['module_regsms_text_sms'];
	    } else if ($this->config->get('module_regsms_text_sms')) {
	    	$data['module_regsms_text_sms'] = $this->config->get('module_regsms_text_sms');
	    } else {
	    	$data['module_regsms_text_sms'] = $this->language->get('entry_regsms_text_sms_default');
	    }

	    $this->load->model('tool/image');
	    $data['no_image'] = $this->model_tool_image->resize('no_image.png', 30, 30);

	    if (isset($this->request->post['module_regsms_mask'])) {
	    	$data['module_regsms_mask'] = $this->request->post['module_regsms_mask'];
	    } else if ($this->config->get('module_regsms_mask')) {
	    	$data['module_regsms_mask'] = $this->config->get('module_regsms_mask');
	    	foreach ($data['module_regsms_mask'] as $key => $mask) {
	    		if (is_file(DIR_IMAGE . $mask['image'])) {
					$thumb = $this->model_tool_image->resize($mask['image'], 30, 30);
				} else {
					$thumb = $this->model_tool_image->resize('no_image.png', 30, 30);
				}
				$data['module_regsms_mask'][$key]['thumb'] = $thumb;
	    	}
	    } else {
	    	$data['module_regsms_mask'] = array(
	    		array(
	    			'region'		=> 'Россия',
	    			'mask'			=> '+7 (999) 999 99-99',
	    			'placeholder'	=> 'Введите номер согласно коду России',
	    			'status'		=> 1,
	    			'image'			=> 'catalog/regsms/ru.png',
	    			'thumb'			=> $this->model_tool_image->resize('catalog/regsms/ru.png', 30, 30)
	    		),
	    		array(
	    			'region'		=> 'Украина',
	    			'mask'			=> '+38(999) 999-99-99',
	    			'placeholder'	=> 'Введите номер согласно коду Украины',
	    			'status'		=> 1,
	    			'image'			=> 'catalog/regsms/ua.png',
	    			'thumb'			=> $this->model_tool_image->resize('catalog/regsms/ua.png', 30, 30)
	    		),array(
	    			'region'		=> 'Белоруссия',
	    			'mask'			=> '+375(99)999-99-99',
	    			'placeholder'	=> 'Введите номер согласно коду Белоруссии',
	    			'status'		=> 1,
	    			'image'			=> 'catalog/regsms/by.png',
	    			'thumb'			=> $this->model_tool_image->resize('catalog/regsms/by.png', 30, 30)
	    		),array(
	    			'region'		=> 'Казахстан',
	    			'mask'			=> '+7 (999) 999 99-99',
	    			'placeholder'	=> 'Введите номер согласно коду Казахстана',
	    			'status'		=> 1,
	    			'image'			=> 'catalog/regsms/kz.png',
	    			'thumb'			=> $this->model_tool_image->resize('catalog/regsms/kz.png', 30, 30)
	    		)
	    	);
	    }

	    if (isset($this->request->post['module_regsms_offer'])) {
	    	$data['module_regsms_offer'] = $this->request->post['module_regsms_offer'];
	    } else {
	    	$data['module_regsms_offer'] = $this->config->get('module_regsms_offer');
	    } 


		$this->document->addStyle('view/stylesheet/regsms.css');

	    //CKEditor
	    if ($this->config->get('config_editor_default')) {
	        $this->document->addScript('view/javascript/ckeditor/ckeditor.js');
	        $this->document->addScript('view/javascript/ckeditor/ckeditor_init.js');
	    }

		$data['ckeditor'] = $this->config->get('config_editor_default');

		$data['lang'] = $this->language->get('lang');


		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');


		$this->response->setOutput($this->load->view('extension/module/regsms', $data));

	}

	public function connect() {

		$json = $this->getSenders($this->request->post['module_regsms_login'],$this->request->post['module_regsms_password']);
		$json['balance'] = $this->getBalance($this->request->post['module_regsms_login'],$this->request->post['module_regsms_password']) . ' руб.';
		$this->response->setOutput(json_encode($json));
	}

	public function getSenders($login,$password) {

		$param = array(
            'security' 	=> array('login' => $login, 'password' 	=> $password),
            'type' 		=> 'originator'
        );

        $result = $this->getRequest($param);
        return $result;
	}

	public function getBalance($login,$password) {

        $param = array(
			'security' 	=> array('login' => $login, 'password' 	=> $password),
			'type' 		=> 'balance'
		);

        $result = $this->getRequest($param);  
		if (isset($result['money']['value'])) {
			return $result['money']['value'];
		} else {
			return '0.00';
		}
        
	}

	public function getRequest($param) {

		$param_json = json_encode($param, true);      
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','charset=utf8','Expect:'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param_json);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_URL, $this->host);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $res = curl_exec($ch);
        $result = json_decode($res, true);
        curl_close($ch);

        return $result;

	}


	public function getMessages() {

		$param = array(
			'security'	=> array('login' => $this->request->post['module_regsms_login'], 'password' => $this->request->post['module_regsms_password']),
			'type' 		=> 'list_stats',
			'stats'		=> array('date_start' => $this->request->post['date_start'], 'date_stop' => $this->request->post['date_stop'], 'state' => $this->request->post['state'], 'originator' => $this->request->post['module_regsms_from'])
		);

		$json = $this->getRequest($param);
        $this->response->setOutput(json_encode($json));

	}	

	

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/regsms')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function install() {

		

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "regsms` ( 
			`regsms_id` INT NOT NULL AUTO_INCREMENT , 
			`telephone` VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , 
			`code` VARCHAR(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL , 
			`time` INT NOT NULL , 
			PRIMARY KEY (`regsms_id`)) ENGINE = InnoDB;");

	}

	public function uninstall() {
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "regsms`");
	}


}