<?php
class ControllerExtensionModuleBattlepass extends Controller {
	public function index() {
		$this->load->language('extension/module/battlepass');
		$this->document->setTitle($this->language->get('heading_title'));
		$this->load->model('extension/module/battlepass');
		$this->load->model('tool/image');
		$this->load->model('catalog/product');

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_module'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true)
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/module/battlepass', 'user_token=' . $this->session->data['user_token'], true)
		);
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            foreach ($this->request->post['battlepass_seasons'] as $season_id => $season_data) {
                if (strpos($season_id, 'new') !== false) {
                    $this->model_extension_module_battlepass->addSeason($season_data);
                } else {
                    $this->model_extension_module_battlepass->editSeason($season_id, $season_data);
                }
            }
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module', true));
        }

        $data['battlepass_seasons'] = array();
        $seasons = $this->model_extension_module_battlepass->getSeasons();

        foreach ($seasons as $season) {
            // 1. Сбор наград (ваш код)
			$rewards = $this->model_extension_module_battlepass->getRewardsBySeason($season['season_id']);
            $reward_data = array();
            
            foreach ($rewards as $reward) {
                $image = ($reward['image'] && is_file(DIR_IMAGE . $reward['image'])) ? $reward['image'] : 'no_image.png';
                $reward_name = $reward['reward_value']; 
                if ($reward['reward_type'] == 'product') {
                    $product_info = $this->model_catalog_product->getProduct($reward['reward_value']);
                    if ($product_info) $reward_name = $product_info['name'];
                }
                $reward_data[] = array(
                    'level'           => $reward['level'],
                    'required_amount' => isset($reward['required_amount']) ? $reward['required_amount'] : 0, // НОВЕ ПОЛЕ
                    'reward_type'     => $reward['reward_type'],
                    'reward_value'    => $reward['reward_value'],
                    'reward_name'     => $reward_name,
                    'title'           => $reward['title'],
                    'thumb'           => $this->model_tool_image->resize($image, 100, 100),
                    'image'           => $reward['image']
                );
            }

            // 2. Сбор СТАТИСТИКИ для этого сезона
            $total_participants = $this->model_extension_module_battlepass->getTotalParticipantsBySeason($season['season_id']);
            $top_players = $this->model_extension_module_battlepass->getTopParticipantsBySeason($season['season_id'], 20);
            
			$stats_data = array();
			foreach ($top_players as $player) {
				$claimed = json_decode($player['claimed_levels'], true) ?: array();
				$last_claimed = !empty($claimed) ? max($claimed) : 0;
				$current_lvl = (int)$player['current_level'];
				$now = time();
				$unlock_time = (!empty($player['next_unlock_date']) && $player['next_unlock_date'] != '0000-00-00 00:00:00') ? strtotime($player['next_unlock_date']) : 0;

				// 1. НА ТАЙМЕРІ (Синій)
				if ($unlock_time > $now) {
					$status_text = 'На таймері';
					$label = 'info';
				} 
				// 2. ОЧІКУЄ СТАТУС ЗАМОВЛЕННЯ (Жовтий)
				elseif ($last_claimed > $current_lvl) {
					$status_text = 'Чекає замовлення';
					$label = 'warning';
				} 
				// 3. МОЖЕ ЗАБРАТИ ПРИЗ (Зелений) — сюди потрапляють і новачки, і ті, у кого вийшов час
				else {
					$status_text = 'Приз доступний!';
					$label = 'success';
				}

				$stats_data[] = array(
					'name'   => $player['name'] ?: 'ID: ' . $player['customer_id'],
					'level'  => $current_lvl,
					'status' => $status_text,
					'label'  => $label
				);
			}

			
			$detailed_stats = $this->model_extension_module_battlepass->getDetailedDistribution($season['season_id']);
			
			$has_active_players = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer_battlepass WHERE season_id = '" . (int)$season['season_id'] . "' AND current_level >= 1 LIMIT 1")->num_rows;
			
			$data['battlepass_seasons'][] = array(
                'season_id'        => $season['season_id'],
                'name'             => $season['name'],
                'progression_type' => isset($season['progression_type']) ? $season['progression_type'] : 'orders', // НОВЕ ПОЛЕ
				'has_active_players' => $has_active_players, // ПЕРЕДАЕМ ФЛАГ В ШАБЛОН
                'date_start'       => ($season['date_start'] != '0000-00-00') ? $season['date_start'] : '',
                'date_end'         => ($season['date_end'] != '0000-00-00') ? $season['date_end'] : '',
                'status'           => $season['status'],
                'rewards'          => $reward_data,
                'total_clients'    => $total_participants,
                'stats'            => $stats_data,
                'detailed_stats'   => $detailed_stats,
            );
        }

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/module/battlepass', $data));
	}

	public function delete() {
		$this->load->model('extension/module/battlepass');
		$json = array();

		if (isset($this->request->get['season_id']) && $this->validate()) {
			$this->model_extension_module_battlepass->deleteSeason($this->request->get['season_id']);
			$json['success'] = true;
		} else {
			$json['error'] = 'Ошибка удаления';
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'extension/module/account')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
	
	public function autocomplete() {
		$json = array();

		if (isset($this->request->get['filter_name'])) {
			$this->load->model('extension/module/battlepass');
			
			$filter_data = array(
				'filter_name'        => $this->request->get['filter_name'],
				'filter_category_id' => isset($this->request->get['filter_category_id']) ? $this->request->get['filter_category_id'] : null,
				'filter_sub_category'=> true, // Включаем поиск во вложенных категориях
				'start'              => 0,
				'limit'              => 10
			);

			$results = $this->model_extension_module_battlepass->getProductsByBattlePass($filter_data);

			foreach ($results as $result) {
				$json[] = array(
					'product_id' => $result['product_id'],
					'name'       => strip_tags(html_entity_decode($result['name'], ENT_QUOTES, 'UTF-8'))
				);
			}
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}