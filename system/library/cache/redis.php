<?php
namespace Cache;
class Redis {
	private $expire;
	private $cache;

	public function __construct($expire) {
		$this->expire = $expire;

		$this->cache = new \Redis();
		$this->cache->pconnect(CACHE_HOSTNAME, CACHE_PORT);
	}

	public function get($key) {
		$data = $this->cache->get(CACHE_PREFIX . $key);

		return json_decode($data, true);
	}

	public function set($key, $value) {
		$status = $this->cache->set(CACHE_PREFIX . $key, json_encode($value));

		if ($status) {
			$this->cache->expire(CACHE_PREFIX . $key, $this->expire);
		}

		return $status;
	}
/*
	public function delete($key) {
		$this->cache->del(CACHE_PREFIX . $key);
	}

	public function delete($key) {
		// Подготовка префикса и шаблона
		$prefix = defined('CACHE_PREFIX') ? CACHE_PREFIX : '';
		$pattern = $key;

		// Если ключ не содержит wildcards, предполагаем, что хотим удалить все начинающиеся с $key
		if (strpos($key, '*') === false && strpos($key, '?') === false) {
			$pattern = $key . '*';
		}

		$pattern = $prefix . $pattern;

		// Попытка SCAN (без блокировок)
		$deleted = 0;
		try {
			if (method_exists($this->cache, 'scan')) {
				$it = null;
				while ($keys = $this->cache->scan($it, $pattern)) {
					if (!empty($keys)) {
						// $this->cache->del принимает массив в phpredis
						$deleted += $this->cache->del($keys);
					}
					error_log("scan");
				}
				
			}

			// Если ничего не найдено через SCAN — fallback на KEYS (медленно, но надёжно)
			if ($deleted === 0) {
				$keys = $this->cache->keys($pattern);
				if (!empty($keys)) {
					$deleted += $this->cache->del($keys);
				}
				error_log("не scan");
			}
		} catch (\Throwable $e) {
			error_log('Redis cache delete error: ' . $e->getMessage());
			return false;
		}

		error_log("Redis cache delete pattern [$pattern] removed {$deleted} keys");
		return $deleted;
	}
*/		
	public function delete($key) {
		$deleted = 0;
		// Если ключ содержит символы подстановки, используем SCAN
		if (strpos($key, '*') !== false || strpos($key, '?') !== false) {

			$keys = $this->cache->keys(CACHE_PREFIX . $key);
			if (!empty($keys)) {
				$deleted += $this->cache->del($keys);
			}
			//error_log("Redis cache delete pattern [$key] removed {$deleted} keys");
			return $deleted;
		}

		// Обычное удаление
// 		error_log("Redis cache delete pattern [$key]");
		return $this->cache->del(CACHE_PREFIX . $key);
	}
}