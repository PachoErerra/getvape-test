<?php

class ControllerExtensionModuleRegsms extends Controller
{

    private $host = 'http://apiagent.ru/password_generation/api.php';
    private $eol = "\n";

    public function index()
    {


        $this->load->language('extension/module/regsms');
        $this->load->language('extension/module/regsms_code');
        $this->load->language('extension/module/regsms_telegram');

        $data['module_regsms_text_button'] = $this->config->get('module_regsms_text_button');
        $data['module_regsms_offer'] = sprintf($this->language->get('text_regsms_offer'), $this->config->get('module_regsms_text_button'));


        $this->load->model('tool/image');

        $data['tek_region'] = "";
        $data['tek_mask'] = "";
        $data['tek_placeholder'] = "";
        $data['tek_thumb'] = "";

        $data['module_regsms_mask'] = $this->config->get('module_regsms_mask');
        foreach ($data['module_regsms_mask'] as $key => $mask) {

            if (!$mask['status']) {
                continue;
            }

            if (is_file(DIR_IMAGE . $mask['image'])) {
                $thumb = $this->model_tool_image->resize($mask['image'], 30, 30);
            } else {
                $thumb = $this->model_tool_image->resize('no_image.png', 30, 30);
            }
            $data['module_regsms_mask'][$key]['thumb'] = $thumb;

            if (!$data['tek_mask']) {
                $data['tek_region'] = $mask['region'];
                $data['tek_mask'] = $mask['mask'];
                $data['tek_placeholder'] = $mask['placeholder'];
                $data['tek_thumb'] = $thumb;
            }
        }

        if (isset($this->request->get['nocode']) && $this->request->get['nocode']) {
            $this->session->data['nocode'] = $this->request->get['nocode'];
        }

//        if ($_SERVER['REMOTE_ADDR'] == '5.58.45.18') {

        $data['messageId'] = $this->session->data['messageId'] ?? null;

        $data['chatId'] = $this->session->data['chatId'] ?? null;

        $data['telegram_bot_link'] = TELEGRAM_BOT_LINK;

        //           $this->response->setOutput($this->load->view('extension/module/regsms_telegram', $data));

        return $this->load->view('extension/module/regsms_telegram', $data);

//        } else {
//            $this->response->setOutput($this->load->view('extension/module/regsms', $data));
//        }


    }

