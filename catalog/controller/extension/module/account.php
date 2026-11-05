<?php
class ControllerExtensionModuleAccount extends Controller {
	public function index() {
		$this->load->language('extension/module/account');

		$data['logged'] = $this->customer->isLogged();

        $this->load->model('extension/module/battlepass');
        $data['active_season'] = $this->model_extension_module_battlepass->getActiveSeason();
        $data['fortune'] = $this->config->get('config_fortune');
		
		if ($this->customer->isLogged()) {
			$this->load->model('account/customer');

			$customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
			$data['name'] = $customer_info['firstname'] . ' ' . $customer_info['lastname'];
			
			$data['discount'] = $customer_info['discount'];
			
			$data['aname'] = mb_substr($data['name'], 0, 1);
			
			$this->getbonus($customer_info['telephone']);
		}
		$secure = !empty($this->request->server['HTTPS']) && $this->request->server['HTTPS'] !== 'off';
		$base = rtrim($secure ? $this->config->get('config_ssl') : $this->config->get('config_url'), '/');
		$path = parse_url($this->request->server['REQUEST_URI'], PHP_URL_PATH) ?: '';
		$data['mid'] = $base . $path;
		$data['register'] = $this->url->link('account/register', '', true);
		$data['login'] = $this->url->link('account/login', '', true);
		$data['logout'] = $this->url->link('account/logout', '', true);
		$data['forgotten'] = $this->url->link('account/forgotten', '', true);
		$data['account'] = $this->url->link('account/account', '', true);
		$data['edit'] = $this->url->link('account/edit', '', true);
		$data['password'] = $this->url->link('account/password', '', true);
		$data['address'] = $this->url->link('account/address', '', true);
		$data['wishlist'] = $this->url->link('account/wishlist', '', true);
		$data['order'] = $this->url->link('account/order', '', true);
		$data['download'] = $this->url->link('account/download', '', true);
		$data['reward'] = $this->url->link('account/reward', '', true);
		$data['return'] = $this->url->link('account/return', '', true);
		$data['transaction'] = $this->url->link('account/transaction', '', true);
		$data['newsletter'] = $this->url->link('account/newsletter', '', true);
		$data['recurring'] = $this->url->link('account/recurring', '', true);
		$data['promocodes'] = $this->url->link('account/promocodes', '', true);
        $data['sprite_version'] = $this->registry->get('sprite_version');
            $special_ips = [
                '62.65.59.212',
                '37.96.35.119',
                '90.191.10.150',
                '92.253.212.224',
                '91.196.55.196',
                '92.253.212.245',
				'178.136.108.250',
				'92.253.212.203',
				'62.221.61.190',
				'92.253.212.37',
				'92.253.212.145',
				'62.16.0.8',
				'178.136.107.23'
            ];
//		$data['referral'] = false;
		$data['is_special_ip'] = in_array($_SERVER['REMOTE_ADDR'], $special_ips);
//		if ($data['is_special_ip']) {
			$data['referral'] = $this->url->link('account/referral', '', true);
//		}
		
		$data['reward_total'] = (int)$this->customer->getRewardPoints();

		return $this->load->view('extension/module/account', $data);
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
