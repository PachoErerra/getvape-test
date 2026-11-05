<?php

/************************copyright***************************/
/*                                                          */
/* telegram: https://t.me/PrutNikolay                       */
/* forum: https://opencartforum.com/profile/18336-exploits/ */
/* email: info@microdata.pro                                */
/* site: https://microdata.pro                              */
/*                                                          */
/************************copyright***************************/

require_once(DIR_SYSTEM . 'library/microdatapro.php');
class ControllerExtensionModuleMicrodataPro extends Controller {

	//index
		public function index() {
			$this->install();
			$data = $this->mdp->lang;

			$this->load->model('tool/image');
			$this->load->model('setting/setting');
			$this->load->model('catalog/attribute');
			$this->load->model('catalog/information');
			$this->load->model('localisation/language');
			$this->load->model('localisation/stock_status');
			$this->document->setTitle($data['heading_title']);

			$data['a'] 					 = $this->mdp->a;
			$data['link_main'] 	 = HTTPS_CATALOG;
			$data['message'] 		 = $this->mdp->message;
			$data['mdp_path'] 	 = $this->mdp->path;
			$data['token_name']  = $this->getToken();
			$data['token'] 			 = $this->session->data[$data['token_name']];
			$data['site_url'] 	 = $this->mdp->info('site');
			$data['success'] 		 = isset($this->request->get['success'])?true:false;
			$data['version'] 		 = $this->mdp->ver;
			$data['email'] 			 = $this->mdp->info('email');
			$data['store_name']  = $this->config->get('config_name');
			$data['cron_link'] 	 = HTTPS_CATALOG . 'index.php?route=' . $this->mdp->path . '/cron&key=';
			$data['lhref'] 			 = "https://microdata.pro/status/?module=microdatapro&domain=" . $this->mdp->info('site');
			$data['action'] 		 = $this->url->link($this->mdp->path . '&success=1', $data['token_name'] . '=' . $data['token'], 'SSL');
			$data['cancel'] 		 = $this->url->link($this->mdp->extension, $data['token_name'] . '=' . $data['token'], 'SSL');
			$data['refresh'] 		 = str_replace('&amp;', '&', $this->url->link(((VERSION==2.3)?$this->mdp->refresh23:$this->mdp->refresh), $data['token_name'] . '=' . $data['token'], 'SSL'));

			$this->getStores($data);
			$this->setSettings($data);
			$this->getVars($data);
			$this->versionFix($data);
			$this->getLinks($data);
			$this->getStoreTypes($data);
			$this->getBreadcrumbs($data);
			$this->getStoreStockStatuses($data);
			$this->getSchemaStockStatuses($data);
			$this->runDiagnostics($data);
			//$this->versionFix($data);

			$data['placeholder'] 	 = $this->model_tool_image->resize('no_image.png', 100, 100);
			$data['logoalt_thumb'] = $this->model_tool_image->resize((is_file(DIR_IMAGE . $data['microdatapro_logoalt']))?$data['microdatapro_logoalt']:'no_image.png', 100, 100);
			$data['attributes']    = $this->model_catalog_attribute->getAttributes(['start'=>0,'limit'=>999,'sort'=>'ad.name','order'=>'ASC']);
			$data['languages'] 	 	 = $this->model_localisation_language->getLanguages();

			$query = $this->db->query("SELECT COUNT(product_id) as count_products FROM " . DB_PREFIX . "product");
			$data['cron_view'] = ($query->row['count_products'] > 100000)?true:false;

			$data['url_informations'] = [];
			$data['url_informations']['information/contact'] = 'information/contact';
			foreach ($this->model_catalog_information->getInformations() as $result) {
				$data['url_informations'][$result['information_id']] = $result['title'];
			}

			$data['blog_types'] = ['Article','NewsArticle','BlogPosting'];

			$data['header'] 		   = $this->load->controller('common/header');
			$data['footer'] 		   = $this->load->controller('common/footer');
			$data['column_left']   = $this->load->controller('common/column_left');

			$this->response->setOutput($this->loadView($this->mdp->path, $data));
		}
	//index

