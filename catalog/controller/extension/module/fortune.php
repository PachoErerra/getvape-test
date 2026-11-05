<?php

class ControllerExtensionModuleFortune extends Controller
{
	
	private $OneSock_id = 19302; //id товару ОДНА шкарпетка
	private $TwoSocks_id = 19301; //id товару Vapors Socks Шкарпетки 36-40
	private $bracelet_black = 19299; //id товару Браслет Чорний Vapors
	private $bracelet_pink = 19300; //id товару Браслет Рожевий Vapors
	private $cosmetic = 19171; //id товару Oxva SUMMER Косметичка
	private $bandana = 19170; //id товару Oxva SUMMER Бандана
	private $keychain = 19172; //id товару Oxva Key Chain (Ключний Ланцюг)
    private $pen = 20003; //id товару Брендовані ручки Vapors
    private $lanyard = 20004; //id Ланьярд Punch

    public function index()
    {

//		$this->response->setOutput($this->load->view('extension/module/fortune', $data));

    }

    public function view()
    {
        $route = isset($this->request->get['route']) ? (string)$this->request->get['route'] : '';
        if (in_array($route, ['product/product', 'checkout/simplecheckout'], true)) {
            return '';
        }

		$this->load->language('extension/module/fortune');
        if ($this->config->get('config_fortune')) {
            if ($this->customer->isLogged()) {
                $data['customer_isLogged'] = true;
				$this->load->model('extension/module/fortune');
				if (!isset($this->session->data['customer_purchase'])) {
					$order_total = $this->model_extension_module_fortune->getTotalOrders();
					$this->session->data['customer_purchase'] = $order_total;
				} else {
					$order_total = $this->session->data['customer_purchase'];
				}
			//	$order_total = 1; //раскоментируй если у тебя нет заказов со статусом 5 - завершен. чтоб стало второе колесо доступно
				if ($order_total) {
					$data['type'] = 'regular';
					$data['customer_twisted'] = $this->model_extension_module_fortune->CheckWinType('Wheel');
				} else {
					$data['type'] = 'newCustomer';
					$data['customer_twisted'] = $this->model_extension_module_fortune->CheckWinType('OneSock');
				}
            } else {
                $data['customer_isLogged'] = false;
				$data['type'] = 'newCustomer'; // или 'newCustomer', 'regular' — что нужно
            }

            $special_ips = [
                '62.65.59.212',
                '37.96.35.119',
                '90.191.10.150',
                '92.253.212.224',
                '91.196.55.196',
                '92.253.212.245',
				'178.136.108.250',
				'62.221.61.190',
				'92.253.212.37',
				'92.253.212.145',
				'178.136.111.245'
            ];
            $data['is_special_ip'] = in_array($_SERVER['REMOTE_ADDR'], $special_ips);

			$data['fortune_style'] = $this->config->get('config_style_fortune');
            $data['current_route'] = $_GET['route'] ?? '';
            $data['fortune_hidden'] = isset($this->request->cookie['fortune_hidden']);

			$data['nts_script_version'] = time();
//			if (isset($data['is_special_ip']) && $data['is_special_ip']) {
				return $this->load->view('extension/module/fortune', $data);
//			}
        }
    }

