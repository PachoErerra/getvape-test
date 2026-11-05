<?php
class ControllerExtensionAnalyticsDGoogleAnalytics extends Controller {
	private $codename = 'd_google_analytics';
	private $route = 'extension/analytics/d_google_analytics';
	private $config_file = 'd_google_analytics';
	private $prefix = '';
	private $extension = array();
	private $error = array(); 
			
	public function __construct($registry) {
        parent::__construct($registry);

        $this->load->model('extension/d_opencart_patch/url');
        $this->load->model('extension/d_opencart_patch/user');
        $this->load->model('extension/d_opencart_patch/load');
        
        $this->d_shopunity = (file_exists(DIR_SYSTEM . 'library/d_shopunity/extension/d_shopunity.json'));
        $this->d_shopunity = (file_exists(DIR_SYSTEM.'library/d_shopunity/extension/d_shopunity.json'));
        $this->d_twig_manager = (file_exists(DIR_SYSTEM.'library/d_shopunity/extension/d_twig_manager.json'));
        $this->d_validator = (file_exists(DIR_SYSTEM . 'library/d_shopunity/extension/d_validator.json'));
        $this->prefix = (VERSION > '3.0.0.0') ? 'analytics_' : '';

        $this->extension = json_decode(file_get_contents(DIR_SYSTEM . 'library/d_shopunity/extension/' . $this->codename . '.json'), true);
    }

    public function index() {
		$this->load->language($this->route);
		
		$this->load->model('setting/setting');

        if($this->d_shopunity){
            $this->load->model('extension/d_shopunity/mbooth');
            $this->model_extension_d_shopunity_mbooth->validateDependencies($this->codename);
        }

        if($this->d_twig_manager){
            $this->load->model('extension/module/d_twig_manager');
            $this->model_extension_module_d_twig_manager->installCompatibility();
        }

        if ($this->d_validator) {
            $this->load->model('extension/d_shopunity/d_validator');
            $this->model_extension_d_shopunity_d_validator->installCompatibility();
        }
        
        // Styles and Scripts
//        $this->document->addStyle('view/stylesheet/d_bootstrap_extra/bootstrap.css');
//        $this->document->addScript('view/javascript/d_bootstrap_switch/js/bootstrap-switch.min.js');
//        $this->document->addStyle('view/javascript/d_bootstrap_switch/css/bootstrap-switch.css');
//		$this->document->addStyle('view/stylesheet/d_admin_style/core/normalize/normalize.css');
//		$this->document->addStyle('view/stylesheet/d_admin_style/themes/light/light.css');
        
        // Heading
        $this->document->setTitle($this->language->get('heading_title_main'));
        $data['heading_title'] = $this->language->get('heading_title_main');
        
        // Variable
        $data['codename'] = $this->codename;
        $data['route'] = $this->route;
        $data['version'] = $this->extension['version'];
        $data['prefix'] = $this->prefix;
        $data['config'] = $this->config_file;
        $data['d_shopunity'] = $this->d_shopunity;
        $data['token'] =  $this->model_extension_d_opencart_patch_user->getToken();
        $data['token_url'] =  $this->model_extension_d_opencart_patch_user->getUrlToken();
        
        if (isset($this->request->server['HTTPS']) && (($this->request->server['HTTPS'] == 'on') || ($this->request->server['HTTPS'] == '1'))) {
            $data['server'] = HTTPS_SERVER;
			$data['catalog'] = HTTPS_CATALOG;
        } else {
            $data['server'] = HTTP_SERVER;
			$data['catalog'] = HTTP_CATALOG;
        }
                
        // Action
        $data['module_link'] = $this->model_extension_d_opencart_patch_url->link($this->route);
        $data['action'] = $this->model_extension_d_opencart_patch_url->link($this->route . '/save');
        $data['cancel'] = $this->model_extension_d_opencart_patch_url->getExtensionLink('analytics');
		
		// Button
		$data['button_save'] = $this->language->get('button_save');
        $data['button_save_and_stay'] = $this->language->get('button_save_and_stay');
		
		// Entry
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_code'] = $this->language->get('entry_code');
		
		// Text
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_analytics'] = $this->language->get('text_analytics');

		// Notification
        foreach ($this->error as $key => $error) {
            $data['error'][$key] = $error;
        }
        
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->model_extension_d_opencart_patch_url->link('common/dashboard')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_analytics'),
            'href' => $this->model_extension_d_opencart_patch_url->getExtensionLink('analytics')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title_main'),
            'href' => $this->model_extension_d_opencart_patch_url->link($this->route)
        );

        // Setting
        $this->config->load($this->config_file);
        $data['setting'] = ($this->config->get($this->codename)) ? $this->config->get($this->codename) : array();
        
        $setting = $this->model_setting_setting->getSetting($this->prefix . $this->codename);
        $status = isset($setting[$this->prefix . $this->codename . '_status']) ? $setting[$this->prefix . $this->codename . '_status'] : false;
        $setting = isset($setting[$this->prefix . $this->codename . '_setting']) ? $setting[$this->prefix . $this->codename . '_setting'] : array();
        
        $data['status'] = $status;
        
        if (!empty($setting)) {
            $data['setting'] = array_replace_recursive($data['setting'], $setting);
        }
        
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->model_extension_d_opencart_patch_load->view($this->route, $data));
	}

	 public function save() {
        $this->load->language($this->route);
        
        $this->load->model('setting/setting');
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting($this->prefix . $this->codename, $this->request->post);

            $this->session->data['success'] = $this->language->get('success_save');
        }
        
        $data['error'] = $this->error;
        
        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];
            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }
        
        $this->response->setOutput(json_encode($data));
    }
            	
	public function install() {
		if (file_exists(DIR_APPLICATION . 'model/extension/module/d_event_manager.php')) {
			$this->load->model('extension/module/d_event_manager');
				
			$this->model_extension_module_d_event_manager->installCompatibility();				
			$this->model_extension_module_d_event_manager->deleteEvent($this->codename);
			
			$this->model_extension_module_d_event_manager->addEvent($this->codename, 'catalog/controller/checkout/success/before', 'extension/analytics/d_google_analytics/checkout_success_before');
		}
	}
	
	public function uninstall() {
		if (file_exists(DIR_APPLICATION . 'model/extension/module/d_event_manager.php')) {
			$this->load->model('extension/module/d_event_manager');
				
			$this->model_extension_module_d_event_manager->deleteEvent($this->codename);
		}
	}
    
    private function validate($permission = 'modify') {
        if (isset($this->request->post['config'])) {
            return false;
        }
                
        if (!$this->user->hasPermission($permission, $this->route)) {
            $this->error['warning'] = $this->language->get('error_permission');
            return false;
        }
        
        return true;
    }
}