    public function auth()
    {

        $json = array();

        if (isset($this->request->post['code']) && isset($this->request->post['telephone'])) {
//print_r($this->session->data['regsms']);
            if (isset($this->session->data['regsms'][$this->request->post['telephone']]['code']) && $this->session->data['regsms'][$this->request->post['telephone']]['code'] == $this->request->post['code']) {

                $this->load->model('extension/module/regsms');

                $customer_info = $this->model_extension_module_regsms->getCustomerByTelephone($this->request->post['telephone']);

                if (!$customer_info) {

                    $customer_data = array(
                        'firstname' => (isset($this->request->post['firstname']) && $this->request->post['firstname'] && $this->request->post['firstname'] != 'undefined') ? $this->request->post['firstname'] : '',
                        'lastname' => (isset($this->request->post['lastname']) && $this->request->post['lastname'] && $this->request->post['lastname'] != 'undefined') ? $this->request->post['lastname'] : '',
                        'fax' => '',
                        'avatar' => '',
                        'telephone' => $this->request->post['telephone']
                    );

                    $customer_id = $this->model_extension_module_regsms->addCustomer($customer_data);

                    $address_data = array(
                        'firstname' => (isset($this->request->post['firstname']) && $this->request->post['firstname'] && $this->request->post['firstname'] != 'undefined') ? $this->request->post['firstname'] : '',
                        'lastname' => (isset($this->request->post['lastname']) && $this->request->post['lastname'] && $this->request->post['lastname'] != 'undefined') ? $this->request->post['lastname'] : '',
                        'telephone' => $this->request->post['telephone'],
                        'zone_id' => $this->config->get('config_zone_id'),
                        'country_id' => $this->config->get('config_country_id'),
                        'default' => true
                    );

                    $this->model_extension_module_regsms->addAddress($customer_id, $address_data);

                }
                $wishlist = isset($_COOKIE['cookieWishlist']) ? json_decode($_COOKIE['cookieWishlist'], 1) : [];
                $wishlist = array_unique($wishlist);
                if ($wishlist) {
                    $this->load->model('account/wishlist');
                    $this->load->model('catalog/product');

                    foreach ($wishlist as $id) {
                        $product_info = $this->model_catalog_product->getProduct($id);
                        if ($product_info) {
                            $this->model_account_wishlist->addWishlist($id);
                        }
                    }

                    setcookie('cookieWishlist', '', time() - 3600 * 24 * 365, '/');
                }


                if ($this->customer->loginSms($this->request->post['telephone'])) {
                    $this->load->model('account/address');

                    if ($this->config->get('config_tax_customer') == 'payment') {
                        $this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
                    }

                    if ($this->config->get('config_tax_customer') == 'shipping') {
                        $this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
                    }

                    if (isset($this->session->data['redirect']) && $this->session->data['redirect'] && ($this->session->data['redirect'] == 'https://getvape.com.ua/index.php?route=checkout/simplecheckout' || $this->session->data['redirect'] == 'https://getvape.com.ua/index.php?route=checkout/simplecheckout ')) {
                        $json['redirect'] = $this->session->data['redirect'];
                    }

                    //	$json['redirect'] = $this->url->link('account/account', '', true);
                    $json['success'] = true;
                }

                if (isset($this->request->cookie['battle_pass_hidden'])) {
                    setcookie('battle_pass_hidden', '', time() - 3600, '/');
                }

                if (isset($this->request->cookie['fortune_hidden'])) {
                    setcookie('fortune_hidden', '', time() - 3600, '/');
                }

                $this->getbonus($this->request->post['telephone']);

            } else {
                $json['error'] = true;
            }
        } else {
            $json['error'] = true;
        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }



    public function offer()
    {

        $this->load->language('extension/module/regsms');
        $data['module_regsms_offer'] = htmlspecialchars_decode($this->config->get('module_regsms_offer'));

        $this->response->setOutput($this->load->view('extension/module/regsms_offer', $data));
    }

    public function sendcode2()
    {

        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->response->addHeader('HTTP/1.1 405 Method Not Allowed');
            exit();
        }

        // ----- Anti-DDOS: limit per IP -----
        $ip = $_SERVER['REMOTE_ADDR'];
        $limit_time = 3; // время блокировки в секундах

        if (!isset($this->session->data['ip_limiter'])) {
            $this->session->data['ip_limiter'] = [];
        }

        if (isset($this->session->data['ip_limiter'][$ip]) && time() - $this->session->data['ip_limiter'][$ip] < $limit_time) {
            http_response_code(429);
            echo json_encode(['error' => 'Too many requests']);
            return;
        }

        $this->session->data['ip_limiter'][$ip] = time();
// ----- END Anti-DDOS -----

        $json = array();
        $code = '';

        $this->load->language('extension/module/regsms');
        $this->load->language('extension/module/regsms_telegram');
        $this->load->model('extension/module/regsms');

//        $this->log->write("PHONE RAW = " . json_encode($this->request->post['telephone']));

        $telephone = $this->validateTelephone($this->request->post['telephone']);

        if (!$telephone) {
            $json['error']['telephone'] = $this->language->get('text_regsms_error_telephone');
        }

        $telephone_info = $this->model_extension_module_regsms->getTelephone($telephone);

        if (isset($this->session->data['regsms'][$telephone]['time']) && $this->session->data['regsms'][$telephone]['telephone'] == $telephone && ($this->session->data['regsms'][$telephone]['time'] + (int)$this->config->get('module_regsms_time_session')) >= time()) {
            $json['error']['session'] = sprintf($this->language->get('text_regsms_error_session'), ($this->session->data['regsms'][$telephone]['time'] + (int)$this->config->get('module_regsms_time_session') - time()));


            //			if (($this->checkActivityTelephone($telephone) > 2) || ($this->checkActivityIP() > 2)) {
            if (($this->checkActivityTelephone($telephone) > 2)) {
                $json['error']['session'] = sprintf($this->language->get('text_regsms_error_session'), ($telephone_info['time'] + 86400 - time()));
            }
        }


        if ($telephone_info && !$json) {
            if (($telephone_info['time'] + (int)$this->config->get('module_regsms_time_session')) >= time()) {
                $json['error']['session'] = sprintf($this->language->get('text_regsms_error_session'), ($telephone_info['time'] + (int)$this->config->get('module_regsms_time_session') - time()));
            }
        }

        if ($telephone_info && $telephone_info['time'] && !isset($this->session->data['nocode'])) {
            $diff = time() - $telephone_info['time'];

            if ($diff < 86400) {
                $display_telephone = trim($this->request->post['telephone'] ?? '') ?: $telephone;
                $json['break'] = sprintf($this->language->get('text_sms_code_already_sent_to_number'), $display_telephone);
                $json['break2'] = $this->language->get('text_sms_code_already_sent_hint');
                $this->session->data['regsms'][$telephone]['telephone'] = $telephone;
                $this->session->data['regsms'][$telephone]['code'] = $telephone_info['code'];
                $this->session->data['regsms'][$telephone]['time'] = $telephone_info['time'];
            }
        }
        unset($this->session->data['nocode']);
//			$this->log->write('Входов с телефоном - ' .$this->checkActivityTelephone($telephone));
//			$this->log->write('Входов с IP - ' .$this->checkActivityIP($telephone));		

//				if (($this->checkActivityTelephone($telephone) > 2) || ($this->checkActivityIP() > 2)) {
        if (($this->checkActivityTelephone($telephone) > 2)) {
            if ($telephone_info) {
                //	$json['error']['session'] = 'Ви вже використали 3 відправки SMS за годину. <br>Спробуйте увійти через Telegram.<br>'.sprintf($this->language->get('text_regsms_error_session'), ($telephone_info['time'] + 86400 - time()));
                $json['error']['session'] = $this->language->get('text_sms_limit_daily_telegram');
            }
        }
        $this->session->data['regsms'][$telephone]['telephone'] = $telephone;


        if (!$json) {

            $this->load->model('extension/module/regsms');
            $customer_info = $this->model_extension_module_regsms->getCustomerByTelephone($telephone);

            $this->addActivity($telephone, $customer_info);

            if (!$customer_info) {
                $pattern = $this->config->get('config_sms_reg');
            } else {
                $pattern = $this->config->get('config_sms_auth');
            }

            $code = rand(1111, 9999);
//            $code  = 4444;

            if ($code) {
                $time = time();
                $this->session->data['regsms'][$telephone]['code'] = $code;
                $this->session->data['regsms'][$telephone]['time'] = $time;
                $this->session->data['regsms'][$telephone]['telephone'] = $telephone;
                $this->model_extension_module_regsms->addTelephoneCode($telephone, $code, $time);

            }


            $url = 'http://api.smscentre.kiev.ua/http/submit_sm.php';
            $request['login'] = 'vapors';
            $request['passwd'] = 'viktor';
            $request['destaddr'] = $telephone;
            $request['msgtext'] = str_replace('[code]', $code, $pattern);
            $request['msgchrset'] = 'cyr';
//$this->log->write('Вход по СМС - ' .$telephone. ' - ' .$_SERVER['REMOTE_ADDR']);
            $result = $this->sendsms($url, $request);


        }
        $json['telephone'] = $telephone;

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));

    }

    public function addActivity($telephone, $data)
    {
        if (isset($data['customer_id'])) {
            $customer_id = $data['customer_id'];
        } else {
            $customer_id = 0;
        }
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_activity_login WHERE date_added < DATE_SUB(NOW(), INTERVAL 24 HOUR)");

        $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_activity_login` SET `customer_id` = '" . (int)$customer_id . "', `telephone` = '" . $this->db->escape($telephone) . "', `ip` = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "', `date_added` = NOW()");

    }

    public function checkActivityTelephone($telephone)
    {

        $query = $this->db->query("SELECT COUNT(telephone) AS total FROM `" . DB_PREFIX . "customer_activity_login` WHERE  `telephone` = '" . $this->db->escape($telephone) . "'");

        if (isset($query->row['total'])) {
            return $query->row['total'];
        } else {
            return 0;
        }
    }

    public function checkActivityIP()
    {
        $query = $this->db->query("SELECT COUNT(ip) AS total FROM `" . DB_PREFIX . "customer_activity_login` WHERE  `ip` = '" . $this->db->escape($this->request->server['REMOTE_ADDR']) . "'");

        if (isset($query->row['total'])) {
            return $query->row['total'];
        } else {
            return 0;
        }
    }

    public function sendsms($url, $request)
    {

        try {

            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($request));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            //execute curl
            $response = curl_exec($curl);

            //get execute result
            $curl_errno = curl_errno($curl);
            $curl_error = curl_error($curl);
            $aInfo = @curl_getinfo($curl);


            //close curl
            curl_close($curl);
            //analysis of the information received

            return json_decode($response, true);

        } catch (Exception $e) {
            return false;
        }

    }


    private function validateTelephoneNew($phone)
    {
        // Очищаем от мусора

        $digits = preg_replace('/\D/', '', $phone);

        // Приводим форматы:
        // 0XXXXXXXXX → +380XXXXXXXXX
        if (preg_match('/^0\d{9}$/', $digits)) {
            $digits = '380' . substr($digits, 1);
        }

        // 380XXXXXXXXX — норм
        if (preg_match('/^380\d{9}$/', $digits)) {
            $number = '+' . $digits;
        } else {
            return false; // НЕ UA номер
        }

        // Проверяем код оператора
        $code = substr($digits, 3, 3); // 067, 093, 050 и т.д.

        $operators = [
            '031', '032', '033', '034', '035', '036', '037', '041',
            '043', '044', '045', '046', '047', '048', '049', '050',
            '051', '052', '053', '054', '055', '056', '057', '061',
            '062', '063', '064', '066', '067', '068', '073', '091',
            '092', '093', '094', '095', '096', '097', '098', '099',
            '077', '075'
        ];

        if (!in_array($code, $operators)) {
            return false;
        }

        return $number;
    }

    private function validateTelephone($phone)
    {

        $phone = str_replace(")", "", $phone);
        $phone = str_replace("(", "", $phone);
        $phone = str_replace("-", "", $phone);
        $phone = str_replace(" ", "", $phone);
        $phone = str_replace("+", "", $phone);

        $first_3 = substr($phone, 0, 3);
        $first_1 = substr($phone, 0, 1);
        $error = 0;

        if (($first_3 == '380') || ($first_3 == '375')) {
            $last = substr($phone, 3);
            if (strlen($last) == 9) {
                $phone = "+" . $phone;
            } else {
                $error = 1;
            }

        } elseif ($first_1 == '7') {
            $last = substr($phone, 1);
            if (strlen($last) == 10) {
                $phone = "+" . $phone;
            } else {
                $error = 1;
            }

        } elseif ($first_1 == '8') {
            $last = substr($phone, 1);
            if (strlen($last) == 10) {
                $phone = "+7" . $last;
            } else {
                $error = 1;
            }
        } else {
            $error = 1;
        }

        if (!$error) {
            return $phone;
        } else {
            return false;
        }

    }

    public function getRequest($param)
    {

        $param_xml = $param;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml', 'charset=utf8'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $param_xml);
        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_URL, $this->host);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $res = curl_exec($ch);

        $xml = simplexml_load_string($res);
        curl_close($ch);

        return $xml;

    }

    public function getbonus2()
    {

        if (isset($this->request->get['telephone']) && $this->request->get['telephone']) {

            $telephone = $this->request->get['telephone'];

//            $url = 'http://5.9.8.211/site_retail/hs/api/0/sitebuyers/?auth_token=3698024';
            $url = 'http://vaporstlgrm.biz.ua/site_retail/hs/api/0/sitebuyers/?auth_token=3698024';
            $telephone = str_replace(['(', ')', '-', ' '], '', $telephone);

            $data = array(
                "telephone" => $telephone,
            );
            $request = json_encode($data);
            //$this->log->write($request);
            try {
                $curl = curl_init($url);
                curl_setopt($curl, CURLOPT_POST, true);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_HEADER, 0);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Accept-Encoding: UTF-8', 'Content-Type: application/json; charset=UTF-8'));
                curl_setopt($curl, CURLOPT_TIMEOUT, 30);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

                $response = curl_exec($curl);
                $curl_errno = curl_errno($curl);
                $curl_error = curl_error($curl);
                $aInfo = @curl_getinfo($curl);

                //close curl
                curl_close($curl);
                //analysis of the information received

                if ($curl_errno != 0) {
                    $this->log->write('CURL failed ' . $curl_error . '(' . $curl_errno . ')');
                    return 'error';
                }
                if ($aInfo["http_code"] != '200') {
                    $this->log->write('HTTP failed ' . $aInfo["http_code"] . '(' . $response . ')');

                    echo $response;
                    print_r($aInfo);

                    return 'error';
                }


                $result = json_decode($response, true);


                print_r($result);

            } catch (Exception $e) {
                return false;
            }

        }
    }


    public function getbonus($telephone)
    {

//		$url = 'http://5.9.8.211:8089/VPR_UNF/hs/api/0/sitebuyers/?auth_token=3698024';
//        $url = 'http://5.9.8.211/site_retail/hs/api/0/sitebuyers/?auth_token=3698024';
        $url = 'http://vaporstlgrm.biz.ua/site_retail/hs/api/0/sitebuyers/?auth_token=3698024';


        $telephone = str_replace(['(', ')', '-', ' '], '', $telephone);

        $data = array(
            "telephone" => $telephone,
        );
        $request = json_encode($data);
//$this->log->write($request);		
        try {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Accept-Encoding: UTF-8', 'Content-Type: application/json; charset=UTF-8'));
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($curl);
            $curl_errno = curl_errno($curl);
            $curl_error = curl_error($curl);
            $aInfo = @curl_getinfo($curl);

            //close curl
            curl_close($curl);
            //analysis of the information received

            if ($curl_errno != 0) {
                $this->log->write('CURL failed ' . $curl_error . '(' . $curl_errno . ')');
                return 'error';
            }
            if ($aInfo["http_code"] != '200') {
                $this->log->write('HTTP failed ' . $aInfo["http_code"] . '(' . $response . ')');
                return 'error';
            }


            $result = json_decode($response, true);

            if ($result['success'] && $result['success'] = 'true' && isset($result['balance'])) {
                $customer_id = $this->customer->getId();
                $this->addReward($customer_id, $result['balance'], 'обновление из 1с');
            }

            if ($result['success'] && $result['success'] = 'true' && isset($result['discount'])) {
                $customer_id = $this->customer->getId();
                $this->setdiscount($customer_id, $result['discount']);
            }


        } catch (Exception $e) {
            return false;
        }
    }

    public function addReward($customer_id, $points = '', $description = '', $order_id = 0)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_reward SET customer_id = '" . (int)$customer_id . "', order_id = '" . (int)$order_id . "', points = '" . (int)$points . "', description = '" . $this->db->escape($description) . "', date_added = NOW()");
    }

    public function setdiscount($customer_id, $discount = '')
    {
        $this->db->query("UPDATE " . DB_PREFIX . "customer SET discount = '" . (int)$discount . "' WHERE customer_id = '" . (int)$customer_id . "'");
    }
}

