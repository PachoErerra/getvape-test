<?php
function token($length = 32) {
	// Create random token
	$string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
	
	$max = strlen($string) - 1;
	
	$token = '';
	
	for ($i = 0; $i < $length; $i++) {
		$token .= $string[mt_rand(0, $max)];
	}	
	
	return $token;
}

/**
 * Backwards support for timing safe hash string comparisons
 * 
 * http://php.net/manual/en/function.hash-equals.php
 */

function is_crawler_bot($user_agent = null) {
	if ($user_agent === null) {
		$user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
	}

	$user_agent = strtolower($user_agent);

	$bots = array(
		'googlebot',
		'google-extended',
		'chrome-lighthouse',
		'headlesschrome',
		'gptbot',
		'chatgpt-user',
		'oai-searchbot',
		'claudebot',
		'claude-user',
		'claude-searchbot',
		'perplexitybot',
		'bytespider',
		'meta-externalagent',
	);

	foreach ($bots as $bot) {
		if (strpos($user_agent, $bot) !== false) {
			return true;
		}
	}

	return false;
}

function get_primary_category_ids() {
	return array(1271, 1122, 3013);
}

/** Сольові / органічні рідини: у фільтрі і характеристиках "Модель" -> "Лінійка" */
function get_liquid_line_category_ids() {
	return array(3013, 2810);
}

function is_liquid_line_category($category_ids) {
	$category_ids = array_map('intval', (array)$category_ids);

	return (bool)array_intersect(get_liquid_line_category_ids(), $category_ids);
}

function get_liquid_line_label($language = '') {
	if (strpos((string)$language, 'ru') === 0) {
		return 'Линейка';
	}

	return 'Лінійка';
}

function rename_model_to_line_label($name, $language = '') {
	if ($name !== 'Модель') {
		return $name;
	}

	return get_liquid_line_label($language);
}

function normalize_menu_href($href) {
	$href = html_entity_decode((string)$href, ENT_QUOTES, 'UTF-8');
	$path = parse_url($href, PHP_URL_PATH);

	if ($path) {
		return rtrim($path, '/');
	}

	return rtrim($href, '/');
}

/**
 * Goods amount after coupon, bonuses and personal discount.
 * Shipping and store credit are not included, so they cannot
 * push an order over the free-delivery threshold.
 */
function get_cart_amount_without_shipping($registry) {
	static $running = false;

	if ($running) {
		return (float)$registry->get('cart')->getSubTotal();
	}

	$running = true;

	try {
		$total = 0;
		$totals = array();
		$taxes = $registry->get('cart')->getTaxes();
		$total_data = array(
			'totals' => &$totals,
			'taxes'  => &$taxes,
			'total'  => &$total
		);

		$config = $registry->get('config');
		$load = $registry->get('load');

		if (version_compare(VERSION, '3', '>=')) {
			$load->model('setting/extension');
			$extensions = $registry->get('model_setting_extension')->getExtensions('total');
		} else {
			$load->model('extension/extension');
			$extensions = $registry->get('model_extension_extension')->getExtensions('total');
		}

		$rows = array();
		$index = 0;

		foreach ($extensions as $extension) {
			$code = $extension['code'];

			if (version_compare(VERSION, '3', '>=')) {
				$status = $config->get('total_' . $code . '_status');
				$sort = (int)$config->get('total_' . $code . '_sort_order');
			} else {
				$status = $config->get($code . '_status');
				$sort = (int)$config->get($code . '_sort_order');
			}

			if ($status) {
				$rows[] = array(
					'sort' => $sort,
					'index' => $index++,
					'code' => $code
				);
			}
		}

		usort($rows, function ($a, $b) {
			if ($a['sort'] === $b['sort']) {
				return $a['index'] - $b['index'];
			}

			return $a['sort'] - $b['sort'];
		});

		foreach ($rows as $row) {
			if ($row['code'] === 'shipping' || $row['code'] === 'credit') {
				continue;
			}

			if (version_compare(VERSION, '2.3', '>=')) {
				$load->model('extension/total/' . $row['code']);
				$registry->get('model_extension_total_' . $row['code'])->getTotal($total_data);
			} elseif (version_compare(VERSION, '2.2', '>=')) {
				$load->model('total/' . $row['code']);
				$registry->get('model_total_' . $row['code'])->getTotal($total_data);
			} else {
				$load->model('total/' . $row['code']);
				$registry->get('model_total_' . $row['code'])->getTotal($totals, $total, $taxes);
			}
		}

		return (float)$total;
	} finally {
		$running = false;
	}
}

function enrich_menu_categories(array &$categories, $url, $category_model) {
	static $href_map = null;

	if (!$categories) {
		return;
	}

	if ($href_map === null) {
		$href_map = array();

		foreach ($category_model->getCategories(0) as $category) {
			if (empty($category['top'])) {
				continue;
			}

			$href_map[normalize_menu_href($url->link('product/category', 'path=' . $category['category_id']))] = (int)$category['category_id'];
		}
	}

	$primary_ids = get_primary_category_ids();

	foreach ($categories as &$category) {
		if (!empty($category['category_id'])) {
			$category['category_id'] = (int)$category['category_id'];
		} elseif (!empty($category['href'])) {
			$href = normalize_menu_href($category['href']);

			if (isset($href_map[$href])) {
				$category['category_id'] = $href_map[$href];
			} elseif (preg_match('/path=(\d+)/', $category['href'], $matches)) {
				$category['category_id'] = (int)$matches[1];
			}
		}

		$category['is_primary'] = !empty($category['category_id']) && in_array((int)$category['category_id'], $primary_ids, true);
	}

	unset($category);
}

if(!function_exists('hash_equals')) {
	function hash_equals($known_string, $user_string) {
		$known_string = (string)$known_string;
		$user_string = (string)$user_string;

		if(strlen($known_string) != strlen($user_string)) {
			return false;
		} else {
			$res = $known_string ^ $user_string;
			$ret = 0;

			for($i = strlen($res) - 1; $i >= 0; $i--) $ret |= ord($res[$i]);

			return !$ret;
		}
	}
}