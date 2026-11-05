<?php
/**
 * Фиксация менеджера, который создал/присвоил ТТН (НП / УП).
 * Нужно для отчета COD: cron ставит "Готовий" с user_id=0, а обработал заказ менеджер.
 *
 * Важно: user_id пишем сразу при вызове mark(), даже если номер ТТН ещё не попал в order
 * (ionCube saveCN часто отдаёт success раньше записи номера). Без этого в рейтинге "—".
 */
class ModelExtensionModuleCnManagerTrack extends Model {
	public function mark($order_id, $carrier = 'novaposhta', $number = '', $write_history = true) {
		$order_id = (int)$order_id;
		$carrier = ($carrier === 'ukrposhta') ? 'ukrposhta' : 'novaposhta';

		if ($order_id < 1) {
			return false;
		}

		$user_id = $this->resolveCurrentUserId();

		if ($user_id < 1) {
			return false;
		}

		$number_col = $carrier . '_cn_number';
		$user_col = $carrier . '_cn_user_id';

		$this->ensureCnUserIdColumn($carrier);
		$this->ensureOrderHistoryUserIdColumn();

		$order = $this->db->query("SELECT `order_status_id`, `" . $number_col . "` AS `cn_number`, `" . $user_col . "` AS `cn_user_id`
			FROM `" . DB_PREFIX . "order`
			WHERE `order_id` = '" . $order_id . "'")->row;

		if (!$order) {
			return false;
		}

		$cn_number = trim($number !== '' ? $number : (string)$order['cn_number']);

		// Ждём номер только если нужна запись в историю (after saveCN).
		// В before usleep нельзя - тормозит сам saveCN.
		if ($cn_number === '' && $write_history) {
			for ($i = 0; $i < 5; $i++) {
				usleep(300000);

				$order = $this->db->query("SELECT `order_status_id`, `" . $number_col . "` AS `cn_number`, `" . $user_col . "` AS `cn_user_id`
					FROM `" . DB_PREFIX . "order`
					WHERE `order_id` = '" . $order_id . "'")->row;

				if (!$order) {
					return false;
				}

				$cn_number = trim((string)$order['cn_number']);

				if ($cn_number !== '') {
					break;
				}
			}
		}

		$previous_user_id = (int)$order['cn_user_id'];

		// Главное: фиксируем менеджера СРАЗУ, даже если номер ещё пустой.
		$this->db->query("UPDATE `" . DB_PREFIX . "order`
			SET `" . $user_col . "` = '" . (int)$user_id . "'
			WHERE `order_id` = '" . $order_id . "'");

		if (!$write_history) {
			return true;
		}

		if ($cn_number === '') {
			return true;
		}

		$prefix = ($carrier === 'ukrposhta') ? '[UP]' : '[NP]';
		$comment = $prefix . ': ТТН створено/присвоєно: ' . $cn_number;

		$exists = $this->db->query("SELECT order_history_id FROM `" . DB_PREFIX . "order_history`
			WHERE `order_id` = '" . $order_id . "'
			AND `user_id` = '" . (int)$user_id . "'
			AND `comment` = '" . $this->db->escape($comment) . "'
			LIMIT 1");

		if ($exists->num_rows) {
			return true;
		}

		if ($previous_user_id === $user_id && trim((string)$order['cn_number']) === $cn_number) {
			$recent = $this->db->query("SELECT order_history_id FROM `" . DB_PREFIX . "order_history`
				WHERE `order_id` = '" . $order_id . "'
				AND `user_id` = '" . (int)$user_id . "'
				AND `comment` LIKE '" . $this->db->escape($prefix . ': ТТН створено/присвоєно: ' . $cn_number) . "'
				LIMIT 1");

			if ($recent->num_rows) {
				return true;
			}
		}

		$this->db->query("INSERT INTO `" . DB_PREFIX . "order_history`
			SET `order_id` = '" . $order_id . "',
				`order_status_id` = '" . (int)$order['order_status_id'] . "',
				`notify` = '0',
				`user_id` = '" . (int)$user_id . "',
				`comment` = '" . $this->db->escape($comment) . "',
				`date_added` = NOW()");

		return true;
	}

	public function clear($order_id, $carrier = 'novaposhta') {
		$order_id = (int)$order_id;
		$carrier = ($carrier === 'ukrposhta') ? 'ukrposhta' : 'novaposhta';

		if ($order_id < 1) {
			return;
		}

		$this->ensureCnUserIdColumn($carrier);

		$this->db->query("UPDATE `" . DB_PREFIX . "order`
			SET `" . $carrier . "_cn_user_id` = '0'
			WHERE `order_id` = '" . $order_id . "'");
	}

	public function ensureCnUserIdColumn($carrier = 'novaposhta') {
		static $checked = array();

		$carrier = ($carrier === 'ukrposhta') ? 'ukrposhta' : 'novaposhta';
		$column = $carrier . '_cn_user_id';

		if (!empty($checked[$column])) {
			return;
		}

		$checked[$column] = true;

		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order` LIKE '" . $this->db->escape($column) . "'");

		if (!$query->num_rows) {
			$after = ($carrier === 'ukrposhta') ? 'ukrposhta_cn_uuid' : 'novaposhta_cn_ref';
			$after_exists = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order` LIKE '" . $this->db->escape($after) . "'");

			if ($after_exists->num_rows) {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "order`
					ADD COLUMN `" . $column . "` INT(11) NOT NULL DEFAULT 0 AFTER `" . $after . "`,
					ADD INDEX `idx_" . $column . "` (`" . $column . "`)");
			} else {
				$this->db->query("ALTER TABLE `" . DB_PREFIX . "order`
					ADD COLUMN `" . $column . "` INT(11) NOT NULL DEFAULT 0,
					ADD INDEX `idx_" . $column . "` (`" . $column . "`)");
			}
		}
	}

	private function resolveCurrentUserId() {
		if (isset($this->user) && is_object($this->user) && method_exists($this->user, 'isLogged') && $this->user->isLogged()) {
			return (int)$this->user->getId();
		}

		// fallback на session (на всякий случай, если user не в registry модели)
		if (!empty($this->session->data['user_id'])) {
			return (int)$this->session->data['user_id'];
		}

		return 0;
	}

	private function ensureOrderHistoryUserIdColumn() {
		static $checked = false;

		if ($checked) {
			return;
		}

		$checked = true;

		$query = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "order_history` LIKE 'user_id'");

		if (!$query->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "order_history`
				ADD COLUMN `user_id` INT(11) NOT NULL DEFAULT 0 AFTER `notify`,
				ADD INDEX `idx_order_history_user_id` (`user_id`)");
		}
	}
}
