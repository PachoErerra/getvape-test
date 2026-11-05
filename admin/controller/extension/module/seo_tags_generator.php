<?php

/**
 * @category   OpenCart
 * @package    SEO Tags Generator
 * @copyright  © Serge Tkach, 2017-2022, https://sergetkach.com/
 */
ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (version_compare(PHP_VERSION, '7.2') >= 0) {
	$php_v = '72_74';
} elseif (version_compare(PHP_VERSION, '7.1') >= 0) {
	$php_v = '71';
} elseif (version_compare(PHP_VERSION, '5.6.0') >= 0) {
	$php_v = '56_70';
} elseif (version_compare(PHP_VERSION, '5.4.0') >= 0) {
	$php_v = '54_56';
} else {
	echo "Sorry! Version for PHP 5.3 Not Supported!<br>Please contact to author!";
	exit;
}

require_once DIR_SYSTEM . 'library/seo_tags_generator/seo_tags_generator_' . $php_v . '.php';

class ControllerExtensionModuleSeoTagsGenerator extends Controller {

	private $error = array();
	private $stg;

	public function __construct($registry) {
		parent::__construct($registry);

		$this->stg = new SeoTagsGenerator();
	}

	public function index() {
		$this->load->language('extension/module/seo_tags_generator');
		$this->load->model('extension/module/seo_tags_generator');
		$this->load->model('setting/setting');

		$this->document->addStyle('view/stylesheet/seo-tags-generator.css');
		
		$this->document->addStyle('view/stylesheet/seo_tags_generator/select2.min.css');
		$this->document->addScript('view/javascript/seo_tags_generator/select2.min.js');


		/* Начальные данные
		  ----------------------------------------------------- */
		$this->session->data['valid_licence']	 = true;
		$data['show_work_area']								 = true;
		$data['show_licence_entry']						 = false;


		/* Лицензия проверка
		  ----------------------------------------------------- */
		if (isset($this->request->post['module_seo_tags_generator_licence']) && !empty($this->request->post['module_seo_tags_generator_licence'])) {
			$licence_code = $this->request->post['module_seo_tags_generator_licence'];
		} else {
			$licence_code = $this->config->get('module_seo_tags_generator_licence');
		}

		if ($licence_code) {

			if ($this->stg->isValidLicence($licence_code)) {
				$this->session->data['valid_licence']	 = true;
				$data['show_work_area']								 = true;
				$data['show_licence_entry']						 = false;
			} else {

				if ($this->stg->isValidLicence($licence_code, 'temp')) {
					$this->session->data['valid_licence']	 = true;
					$data['show_work_area']								 = true;
					$data['show_licence_entry']						 = false;

					$arr = $this->stg->decodeTempLicence($licence_code);

					$time_final	 = $arr['time'] + $arr['days'] * 60 * 60 * 24;
					$delta_t		 = $time_final - time();
					$delta_t		 = ceil($delta_t / (60 * 60 * 24));

					$data['warning']['licence'] = str_replace('[x]', $delta_t, $this->language->get('warning_licence'));
				} else {
					// Временная лицензия может быть некорректной, если:
					// Лицензия сгенерина неверно
					// Вышел ее срок действия
					// Пользователь пытался ее подобрать
					$this->session->data['valid_licence']	 = true;
					$data['show_work_area']								 = true;
					$data['show_licence_entry']						 = false;

					$data['module_seo_tags_generator_licence']	 = '';
					$data['errors']['licence']					 = $this->language->get('error_licence_not_valid');
				}
			}
		}


		/* Обновления
		  -------------------------------------- */
		
		// Поле `category_name_singular_genitive` - добавлено в версии 3.6.0
		$exist = false;
		$sql = "SHOW COLUMNS FROM `" . DB_PREFIX . "seo_tags_generator_category_declension`";
		$result	= $this->db->query($sql);

		foreach ($result->rows as $row) {
			if ('category_name_singular_genitive' == $row['Field']) {
				$exist = true;
				break;
			}
		}
		
		if (!$exist) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "seo_tags_generator_category_declension` ADD `category_name_singular_genitive` VARCHAR(255) NOT NULL AFTER `category_name_singular_nominative`");
		}		
		
		
		/* Зависимость
		  -------------------------------------- */
		// В версии с падежами, работа модуля будет зависеть от заполненности словоформ категорий


		/* Сохранение
		  ------------------------------------ */
		$data['text_success'] = ''; // if no success redirect

		if ($this->request->server['REQUEST_METHOD'] == 'POST' && $this->validateForm()) {
			$data['text_success'] = $this->language->get('text_success'); // if no success redirect
			// Избавляемся от необходимости делать hidden поля
			if (!isset($this->request->post['module_seo_tags_generator_licence'])) {
				$this->request->post['module_seo_tags_generator_licence'] = $this->config->get('module_seo_tags_generator_licence');
			}

//			echo "----------------------------------------------------------------------"
//			. "</br>\$this->request->post</br>";
//			echo "<pre>";
//			print_r($this->request->post);
//			echo "</pre></br>";
//			exit;
			// Общие формулы
			$this->model_setting_setting->editSetting('module_seo_tags_generator', $this->request->post);
		}


		/* Переменные ошибок для validateForm
		  --------------------------------------- */

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['errors'])) {
			$data['errors'] = $this->error['errors'];
		} else {
			$data['errors'] = '';
		}


		/* Тексты
		  ------------------------------------ */
		$this->document->setTitle(strip_tags($this->language->get('heading_title')));

		//$data['heading_title']			 = $this->language->get('heading_title');
		$data['heading_title']			 = strip_tags($this->language->get('heading_title'));
		$data['text_edit']					 = $this->language->get('text_edit');
		$data['text_enabled']				 = $this->language->get('text_enabled');
		$data['text_disabled']			 = $this->language->get('text_disabled');
		$data['text_available_vars'] = $this->language->get('text_available_vars');
		$data['text_author']				 = $this->language->get('text_author');
		$data['text_author_support'] = $this->language->get('text_author_support');

		$data['entry_licence'] = $this->language->get('entry_licence');
		$data['entry_status']	 = $this->language->get('entry_status');

		$data['entry_generate_mode_category']					 = $this->language->get('entry_generate_mode_category');
		$data['entry_generate_mode_category_h1']			 = $this->language->get('entry_generate_mode_category_h1');
		$data['entry_generate_mode_category_text']		 = $this->language->get('entry_generate_mode_category_text');
		$data['entry_generate_mode_product']					 = $this->language->get('entry_generate_mode_product');
		$data['entry_generate_mode_product_h1']				 = $this->language->get('entry_generate_mode_product_h1');
		$data['entry_generate_mode_product_text']			 = $this->language->get('entry_generate_mode_product_text');
		$data['entry_generate_mode_manufacturer']			 = $this->language->get('entry_generate_mode_manufacturer');
		$data['entry_generate_mode_manufacturer_h1']	 = $this->language->get('entry_generate_mode_manufacturer_h1');
		$data['entry_generate_mode_manufacturer_text'] = $this->language->get('entry_generate_mode_manufacturer_text');

		$data['entry_inheritance']				 = $this->language->get('entry_inheritance');
		$data['entry_inheritance_tooltip'] = $this->language->get('entry_inheritance_tooltip');
		$data['entry_declension']					 = $this->language->get('entry_declension');
		$data['entry_declension_tooltip']	 = $this->language->get('entry_declension_tooltip');

		$data['entry_category_title']				 = $this->language->get('entry_category_title');
		$data['entry_category_description']	 = $this->language->get('entry_category_description');
		$data['entry_category_keyword']			 = $this->language->get('entry_category_keyword');
		$data['entry_category_h1']					 = $this->language->get('entry_category_h1');
		$data['entry_category_text']				 = $this->language->get('entry_category_text');

		$data['entry_product_title']			 = $this->language->get('entry_product_title');
		$data['entry_product_description'] = $this->language->get('entry_product_description');
		$data['entry_product_keyword']		 = $this->language->get('entry_product_keyword');
		$data['entry_product_h1']					 = $this->language->get('entry_product_h1');
		$data['entry_product_text']				 = $this->language->get('entry_product_text');

		$data['entry_manufacturer_title']				 = $this->language->get('entry_manufacturer_title');
		$data['entry_manufacturer_description']	 = $this->language->get('entry_manufacturer_description');
		$data['entry_manufacturer_keyword']			 = $this->language->get('entry_manufacturer_keyword');
		$data['entry_manufacturer_h1']					 = $this->language->get('entry_manufacturer_h1');
		$data['entry_manufacturer_text']				 = $this->language->get('entry_manufacturer_text');

		$data['fieldset_setting']						 = $this->language->get('fieldset_setting');
		$data['fieldset_formula_common']		 = $this->language->get('fieldset_formula_common');
		$data['fieldset_attributes_common']	 = $this->language->get('fieldset_attributes_common');

		$data['attributes_title']			 = $this->language->get('attributes_title');
		$data['add_attribute']				 = $this->language->get('add_attribute');
		$data['delete_attribute']			 = $this->language->get('delete_attribute');
		$data['text_attribute_select'] = $this->language->get('text_attribute_select');

		$data['tab_category']			 = $this->language->get('tab_category');
		$data['tab_product']			 = $this->language->get('tab_product');
		$data['tab_manufacturer']	 = $this->language->get('tab_manufacturer');

		$data['button_save']	 = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');


		/* breadcrumbs
		  ------------------------------------------------- */
		// user_token need in js ajax
		$data['user_token'] = $this->session->data['user_token'];

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
			'text' => strip_tags($this->language->get('heading_title')),
			'href' => $this->url->link('extension/module/seo_tags_generator', 'user_token=' . $this->session->data['user_token'], true)
		);


		// Кнопки
		$data['action']	 = $this->url->link('extension/module/seo_tags_generator', 'user_token=' . $this->session->data['user_token'], true);
		$data['cancel']	 = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true);


		/* Языки
		  --------------------------------------- */
		$this->load->model('localisation/language');
		$data['languages'] = $this->model_localisation_language->getLanguages();


		/* Данные модуля: Настройки
		  --------------------------------------- */
		$data['module_seo_tags_generator_status'] = '';
		if (isset($this->request->post['module_seo_tags_generator_status'])) {
			$data['module_seo_tags_generator_status'] = $this->request->post['module_seo_tags_generator_status'];
		} else {
			$data['module_seo_tags_generator_status'] = $this->config->get('module_seo_tags_generator_status');
		}

		$a_generate_mode = $this->model_extension_module_seo_tags_generator->getGenerateMode();

		$data['a_generate_mode'] = array();
		foreach ($a_generate_mode as $item) {
			$data['a_generate_mode'][$item] = $this->language->get('text_generate_mode_' . $item);
		}

		if (isset($this->request->post['module_seo_tags_generator_generate_mode_category'])) {
			$data['module_seo_tags_generator_generate_mode_category'] = $this->request->post['module_seo_tags_generator_generate_mode_category'];
		} else {
			$data['module_seo_tags_generator_generate_mode_category'] = $this->config->get('module_seo_tags_generator_generate_mode_category');
		}
		if (!$data['module_seo_tags_generator_generate_mode_category']) {
			$data['module_seo_tags_generator_generate_mode_category'] = 'nofollow'; // for migrate from 2.x to 3.x
		}

		$data['module_seo_tags_generator_generate_mode_category_h1'] = '';
		if (isset($this->request->post['module_seo_tags_generator_generate_mode_category_h1'])) {
			$data['module_seo_tags_generator_generate_mode_category_h1'] = $this->request->post['module_seo_tags_generator_generate_mode_category_h1'];
		} else {
			$data['module_seo_tags_generator_generate_mode_category_h1'] = $this->config->get('module_seo_tags_generator_generate_mode_category_h1');
		}

		$data['module_seo_tags_generator_generate_mode_category_text'] = '';
		if (isset($this->request->post['module_seo_tags_generator_generate_mode_category_text'])) {
			$data['module_seo_tags_generator_generate_mode_category_text'] = $this->request->post['module_seo_tags_generator_generate_mode_category_text'];
		} else {
			$data['module_seo_tags_generator_generate_mode_category_text'] = $this->config->get('module_seo_tags_generator_generate_mode_category_text');
		}

		if (isset($this->request->post['module_seo_tags_generator_generate_mode_product'])) {
			$data['module_seo_tags_generator_generate_mode_product'] = $this->request->post['module_seo_tags_generator_generate_mode_product'];
		} else {
			$data['module_seo_tags_generator_generate_mode_product'] = $this->config->get('module_seo_tags_generator_generate_mode_product');
		}
		if (!$data['module_seo_tags_generator_generate_mode_product']) {
			$data['module_seo_tags_generator_generate_mode_product'] = 'nofollow'; // for migrate from 2.x to 3.x
		}

		$data['module_seo_tags_generator_generate_mode_product_h1'] = '';
		if (isset($this->request->post['module_seo_tags_generator_generate_mode_product_h1'])) {
			$data['module_seo_tags_generator_generate_mode_product_h1'] = $this->request->post['module_seo_tags_generator_generate_mode_product_h1'];
		} else {
			$data['module_seo_tags_generator_generate_mode_product_h1'] = $this->config->get('module_seo_tags_generator_generate_mode_product_h1');
		}

		$data['module_seo_tags_generator_generate_mode_product_text'] = '';
		if (isset($this->request->post['module_seo_tags_generator_generate_mode_product_text'])) {
			$data['module_seo_tags_generator_generate_mode_product_text'] = $this->request->post['module_seo_tags_generator_generate_mode_product_text'];
		} else {
			$data['module_seo_tags_generator_generate_mode_product_text'] = $this->config->get('module_seo_tags_generator_generate_mode_product_text');
		}

		if (isset($this->request->post['module_seo_tags_generator_generate_mode_manufacturer'])) {
			$data['module_seo_tags_generator_generate_mode_manufacturer'] = $this->request->post['module_seo_tags_generator_generate_mode_manufacturer'];
		} else {
			$data['module_seo_tags_generator_generate_mode_manufacturer'] = $this->config->get('module_seo_tags_generator_generate_mode_manufacturer');
		}
		if (!$data['module_seo_tags_generator_generate_mode_manufacturer']) {
			$data['module_seo_tags_generator_generate_mode_manufacturer'] = 'nofollow'; // for migrate from 2.x to 3.x
		}

		$data['module_seo_tags_generator_generate_mode_manufacturer_h1'] = '';
		if (isset($this->request->post['module_seo_tags_generator_generate_mode_manufacturer_h1'])) {
			$data['module_seo_tags_generator_generate_mode_manufacturer_h1'] = $this->request->post['module_seo_tags_generator_generate_mode_manufacturer_h1'];
		} else {
			$data['module_seo_tags_generator_generate_mode_manufacturer_h1'] = $this->config->get('module_seo_tags_generator_generate_mode_manufacturer_h1');
		}

		$data['module_seo_tags_generator_generate_mode_manufacturer_text'] = '';
		if (isset($this->request->post['module_seo_tags_generator_generate_mode_manufacturer_text'])) {
			$data['module_seo_tags_generator_generate_mode_manufacturer_text'] = $this->request->post['module_seo_tags_generator_generate_mode_manufacturer_text'];
		} else {
			$data['module_seo_tags_generator_generate_mode_manufacturer_text'] = $this->config->get('module_seo_tags_generator_generate_mode_manufacturer_text');
		}

		$data['module_seo_tags_generator_inheritance'] = 1;
		if (isset($this->request->post['module_seo_tags_generator_inheritance'])) {
			$data['module_seo_tags_generator_inheritance'] = $this->request->post['module_seo_tags_generator_inheritance'];
		} else {
			$data['module_seo_tags_generator_inheritance'] = $this->config->get('module_seo_tags_generator_inheritance');
		}

		$data['module_seo_tags_generator_declension'] = false;
		if (isset($this->request->post['module_seo_tags_generator_declension'])) {
			$data['module_seo_tags_generator_declension'] = $this->request->post['module_seo_tags_generator_declension'];
		} else {
			$data['module_seo_tags_generator_declension'] = $this->config->get('module_seo_tags_generator_declension');
		}

		$data['exist_category_h1']		 = $this->model_extension_module_seo_tags_generator->existFieldCategoryH1();
		$data['exist_product_h1']			 = $this->model_extension_module_seo_tags_generator->existFieldProductH1();
		$data['exist_manufacturer_h1'] = $this->model_extension_module_seo_tags_generator->existFieldManufacturerH1();
		$data['exist_model_synonym']	 = $this->model_extension_module_seo_tags_generator->existFieldModelSynonym();

		/* Данные модуля: Формулы + Лицензия
		  --------------------------------------- */
		$a_fields = array(
			'category_title', 'category_description', 'category_keyword', 'category_h1', 'category_text',
			'product_title', 'product_description', 'product_keyword', 'product_h1', 'product_text',
			'manufacturer_title', 'manufacturer_description', 'manufacturer_keyword', 'manufacturer_h1', 'manufacturer_text',
			'licence'
		);

		foreach ($a_fields as $field) {
			if (isset($this->request->post['module_seo_tags_generator_' . $field])) {
				$data['module_seo_tags_generator_' . $field] = $this->request->post['module_seo_tags_generator_' . $field];
			} else {
				$data['module_seo_tags_generator_' . $field] = $this->config->get('module_seo_tags_generator_' . $field);
			}

			if (!isset($data['module_seo_tags_generator_' . $field])) {
				$data['module_seo_tags_generator_' . $field] = '';
			}
		}


		// Attributes
		if (isset($this->request->post['module_seo_tags_generator_attributes'])) {
			$data['module_seo_tags_generator_attributes'] = $this->request->post['module_seo_tags_generator_attributes'];
		} elseif ($this->config->get('module_seo_tags_generator_attributes')) {
			$data['module_seo_tags_generator_attributes'] = $this->config->get('module_seo_tags_generator_attributes');
		} else {
			$data['module_seo_tags_generator_attributes'] = array();
		}

		// Attributes Exist
		$results = $this->model_extension_module_seo_tags_generator->getAttributes();

		$data['attributes_exist'] = array();

		foreach ($results as $result) {
			$data['attributes_exist'][] = array(
				'attribute_id' => $result['attribute_id'],
				'name'				 => strip_tags(html_entity_decode($result['attribute_group'], ENT_QUOTES, 'UTF-8') . ' :: ' . html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
				//'attribute_group' => $result['attribute_group']
			);
		}

		$data['header']			 = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer']			 = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/seo_tags_generator', $data));
	}

	protected function validateForm() {
		// Проверка прав группы пользователя
		if (!$this->user->hasPermission('modify', 'extension/module/seo_tags_generator')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		/* Проверка лицензии
		  -------------------------------------------------------- */
		// Тестовая и временная — уже должны были создать сессию
		// Возможно сразу вводят постоянную лицензию

		if (isset($this->request->post['module_seo_tags_generator_licence']) && !empty($this->request->post['module_seo_tags_generator_licence'])) {
			if ($this->stg->isValidLicence($this->request->post['module_seo_tags_generator_licence'])) {
				$this->session->data['valid_licence'] = true;
			} elseif ($this->stg->isValidLicence($this->request->post['module_seo_tags_generator_licence'], 'temp')) {
				$this->session->data['valid_licence'] = true;
			} else {
				$this->error['errors']['licence']									 = $this->language->get('error_licence_not_valid');
				$this->request->post['module_seo_tags_generator_licence'] = '';
			}
		}

		// if (!$this->session->data['valid_licence']) {
		// 	return false;
		// }


		/* Если лицензия ок
		  -------------------------------------------------------- */
		if ($this->request->post['module_seo_tags_generator_status']) {
			$this->load->model('localisation/language');

			$languages = $this->model_localisation_language->getLanguages();

			// Проверяемые поля без префикса модуля
			$array1 = array(
				'category_title',
				'category_description',
				'product_title',
				'product_description',
				'manufacturer_title',
				'manufacturer_description',
				//'searchtag_title',
				//'searchtag_description',
			);

			foreach ($array1 as $key_item) {
				foreach ($languages as $language) {
					if (empty($this->request->post['module_seo_tags_generator_' . $key_item][$language['language_id']])) {
						$this->error['errors'][$key_item][$language['language_id']] = $key_item . ' is empty!'; // tmp
					}
				}
			}

			if ('empty' == $this->request->post['module_seo_tags_generator_generate_mode_category_h1'] || 'forced' == $this->request->post['module_seo_tags_generator_generate_mode_category_h1']) {
				foreach ($languages as $language) {
					if (empty($this->request->post['module_seo_tags_generator_category_h1'][$language['language_id']])) {
						$this->error['errors']['category_h1'][$language['language_id']] = 'category_h1 is empty!'; // tmp
					}
				}
			}

			if ('empty' == $this->request->post['module_seo_tags_generator_generate_mode_category_text'] || 'forced' == $this->request->post['module_seo_tags_generator_generate_mode_category_text']) {
				foreach ($languages as $language) {
					if (empty($this->request->post['module_seo_tags_generator_category_text'][$language['language_id']])) {
						$this->error['errors']['category_text'][$language['language_id']] = 'category_text is empty!'; // tmp
					}
				}
			}

			if ('empty' == $this->request->post['module_seo_tags_generator_generate_mode_product_h1'] || 'forced' == $this->request->post['module_seo_tags_generator_generate_mode_product_h1']) {
				foreach ($languages as $language) {
					if (empty($this->request->post['module_seo_tags_generator_product_h1'][$language['language_id']])) {
						$this->error['errors']['product_h1'][$language['language_id']] = 'product_h1 is empty!'; // tmp
					}
				}
			}

			if ('empty' == $this->request->post['module_seo_tags_generator_generate_mode_product_text'] || 'forced' == $this->request->post['module_seo_tags_generator_generate_mode_product_text']) {
				foreach ($languages as $language) {
					if (empty($this->request->post['module_seo_tags_generator_product_text'][$language['language_id']])) {
						$this->error['errors']['product_text'][$language['language_id']] = 'product_text is empty!'; // tmp
					}
				}
			}

			if ('empty' == $this->request->post['module_seo_tags_generator_generate_mode_manufacturer_h1'] || 'forced' == $this->request->post['module_seo_tags_generator_generate_mode_manufacturer_h1']) {
				foreach ($languages as $language) {
					if (empty($this->request->post['module_seo_tags_generator_manufacturer_h1'][$language['language_id']])) {
						$this->error['errors']['manufacturer_h1'][$language['language_id']] = 'manufacturer_h1 is empty!'; // tmp
					}
				}
			}

			if ('empty' == $this->request->post['module_seo_tags_generator_generate_mode_manufacturer_text'] || 'forced' == $this->request->post['module_seo_tags_generator_generate_mode_manufacturer_text']) {
				foreach ($languages as $language) {
					if (empty($this->request->post['module_seo_tags_generator_manufacturer_text'][$language['language_id']])) {
						$this->error['errors']['manufacturer_text'][$language['language_id']] = 'manufacturer_text is empty!'; // tmp
					}
				}
			}
		}

		// Attributes
		if (isset($this->request->post['module_seo_tags_generator_attributes'])) {
			foreach ($this->request->post['module_seo_tags_generator_attributes'] as $attribute) {
				if (!$attribute) {
					$this->error['errors']['attributes'] = $this->language->get('error_attributes_empty');
				}
			}
		}

		// В случае других ошибок, кроме предупреждения о временной лицензии, вывести вверх формы сообщение, что мол проверьте форму
		if ($this->error && !isset($this->error['warning'])) {
			$this->error['warning'] = $this->language->get('error_warning');
		}

		return !$this->error;
	}

	public function install() {

		/* Права
		  ----------------------------------------------------- */
		$this->load->model('user/user_group');

		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'access', 'extension/module/seo_tags_generator');
		$this->model_user_user_group->addPermission($this->user->getGroupId(), 'modify', 'extension/module/seo_tags_generator');
		
		
		/* Предотвращение потери настроек модуля - ТОЛЬКО ДЛЯ 3 (переход на префикс module_)
		  ----------------------------------------------------- */
		$sql = "SELECT * FROM `" . DB_PREFIX . "setting` WHERE `code` = 'seo_tags_generator'";
		
		$query = $this->db->query($sql);
		
		$module_data = [];
		
		if ($query->num_rows > 0) {
			foreach ($query->rows as $row) {
				$module_data['module_' . $row['key']] = $this->config->get($row['key']);
			}
		}
		
		if (count($module_data) > 0) {
			$this->load->model('setting/setting');
			$this->model_setting_setting->editSetting('module_seo_tags_generator', $module_data);
			
			// Зачистить таблицу настроек от версии без префикса module_
			$this->model_setting_setting->editSetting('seo_tags_generator', '');
		}

		/* База
		  ----------------------------------------------------- */

		/* Формулы */
		$sql = "
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "seo_tags_generator` (
				`category_id` int(11) NOT NULL,
				`language_id` int(2) NOT NULL,
				`key` varchar(15) NOT NULL,
				`value` text NOT NULL,
				KEY `language_id` (`language_id`),
				KEY `category_id` (`category_id`),
				KEY `key` (`key`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		";

		$result = $this->db->query($sql);


		/* Не использовать автоформулы в заданных сущностях */
		// essense_id
		// 1 - product
		// 2 - category
		$sql = "
			CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "seo_tags_generator_not_use` (
				`id` int(11) NOT NULL,
				`essence_id` int(1) NOT NULL,
				PRIMARY KEY (`id`,`essence_id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8;
		";

		$result = $this->db->query($sql);


		/* Падежи категорий */
		$sql = "
      CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "seo_tags_generator_category_declension` (
        `category_id` int(11) NOT NULL,
        `language_id` int(3) NOT NULL,
        `category_name_singular_nominative` varchar(255) NOT NULL,
        `category_name_singular_genitive` varchar(255) NOT NULL,
        `category_name_plural_nominative` varchar(255) NOT NULL,
        `category_name_plural_genitive` varchar(255) NOT NULL,
        PRIMARY KEY (`category_id`,`language_id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ";

		$result = $this->db->query($sql);


		/* Настройки отдельных формул */

		$sql = "
      CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "seo_tags_generator_category_setting` (
        `category_id` int(11) NOT NULL,
        `setting` text NOT NULL,
        UNIQUE KEY `category_id` (`category_id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ";

		$result = $this->db->query($sql);


		/* Скопированные формулы */

		$sql = "
      CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "seo_tags_generator_category_copy` (
        `category_id` int(11) NOT NULL,
        `copy_category_id` int(11) NOT NULL,
        PRIMARY KEY (`category_id`,`copy_category_id`),
        KEY `copy_category_id` (`copy_category_id`)
      ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    ";

		$result = $this->db->query($sql);
	}

	public function uninstall() {
		$this->load->model('user/user_group');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'access', 'extension/module/seo_tags_generator');
		$this->model_user_user_group->removePermission($this->user->getGroupId(), 'modify', 'extension/module/seo_tags_generator');

		// Вроде как все чистится автоматом при деинсталляции
		//$this->load->model('setting/setting');
		//$this->model_setting_setting->editSetting('seo_tags_generator', '');
	}

	/* Category Tab
	  --------------------------------------------------------------------------- */

	public function getCategoryTab() {
		$this->load->model('extension/module/seo_tags_generator');

		$this->load->language('extension/module/seo_tags_generator');

		$data['tab_category_setting']				 = $this->language->get('tab_category_setting');
		$data['tab_seo_tags_generator_info'] = $this->language->get('tab_seo_tags_generator_info');
		$data['fieldset_formula_specific']	 = $this->language->get('fieldset_formula_specific');
		$data['text_available_vars']				 = $this->language->get('text_available_vars');

		$data['tab_category']	 = $this->language->get('tab_category');
		$data['tab_product']	 = $this->language->get('tab_product');

		$data['entry_category_title']				 = $this->language->get('entry_category_title');
		$data['entry_category_description']	 = $this->language->get('entry_category_description');
		$data['entry_category_keyword']			 = $this->language->get('entry_category_keyword');
		$data['entry_category_h1']					 = $this->language->get('entry_category_h1');
		$data['entry_category_text']				 = $this->language->get('entry_category_text');

		$data['entry_product_title']			 = $this->language->get('entry_product_title');
		$data['entry_product_description'] = $this->language->get('entry_product_description');
		$data['entry_product_keyword']		 = $this->language->get('entry_product_keyword');
		$data['entry_product_h1']					 = $this->language->get('entry_product_h1');
		$data['entry_product_text']				 = $this->language->get('entry_product_text');

		$data['entry_category_setting_inheritance']	 = $this->language->get('entry_category_setting_inheritance');
		$data['text_inheritance_yes']								 = $this->language->get('text_inheritance_yes');
		$data['text_inheritance_no']								 = $this->language->get('text_inheritance_no');

		$data['entry_category_setting_inheritance_copy'] = $this->language->get('entry_category_setting_inheritance_copy');
		$data['text_inheritance_copy_yes']							 = $this->language->get('text_inheritance_copy_yes');
		$data['text_inheritance_copy_warning']					 = $this->language->get('text_inheritance_copy_warning');

		$data['entry_category_setting_copy_to_others'] = $this->language->get('entry_category_setting_copy_to_others');
		$data['text_copy_to_others_yes']							 = $this->language->get('text_copy_to_others_yes');
		$data['text_copy_to_others_warning']					 = $this->language->get('text_copy_to_others_warning');

		$data['attributes_title_specific']		 = $this->language->get('attributes_title_specific');
		$data['attributes_subtitle_specific']	 = $this->language->get('attributes_subtitle_specific');
		$data['text_attribute_select']				 = $this->language->get('text_attribute_select');
		$data['add_attribute']								 = $this->language->get('add_attribute');
		$data['delete_attribute']							 = $this->language->get('delete_attribute');

		$data['entry_categories']	 = $this->language->get('entry_categories');
		$data['text_select_all']	 = $this->language->get('text_select_all');
		$data['text_unselect_all'] = $this->language->get('text_unselect_all');

		// user_token need in js ajax
		$data['user_token'] = $this->session->data['user_token'];

		$data['module_seo_tags_generator_declension'] = $this->config->get('module_seo_tags_generator_declension');

		if (isset($this->request->post['stg_specific']['formulas'])) {
			$data['stg_specific']['formulas'] = $this->request->post['stg_specific']['formulas'];
		} elseif (isset($this->request->get['category_id'])) {
			$data['stg_specific']['formulas'] = $this->model_extension_module_seo_tags_generator->getCategoryFormulas($this->request->get['category_id']);
		} else {
			$data['stg_specific']['formulas'] = array();
		}

		$data['exist_category_h1']	 = $this->model_extension_module_seo_tags_generator->existFieldCategoryH1();
		$data['exist_product_h1']		 = $this->model_extension_module_seo_tags_generator->existFieldProductH1();
		$data['exist_model_synonym'] = $this->model_extension_module_seo_tags_generator->existFieldModelSynonym();

		if (isset($this->request->post['stg_specific']['setting'])) {
			$data['stg_specific']['setting'] = $this->request->post['stg_specific']['setting'];
		} elseif (isset($this->request->get['category_id'])) {
			$data['stg_specific']['setting'] = $this->model_extension_module_seo_tags_generator->getCategorySetting($this->request->get['category_id']);
		} else {
			$data['stg_specific']['setting'] = array();
		}

		if (!isset($data['stg_specific']['setting']['inheritance'])) {
			$data['stg_specific']['setting']['inheritance'] = $this->config->get('module_seo_tags_generator_inheritance');
		}

		// Attributes
		if (!isset($data['stg_specific']['setting']['attributes'])) {
			$data['stg_specific']['setting']['attributes'] = array();
		}

		// Attributes Exist
		$results = $this->model_extension_module_seo_tags_generator->getAttributes();

		$data['attributes_exist'] = array();

		foreach ($results as $result) {
			$data['attributes_exist'][] = array(
				'attribute_id' => $result['attribute_id'],
				'name'				 => strip_tags(html_entity_decode($result['attribute_group'], ENT_QUOTES, 'UTF-8') . ' :: ' . html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
				//'attribute_group' => $result['attribute_group']
			);
		}

		$this->load->model('localisation/language');

		$data['languages'] = $this->model_localisation_language->getLanguages();

		// Categories
		$this->load->model('catalog/category');

		$filter_data = array(
			'sort'	 => 'name',
			'order'	 => 'ASC'
		);

		//$data['categories'] = $this->model_catalog_category->getCategories($filter_data);
		$categories					 = $this->model_catalog_category->getCategories($filter_data);
		$data['categories']	 = array();

		$categories = $this->model_extension_module_seo_tags_generator->getCategoriesMain();

		foreach ($categories as $category_id) {
			$data['categories'][] = $this->model_extension_module_seo_tags_generator->getDescendantsTreeForCategory($category_id);
		}

		// todo
		// Убрать текущую категорию из списка ? - А как насчет дочерних ??

		if (isset($this->request->post['copy_to_categories'])) {
			$data['copy_to_categories'] = $this->request->post['copy_to_categories'];
		} elseif (isset($this->request->get['category_id'])) {
			$data['copy_to_categories'] = $this->model_extension_module_seo_tags_generator->getCopyCategories($this->request->get['category_id']);
		} else {
			$data['copy_to_categories'] = array();
		}

		// copy categories ( of this category ! )
		if (isset($this->request->get['category_id'])) {
			$data['copy_to_categories'] = $this->model_extension_module_seo_tags_generator->getCategoryCopy($this->request->get['category_id']);
		} else {
			$data['copy_to_categories'] = array();
		}

		// Init licence for $this->getCategoriesList
		// $this->stg->setLicence($this->config->get('module_seo_tags_generator_licence'));

		// inactive categories ( all copy category with other parent category )
		$categories_inactive = $this->model_extension_module_seo_tags_generator->getCategoryCopyExist($data['copy_to_categories']);

		$data['categories_list'] = $this->stg->getCategoriesList($data['categories'], $data['copy_to_categories'], $categories_inactive, $level									 = 1, 'stg_specific[copy_to_categories]');

		return $this->load->view('extension/module/seo_tags_generator_category_tab', $data);
	}

	public function getAttributeList() {
		$json = array();

		$this->load->model('extension/module/seo_tags_generator');

		$results = $this->model_extension_module_seo_tags_generator->getAttributes();

		$attribute_list = array();

		foreach ($results as $result) {
			$attribute_list[] = array(
				'attribute_id' => $result['attribute_id'],
				'name'				 => strip_tags(html_entity_decode($result['attribute_group'], ENT_QUOTES, 'UTF-8') . ' :: ' . html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8')),
				//'attribute_group' => $result['attribute_group']
			);
		}

		$json['status']	 = 'success';
		$json['data']		 = $attribute_list;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function getAllAttributeValues() {
		$this->load->model('extension/module/seo_tags_generator');

		$json = array();

		$attribute_all_values = array();

		$attribute_all_values = $this->model_extension_module_seo_tags_generator->getAllAttributeValues();

		$attribute_values = array();

		foreach ($attribute_all_values as $item) {
			$attribute_values[$item['attribute_id']][$item['language_id']][] = strip_tags(html_entity_decode($item['text'], ENT_QUOTES, 'UTF-8'));
		}

		$json['status']	 = 'success';
		$json['data']		 = $attribute_values;

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	
	
	
	/* GENERATION
	--------------------------------------------------------------------------- */
public function getCategoryTags($data) {
	// $this->stg->setLicence($this->config->get('module_seo_tags_generator_licence'));
	
		$category_info = $data['category_info'];

		$lang_id = $data['language_id'];

		foreach ($category_info as $key => $value) {
			$category_info[$key] = is_string($value) ? trim($value) : $value;
		}

		if (!$this->config->get('module_seo_tags_generator_status')) {
			return $category_info;
		}

		if (array_key_exists('h1', $category_info)) {
			$h1 = 'h1'; // Мой модификатор
		} elseif (array_key_exists('meta_h1', $category_info)) {
			$h1 = 'meta_h1'; // ocStore
		} else {
			$h1 = false;
		}

		$this->load->model('extension/module/seo_tags_generator');

		if (!$this->model_extension_module_seo_tags_generator->notUseAutoCategory($category_info['category_id'])) {
			$a_specific_formula = $this->model_extension_module_seo_tags_generator->getSTGFormulasByCatId($category_info['category_id'], $lang_id, 'category');
		} else {
			// Change vars in real meta-tags
			$a_specific_formula = array(
				'title'				 => $category_info['meta_title'],
				'description'	 => $category_info['meta_description'],
				'keyword'			 => $category_info['meta_keyword'],
				'text'				 => $category_info['description']
			);

			if ($h1 && isset($category_info[$h1])) {
				$a_specific_formula['h1'] = $category_info[$h1];
			}
		}

		// Внимание!
		// В специфических формулах может быть такое, что задан только title или только description (!)
		// В админке не проверяется на заполненность всех полей для специфических формул

		if (isset($a_specific_formula['title']) && !empty($a_specific_formula['title'])) {
			$f_title = html_entity_decode($a_specific_formula['title'], ENT_QUOTES, 'UTF-8');
		} else {
			$f_title = $this->config->get('module_seo_tags_generator_category_title');
			$f_title = html_entity_decode($f_title[$lang_id], ENT_QUOTES, 'UTF-8');
		}

		if (isset($a_specific_formula['description']) && !empty($a_specific_formula['description'])) {
			$f_description = html_entity_decode($a_specific_formula['description'], ENT_QUOTES, 'UTF-8');
		} else {
			$f_description = $this->config->get('module_seo_tags_generator_category_description');
			$f_description = html_entity_decode($f_description[$lang_id], ENT_QUOTES, 'UTF-8');
		}

		if (isset($a_specific_formula['keyword']) && !empty($a_specific_formula['keyword'])) {
			$f_keyword = html_entity_decode($a_specific_formula['keyword'], ENT_QUOTES, 'UTF-8');
		} else {
			$f_keyword = $this->config->get('module_seo_tags_generator_category_keyword');
			$f_keyword = html_entity_decode($f_keyword[$lang_id], ENT_QUOTES, 'UTF-8');
		}

		if (isset($a_specific_formula['h1']) && !empty($a_specific_formula['h1'])) {
			$f_h1 = html_entity_decode($a_specific_formula['h1'], ENT_QUOTES, 'UTF-8');
		} else {
			$f_h1	 = $this->config->get('module_seo_tags_generator_category_h1');
			$f_h1	 = html_entity_decode($f_h1[$lang_id], ENT_QUOTES, 'UTF-8');
		}

		if (isset($a_specific_formula['text']) && !empty($a_specific_formula['text'])) {
			$f_text = html_entity_decode($a_specific_formula['text'], ENT_QUOTES, 'UTF-8');
		} else {
			$f_text	 = $this->config->get('module_seo_tags_generator_category_text');
			$f_text	 = html_entity_decode($f_text[$lang_id], ENT_QUOTES, 'UTF-8');
		}

		// Чисто для isFollowedVar()
		$formulas_array = array(
			'title'								 => $f_title,
			'description'					 => $f_description,
			'keyword'							 => $f_keyword,
			'h1'									 => $f_h1,
			'text'								 => $f_text,
			'ci_meta_title'				 => html_entity_decode($category_info['meta_title'], ENT_QUOTES, 'UTF-8'),
			'ci_meta_description'	 => html_entity_decode($category_info['meta_description'], ENT_QUOTES, 'UTF-8'),
			'ci_meta_keyword'			 => html_entity_decode($category_info['meta_keyword'], ENT_QUOTES, 'UTF-8'),
			'ci_description'			 => html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8'),
		);

		if ($h1 && isset($category_info[$h1])) {
			$formulas_array['ci_h1'] = html_entity_decode($category_info[$h1], ENT_QUOTES, 'UTF-8');
		}

		### Подготовка данных
		// Данные из $category_info по умолчанию, которые будут участвовать в заменах с помощью функций!
		$var_values = array(
			'category_name' => $category_info['name'],
		);

		$var_values['static_category_h1'] = '';

		if ($h1 && $category_info[$h1]) {
			$var_values['static_category_h1'] = $category_info[$h1];
		}

		if ($this->isFollowedVar('page_number', $formulas_array)) {
			$var_values['page_number'] = isset($this->request->get['page']) && $this->request->get['page'] ? $this->request->get['page'] : false;
		}

		if ($this->isFollowedVar('shop_name', $formulas_array)) {
			$var_values['shop_name'] = $this->config->get('config_name');
		}

		if ($this->isFollowedVar('config_telephone', $formulas_array)) {
			$var_values['config_telephone'] = $this->config->get('config_telephone');
		}

		// category_name - already exist
		if ($this->isFollowedVar('category_name_', $formulas_array)) {
			$category_declension = $this->model_extension_module_seo_tags_generator->getCategoryDeclension($category_info['category_id'], $lang_id);

			if (is_array($category_declension)) {
				$var_values['category_name_singular_nominative'] = $category_declension['category_name_singular_nominative'] ? $category_declension['category_name_singular_nominative'] : false;
				$var_values['category_name_singular_genitive'] = $category_declension['category_name_singular_genitive'] ? $category_declension['category_name_singular_genitive'] : false;
				$var_values['category_name_plural_nominative']	 = $category_declension['category_name_plural_nominative'] ? $category_declension['category_name_plural_nominative'] : false;
				$var_values['category_name_plural_genitive']		 = $category_declension['category_name_plural_genitive'] ? $category_declension['category_name_plural_genitive'] : false;
			} else {
				// Юзеру сразу видно, что он не заполнил переменные, то есть переменные вообще не попадают в список переменных
				$var_values['category_name_singular_nominative'] = $var_values['category_name_plural_nominative']	 = $var_values['category_name_plural_genitive']		 = false;
			}
		}

		// parent_category_name
		if ($this->isFollowedVar('parent_category_name', $formulas_array)) {
			$parent_category_id = $this->model_extension_module_seo_tags_generator->getParentCategoryByCategoryId($category_info['category_id']);
			
			// category_name - for parent category doesn't exist!			
			$var_values['parent_category_name'] = $this->model_extension_module_seo_tags_generator->getCategoryName($parent_category_id, $lang_id);
			
			$parent_category_declension = $this->model_extension_module_seo_tags_generator->getCategoryDeclension($parent_category_id, $lang_id);
			
			// A!
			// Take care: for $parent_category_declension is followed the same model method as for $category_declension
			// There is equal keys in returned array
			if (is_array($parent_category_declension)) {
				$var_values['parent_category_name_singular_nominative'] = $parent_category_declension['category_name_singular_nominative'] ? $parent_category_declension['category_name_singular_nominative'] : false;
				$var_values['parent_category_name_singular_genitive'] = $parent_category_declension['category_name_singular_genitive'] ? $parent_category_declension['category_name_singular_genitive'] : false;
				$var_values['parent_category_name_plural_nominative'] = $parent_category_declension['category_name_plural_nominative'] ? $parent_category_declension['category_name_plural_nominative'] : false;
				$var_values['parent_category_name_plural_genitive'] = $parent_category_declension['category_name_plural_genitive'] ? $parent_category_declension['category_name_plural_genitive'] : false;
			} else {
				// Юзеру сразу видно, что он не заполнил переменные, то есть переменные вообще не попадают в список переменных
				$var_values['parent_category_name_singular_nominative'] = $var_values['parent_category_name_plural_nominative'] = $var_values['parent_category_name_plural_genitive'] = false;
			}
		}

		if ($this->isFollowedVar('city', $formulas_array)) {
			$config_store	= $this->config->get('config_store');

			$config_store_city	= $config_store[$lang_id];

			//$followed_variables[] = 'city'; // ... multiple

			$var_values['city']								 = $config_store_city['city'];
			$var_values['city_genitive']			 = $config_store_city['city_genitive'];
			$var_values['city_dative']				 = $config_store_city['city_dative'];
			$var_values['city_prepositional']	 = $config_store_city['city_prepositional'];
		}

		// count products in cat
		if ($this->isFollowedVar('count_products', $formulas_array)) {
			$filter_data = array(
				'filter_category_id'	 => $category_info['category_id'],
				'filter_sub_category'	 => true
			);

			$this->load->model('catalog/product');

			$var_values['count_products'] = $this->model_catalog_product->getTotalProducts($filter_data);
		}

		// get min price in this category
		if ($this->isFollowedVar('min_price', $formulas_array)) {
			$min_price = $this->model_extension_module_seo_tags_generator->getMinPriceInCat($category_info['category_id']);

			if ($min_price) {
				$var_values['min_price'] = $min_price;
			} else {
				$var_values['min_price'] = 0;
			}
		}

		// get max price in this category
		if ($this->isFollowedVar('max_price', $formulas_array)) {
			$max_price = $this->model_extension_module_seo_tags_generator->getMaxPriceInCat($category_info['category_id']);

			if ($max_price) {
				$var_values['max_price'] = $max_price;
			} else {
				$var_values['max_price'] = 0;
			}
		}

		// Category Level
		if ($this->isFollowedVar('category_level', $formulas_array)) {
			$var_values['category_level'] = $this->model_extension_module_seo_tags_generator->getCategoryLevel($category_info['category_id']);

			// Levels for people begin with 1 (not with 0)
			if (false !== $var_values['category_level']) {
				$var_values['category_level']++;
			}
		}

		// Category Nested
		if ($this->isFollowedVar('category_nested', $formulas_array)) {
			// has category_nested without indexes
			// Index array begin with 1 (not 0)
			$categories_names = array();

			$categories_names0 = $this->model_extension_module_seo_tags_generator->getCategoriesNestedNames($category_info['category_id'], $lang_id);

			foreach ($categories_names0 as $key => $value) {
				$categories_names[$key + 1] = $value['name']; // sort array start with index 1 (not 0)
			}

			// One time is found in all formulas!
			if ($this->isFollowedVar('category_nested SORT_FROM_PARENT_TO_CHILD', $formulas_array)) {
				$categories_names_reverse = array();

				foreach (array_reverse($categories_names) as $key => $value) {
					$categories_names_reverse[$key + 1] = $value; // sort array start with index 1 (not 0)
				}

				$var_values['category_nested SORT_FROM_PARENT_TO_CHILD'] = $this->stg->getCategoryNestedSortedValue($categories_names_reverse);

				if ($this->isFollowedVar('category_nested SORT_FROM_PARENT_TO_CHILD exclude', $formulas_array)) {
					$var_values = array_merge($var_values, $this->excludeCategories($formulas_array, $categories_names_reverse, 'SORT_FROM_PARENT_TO_CHILD'));
				}
			}

			// One time is found in all formulas!
			if ($this->isFollowedVar('category_nested SORT_FROM_CHILD_TO_PARENT', $formulas_array)) {
				$var_values['category_nested SORT_FROM_CHILD_TO_PARENT'] = $this->stg->getCategoryNestedSortedValue($categories_names);

				if ($this->isFollowedVar('category_nested SORT_FROM_CHILD_TO_PARENT exclude', $formulas_array)) {
					$var_values = array_merge($var_values, $this->excludeCategories($formulas_array, $categories_names, 'SORT_FROM_CHILD_TO_PARENT'));
				}
			}

			// One time is found in all formulas!
			if ($this->isFollowedVar('category_nested', $formulas_array)) {
				$var_values['category_nested'] = $this->stg->getCategoryNestedSortedValue($categories_names);

				if ($this->isFollowedVar('category_nested exclude', $formulas_array)) {
					$var_values = array_merge($var_values, $this->excludeCategories($formulas_array, $categories_names));
				}
			}
		}

		if ($this->isFollowedVar('category_nested sort', $formulas_array)) {
			// has category_nested with indexes

			$category_indexes = $this->stg->findCategoryNestedIndexes($formulas_array);

			$categories_keys = $this->stg->getCategoriesKeysForVars($category_indexes);

			//$this->stg->getCategoriesLevels($category_indexes);

			foreach ($category_indexes as $item) {
				$var_values[$item['key']] = $this->stg->getCategoryNestedSortedValue($categories_names, $item['sort']);
			}
		}
		
		if ($this->isFollowedVar('category_nested SORT_FROM_PARENT_TO_CHILD sort', $formulas_array)) {
			// has category_nested with indexes

			$category_indexes = $this->findCategoryNestedFromParent2ChildIndexes($formulas_array);

			$categories_keys = $this->stg->getCategoriesKeysForVars($category_indexes);
			
			//$this->stg->getCategoriesLevels($category_indexes);

			foreach ($category_indexes as $item) {
				$var_values[$item['key']] = $this->stg->getCategoryNestedSortedValue($categories_names_reverse, $item['sort']);
			}
		}

		// A! [original_text] must be last!
		if ($this->isFollowedVar('original_text', $formulas_array)) {
			$var_values['original_text'] = $this->stg->parse(html_entity_decode(html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'), $var_values);
		}

		// Борьба с багом со скобками при использовании функций
		if (false !== strpos($var_values['category_name'], '(')) {
			$var_values['category_name'] = str_replace(array('(', ')'), array('left_bracket', 'right_bracket'), $var_values['category_name']);
		}

		// Генерация мета-тегов в зависимости от настроек модуля
		$generate_mode = $this->config->get('module_seo_tags_generator_generate_mode_category');

		if ('nofollow' == $generate_mode) {
			// only vars replace, but no follow formulas
			$category_info['meta_title']			 = $this->cleanup($this->stg->parse($category_info['meta_title'], $var_values));
			$category_info['meta_description'] = $this->cleanup($this->stg->parse($category_info['meta_description'], $var_values));
			$category_info['meta_keyword']		 = $this->cleanup($this->stg->parse($category_info['meta_keyword'], $var_values));
		}

		if ('empty' == $generate_mode) {
			if (empty($category_info['meta_title'])) {
				$category_info['meta_title'] = $this->cleanup($this->stg->parse($f_title, $var_values));
			} else {
				$category_info['meta_title'] = $this->cleanup($this->stg->parse($category_info['meta_title'], $var_values));
			}

			if (empty($category_info['meta_description'])) {
				$category_info['meta_description'] = $this->cleanup($this->stg->parse($f_description, $var_values));
			} else {
				$category_info['meta_description'] = $this->cleanup($this->stg->parse($category_info['meta_description'], $var_values));
			}

			if (empty($category_info['meta_keyword'])) {
				$category_info['meta_keyword'] = $this->cleanup($this->stg->parse($f_keyword, $var_values));
			} else {
				$category_info['meta_keyword'] = $this->cleanup($this->stg->parse($category_info['meta_keyword'], $var_values));
			}
		}

		if ('forced' == $generate_mode) {
			$category_info['meta_title']			 = $this->cleanup($this->stg->parse($f_title, $var_values));
			$category_info['meta_description'] = $this->cleanup($this->stg->parse($f_description, $var_values));
			$category_info['meta_keyword']		 = $this->cleanup($this->stg->parse($f_keyword, $var_values));
		}

		// Проверяем, не генерится ли H1 по формуле?
		$generate_mode_category_h1 = $this->config->get('module_seo_tags_generator_generate_mode_category_h1');

		// Заголовок в OpenCart Initial отсутствует, а name (из которого он берется) обязателен
		if ('nofollow' == $generate_mode_category_h1) {
			// only vars replace, but no follow formulas
			if ($h1) {
				$category_info[$h1] = $this->escapeBugParentheses($this->stg->parse($category_info[$h1], $var_values));
			}
		}

		if ('empty' == $generate_mode_category_h1) {
			if ($h1) {
				if (empty($category_info[$h1])) {
					$category_info[$h1] = $this->escapeBugParentheses($this->stg->parse($f_h1, $var_values));
				} else {
					$category_info[$h1] = $this->escapeBugParentheses($this->stg->parse($category_info[$h1], $var_values));
				}
			}
		}

		if ('forced' == $generate_mode_category_h1) {
			if ($h1) {
				$category_info[$h1] = $this->escapeBugParentheses($this->stg->parse($f_h1, $var_values));
			}
		}

		# Description - Text
		#
		// Description - is separated
		// for decode double htmlentities (1 in js in text editor + 1 on save process in DB)
		$category_text_tmp = html_entity_decode(html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');

		$generate_mode_category_text = $this->config->get('module_seo_tags_generator_generate_mode_category_text');

		if ('nofollow' == $generate_mode_category_text) {
			// only vars replace, but no follow formulas
			$category_info['description'] = $this->escapeBugParentheses($this->stg->parse($category_text_tmp, $var_values));
		}

		if ('empty' == $generate_mode_category_text) {
			$tmp_descr = trim(str_replace('&nbsp;', '', strip_tags(html_entity_decode($category_info['description'], ENT_QUOTES, 'UTF-8'))));
			if (empty($tmp_descr)) {
				$category_info['description'] = $this->escapeBugParentheses($this->stg->parse($f_text, $var_values));
			} else {
				$category_info['description'] = $this->escapeBugParentheses($this->stg->parse($category_text_tmp, $var_values));
			}
		}

		if ('forced' == $generate_mode_category_text) {
			$category_info['description'] = $this->escapeBugParentheses($this->stg->parse($f_text, $var_values));
		}

		return $category_info;
	}

	public function getProductTags($data) {
		// $this->stg->setLicence($this->config->get('module_seo_tags_generator_licence'));
		
		$product_info = $data['product_info'];

		$lang_id = $data['language_id'];

		if (!$this->config->get('module_seo_tags_generator_status')) {
			return $data['product_info'];
		}

		$product_info = $data['product_info'];

		foreach ($product_info as $key => $value) {
			$product_info[$key] = is_string($value) ? trim($value) : $value;
		}

		$attribute_groups = $data['attribute_groups'];

		if (array_key_exists('h1', $product_info)) {
			$h1 = 'h1'; // Мой модификатор
		} elseif (array_key_exists('meta_h1', $product_info)) {
			$h1 = 'meta_h1'; // ocStore
		} else {
			$h1 = false;
		}

		$this->load->model('extension/module/seo_tags_generator');

		$category_id = $this->model_extension_module_seo_tags_generator->getParentCategoryByProductId($product_info['product_id']);

		if (!$this->model_extension_module_seo_tags_generator->notUseAutoProduct($product_info['product_id'])) {
			$a_specific_formula = $this->model_extension_module_seo_tags_generator->getSTGFormulasByCatId($category_id, $lang_id, 'product');
		} else {
			// Change vars in real meta-tags
			$a_specific_formula = array(
				'title'				 => $product_info['meta_title'],
				'description'	 => $product_info['meta_description'],
				'keyword'			 => $product_info['meta_keyword'],
				'text'				 => $product_info['description']
			);

			if ($h1 && isset($product_info[$h1])) {
				$a_specific_formula['h1'] = $product_info[$h1];
			}
		}

		// Внимание!
		// В специфических формулах может быть такое, что задан только title или только description (!)
		// В админке не проверяется на заполненность всех полей для специфических формул

		if (isset($a_specific_formula['title']) && !empty($a_specific_formula['title'])) {
			$f_title = html_entity_decode($a_specific_formula['title'], ENT_QUOTES, 'UTF-8');
		} else {
			$f_title = $this->config->get('module_seo_tags_generator_product_title');
			$f_title = html_entity_decode($f_title[$lang_id], ENT_QUOTES, 'UTF-8');
		}

		if (isset($a_specific_formula['description']) && !empty($a_specific_formula['description'])) {
			$f_description = html_entity_decode($a_specific_formula['description'], ENT_QUOTES, 'UTF-8');
		} else {
			$f_description = $this->config->get('module_seo_tags_generator_product_description');
			$f_description = html_entity_decode($f_description[$lang_id], ENT_QUOTES, 'UTF-8');
		}

		if (isset($a_specific_formula['keyword']) && !empty($a_specific_formula['keyword'])) {
			$f_keyword = html_entity_decode($a_specific_formula['keyword'], ENT_QUOTES, 'UTF-8');
		} else {
			$f_keyword = $this->config->get('module_seo_tags_generator_product_keyword');
			$f_keyword = html_entity_decode($f_keyword[$lang_id], ENT_QUOTES, 'UTF-8');
		}

		if (isset($a_specific_formula['h1']) && !empty($a_specific_formula['h1'])) {
			$f_h1 = html_entity_decode($a_specific_formula['h1'], ENT_QUOTES, 'UTF-8');
		} else {
			$f_h1	 = $this->config->get('module_seo_tags_generator_product_h1');
			$f_h1	 = html_entity_decode($f_h1[$lang_id], ENT_QUOTES, 'UTF-8');
		}

		if (isset($a_specific_formula['text']) && !empty($a_specific_formula['text'])) {
			$f_text = html_entity_decode($a_specific_formula['text'], ENT_QUOTES, 'UTF-8');
		} else {
			$f_text	 = $this->config->get('module_seo_tags_generator_product_text');
			$f_text	 = html_entity_decode($f_text[$lang_id], ENT_QUOTES, 'UTF-8');
		}

		// Чисто для isFollowedVar()
		$formulas_array = array(
			'title'								 => $f_title,
			'description'					 => $f_description,
			'keyword'							 => $f_keyword,
			'h1'									 => $f_h1,
			'text'								 => $f_text,
			'pi_meta_title'				 => html_entity_decode($product_info['meta_title'], ENT_QUOTES, 'UTF-8'),
			'pi_meta_description'	 => html_entity_decode($product_info['meta_description'], ENT_QUOTES, 'UTF-8'),
			'pi_meta_keyword'			 => html_entity_decode($product_info['meta_keyword'], ENT_QUOTES, 'UTF-8'),
			'pi_description'			 => html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'),
		);

		if ($h1 && isset($product_info[$h1])) {
			$formulas_array['pi_h1'] = html_entity_decode($product_info[$h1], ENT_QUOTES, 'UTF-8');
		}

		### Подготовка данных
		// Данные из $product_info по умолчанию, которые будут участвовать в заменах с помощью функций!
		$var_values = array(
			'minimum'						 => $product_info['minimum'],
			'price'							 => $product_info['price'], // A! without currency
			'price_formatted'		 => $this->currency->format($this->tax->calculate($product_info['price'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
			'special'						 => $product_info['special'], // A! without currency
			'special_formatted'	 => $this->currency->format($this->tax->calculate($product_info['special'], $product_info['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']),
			'rating'						 => $product_info['rating'],
			'reviews'						 => $product_info['reviews'],
			'quantity'					 => $product_info['quantity'],
			'viewed'						 => $product_info['viewed'],
			'product_name'			 => $product_info['name'],
			'model'							 => $product_info['model'],
			'sku'								 => $product_info['sku'],
			'upc'								 => $product_info['upc'],
			'ean'								 => $product_info['ean'],
			'jan'								 => $product_info['jan'],
			'isbn'							 => $product_info['isbn'],
			'mpn'								 => $product_info['mpn'],
			//'manufacturer'			 => $product_info['manufacturer'], // A-M!
		);

		// Записываем использованные переменные, которые не являются стандартными полями товаров
		if (isset($product_info['model_synonym'])) {
			$var_values['model_synonym'] = $product_info['model_synonym'];
		}

		$var_values['static_product_h1'] = '';

		if ($h1 && $product_info[$h1]) {
			$var_values['static_product_h1'] = $product_info[$h1];
		}

		// A-M! -- Explication:
		// В ocStore 2 название хранится в manufacturer_description для каждого языка
		// В OpenCart 2, 3 и ocStore 3 - нет.
		// $product_info['manufacturer'] на витрине отображается на выбранном языке
		// В админке же идет перебор всех языков, а $data['manufacturer'] в контроллере товара содержит название только для одного языка
		// Конечно, это все можно сделать через модификатор под отдельную версию системы
		// Но!
		// Есть расширения, которые добавляют разные языки для производителя для OpenCart, да и для ocStore 3 небось тоже может быть!!
		// И тогда конфликтов не избежать. Лучше все это обработать здесь
		if ($this->isFollowedVar('manufacturer', $formulas_array)) {
			if ($this->model_extension_module_seo_tags_generator->isNameInManufacturerDescription()) {
				$manufacturer_description = $this->model_extension_module_seo_tags_generator->getManufacturerDescription($product_info['manufacturer_id'], $lang_id);

				if (is_array($manufacturer_description)) {
					$var_values['manufacturer']	= isset($manufacturer_description['name']) ? $manufacturer_description['name'] : '';
					$var_values['manufacturer_synonym']	= isset($manufacturer_description['name_synonym']) ? $manufacturer_description['name_synonym'] : '';
					$var_values['static_manufacturer_h1'] = isset($manufacturer_description['meta_h1']) ? $manufacturer_description['meta_h1'] : ''; // My modificator doesn't has h1 for manufacturer
				}
			} else {
				$this->load->model('catalog/manufacturer');
				$manufacturer_info = $this->model_catalog_manufacturer->getManufacturer($product_info['manufacturer_id']);				
				$var_values['manufacturer']	= isset($manufacturer_info['name']) ? $manufacturer_info['name'] : '';				
				$var_values['manufacturer_synonym']	= isset($manufacturer_info['name_synonym']) ? $manufacturer_info['name_synonym'] : '';
				$var_values['static_manufacturer_h1'] = isset($manufacturer_info['meta_h1']) ? $manufacturer_info['meta_h1'] : ''; // My modificator doesn't has h1 for manufacturer
			}
		}

		if ($this->isFollowedVar('shop_name', $formulas_array)) {
			$var_values['shop_name'] = $this->config->get('config_name');
		}

		if ($this->isFollowedVar('config_telephone', $formulas_array)) {
			$var_values['config_telephone'] = $this->config->get('config_telephone');
		}

		if ($this->isFollowedVar('count_sales', $formulas_array)) {
			$var_values['count_sales'] = $this->model_extension_module_seo_tags_generator->getProductSales($product_info['product_id']);
		}

		if ($this->isFollowedVar('category', $formulas_array) || $this->isFollowedVar('static_category_', $formulas_array)) {
			$category_description = $this->model_extension_module_seo_tags_generator->getCategoryDescriptionByIdAndLang($category_id, $lang_id);

			$var_values['category_name'] = $category_description['name'];

			if ($this->isFollowedVar('static_category_h1', $formulas_array)) {
				$var_values['static_category_h1'] = $category_description[$h1];
			}

			if ($this->isFollowedVar('category_name_', $formulas_array)) {
				$category_declension = $this->model_extension_module_seo_tags_generator->getCategoryDeclension($category_id, $lang_id);

				if (is_array($category_declension)) {
					$var_values['category_name_singular_nominative'] = $category_declension['category_name_singular_nominative'] ? $category_declension['category_name_singular_nominative'] : false;
					$var_values['category_name_singular_genitive'] = $category_declension['category_name_singular_genitive'] ? $category_declension['category_name_singular_genitive'] : false;
					$var_values['category_name_plural_nominative']	 = $category_declension['category_name_plural_nominative'] ? $category_declension['category_name_plural_nominative'] : false;
					$var_values['category_name_plural_genitive']		 = $category_declension['category_name_plural_genitive'] ? $category_declension['category_name_plural_genitive'] : false;
				} else {
					// Юзеру сразу видно, что он не заполнил переменные, то есть переменные вообще не попадают в список переменных
					$var_values['category_name_singular_nominative'] = $var_values['category_name_plural_nominative']	 = $var_values['category_name_plural_genitive']		 = false;
				}
			}
			
			// parent_category_name
			if ($this->isFollowedVar('parent_category_name', $formulas_array)) {
				$parent_category_id = $this->model_extension_module_seo_tags_generator->getParentCategoryByCategoryId($category_id);

				// category_name - for parent category doesn't exist!			
				$var_values['parent_category_name'] = $this->model_extension_module_seo_tags_generator->getCategoryName($parent_category_id, $lang_id);

				$parent_category_declension = $this->model_extension_module_seo_tags_generator->getCategoryDeclension($parent_category_id, $lang_id);

				// A!
				// Take care: for $parent_category_declension is followed the same model method as for $category_declension
				// There is equal keys in returned array
				if (is_array($parent_category_declension)) {
					$var_values['parent_category_name_singular_nominative']	 = $parent_category_declension['category_name_singular_nominative'] ? $parent_category_declension['category_name_singular_nominative'] : false;
					$var_values['parent_category_name_singular_genitive']		 = $parent_category_declension['category_name_singular_genitive'] ? $parent_category_declension['category_name_singular_genitive'] : false;
					$var_values['parent_category_name_plural_nominative']		 = $parent_category_declension['category_name_plural_nominative'] ? $parent_category_declension['category_name_plural_nominative'] : false;
					$var_values['parent_category_name_plural_genitive']			 = $parent_category_declension['category_name_plural_genitive'] ? $parent_category_declension['category_name_plural_genitive'] : false;
				} else {
					// Юзеру сразу видно, что он не заполнил переменные, то есть переменные вообще не попадают в список переменных
					$var_values['parent_category_name_singular_nominative']	 = $var_values['parent_category_name_plural_nominative']		 = $var_values['parent_category_name_plural_genitive']			 = false;
				}
			}
		}

		if ($this->isFollowedVar('city', $formulas_array)) {
			$config_store	= $this->config->get('config_store');

			$config_store_city	= $config_store[$lang_id];

			//$followed_variables[] = 'city'; // ... multiple

			$var_values['city']								 = $config_store_city['city'];
			$var_values['city_genitive']			 = $config_store_city['city_genitive'];
			$var_values['city_dative']				 = $config_store_city['city_dative'];
			$var_values['city_prepositional']	 = $config_store_city['city_prepositional'];
		}

		// Attributes
		// Can Be [attributes] (all) & [attribute index="n"] (separately)
		if ($this->isFollowedVar('attribute', $formulas_array)) {
			// Если [attributes] - то просто поместить в переменную [attributes] все атрибуты, которые идут по настройкам
			// Если [attribute index= - то создать переменнуые под каждый атрибут

			$s_attributes = '';

			$category_setting = $this->model_extension_module_seo_tags_generator->getSTGSettingsByCatId($category_id);

			if (isset($category_setting['attributes'])) {
				$attributes_setting = $category_setting['attributes'];
			} else {
				$attributes_setting = $this->config->get('module_seo_tags_generator_attributes');
			}

			$attr_i_exist = array();

			if (isset($attributes_setting) && count($attributes_setting) > 0) {
				$a_attributes = array();

				// Внимание!
				// Может быть так, что в формуле и настройках атрибут задан, а в самом товаре - нет

				foreach ($attribute_groups as $item) {
					foreach ($item['attribute'] as $attribute) {
						$a_attributes[$attribute['attribute_id']]['name']	 = $attribute['name'];
						$a_attributes[$attribute['attribute_id']]['text']	 = $attribute['text'];
					}
				}

				$i = 1; // индекс задается порядковым номером при переборе - но не ключом в массиве!!
				foreach ($attributes_setting as $attribute_id) {
					$attr_i_exist[] = $i;

					if (isset($a_attributes[$attribute_id])) {
						$s_attributes	 .= ($i > 1) ? '; ' : '';
						$s_attributes	 .= $a_attributes[$attribute_id]['name'] . ': ' . $a_attributes[$attribute_id]['text'];

						if ($this->isFollowedVar('attribute index="' . $i . '"', $formulas_array)) {
							$var_values['attribute index="' . $i . '"'] = $a_attributes[$attribute_id]['text'];
						}
					} else {
						if ($this->isFollowedVar('attribute index="' . $i . '"', $formulas_array)) {
							$var_values['attribute index="' . $i . '"'] = ''; // Заменяем переменную на пустоту
						}
					}

					$i++;
				}

				$var_values['attributes'] = $s_attributes;

				// Внимание!
				// Может случиться так, что переменные атрибутов в настройках не будут добавлены, но при этом будут использованы в формуле
				// В таком случае получим [attribute index="1"] с кавычками в формулах, а это привеет к ошибкам в мета-тегах

				$product_info['meta_title']				 = $this->excludeNotFollowedAttributesVars($product_info['meta_title'], $i, $attr_i_exist);
				$product_info['meta_description']	 = $this->excludeNotFollowedAttributesVars($product_info['meta_description'], $i, $attr_i_exist);
				$product_info['meta_keyword']			 = $this->excludeNotFollowedAttributesVars($product_info['meta_keyword'], $i, $attr_i_exist);

				$f_title			 = $this->excludeNotFollowedAttributesVars($f_title, $i, $attr_i_exist);
				$f_description = $this->excludeNotFollowedAttributesVars($f_description, $i, $attr_i_exist);
				$f_keyword		 = $this->excludeNotFollowedAttributesVars($f_keyword, $i, $attr_i_exist);
			}
		}

		// Category Level
		if ($this->isFollowedVar('category_level', $formulas_array)) {
			$var_values['category_level'] = $this->model_extension_module_seo_tags_generator->getCategoryLevel($category_id);

			// Levels for people begin with 1 (not with 0)
			if (false !== $var_values['category_level']) {
				$var_values['category_level']++;
			}
		}

		// Category Nested
		if ($this->isFollowedVar('category_nested', $formulas_array)) {
			// has category_nested without indexes
			// Index array begin with 1 (not 0)
			$categories_names = array();

			$categories_names0 = $this->model_extension_module_seo_tags_generator->getCategoriesNestedNames($category_id, $lang_id);

			foreach ($categories_names0 as $key => $value) {
				$categories_names[$key + 1] = $value['name']; // sort array start with index 1 (not 0)
			}

			// One time is found in all formulas!
			if ($this->isFollowedVar('category_nested SORT_FROM_PARENT_TO_CHILD', $formulas_array)) {
				$categories_names_reverse = array();

				foreach (array_reverse($categories_names) as $key => $value) {
					$categories_names_reverse[$key + 1] = $value; // sort array start with index 1 (not 0)
				}

				$var_values['category_nested SORT_FROM_PARENT_TO_CHILD'] = $this->stg->getCategoryNestedSortedValue($categories_names_reverse);

				if ($this->isFollowedVar('category_nested SORT_FROM_PARENT_TO_CHILD exclude', $formulas_array)) {
					$var_values = array_merge($var_values, $this->excludeCategories($formulas_array, $categories_names_reverse, 'SORT_FROM_PARENT_TO_CHILD'));
				}
			}

			// One time is found in all formulas!
			if ($this->isFollowedVar('category_nested SORT_FROM_CHILD_TO_PARENT', $formulas_array)) {
				$var_values['category_nested SORT_FROM_CHILD_TO_PARENT'] = $this->stg->getCategoryNestedSortedValue($categories_names);

				if ($this->isFollowedVar('category_nested SORT_FROM_CHILD_TO_PARENT exclude', $formulas_array)) {
					$var_values = array_merge($var_values, $this->excludeCategories($formulas_array, $categories_names, 'SORT_FROM_CHILD_TO_PARENT'));
				}
			}

			// One time is found in all formulas!
			if ($this->isFollowedVar('category_nested', $formulas_array)) {
				$var_values['category_nested'] = $this->stg->getCategoryNestedSortedValue($categories_names);

				if ($this->isFollowedVar('category_nested exclude', $formulas_array)) {
					$var_values = array_merge($var_values, $this->excludeCategories($formulas_array, $categories_names));
				}
			}
		}

		if ($this->isFollowedVar('category_nested sort', $formulas_array)) {
			// has category_nested with indexes

			$category_indexes = $this->stg->findCategoryNestedIndexes($formulas_array);

			$categories_keys = $this->stg->getCategoriesKeysForVars($category_indexes);

			//$this->stg->getCategoriesLevels($category_indexes);

			foreach ($category_indexes as $item) {
				$var_values[$item['key']] = $this->stg->getCategoryNestedSortedValue($categories_names, $item['sort']);
			}
		}
		
		if ($this->isFollowedVar('category_nested SORT_FROM_PARENT_TO_CHILD sort', $formulas_array)) {
			// has category_nested with indexes

			$category_indexes = $this->findCategoryNestedFromParent2ChildIndexes($formulas_array);

			$categories_keys = $this->stg->getCategoriesKeysForVars($category_indexes);
			
			//$this->stg->getCategoriesLevels($category_indexes);

			foreach ($category_indexes as $item) {
				$var_values[$item['key']] = $this->stg->getCategoryNestedSortedValue($categories_names_reverse, $item['sort']);
			}
		}

		// A! [original_text] must be last!
		if ($this->isFollowedVar('original_text', $formulas_array)) {
			$var_values['original_text'] = $this->stg->parse(html_entity_decode(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'), $var_values);
		}

		// Борьба с багом со скобками при использовании функций
		if (false !== strpos($var_values['product_name'], '(')) {
			$var_values['product_name'] = str_replace(array('(', ')'), array('left_bracket', 'right_bracket'), $var_values['product_name']);
		}

		if (false !== strpos($var_values['model'], '(')) {
			$var_values['model'] = str_replace(array('(', ')'), array('left_bracket', 'right_bracket'), $var_values['model']);
		}

		if (false !== strpos($var_values['sku'], '(')) {
			$var_values['sku'] = str_replace(array('(', ')'), array('left_bracket', 'right_bracket'), $var_values['sku']);
		}

		// Генерация мета-тегов в зависимости от настроек модуля
		$generate_mode = $this->config->get('module_seo_tags_generator_generate_mode_product');

		if ('nofollow' == $generate_mode) {
			// only vars replace, but no follow formulas
			// h1 is separated
			$product_info['meta_title']				 = $this->cleanup($this->stg->parse($product_info['meta_title'], $var_values));
			$product_info['meta_description']	 = $this->cleanup($this->stg->parse($product_info['meta_description'], $var_values));
			$product_info['meta_keyword']			 = $this->cleanup($this->stg->parse($product_info['meta_keyword'], $var_values));
		}

		if ('empty' == $generate_mode) {
			if (empty($product_info['meta_title'])) {
				$product_info['meta_title'] = $this->cleanup($this->stg->parse($f_title, $var_values));
			} else {
				$product_info['meta_title'] = $this->cleanup($this->stg->parse($product_info['meta_title'], $var_values));
			}

			if (empty($product_info['meta_description'])) {
				$product_info['meta_description'] = $this->cleanup($this->stg->parse($f_description, $var_values));
			} else {
				$product_info['meta_description'] = $this->cleanup($this->stg->parse($product_info['meta_description'], $var_values));
			}

			if (empty($product_info['meta_keyword'])) {
				$product_info['meta_keyword'] = $this->cleanup($this->stg->parse($f_keyword, $var_values));
			} else {
				$product_info['meta_keyword'] = $this->cleanup($this->stg->parse($product_info['meta_keyword'], $var_values));
			}
		}

		if ('forced' == $generate_mode) {
			$product_info['meta_title']				 = $this->cleanup($this->stg->parse($f_title, $var_values));
			$product_info['meta_description']	 = $this->cleanup($this->stg->parse($f_description, $var_values));
			$product_info['meta_keyword']			 = $this->cleanup($this->stg->parse($f_keyword, $var_values));
		}

		// Проверяем, не генерится ли H1 по формуле?
		$generate_mode_product_h1 = $this->config->get('module_seo_tags_generator_generate_mode_product_h1');

		// Заголовок в OpenCart Initial отсутствует, а name (из которого он берется) обязателен
		if ('nofollow' == $generate_mode_product_h1) {
			// only vars replace, but no follow formulas
			if ($h1) {
				$product_info[$h1] = $this->escapeBugParentheses($this->stg->parse($product_info[$h1], $var_values));
			}
		}

		if ('empty' == $generate_mode_product_h1) {
			if ($h1) {
				if (empty($product_info[$h1])) {
					$product_info[$h1] = $this->escapeBugParentheses($this->stg->parse($f_h1, $var_values));
				} else {
					$product_info[$h1] = $this->escapeBugParentheses($this->stg->parse($product_info[$h1], $var_values));
				}
			}
		}

		if ('forced' == $generate_mode_product_h1) {
			if ($h1) {
				$product_info[$h1] = $this->escapeBugParentheses($this->stg->parse($f_h1, $var_values));
			}
		}

		# Description - Text
		#
		// Description - is separated
		// for decode double htmlentities (1 in js in text editor + 1 on save process in DB)
		// $str3 = '&lt;[special]&gt;';
		// print_r(htmlentities($str3, ENT_QUOTES, 'UTF-8'));
		// &amp;lt;[special]&amp;gt;
		$product_text_tmp = html_entity_decode(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');

		$generate_mode_product_text = $this->config->get('module_seo_tags_generator_generate_mode_product_text');

		if ('nofollow' == $generate_mode_product_text) {
			// only vars replace, but no follow formulas
			$product_info['description'] = $this->escapeBugParentheses($this->stg->parse($product_text_tmp, $var_values));
		}

		if ('empty' == $generate_mode_product_text) {
			$tmp_descr = trim(str_replace('&nbsp;', '', strip_tags(html_entity_decode($product_info['description'], ENT_QUOTES, 'UTF-8'))));
			if (empty($tmp_descr)) {
				$product_info['description'] = $this->escapeBugParentheses($this->stg->parse($f_text, $var_values));
			} else {
				$product_info['description'] = $this->escapeBugParentheses($this->stg->parse($product_text_tmp, $var_values));
			}
		}

		if ('forced' == $generate_mode_product_text) {
			$product_info['description'] = $this->escapeBugParentheses($this->stg->parse($f_text, $var_values));
		}

		return $product_info;
	}

	public function getManufacturerTags($data) {
		// $this->stg->setLicence($this->config->get('module_seo_tags_generator_licence'));
		
		$manufacturer_info = $data['manufacturer_info'];

		$lang_id = $data['language_id'];

		foreach ($manufacturer_info as $key => $value) {
			$manufacturer_info[$key] = is_string($value) ? trim($value) : $value;
		}

		if (!$this->config->get('module_seo_tags_generator_status')) {
			return $manufacturer_info;
		}

		$this->load->model('extension/module/seo_tags_generator');

		$f_title = $this->config->get('module_seo_tags_generator_manufacturer_title');
		$f_title = html_entity_decode($f_title[$lang_id], ENT_QUOTES, 'UTF-8');

		$f_description = $this->config->get('module_seo_tags_generator_manufacturer_description');
		$f_description = html_entity_decode($f_description[$lang_id], ENT_QUOTES, 'UTF-8');

		$f_keyword = $this->config->get('module_seo_tags_generator_manufacturer_keyword');
		$f_keyword = html_entity_decode($f_keyword[$lang_id], ENT_QUOTES, 'UTF-8');

		$f_h1	 = $this->config->get('module_seo_tags_generator_manufacturer_h1');
		$f_h1	 = html_entity_decode($f_h1[$lang_id], ENT_QUOTES, 'UTF-8');

		$f_text	 = $this->config->get('module_seo_tags_generator_manufacturer_text');
		$f_text	 = html_entity_decode($f_text[$lang_id], ENT_QUOTES, 'UTF-8');

		// Чисто для isFollowedVar()
		$formulas_array = array(
			'title'				 => $f_title,
			'description'	 => $f_description,
			'keyword'			 => $f_keyword,
			'h1'					 => $f_h1,
			'text'				 => $f_text,
		);

		if (isset($manufacturer_info['meta_title'])) {
			$formulas_array['mi_meta_title'] = html_entity_decode($manufacturer_info['meta_title'], ENT_QUOTES, 'UTF-8');
		}

		if (isset($manufacturer_info['meta_description'])) {
			$formulas_array['mi_meta_description'] = html_entity_decode($manufacturer_info['meta_description'], ENT_QUOTES, 'UTF-8');
		}

		if (isset($manufacturer_info['meta_keyword'])) {
			$formulas_array['mi_meta_keyword'] = html_entity_decode($manufacturer_info['meta_keyword'], ENT_QUOTES, 'UTF-8');
		}

		if (isset($manufacturer_info['meta_h1'])) {
			$formulas_array['mi_h1'] = html_entity_decode($manufacturer_info['meta_h1'], ENT_QUOTES, 'UTF-8');
		}

		if (isset($manufacturer_info['description'])) {
			$formulas_array['mi_description'] = html_entity_decode($manufacturer_info['description'], ENT_QUOTES, 'UTF-8');
		}


		### Подготовка данных
		// Данные из $manufacturer_info по умолчанию, которые будут участвовать в заменах с помощью функций!
		$var_values = array(
			'manufacturer_name' => $manufacturer_info['name'],
		);

		if (isset($manufacturer_info['meta_h1'])) {
			$var_values['static_manufacturer_h1'] = $manufacturer_info['meta_h1'];
		} elseif (isset($manufacturer_info['h1'])) {
			$var_values['static_manufacturer_h1'] = $manufacturer_info['h1'];
		}

		if ($this->isFollowedVar('page_number', $formulas_array)) {
			$var_values['page_number'] = isset($this->request->get['page']) && $this->request->get['page'] ? $this->request->get['page'] : false;
		}

		if ($this->isFollowedVar('manufacturer_synonym', $formulas_array)) {
			$var_values['manufacturer_synonym'] = isset($manufacturer_info['name_synonym']) ? $manufacturer_info['name_synonym'] : '';
		}

		if ($this->isFollowedVar('shop_name', $formulas_array)) {
			$var_values['shop_name'] = $this->config->get('config_name');
		}

		if ($this->isFollowedVar('config_telephone', $formulas_array)) {
			$var_values['config_telephone'] = $this->config->get('config_telephone');
		}

		if ($this->isFollowedVar('city', $formulas_array)) {
			$config_store	= $this->config->get('config_store');

			$config_store_city	= $config_store[$lang_id];

			//$followed_variables[] = 'city'; // ... multiple

			$var_values['city']								 = $config_store_city['city'];
			$var_values['city_genitive']			 = $config_store_city['city_genitive'];
			$var_values['city_dative']				 = $config_store_city['city_dative'];
			$var_values['city_prepositional']	 = $config_store_city['city_prepositional'];
		}

		// A! [original_text] must be last!
		if ($this->isFollowedVar('original_text', $formulas_array)) {
			if (array_key_exists('description', $manufacturer_info)) {
				$var_values['original_text'] = $this->stg->parse(html_entity_decode(html_entity_decode($manufacturer_info['description'], ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8'), $var_values);
			} else {
				$var_values['original_text'] = '';
			}
		}

		// for no errors on OpenCart pure
		if (!isset($manufacturer_info['meta_title'])) {
			$manufacturer_info['meta_title'] = $manufacturer_info['name'];
		}

		if (!isset($manufacturer_info['meta_description'])) {
			$manufacturer_info['meta_description'] = '';
		}

		if (!isset($manufacturer_info['meta_keyword'])) {
			$manufacturer_info['meta_keyword'] = '';
		}

		// get min price of this manufacturer
		if ($this->isFollowedVar('min_price', $formulas_array)) {
			if (isset($manufacturer_info['min_price']) && $manufacturer_info['min_price']) {
				$var_values['min_price'] = $manufacturer_info['min_price'];
				
				goto min_price_of_manufacturer_end;
			}
			
			$min_price = $this->model_extension_module_seo_tags_generator->getMinPriceOfManufacturer($manufacturer_info['manufacturer_id']);

			if ($min_price) {
				$var_values['min_price'] = $min_price;
			} else {
				$var_values['min_price'] = 0;
			}
		}

		min_price_of_manufacturer_end:

		// get max price in this category
		if ($this->isFollowedVar('max_price', $formulas_array)) {
			if (isset($manufacturer_info['max_price']) && $manufacturer_info['max_price']) {
				$var_values['max_price'] = $manufacturer_info['max_price'];
				
				goto max_price_of_manufacturer_end;
			}
			
			$max_price = $this->model_extension_module_seo_tags_generator->getMaxPriceOfManufacturer($manufacturer_info['manufacturer_id']);

			if ($max_price) {
				$var_values['max_price'] = $max_price;
			} else {
				$var_values['max_price'] = 0;
			}
		}

		max_price_of_manufacturer_end:
		
		// Борьба с багом со скобками при использовании функций
		if (false !== strpos($var_values['manufacturer_name'], '(')) {
			$var_values['manufacturer_name'] = str_replace(array('(', ')'), array('left_bracket', 'right_bracket'), $var_values['manufacturer_name']);
		}

		// Генерация мета-тегов в зависимости от настроек модуля
		$generate_mode = $this->config->get('module_seo_tags_generator_generate_mode_manufacturer');

		if ('nofollow' == $generate_mode) {
			// only vars replace, but no follow formulas
			$manufacturer_info['meta_title']			 = $this->cleanup($this->stg->parse($manufacturer_info['meta_title'], $var_values));
			$manufacturer_info['meta_description'] = $this->cleanup($this->stg->parse($manufacturer_info['meta_description'], $var_values));
			$manufacturer_info['meta_keyword']		 = $this->cleanup($this->stg->parse($manufacturer_info['meta_keyword'], $var_values));
		}

		if ('empty' == $generate_mode) {
			if (empty($manufacturer_info['meta_title'])) {
				$manufacturer_info['meta_title'] = $this->cleanup($this->stg->parse($f_title, $var_values));
			} else {
				$manufacturer_info['meta_title'] = $this->cleanup($this->stg->parse($manufacturer_info['meta_title'], $var_values));
			}

			if (empty($manufacturer_info['meta_description'])) {
				$manufacturer_info['meta_description'] = $this->cleanup($this->stg->parse($f_description, $var_values));
			} else {
				$manufacturer_info['meta_description'] = $this->cleanup($this->stg->parse($manufacturer_info['meta_description'], $var_values));
			}

			if (empty($manufacturer_info['meta_keyword'])) {
				$manufacturer_info['meta_keyword'] = $this->cleanup($this->stg->parse($f_keyword, $var_values));
			} else {
				$manufacturer_info['meta_keyword'] = $this->cleanup($this->stg->parse($manufacturer_info['meta_keyword'], $var_values));
			}
		}

		if ('forced' == $generate_mode) {
			$manufacturer_info['meta_title']			 = $this->cleanup($this->stg->parse($f_title, $var_values));
			$manufacturer_info['meta_description'] = $this->cleanup($this->stg->parse($f_description, $var_values));
			$manufacturer_info['meta_keyword']		 = $this->cleanup($this->stg->parse($f_keyword, $var_values));
		}

		// Проверяем, не генерится ли H1 по формуле?
		$generate_mode_manufacturer_h1 = $this->config->get('module_seo_tags_generator_generate_mode_manufacturer_h1');

		// Заголовок в OpenCart Initial отсутствует, а name (из которого он берется) обязателен
		if ('nofollow' == $generate_mode_manufacturer_h1) {
			// only vars replace, but no follow formulas
			if (isset($manufacturer_info['meta_h1']) && $manufacturer_info['meta_h1']) {
				$manufacturer_info['meta_h1'] = $this->escapeBugParentheses($this->stg->parse($manufacturer_info['meta_h1'], $var_values));
			} else {
				$manufacturer_info['name'] = $this->escapeBugParentheses($this->stg->parse($manufacturer_info['name'], $var_values)); // A! OpenCart Initial - for catalog ONLY
			}
		}

		if ('empty' == $generate_mode_manufacturer_h1) {
			if (isset($manufacturer_info['meta_h1'])) {
				if (empty($manufacturer_info['meta_h1'])) {
					$manufacturer_info['meta_h1'] = $this->escapeBugParentheses($this->stg->parse($f_h1, $var_values));
				} else {
					$manufacturer_info['meta_h1'] = $this->escapeBugParentheses($this->stg->parse($manufacturer_info['meta_h1'], $var_values));
				}
			} else {
				if (empty($manufacturer_info['name'])) {
					$manufacturer_info['name'] = $this->escapeBugParentheses($this->stg->parse($f_h1, $var_values));
				} else {
					$manufacturer_info['name'] = $this->escapeBugParentheses($this->stg->parse($manufacturer_info['name'], $var_values)); // A! OpenCart Initial - for catalog ONLY
				}
			}
		}

		if ('forced' == $generate_mode_manufacturer_h1) {
			if (isset($manufacturer_info['meta_h1'])) {
				$manufacturer_info['meta_h1'] = $this->escapeBugParentheses($this->stg->parse($f_h1, $var_values));
			} else {
				$manufacturer_info['name'] = $this->escapeBugParentheses($this->stg->parse($f_h1, $var_values)); // A! OpenCart Initial - for catalog ONLY
			}
		}

		# Description - Text
		#
		// Description - is separated
		// for decode double htmlentities (1 in js in text editor + 1 on save process in DB)
		// In OpenCart pure manufacturer don't hase description
		if (isset($manufacturer_info['description'])) {
			$manufacturer_text_tmp = html_entity_decode(html_entity_decode($manufacturer_info['description'], ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
		} else {
			$manufacturer_text_tmp = false;
		}

		$generate_mode_manufacturer_text = $this->config->get('module_seo_tags_generator_generate_mode_manufacturer_text');

		if ('nofollow' == $generate_mode_manufacturer_text) {
			// only vars replace, but no follow formulas
			if ($manufacturer_text_tmp) {
				$manufacturer_info['description'] = $this->escapeBugParentheses($this->stg->parse($manufacturer_text_tmp, $var_values));
			}
		}

		if ('empty' == $generate_mode_manufacturer_text) {
			if ($manufacturer_text_tmp) {
				$tmp_descr = trim(str_replace('&nbsp;', '', strip_tags(html_entity_decode($manufacturer_info['description'], ENT_QUOTES, 'UTF-8'))));
				if (empty($tmp_descr)) {
					$manufacturer_info['description'] = $this->escapeBugParentheses($this->stg->parse($f_text, $var_values));
				} else {
					$manufacturer_info['description'] = $this->escapeBugParentheses($this->stg->parse($manufacturer_text_tmp, $var_values));
				}
			} else {
				$manufacturer_info['description'] = $this->escapeBugParentheses($this->stg->parse($f_text, $var_values));
			}
		}

		if ('forced' == $generate_mode_manufacturer_text) {
			$manufacturer_info['description'] = $this->escapeBugParentheses($this->stg->parse($f_text, $var_values));
		}

		return $manufacturer_info;
	}

	/*
	 * Check if is followed var
	 */

	private function isFollowedVar($var_key, $array) {
		// !A - [city is multiple vars: [city], [city_genitive]
		// => "[$var_key"

		foreach ($array as $key => $value) {
			if (false !== strpos($array[$key], "[$var_key")) {
				return true;
			}
		}

		return false;
	}

	/*
	 * Follow cleanup only for meta tags!!
	 * Replace " - to &quot;
	 * A! No follow htmlentities($str, ENT_QUOTES, "UTF-8");
	 * Data inserted from admin is processed with htmlentities
	 */

	private function cleanup($string) {
		$string	 = strip_tags($string); // от Лайтшоп
		$string	 = $this->escapeBugParentheses($string);

		return $string;
	}

	private function escapeBugParentheses($string) {
		$string	 = trim(preg_replace(array('/\s+/', '/\s\./', '/\"/'), array(' ', '.', '&quot;'), $string)); // Убрать двойные пробелы - некоторые криво вписывают названия товаров и формулы
		$string	 = str_replace(array('left_bracket', 'right_bracket'), array('(', ')'), $string); // Борьба с багом со скобками при использовании функций
		return $string;
	}

	/*
	 * Remove not followed attributes vars from meta-tags
	 */

	private function excludeNotFollowedAttributesVars($value, $i, $attr_i_exist) {
		$n = 1;

		while ($n <= $i) {
			if (!in_array($n, $attr_i_exist)) {
				$value = str_replace('[attribute index="' . $n . '"]', '', $value);
			}

			$n++;
		}

		return $value;
	}

	/*
	 * Exclude Categories
	 */

	private function excludeCategories($formulas_array, $catgories_names, $flag = false) {
		$result = array();

		$category_keys_exist = array();

		$category_nested_followed = array();

		$string = implode($formulas_array);

		// if no flag no 2 spaces!
		$s_find	 = '\[category_nested ';
		$s_find	 .= $flag ? $flag . ' ' : '';
		$s_find	 .= 'exclude="(.*?)"\s*\]';

		preg_match_all('|' . $s_find . '|s', $string, $matches_foo, PREG_SET_ORDER);

		if (count($matches_foo) > 0) {
			foreach ($matches_foo as $key => $item) {
				if (!in_array($item[0], $category_keys_exist)) {
					$category_keys_exist[]										 = $item[0];
					$category_nested_followed[$key]['key']		 = $categories_keys[$key]										 = str_replace(array('[', ']'), array('', ''), $item[0]);
					$category_nested_followed[$key]['exclude'] = $item[1];
				}
			}
		}

		foreach ($category_nested_followed as $item) {
			$a_exclude = explode(',', trim($item['exclude']));

			foreach ($a_exclude as $key => $value) {
				$value = trim($value);
				if (!empty($value)) {
					$a_exclude[$key] = trim($value);
				} else {
					unset($a_exclude[$key]);
				}
			}

			$catgories_names1 = $catgories_names;

			foreach ($a_exclude as $a_exclude_value) {
				unset($catgories_names1[$a_exclude_value]);
			}

			$out1 = '';

			$i = 0;
			foreach ($catgories_names1 as $item1) {
				$out1	 .= $i ? ' ' : '';
				$out1	 .= $item1;
				$i++;
			}

			$result[$item['key']] = $out1;
		}

		return $result;
	}
	
	// tmp
	public function findCategoryNestedFromParent2ChildIndexes($array) {
		$category_keys_exist = array();
		$category_levels = array();

		// Format array to string
		$string = implode($array);

		// find all matches
		preg_match_all('|\[category_nested SORT_FROM_PARENT_TO_CHILD sort="(.*?)"\s*\]|s', $string, $matches_foo, PREG_SET_ORDER);

		if (count($matches_foo) > 0) {
			foreach ($matches_foo as $key => $item) {
				if (!in_array($item[0], $category_keys_exist)) {
					$category_keys_exist[] = $item[0];
					$category_levels[$key]['key'] = $categories_keys[$key] = str_replace(array('[', ']'), array('', ''), $item[0]);
					$category_levels[$key]['sort'] = $this->getIndexesArray($item[1]);
				}
			}
		}

		return $category_levels;
	}
	
	// tmp
	private function getIndexesArray($string) {
		// Получаю массив индексов с очищением от возможных пробелов и пустых значений
		$a_sort = explode(',', trim($string));

		foreach ($a_sort as $key => $value) {
			$value = trim($value);
			if (!empty($value)) {
				$a_sort[$key] = trim($value);
			} else {
				unset($a_sort[$key]);
			}
		}

		return $a_sort;
	}

}
