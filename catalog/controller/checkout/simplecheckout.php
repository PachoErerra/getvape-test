<?php
/*
@author Dmitriy Kubarev
@link   http://www.simpleopencart.com
*/

include_once(DIR_SYSTEM . 'library/simple/simple_controller.php');

class ControllerCheckoutSimpleCheckout extends SimpleController {
    private $_templateData = array();

    public function index($args = null) {

        if ($this->customer->isLogged()) {
            $customerId = $this->customer->getId();

            $query = $this->db->query("SELECT * FROM  " . DB_PREFIX . "customer_simple_fields WHERE customer_id = '". $customerId ."'");

            if (!$query->num_rows) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "customer_simple_fields SET callme = 1, metadata = 'callme', sms = '', customer_id = '". $customerId ."'");
            }

            if (isset($this->request->post['customer']['callme']) && $this->customer->isLogged()) {
                $this->db->query("UPDATE " . DB_PREFIX . "customer_simple_fields SET callme = ". (int)$this->request->post['customer']['callme'] ." WHERE customer_id = '". $customerId ."'");
            }
        }

        $this->loadLibrary('simple/simplecheckout');

        $settingsGroup = !empty($args['group']) ? $args['group'] : (!empty($this->request->get['group']) ? $this->request->get['group'] : $this->config->get('simple_default_checkout_group'));

        $this->simplecheckout = SimpleCheckout::getInstance($this->registry, $settingsGroup);

        if (!$this->customer->isLogged() && $this->simplecheckout->isGuestCheckoutDisabled()) {
            $this->session->data['redirect'] = $this->url->link('checkout/simplecheckout', '', 'SSL');
            $this->simplecheckout->redirect($this->url->link('account/login','','SSL'));
        }

        $this->language->load('checkout/checkout');
        $this->language->load('checkout/simplecheckout');

        $this->language->load('checkout/simplecheckout_summary');
        $this->language->load('checkout/simplecheckout_customer');
        $this->language->load('checkout/simplecheckout_comment');

        $this->_templateData['text_telegram_nick_modal_info'] = $this->language->get('text_telegram_nick_modal_info');
        $this->_templateData['text_telegram_nick_modal_title'] = $this->language->get('text_telegram_nick_modal_title');
        $this->_templateData['text_telegram_nick_modal_save'] = $this->language->get('text_telegram_nick_modal_save');
        $this->_templateData['error_telegram_nick_modal'] = $this->language->get('error_telegram_nick_modal');

        if (empty($args)) {
            $this->document->setTitle($this->language->get('heading_title'));
        }

        $this->_templateData['breadcrumbs'] = array();

        $this->_templateData['breadcrumbs'][] = array(
            'text'      => $this->language->get('text_home'),
            'href'      => $this->url->link('common/home'),
            'separator' => false
        );

        if (!$this->config->get('simple_replace_cart')) {
            $this->_templateData['breadcrumbs'][] = array(
                'text' => $this->language->get('text_cart'),
                'href' => $this->url->link('checkout/cart'),
                'separator' => $this->language->get('text_separator')
            );
        }

        $this->_templateData['breadcrumbs'][] = array(
            'text'      => $this->language->get('heading_title'),
            'href'      => $this->url->link('checkout/simplecheckout', '', 'SSL'),
            'separator' => $this->language->get('text_separator')
        );

        $this->_templateData['action'] = 'index.php?'.$this->simplecheckout->getAdditionalParams().'route=checkout/simplecheckout&group='.$settingsGroup;

        $this->_templateData['heading_title'] = $this->language->get('heading_title');

        $this->simplecheckout->clearPreventDeleteFlag();
        $this->simplecheckout->clearSimpleSession();

        $this->_templateData['error_warning'] = '';
        $this->_templateData['reorder_warning'] = '';

        if (!empty($this->session->data['reorder_warning'])) {
            $this->_templateData['reorder_warning'] = $this->session->data['reorder_warning'];

            // Keep for the first full page paint; clear after the following AJAX refresh
            if ($this->simplecheckout->isAjaxRequest()) {
                unset($this->session->data['reorder_warning']);
            }
        }

        $this->simplecheckout->initBlocks();

        if ($this->simplecheckout->getOpencartVersion() >= 230) {
            $this->tax->unsetRates();
            $this->tax->setStoreAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
        }

        // stupid hack
        if (isset($this->session->data['simple']['customer']['customer_group_id']) && $this->session->data['simple']['customer']['customer_group_id'] === '') {
            $this->session->data['simple']['customer']['customer_group_id'] = $this->config->get('config_customer_group_id');
        }

        $this->getChildController('checkout/simplecheckout_customer/update_session');
        $this->getChildController('checkout/simplecheckout_payment_address/update_session');
        $this->getChildController('checkout/simplecheckout_shipping_address/update_session');
        $this->getChildController('checkout/simplecheckout_cart/update');

        if ($this->cart->hasProducts() || !empty($this->session->data['vouchers'])) {
            $this->abandoned();

            $this->_templateData['simple_blocks'] = array(
                'customer'         => '',
                'payment_address'  => '',
                'shipping_address' => '',
                'cart'             => '',
                'shipping'         => '',
                'payment'          => '',
                'agreement'        => '',
                'help'             => '',
                'summary'          => '',
                'comment'          => '',
                'payment_form'     => ''
            );

            // stupid hack for opencart > 2.0
            if ($this->simplecheckout->getOpencartVersion() >= 200) {
                if ($this->simplecheckout->getOpencartVersion() < 220) {
                    $this->tax = new Tax($this->registry);
                    $this->cart = new Cart($this->registry);
                } else {
                    //$this->tax = new Cart\Tax($this->registry);
                    //$this->cart = new Cart\Cart($this->registry);
                }
            }
            // end

            if ($this->simplecheckout->isPaymentBeforeShipping()) {
                $this->_templateData['simple_blocks']['payment']  = $this->getChildController('checkout/simplecheckout_payment');
                $this->_templateData['simple_blocks']['shipping'] = $this->getChildController('checkout/simplecheckout_shipping');
            } else {
                $this->_templateData['simple_blocks']['shipping'] = $this->getChildController('checkout/simplecheckout_shipping');
                $this->_templateData['simple_blocks']['payment']  = $this->getChildController('checkout/simplecheckout_payment');
            }

            $this->_templateData['simple_blocks']['cart']             = $this->getChildController('checkout/simplecheckout_cart');
            $this->_templateData['simple_blocks']['customer']         = $this->getChildController('checkout/simplecheckout_customer');
            $this->_templateData['simple_blocks']['payment_address']  = $this->getChildController('checkout/simplecheckout_payment_address');
            $this->_templateData['simple_blocks']['shipping_address'] = $this->getChildController('checkout/simplecheckout_shipping_address');

            if ($this->simplecheckout->hasBlock('agreement')) {
                $this->_templateData['simple_blocks']['agreement'] = $this->getChildController('checkout/simplecheckout_text', 'agreement');
            }

            if ($this->simplecheckout->hasBlock('help')) {
                $this->_templateData['simple_blocks']['help'] = $this->getChildController('checkout/simplecheckout_text', 'help');
            }

            if ($this->simplecheckout->hasBlock('comment')) {
                $this->_templateData['simple_blocks']['comment'] = $this->getChildController('checkout/simplecheckout_comment');
            }

            $modules = $this->simplecheckout->getModules();

            foreach ($modules as $m) {
                $modulesPath = 'controller/module/';
                if ($this->simplecheckout->getOpencartVersion() >= 230) {
                    $modulesPath = 'controller/extension/module/';
                }

                if ($m != 'payment_simple' && file_exists(DIR_APPLICATION . $modulesPath . $m . '.php')) {
                    $defaultSettings = array('limit' => 5, 'width' => 100, 'height' => 100, 'banner_id' => 6, 'position' => 'top', 'layout_id' => 0);

                    $allSettings = $this->config->get($m . '_module');

                    $this->load->model('design/layout');
                    $currentLayoutId = $this->model_design_layout->getLayout('checkout/simplecheckout');

                    if (!empty($allSettings) && is_array($allSettings)) {
                        $found = false;
                        foreach ($allSettings as $s) {
                            if ($s['layout_id'] == $currentLayoutId) {
                                $defaultSettings = $s;
                                $found = true;
                                break;
                            }
                        }
                        if (!$found) {
                            $defaultSettings = reset($allSettings);
                        }
                    }

                    $this->_templateData['simple_blocks'][$m] = $this->getChildController('module/'.$m, $defaultSettings);
                } elseif ($m == 'payment_simple') {
                    if ($this->simplecheckout->getSettingValue('ignorePayment')) {
                        $this->_templateData['simple_blocks'][$m] = '';
                    } else {
                        $payment_method = $this->session->data['payment_method'];

                        $additonal_path = '';

                        if ($this->simplecheckout->getOpencartVersion() >= 230) {
                            $additonal_path = 'extension/';
                        }

                        if (!empty($payment_method['code']) && file_exists(DIR_APPLICATION . 'controller/' . $additonal_path . 'module/' . $payment_method['code'] . '.php')) {
                            $this->_templateData['simple_blocks'][$m] = $this->getChildController($additonal_path . 'module/'.$payment_method['code']);
                        } elseif (!empty($payment_method['code']) && file_exists(DIR_APPLICATION . 'controller/' . $additonal_path . 'module/' . $payment_method['code'] . '_simple.php')) {
                            $this->_templateData['simple_blocks'][$m] = $this->getChildController($additonal_path . 'module/'.$payment_method['code'].'_simple');
                        } else {
                            $this->_templateData['simple_blocks'][$m] = '';
                        }
                    }
                }
            }

            if ($this->simplecheckout->hasBlock('summary')) {
                $this->_templateData['simple_blocks']['summary'] = $this->getChildController('checkout/simplecheckout_summary');
            }

            $this->_templateData['block_order'] = $this->simplecheckout->isOrderBlocked();

            if ($this->request->server['REQUEST_METHOD'] == 'POST') {
                $this->_templateData['agreements'] = !empty($this->request->post['agreements']) ? $this->request->post['agreements'] : array();
            } else {
                if ($this->simplecheckout->getSettingValue('agreementCheckboxInit')) {
                    if ($this->simplecheckout->getSettingValue('agreementType') == 2) {
                        $agreement_id = $this->simplecheckout->getSettingValue('agreementId');
                        $agreements = $this->simplecheckout->getSettingValue('agreementIds');

                        if ($agreement_id) {
                            $this->_templateData['agreements'] = array($agreement_id);
                        } elseif (!empty($agreements) && is_array($agreements)) {
                            $this->_templateData['agreements'] = $agreements;
                        } else {
                            $this->_templateData['agreements'] = array();
                        }
                    } else {
                        $this->_templateData['agreements'] = array('all');
                    }
                } else {
                    $this->_templateData['agreements'] = array();
                }
            }


            $stateChanged = false;

            // Create DB order only on explicit "Оформити" click (or ignorePayment).
            // ASAP alone used to spam status=0 drafts; confirm then often died in JS.
            $explicitCreate = !empty($this->request->post['create_order']);
            $ignorePayment = (bool)$this->simplecheckout->getSettingValue('ignorePayment');
            $this->_templateData['simple_redirect_url'] = '';

            if ($this->validate() && !$this->simplecheckout->isOrderBlocked() && $this->simplecheckout->canCreateOrder() && ($explicitCreate || $ignorePayment)) {
                if (!$this->customer->isLogged()) {
                    $this->simplecheckout->clearUnusedFields();
                }

                $stateChanged = $this->saveCustomerInfo();
                $order_id = $this->order();

                if ($order_id) {
                if ($ignorePayment) {
                    $redirect_url = $this->url->link('checkout/success');
                    $this->_templateData['simple_redirect_url'] = $redirect_url;
                    $this->_templateData['simple_blocks']['payment_form'] = '<input type="hidden" id="simple_redirect_url" value="' . $redirect_url . '">';

                    $this->confirm();
                } else {
                    $payment_method = !empty($this->session->data['payment_method']) ? $this->session->data['payment_method'] : array();
                    $paymentCode = !empty($payment_method['code']) ? explode('.', $payment_method['code']) : array('');
                    $paymentCode0 = $paymentCode[0];

                    // COD / bank_transfer: confirm on server, do not rely on hidden JS button
                    if ($explicitCreate && $this->confirmSimplePaymentMethod($paymentCode0)) {
                        $this->simplecheckout->setPreventDeleteFlag();
                        $redirect_url = $this->url->link('checkout/success');
                        $this->_templateData['simple_redirect_url'] = $redirect_url;
                        $this->_templateData['simple_blocks']['payment_form'] = '<input type="hidden" id="simple_redirect_url" value="' . $redirect_url . '">';
                    } else {
                        $requestMethod = $this->request->server['REQUEST_METHOD'];
                        $this->request->server['REQUEST_METHOD'] = 'GET';

                        $this->_templateData['simple_blocks']['payment_form'] = $this->getChildController('payment/' . $paymentCode0);

                        $this->request->server['REQUEST_METHOD'] = $requestMethod;
                    }
                }
                }
            }

            if ($stateChanged) {
                $this->simplecheckout->initBlocks(true);

                $this->getChildController('checkout/simplecheckout_customer/update_session');
                $this->getChildController('checkout/simplecheckout_payment_address/update_session');
                $this->getChildController('checkout/simplecheckout_shipping_address/update_session');

                $this->_templateData['simple_blocks']['customer'] = $this->getChildController('checkout/simplecheckout_customer');
                $this->_templateData['simple_blocks']['payment_address'] = $this->getChildController('checkout/simplecheckout_payment_address');
                $this->_templateData['simple_blocks']['shipping_address'] = $this->getChildController('checkout/simplecheckout_shipping_address');
            }
        }

        $this->_templateData['ajax']                             = $this->simplecheckout->isAjaxRequest();
        $this->_templateData['weight']                           = $this->simplecheckout->displayWeight() ? $this->weight->format($this->cart->getWeight(), $this->config->get('config_weight_class_id'), $this->language->get('decimal_point'), $this->language->get('thousand_point')) : '';
        $this->_templateData['additional_path']                  = $this->simplecheckout->getAdditionalPath();
        $this->_templateData['additional_params']                = $this->simplecheckout->getAdditionalParams();
        $this->_templateData['login_type']                       = $this->simplecheckout->getSettingValue('loginType');
        $this->_templateData['current_theme']                    = $this->config->get('config_template');
        $this->_templateData['simple_template']                  = $this->simplecheckout->getTemplate();
        $this->_templateData['logged']                           = $this->customer->isLogged();
        $this->_templateData['steps_count']                      = $this->simplecheckout->getStepsCount();
        $this->_templateData['step_names']                       = $this->simplecheckout->getStepsNames();
        $this->_templateData['step_buttons']                     = json_encode($this->simplecheckout->getStepsButtons());
        $this->_templateData['display_agreement_checkbox']       = $this->simplecheckout->getSettingValue('displayAgreementCheckbox');
        $this->_templateData['agreement_checkbox_step']          = $this->simplecheckout->getSettingValue('agreementCheckboxStep');

        $this->_templateData['order_blocked']                    = $this->simplecheckout->isOrderBlocked();
        $this->_templateData['javascript_callback']              = $this->simplecheckout->getJavascriptCallback();

        $this->_templateData['display_error']                    = $this->simplecheckout->displayError();
        $this->_templateData['has_error']                        = $this->simplecheckout->hasError('agreement');
        $this->_templateData['display_weight']                   = $this->simplecheckout->displayWeight();
        $this->_templateData['display_back_button']              = $this->simplecheckout->getSettingValue('displayBackButton');
        $this->_templateData['display_proceed_text']             = $this->simplecheckout->getSettingValue('displayProceedText');
        $this->_templateData['scroll_to_error']                  = $this->simplecheckout->getSettingValue('scrollToError');
        $this->_templateData['scroll_to_payment_form']           = $this->simplecheckout->getSettingValue('scrollToPaymentForm');
        $this->_templateData['left_column_width']                = $this->simplecheckout->getSettingValue('leftColumnWidth');
        $this->_templateData['right_column_width']               = $this->simplecheckout->getSettingValue('rightColumnWidth');
        $this->_templateData['use_autocomplete']                 = $this->simplecheckout->getCommonSetting('useAutocomplete');
        $this->_templateData['enable_reloading_of_payment_form'] = $this->simplecheckout->getSettingValue('enableAutoReloaingOfPaymentFrom');
        $this->_templateData['menu_type']                        = $this->simplecheckout->getSettingValue('menuType');
        $this->_templateData['language_code']                    = isset($this->session->data['language']) && strlen($this->session->data['language']) > 0 && strlen($this->session->data['language']) < 6 ? $this->session->data['language'] : $this->config->get('config_language');

        $this->_templateData['errors'] = array();

        $errors = $this->simplecheckout->getErrors();
        $forceTopErrors = !empty($this->request->post['create_order']);

        if (!empty($errors) && is_array($errors)) {
            foreach ($errors as $error) {
                if ($error['block'] == 'agreement') {
                    continue;
                }

                // On create_order click show every block error at the top (theme often hides in-block errors)
                if ($forceTopErrors || !$this->simplecheckout->hasBlock($error['block'])) {
                    $this->_templateData['errors'][] = $error['text'] ? $error['text'] : $this->language->get('error_' . $error['block']);
                }
            }
        }

        if (!isset($this->_templateData['simple_redirect_url'])) {
            $this->_templateData['simple_redirect_url'] = '';
        }

        $this->_templateData['popup']     = !empty($args['popup']) ? true : (isset($this->request->get['popup']) ? true : false);
        $this->_templateData['as_module'] = !empty($args['module']) ? true : (isset($this->request->get['module']) ? true : false);

        if ($this->_templateData['popup']) {
            $links = $this->simplecheckout->getScriptsAndStyles();

            $this->_templateData['simple_styles'] = $links['styles'];
            $this->_templateData['simple_scripts'] = $links['scripts'];
        }

        $this->_templateData['text_proceed_payment']    = $this->language->get('text_proceed_payment');
        $this->_templateData['text_payment_form_title'] = $this->language->get('text_payment_form_title');
        $this->_templateData['text_need_save_changes']  = $this->language->get('text_need_save_changes');
        $this->_templateData['text_saving_changes']     = $this->language->get('text_saving_changes');
        $this->_templateData['text_cart']               = $this->language->get('text_cart');
        $this->_templateData['text_please_confirm']     = $this->language->get('text_please_confirm');
        $this->_templateData['button_save_changes']     = $this->language->get('button_save_changes');
        $this->_templateData['button_order']            = $this->language->get('button_order');
        $this->_templateData['button_back']             = $this->language->get('button_back');
        $this->_templateData['button_prev']             = $this->language->get('button_prev');
        $this->_templateData['button_next']             = $this->language->get('button_next');
        $this->_templateData['group']                   = $settingsGroup;
        $this->_templateData['cart_empty']              = !$this->cart->hasProducts() && empty($this->session->data['vouchers']);
        $this->_templateData['text_error']              = $this->language->get('text_empty');
        $this->_templateData['button_continue']         = $this->language->get('button_continue');
        $this->_templateData['continue']                = $this->url->link('common/home');
        $this->_templateData['use_storage']             = false;

        $this->_templateData['scroll_to_error']         = $this->simplecheckout->getCommonSetting('scrollingChanged') ? $this->simplecheckout->getCommonSetting('scrollToError') : $this->simplecheckout->getSettingValue('scrollToError');

        $this->_templateData['notification_default']    = $this->simplecheckout->getCommonSetting('notificationChanged') ? $this->simplecheckout->getCommonSetting('notificationDefault') : true;
        $this->_templateData['notification_toasts']     = $this->simplecheckout->getCommonSetting('notificationToasts');
        $this->_templateData['notification_position']   = $this->simplecheckout->getCommonSetting('notificationPosition');
        $this->_templateData['notification_timeout']    = $this->simplecheckout->getCommonSetting('notificationTimeout');
        $this->_templateData['notification_check_form'] = $this->simplecheckout->getCommonSetting('notificationCheckForm');

        $this->_templateData['notification_check_form_text'] = '';

        $notification_check_form_text = $this->simplecheckout->getCommonSetting('notificationCheckFormText');

        $language_code = $this->simplecheckout->getCurrentLanguageCode();

        if (!empty($notification_check_form_text) && !empty($notification_check_form_text[$language_code])) {
            $this->_templateData['notification_check_form_text'] = $notification_check_form_text[$language_code];
        }

        $minicart = $this->simplecheckout->getSettingValue('minicartText', 'cart');

        $text_items = '';
        $language_code = $this->simplecheckout->getCurrentLanguageCode();

        if ($minicart && !empty($minicart[$language_code])) {
            $text_items = $minicart[$language_code];
        }

        if (!$text_items) {
            $this->language->load('checkout/cart');
            $text_items = $this->language->get('text_items');
            $this->language->load('checkout/simplecheckout');
        }

        if (strpos($text_items, '{quantity}') !== false || strpos($text_items, '{total}') !== false) {
            $find = array(
                '{quantity}',
                '{total}'
            );

            $replace = array(
                '{quantity}' => 0,
                '{total}' => $this->simplecheckout->formatCurrency(0)
            );

            $this->_templateData['cart_total'] = str_replace($find, $replace, $text_items);
        } else {
            $this->_templateData['cart_total'] = sprintf($text_items, 0, $this->simplecheckout->formatCurrency(0));
        }

        $this->_templateData['customer_with_payment_address']  = $this->simplecheckout->isCustomerCombinedWithPaymentAddress();
        $this->_templateData['customer_with_shipping_address'] = $this->simplecheckout->isCustomerCombinedWithShippingAddress();

        if ($this->_templateData['display_agreement_checkbox']) {
            $disable_popup = $this->_templateData['popup'] ? true : $this->simplecheckout->getSettingValue('agreementDisablePopup');

            if (!$disable_popup) {
                $seo_url = $this->config->get('config_seo_url');
                $this->config->set('config_seo_url', false);
            }

            $agreement_id = $this->simplecheckout->getSettingValue('agreementId');
            $lang_id = ($this->config->get('config_template') == 'shoppica' || $this->config->get('config_template') == 'shoppica2') ? 'text_agree_shoppica' : 'text_agree';
            $agreement_text = $this->language->get($lang_id);

            if ($disable_popup) {
                $agreement_text = str_replace('href=', 'target="_blank" href=', $agreement_text);
                $agreement_text = preg_replace('/colorbox|fancybox|agree/', '', $agreement_text);
            }

            $this->_templateData['text_agreements'] = array();

            if ($agreement_id) {
                $title = $this->simplecheckout->getInformationTitle($agreement_id);

                if ($this->simplecheckout->getSettingValue('agreementType') == 2) {
                    $this->_templateData['text_agreements'][$agreement_id] = sprintf($agreement_text, $this->url->link($this->simplecheckout->getInformationRoute($disable_popup), $this->simplecheckout->getAdditionalParams() . 'information_id=' . $agreement_id, 'SSL'), $title, $title);
                } else {
                    $this->_templateData['text_agreements']['all'] = sprintf($agreement_text, $this->url->link($this->simplecheckout->getInformationRoute($disable_popup), $this->simplecheckout->getAdditionalParams() . 'information_id=' . $agreement_id, 'SSL'), $title, $title);
                }

                $errors = array();

                $errors[$agreement_id] = sprintf($this->language->get('error_agree'), $title);

                $this->_templateData['error_warning_agreement'] = $errors;
            } else {
                $agreements = $this->simplecheckout->getSettingValue('agreementIds');
                if (!empty($agreements) && is_array($agreements)) {
                    if ($this->simplecheckout->getSettingValue('agreementType') == 2) {
                        $errors = array();

                        foreach ($agreements as $agreement_id) {
                            $title = $this->simplecheckout->getInformationTitle($agreement_id);

                            $this->_templateData['text_agreements'][$agreement_id] = sprintf($agreement_text, $this->url->link($this->simplecheckout->getInformationRoute($disable_popup), $this->simplecheckout->getAdditionalParams() . 'information_id=' . $agreement_id, 'SSL'), $title, $title);

                            $errors[$agreement_id] = sprintf($this->language->get('error_agree'), $title);
                        }

                        $this->_templateData['error_warning_agreement'] = $errors;
                    } else {
                        $agreement_link = '';

                        if (@preg_match('/<a.+a>/', $agreement_text, $matches)) {
                            $agreement_link = $matches[0];
                            $agreement_text = @preg_replace('/<a.+a>/', '%s', $agreement_text);
                        }

                        $links = array();
                        $errors = array();

                        foreach ($agreements as $agreement_id) {
                            $title = $this->simplecheckout->getInformationTitle($agreement_id);

                            $links[] = sprintf($agreement_link, $this->url->link($this->simplecheckout->getInformationRoute($disable_popup), $this->simplecheckout->getAdditionalParams() . 'information_id=' . $agreement_id, 'SSL'), $title, $title);

                            $errors[$agreement_id] = sprintf($this->language->get('error_agree'), $title);
                        }

                        $this->_templateData['text_agreements']['all'] = sprintf($agreement_text, implode(', ', $links));

                        $this->_templateData['error_warning_agreement'] = $errors;
                    }
                }
            }

            if (!$disable_popup) {
                $this->config->set('config_seo_url', $seo_url);
            }
        }

        $childrens = array();

        if (!$this->simplecheckout->isAjaxRequest() && !$this->_templateData['popup'] && !$this->_templateData['as_module']) {
            $childrens = array(
                'common/column_left',
                'common/column_right',
                'common/content_top',
                'common/content_bottom',
                'common/footer',
                'common/header',
            );

            $this->_templateData['simple_header'] = $this->simplecheckout->getLinkToHeaderTpl();
            $this->_templateData['simple_footer'] = $this->simplecheckout->getLinkToFooterTpl();
        }

        $this->setOutputContent(trim($this->renderPage('checkout/simplecheckout', $this->_templateData, $childrens)));
    }

    public function abandoned() {
        if ($this->request->server['REQUEST_METHOD'] == 'POST' && empty($this->request->post['simple_ajax']) && empty($this->session->data['human'])) {
            return;
        }

        $opencart_version = explode('.', VERSION);
        $opencart_version = floatval($opencart_version[0].$opencart_version[1].$opencart_version[2].'.'.(isset($opencart_version[3]) ? $opencart_version[3] : 0));

        $products = array();

        if ($opencart_version >= 200) {
            $this->load->model('tool/upload');
        }

        if ($opencart_version < 210) {
            $this->loadLibrary('encryption');
        }

        foreach ($this->cart->getProducts() as $product) {
            if ($opencart_version < 220) {
                $price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')));
                $total = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity']);
            } else {
                $price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                $total = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')) * $product['quantity'], $this->session->data['currency']);
            }

            $option_data = array();

            foreach ($product['option'] as $option) {
                if ($opencart_version >= 200) {
                    if ($option['type'] != 'file') {
                        $value = $option['value'];
                    } else {
                        $upload_info = $this->model_tool_upload->getUploadByCode($option['value']);

                        if ($upload_info) {
                            $value = $upload_info['name'];
                        } else {
                            $value = '';
                        }
                    }
                } else {
                    if ($option['type'] != 'file') {
                        $value = $option['option_value'];
                    } else {
                        $encryption = new Encryption($this->config->get('config_encryption'));
                        $value = $encryption->decrypt($option['option_value']);
                    }
                }

                $option_data[] = array(
                    'name'  => $option['name'],
                    'value' => $value
                );
            }

            $products[] = array(
                'product_id' => $product['product_id'],
                'name'       => $product['name'],
                'model'      => $product['model'],
                'option'     => $option_data,
                'quantity'   => $product['quantity'],
                'price'      => $price,
                'total'      => $total,
                'href'       => htmlspecialchars_decode($this->url->link('product/product', 'product_id=' . $product['product_id']))
            );
        }

        $data = array(
            'simple_cart_id' => !empty($this->session->data['simple_cart_id']) ? $this->session->data['simple_cart_id'] : 0,
            'store_id' => $this->config->get('store_id'),
            'customer_id' => $this->customer->isLogged() ? $this->customer->getId() : '',
            'email' => '',
            'firstname' => '',
            'lastname' => '',
            'telephone' => '',
            'fax' => '',
            'products' => $products
        );

        if ($this->request->server['REQUEST_METHOD'] == 'GET') {
            $from = isset($this->session->data['simple']) ? $this->session->data['simple'] : array();
        } else {
            $from = $this->request->post;
        }

        if (!empty($from['customer']) && !empty($from['customer']['email'])) {
            $data['email'] = $from['customer']['email'];
        }

        if (!empty($from['customer']) && !empty($from['customer']['telephone'])) {
            $data['telephone'] = $from['customer']['telephone'];
        }

        if (!empty($from['customer']) && !empty($from['customer']['fax'])) {
            $data['fax'] = $from['customer']['fax'];
        }

        foreach (array('payment_address', 'shipping_address', 'customer') as $block) {
            if (!empty($from[$block])) {
                if (!empty($from[$block]['firstname'])) {
                    $data['firstname'] = $from[$block]['firstname'];
                }

                if (!empty($from[$block]['lastname'])) {
                    $data['lastname'] = $from[$block]['lastname'];
                }
            }
        }

        if (!empty($data['products']) && (!empty($data['email']) || !empty($data['telephone']))) {
            $this->load->model('tool/simpleapi');

            $cart_id = $this->model_tool_simpleapi->updateAbandonedCart($data);

            $this->session->data['simple_cart_id'] = $cart_id;
        }
    }

    private function validate() {
        $error = false;

        if ($this->simplecheckout->getSettingValue('displayAgreementCheckbox')) {
            $agreement_id = $this->simplecheckout->getSettingValue('agreementId');
            $agreements = $this->simplecheckout->getSettingValue('agreementIds');

            if ($agreement_id) {
                $agreements = array($agreement_id);
            } elseif (!empty($agreements) && is_array($agreements)) {
                $agreements = $agreements;
            } else {
                $agreements = array();
            }

            foreach ($agreements as $agreement_id) {
                $find = $agreement_id;

                if ($this->simplecheckout->getSettingValue('agreementType') != 2) {
                    $find = 'all';
                }

                if (!in_array($find, $this->_templateData['agreements'])) {
                    $this->simplecheckout->addError('agreement');
                    $error = true;
                }
            }
        }

        $errors = $this->simplecheckout->getErrors();

        if (!empty($errors)) {
            $error = true;
        }

        if (!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) {
            $error = true;
        }

        $this->load->model('extension/module/vapors_punch_gift');

        if ($this->model_extension_module_vapors_punch_gift->isActive() && !$this->model_extension_module_vapors_punch_gift->validate()) {
            $this->session->data['vapors_punch_gift_invalid'] = true;
            $this->simplecheckout->addError('cart', 'Оберіть смак подарунка Vapors X Punch');
            $this->simplecheckout->blockOrder();
            $error = true;
        }

        // Promo reward gift must not be ordered without a still-valid coupon
        $this->load->model('extension/total/coupon');
        if (!$this->model_extension_total_coupon->syncCouponGiftState()) {
            $this->load->language('checkout/simplecheckout');
            $this->simplecheckout->addError('cart', $this->language->get('error_coupon'));
            $this->simplecheckout->blockOrder();
            $error = true;
        } elseif (!empty($this->session->data['coupon_gift'])) {
            $coupon_info = $this->model_extension_total_coupon->getCoupon($this->session->data['coupon']);
            if (!$coupon_info || $coupon_info['type'] != 'M') {
                $this->model_extension_total_coupon->clearCouponGift();
                unset($this->session->data['coupon']);
                $this->load->language('checkout/simplecheckout');
                $this->simplecheckout->addError('cart', $this->language->get('error_coupon'));
                $this->simplecheckout->blockOrder();
                $error = true;
            }
        }

        if (!$this->validateCheckoutMobilePhones()) {
            $error = true;
        }

        return !$error;
    }

    /**
     * Полная проверка UA-мобильных на checkout: телефон клиента и получателя.
     */
    private function validateCheckoutMobilePhones() {
        $operators = array(
            '031', '032', '033', '034', '035', '036', '037', '041',
            '043', '044', '045', '046', '047', '048', '049', '050',
            '051', '052', '053', '054', '055', '056', '057', '061',
            '062', '063', '064', '066', '067', '068', '073', '091',
            '092', '093', '094', '095', '096', '097', '098', '099',
            '077', '075'
        );

        $ok = true;

        $customer_telephone = '';
        if (!empty($this->request->post['customer']['telephone'])) {
            $customer_telephone = $this->request->post['customer']['telephone'];
        } elseif (!empty($this->session->data['simple']['customer']['telephone'])) {
            $customer_telephone = $this->session->data['simple']['customer']['telephone'];
        } elseif ($this->customer->isLogged()) {
            $customer_telephone = $this->customer->getTelephone();
        }

        if ($customer_telephone !== '' && !$this->isValidUaMobilePhone($customer_telephone, $operators)) {
            $this->simplecheckout->addError('customer', 'Вкажіть правильний код мобільного оператора!');
            $ok = false;
        }

        $ya = 0;
        if (isset($this->request->post['shipping_address']['ya'])) {
            $ya = (int)$this->request->post['shipping_address']['ya'];
        } elseif (isset($this->session->data['simple']['shipping_address']['ya'])) {
            $ya = (int)$this->session->data['simple']['shipping_address']['ya'];
        }

        if (!$ya) {
            $receiver_phone = '';
            if (!empty($this->request->post['shipping_address']['receiver_phone'])) {
                $receiver_phone = $this->request->post['shipping_address']['receiver_phone'];
            } elseif (!empty($this->session->data['simple']['shipping_address']['receiver_phone'])) {
                $receiver_phone = $this->session->data['simple']['shipping_address']['receiver_phone'];
            }

            if ($receiver_phone !== '' && !$this->isValidUaMobilePhone($receiver_phone, $operators)) {
                $this->simplecheckout->addError('shipping_address', 'Вкажіть правильний код мобільного оператора!');
                $ok = false;
            }
        }

        return $ok;
    }

    private function isValidUaMobilePhone($phone, $operators) {
        $phone = trim((string)$phone);

        // Маска Simple: +38(0XX)XXX-XX-XX
        if (preg_match('/^\+38\((\d{3})\)(\d{3})-?(\d{2})-?(\d{2})$/', $phone, $m)) {
            $code = $m[1];
            $national = $m[1] . $m[2] . $m[3] . $m[4];
        } else {
            $digits = preg_replace('/\D/', '', $phone);

            if (preg_match('/^0\d{9}$/', $digits)) {
                $national = $digits;
                $code = substr($digits, 0, 3);
            } elseif (preg_match('/^380\d{9}$/', $digits)) {
                $national = '0' . substr($digits, 3);
                $code = substr($national, 0, 3);
            } else {
                return false;
            }
        }

        if (!in_array($code, $operators, true)) {
            return false;
        }

        // Очевидно фейковые: 0000000000 / 0500000000 / 0123456789
        if (preg_match('/^(\d)\1{9}$/', $national)) {
            return false;
        }

        if (preg_match('/^(\d)\1{6}$/', substr($national, 3))) {
            return false;
        }

        if (in_array($national, array('0123456789', '1234567890'), true) || substr($national, 1) === '123456789') {
            return false;
        }

        return true;
    }

    public function prevent_delete() {
        $this->loadLibrary('simple/simplecheckout');

        $this->simplecheckout = SimpleCheckout::getInstance($this->registry);

        $this->simplecheckout->setPreventDeleteFlag();

        echo 'success';
    }

    private function saveCustomerInfo() {
        $stateChanged = false;

        if (isset($this->request->post['ignore_post'])) {
            return $stateChanged;
        }

        if (!$this->customer->isLogged()) {
            if ($this->session->data['simple']['customer']['register'] && !empty($this->session->data['simple']['customer']['email'])) {
                $this->load->model('account/customer');
                $this->load->model('account/address');

                // fix for old versions
                $tmpCustomerGroupId = $this->config->get('config_customer_group_id');
                $this->config->set('config_customer_group_id', $this->session->data['simple']['customer']['customer_group_id']);

                $info = array_merge($this->session->data['simple']['payment_address'], $this->session->data['simple']['customer']);

                $info['custom_field'] = array(
                    'account' => isset($this->session->data['simple']['customer']['custom_field']) ? $this->session->data['simple']['customer']['custom_field'] : array(),
                    'address' => isset($this->session->data['simple']['payment_address']['custom_field']) ? $this->session->data['simple']['payment_address']['custom_field'] : array()
                );

                if (empty($info['password'])) {
                    $this->load->model('tool/simpleapimain');

                    if ($this->config->get('simple_disable_method_checking')) {
                        $info['password'] = $this->model_tool_simpleapimain->getRandomPassword();
                    } else {
                        if (method_exists($this->model_tool_simpleapimain, 'getRandomPassword') || property_exists($this->model_tool_simpleapimain, 'getRandomPassword') || (method_exists($this->model_tool_simpleapimain, 'isExistForSimple') && $this->model_tool_simpleapimain->isExistForSimple('getRandomPassword'))) {
                            $info['password'] = $this->model_tool_simpleapimain->getRandomPassword();
                        }
                    }
                }

                $this->model_account_customer->addCustomer($info);

                $this->config->set('config_customer_group_id', $tmpCustomerGroupId);

                $this->session->data['simple']['registered'] = true;

                $this->customer->login($this->session->data['simple']['customer']['email'], $info['password']);

                $customerId = 0;
                $addressId = 0;

                if ($this->simplecheckout->getOpencartVersion() < 300) {
                    if ($this->customer->isLogged()) {
                        $customerId = $this->customer->getId();
                        $addressId = $this->customer->getAddressId();
                    } else {
                        $customerInfo = $this->simplecheckout->getCustomerInfoByEmail($info['email']);
                        $customerId = $customerInfo['customer_id'];
                        $addressId = $customerInfo['address_id'];
                    }
                } else {
                    if ($this->customer->isLogged()) {
                        $customerId = $this->customer->getId();
                    } else {
                        $customerInfo = $this->simplecheckout->getCustomerInfoByEmail($info['email']);
                        $customerId = $customerInfo['customer_id'];
                    }

                    if (!$this->simplecheckout->isAddressEmpty($info)) {
                        $info['default'] = true;

                        if ($this->simplecheckout->getOpencartVersion() < 300) {
                            $addressId = $this->model_account_address->addAddress($info);
                        } else {
                            $addressId = $this->model_account_address->addAddress($customerId, $info);
                        }

                        // stupid hack for opencart >= 3.0
                        if ($this->simplecheckout->getOpencartVersion() >= 300) {
                            $this->customer = new Cart\Customer($this->registry);
                            $this->customer->login($this->session->data['simple']['customer']['email'], $info['password']);
                        }
                        // end
                    }
                }

                if ($this->customer->isLogged()) {
                    // stupid hack for opencart > 2.1
                    if ($this->simplecheckout->getOpencartVersion() >= 210) {
                        if ($this->simplecheckout->getOpencartVersion() < 220) {
                            $this->cart = new Cart($this->registry);
                        } else {
                            $this->cart = new Cart\Cart($this->registry);
                        }
                    }
                    // end

                    $stateChanged = true;
                }

                if (($this->simplecheckout->getOpencartVersion() > 200 && $this->simplecheckout->getOpencartVersion() < 230) || ($this->simplecheckout->getOpencartVersion() >= 230 && $this->config->get('config_customer_activity'))) {
                    // Add to activity log
                    $this->load->model('account/activity');

                    $activity_data = array(
                        'customer_id' => $customerId,
                        'name'        => $info['firstname'] . ' ' . $info['lastname']
                    );

                    $this->model_account_activity->addActivity('register', $activity_data);
                }

                $this->session->data['simple']['customer']['customer_id'] = $customerId;
                $this->session->data['simple']['payment_address']['address_id'] = $addressId;

                $this->simplecheckout->replaceAddressIdInPostRequest('payment_address', $this->session->data['simple']['payment_address']['address_id']);

                $this->simplecheckout->saveCustomFields(array('customer'), 'customer', $customerId);

                if (!empty($this->session->data['simple']['payment_address']['address_id'])) {
                    $this->simplecheckout->saveCustomFields(array('payment_address', 'payment'), 'address', $this->session->data['simple']['payment_address']['address_id']);
                }

                if (!$this->simplecheckout->isBlockHidden('shipping_address') && !$this->simplecheckout->isAddressSame()) {
                    if ($this->simplecheckout->getOpencartVersion() < 300) {
                        $this->session->data['simple']['shipping_address']['address_id'] = $this->model_account_address->addAddress($this->session->data['simple']['shipping_address']);
                    } else {
                        $this->session->data['simple']['shipping_address']['address_id'] = $this->model_account_address->addAddress($this->customer->getId(), $this->session->data['simple']['shipping_address']);
                    }

                    $this->simplecheckout->replaceAddressIdInPostRequest('shipping_address', $this->session->data['simple']['shipping_address']['address_id']);

                    if (!empty($this->session->data['simple']['shipping_address']['address_id'])) {
                        $this->simplecheckout->saveCustomFields(array('shipping_address', 'shipping'), 'address', $this->session->data['simple']['shipping_address']['address_id']);
                    }
                }
            }
        } else {
            $this->load->model('account/customer');
            $this->load->model('account/address');

            if (!$this->simplecheckout->isBlockHidden('customer')) {
                unset($this->session->data['simple']['customer']['password']);

                if ($this->simplecheckout->getOpencartVersion() < 300) {
                    $this->model_account_customer->editCustomer($this->session->data['simple']['customer']);
                } else {
                    $this->model_account_customer->editCustomer($this->customer->getId(), $this->session->data['simple']['customer']);
                }

                $this->simplecheckout->saveCustomFields(array('customer'), 'customer', $this->customer->getId());

                if ($this->simplecheckout->isFieldUsed('customer_group_id', 'customer')) {
                    $this->simplecheckout->editCustomerGroupId($this->session->data['simple']['customer']['customer_group_id']);
                }
            }

            if ((!$this->simplecheckout->isBlockHidden('payment_address') || !empty($this->session->data['simple']['payment'])) && !isset($this->request->post['payment_address']['ignore_post'])) {
                if ($this->session->data['simple']['payment_address']['address_id']) {
                    $this->model_account_address->editAddress($this->session->data['simple']['payment_address']['address_id'], $this->session->data['simple']['payment_address']);
                    $this->simplecheckout->saveCustomFields(array('payment_address', 'payment'), 'address', $this->session->data['simple']['payment_address']['address_id']);
                } else {
                    if ($this->simplecheckout->getOpencartVersion() < 300) {
                        $this->session->data['simple']['payment_address']['address_id'] = $this->model_account_address->addAddress($this->session->data['simple']['payment_address']);
                    } else {
                        $this->session->data['simple']['payment_address']['address_id'] = $this->model_account_address->addAddress($this->customer->getId(), $this->session->data['simple']['payment_address']);
                    }

                    $this->simplecheckout->replaceAddressIdInPostRequest('payment_address', $this->session->data['simple']['payment_address']['address_id']);
                    $this->simplecheckout->saveCustomFields(array('payment_address', 'payment'), 'address', $this->session->data['simple']['payment_address']['address_id']);
                }

                $stateChanged = true;
            }

            if ((!$this->simplecheckout->isBlockHidden('shipping_address') || !empty($this->session->data['simple']['shipping'])) && !isset($this->request->post['shipping_address']['ignore_post']) && ($this->simplecheckout->isBlockHidden('payment_address') || (!$this->simplecheckout->isBlockHidden('payment_address') && !$this->simplecheckout->isAddressSame()))) {
                if ($this->session->data['simple']['shipping_address']['address_id']) {
                    $this->model_account_address->editAddress($this->session->data['simple']['shipping_address']['address_id'], $this->session->data['simple']['shipping_address']);
                    $this->simplecheckout->saveCustomFields(array('shipping_address', 'shipping'), 'address', $this->session->data['simple']['shipping_address']['address_id']);
                } else {
                    if ($this->simplecheckout->getOpencartVersion() < 300) {
                        $this->session->data['simple']['shipping_address']['address_id'] = $this->model_account_address->addAddress($this->session->data['simple']['shipping_address']);
                    } else {
                        $this->session->data['simple']['shipping_address']['address_id'] = $this->model_account_address->addAddress($this->customer->getId(), $this->session->data['simple']['shipping_address']);
                    }

                    $this->simplecheckout->replaceAddressIdInPostRequest('shipping_address', $this->session->data['simple']['shipping_address']['address_id']);
                    $this->simplecheckout->saveCustomFields(array('shipping_address', 'shipping'), 'address', $this->session->data['simple']['shipping_address']['address_id']);
                }

                $stateChanged = true;
            }
        }

        return $stateChanged;
    }

    private function order() {
        $existing_id = $this->findRecentDuplicateOrderId();

        if ($existing_id) {
            $this->session->data['order_id'] = $existing_id;
            $this->simplecheckout->setPreventDeleteFlag();
            return $existing_id;
        }

        $this->simplecheckout->clearOrder();

        // Final guard: never create an order with unrealistic / overstock quantities
        $this->load->model('catalog/product');
        $this->load->model('extension/module/vapors_punch_gift');
        $gift_ids = $this->model_extension_module_vapors_punch_gift->getGiftCartIds();

        foreach ($this->cart->getProducts() as $product) {
            $cart_id = (int)$product['cart_id'];
            $qty = (int)$product['quantity'];
            $max = 99;

            if (in_array($cart_id, $gift_ids, true)) {
                $max = 1;
            } else {
                $product_info = $this->model_catalog_product->getProduct($product['product_id']);
                if ($product_info && !empty($product_info['subtract'])) {
                    // Online orders only from the 4 warehouses (product.quantity)
                    $stock = (int)$product_info['quantity'];
                    if ($stock > 0) {
                        $max = min($max, $stock);
                    }
                }
            }

            if ($max < 1) {
                $max = 1;
            }

            if ($qty > $max || $qty < 1) {
                $this->cart->update($cart_id, $qty > $max ? $max : 1);
                $this->simplecheckout->addError('cart');
                $this->simplecheckout->blockOrder();
                return 0;
            }
        }

        // NP directory final guard temporarily disabled until Select2 rewrite

        $customer_info    = $this->session->data['simple']['customer'];
        $payment_address  = $this->session->data['simple']['payment_address'];
        $shipping_address = $this->session->data['simple']['shipping_address'];

        $comment          = $this->simplecheckout->getComment();
        $version          = $this->simplecheckout->getOpencartVersion();

        $payment_method   = array();
        $shipping_method  = array();

        if ($this->simplecheckout->getSettingValue('clearUnusedFields')) {
            $shipping_address_fields = $this->simplecheckout->getFieldsInBlock('shipping_address');

            foreach ($shipping_address as $field => $value) {
                if (in_array($field, ['custom_field', 'address_id'])) {
                    continue;
                }

                if (!in_array($field, $shipping_address_fields)) {
                    $shipping_address[$field] = '';
                }
            }
        }

        if (!$this->simplecheckout->getSettingValue('ignorePayment') && isset($this->session->data['payment_method'])) {
            $payment_method = $this->session->data['payment_method'];
        }

        $ignore_shipping = $this->simplecheckout->getSettingValue('ignoreShipping');

        if (!$ignore_shipping && isset($this->session->data['shipping_method'])) {
            $shipping_method = $this->session->data['shipping_method'];

            // Threshold uses the payable goods amount, after bonuses and discounts
            $cart_subtotal = get_cart_amount_without_shipping($this->registry);
            $shipping_code = !empty($shipping_method['code']) ? $shipping_method['code'] : '';
            $free_post = (float)$this->config->get('config_free_post');
            $free_boy = (float)$this->config->get('config_free_boy');
            $shipping_free = false;

            if ($shipping_code && in_array($shipping_code, array('novaposhta.department', 'novaposhta.poshtomat', 'novaposhta.doors')) && $free_post > 0 && $cart_subtotal >= $free_post) {
                $shipping_free = true;
            }

            if ($shipping_code == 'flat.flat' && $free_boy > 0 && $cart_subtotal >= $free_boy) {
                $shipping_free = true;
            }

            if ($this->config->get('config_hh_freedelivery_on')) {
                $today = date_create(date('Y-m-d H:i'));
                $start_hh_date = date_create($this->config->get('config_hh_freedelivery_start'));
                $end_hh_date = date_create($this->config->get('config_hh_freedelivery_end'));
                if ($today >= $start_hh_date && $today <= $end_hh_date) {
                    $shipping_free = true;
                }
            }

            if ($shipping_free) {
                $shipping_method['cost'] = 0;
                $this->session->data['shipping_method']['cost'] = 0;
            }
        }

        if (empty($customer_info['email'])) {
            $emptyEmail = $this->simplecheckout->getSettingValue('emptyEmail', 'customer');

            if (!empty($emptyEmail)) {
                $customer_info['email'] = $emptyEmail;
            } else {
                $customer_info['email'] = 'guest' .time() . '@' . str_replace('www.', '', $_SERVER['HTTP_HOST']);
            }
        }

        $totals = array();
        $total = 0;
        $taxes = $this->cart->getTaxes();

        $total_data = array(
            'totals' => &$totals,
            'taxes'  => &$taxes,
            'total'  => &$total
        );

        $sort_order = array();

        if ($version < 200 || $version >= 300) {
            $this->load->model('setting/extension');

            $results = $this->model_setting_extension->getExtensions('total');
        } else {
            $this->load->model('extension/extension');

            $results = $this->model_extension_extension->getExtensions('total');
        }

        foreach ($results as $key => $result) {
            if ($version < 300) {
                $sort_order[$key] = $this->config->get($result['code'] . '_sort_order');
            } else {
                $sort_order[$key] = $this->config->get('total_' . $result['code'] . '_sort_order');
            }
        }

        array_multisort($sort_order, SORT_ASC, $results);

        $shipping_cost = isset($shipping_method['cost']) ? (float)$shipping_method['cost'] : 0;
        $skip_zero_cost_shipping = $this->simplecheckout->getSettingValue('skipZeroCostShipping', 'cart');

        foreach ($results as $result) {
            if ($version < 300) {
                $status = $this->config->get($result['code'] . '_status');
            } else {
                $status = $this->config->get('total_' . $result['code'] . '_status');
            }

            if ($result['code'] == 'shipping') {
                if ($shipping_cost > 0 && !$ignore_shipping) {
                    $status = true;
                } elseif ((!$shipping_cost && $skip_zero_cost_shipping) || $ignore_shipping) {
                    $status = false;
                }
            }

            if ($status) {
                $this->simplecheckout->loadModel('total/' . $result['code']);

                if ($version < 220) {
                    $this->{'model_total_' . $result['code']}->getTotal($totals, $total, $taxes);
                } else {
                    $this->{'model_total_' . $result['code']}->getTotal($total_data);
                }
            }
        }

        $sort_order = array();

        foreach ($totals as $key => $value) {
            $sort_order[$key] = $value['sort_order'];

            if (!isset($value['text'])) {
                $totals[$key]['text'] = $this->simplecheckout->formatCurrency($value['value']);
            }

            if (!empty($value['code']) && $value['code'] == 'shipping' && isset($this->session->data['shipping_method']) && isset($this->session->data['shipping_method']['text'])) {
                $totals[$key]['text'] = $this->session->data['shipping_method']['text'];
            }
        }

        array_multisort($sort_order, SORT_ASC, $totals);

        $data = array();

        $data['invoice_prefix'] = $this->config->get('config_invoice_prefix');
        $data['store_id'] = $this->config->get('config_store_id');
        $data['store_name'] = $this->config->get('config_name');

        if ($data['store_id']) {
            $data['store_url'] = $this->config->get('config_url');
        } else {
            $data['store_url'] = HTTP_SERVER;
        }

        $data['customer_id']            = $customer_info['customer_id'];
        $data['customer_group_id']      = $customer_info['customer_group_id'];
        $data['firstname']              = $customer_info['firstname'];
        $data['lastname']               = $customer_info['lastname'];
        $data['email']                  = $customer_info['email'];
        $data['telephone']              = $customer_info['telephone'];
        $data['fax']                    = !empty($customer_info['fax']) ? $customer_info['fax'] : '';
        $data['custom_field']           = isset($customer_info['custom_field']) ? $customer_info['custom_field'] : array();

        $data['payment_firstname']      = $payment_address['firstname'];
        $data['payment_lastname']       = $payment_address['lastname'];
        $data['payment_company']        = $payment_address['company'];
        $data['payment_address_1']      = $payment_address['address_1'];
        $data['payment_address_2']      = $payment_address['address_2'];
        $data['payment_city']           = $payment_address['city'];
        $data['payment_postcode']       = $payment_address['postcode'];
        $data['payment_zone']           = $payment_address['zone'];
        $data['payment_zone_id']        = $payment_address['zone_id'];
        $data['payment_country']        = $payment_address['country'];
        $data['payment_country_id']     = $payment_address['country_id'];
        $data['payment_address_format'] = $payment_address['address_format'];
        $data['payment_company_id']     = isset($payment_address['company_id']) ? $payment_address['company_id'] : '';
        $data['payment_tax_id']         = isset($payment_address['tax_id']) ? $payment_address['tax_id'] : '';
        $data['payment_custom_field']   = isset($payment_address['custom_field']) ? $payment_address['custom_field'] : array();

        if (isset($payment_method['title'])) {
            $data['payment_method'] = $payment_method['title'];
        } else {
            $data['payment_method'] = '';
        }

        if (isset($payment_method['code'])) {
            $data['payment_code'] = $payment_method['code'];
        } else {
            $data['payment_code'] = '';
        }

        if ($this->simplecheckout->hasShipping()) {
            $data['shipping_firstname']      = $shipping_address['firstname'];
            $data['shipping_lastname']       = $shipping_address['lastname'];
            $data['shipping_company']        = $shipping_address['company'];
            $data['shipping_address_1']      = $shipping_address['address_1'];
            $data['shipping_address_2']      = $shipping_address['address_2'];
            $data['shipping_city']           = $shipping_address['city'];
            $data['shipping_postcode']       = $shipping_address['postcode'];
            $data['shipping_zone']           = $shipping_address['zone'];
            $data['shipping_zone_id']        = $shipping_address['zone_id'];
            $data['shipping_country']        = $shipping_address['country'];
            $data['shipping_country_id']     = $shipping_address['country_id'];
            $data['shipping_address_format'] = $shipping_address['address_format'];
            $data['shipping_custom_field']   = isset($shipping_address['custom_field']) ? $shipping_address['custom_field'] : array();

            if (isset($shipping_method['title'])) {
                $data['shipping_method'] = $shipping_method['title'];
            } else {
                $data['shipping_method'] = '';
            }

            if (isset($shipping_method['code'])) {
                $data['shipping_code'] = $shipping_method['code'];
            } else {
                $data['shipping_code'] = '';
            }
        } else {
            $data['shipping_firstname']      = '';
            $data['shipping_lastname']       = '';
            $data['shipping_company']        = '';
            $data['shipping_address_1']      = '';
            $data['shipping_address_2']      = '';
            $data['shipping_city']           = '';
            $data['shipping_postcode']       = '';
            $data['shipping_zone']           = '';
            $data['shipping_zone_id']        = '';
            $data['shipping_country']        = '';
            $data['shipping_country_id']     = '';
            $data['shipping_address_format'] = '';
            $data['shipping_method']         = '';
            $data['shipping_code']           = '';
            $data['shipping_custom_field']   = array();
        }

        $data['payment_address_format'] = $this->simplecheckout->getAddressFormat($data, 'payment');
        $data['shipping_address_format'] = $this->simplecheckout->getAddressFormat($data, 'shipping');

        $product_data = array();

        if ($version < 152) {

            if (method_exists($this->tax,'setZone')) {
                if ($this->simplecheckout->hasShipping()) {
                    $this->tax->setZone($data['shipping_country_id'], $data['shipping_zone_id']);
                } else {
                    $this->tax->setZone($data['payment_country_id'], $data['payment_zone_id']);
                }
            }

            $this->loadLibrary('encryption');

            foreach ($this->cart->getProducts() as $product) {
                $option_data = array();

                foreach ($product['option'] as $option) {
                    if ($option['type'] != 'file') {
                        $option_data[] = array(
                            'product_option_id'       => $option['product_option_id'],
                            'product_option_value_id' => $option['product_option_value_id'],
                            'product_option_id'       => $option['product_option_id'],
                            'product_option_value_id' => $option['product_option_value_id'],
                            'option_id'               => $option['option_id'],
                            'option_value_id'         => $option['option_value_id'],
                            'name'                    => $option['name'],
                            'value'                   => $option['option_value'],
                            'type'                    => $option['type']
                        );
                    } else {
                        $encryption = new Encryption($this->config->get('config_encryption'));

                        $option_data[] = array(
                            'product_option_id'       => $option['product_option_id'],
                            'product_option_value_id' => $option['product_option_value_id'],
                            'product_option_id'       => $option['product_option_id'],
                            'product_option_value_id' => $option['product_option_value_id'],
                            'option_id'               => $option['option_id'],
                            'option_value_id'         => $option['option_value_id'],
                            'name'                    => $option['name'],
                            'value'                   => $encryption->decrypt($option['option_value']),
                            'type'                    => $option['type']
                        );
                    }
                }

                $product_data[] = array(
                    'product_id' => $product['product_id'],
                    'name'       => $product['name'],
                    'model'      => $product['model'],
                    'option'     => $option_data,
                    'download'   => $product['download'],
                    'quantity'   => $product['quantity'],
                    'subtract'   => $product['subtract'],
                    'price'      => $product['price'],
                    'total'      => $product['total'],
                    'tax'        => method_exists($this->tax,'getRate') ? $this->tax->getRate($product['tax_class_id']) : $this->tax->getTax($product['price'], $product['tax_class_id'])
                );
            }

            // Gift Voucher
            if (isset($this->session->data['vouchers']) && $this->session->data['vouchers']) {
                foreach ($this->session->data['vouchers'] as $voucher) {
                    $product_data[] = array(
                        'product_id' => 0,
                        'name'       => $voucher['description'],
                        'model'      => '',
                        'option'     => array(),
                        'download'   => array(),
                        'quantity'   => 1,
                        'subtract'   => false,
                        'price'      => $voucher['amount'],
                        'total'      => $voucher['amount'],
                        'tax'        => 0
                    );
                }
            }

            $data['products'] = $product_data;
            $data['totals'] = $totals;
            $data['comment'] = $comment;
            $data['total'] = $total;
            $data['reward'] = $this->cart->getTotalRewardPoints();
        } elseif ($version >= 152) {
            foreach ($this->cart->getProducts() as $product) {
                $option_data = array();

                foreach ($product['option'] as $option) {
                    if ($version >= 200) {
                        $value = $option['value'];
                    } else {
                        if ($option['type'] != 'file') {
                            $value = $option['option_value'];
                        } else {
                            $value = $this->encryption->decrypt($option['option_value']);
                        }
                    }

                    $option_data[] = array(
                        'product_option_id'       => $option['product_option_id'],
                        'product_option_value_id' => $option['product_option_value_id'],
                        'option_id'               => $option['option_id'],
                        'option_value_id'         => $option['option_value_id'],
                        'name'                    => $option['name'],
                        'value'                   => $value,
                        'type'                    => $option['type']
                    );
                }

                $product_data[] = array(
                    'product_id' => $product['product_id'],
                    'name'       => $product['name'],
                    'model'      => $product['model'],
                    'option'     => $option_data,
                    'download'   => $product['download'],
                    'quantity'   => $product['quantity'],
                    'subtract'   => $product['subtract'],
                    'price'      => $product['price'],
                    'total'      => $product['total'],
                    'tax'        => $this->tax->getTax($product['price'], $product['tax_class_id']),
                    'reward'     => $product['reward']
                );
            }

            // Gift Voucher
            $voucher_data = array();

            if (!empty($this->session->data['vouchers'])) {
                foreach ($this->session->data['vouchers'] as $voucher) {
                    $voucher_data[] = array(
                        'description'      => $voucher['description'],
                        'code'             => substr(md5(rand()), 0, 10),
                        'to_name'          => $voucher['to_name'],
                        'to_email'         => $voucher['to_email'],
                        'from_name'        => $voucher['from_name'],
                        'from_email'       => $voucher['from_email'],
                        'voucher_theme_id' => $voucher['voucher_theme_id'],
                        'message'          => $voucher['message'],
                        'amount'           => $voucher['amount']

                    );
                }
            }

            $data['products'] = $product_data;
            $data['vouchers'] = $voucher_data;
            $data['totals'] = $totals;
            $data['comment'] = $comment;
            $data['total'] = $total;
        }

        $data['affiliate_id'] = 0;
        $data['commission'] = 0;
        $data['marketing_id'] = 0;
        $data['tracking'] = '';
        if ($customer_info && $customer_info['customer_id']) {
            $this->load->model('account/customer');

            $full_customer_info = $this->model_account_customer->getCustomer($customer_info['customer_id']);

            if ($full_customer_info['referal'] && $full_customer_info['referal_date'] && !$this->getTotalOrders() && !$full_customer_info['referal_used'] && $customer_info['customer_id'] != $full_customer_info['referal']) {
                $expire = new DateTime($full_customer_info['referal_date']);
                $expire->modify('+48 hours');

                if ((new DateTime()) < $expire) {
                    $affiliate_info = $this->model_account_customer->getAffiliate($full_customer_info['referal']);
                    $data['affiliate_id'] = $full_customer_info['referal'];
                    $data['commission'] = $affiliate_info['commission'];
                }
            }
        }
        /*
                if (isset($this->request->cookie['tracking'])) {
                    $data['tracking'] = $this->request->cookie['tracking'];

                    if ($version < 300) {
                        $this->load->model('affiliate/affiliate');

                        $affiliate_info = $this->model_affiliate_affiliate->getAffiliateByCode($this->request->cookie['tracking']);
                    } else {
                        $this->load->model('account/customer');

                        $affiliate_info = $this->model_account_customer->getAffiliateByTracking($this->request->cookie['tracking']);
                    }

                    $subtotal = $this->cart->getSubTotal();

                    if ($affiliate_info) {
                        $data['affiliate_id'] = $version < 300 ? $affiliate_info['affiliate_id'] : $affiliate_info['customer_id'];
                        $data['commission'] = ($subtotal / 100) * $affiliate_info['commission'];
                    } else {
                        $data['affiliate_id'] = 0;
                        $data['commission'] = 0;
                    }

                    if ($version >= 200) {
                        $this->load->model('checkout/marketing');

                        $marketing_info = $this->model_checkout_marketing->getMarketingByCode($this->request->cookie['tracking']);

                        if ($marketing_info) {
                            $data['marketing_id'] = $marketing_info['marketing_id'];
                        } else {
                            $data['marketing_id'] = 0;
                        }
                    }
                } else {
                    $data['affiliate_id'] = 0;
                    $data['commission'] = 0;
                    $data['marketing_id'] = 0;
                    $data['tracking'] = '';
                }
        */
        $data['language_id']    = $this->config->get('config_language_id');

        if ($version < 220) {
            $data['currency_id']    = $this->currency->getId();
            $data['currency_code']  = $this->currency->getCode();
            $data['currency_value'] = $this->currency->getValue($this->currency->getCode());
        } else {
            $data['currency_id']    = $this->currency->getId($this->session->data['currency']);
            $data['currency_code']  = $this->session->data['currency'];
            $data['currency_value'] = $this->currency->getValue($this->session->data['currency']);
        }

        $data['ip'] = $this->request->server['REMOTE_ADDR'];

        if (!empty($this->request->server['HTTP_X_FORWARDED_FOR'])) {
            $data['forwarded_ip'] = $this->request->server['HTTP_X_FORWARDED_FOR'];
        } elseif(!empty($this->request->server['HTTP_CLIENT_IP'])) {
            $data['forwarded_ip'] = $this->request->server['HTTP_CLIENT_IP'];
        } else {
            $data['forwarded_ip'] = '';
        }

        if (isset($this->request->server['HTTP_USER_AGENT'])) {
            $data['user_agent'] = $this->request->server['HTTP_USER_AGENT'];
        } else {
            $data['user_agent'] = '';
        }

        if (isset($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
            $data['accept_language'] = $this->request->server['HTTP_ACCEPT_LANGUAGE'];
        } else {
            $data['accept_language'] = '';
        }

        $this->load->model('checkout/order');

        $order_id = 0;

        $customInfo = $this->simplecheckout->getCustomFields(array('customer', 'payment_address', 'payment', 'shipping_address', 'shipping'), 'order');

        $data = array_merge($customInfo, $data);

        if (!$data['shipping_address_1'] && !$data['shipping_address_2'] && $data['shipping_poshtomat_address_1'] && $data['shipping_code'] == 'novaposhta.poshtomat') {
            $data['shipping_address_1'] = $data['shipping_poshtomat_address_1'];
        }

        if (!$data['shipping_address_1'] && !$data['shipping_address_2'] && $data['shipping_street'] && $data['shipping_code'] == 'novaposhta.doors') {
            $data['shipping_address_1'] = $data['shipping_street'];
            if (isset($data['shipping_house']) && $data['shipping_house']) {
                $data['shipping_address_1'] .= ', б.'. $data['shipping_house'];
            }
            if (isset($data['shipping_flat']) && $data['shipping_flat']) {
                $data['shipping_address_1'] .= ', кв./оф.'. $data['shipping_flat'];
            }
        }
//HappayHour
        if ($this->config->get('config_hh_freedelivery_on')) {
            $today = date_create(date('Y-m-d H:i'));
            $start_hh_date = date_create($this->config->get('config_hh_freedelivery_start'));
            $end_hh_date = date_create($this->config->get('config_hh_freedelivery_end'));
            if ($today >= $start_hh_date && $today <= $end_hh_date) {
                $data['comment'] .= ". Щасливі години: Безкоштовна доставка";
            }
        }
//HappayHour

        if (isset($this->session->data['shipping_coupon']) && $this->session->data['shipping_coupon']) {
            $data['comment'] .= ". Безкоштовна доставка за промокодом";
        }

        if (!empty($this->session->data['double_cashback_coupon'])) {
            $data['comment'] .= ". Подвійний кешбек за промокодом " . $this->session->data['double_cashback_coupon'];
        }

        if ($version < 152) {
            $order_id = $this->model_checkout_order->create($data);

            // Gift Voucher
            if (isset($this->session->data['vouchers']) && is_array($this->session->data['vouchers'])) {
                $this->load->model('checkout/voucher');

                foreach ($this->session->data['vouchers'] as $voucher) {
                    $this->model_checkout_voucher->addVoucher($order_id, $voucher);
                }
            }
        } elseif ($version >= 152) {
            $order_id = $this->model_checkout_order->addOrder($data);
        }

        $this->session->data['order_id'] = $order_id;

        $this->simplecheckout->saveCustomFields(array('customer', 'payment_address', 'payment', 'shipping_address', 'shipping'), 'order', $order_id);

        $simple_cart_id = !empty($this->session->data['simple_cart_id']) ? $this->session->data['simple_cart_id'] : 0;

        if ($simple_cart_id) {
            $this->load->model('tool/simpleapi');
            $this->model_tool_simpleapi->deleteAbandonedCart($simple_cart_id);
        }

        return $order_id;
    }

    private function confirm() {
        $order_id = $this->session->data['order_id'];

        $this->load->model('checkout/order');

        $existing = $this->db->query("SELECT order_status_id FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");

        if ($existing->num_rows && (int)$existing->row['order_status_id'] > 0) {
            return;
        }

        if ($this->simplecheckout->getOpenCartVersion() < 200) {
            $this->model_checkout_order->confirm($order_id, $this->simplecheckout->getSettingValue('ignorePaymentOrderStatus'), '', false);
        } else {
            $this->model_checkout_order->addOrderHistory($order_id, $this->simplecheckout->getSettingValue('ignorePaymentOrderStatus'), '', false);
        }
    }

    /**
     * Confirm payments that only need addOrderHistory (no gateway redirect).
     * Returns true when order was confirmed (or already confirmed).
     */
    private function confirmSimplePaymentMethod($payment_code) {
        $payment_code = (string)$payment_code;

        $status_keys = array(
            'cod' => 'payment_cod_order_status_id',
            'bank_transfer' => 'payment_bank_transfer_order_status_id',
        );

        if (!isset($status_keys[$payment_code])) {
            return false;
        }

        $order_id = !empty($this->session->data['order_id']) ? (int)$this->session->data['order_id'] : 0;

        if ($order_id < 1) {
            return false;
        }

        $this->load->model('checkout/order');

        $existing = $this->db->query("SELECT order_status_id FROM `" . DB_PREFIX . "order` WHERE order_id = '" . (int)$order_id . "'");

        if ($existing->num_rows && (int)$existing->row['order_status_id'] > 0) {
            return true;
        }

        $order_status_id = (int)$this->config->get($status_keys[$payment_code]);

        if ($order_status_id < 1) {
            return false;
        }

        $this->model_checkout_order->addOrderHistory($order_id, $order_status_id);

        return true;
    }

    private function findRecentDuplicateOrderId() {
        $signature = $this->getCartProductSignature();

        if ($signature === '') {
            return 0;
        }

        if (!empty($this->session->data['order_id'])) {
            $session_id = (int)$this->session->data['order_id'];
            $session_order = $this->db->query("SELECT order_id, order_status_id FROM `" . DB_PREFIX . "order` WHERE order_id = '" . $session_id . "'");

            if ($session_order->num_rows && $this->isReusableOrderStatus($session_order->row['order_status_id']) && $this->getOrderProductSignature($session_id) === $signature) {
                return $session_id;
            }
        }

        $telephone = '';

        if (!empty($this->session->data['simple']['customer']['telephone'])) {
            $telephone = $this->session->data['simple']['customer']['telephone'];
        }

        $digits = preg_replace('/\D/', '', (string)$telephone);
        $last9 = strlen($digits) >= 9 ? substr($digits, -9) : '';

        $customer_id = (int)$this->customer->getId();

        if (!$customer_id && !empty($this->session->data['simple']['customer']['customer_id'])) {
            $customer_id = (int)$this->session->data['simple']['customer']['customer_id'];
        }

        if ($last9 === '' && $customer_id < 1) {
            return 0;
        }

        $where = array();

        if ($last9 !== '') {
            $where[] = "REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(o.telephone, '+', ''), ' ', ''), '-', ''), '(', ''), ')', '') LIKE '%" . $this->db->escape($last9) . "'";
        }

        if ($customer_id > 0) {
            $where[] = "o.customer_id = '" . $customer_id . "'";
        }

        $query = $this->db->query("SELECT o.order_id, o.order_status_id
            FROM `" . DB_PREFIX . "order` o
            WHERE o.date_added >= DATE_SUB(NOW(), INTERVAL 5 MINUTE)
              AND o.order_status_id > 0
              AND o.order_status_id <> 7
              AND (" . implode(' OR ', $where) . ")
            ORDER BY o.order_id DESC
            LIMIT 10");

        foreach ($query->rows as $row) {
            if (!$this->isReusableOrderStatus($row['order_status_id'])) {
                continue;
            }

            if ($this->getOrderProductSignature((int)$row['order_id']) === $signature) {
                return (int)$row['order_id'];
            }
        }

        return 0;
    }

    private function isReusableOrderStatus($order_status_id) {
        $order_status_id = (int)$order_status_id;

        return $order_status_id > 0 && $order_status_id !== 7;
    }

    private function getCartProductSignature() {
        $parts = array();

        foreach ($this->cart->getProducts() as $product) {
            $parts[] = (int)$product['product_id'] . 'x' . (int)$product['quantity'];
        }

        sort($parts);

        return implode('|', $parts);
    }

    private function getOrderProductSignature($order_id) {
        $query = $this->db->query("SELECT product_id, quantity FROM " . DB_PREFIX . "order_product WHERE order_id = '" . (int)$order_id . "'");
        $parts = array();

        foreach ($query->rows as $row) {
            $parts[] = (int)$row['product_id'] . 'x' . (int)$row['quantity'];
        }

        sort($parts); 

        return implode('|', $parts);
    }

    public function getTotalOrders() {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` o WHERE customer_id = '" . (int)$this->customer->getId() . "' AND o.order_status_id = '5' AND o.store_id = '" . (int)$this->config->get('config_store_id') . "'");
        return $query->row['total'];
    }
}
 