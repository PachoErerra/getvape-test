<?php

/**
 * Клиент к 1С (розница) — бонусный баланс и скидка покупателя по телефону.
 *
 * Куда шлём:
 *   POST http://37.27.98.208/site_retail/hs/api/0/sitebuyers/?auth_token=...
 *   POST http://vaporstlgrm.biz.ua/site_retail/hs/api/0/sitebuyers/?auth_token=...
 *   Host 37.27.98.208 = внутренний HTTP-сервис 1С (site_retail / HS API).
 *
 * Запрос:
 *   Content-Type: application/json
 *   {"telephone":"380501234567"}   // без пробелов, скобок, дефисов
 *
 * Ответ (ожидаем HTTP 200):
 *   {"success":true,"balance":"150","discount":10}
 *   balance  — бонусы ₴ (плейсхолдер [bonus] в Telegram)
 *   discount — персональная скидка %
 *
 * Зачем:
 *   Подставить баланс/скидку в сообщения бота и рассылок.
 *   НЕ участвует в создании заказа, оплате и редиректе на «спасибо».
 *
 * Кто вызывает:
 *   - event/order_status  (TG при смене статуса, только если в шаблоне есть [bonus])
 *   - telegram/webhook    (кнопка «баланс» в боте)
 *   - telegram/telegram   (cron «после заказа»)
 *
 * Защита сайта:
 *   connect 2с / total 3с; 2 фейла подряд → 2 мин не дергаем 1С (circuit);
 *   успешный ответ кэшируется 60с на телефон.
 */
class Balance_Api
{
    // 1С HTTP-сервис site_retail → метод sitebuyers
//    const URL = 'http://37.27.98.208/site_retail/hs/api/0/sitebuyers/?auth_token=3698024';
    const URL = 'http://vaporstlgrm.biz.ua/site_retail/hs/api/0/sitebuyers/?auth_token=3698024';

    const CONNECT_TIMEOUT = 2;
    const TIMEOUT = 3;
    const CIRCUIT_OPEN_SECONDS = 120;
    const CIRCUIT_FAIL_THRESHOLD = 2;
    const RESPONSE_CACHE_SECONDS = 60;

    /**
     * Спросить у 1С баланс/скидку по телефону.
     *
     * @param string $telephone
     * @return array|false  массив с balance/discount или false (таймаут / circuit / ошибка)
     */
    public function getBalanceInfoByPhone($telephone)
    {
        $telephone = str_replace(['(', ')', '-', ' '], '', (string)$telephone);

        if ($telephone === '') {
            return false;
        }

        if ($this->isCircuitOpen()) {
            return false;
        }

        $cached = $this->getCachedResponse($telephone);

        if ($cached !== null) {
            return $cached;
        }

        $started = microtime(true);
        $body = json_encode(['telephone' => $telephone]);

        try {
            // POST на 1С: /site_retail/hs/api/0/sitebuyers/
            $curl = curl_init(self::URL);

            curl_setopt_array($curl, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $body,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER => false,
                CURLOPT_CONNECTTIMEOUT => self::CONNECT_TIMEOUT,
                CURLOPT_TIMEOUT => self::TIMEOUT,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Content-Type: application/json; charset=UTF-8',
                ],
            ]);

            $response = curl_exec($curl);
            $errno = curl_errno($curl);
            $httpCode = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            $elapsedMs = (int)round((microtime(true) - $started) * 1000);

            if ($errno || $httpCode !== 200 || $response === false || $response === '') {
                $this->registerFailure('errno=' . $errno . ' http=' . $httpCode . ' ms=' . $elapsedMs);
                return false;
            }

            $data = json_decode($response, true);

            if (!is_array($data)) {
                $this->registerFailure('invalid_json ms=' . $elapsedMs);
                return false;
            }

            $this->registerSuccess();
            $this->setCachedResponse($telephone, $data);

            return $data;
        } catch (\Throwable $e) {
            $this->registerFailure('exception=' . $e->getMessage());
            return false;
        }
    }

    /** Быстрая проверка: можно ли сейчас звать 1С (circuit не открыт). */
    public function isAvailable()
    {
        return !$this->isCircuitOpen();
    }

    private function circuitFile()
    {
        return DIR_CACHE . 'balance_api_circuit.json';
    }

    private function responseCacheFile($telephone)
    {
        return DIR_CACHE . 'balance_api_' . md5($telephone) . '.json';
    }

    private function readJsonFile($path)
    {
        if (!is_file($path)) {
            return null;
        }

        $raw = @file_get_contents($path);

        if ($raw === false || $raw === '') {
            return null;
        }

        $data = json_decode($raw, true);

        return is_array($data) ? $data : null;
    }

    private function writeJsonFile($path, array $data)
    {
        @file_put_contents($path, json_encode($data), LOCK_EX);
    }

    private function isCircuitOpen()
    {
        $state = $this->readJsonFile($this->circuitFile());

        if (!$state || empty($state['open_until'])) {
            return false;
        }

        if ((int)$state['open_until'] > time()) {
            return true;
        }

        // окно истекло — сбрасываем
        @unlink($this->circuitFile());

        return false;
    }

    private function registerFailure($reason = '')
    {
        $state = $this->readJsonFile($this->circuitFile()) ?: [
            'fails' => 0,
            'open_until' => 0,
        ];

        $state['fails'] = (int)$state['fails'] + 1;
        $state['last_error'] = $reason;
        $state['last_fail_at'] = time();

        if ($state['fails'] >= self::CIRCUIT_FAIL_THRESHOLD) {
            $state['open_until'] = time() + self::CIRCUIT_OPEN_SECONDS;
            $state['fails'] = 0;
        }

        $this->writeJsonFile($this->circuitFile(), $state);
    }

    private function registerSuccess()
    {
        $path = $this->circuitFile();

        if (is_file($path)) {
            @unlink($path);
        }
    }

    private function getCachedResponse($telephone)
    {
        $data = $this->readJsonFile($this->responseCacheFile($telephone));

        if (!$data || empty($data['expires_at']) || (int)$data['expires_at'] < time()) {
            return null;
        }

        return isset($data['payload']) && is_array($data['payload']) ? $data['payload'] : null;
    }

    private function setCachedResponse($telephone, array $payload)
    {
        $this->writeJsonFile($this->responseCacheFile($telephone), [
            'expires_at' => time() + self::RESPONSE_CACHE_SECONDS,
            'payload' => $payload,
        ]);
    }
}
