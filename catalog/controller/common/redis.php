<?php
class ControllerCommonRedis extends Controller {
    public function index() {
        header('Content-Type: text/plain; charset=utf-8');

        try {
            $redis = new Redis();

            // Путь к сокету Redis на Ukraine.com.ua
            $sock = '/home/if506393/.system/redis.sock';

            $redis->connect($sock);

            echo "✅ Подключено к Redis через сокет: $sock\n";

            $redis->set('oc_test_key', 'Работает!');
            echo "Set OK\n";

            $val = $redis->get('oc_test_key');
            echo "Value: $val\n";

            $redis->del('oc_test_key');
            echo "Key deleted\n";


$info = $redis->info();

// Разбираем строку "keys=...,expires=..."
$keys = 0;
if (isset($info['db0'])) {
    if (preg_match('/keys=(\d+)/', $info['db0'], $match)) {
        $keys = (int)$match[1];
    }
}

echo "Использовано памяти: " . $info['used_memory_human'] . "\n";
echo "Ключей в Redis: " . $keys . "\n";

$keys = $redis->keys('oc_product.id*');
echo 'Всего ключей: oc_product.id - ' . count($keys) . "\n";

$keys = $redis->keys('oc_product.v*');
echo 'Всего ключей: oc_product.v - ' . count($keys) . "\n";

$keys = $redis->keys('sess_*');
echo 'Всего ключей: sess_ - ' . count($keys) . "\n";

$keys = $redis->keys('oc_seo*');
echo 'Всего ключей: oc_seo - ' . count($keys) . "\n";

$keys = $redis->keys('oc_ocfilter*');
echo 'Всего ключей: oc_ocfilter - ' . count($keys) . "\n";
//удаление кэша товаров

/*
$keys = $redis->keys('oc_seo*');
if ($keys) {
    $redis->del($keys);
}
*/
$keys = $redis->keys('oc_seo*');
print_r($keys);
          echo "Найдено сессий: " . count($keys) . "\n\n";

            foreach ($keys as $key) {
                echo "=== KEY: $key ===\n";

                $ttl = $redis->ttl($key);
                $raw = $redis->get($key);
                $len = ($raw === false) ? 0 : strlen($raw);

                echo "TTL (сек): " . $ttl . "\n";
                echo "Размер (байт): " . $len . "\n\n";

                // Вывод сырого значения (обрезаем если очень длинное)
                $preview = $raw;
                if (strlen($raw) > 1000) {
                    $preview = substr($raw, 0, 1000) . '... (truncated)';
                }
                echo "Raw value preview:\n" . $preview . "\n\n";





                echo "\n";
            }
		
			
        } catch (Exception $e) {
            echo "❌ Ошибка Redis: " . $e->getMessage();
        }
    }
}