	//versionFix
		private function versionFix(&$data){
			//from 8 to 10
			$this->getLocations($data);

			foreach($data['stores'] as $result){
				$this->getLocations($data, $result['store_id']);
			}

			//fix 2-3
			$old_data = $data;
			$data = [];
			foreach($old_data as $data_key => $data_value){
				$data[str_replace($this->mdp->key() . '_', 'microdatapro_', $data_key)] = $data_value;
			}
			$old_data = [];

			if(is_array($data['email'])){
				foreach($data['email'] as $email){
					$data['email'] = $email;
					break;
				}
			}

		}
	//versionFix

	//getLocations
		private function getLocations(&$data, $store_id = ''){
			if($this->mdp->mdc('locations' . $store_id)){
				foreach(explode(PHP_EOL, $this->mdp->mdc('locations' . $store_id)) as $location){
					$geo = explode("//", trim($location));

					$data[$this->mdp->key() . '_'  . 'locations_coordinates' . $store_id] = $geo[0]?str_replace(",", ";", $geo[0]):'';

					if(isset($geo[1])){
						$country_data = explode(",", trim($geo[1]));
						$data[$this->mdp->key() . '_'  . 'locations_country' . $store_id] = isset($country_data[1])?trim($country_data[1]):'';
						$data[$this->mdp->key() . '_'  . 'locations_city' . $store_id] = isset($country_data[0])?trim($country_data[0]):'';
						$data[$this->mdp->key() . '_'  . 'locations_address' . $store_id] = isset($geo[2])?trim($geo[2]):'';
						$data[$this->mdp->key() . '_'  . 'locations_code' . $store_id] = isset($geo[3])?trim($geo[3]):'';
					}

					break;
				}
			}
		}
	//getLocations

	//getStoreStockStatuses
		private function getStoreStockStatuses(&$data){
			$data['stock_statuses'] =  $this->model_localisation_stock_status->getStockStatuses();

			foreach($data['stock_statuses'] as $stock_status_key => $stock_status_item){
				$data['stock_statuses'][$stock_status_key]['active'] = isset($data['microdatapro_stock_statuses'][$stock_status_item['stock_status_id']])?$data['microdatapro_stock_statuses'][$stock_status_item['stock_status_id']]:'';
			}
		}
	//getStoreStockStatuses

	//getSchemaStockStatuses
		private function getSchemaStockStatuses(&$data){
			$data['schema_stock_statuses'] =  [
				'InStock' => 'InStock - ' . $data['text_InStock'],
				'OutOfStock' => 'OutOfStock - ' . $data['OutOfStock'],
				'LimitedAvailability' => 'LimitedAvailability - ' . $data['LimitedAvailability'],
				'InStoreOnly' => 'InStoreOnly - ' . $data['InStoreOnly'],
				'OnlineOnly' => 'OnlineOnly - ' . $data['OnlineOnly'],
				'PreOrder' => 'PreOrder - ' . $data['PreOrder'],
				'PreSale' => 'PreSale - ' . $data['PreSale'],
				'Discontinued' => 'Discontinued - ' . $data['Discontinued'],
				'SoldOut' => 'SoldOut - ' . $data['SoldOut'],
			];
		}
	//getSchemaStockStatuses

	//getStoreTypes
		private function getStoreTypes(&$data){
			$data['store_types'] = [];

			for($i=0; $i<31; $i++){
				$data['store_types'][$i] = [
					'id' => $i + 1,
					'name' => $data['text_storetype_' . $i]
				];
			}
		}
	//getStoreTypes

	//runDiagnostics
		public function runDiagnostics(&$data){
			$this->checkMicrodataMod($data);
			$this->findMicrodataInTheme($data);
			$this->findMicrodataInModification($data);
		}
	//runDiagnostics

