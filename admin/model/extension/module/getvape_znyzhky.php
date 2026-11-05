<?php
/**
 * GetVape Знижки сайту
 * Author: Not The Same (https://ntsame.agency/)
 * Contact: hoffmanxyz@gmail.com
 */
class ModelExtensionModuleGetvapeZnyzhky extends Model {
	private $scope_weight = array(
		'product'      => 3,
		'manufacturer' => 2,
		'category'     => 1
	);

	public function install() {
		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "getvape_znyzhky_rule` (
			`rule_id` INT(11) NOT NULL AUTO_INCREMENT,
			`name` VARCHAR(255) NOT NULL,
			`status` TINYINT(1) NOT NULL DEFAULT 1,
			`scope` ENUM('category','manufacturer','product','mixed') NOT NULL DEFAULT 'product',
			`discount_type` ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
			`discount_value` DECIMAL(15,4) NOT NULL DEFAULT 0,
			`date_start` DATE NOT NULL DEFAULT '0000-00-00',
			`date_end` DATE NOT NULL DEFAULT '0000-00-00',
			`sort_order` INT(11) NOT NULL DEFAULT 0,
			`date_added` DATETIME NOT NULL,
			`date_modified` DATETIME NOT NULL,
			PRIMARY KEY (`rule_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "getvape_znyzhky_target` (
			`rule_id` INT(11) NOT NULL,
			`target_type` VARCHAR(32) NOT NULL DEFAULT 'product',
			`target_id` INT(11) NOT NULL,
			PRIMARY KEY (`rule_id`, `target_type`, `target_id`),
			KEY `target_id` (`target_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");

		$this->db->query("CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "getvape_znyzhky_lock` (
			`product_id` INT(11) NOT NULL,
			`rule_id` INT(11) NOT NULL,
			`discount_type` ENUM('percent','fixed') NOT NULL DEFAULT 'percent',
			`discount_value` DECIMAL(15,4) NOT NULL DEFAULT 0,
			`special_price` DECIMAL(15,4) NOT NULL DEFAULT 0,
			`backup_price` DECIMAL(15,4) NOT NULL DEFAULT 0,
			`backup_special` DECIMAL(15,4) NOT NULL DEFAULT 0,
			`backup_has_special` TINYINT(1) NOT NULL DEFAULT 0,
			`date_modified` DATETIME NOT NULL,
			PRIMARY KEY (`product_id`),
			KEY `rule_id` (`rule_id`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci");

		$this->migrateTargetType();
		$this->migrateLockBackup();
	}

	public function migrateTargetType() {
		$columns = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "getvape_znyzhky_target` LIKE 'target_type'");

		if (!$columns->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "getvape_znyzhky_target` ADD `target_type` VARCHAR(32) NOT NULL DEFAULT 'product' AFTER `rule_id`");
			$this->db->query("UPDATE `" . DB_PREFIX . "getvape_znyzhky_target` t
				INNER JOIN `" . DB_PREFIX . "getvape_znyzhky_rule` r ON (t.rule_id = r.rule_id)
				SET t.target_type = r.scope");
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "getvape_znyzhky_target` DROP PRIMARY KEY, ADD PRIMARY KEY (`rule_id`, `target_type`, `target_id`)");
		}

		$scope_col = $this->db->query("SHOW COLUMNS FROM `" . DB_PREFIX . "getvape_znyzhky_rule` WHERE Field = 'scope' AND Type LIKE '%mixed%'");

		if (!$scope_col->num_rows) {
			$this->db->query("ALTER TABLE `" . DB_PREFIX . "getvape_znyzhky_rule` MODIFY `scope` ENUM('category','manufacturer','product','mixed') NOT NULL DEFAULT 'product'");
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

	public function uninstall() {
		$this->migrateLockBackup();

		$locks = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "getvape_znyzhky_lock`");

		foreach ($locks->rows as $lock) {
			$this->unlockProduct((int)$lock['product_id']);
		}

		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "getvape_znyzhky_lock`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "getvape_znyzhky_target`");
		$this->db->query("DROP TABLE IF EXISTS `" . DB_PREFIX . "getvape_znyzhky_rule`");
	}

	public function addRule($data) {
		$this->migrateTargetType();

		$scope = $this->detectScope($data);

		$this->db->query("INSERT INTO `" . DB_PREFIX . "getvape_znyzhky_rule` SET
			`name` = '" . $this->db->escape($data['name']) . "',
			`status` = '" . (int)$data['status'] . "',
			`scope` = '" . $this->db->escape($scope) . "',
			`discount_type` = '" . $this->db->escape($data['discount_type']) . "',
			`discount_value` = '" . (float)$data['discount_value'] . "',
			`date_start` = '" . $this->db->escape($data['date_start']) . "',
			`date_end` = '" . $this->db->escape($data['date_end']) . "',
			`sort_order` = '" . (int)$data['sort_order'] . "',
			`date_added` = NOW(),
			`date_modified` = NOW()");

		$rule_id = $this->db->getLastId();
		$this->setTargets($rule_id, $data);
		$this->applyAffectedByRule($rule_id);

		return $rule_id;
	}

	public function editRule($rule_id, $data) {
		$this->migrateTargetType();

		$old_product_ids = $this->getProductIdsForRule($rule_id);
		$scope = $this->detectScope($data);

		$this->db->query("UPDATE `" . DB_PREFIX . "getvape_znyzhky_rule` SET
			`name` = '" . $this->db->escape($data['name']) . "',
			`status` = '" . (int)$data['status'] . "',
			`scope` = '" . $this->db->escape($scope) . "',
			`discount_type` = '" . $this->db->escape($data['discount_type']) . "',
			`discount_value` = '" . (float)$data['discount_value'] . "',
			`date_start` = '" . $this->db->escape($data['date_start']) . "',
			`date_end` = '" . $this->db->escape($data['date_end']) . "',
			`sort_order` = '" . (int)$data['sort_order'] . "',
			`date_modified` = NOW()
			WHERE `rule_id` = '" . (int)$rule_id . "'");

		$this->setTargets($rule_id, $data);

		$new_product_ids = $this->getProductIdsForRule($rule_id);
		$product_ids = array_unique(array_merge($old_product_ids, $new_product_ids, $this->getLockedProductIdsByRule($rule_id)));

		$this->recalculateProducts($product_ids);
	}

	public function deleteRule($rule_id) {
		$product_ids = array_unique(array_merge(
			$this->getProductIdsForRule($rule_id),
			$this->getLockedProductIdsByRule($rule_id)
		));

		$this->db->query("DELETE FROM `" . DB_PREFIX . "getvape_znyzhky_target` WHERE `rule_id` = '" . (int)$rule_id . "'");
		$this->db->query("DELETE FROM `" . DB_PREFIX . "getvape_znyzhky_rule` WHERE `rule_id` = '" . (int)$rule_id . "'");

		$this->recalculateProducts($product_ids);
	}

	public function getRule($rule_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "getvape_znyzhky_rule` WHERE `rule_id` = '" . (int)$rule_id . "'");

		return $query->row;
	}

	public function getTargets($rule_id) {
		return $this->getTargetsGrouped($rule_id);
	}

	public function getTargetsGrouped($rule_id) {
		$this->migrateTargetType();

		$query = $this->db->query("SELECT `target_type`, `target_id` FROM `" . DB_PREFIX . "getvape_znyzhky_target` WHERE `rule_id` = '" . (int)$rule_id . "'");

		$targets = array(
			'category'     => array(),
			'manufacturer' => array(),
			'product'      => array()
		);

		foreach ($query->rows as $row) {
			$type = $row['target_type'];

			if (!isset($targets[$type])) {
				$type = 'product';
			}

			$targets[$type][] = (int)$row['target_id'];
		}

		return $targets;
	}

	public function getScopeLabels($rule_id) {
		$targets = $this->getTargetsGrouped($rule_id);
		$labels = array();

		if ($targets['category']) {
			$labels[] = 'category';
		}

		if ($targets['manufacturer']) {
			$labels[] = 'manufacturer';
		}

		if ($targets['product']) {
			$labels[] = 'product';
		}

		return $labels;
	}

	public function getRules($data = array()) {
		$this->migrateTargetType();
		$this->migrateLockBackup();

		$sql = "SELECT r.*, (SELECT COUNT(*) FROM `" . DB_PREFIX . "getvape_znyzhky_lock` l WHERE l.rule_id = r.rule_id) AS locked_count
			FROM `" . DB_PREFIX . "getvape_znyzhky_rule` r";

		$sort_data = array('r.name', 'r.scope', 'r.status', 'r.sort_order', 'r.date_start', 'r.date_end', 'r.date_modified');

		if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
			$sql .= " ORDER BY " . $data['sort'];
		} else {
			$sql .= " ORDER BY r.date_modified";
		}

		if (isset($data['order']) && ($data['order'] == 'ASC')) {
			$sql .= " ASC";
		} else {
			$sql .= " DESC";
		}

		if (isset($data['start']) || isset($data['limit'])) {
			$start = isset($data['start']) ? (int)$data['start'] : 0;
			$limit = isset($data['limit']) ? (int)$data['limit'] : 20;

			if ($start < 0) {
				$start = 0;
			}

			if ($limit < 1) {
				$limit = 20;
			}

			$sql .= " LIMIT " . $start . "," . $limit;
		}

		$query = $this->db->query($sql);

		return $query->rows;
	}

	public function getTotalRules() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "getvape_znyzhky_rule`");

		return (int)$query->row['total'];
	}

	public function getLock($product_id) {
		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "getvape_znyzhky_lock` WHERE `product_id` = '" . (int)$product_id . "'");

		return $query->row;
	}