    public function checkwin()
    {
        $json = array();
        if ($this->customer->isLogged()) {
            $this->load->model('extension/module/fortune');
            $customer_id = $this->customer->getId();
            $win = $this->model_extension_module_fortune->checkwin($customer_id);
            if ($win) {
                $json['error'] = 'Вы уже играли, приходите завтра';
            } else {
                $json['success'] = 'Можно крутить';
            }
        } else {
            $json['error'] = 'Пользователь не залогинен';
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function cronclearwinners()
    {
        //https://dev.getvape.com.ua/index.php?route=extension/module/fortune/cronclearwinners&http_pass=23jgdfliuyt23pofger34h65jt8Fk5jFht84GJt
        $http_pass = '23jgdfliuyt23pofger34h65jt8Fk5jFht84GJt';
        if ($http_pass != $_GET['http_pass']) die('Неверный ключ доступа!');
        $this->load->model('extension/module/fortune');
        $this->model_extension_module_fortune->cronclearwinners();
    }

    public function checkwinserver()
    {
        $json = array();
        if ($this->customer->isLogged()) {
            $this->load->model('extension/module/fortune');
            $customer_id = $this->customer->getId();
            $win = $this->model_extension_module_fortune->checkwin($customer_id);
            if ($win) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function checkwinserversock()
    {
        $json = array();
        if ($this->customer->isLogged()) {
            $this->load->model('extension/module/fortune');
			$data_win = array();
            $data_win['customer_id'] = $this->customer->getId();
			$data_win['name'] = 'Пара шкарпеток';
            $win = $this->model_extension_module_fortune->checkwinsock($data_win['customer_id'], $data_win['name']);
            if ($win) {
                return $win;
            } else {
				return false;
			}
        } else {
            return false;
        }
    }

    public function addsock()
    {
        $json = array();
        if ($this->customer->isLogged()) {
            $this->load->model('extension/module/fortune');
			$data_win = array();
            $data_win['customer_id'] = $this->customer->getId();
			$data_win['name'] = 'Пара шкарпеток';
            $win = $this->model_extension_module_fortune->checkwinsock($data_win['customer_id'], $data_win['name']);
            if (!$win || (!$win['used'] && strtotime($win['date_valid']) < time())) {
                $data_win['telephone'] = $this->customer->getTelephone();
                $data_win['type'] = 'M';
                $data_win['total'] = 500;
                $data_win['logged'] = 1;
                $data_win['shipping'] = 0;
                $data_win['uses_total'] = 1;
                $data_win['uses_customer'] = 1;
                $data_win['status'] = 1;
                $data_win['coupon_product'] = array(0 => $this->TwoSocks_id); //id товара Пара шкарпеток за 1 грн
				$data_win['conditions'] = 'Мінімальна сума замовлення від 500 грн';
                $data_win['discount'] = 0;
                $chars = 'abdefhiknrstyz1234567890';
                $numChars = strlen($chars);
                $string = '';
                for ($k = 0; $k < 7; $k++) {
                    $string .= substr($chars, rand(1, $numChars) - 1, 1);
                }
				$data_win['code'] = 'TS0-' . $string;
					
                $this->model_extension_module_fortune->addWin($data_win,1);
                $coupon_id = $this->model_extension_module_fortune->addCoupon($data_win,1);
                if ($coupon_id) {
					$this->session->data['coupon'] = $data_win['code'];
					$this->session->data['TwoSocks_id'] = $this->TwoSocks_id;
				//	$this->cart->add($this->TwoSocks_id);//id товара Пара шкарпеток за 1 грн
                    $this->response->redirect($this->url->link('account/promocodes', '', true));
                }
            }
        } else {
            return false;
        }
    }
	
    public function win()
    {
        $json = array();
        if (isset($this->request->post['win']) && $this->request->post['win']) {
            if ($this->customer->isLogged()) {
				unset($this->session->data['TwoSocks_id']);
				$this->load->model('extension/module/fortune');
                $data_win = array();				
				$prize = $this->request->post['win'];
				$data_win['customer_id'] = $this->customer->getId();
                $data_win['telephone'] = $this->customer->getTelephone();

                $coupons_array = array('5p', '10p', '15p', '20p', '25p', 'free', '1_2', '1_1_3', 'freeDelivery','bracelet-black','bracelet-pink','cosmetic','bandana','keychain','pen','lanyard');
                $chars = 'abdefhiknrstyz1234567890';
                $numChars = strlen($chars);
                $string = '';
                for ($k = 0; $k < 7; $k++) {
                    $string .= substr($chars, rand(1, $numChars) - 1, 1);
                }				
				
				
				if ($prize == 'OneSock') {
					if (!$this->model_extension_module_fortune->CheckWinType($prize)) {
                        $data_win['type'] = 'M';
						$data_win['prize_type'] = 'OneSock';
                        $data_win['total'] = 0;
                        $data_win['logged'] = 1;
                        $data_win['shipping'] = 0;
                        $data_win['uses_total'] = 1;
                        $data_win['uses_customer'] = 1;
                        $data_win['status'] = 1;	
                        $data_win['name'] = 'ОДНА шкарпетка';
                        $data_win['code'] = 'OS-' . $string;
                        $data_win['discount'] = 0;
						$data_win['coupon_product'] = array(0 => $this->OneSock_id);
						$data_win['conditions'] = 'Друга шкарпетка буде з другим замовленням';
                        $this->model_extension_module_fortune->addWin($data_win,1);
                        $coupon_id = $this->model_extension_module_fortune->addCoupon($data_win,1);
                        if ($coupon_id) {
                            $json['success'] = $data_win['code'];
                            $json['promo'] = $data_win['code'];
                            $this->session->data['coupon'] = $data_win['code'];
						//	$this->cart->add($this->OneSock_id);
                        }
					} else {
						$json['error'] = 'Ви вже виграли приз, приходьте завтра!';
					}
				} else {
					if (!$this->model_extension_module_fortune->CheckWinType('Wheel')) {
						$data_win['prize_type'] = 'Wheel';
						if (in_array($prize, $coupons_array)) {

							
							$data_win['type'] = 'P';
							$data_win['total'] = 0;
							$data_win['logged'] = 1;
							$data_win['shipping'] = 0;
							$data_win['uses_total'] = 1;
							$data_win['uses_customer'] = 1;
							$data_win['status'] = 1;
							$data_win['discount'] = 0;

							if ($prize == 'bracelet-black') {
								$data_win['type'] = 'M';
								$data_win['name'] = 'Чорний браслет';
								$data_win['code'] = 'BB-' . $string;
								$data_win['coupon_product'] = array(0 => $this->bracelet_black);
							}

							if ($prize == 'bracelet-pink') {
								$data_win['type'] = 'M';
								$data_win['name'] = 'Рожевий браслет';
								$data_win['code'] = 'BP-' . $string;
								$data_win['coupon_product'] = array(0 => $this->bracelet_pink);
							}
							
							if ($prize == 'cosmetic') {
								$data_win['type'] = 'M';
								$data_win['name'] = 'Косметичка';
								$data_win['code'] = 'C-' . $string;
								$data_win['coupon_product'] = array(0 => $this->cosmetic);
							}

							if ($prize == 'bandana') {
								$data_win['type'] = 'M';
								$data_win['name'] = 'Бандана';
								$data_win['code'] = 'B-' . $string;
								$data_win['coupon_product'] = array(0 => $this->bandana);
							}

							if ($prize == 'keychain') {
								$data_win['type'] = 'M';
								$data_win['name'] = 'Брелок';
								$data_win['code'] = 'K-' . $string;
								$data_win['coupon_product'] = array(0 => $this->keychain);
							}

                            if ($prize == 'pen') {
                                $data_win['type'] = 'M'; //
                                $data_win['name'] = 'Ручка Vapors';
                                $data_win['code'] = 'PV-' . $string;
                                $data_win['coupon_product'] = array(0 => $this->pen);
                            }

                            if ($prize == 'lanyard') {
                                $data_win['type'] = 'M'; //
                                $data_win['name'] = 'Ланьярд Punch';
                                $data_win['code'] = 'LP-' . $string;
                                $data_win['coupon_product'] = array(0 => $this->lanyard);
                            }
																										
							if ($prize == '10p') {
								$data_win['name'] = 'Знижка 10%';
								$data_win['code'] = 'Fortune10-' . $string;
								$data_win['discount'] = 10;
							}
					
							if ($prize == 'freeDelivery') {
								$data_win['name'] = 'Безкоштовна доставка';
								$data_win['code'] = 'FortuneFD-' . $string;
								$data_win['discount'] = 0;
								$data_win['shipping'] = 1;
							}
							
							if ($prize == '15p') {
								$data_win['name'] = 'Знижка 15%';
								$data_win['code'] = 'Fortune15-' . $string;
								$data_win['discount'] = 15;
							}
							if ($prize == '20p') {
								$data_win['name'] = 'Знижка 20%';
								$data_win['code'] = 'Fortune20-' . $string;
								$data_win['discount'] = 20;
							}
							if ($prize == 'free') {
								$data_win['coupon_category'] = array(0 => 1271);
								$data_win['name'] = 'Безкоштовний картридж';
								$data_win['code'] = 'FortuneFree-' . $string;
								$data_win['discount'] = 100;
							}
							if ($prize == '1_2') {
								$data_win['coupon_category'] = array(0 => 2810, 1 => 3013);
								$data_win['name'] = '1=2 на рідини';
								$data_win['code'] = 'Fortune1=2-' . $string;
								$data_win['discount'] = 100;
							}
							if ($prize == '1_1_3') {
								$data_win['coupon_category'] = array(0 => 2810, 1 => 3013);
								$data_win['name'] = '1plus1=3 на рідини';
								$data_win['code'] = 'Fortune1plus1=3-' . $string;
								$data_win['discount'] = 100;
							}
							$this->model_extension_module_fortune->addWin($data_win,3);
							$coupon_id = $this->model_extension_module_fortune->addCoupon($data_win,3);
							if ($coupon_id) {
								$json['success'] = $data_win['code'];
								$json['promo'] = $data_win['code'];
								$this->session->data['coupon'] = $data_win['code'];
							}
						} else if ($prize == '40b') {
							$sum = 40;

							$data_win['name'] = 'Fortune ' . $sum . ' bonus';
							$data_win['code'] = '';
							$data_win['conditions'] = 'Вже додано до вашого бонусного рахунку';
							$telephone = $this->customer->getTelephone();
							$telephone = str_replace(['(', ')', '-', ' '], '', $telephone);

							$data = array(
								"telephone" => $telephone,
							);

							$url = 'http://vaporstlgrm.biz.ua/site_retail/hs/api/0/addbonus/?auth_token=3698024&sum=' . $sum . '&phone=' . $telephone;
							$request = json_encode($data);

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

								if ($response == 'success') {
								//	$this->log->write('Бонусы  БП зачислены ' . $telephone . ' бонусов ' . (int)$sum . '. ответ сервера: ' . $response);
									$this->getbonus($telephone);
									$this->model_extension_module_fortune->addWin($data_win);
									$json['success'] = 'Вам нараховано ' . $sum . ' бонусів';
									$json['bonus'] = $sum;
								} else {
									$this->log->write('Бонусы фортуны не зачислены ' . $telephone . ' бонусов ' . (int)$sum . '. ответ сервера: ' . $response);
									$json['error'] = 'Щось пішло не так';
								}

							} catch (Exception $e) {
								return false;
							}
						} else if ($prize == 'double') {
							$data_win['name'] = 'Подвійний кешбек';
							$data_win['code'] = 'FortuneDouble-' . $string;
							$data_win['type'] = 'D';
							$data_win['total'] = 0;
							$data_win['logged'] = 1;
							$data_win['shipping'] = 0;
							$data_win['uses_total'] = 1;
							$data_win['uses_customer'] = 1;
							$data_win['status'] = 1;
							$data_win['discount'] = 0;
							$data_win['conditions'] = 'Подвійний кешбек. Бонуси нараховуються о 00:00 після оплати замовлення';
							$this->model_extension_module_fortune->addWin($data_win, 3);
							$coupon_id = $this->model_extension_module_fortune->addCoupon($data_win, 3);
							if ($coupon_id) {
								$json['success'] = $data_win['code'];
								$json['promo'] = $data_win['code'];
								$json['double'] = 'double';
								$this->session->data['coupon'] = $data_win['code'];
							}
						} else if ($prize == 'socks') {

							
							$data_win['name'] = 'socks';
							$data_win['code'] = '';
							$this->model_extension_module_fortune->addWin($data_win,3);
							$json['success'] = 'socks';
							$json['socks'] = 'socks';
						} else if ($prize == 'friend') {

							
							$data_win['name'] = 'Приведи друга';
							$data_win['code'] = 'Friend-' . $string;
							$this->model_extension_module_fortune->addWin($data_win,3);
							$json['success'] = 'friend';
							$json['socks'] = 'friend';
							$json['promo'] = 'https://getvape.com.ua?fortune=' . $data_win['code'];
						}
						//проверяем переход по ссылке
						if (isset($this->session->data['fortune']) && $this->session->data['fortune']) {
							//проверяем активность ссылки и реффера
							$ref_customer_id = $this->model_extension_module_fortune->checkFriend($this->session->data['fortune']);
							if ($ref_customer_id && ($ref_customer_id != $this->customer->getId())) {
								$this->load->model('account/customer');
								$customer_win = $this->model_account_customer->getCustomer($ref_customer_id);

								//формируем промокод
								$string = '';
								for ($k = 0; $k < 7; $k++) {
									$string .= substr($chars, rand(1, $numChars) - 1, 1);
								}

								
								$data_win['type'] = 'P';
								$data_win['total'] = 0;
								$data_win['logged'] = 1;
								$data_win['shipping'] = 0;
								$data_win['uses_total'] = 1;
								$data_win['uses_customer'] = 1;
								$data_win['status'] = 1;
								$data_win['name'] = 'Link 10%';
								$data_win['code'] = 'Link10-' . $string;
								$data_win['discount'] = 10;
								$this->model_extension_module_fortune->addWin($data_win, 1);
								$coupon_id = $this->model_extension_module_fortune->addCoupon($data_win, 1);

								//регистрируем переход по ссылке
								$this->model_extension_module_fortune->usefriendlink($this->session->data['fortune']);

								//отправляем смс
								$url = 'http://api.smscentre.kiev.ua/http/submit_sm.php';
								$request['login'] = 'vapors';
								$request['passwd'] = 'viktor';
								$request['destaddr'] = $customer_win['telephone'];
								//длинное смс не проходит, хотя в статистике оно есть https://prnt.sc/BW08n2LitEmp
								//$request['msgtext'] = 'Завдяки Вашому другу '.$this->customer->getTelephone().' ви виграли знижку %5. Промокод - '.$data_win['code'];
								$request['msgtext'] = 'Знижка 10%! Промокод - ' . $data_win['code'];
								$request['msgchrset'] = 'cyr';
								$result = $this->sendsms($url, $request);
								//на фронт оповещение, хз надо ли
								$json['refferer'] = 'Ваш друг ' . $customer_win['telephone'] . ' виграв знижку 10%!';
							}
						}
					} else {
						$json['error'] = 'Ви вже виграли сьогодні, приходьте завтра!';
					}
				}

            } else {
                $json['error'] = 'Користувач не залогінений';
            }

        } else {
            $json['error'] = 'Не отримано даних про виграш';
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function getbonus($telephone)
    {

        $url = 'http://vaporstlgrm.biz.ua/site_retail/hs/api/0/sitebuyers/?auth_token=3698024';


        $telephone = str_replace(['(', ')', '-', ' '], '', $telephone);

        $data = array(
            "telephone" => $telephone,
        );
        $request = json_encode($data);

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

        } catch (Exception $e) {
            return false;
        }
    }

    public function addReward($customer_id, $points = '', $description = '', $order_id = 0)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_reward SET customer_id = '" . (int)$customer_id . "', order_id = '" . (int)$order_id . "', points = '" . (int)$points . "', description = '" . $this->db->escape($description) . "', date_added = NOW()");
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
}
