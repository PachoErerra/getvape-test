<?php
/**
 * GetVape Знижки сайту - catalog runtime (lock / 1C sync / cron)
 * Author: Not The Same (https://ntsame.agency/)
 * Contact: hoffmanxyz@gmail.com
 */
class ModelExtensionModuleGetvapeZnyzhky extends Model {
	private $scope_weight = array(
		'product'      => 3,
		'manufacturer' => 2,
		'category'     => 1
	);

	public function getLock($product_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "getvape_znyzhky_lock` WHERE `product_id` = '" . (int)$product_id . "'");

		return $query->row;
	}

	/**
	 * Знайти переможне правило для одного товару (бренд / категорія / товар).
	 */
	public function findWinningRuleForProduct($product_id) {
		$product_id = (int)$product_id;

		if ($product_id < 1) {
			return null;
		}

		$product = $this->db->query("SELECT manufacturer_id FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$product_id . "'");

		if (!$product->num_rows) {
			return null;
		}

		$manufacturer_id = (int)$product->row['manufacturer_id'];
		$today = date('Y-m-d');

		$rules = $this->db->query("SELECT * FROM `" . DB_PREFIX . "getvape_znyzhky_rule`
			WHERE status = '1'
			AND (date_end = '0000-00-00' OR date_end >= '" . $this->db->escape($today) . "')
			ORDER BY sort_order DESC, rule_id DESC");

		if (!$rules->num_rows) {
			return null;
		}

		$winner = null;

		foreach ($rules->rows as $rule) {
			$weight = $this->getMatchWeightForProduct($rule, $product_id, $manufacturer_id);

			if ($weight < 1) {
				continue;
			}

			$candidate = $rule;
			$candidate['_match_weight'] = $weight;

			if (!$winner || $this->isBetterRule($candidate, $winner)) {
				$winner = $candidate;
			}
		}

		return $winner;
	}

	/**
	 * Застосувати правило до товару від заданої базової ціни.
	 * $backup - дані знижки 1С (backup_price / backup_special / backup_has_special), щоб після зняття сайту одразу повернути.
	 * Повертає special або false/0.
	 */
	public function applyRuleToProduct($product_id, $rule, $price, $backup = null) {
		$this->migrateLockBackup();
		$this->migrateLockOptionBackup();

		$special = $this->calculateSpecial($price, $rule['discount_type'], $rule['discount_value']);

		if ($special === false) {
			$this->unlockProduct($product_id);
			return 0;
		}

		$existing = $this->getLock($product_id);

		if ($existing) {
			$this->restoreMinusOptionPrices($product_id, $existing);
		}

		$dates = $this->resolveSpecialDates($rule);
		$backup = $this->resolveBackup($product_id, $backup);
		$backup_options = $this->snapshotMinusOptionPrices($product_id);

		$this->writeProductSpecial($product_id, $special, $dates['date_start'], $dates['date_end']);
		$this->writeLock($product_id, $rule, $special, $backup, $backup_options);

		$this->db->query("UPDATE `" . DB_PREFIX . "product` SET points = '" . (float)$special . "' WHERE product_id = '" . (int)$product_id . "'");
		$this->syncProductMetaPrice($product_id, $price, $special);
		$this->scaleMinusOptionPrices($product_id, $price, $special, $backup_options);

		return $special;
	}

	/**
	 * Підхопити товар під активні правила (новий товар бренду/категорії тощо).
	 */
	public function applyMatchingRulesForProduct($product_id, $price = null, $backup = null) {
		$winner = $this->findWinningRuleForProduct($product_id);

		if (!$winner) {
			$this->unlockProduct($product_id);
			return false;
		}

		if ($price === null) {
			$query = $this->db->query("SELECT price FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$product_id . "'");

			if (!$query->num_rows) {
				return false;
			}

			$price = (float)$query->row['price'];
		}

		return $this->applyRuleToProduct($product_id, $winner, $price, $backup);
	}

	/**
	 * Зняти сайт-знижку і одразу повернути попередню знижку 1С (якщо була в backup).
	 */
	public function unlockProduct($product_id) {
		$this->migrateLockBackup();
		$this->migrateLockOptionBackup();

		$lock = $this->getLock($product_id);

		if (!$lock) {
			return;
		}

		$this->restoreMinusOptionPrices($product_id, $lock);

		$product_query = $this->db->query("SELECT price FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$product_id . "'");

		if (!$product_query->num_rows) {
			$this->db->query("DELETE FROM `" . DB_PREFIX . "getvape_znyzhky_lock` WHERE product_id = '" . (int)$product_id . "'");
			return;
		}

		$price = (float)$product_query->row['price'];
		$restored = $this->restoreBackupSpecial($product_id, $price, $lock);

		$this->db->query("DELETE FROM `" . DB_PREFIX . "getvape_znyzhky_lock` WHERE product_id = '" . (int)$product_id . "'");

		if ($restored === false) {
			$this->db->query("UPDATE `" . DB_PREFIX . "product` SET points = '" . (float)$price . "' WHERE product_id = '" . (int)$product_id . "'");
			$this->syncProductMetaPrice($product_id, $price, 0);
		}
	}

	public function migrateLockBackup() {
		$table = $this->db->query("SHOW TABLES LIKE '" . str_replace('_', '\_', DB_PREFIX . 'getvape_znyzhky_lock') . "'");

		if (!$table->num_rows) {
			return;
		}

		$col = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "getvape_znyzhky_lock` LIKE 'backup_has_special'");

		if (!$col->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "getvape_znyzhky_lock`
				ADD `backup_price` DECIMAL(15,4) NOT NULL DEFAULT 0 AFTER `special_price`,
				ADD `backup_special` DECIMAL(15,4) NOT NULL DEFAULT 0 AFTER `backup_price`,
				ADD `backup_has_special` TINYINT(1) NOT NULL DEFAULT 0 AFTER `backup_special`");
		}
	}

	public function migrateLockOptionBackup() {
		$table = $this->db->query("SHOW TABLES LIKE '" . str_replace('_', '\_', DB_PREFIX . 'getvape_znyzhky_lock') . "'");

		if (!$table->num_rows) {
			return;
		}

		$col = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "getvape_znyzhky_lock` LIKE 'backup_options'");

		if (!$col->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "getvape_znyzhky_lock`
				ADD `backup_options` TEXT NULL AFTER `backup_has_special`");
		}
	}

	private function resolveBackup($product_id, $backup = null) {
		$this->migrateLockBackup();

		if (is_array($backup) && array_key_exists('backup_has_special', $backup)) {
			$has = !empty($backup['backup_has_special']) ? 1 : 0;
			$base = isset($backup['backup_price']) ? (float)$backup['backup_price'] : 0;
			$spec = isset($backup['backup_special']) ? (float)$backup['backup_special'] : 0;

			if ($has && ($base <= 0 || $spec <= 0 || $spec >= $base)) {
				$has = 0;
				$spec = 0;
			}

			return array(
				'backup_price'       => $base,
				'backup_special'     => $has ? $spec : 0,
				'backup_has_special' => $has
			);
		}

		$existing = $this->getLock($product_id);

		if ($existing && array_key_exists('backup_has_special', $existing)) {
			return array(
				'backup_price'       => (float)$existing['backup_price'],
				'backup_special'     => (float)$existing['backup_special'],
				'backup_has_special' => !empty($existing['backup_has_special']) ? 1 : 0
			);
		}

		return $this->snapshotCurrentSpecialBackup($product_id);
	}

	private function snapshotCurrentSpecialBackup($product_id) {
		$product_query = $this->db->query("SELECT price FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$product_id . "'");

		if (!$product_query->num_rows) {
			return array(
				'backup_price'       => 0,
				'backup_special'     => 0,
				'backup_has_special' => 0
			);
		}

		$price = (float)$product_query->row['price'];
		$special_query = $this->db->query("SELECT price FROM `" . DB_PREFIX . "product_special`
			WHERE product_id = '" . (int)$product_id . "'
			AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW()))
			ORDER BY priority ASC, price ASC
			LIMIT 1");

		if ($special_query->num_rows) {
			$special = (float)$special_query->row['price'];

			if ($special > 0 && $special < $price) {
				return array(
					'backup_price'       => $price,
					'backup_special'     => $special,
					'backup_has_special' => 1
				);
			}
		}

		return array(
			'backup_price'       => $price,
			'backup_special'     => 0,
			'backup_has_special' => 0
		);
	}

	private function writeLock($product_id, $rule, $special, $backup, $backup_options = null) {
		$this->migrateLockBackup();
		$this->migrateLockOptionBackup();

		if ($backup_options === null) {
			$existing = $this->getLock($product_id);
			$decoded = $existing ? $this->decodeBackupOptions($existing) : array();
			$backup_options = $decoded ? $decoded : $this->snapshotMinusOptionPrices($product_id);
		}

		$options_json = is_string($backup_options)
			? $backup_options
			: json_encode(array_values($backup_options), JSON_UNESCAPED_UNICODE);

		$this->db->query("REPLACE INTO `" . DB_PREFIX . "getvape_znyzhky_lock` SET
			product_id = '" . (int)$product_id . "',
			rule_id = '" . (int)$rule['rule_id'] . "',
			discount_type = '" . $this->db->escape($rule['discount_type']) . "',
			discount_value = '" . (float)$rule['discount_value'] . "',
			special_price = '" . (float)$special . "',
			backup_price = '" . (float)$backup['backup_price'] . "',
			backup_special = '" . (float)$backup['backup_special'] . "',
			backup_has_special = '" . (int)$backup['backup_has_special'] . "',
			backup_options = '" . $this->db->escape($options_json ? $options_json : '') . "',
			date_modified = NOW()");
	}

	/**
	 * Повні ціни option_value з prefix '-' (до масштабування під special).
	 */
	public function snapshotMinusOptionPrices($product_id) {
		$query = $this->db->query("SELECT product_option_value_id, price, points
			FROM `" . DB_PREFIX . "product_option_value`
			WHERE product_id = '" . (int)$product_id . "'
			AND price_prefix = '-'
			AND price > 0");

		$rows = array();

		foreach ($query->rows as $row) {
			$rows[] = array(
				'product_option_value_id' => (int)$row['product_option_value_id'],
				'price'                   => (float)$row['price'],
				'points'                  => (int)$row['points']
			);
		}

		return $rows;
	}

	public function decodeBackupOptions($lock) {
		if (empty($lock['backup_options'])) {
			return array();
		}

		$data = json_decode($lock['backup_options'], true);

		if (!is_array($data)) {
			return array();
		}

		$rows = array();

		foreach ($data as $row) {
			if (empty($row['product_option_value_id'])) {
				continue;
			}

			$rows[] = array(
				'product_option_value_id' => (int)$row['product_option_value_id'],
				'price'                   => isset($row['price']) ? (float)$row['price'] : 0,
				'points'                  => isset($row['points']) ? (int)$row['points'] : 0
			);
		}

		return $rows;
	}

	public function scaleOptionPrice($full_price, $base_price, $special_price) {
		$full_price = (float)$full_price;
		$base_price = (float)$base_price;
		$special_price = (float)$special_price;

		if ($base_price <= 0 || $special_price <= 0 || $special_price >= $base_price) {
			return $this->roundPrice($full_price);
		}

		return $this->roundPrice($full_price * ($special_price / $base_price));
	}

	/**
	 * Масштабувати ціни option з prefix '-' пропорційно special/base,
	 * щоб зняття бустера/гліцерину давало base*знижка, а не special - full_option.
	 */
	public function scaleMinusOptionPrices($product_id, $base_price, $special_price, $full_options = null) {
		$product_id = (int)$product_id;
		$base_price = (float)$base_price;
		$special_price = (float)$special_price;

		if ($product_id < 1 || $base_price <= 0) {
			return;
		}

		if ($full_options === null) {
			$lock = $this->getLock($product_id);
			$full_options = $lock ? $this->decodeBackupOptions($lock) : array();
		}

		if (!$full_options) {
			$full_options = $this->snapshotMinusOptionPrices($product_id);
		}

		if (!$full_options) {
			return;
		}

		foreach ($full_options as $row) {
			$full = (float)$row['price'];
			$scaled = $this->scaleOptionPrice($full, $base_price, $special_price);
			$points = (int)round($scaled * 0.05);

			$this->db->query("UPDATE `" . DB_PREFIX . "product_option_value` SET
				price = '" . (float)$scaled . "',
				points = '" . (int)$points . "',
				points_prefix = '-'
				WHERE product_option_value_id = '" . (int)$row['product_option_value_id'] . "'
				AND product_id = '" . (int)$product_id . "'
				AND price_prefix = '-'");
		}
	}

	public function restoreMinusOptionPrices($product_id, $lock) {
		$full_options = $this->decodeBackupOptions($lock);

		if (!$full_options) {
			return;
		}

		foreach ($full_options as $row) {
			$price = (float)$row['price'];
			$points = isset($row['points']) ? (int)$row['points'] : (int)round($price * 0.05);

			$this->db->query("UPDATE `" . DB_PREFIX . "product_option_value` SET
				price = '" . (float)$price . "',
				points = '" . (int)$points . "',
				points_prefix = '-'
				WHERE product_option_value_id = '" . (int)$row['product_option_value_id'] . "'
				AND product_id = '" . (int)$product_id . "'
				AND price_prefix = '-'");
		}
	}

	/**
	 * Після запису повних цін опцій (sync sol): оновити backup_options і масштабувати під special.
	 * Також для знижки 1С без site-lock.
	 */
	public function refreshMinusOptionDiscount($product_id, $base_price = null) {
		$this->migrateLockOptionBackup();

		$product_id = (int)$product_id;
		$product_query = $this->db->query("SELECT price FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$product_id . "'");

		if (!$product_query->num_rows) {
			return false;
		}

		if ($base_price === null) {
			$base_price = (float)$product_query->row['price'];
		} else {
			$base_price = (float)$base_price;
		}

		$full_options = $this->snapshotMinusOptionPrices($product_id);
		$lock = $this->getLock($product_id);

		if ($lock) {
			$special = $this->calculateSpecial($base_price, $lock['discount_type'], $lock['discount_value']);

			if ($special === false) {
				$special = (float)$lock['special_price'];
			}

			$options_json = json_encode(array_values($full_options), JSON_UNESCAPED_UNICODE);

			$this->db->query("UPDATE `" . DB_PREFIX . "getvape_znyzhky_lock` SET
				backup_options = '" . $this->db->escape($options_json ? $options_json : '') . "',
				date_modified = NOW()
				WHERE product_id = '" . (int)$product_id . "'");

			if ($special > 0 && $special < $base_price) {
				$this->scaleMinusOptionPrices($product_id, $base_price, $special, $full_options);
				return $special;
			}

			return false;
		}

		$special_query = $this->db->query("SELECT price FROM `" . DB_PREFIX . "product_special`
			WHERE product_id = '" . (int)$product_id . "'
			AND ((date_start = '0000-00-00' OR date_start < NOW()) AND (date_end = '0000-00-00' OR date_end > NOW()))
			ORDER BY priority ASC, price ASC
			LIMIT 1");

		if (!$special_query->num_rows) {
			return false;
		}

		$special = (float)$special_query->row['price'];

		if ($special <= 0 || $special >= $base_price) {
			return false;
		}

		$this->scaleMinusOptionPrices($product_id, $base_price, $special, $full_options);

		return $special;
	}

	private function restoreBackupSpecial($product_id, $price, $lock) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_special` WHERE product_id = '" . (int)$product_id . "'");

		if (empty($lock['backup_has_special'])) {
			return false;
		}

		$backup_price = (float)$lock['backup_price'];
		$backup_special = (float)$lock['backup_special'];

		if ($backup_price <= 0 || $backup_special <= 0 || $backup_special >= $backup_price) {
			return false;
		}

		$diff = $backup_price - $backup_special;
		$restore = $this->roundPrice($price - $diff);

		if ($restore <= 0 || $restore >= $price) {
			return false;
		}

		$this->writeProductSpecial($product_id, $restore, '0000-00-00', '0000-00-00');
		$this->db->query("UPDATE `" . DB_PREFIX . "product` SET points = '" . (float)$restore . "' WHERE product_id = '" . (int)$product_id . "'");
		$this->syncProductMetaPrice($product_id, $price, $restore);

		return $restore;
	}

	public function clearFrontCaches() {
		// Как при сохранении товара в админке (editProduct)
		$this->cache->delete('product*');
		$this->cache->delete('module*');

		if ($this->config->get('config_seo_pro')) {
			$this->cache->delete('seopro*');
		}

		foreach (array(1, 2, 3) as $language_id) {
			$file = DIR_LOGS . 'cache.header.special' . (int)$language_id;

			if (is_file($file)) {
				@unlink($file);
			}
		}
	}

	public function calculateSpecial($price, $discount_type, $discount_value) {
		$price = (float)$price;
		$discount_value = (float)$discount_value;

		if ($price <= 0 || $discount_value <= 0) {
			return false;
		}

		if ($discount_type == 'percent') {
			if ($discount_value >= 100) {
				return false;
			}

			// Округлення до цілих грн: <0.50 вниз, >=0.50 вгору
			$special = $this->roundPrice($price * (1 - ($discount_value / 100)));
		} else {
			// Фіксована знижка в грн (не нова ціна)
			$special = $this->roundPrice($price - $discount_value);
		}

		if ($special <= 0 || $special >= $price) {
			return false;
		}

		return $special;
	}

	public function roundPrice($amount) {
		return (float)round((float)$amount, 0, PHP_ROUND_HALF_UP);
	}

	public function syncLockedSpecialFromPrice($product_id, $price) {
		$this->migrateLockOptionBackup();

		$lock = $this->getLock($product_id);

		if (!$lock) {
			return false;
		}

		$special = $this->calculateSpecial($price, $lock['discount_type'], $lock['discount_value']);

		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_special` WHERE product_id = '" . (int)$product_id . "'");

		if ($special === false) {
			$this->restoreMinusOptionPrices($product_id, $lock);
			return 0;
		}

		$rule = array();
		$rule_query = $this->db->query("SELECT date_start, date_end FROM `" . DB_PREFIX . "getvape_znyzhky_rule` WHERE rule_id = '" . (int)$lock['rule_id'] . "'");

		if ($rule_query->num_rows) {
			$rule = $rule_query->row;
		}

		$dates = $this->resolveSpecialDates($rule);
		$this->writeProductSpecial($product_id, $special, $dates['date_start'], $dates['date_end']);

		$full_options = $this->decodeBackupOptions($lock);

		if (!$full_options) {
			$this->restoreMinusOptionPrices($product_id, $lock);
			$full_options = $this->snapshotMinusOptionPrices($product_id);
		}

		$options_json = json_encode(array_values($full_options), JSON_UNESCAPED_UNICODE);

		$this->db->query("UPDATE `" . DB_PREFIX . "getvape_znyzhky_lock` SET
			special_price = '" . (float)$special . "',
			backup_options = '" . $this->db->escape($options_json ? $options_json : '') . "',
			date_modified = NOW()
			WHERE product_id = '" . (int)$product_id . "'");

		$this->db->query("UPDATE `" . DB_PREFIX . "product` SET points = '" . (float)$special . "' WHERE product_id = '" . (int)$product_id . "'");
		$this->syncProductMetaPrice($product_id, $price, $special);
		$this->scaleMinusOptionPrices($product_id, $price, $special, $full_options);

		return $special;
	}

	public function cronExpire() {
		$today = date('Y-m-d');

		$query = $this->db->query("SELECT l.product_id
			FROM `" . DB_PREFIX . "getvape_znyzhky_lock` l
			LEFT JOIN `" . DB_PREFIX . "getvape_znyzhky_rule` r ON (l.rule_id = r.rule_id)
			WHERE r.rule_id IS NULL
				OR r.status = 0
				OR (r.date_end != '0000-00-00' AND r.date_end < '" . $this->db->escape($today) . "')");

		$count = 0;

		foreach ($query->rows as $row) {
			$this->unlockProduct((int)$row['product_id']);
			$count++;
		}

		// Re-apply other still-valid rules that may now win on freed products
		$this->reapplyAllActive();

		if ($count) {
			$this->clearFrontCaches();
		}

		return $count;
	}

	private function reapplyAllActive() {
		$today = date('Y-m-d');

		$rules_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "getvape_znyzhky_rule`
			WHERE status = '1'
			AND (date_end = '0000-00-00' OR date_end >= '" . $this->db->escape($today) . "')
			ORDER BY sort_order DESC, rule_id DESC");

		if (!$rules_query->num_rows) {
			return;
		}

		$winners = array();

		foreach ($rules_query->rows as $rule) {
			$matched = $this->getProductMatchWeights($rule);

			foreach ($matched as $product_id => $weight) {
				$candidate = $rule;
				$candidate['_match_weight'] = $weight;

				if (!isset($winners[$product_id]) || $this->isBetterRule($candidate, $winners[$product_id])) {
					$winners[$product_id] = $candidate;
				}
			}
		}

		foreach ($winners as $product_id => $rule) {
			$existing = $this->getLock($product_id);

			if ($existing && (int)$existing['rule_id'] == (int)$rule['rule_id']) {
				continue;
			}

			$product_query = $this->db->query("SELECT price FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$product_id . "'");

			if (!$product_query->num_rows) {
				continue;
			}

			$this->applyRuleToProduct($product_id, $rule, (float)$product_query->row['price']);
		}
	}

	private function resolveSpecialDates($rule) {
		$today = date('Y-m-d');
		$date_start = '0000-00-00';
		$date_end = '0000-00-00';

		if (!empty($rule['date_start']) && $rule['date_start'] != '0000-00-00' && $rule['date_start'] > $today) {
			$date_start = $rule['date_start'];
		}

		if (!empty($rule['date_end']) && $rule['date_end'] != '0000-00-00') {
			$date_end = date('Y-m-d', strtotime($rule['date_end'] . ' +1 day'));
		}

		return array(
			'date_start' => $date_start,
			'date_end'   => $date_end
		);
	}

	private function writeProductSpecial($product_id, $special, $date_start, $date_end) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_special` WHERE product_id = '" . (int)$product_id . "'");

		$groups = $this->db->query("SELECT customer_group_id FROM `" . DB_PREFIX . "customer_group`");
		$group_ids = array();

		foreach ($groups->rows as $row) {
			$group_ids[] = (int)$row['customer_group_id'];
		}

		if (!$group_ids) {
			$group_ids[] = 1;
		}

		foreach ($group_ids as $customer_group_id) {
			$this->db->query("INSERT INTO `" . DB_PREFIX . "product_special` SET
				product_id = '" . (int)$product_id . "',
				customer_group_id = '" . (int)$customer_group_id . "',
				priority = '0',
				price = '" . (float)$special . "',
				date_start = '" . $this->db->escape($date_start) . "',
				date_end = '" . $this->db->escape($date_end) . "'");
		}
	}

	private function syncProductMetaPrice($product_id, $price, $special) {
		$display = (int)round((float)($special ? $special : $price));

		$query = $this->db->query("SELECT language_id, meta_description FROM `" . DB_PREFIX . "product_description` WHERE product_id = '" . (int)$product_id . "'");

		foreach ($query->rows as $row) {
			$meta = $row['meta_description'];

			if ($meta === '' || $meta === null) {
				continue;
			}

			$updated = preg_replace('/за\s+\d+(?:[.,]\d+)?\s*₴/u', 'за ' . $display . ' ₴', $meta);

			if ($updated !== null && $updated !== $meta) {
				$this->db->query("UPDATE `" . DB_PREFIX . "product_description` SET meta_description = '" . $this->db->escape($updated) . "' WHERE product_id = '" . (int)$product_id . "' AND language_id = '" . (int)$row['language_id'] . "'");
			}
		}
	}

	private function isBetterRule($candidate, $current) {
		$cw = isset($candidate['_match_weight']) ? (int)$candidate['_match_weight'] : (isset($this->scope_weight[$candidate['scope']]) ? $this->scope_weight[$candidate['scope']] : 0);
		$nw = isset($current['_match_weight']) ? (int)$current['_match_weight'] : (isset($this->scope_weight[$current['scope']]) ? $this->scope_weight[$current['scope']] : 0);

		if ($cw != $nw) {
			return $cw > $nw;
		}

		if ((int)$candidate['sort_order'] != (int)$current['sort_order']) {
			return (int)$candidate['sort_order'] > (int)$current['sort_order'];
		}

		return (int)$candidate['rule_id'] > (int)$current['rule_id'];
	}

	private function getMatchWeightForProduct($rule, $product_id, $manufacturer_id) {
		$columns = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "getvape_znyzhky_target` LIKE 'target_type'");
		$has_type = (bool)$columns->num_rows;

		$targets = array(
			'category'     => array(),
			'manufacturer' => array(),
			'product'      => array()
		);

		if ($has_type) {
			$query = $this->db->query("SELECT target_type, target_id FROM `" . DB_PREFIX . "getvape_znyzhky_target` WHERE rule_id = '" . (int)$rule['rule_id'] . "'");

			foreach ($query->rows as $row) {
				$type = $row['target_type'];

				if (!isset($targets[$type])) {
					$type = 'product';
				}

				$targets[$type][] = (int)$row['target_id'];
			}
		} else {
			$query = $this->db->query("SELECT target_id FROM `" . DB_PREFIX . "getvape_znyzhky_target` WHERE rule_id = '" . (int)$rule['rule_id'] . "'");
			$type = !empty($rule['scope']) ? $rule['scope'] : 'product';

			if (!isset($targets[$type])) {
				$type = 'product';
			}

			foreach ($query->rows as $row) {
				$targets[$type][] = (int)$row['target_id'];
			}
		}

		$weight = 0;

		if ($targets['product'] && in_array((int)$product_id, $targets['product'], true)) {
			$weight = 3;
		}

		if ($targets['manufacturer'] && $manufacturer_id > 0 && in_array($manufacturer_id, $targets['manufacturer'], true)) {
			if ($weight < 2) {
				$weight = 2;
			}
		}

		if ($targets['category'] && $weight < 1) {
			$query = $this->db->query("SELECT p2c.product_id
				FROM `" . DB_PREFIX . "product_to_category` p2c
				LEFT JOIN `" . DB_PREFIX . "category_path` cp ON (p2c.category_id = cp.category_id)
				WHERE p2c.product_id = '" . (int)$product_id . "'
				AND cp.path_id IN (" . implode(',', array_map('intval', $targets['category'])) . ")
				LIMIT 1");

			if ($query->num_rows) {
				$weight = 1;
			}
		}

		return $weight;
	}

	private function getProductIdsForRule($rule) {
		return array_keys($this->getProductMatchWeights($rule));
	}

	private function getProductMatchWeights($rule) {
		$columns = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "getvape_znyzhky_target` LIKE 'target_type'");
		$has_type = (bool)$columns->num_rows;

		$targets = array(
			'category'     => array(),
			'manufacturer' => array(),
			'product'      => array()
		);

		if ($has_type) {
			$query = $this->db->query("SELECT target_type, target_id FROM `" . DB_PREFIX . "getvape_znyzhky_target` WHERE rule_id = '" . (int)$rule['rule_id'] . "'");

			foreach ($query->rows as $row) {
				$type = $row['target_type'];

				if (!isset($targets[$type])) {
					$type = 'product';
				}

				$targets[$type][] = (int)$row['target_id'];
			}
		} else {
			$query = $this->db->query("SELECT target_id FROM `" . DB_PREFIX . "getvape_znyzhky_target` WHERE rule_id = '" . (int)$rule['rule_id'] . "'");
			$type = !empty($rule['scope']) ? $rule['scope'] : 'product';

			if (!isset($targets[$type])) {
				$type = 'product';
			}

			foreach ($query->rows as $row) {
				$targets[$type][] = (int)$row['target_id'];
			}
		}

		$weights = array();

		if ($targets['product']) {
			foreach ($targets['product'] as $product_id) {
				$weights[(int)$product_id] = 3;
			}
		}

		if ($targets['manufacturer']) {
			$query = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "product` WHERE manufacturer_id IN (" . implode(',', array_map('intval', $targets['manufacturer'])) . ")");

			foreach ($query->rows as $row) {
				$product_id = (int)$row['product_id'];

				if (!isset($weights[$product_id]) || $weights[$product_id] < 2) {
					$weights[$product_id] = 2;
				}
			}
		}

		if ($targets['category']) {
			$query = $this->db->query("SELECT DISTINCT p.product_id
				FROM `" . DB_PREFIX . "category_path` cp
				LEFT JOIN `" . DB_PREFIX . "product_to_category` p2c ON (cp.category_id = p2c.category_id)
				LEFT JOIN `" . DB_PREFIX . "product` p ON (p2c.product_id = p.product_id)
				WHERE cp.path_id IN (" . implode(',', array_map('intval', $targets['category'])) . ")
				AND p.product_id IS NOT NULL");

			foreach ($query->rows as $row) {
				$product_id = (int)$row['product_id'];

				if (!isset($weights[$product_id]) || $weights[$product_id] < 1) {
					$weights[$product_id] = 1;
				}
			}
		}

		return $weights;
	}
}
