<?php
class ModelExtensionModuleBattlepass extends Model {
	
	public function getNextLevelAmount($season_id, $current_level) {
		$query = $this->db->query("SELECT required_amount FROM " . DB_PREFIX . "battlepass_reward 
			WHERE season_id = '" . (int)$season_id . "' 
			AND level = '" . (int)($current_level + 1) . "' 
			LIMIT 1");

		if ($query->num_rows) {
			return (float)$query->row['required_amount'];
		}

		return 0;
	}
	
	public function addOrderAmount($customer_id, $season_id, $amount) {
		// 1. Гарантуємо рядок прогресу, потім додаємо суму
		$this->getCustomerProgress($customer_id, $season_id);
		$this->db->query("UPDATE " . DB_PREFIX . "customer_battlepass SET 
			total_amount = total_amount + '" . (float)$amount . "' 
			WHERE customer_id = '" . (int)$customer_id . "' AND season_id = '" . (int)$season_id . "'");

		// 2. Получаем обновленные данные клиента
		$progress = $this->getCustomerProgress($customer_id, $season_id);
		$current_total = (float)$progress['total_amount'];
		$current_lvl = (int)$progress['current_level'];

		// 3. Ищем, до какого максимального уровня он теперь "дорос" по сумме
		$query = $this->db->query("SELECT MAX(level) as max_reachable_level 
			FROM " . DB_PREFIX . "battlepass_reward 
			WHERE season_id = '" . (int)$season_id . "' 
			AND required_amount <= '" . (float)$current_total . "'");

		if ($query->num_rows && $query->row['max_reachable_level'] > $current_lvl) {
			$new_level = (int)$query->row['max_reachable_level'];
			
			// Обновляем уровень в БД
			$this->db->query("UPDATE " . DB_PREFIX . "customer_battlepass SET 
				current_level = '" . (int)$new_level . "' 
				WHERE customer_id = '" . (int)$customer_id . "' AND season_id = '" . (int)$season_id . "'");
			
			return $new_level;
		}

		return $current_lvl;
	}

    public function getActiveSeason() {
        return $this->db->query("SELECT * FROM " . DB_PREFIX . "battlepass_season WHERE status = '1' AND date_start <= CURDATE() AND date_end >= CURDATE() LIMIT 1")->row;
    }

    public function getCustomerProgress($customer_id, $season_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_battlepass WHERE customer_id = '" . (int)$customer_id . "' AND season_id = '" . (int)$season_id . "'");

        if ($query->num_rows) {
            return $query->row;
        } else {
            $this->db->query("INSERT INTO " . DB_PREFIX . "customer_battlepass SET customer_id = '" . (int)$customer_id . "', season_id = '" . (int)$season_id . "', current_level = 0, total_amount = 0, claimed_levels = '[]', next_unlock_date = '0000-00-00 00:00:00'");
            
            return array(
                'customer_id'      => $customer_id,
                'season_id'        => $season_id,
                'current_level'    => 0,
                'total_amount'     => 0,
                'claimed_levels'   => '[]',
                'next_unlock_date' => '0000-00-00 00:00:00'
            );
        }
    }

    public function getRewardByLevel($season_id, $level) {
        return $this->db->query("SELECT * FROM " . DB_PREFIX . "battlepass_reward WHERE season_id = '" . (int)$season_id . "' AND level = '" . (int)$level . "'")->row;
    }

    public function getSeasonRewards($season_id) {
        return $this->db->query("SELECT * FROM " . DB_PREFIX . "battlepass_reward WHERE season_id = '" . (int)$season_id . "' ORDER BY level ASC")->rows;
    }

    public function updateClaimedLevels($customer_id, $season_id, $claimed) {
        $this->db->query("UPDATE " . DB_PREFIX . "customer_battlepass SET claimed_levels = '" . $this->db->escape(json_encode($claimed)) . "' WHERE customer_id = '" . (int)$customer_id . "' AND season_id = '" . (int)$season_id . "'");
    }

    /**
     * Атомарно резервирует уровень в claimed (защита от даблклика).
     * @return array|false новый claimed или false если уже занят
     */
    public function tryReserveClaim($customer_id, $season_id, $level) {
        $customer_id = (int)$customer_id;
        $season_id = (int)$season_id;
        $level = (int)$level;

        $this->db->query("START TRANSACTION");
        $query = $this->db->query("SELECT claimed_levels FROM " . DB_PREFIX . "customer_battlepass WHERE customer_id = '" . $customer_id . "' AND season_id = '" . $season_id . "' FOR UPDATE");

        if (!$query->num_rows) {
            $this->db->query("ROLLBACK");
            return false;
        }

        $claimed = json_decode($query->row['claimed_levels'], true) ?: array();
        $claimed = array_map('intval', $claimed);

        if (in_array($level, $claimed, true)) {
            $this->db->query("ROLLBACK");
            return false;
        }

        $claimed[] = $level;
        $claimed = array_values(array_unique($claimed));
        sort($claimed);

        $this->db->query("UPDATE " . DB_PREFIX . "customer_battlepass SET claimed_levels = '" . $this->db->escape(json_encode($claimed)) . "' WHERE customer_id = '" . $customer_id . "' AND season_id = '" . $season_id . "'");
        $this->db->query("COMMIT");

        return $claimed;
    }

    public function releaseClaimLevel($customer_id, $season_id, $level) {
        $progress = $this->getProgress($customer_id, $season_id);
        if (!$progress) {
            return;
        }

        $claimed = json_decode($progress['claimed_levels'], true) ?: array();
        $claimed = array_values(array_filter(array_map('intval', $claimed), function ($item) use ($level) {
            return $item !== (int)$level;
        }));

        $this->updateClaimedLevels($customer_id, $season_id, $claimed);
    }

    public function setNextUnlockTimer($customer_id, $season_id, $hours = 24) {
        $date = date('Y-m-d H:i:s', strtotime("+" . (int)$hours . " hours"));
        $this->db->query("UPDATE " . DB_PREFIX . "customer_battlepass SET next_unlock_date = '" . $this->db->escape($date) . "' WHERE customer_id = '" . (int)$customer_id . "' AND season_id = '" . (int)$season_id . "'");
    }
    
    public function getProgress($customer_id, $season_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_battlepass WHERE customer_id = '" . (int)$customer_id . "' AND season_id = '" . (int)$season_id . "'");
        return $query->row;
    }

    public function getLastClaimedLevel($customer_id, $season_id) {
        $query = $this->db->query("SELECT claimed_levels FROM " . DB_PREFIX . "customer_battlepass WHERE customer_id = '" . (int)$customer_id . "' AND season_id = '" . (int)$season_id . "'");
        if ($query->num_rows) {
            $levels = json_decode($query->row['claimed_levels'], true);
            return (!empty($levels)) ? (int)max($levels) : 0;
        }
        return 0;
    }

    public function isWaitingForOrderStatus($customer_id, $season_id) {
        $progress = $this->getProgress($customer_id, $season_id);
        if ($progress) {
            return (empty($progress['next_unlock_date']) || $progress['next_unlock_date'] == '0000-00-00 00:00:00');
        }
        return false;
    }
    
    public function addUltra($customer_id, $description = '', $ultra = 0) {
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_ultra SET customer_id = '" . (int)$customer_id . "', ultra = '" . (int)$ultra . "', description = '" . $this->db->escape($description) . "', date_added = NOW()");
    }
    
    public function getTotalUltra($customer_id) {
        $query = $this->db->query("SELECT SUM(ultra) AS total FROM `" . DB_PREFIX . "customer_ultra` WHERE customer_id = '" . (int)$customer_id . "'");
        return ($query->row['total']) ? $query->row['total'] : 0;
    }
}