	//checkMicrodataMod
		private function checkMicrodataMod(&$data){
			$data['count_errors'] = 0;
			$data['mod_errors'] = 8;
			$data['mod_files'] = [];

			$files = [
				'common/home',
				'product/product',
				'product/category',
				'product/manufacturer',
				'information/information',
				'information/contact',
				'product/special',
				'product/search',
			];

			foreach($files as $file){
				$status = false;
				$ocmod = false;

				$file_orig = DIR_CATALOG . 'controller/' . $file . '.php';
				$orig_content = file_get_contents($file_orig);

				$file_mod = DIR_MODIFICATION . 'catalog/controller/' . $file . '.php';
				$mod_content = file_get_contents($file_mod);

				//проверка прописался ли модификатор в файле
				if(is_file($file_orig) && strpos($orig_content, "data['header']") !== false){
					$status = true;
				}

				//проверка прописался ли модификатор в файле
				if(is_file($file_mod) && strpos($mod_content, 'module/microdatapro') !== false){
					$data['mod_errors']--;
					$ocmod = true;
				}

				$data['mod_files']['catalog/controller/' . $file . '.php'] = [
					'status' => $status,
					'ocmod' => $ocmod
				];
			}

		}
	//checkMicrodataMod

	//findMicrodataInTheme
		private function findMicrodataInTheme(&$data){
			$data['found_old'] = [];
			$data['found_old_count'] = [];

			$files = $this->getFilesForScan();

			foreach($files as $file){
				if(is_file($file)){
				  $file_content = @file_get_contents($file);

					$matches = $this->findMicrodataPattern($file_content);

					if($matches){
						$data['found_old'][$file] = str_replace(DIR_CATALOG, 'catalog/', $file);
					}
				}
			}
			if($data['found_old']){
				$data['count_errors']++;
				$data['found_old_count'] = count($data['found_old']);
			}
		}
	//findMicrodataInTheme

	//findMicrodataInModification
		private function findMicrodataInModification(&$data){
			$data['found_mod'] = [];
			$data['found_mod_admin'] = [];
			$data['found_mod_system'] = [];

			//Mod Files
				$files = $this->getFilesForScan(true);
				foreach($files as $file){
					if(is_file($file)){
					  $file_content = @file_get_contents($file);

						$matches = $this->findMicrodataPattern($file_content);

						if($matches){
							$data['found_mod'][$file] = str_replace(DIR_SYSTEM, 'system/', $file);
						}
					}
				}
			//Mod Files

			//Mod in DB
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "modification");
				if($query->num_rows){
					foreach($query->rows as $mod){
						$matches = $this->findMicrodataPattern($mod['xml']);

						if($matches){
							$matches[0] = array_unique($matches[0]);
							$data['found_mod_admin'][$mod['modification_id']] = $mod['name'] . ' (' . implode(',', $matches[0]) . ')';
						}
					}
				}
			//Mod in DB

			//Mod in System
				$files = glob(DIR_SYSTEM . '*.ocmod.xml');
				if($files){
					foreach($files as $mod){
						$mod_data = @file_get_contents($mod);
						$matches = $this->findMicrodataPattern($mod_data);

						if($matches){
							$data['found_mod_system'][$mod] = str_replace(DIR_SYSTEM, 'system/', $mod) . ' (' . implode(',', $matches[0]) . ')';
						}
					}
				}
			//Mod in System

