<?php
class ModelExtensionModuleBattlepass extends Model {

public function addSeason($data) {
        $progression_type = isset($data['progression_type']) ? $data['progression_type'] : 'orders';
        
        $this->db->query("INSERT INTO " . DB_PREFIX . "battlepass_season SET 
            name = '" . $this->db->escape($data['name']) . "', 
            date_start = '" . $this->db->escape($data['date_start']) . "', 
            date_end = '" . $this->db->escape($data['date_end']) . "', 
            progression_type = '" . $this->db->escape($progression_type) . "',
            status = '" . (int)$data['status'] . "'");

        $season_id = $this->db->getLastId(); 

        if (isset($data['reward'])) {
            foreach ($data['reward'] as $reward) {
                $required_amount = isset($reward['required_amount']) ? (float)$reward['required_amount'] : 0;
                $this->db->query("INSERT INTO " . DB_PREFIX . "battlepass_reward SET 
                    season_id = '" . (int)$season_id . "', 
                    level = '" . (int)$reward['level'] . "', 
                    required_amount = '" . (float)$required_amount . "',
                    reward_type = '" . $this->db->escape($reward['type']) . "', 
                    title = '" . $this->db->escape($reward['title']) . "',
                    reward_value = '" . $this->db->escape($reward['value']) . "', 
                    image = '" . $this->db->escape($reward['image']) . "'");
            }
        }
        return $season_id;
    }

    public function editSeason($season_id, $data) {
        $progression_type = isset($data['progression_type']) ? $data['progression_type'] : 'orders';

        $this->db->query("UPDATE " . DB_PREFIX . "battlepass_season SET 
            name = '" . $this->db->escape($data['name']) . "', 
            date_start = '" . $this->db->escape($data['date_start']) . "', 
            date_end = '" . $this->db->escape($data['date_end']) . "', 
            progression_type = '" . $this->db->escape($progression_type) . "',
            status = '" . (int)$data['status'] . "' 
            WHERE season_id = '" . (int)$season_id . "'");

        $this->db->query("DELETE FROM " . DB_PREFIX . "battlepass_reward WHERE season_id = '" . (int)$season_id . "'");

        if (isset($data['reward'])) {
            foreach ($data['reward'] as $reward) {
                $required_amount = isset($reward['required_amount']) ? (float)$reward['required_amount'] : 0;
                $this->db->query("INSERT INTO " . DB_PREFIX . "battlepass_reward SET 
                    season_id = '" . (int)$season_id . "', 
                    level = '" . (int)$reward['level'] . "', 
                    required_amount = '" . (float)$required_amount . "',
                    reward_type = '" . $this->db->escape($reward['type']) . "', 
                    title = '" . $this->db->escape($reward['title']) . "',
                    reward_value = '" . $this->db->escape($reward['value']) . "', 
                    image = '" . $this->db->escape($reward['image']) . "'");
            }
        }
    }

    public function getSeasons() {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "battlepass_season ORDER BY date_start DESC");
        return $query->rows;
    }

    public function getRewardsBySeason($season_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "battlepass_reward WHERE season_id = '" . (int)$season_id . "' ORDER BY level ASC");
        return $query->rows;
    }
	
	public function deleteSeason($season_id) {
		// Удаляем награды, привязанные к сезону
		$this->db->query("DELETE FROM " . DB_PREFIX . "battlepass_reward WHERE season_id = '" . (int)$season_id . "'");
		// Удаляем сам сезон
		$this->db->query("DELETE FROM " . DB_PREFIX . "battlepass_season WHERE season_id = '" . (int)$season_id . "'");
	}
	
	public function getProductsByBattlePass($data = array()) {
		$sql = "SELECT p.product_id, pd.name FROM " . DB_PREFIX . "product p 
				LEFT JOIN " . DB_PREFIX . "product_description pd ON (p.product_id = pd.product_id) 
				LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON (p.product_id = p2c.product_id)
				WHERE pd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

		if (!empty($data['filter_name'])) {
			$sql .= " AND pd.name LIKE '" . $this->db->escape($data['filter_name']) . "%'";
		}

		if (!empty($data['filter_category_id'])) {
			$sql .= " AND p2c.category_id IN (
				SELECT category_id FROM " . DB_PREFIX . "category_path 
				WHERE path_id = '" . (int)$data['filter_category_id'] . "'
			)";
		}

		$sql .= " GROUP BY p.product_id ORDER BY pd.name ASC LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];

		$query = $this->db->query($sql);
		return $query->rows;
	}
	
	public function getTotalParticipantsBySeason($season_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_battlepass WHERE season_id = '" . (int)$season_id . "'");
		return $query->row['total'];
	}

	public function getTopParticipantsBySeason($season_id, $limit = 20) {
		$query = $this->db->query("
			SELECT cb.*, CONCAT(c.firstname, ' ', c.lastname) as name, c.email 
			FROM " . DB_PREFIX . "customer_battlepass cb 
			LEFT JOIN " . DB_PREFIX . "customer c ON cb.customer_id = c.customer_id 
			WHERE cb.season_id = '" . (int)$season_id . "' 
			ORDER BY cb.current_level DESC, cb.next_unlock_date DESC 
			LIMIT " . (int)$limit
		);
		return $query->rows;
	}
	
	public function getLevelDistribution($season_id) {
		$query = $this->db->query("
			SELECT current_level, COUNT(*) as total 
			FROM " . DB_PREFIX . "customer_battlepass 
			WHERE season_id = '" . (int)$season_id . "' 
			GROUP BY current_level 
			ORDER BY current_level DESC
		");
		return $query->rows;
	}
	
	public function getDetailedDistribution($season_id) {
		$query = $this->db->query("SELECT current_level, claimed_levels, next_unlock_date FROM " . DB_PREFIX . "customer_battlepass WHERE season_id = '" . (int)$season_id . "'");
		
		$distribution = array();
		$now = time();

		foreach ($query->rows as $row) {
			$lvl = (int)$row['current_level'];
			$claimed = json_decode($row['claimed_levels'], true) ?: array();
			$last_claimed = !empty($claimed) ? max($claimed) : 0;
			
			// Проверяем таймер: существует ли он и в будущем ли он
			$unlock_time = (!empty($row['next_unlock_date']) && $row['next_unlock_date'] != '0000-00-00 00:00:00') ? strtotime($row['next_unlock_date']) : 0;
			$is_timer_active = ($unlock_time > $now);

			if (!isset($distribution[$lvl])) {
				$distribution[$lvl] = array(
					'total'         => 0,
					'ready_claim'   => 0, // Зеленый: Ждут клика
					'waiting_order' => 0, // Желтый: Ждут менеджера
					'on_timer'      => 0  // Синий: Ждут время
				);
			}

			$distribution[$lvl]['total']++;

			// --- НОВАЯ ЛОГИКА 3-Х СОСТОЯНИЙ ---

			// 1. ТАЙМЕР: Если время установлено и оно еще не наступило
			if ($is_timer_active) {
				$distribution[$lvl]['on_timer']++;
			}
			// 2. ЖДУТ ЗАКАЗ: Кнопку нажали (уровень в claimed есть), но уровень игрока еще не подняли
			elseif ($last_claimed > $lvl) {
				$distribution[$lvl]['waiting_order']++;
			}
			// 3. МОГУТ ЗАБРАТЬ: Все остальные случаи
			// Сюда попадают:
			// - Новички (0 уровень, ничего не забрали)
			// - Те, у кого таймер истек
			// - Те, у кого уровень > забраных
			else {
				$distribution[$lvl]['ready_claim']++;
			}
		}
		
		ksort($distribution);
		return $distribution;
	}
}