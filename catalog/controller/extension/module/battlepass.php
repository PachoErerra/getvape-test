<?php
class ControllerExtensionModuleBattlepass extends Controller {

    public function index() {
        if (is_crawler_bot()) {
            return '';
        }

        $route = isset($this->request->get['route']) ? (string)$this->request->get['route'] : '';
        if (in_array($route, ['product/product', 'checkout/simplecheckout'], true)) {
            return '';
        }

        $special_ips = [
            '213.35.183.17',
			'178.136.107.21',
            '162.120.188.179',
            '92.253.212.214',
        ];
//		$data['is_special_ip'] = in_array($_SERVER['REMOTE_ADDR'], $special_ips);
//		if (!$data['is_special_ip']) return;
		
        $this->load->language('extension/module/battlepass');
        $this->load->model('extension/module/battlepass');
        $this->load->model('tool/image');

        $data['customer_isLogged'] = $this->customer->isLogged();
        $data['battle_pass_hidden'] = isset($this->request->cookie['battle_pass_hidden']);

		$data['bp_show'] = !isset($this->request->cookie['bp_show']);
		$expire = time() + (24 * 60 * 60); 
		setcookie("bp_show", 1, $expire, "/");
        
        $active_season = $this->model_extension_module_battlepass->getActiveSeason();
        if (!$active_season) {
            return ''; 
        }
		
		$data['rules_link'] = $this->url->link('information/information', 'information_id=13');

        $season_id = $active_season['season_id'];
        $customer_id = $this->customer->isLogged() ? $this->customer->getId() : 0;
        
        $data['progression_type'] = isset($active_season['progression_type']) ? $active_season['progression_type'] : 'orders';
        $data['total_ultra'] = $this->model_extension_module_battlepass->getTotalUltra($customer_id);
        $data['current_level'] = 0;
        $data['total_amount'] = 0.00;
		if ($this->customer->isLogged()) {
			$data['has_unclaimed'] = false;
		} else {
			$data['has_unclaimed'] = true;
		}
        $data['next_level_amount'] = 0;
        $data['percent_to_next'] = 0;
        $data['waiting_message'] = '';
		$data['season_end'] = $active_season['date_end'];
        
        $claimed = array();
        $is_waiting_order = false;
		
		$data['can_play'] = 1;
		$data['prize'] = array('type' => 'points', 'amount' => 1);
		$data['text_prize'] = '';
			
        // 1. ОТРИМУЄМО ДАНІ КЛІЄНТА
        if ($this->customer->isLogged()) {
			
			// Проверяем по базе, была ли валидная выдача сегодня
			$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_lottery_history` 
									   WHERE customer_id = '" . (int)$customer_id . "' 
									   AND date_added >= CURDATE()
									   AND prize_type IN ('points', 'ultra')
									   AND amount > 0
									   LIMIT 1");
			
			$can_play = ($query->num_rows == 0);
			$data['can_play'] = $can_play;

			if ($can_play) {
				if (!$this->isValidLotteryPrize(isset($this->session->data['lottery_prize']) ? $this->session->data['lottery_prize'] : null)) {
					$this->session->data['lottery_prize'] = $this->generatePrize();
				}

				$data['prize'] = $this->session->data['lottery_prize'];
			} else {
				// Рассчитываем время до полуночи
				$tomorrow = strtotime('tomorrow');
				$data['tomorrow'] = $tomorrow;
				$time_left = $tomorrow - time();
				$data['time_left_text'] = sprintf("%02d ч %02d мин", floor($time_left / 3600), floor(($time_left % 3600) / 60));
				
				// Информация о сегодняшнем призе для статистики (опционально)
				$data['today_prize'] = $query->row;

				if (!empty($query->row['prize_type']) && (float)$query->row['amount'] > 0) {
					$data['prize'] = array(
						'type' => $query->row['prize_type'],
						'amount' => (float)$query->row['amount']
					);
				}

				unset($this->session->data['lottery_prize']);
			}

			if ($data['prize']['type'] == 'ultra') {
				$titles = array_map('trim', explode(',', $this->language->get('text_payments_ultra')));
			} else {
				$titles = array_map('trim', explode(',', $this->language->get('text_payments')));
			}
			$data['text_prize'] = $this->declOfNum($data['prize']['amount'], $titles);

			if (isset($this->session->data['success'])) {
				$data['success'] = $this->session->data['success'];
				unset($this->session->data['success']);
			}
			// конец лотереи
		
            $progress = $this->model_extension_module_battlepass->getCustomerProgress($customer_id, $season_id);
      
            if ($progress && is_array($progress)) {
                $data['current_level'] = (int)$progress['current_level'];
                $data['total_amount'] = (isset($progress['total_amount'])&&$progress['total_amount'])?ceil((float)$progress['total_amount']):0;
                $claimed = json_decode($progress['claimed_levels'], true) ?: array();
                
                if ($data['progression_type'] == 'orders') {
                    $unlock_time = (!empty($progress['next_unlock_date']) && $progress['next_unlock_date'] != '0000-00-00 00:00:00') ? strtotime($progress['next_unlock_date']) : 0;
                    $last_claimed = !empty($claimed) ? max($claimed) : 0;
                    
                    if ($unlock_time > time()) {
                        $data['waiting_message'] = 'Наступна нагорода буде доступна: ' . date('d.m.Y H:i', $unlock_time);
                        $is_waiting_order = true;
                    } elseif ($last_claimed > $data['current_level']) {
                        $data['waiting_message'] = 'Очікуємо підтвердження замовлення...';
                        $is_waiting_order = true;
                    }
                }
            }
        } else {
			$data['current_level'] = 0;
			$data['claimed_levels'] = array();

			if (!$this->isValidLotteryPrize(isset($this->session->data['lottery_prize']) ? $this->session->data['lottery_prize'] : null)) {
				$this->session->data['lottery_prize'] = $this->generatePrize();
			}

			$data['prize'] = $this->session->data['lottery_prize'];

			if ($data['prize']['type'] == 'ultra') {
				$titles = array_map('trim', explode(',', $this->language->get('text_payments_ultra')));
			} else {
				$titles = array_map('trim', explode(',', $this->language->get('text_payments')));
			}
			$data['text_prize'] = $this->declOfNum($data['prize']['amount'], $titles);
		}

        // 2. ФОРМУЄМО КАРТКИ НАГОРОД З НАКОПИЧУВАЛЬНОЮ СУМОЮ
        $db_rewards = $this->model_extension_module_battlepass->getSeasonRewards($season_id);
        
        // НОВА ЛОГІКА: Рахуємо загальну суму для кожного рівня
        $cumulative_amount = 0;
        $calculated_rewards = array();
        foreach ($db_rewards as $reward) {
            $cumulative_amount += (float)$reward['required_amount'];
            $reward['cumulative_amount'] = $cumulative_amount;
            $calculated_rewards[] = $reward;
        }

        $data['rewards'] = array();

        foreach ($calculated_rewards as $key=>$reward) {
            $status = 'locked';
            // Використовуємо саме накопичену суму для перевірок та виводу у шаблон
            $required_amount = $reward['cumulative_amount'];

            if ($this->customer->isLogged()) {
                if ($data['progression_type'] == 'amount') {
                    if (in_array($reward['level'], $claimed)) {
                        $status = 'claimed';
                    } elseif ($data['total_amount'] >= $required_amount) {
                        $status = 'available';
                        $data['has_unclaimed'] = true;
                    } else {
                        $status = 'locked';
                    }
                } else {
                    if (in_array($reward['level'], $claimed)) {
                        $status = ($reward['level'] <= $data['current_level']) ? 'claimed' : 'waiting';
                    } elseif ($reward['level'] <= $data['current_level']) {
                        $status = 'available';
                        $data['has_unclaimed'] = true;
                    } elseif ($reward['level'] == $data['current_level'] + 1) {
                        if ($is_waiting_order) {
                            $status = 'waiting';
                        } else {
                            $status = 'available';
                            $data['has_unclaimed'] = true;
                        }
                    } else {
                        $status = 'locked';
                    }
                }
            } else {
				if ($key == 0) {
				$status = 'available';
				}
			}

            $image = ($reward['image'] && is_file(DIR_IMAGE . $reward['image'])) ? $this->model_tool_image->resize($reward['image'], 200, 200) : $this->model_tool_image->resize('no_image.png', 200, 200);

            $data['rewards'][] = array(
                'level'           => $reward['level'],
                'required_amount' => $required_amount, // Передаємо у шаблон фінальну мету рівня
                'status'          => $status,
                'title'           => $reward['title'],
                'image'           => $image
            );
        }

		// 3. ВИЗНАЧАЄМО ЄДИНУ АКТИВНУ СТРІЛКУ
        $active_arrow_set = false;
        foreach ($data['rewards'] as &$arrow_reward) {
            if (!$active_arrow_set && in_array($arrow_reward['status'], ['available', 'waiting', 'locked'])) {
                $arrow_reward['is_active_arrow'] = true;
                $active_arrow_set = true;
            } else {
                $arrow_reward['is_active_arrow'] = false;
            }
        }
        if (!$active_arrow_set && count($data['rewards']) > 0) {
            $data['rewards'][count($data['rewards']) - 1]['is_active_arrow'] = true;
        }
        unset($arrow_reward); // <--- КРИТИЧНО ВАЖЛИВО! Розриваємо зв'язок з масивом

        // 4. ПРАВИЛЬНИЙ РОЗРАХУНОК ПРОГРЕС-БАРУ ДЛЯ СУМИ
        if ($this->customer->isLogged() && $data['progression_type'] == 'amount') {
            $max_amount = 0;
            
            foreach ($calculated_rewards as $calc_r) {
                $max_amount = $calc_r['cumulative_amount'];
                if ($data['next_level_amount'] == 0 && $calc_r['cumulative_amount'] > $data['total_amount']) {
                    $data['next_level_amount'] = $calc_r['cumulative_amount'];
                }
            }

            if ($max_amount > 0) {
                $data['percent_to_next'] = min(100, round(($data['total_amount'] / $max_amount) * 100));
            } else {
                $data['percent_to_next'] = 100;
            }
        }

        // 5. ВИЗНАЧАЄМО СТАРТОВИЙ РІВЕНЬ ДЛЯ КАРУСЕЛІ
        $data['carousel_start_level'] = $data['current_level'] > 0 ? $data['current_level'] : 1;
        
        foreach ($data['rewards'] as $carousel_r) {
            if ($carousel_r['status'] == 'available') {
                $data['carousel_start_level'] = (int)$carousel_r['level']-1;
                break;
            }
        }

        return $this->load->view('extension/module/battlepass', $data);
    }

    public function claim() {
        $json = array();
        $json['redirect'] = false;
        
        if ($this->customer->isLogged()) {
            $this->load->model('extension/module/battlepass');
            $active_season = $this->model_extension_module_battlepass->getActiveSeason();
            
            if (!$active_season) {
                $json['error'] = 'Активний сезон не знайдено.';
                $this->response->setOutput(json_encode($json));
                return;
            }

            $season_id = (int)$active_season['season_id'];
            $progression_type = isset($active_season['progression_type']) ? $active_season['progression_type'] : 'orders';
            
            $level = (int)$this->request->post['level'];
            $customer_id = $this->customer->getId();
            
            $progress = $this->model_extension_module_battlepass->getCustomerProgress($customer_id, $season_id);
            $claimed = json_decode($progress['claimed_levels'], true) ?: array();

            // НОВА ЛОГІКА: Шукаємо нагороду і паралельно рахуємо її накопичувальну ціну
            $db_rewards = $this->model_extension_module_battlepass->getSeasonRewards($season_id);
            $reward = null;
            $target_amount = 0;
            $cumulative = 0;
            
            foreach ($db_rewards as $r) {
                $cumulative += (float)$r['required_amount'];
                if ($r['level'] == $level) {
                    $reward = $r;
                    $target_amount = $cumulative; // Накопичена сума саме для цього рівня
                    break;
                }
            }

            // 1. БАЗОВІ ПЕРЕВІРКИ
            if (!$reward) {
                $json['error'] = 'Нагороду не знайдено.';
            } elseif (in_array($level, $claimed)) {
                $json['error'] = 'Ви вже отримали цю нагороду.';
            } else {
                
                $can_claim = false;

                // 2. ПЕРЕВІРКА ПРАВА НА ОТРИМАННЯ
				if ($progression_type == 'amount') {
                    // Беремо округлену суму для перевірки доступу
                    $current_total_rounded = ceil((float)$progress['total_amount']);
                    if ($current_total_rounded >= $target_amount) {
                        $can_claim = true;
                    } else {
                        $json['error'] = 'Ви ще не накопичили необхідну суму для цього рівня.';
                    }
                } else {
                    if (!empty($progress['next_unlock_date']) && strtotime($progress['next_unlock_date']) > time()) {
                        $json['error'] = 'Будь ласка, зачекайте завершення часу очікування.';
                    } elseif ($level != (int)$progress['current_level'] + 1) {
                        $json['error'] = 'Ви ще не досягли цього рівня.';
                    } else {
                        $can_claim = true;
                    }
                }

                // 3. ВИДАЧА НАГОРОДИ (спочатку атомарний reserve — захист від даблклика)
                if ($can_claim) {
                    $reserved = $this->model_extension_module_battlepass->tryReserveClaim($customer_id, $season_id, $level);
                    if ($reserved === false) {
                        $json['error'] = 'Ви вже отримали цю нагороду.';
                    } elseif ($reward['reward_type'] == 'points' || $reward['reward_type'] == 'ultra') {
                        if ($reward['reward_type'] == 'points') {
                            $points_result = $this->addPoints($customer_id, $reward['reward_value']);
                            if ($points_result === 'error' || $points_result === false) {
                                $this->model_extension_module_battlepass->releaseClaimLevel($customer_id, $season_id, $level);
                                $json['error'] = 'Не вдалося нарахувати бонуси. Спробуйте пізніше.';
                                $this->response->setOutput(json_encode($json));
                                return;
                            }
                            $json['message'] = "Вітаємо! Ви виграли " . $reward['reward_value'] . " бонусних балів.";
                            if ($progression_type == 'orders') {
                                $json['message'] .= " Наступний подарунок буде доступний після оформлення та отримання замовлення через 24 години.";
                            }
                        } else {
                            $this->model_extension_module_battlepass->addUltra($customer_id, 'BattlePass Season ' . $season_id, $reward['reward_value']);
                            $json['message'] = "Вітаємо! Ви виграли ультра валюту";
                        }

                        $json['success'] = true;
                    } else {
                        $code = $this->createCoupon($reward);
                        $this->session->data['coupon'] = $code;
                        $json['success'] = true;
                        $json['message'] = "Вітаємо! Ви виграли подарунок! <br/> Отримати його можна за промокодом, який знаходиться у вашому особистому кабінеті в розділі «Мої промокоди». <br/>  Скористайтеся ним під час покупки та заберіть свій подарунок.";
                        $json['redirect'] = $this->url->link('account/promocodes', '', true);
                    }
                }
            }
        } else {
            $json['error'] = 'Авторизуйтесь';
        }
        
        $this->response->setOutput(json_encode($json));
    }

	public function claimlottery() {
        $json = array();
        $json['redirect'] = false;

        if (!$this->customer->isLogged()) {
            $json['error'] = 'Авторизуйтесь';
            $this->response->setOutput(json_encode($json));
            return;
        }

        $customer_id = (int)$this->customer->getId();
        $telephone = $this->db->escape($this->customer->getTelephone());

        // Прибираємо сьогоднішні биті записи (порожній/none/0), щоб не блокували повторний клейм
        $this->db->query("DELETE FROM `" . DB_PREFIX . "customer_lottery_history`
            WHERE customer_id = '" . $customer_id . "'
            AND date_added >= CURDATE()
            AND (
                prize_type = ''
                OR prize_type = 'none'
                OR prize_type IS NULL
                OR amount <= 0
            )");

        $prize = isset($this->session->data['lottery_prize']) ? $this->session->data['lottery_prize'] : null;

        // Якщо сесія з призом зникла - генеруємо новий, а не пишемо порожній запис
        if (!$this->isValidLotteryPrize($prize)) {
            $prize = $this->generatePrize();
            $this->session->data['lottery_prize'] = $prize;
        }

        $prize_type = $prize['type'];
        $prize_amount = (float)$prize['amount'];

        // Атомарний insert: тільки якщо сьогодні ще немає валідної видачі
        $this->db->query("INSERT INTO `" . DB_PREFIX . "customer_lottery_history`
            (`customer_id`, `telephone`, `prize_type`, `amount`, `date_added`)
            SELECT '" . $customer_id . "', '" . $telephone . "', '" . $this->db->escape($prize_type) . "', '" . $prize_amount . "', NOW()
            FROM DUAL
            WHERE NOT EXISTS (
                SELECT 1 FROM `" . DB_PREFIX . "customer_lottery_history`
                WHERE customer_id = '" . $customer_id . "'
                AND date_added >= CURDATE()
                AND prize_type IN ('points', 'ultra')
                AND amount > 0
            )");

        if (!$this->db->countAffected()) {
            $json['error'] = 'Ви вже отримали сьогоднішню нагороду.';
            unset($this->session->data['lottery_prize']);
            $this->response->setOutput(json_encode($json));
            return;
        }

        $history_id = (int)$this->db->getLastId();

        if ($prize_type == 'points') {
            $points_result = $this->addPoints($customer_id, $prize_amount, 1);

            if ($points_result === 'error' || $points_result === false) {
                $this->db->query("DELETE FROM `" . DB_PREFIX . "customer_lottery_history` WHERE lottery_history_id = '" . $history_id . "'");
                $json['error'] = 'Не вдалося нарахувати бонуси. Спробуйте пізніше.';
                $this->response->setOutput(json_encode($json));
                return;
            }
        } else {
            $this->load->model('extension/module/battlepass');
            $this->model_extension_module_battlepass->addUltra($customer_id, 'BattlePass Lottery ', $prize_amount);
        }

        unset($this->session->data['lottery_prize']);
        $this->session->data['success'] = 'Приз успешно зачислен!';

        $json['success'] = true;
        $json['message'] = 'Вітаємо! виграш вже зарахований на ваш рахунок!';
        $json['prize'] = array(
            'type' => $prize_type,
            'amount' => $prize_amount
        );

        $this->response->setOutput(json_encode($json));
    }
	
    public function onOrderHistoryAdd(&$route, &$args) {
        if (!isset($args[0]) || !isset($args[1]) || $args[1] != 5) return;

        $this->load->model('checkout/order');
        $order_info = $this->model_checkout_order->getOrder($args[0]);

        if ($order_info && $order_info['customer_id']) {
            $customer_id = $order_info['customer_id'];
            $this->load->model('extension/module/battlepass');
            $active_season = $this->model_extension_module_battlepass->getActiveSeason();
            
            if (!$active_season) return;
            $season_id = $active_season['season_id'];

			if ($active_season['progression_type'] == 'amount') {
                // Спочатку гарантуємо рядок прогресу, інакше UPDATE нічого не оновить
                $this->model_extension_module_battlepass->getCustomerProgress($customer_id, $season_id);

                // Округлюємо суму замовлення в більшу сторону перед додаванням у базу
                $order_total_rounded = ceil((float)$order_info['total']);
                $this->db->query("UPDATE " . DB_PREFIX . "customer_battlepass SET total_amount = total_amount + '" . $order_total_rounded . "' WHERE customer_id = '" . (int)$customer_id . "' AND season_id = '" . $season_id . "'");
                
                // Отримуємо нову суму та гарантовано беремо її як ціле число
                $progress = $this->model_extension_module_battlepass->getCustomerProgress($customer_id, $season_id);
            //    $total_amt = ceil((float)$progress['total_amount']);
                $total_amt = (isset($progress['total_amount'])&&$progress['total_amount'])?ceil((float)$progress['total_amount']):0;
                // НОВА ЛОГІКА: Рахуємо рівень через накопичувальну суму
                $db_rewards = $this->model_extension_module_battlepass->getSeasonRewards($season_id);
                $cumulative = 0;
                $new_level = 0;
                
                foreach ($db_rewards as $r) {
                    $cumulative += (float)$r['required_amount'];
                    if ($total_amt >= $cumulative) {
                        $new_level = $r['level'];
                    } else {
                        break; // Далі перевіряти немає сенсу, грошей не вистачає
                    }
                }
                
                if ($new_level > $progress['current_level']) {
                    $this->db->query("UPDATE " . DB_PREFIX . "customer_battlepass SET current_level = '" . (int)$new_level . "' WHERE customer_id = '" . (int)$customer_id . "' AND season_id = '" . $season_id . "'");
                }
                
            } else {
                $progress = $this->model_extension_module_battlepass->getCustomerProgress($customer_id, $season_id);
                $claimed = json_decode($progress['claimed_levels'], true) ?: array();
                $next_level = (int)$progress['current_level'] + 1;

                if (in_array($next_level, $claimed)) {
                    $reward = $this->model_extension_module_battlepass->getRewardByLevel($season_id, $next_level);
                    if ($reward && ($reward['reward_type'] == 'product' || $reward['reward_type'] == 'discount')) {
                        $this->model_extension_module_battlepass->setNextUnlockTimer($customer_id, $season_id, 24);
                        $this->db->query("UPDATE " . DB_PREFIX . "customer_battlepass SET current_level = current_level + 1 WHERE customer_id = '" . (int)$customer_id . "' AND season_id = '" . (int)$season_id . "'");
                    }
                }
            }
        }
    }

    private function createCoupon($reward) {
        $code = 'BP' . strtoupper(substr(md5(uniqid()), 0, 6));
        $type = ($reward['reward_type'] == 'product') ? 'M' : 'P';
        $discount = ($reward['reward_type'] == 'product') ? 0 : $reward['reward_value'];
        
        $this->db->query("INSERT INTO " . DB_PREFIX . "coupon SET name = 'Battle Pass " . $this->db->escape($reward['title']) . "', code = '" . $this->db->escape($code) . "', discount = '" . (float)$discount . "', type = '" . $type . "', total = '0', logged = '1', shipping = '0', date_start = NOW(), date_added = NOW(), date_end = NOW() + INTERVAL 90 DAY, uses_total = '1', uses_customer = '1', status = '1'");
        $coupon_id = $this->db->getLastId();

        if ($reward['reward_type'] == 'product') {
            $this->db->query("INSERT INTO " . DB_PREFIX . "coupon_product SET coupon_id = '" . (int)$coupon_id . "', product_id = '" . (int)$reward['reward_value'] . "'");
        }
        
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_battlepass_coupons SET customer_id = '" . (int)$this->customer->getId() . "', telephone = '" . (int)$this->customer->getTelephone() . "', date_added = NOW(), date_valid = NOW() + INTERVAL 30 DAY, prize = '" . $this->db->escape($reward['title']) . "', code = '" . $this->db->escape($code) . "'");
        
        return $code;
    }

    public function addPoints($customer_id, $sum, $type = 0) {
        $telephone = $this->customer->getTelephone();
        $telephone = str_replace(['(', ')', '-', ' '], '', $telephone);
        $data = array("telephone" => $telephone);
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
            curl_close($curl);

            if ($curl_errno != 0) return 'error';
            if ($aInfo["http_code"] != '200') return 'error';

            if ($response == 'success') {
                $this->db->query("INSERT INTO " . DB_PREFIX . "customer_reward SET customer_id = '" . (int)$customer_id . "', points = '" . (int)$sum . "', description = 'Battle pass" . ($type?' lottery':'')."', date_added = NOW()");
			//	$this->log->write('Бонусы  БП зачислены ' . $telephone . ' бонусов ' . (int)$sum . '. ответ сервера: ' . $response);
            } else {
				$this->log->write('Бонусы  БП не зачислены ' . $telephone . ' бонусов ' . (int)$sum . '. ответ сервера: ' . $response);
                return 'error';
            }
        } catch (Exception $e) {
            return false;
        }
    }
	


	private function isValidLotteryPrize($prize) {
		if (!is_array($prize)) {
			return false;
		}

		if (empty($prize['type']) || !isset($prize['amount'])) {
			return false;
		}

		if ($prize['type'] != 'points' && $prize['type'] != 'ultra') {
			return false;
		}

		return (float)$prize['amount'] > 0;
	}

	private function generatePrize() {

        $rand = mt_rand(1, 10000);
        $current_threshold = 0;

        $prizes = [
            // Баллы (сума = 9990 / 10000 = 99.9%)
            ['type' => 'points', 'amount' => 1,  'chance' => 4000], // 40%
            ['type' => 'points', 'amount' => 2,  'chance' => 3000], // 30%
            ['type' => 'points', 'amount' => 3,  'chance' => 1810], // 18.1%
            ['type' => 'points', 'amount' => 4,  'chance' => 200],  // 2%
            ['type' => 'points', 'amount' => 5,  'chance' => 200],  // 2%
            ['type' => 'points', 'amount' => 6,  'chance' => 200],  // 2%
            ['type' => 'points', 'amount' => 7,  'chance' => 100],  // 1%
            ['type' => 'points', 'amount' => 8,  'chance' => 100],  // 1%
            ['type' => 'points', 'amount' => 9,  'chance' => 100],  // 1%
            ['type' => 'points', 'amount' => 10, 'chance' => 100],  // 1%
            ['type' => 'points', 'amount' => 11, 'chance' => 50],   // 0.5%
            ['type' => 'points', 'amount' => 12, 'chance' => 50],   // 0.5%
            ['type' => 'points', 'amount' => 13, 'chance' => 30],   // 0.3%
            ['type' => 'points', 'amount' => 14, 'chance' => 30],   // 0.3%
            ['type' => 'points', 'amount' => 15, 'chance' => 20],   // 0.2%

            // Ультравалюта (сума = 10 / 10000 = 0.1%)
            ['type' => 'ultra',  'amount' => 1,  'chance' => 5],    // 0.05%
            ['type' => 'ultra',  'amount' => 2,  'chance' => 5],    // 0.05%
        ];

        foreach ($prizes as $prize) {
            $current_threshold += $prize['chance'];
            if ($rand <= $current_threshold) {
                return $prize;
            }
        }

        return ['type' => 'points', 'amount' => 1];
    }
	
	private function declOfNum($number, $titles) {
		// 1. Защита: берем число по модулю и приводим к целому (отсекаем копейки/нули)
		$number = abs((int)$number);
		
		$cases = [2, 0, 1, 1, 1, 2];
		
		// 2. Используем классическую формулу (заменено > 7 на > 4) и убраны неразрывные пробелы
		$index = ($number % 100 > 4 && $number % 100 < 20) 
				 ? 2 
				 : $cases[($number % 10 < 5) ? $number % 10 : 5];
				 
		// 3. Возвращаем результат, добавляя проверку существования индекса на всякий случай
		return isset($titles[$index]) ? $titles[$index] : $titles[2];
	}
}