			if($data['found_mod']){
				$data['count_errors']++;
			}
		}
	//findMicrodataInModification

	//findMicrodataPattern
		private function findMicrodataPattern($file_content){ //xmlns
			$match = preg_match_all('/(itemscope|itemtype|itemprop|ld\+json|vocab|property)/i', $file_content, $matches); //typeof|

			return $match?$matches:false;
		}
	//findMicrodataPattern

	//viewFile
		public function getViewFile(){
			$file = $this->request->get['file'];
			if(substr($file, 0, 3) == 'DB:'){
				$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "modification WHERE modification_id = '" . (int)str_replace('DB:', '', $file) . "'");
				$file = htmlentities($query->row['xml'], ENT_QUOTES, "UTF-8");
			}else{
				$file = file_get_contents($file);
				$file = htmlentities($file, ENT_QUOTES, "UTF-8");
			}

			$matches = $this->findMicrodataPattern($file);
			if($matches){
				foreach($matches[0] as $item){
					$file = str_replace($item, '<b>' . $item . '</b>', $file);
				}
			}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput($file);
		}
	//viewFile

	//replaceInFile
		public function replaceInFile(){
			$file = html_entity_decode($this->request->post['file_full'], ENT_QUOTES, 'UTF-8');
			$from = html_entity_decode($this->request->post['from'], ENT_QUOTES, 'UTF-8');
			$to = html_entity_decode($this->request->post['to'], ENT_QUOTES, 'UTF-8');

			if(substr($file, 0, 3) == 'DB:'){
				$this->db->query("UPDATE " . DB_PREFIX . "modification SET xml = REPLACE(xml, '" . $this->db->escape($from) . "', '" . $this->db->escape($to) . "') WHERE modification_id = '" . (int)str_replace('DB:', '', $file) . "'");
			}else{
				$file_data = file_get_contents($file);
				$file_data = str_replace($from, $to, $file_data);

				$original_file = fopen($file, 'w');
				fwrite($original_file, $file_data);
				fclose($original_file);
			}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode(1));
		}
	//replaceInFile

	//getFilesForScan
		private function getFilesForScan($modification = false){
			$controller = [
				'common/header',
				'common/home',
				'common/footer',
				'product/product',
				'product/category',
				'product/manufacturer',
				'information/information',
			];

			$theme = [
				'common/header',
				'common/home',
				'common/footer',
				'product/product',
				'product/review',
				'product/category',
				'product/special',
				'product/manufacturer_info',
				'product/manufacturer_list',
				'information/information',
				'information/contact',
				'module/alltabs',
				'module/bestseller',
				'module/bestsellerpercategory',
				'module/featured',
				'module/latest',
				'module/popular',
				'module/product_categorytabs',
				'module/product_tab',
				'module/productany',
				'module/productviewed',
				'module/special',
				'module/specialpercategory',
				'module/anylist',
				'module/product_review',
				'module/product_viewed',
				'extension/module/alltabs',
				'extension/module/bestseller',
				'extension/module/bestsellerpercategory',
				'extension/module/featured',
				'extension/module/latest',
				'extension/module/popular',
				'extension/module/product_categorytabs',
				'extension/module/product_tab',
				'extension/module/productany',
				'extension/module/productviewed',
				'extension/module/special',
				'extension/module/specialpercategory',
				'extension/module/anylist',
				'extension/module/product_review',
				'extension/module/product_viewed',
			];

			$files = [];
			foreach($controller as $controller_file){
				$files[] = ($modification?DIR_MODIFICATION.'catalog/':DIR_CATALOG) . 'controller/' . $controller_file . '.php';
			}

			$ext = (substr(VERSION, 0, 1) == 3)?'twig':'tpl';

			foreach($theme as $theme_file){
				$files[] = ($modification?DIR_MODIFICATION.'catalog/':DIR_CATALOG) . 'view/theme/' . $this->getThemeDir() . '/template/' . $theme_file . '.' . $ext;
			}

			return $files;
		}
	//getFilesForScan

	//getThemeDir
		private function getThemeDir() {
			return str_replace('theme_default', 'default', $this->config->get((substr(VERSION, 0, 3) < 2.2)?'config_template':'config_theme'));
		}
	//getThemeDir

	//clearOldMicrodata
		public function clearOldMicrodata(){
			$files = $this->getFilesForScan();
			if($files){
				$this->log->write("============================================================");
				$this->log->write("MicrodataPro clear old start");

				foreach($files as $file){
					if(is_file($file)){
						$file_content = @file_get_contents($file);

						$file_content = preg_replace([ //|xmlns
						    '/<script[^>]*type=("|\')application\/ld\+json\1[^>]*>.*?<\/script>/is',
								//'/<meta[^>]*(property|name)=(?:"|\')(og:|business:|place:|twitter:)[^>]*>/i',
								'/<meta[^>]*(property|name)=(?:"|\')(og:|business:|place:|twitter:)[^>]*?\/?>/is',
						    '/\s+(itemscope|itemtype|itemprop|vocab|property)(\s*=\s*(".*?"|\'.*?\'|[^\s>]+))?/i'
						], '', $file_content);

						rename($file, $file."_mdb");
						$fp = fopen($file, "w");
						fwrite($fp, $file_content);
						fclose($fp);
						$this->log->write("microdatapro mod file: " . $file);
						$this->log->write("microdatapro orig file: " . $file . "_mdb");
					}
				}

				$this->log->write("MicrodataPro clear old finish");
				$this->log->write("============================================================");
			}

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode(1));
		}
	//clearOldMicrodata

	//setSettings
		private function setSettings($data){
			if($this->request->server['REQUEST_METHOD'] == 'POST'){

				foreach($this->request->post as $post_key => $post_data){
					unset($this->request->post[$post_key]);
					$this->request->post[str_replace('microdatapro_', $this->mdp->key() . '_', $post_key)] = $post_data;
				}

				$this->request->post[$this->mdp->key() . '_' . 'front_stores'] = $data['stores'];

				$this->model_setting_setting->editSetting($this->mdp->key(), $this->request->post);

				$this->response->redirect($this->url->link($this->mdp->path . (isset($this->request->get['success'])?"&success=1":""), $data['token_name'] . '=' . $data['token'], 'SSL'));
			}
		}
	//setSettings

	//getVars
		private function getVars(&$data){
			$vars = [
				'key',
				'name',
				'status',
				'opengraph',
				'opengraph_meta',
				'twitter_account',
				'blog',
				'blog_type',
				'blog_author',
				'blog_author_url',
				'company',
				'company_type',
				'store_type',
				'hcard',
				'email',
				'oh_1',
				'oh_2',
				'oh_3',
				'oh_4',
				'oh_5',
				'oh_6',
				'oh_7',
				'phones',
				'groups',
				'locations',
				'locations_coordinates',
				'locations_country',
				'locations_city',
				'locations_address',
				'locations_code',
				'url_contact',
				'url_privacy',
				'map',
				'video',
				'video_key',
				'product',
				'product_breadcrumb',
				'product_gallery',
				'hide_price',
				'sku',
				'upc',
				'ean',
				'mpn',
				'isbn',
				'product_reviews',
				'product_related',
				'product_attribute',
				'product_in_stock',
				'stock_statuses',
				'category',
				'category_range',
				'category_review',
				'category_gallery',
				'manufacturer',
				'information',
				'age_group',
				'target_gender',
				'profile_id',
				'attr_color',
				'attr_material',
				'attr_size',
				'logosize_1',
				'logosize_2',
				'category_thumb_1',
				'category_thumb_2',
				'category_popup_1',
				'category_popup_2',
				'cron_key',
				'category_list',
				'category_list_extended',
				'logoalt',
				'shipping_cost',
				'shipping_country',
				'shipping_time1_min',
				'shipping_time1_max',
				'shipping_time2_min',
				'shipping_time2_max',
				'return_days',
			];

			//add multistore vars
			foreach($data['stores'] as $result){
				$vars[] = 'locations_coordinates'.$result['store_id'];
				$vars[] = 'locations_country'.$result['store_id'];
				$vars[] = 'locations_city'.$result['store_id'];
				$vars[] = 'locations_address'.$result['store_id'];
				$vars[] = 'locations_code'.$result['store_id'];
				$vars[] = 'phones'.$result['store_id'];
				$vars[] = 'groups'.$result['store_id'];
				$vars[] = 'locations'.$result['store_id'];
				$vars[] = 'map'.$result['store_id'];
			}

			foreach($vars as $var){
				if(isset($this->request->post[$var])){
					$data[$this->mdp->key() . '_'  . $var] = $this->request->post[$this->mdp->key() . '_'  . $var];
				}else{
					$data[$this->mdp->key() . '_'  . $var] = $this->config->get($this->mdp->key() . '_'  . $var);
				}
			}
		}
	//getVars

	//getLinks
		public function getLinks(&$data){
			$data['link_product'] 		 = false;
			$data['link_category'] 		 = false;
			$data['link_information']  = false;
			$data['link_manufacturer'] = false;

			$category_query = $this->db->query("SELECT category_id FROM " . DB_PREFIX ."category WHERE status = 1 LIMIT 0,1");
			if($category_query->num_rows){
				$data['link_category'] = HTTPS_CATALOG . 'index.php%3Froute=product/category%26path%3D' . $category_query->row['category_id'];
			}

			$product_query = $this->db->query("SELECT product_id FROM " . DB_PREFIX ."product WHERE status = 1 LIMIT 0,1");
			if($product_query->num_rows){
				$data['link_product'] = HTTPS_CATALOG . 'index.php%3Froute=product/product%26product_id%3D' . $product_query->row['product_id'];
			}

			$manufacturer_query = $this->db->query("SELECT manufacturer_id FROM " . DB_PREFIX ."manufacturer ORDER BY manufacturer_id DESC LIMIT 0,1");
			if($manufacturer_query->num_rows){
				$data['link_manufacturer'] = HTTPS_CATALOG . 'index.php%3Froute=product/manufacturer/info%26manufacturer_id%3D' . $manufacturer_query->row['manufacturer_id'];
			}

			$information_query = $this->db->query("SELECT information_id FROM " . DB_PREFIX ."information WHERE status = 1 LIMIT 0,1");
			if($information_query->num_rows){
				$data['link_information'] = HTTPS_CATALOG . 'index.php%3Froute=information/information%26information_id%3D' . $information_query->row['information_id'];
			}
		}
	//getLinks

	//getStores
		private function getStores(&$data){
			$data['stores'] = [];

			$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "store ORDER BY store_id ASC");

			foreach ($query->rows as $result) {
				$data['stores'][] = [
					'store_id' => $result['store_id'],
					'name'     => $result['name'],
					'url'      => $result['url']
				];
			}
		}
	//getStores

	//getBreadcrumbs
		private function getBreadcrumbs(&$data){
			$data['breadcrumbs'] = [];

			foreach([$this->language->get('text_home') => 'common/dashboard', $data['text_module'] => $this->mdp->extension, $data['heading_title'] => $this->mdp->path] as $btext => $blink){
				$data['breadcrumbs'][] = [
					'text' => $btext,
					'href' => $this->url->link($blink, $data['token_name'] . '=' . $data['token'], 'SSL')
				];
			}
		}
	//getBreadcrumbs

	//getToken
		private function getToken(){
	    return isset($this->request->get['token'])?'token':'user_token';
	  }
	//getToken

	//loadView
    private function loadView($route, $data){

			$delete_old_twig_tpl = [
				DIR_CATALOG . 'view/theme/default/template/' . $this->mdp->path . '/tc_og.twig',
				DIR_CATALOG . 'view/theme/default/template/' . $this->mdp->path . '/company.twig',
				DIR_CATALOG . 'view/theme/default/template/' . $this->mdp->path . '/product.twig',
				DIR_CATALOG . 'view/theme/default/template/' . $this->mdp->path . '/category_manufacturer.twig',
				DIR_CATALOG . 'view/theme/default/template/' . $this->mdp->path . '/information.twig',
			];

			if(is_file(DIR_TEMPLATE . 'extension/module/microdatapro.twig')){
				unlink(DIR_TEMPLATE . 'extension/module/microdatapro.twig');
			}

			foreach($delete_old_twig_tpl as $delete_file){
				if(is_file($delete_file)){
					unlink($delete_file);
				}
				if(is_file(str_replace('.twig', '.tpl', $delete_file))){
					unlink(str_replace('.twig', '.tpl', $delete_file));
				}
			}

      if(substr(VERSION, 0, 1) == 3){
        $this->registry->get('config')->set('template_engine', 'template'); //go to tpl
      }
      if(substr(VERSION, 0, 3) < 2.3){
        $route .= '.tpl';
      }else{
        if(is_file(DIR_APPLICATION . 'controller/module/microdatapro.php')){
          unlink(DIR_APPLICATION . 'controller/module/microdatapro.php');
        }
      }
      return $this->load->view($route, $data);
    }
  //loadView

	//Other functions
		public function __construct($registry) {
			parent::__construct($registry);
			$this->mdp = new Microdatapro($this->registry);
		}

		public function install() {
			$this->mdp->init();
		}

		public function addKey(){
			if(isset($this->request->post['txt']) && $this->request->post['txt'] && $this->mdp->status($this->request->post['txt'])){
				$this->db->query("UPDATE " . DB_PREFIX . "setting SET `value` = '" . $this->db->escape($this->request->post['txt']) . "' WHERE `key` = '" . $this->mdp->key() . "_key'");
				$this->response->addHeader('Content-Type: application/json');
				$this->response->setOutput(json_encode(1));
			}
			return false;
		}
	//Other functions
}