	public function applyAll() {
		$this->expireLocks();

		$product_ids = array();

		foreach ($this->getApplicableRules() as $rule) {
			$product_ids = array_merge($product_ids, $this->getProductIdsForRuleData($rule));
		}

		foreach ($this->getAllLockedProductIds() as $product_id) {
			$product_ids[] = $product_id;
		}

		$this->recalculateProducts(array_unique($product_ids));
	}

	public function expireLocks() {
		$today = date('Y-m-d');

		$query = $this->db->query("SELECT l.product_id, l.rule_id
			FROM `" . DB_PREFIX . "getvape_znyzhky_lock` l
			LEFT JOIN `" . DB_PREFIX . "getvape_znyzhky_rule` r ON (l.rule_id = r.rule_id)
			WHERE r.rule_id IS NULL
				OR r.status = 0
				OR (r.date_end != '0000-00-00' AND r.date_end < '" . $this->db->escape($today) . "')");

		$product_ids = array();

		foreach ($query->rows as $row) {
			$product_ids[] = (int)$row['product_id'];
		}

		if ($product_ids) {
			$this->recalculateProducts(array_unique($product_ids));
		}

		return count($product_ids);
	}

	public function applyAffectedByRule($rule_id) {
		$product_ids = array_unique(array_merge(
			$this->getProductIdsForRule($rule_id),
			$this->getLockedProductIdsByRule($rule_id)
		));

		$this->recalculateProducts($product_ids);
	}

	public function recalculateProducts($product_ids) {
		$product_ids = array_values(array_unique(array_map('intval', $product_ids)));

		if (!$product_ids) {
			return;
		}

		$winners = $this->resolveWinners($product_ids);

		foreach ($product_ids as $product_id) {
			if (isset($winners[$product_id])) {
				$this->applyWinner($product_id, $winners[$product_id]);
			} else {
				$this->unlockProduct($product_id);
			}
		}

		$this->clearFrontCaches();
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
			$this->db->query("DELETE FROM `" . DB_PREFIX . "getvape_znyzhky_lock` WHERE `product_id` = '" . (int)$product_id . "'");
			return;
		}

		$price = (float)$product_query->row['price'];
		$restored = $this->restoreBackupSpecial($product_id, $price, $lock);

		$this->db->query("DELETE FROM `" . DB_PREFIX . "getvape_znyzhky_lock` WHERE `product_id` = '" . (int)$product_id . "'");

		if ($restored === false) {
			$this->db->query("UPDATE `" . DB_PREFIX . "product` SET points = '" . (float)$price . "' WHERE product_id = '" . (int)$product_id . "'");
			$this->syncProductMetaPrice($product_id, $price, 0);
		}
	}

	private function resolveWinners($product_ids) {
		$winners = array();
		$rules = $this->getApplicableRules();

		if (!$rules || !$product_ids) {
			return $winners;
		}

		$product_map = array_fill_keys($product_ids, true);

		foreach ($rules as $rule) {
			$matched = $this->getProductMatchWeights($rule);

			foreach ($matched as $product_id => $weight) {
				if (!isset($product_map[$product_id])) {
					continue;
				}

				$candidate = $rule;
				$candidate['_match_weight'] = $weight;

				if (!isset($winners[$product_id]) || $this->isBetterRule($candidate, $winners[$product_id])) {
					$winners[$product_id] = $candidate;
				}
			}
		}

		return $winners;
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

	private function applyWinner($product_id, $rule, $backup = null) {
		$this->migrateLockOptionBackup();

		$product_query = $this->db->query("SELECT price FROM `" . DB_PREFIX . "product` WHERE product_id = '" . (int)$product_id . "'");

		if (!$product_query->num_rows) {
			return;
		}

		$price = (float)$product_query->row['price'];
		$special = $this->calculateSpecial($price, $rule['discount_type'], $rule['discount_value']);

		if ($special === false) {
			$this->unlockProduct($product_id);
			return;
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

		// Як у 1С: points = актуальна ціна зі знижкою
		$this->db->query("UPDATE `" . DB_PREFIX . "product` SET points = '" . (float)$special . "' WHERE product_id = '" . (int)$product_id . "'");
		$this->syncProductMetaPrice($product_id, $price, $special);
		$this->scaleMinusOptionPrices($product_id, $price, $special, $backup_options);
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
				'backup_price'        => $base,
				'backup_special'      => $has ? $spec : 0,
				'backup_has_special'  => $has
			);
		}

		$existing = $this->getLock($product_id);

		if ($existing && array_key_exists('backup_has_special', $existing)) {
			return array(
				'backup_price'        => (float)$existing['backup_price'],
				'backup_special'      => (float)$existing['backup_special'],
				'backup_has_special'  => !empty($existing['backup_has_special']) ? 1 : 0
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
		$this->clearProductSpecial($product_id);

		if (empty($lock['backup_has_special'])) {
			return false;
		}

		$backup_price = (float)$lock['backup_price'];
		$backup_special = (float)$lock['backup_special'];

		if ($backup_price <= 0 || $backup_special <= 0 || $backup_special >= $backup_price) {
			return false;
		}

		// Зберігаємо різницю 1С (price - special), щоб коректно працювало з бустерами sol
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

		if ($special === false) {
			$this->clearProductSpecial($product_id);
			$this->restoreMinusOptionPrices($product_id, $lock);

			return 0;
		}

		$rule = $this->getRule($lock['rule_id']);
		$dates = $this->resolveSpecialDates($rule ? $rule : array());

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

		$this->scaleMinusOptionPrices($product_id, $price, $special, $full_options);

		return $special;
	}

	/**
	 * OpenCart: date_start < NOW() і date_end > NOW().
	 * Тому вже початі правила пишемо з 0000-00-00, а кінець +1 день (щоб "по" була включно).
	 */
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
		$this->clearProductSpecial($product_id);

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

	private function clearFrontCaches() {
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

	private function clearProductSpecial($product_id) {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "product_special` WHERE product_id = '" . (int)$product_id . "'");
	}

	private function setTargets($rule_id, $data) {
		$this->migrateTargetType();
		$this->db->query("DELETE FROM `" . DB_PREFIX . "getvape_znyzhky_target` WHERE `rule_id` = '" . (int)$rule_id . "'");

		$groups = array(
			'category'     => isset($data['target_category']) ? $data['target_category'] : array(),
			'manufacturer' => isset($data['target_manufacturer']) ? $data['target_manufacturer'] : array(),
			'product'      => isset($data['target_product']) ? $data['target_product'] : array()
		);

		foreach ($groups as $type => $targets) {
			if (!is_array($targets)) {
				continue;
			}

			$seen = array();

			foreach ($targets as $target_id) {
				$target_id = (int)$target_id;

				if ($target_id < 1 || isset($seen[$target_id])) {
					continue;
				}

				$seen[$target_id] = true;

				$this->db->query("INSERT INTO `" . DB_PREFIX . "getvape_znyzhky_target` SET
					`rule_id` = '" . (int)$rule_id . "',
					`target_type` = '" . $this->db->escape($type) . "',
					`target_id` = '" . (int)$target_id . "'");
			}
		}
	}

	private function detectScope($data) {
		$has_category = !empty($data['target_category']) && is_array($data['target_category']);
		$has_manufacturer = !empty($data['target_manufacturer']) && is_array($data['target_manufacturer']);
		$has_product = !empty($data['target_product']) && is_array($data['target_product']);
		$count = (int)$has_category + (int)$has_manufacturer + (int)$has_product;

		if ($count > 1) {
			return 'mixed';
		}

		if ($has_product) {
			return 'product';
		}

		if ($has_manufacturer) {
			return 'manufacturer';
		}

		if ($has_category) {
			return 'category';
		}

		return 'product';
	}

	private function getApplicableRules() {
		$today = date('Y-m-d');

		$query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "getvape_znyzhky_rule`
			WHERE `status` = '1'
			AND (`date_end` = '0000-00-00' OR `date_end` >= '" . $this->db->escape($today) . "')
			ORDER BY `sort_order` DESC, `rule_id` DESC");

		return $query->rows;
	}

	private function getProductIdsForRule($rule_id) {
		$rule = $this->getRule($rule_id);

		if (!$rule) {
			return array();
		}

		return array_keys($this->getProductMatchWeights($rule));
	}

	private function getProductIdsForRuleData($rule) {
		return array_keys($this->getProductMatchWeights($rule));
	}

	private function getProductMatchWeights($rule) {
		$targets = $this->getTargetsGrouped($rule['rule_id']);
		$weights = array();

		if ($targets['product']) {
			foreach ($targets['product'] as $product_id) {
				$weights[(int)$product_id] = 3;
			}
		}

		if ($targets['manufacturer']) {
			$query = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "product`
				WHERE manufacturer_id IN (" . implode(',', array_map('intval', $targets['manufacturer'])) . ")");

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

	private function getLockedProductIdsByRule($rule_id) {
		$query = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "getvape_znyzhky_lock` WHERE rule_id = '" . (int)$rule_id . "'");

		$ids = array();

		foreach ($query->rows as $row) {
			$ids[] = (int)$row['product_id'];
		}

		return $ids;
	}

	private function getAllLockedProductIds() {
		$query = $this->db->query("SELECT product_id FROM `" . DB_PREFIX . "getvape_znyzhky_lock`");

		$ids = array();

		foreach ($query->rows as $row) {
			$ids[] = (int)$row['product_id'];
		}

		return $ids;
	}
}
