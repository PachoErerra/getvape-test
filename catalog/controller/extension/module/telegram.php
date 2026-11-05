<?php

use Telegram\Bot\Api;

class ControllerExtensionModuleTelegram extends Controller
{


    const BTN_GO_TO_SHOPPING = '🌐 Перейти до покупок';

//    const BTN_OUR_STORES = '🛍️ Наші магазини';

    const DRAW = '🎁 Розіграш';
    const BTN_PERSONAL_DATA = '👤 Мої бонуси та дані';
    const BTN_CONTACT_THE_MANAGER = "📩 Зв'язатися з менеджером";

    const BTN_SEND_PROPOSAL = "📝 Скарги та пропозиції";

    /** Мінімальний інтервал між запитами з одного IP (сек). */
    private const TG_IP_LIMIT_SEC = 3;

    /** Макс. відправок коду в Telegram на номер за 24 год. */
    private const TG_DAILY_MAX_SENDS = 3;

    /** Макс. невірних спроб введення коду з одного chat_id. */
    private const TG_AUTH_MAX_FAILS = 5;

    /** Блокування після перевищення спроб auth (сек). */
    private const TG_AUTH_LOCK_SEC = 900;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->library('telegram');

        $this->telegramClient = $registry->get('telegram')->getClient();

        $this->telegram = $registry->get('telegram');
    }

    public function check()
    {
        $this->load->model('customer/customer');

        $this->load->language('extension/module/regsms');


        $telephone = $this->validateTelephone($this->request->post['telephone']);

        if (!$telephone) {
            $result['error']['telephone'] = $this->language->get('text_regsms_error_telephone');
        } else {

            $customer_id = $this->model_customer_customer->getCustomerByPhone($telephone);


            if ($customer_id) {

                $telegramUserId = $this->model_customer_customer->getTelegramCustomerByCustomerId($customer_id);

                if ($telegramUserId) {

                    $result = $this->sendCheckMessage($telegramUserId);

                } else {
                    $result = [
                        'error' => 0,
                        'link' => TELEGRAM_BOT_LINK,
                    ];
                }
            } else {
                $result = [
                    'error' => 0,
                    'link' => TELEGRAM_BOT_LINK,
                ];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));

    }


    public function telegram_clear_cache()
    {

        $messageId = $this->request->post['messageId'];

        $chatId = $this->request->post['chatId'];

        try {

            if ($messageId && $chatId) {

                $this->telegram->deleteMessage($chatId, $messageId);
            }

            $sessionId = $this->session->getId();

            $this->cache->delete('sms_code' . $sessionId);

            $this->cache->delete('sms_link' . $sessionId);

            $this->session->data['login_user'] = false;

        } catch (\Exception $e) {

        }

    }

    public function check_telegram_login()
    {


        if ($this->customer->getId()) {
            $json['success'] = true;
        } else if (isset($this->session->data['login_user']) && $this->session->data['login_user']) {

            $this->load->model('customer/customer');

            $customer_id = $this->session->data['login_user'];

            $wishlist = isset($_COOKIE['cookieWishlist']) ? json_decode($_COOKIE['cookieWishlist'], 1) : [];

            $wishlist = array_unique($wishlist);

            if ($wishlist) {
                $this->load->model('account/wishlist');
                $this->load->model('catalog/product');

                foreach ($wishlist as $id) {
                    $product_info = $this->model_catalog_product->getProduct($id);
                    if ($product_info) {
                        $this->model_account_wishlist->addWishlist($id);
                    }
                }

                setcookie('cookieWishlist', '', time() - 3600 * 24 * 365, '/');
            }

            $customer_phone = $this->model_customer_customer->getCustomerPhoneById($customer_id);

            if ($this->customer->loginSms($customer_phone)) {
                $this->load->model('account/address');

                if ($this->config->get('config_tax_customer') == 'payment') {
                    $this->session->data['payment_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
                }

                if ($this->config->get('config_tax_customer') == 'shipping') {
                    $this->session->data['shipping_address'] = $this->model_account_address->getAddress($this->customer->getAddressId());
                }


                //	$json['redirect'] = $this->url->link('account/account', '', true);
                $json['success'] = true;
            } else {
                $json['success'] = false;
            }

            if (isset($this->request->cookie['battle_pass_hidden'])) {
                setcookie('battle_pass_hidden', '', time() - 3600, '/');
            }

            if (isset($this->request->cookie['fortune_hidden'])) {
                setcookie('fortune_hidden', '', time() - 3600, '/');
            }

            $this->getbonus($customer_phone);

            $this->session->data['login_user'] = false;
        } else {
            $json['success'] = false;
        }


        //       $json['link'] = $this->url->link('account/edit', '', true);

        if (isset($this->session->data['redirect']) && $this->session->data['redirect'] && ($this->session->data['redirect'] == 'https://getvape.com.ua/index.php?route=checkout/simplecheckout' || $this->session->data['redirect'] == 'https://getvape.com.ua/index.php?route=checkout/simplecheckout ')) {
            $json['link'] = $this->session->data['redirect'];
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));

    }

    public function telegram_login()
    {

        $telephone = $this->validateTelephone($this->request->post['telephone']);

        $this->load->language('extension/module/regsms');

        if (!$telephone) {

            $result = [
                'error' => 1,
                'text' => $this->language->get('text_regsms_error_telephone'),
            ];

        } else {

            $this->load->model('customer/customer');

            $customer_id = $this->model_customer_customer->getCustomerByPhone($telephone);


            if ($customer_id) {

                $telegramUserId = $this->model_customer_customer->getTelegramCustomerByCustomerId($customer_id);

                if ($telegramUserId) {
                    $result = $this->sendTelegramLoginButton($telegramUserId);
                } else {
                    $result = $this->telegramNeedBotResult();
                }
            } else {
                $result = $this->telegramNeedBotResult();
            }
        }

        $this->response->addHeader('Content-Type: application/json');

        $this->response->setOutput(json_encode($result));

    }

    public function telegram_login_code()
    {
        $requestId = $this->telegramRequestId();
        $this->telegramDebug('telegram_login_code.enter', [
            'request_id' => $requestId,
            'method' => $this->request->server['REQUEST_METHOD'] ?? '',
            'ip' => $this->telegramGetClientIp(),
            'telephone_raw' => $this->request->post['telephone'] ?? null,
        ]);

        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->telegramDebug('telegram_login_code.method_not_allowed', ['request_id' => $requestId], 'WARN');
            $this->telegramJsonResponse(['error' => 1, 'text' => 'Method Not Allowed'], 405);
            return;
        }

        $this->load->language('extension/module/regsms');
        $this->load->language('extension/module/regsms_telegram');

        if ($rate = $this->telegramCheckIpRateLimit()) {
            $this->telegramDebug('telegram_login_code.rate_limit_ip', [
                'request_id' => $requestId,
                'rate' => $rate,
            ], 'WARN');
            $this->telegramJsonResponse($rate, 429);
            return;
        }

        $telephone = $this->validateTelephone($this->request->post['telephone'] ?? '');
        $this->telegramDebug('telegram_login_code.telephone_validated', [
            'request_id' => $requestId,
            'telephone_normalized' => $telephone,
        ]);

        if (!$telephone) {
            $this->telegramDebug('telegram_login_code.invalid_telephone', ['request_id' => $requestId], 'WARN');
            $this->telegramJsonResponse([
                'error' => 1,
                'text' => $this->language->get('text_regsms_error_telephone'),
            ]);
            return;
        }

        if ($rate = $this->telegramCheckDailySendLimit($telephone)) {
            $this->telegramDebug('telegram_login_code.rate_limit_daily', [
                'request_id' => $requestId,
                'telephone' => $telephone,
                'rate' => $rate,
            ], 'WARN');
            $this->telegramJsonResponse($rate);
            return;
        }

        $this->load->model('customer/customer');

        $customer_id = $this->model_customer_customer->getCustomerIdByTelephoneNormalized($telephone);
        $this->telegramDebug('telegram_login_code.customer_resolved', [
            'request_id' => $requestId,
            'telephone' => $telephone,
            'customer_id' => $customer_id,
        ]);

        if (!$customer_id) {
            // Нормальный сценарій: перший вхід, customer створиться після контакту у боті (webhook).
            $this->telegramDebug('telegram_login_code.customer_absent_first_login_flow', [
                'request_id' => $requestId,
                'telephone' => $telephone,
            ], 'INFO');
            $this->telegramJsonResponse($this->telegramNeedBotResult());
            return;
        }

        $telegramUserId = $this->model_customer_customer->getTelegramCustomerByCustomerId($customer_id);

        if (!$telegramUserId) {
            $telegramUserId = $this->model_customer_customer->getTelegramCustomerIdByTelephone($telephone);
        }
        $this->telegramDebug('telegram_login_code.telegram_user_resolved', [
            'request_id' => $requestId,
            'telegram_user_id' => $telegramUserId,
        ]);

        if (!$telegramUserId) {
            $this->telegramDebug('telegram_login_code.telegram_user_not_found', ['request_id' => $requestId], 'WARN');
            $this->telegramJsonResponse($this->telegramNeedBotResult());
            return;
        }

        if ($rate = $this->telegramCheckCodeSendCooldown($telephone, $telegramUserId)) {
            $this->telegramDebug('telegram_login_code.rate_limit_cooldown', [
                'request_id' => $requestId,
                'telegram_user_id' => $telegramUserId,
                'telephone' => $telephone,
                'rate' => $rate,
            ], 'WARN');
            $this->telegramJsonResponse($rate);
            return;
        }

        $code = rand(1111, 9999);
        $time = time();

        $this->session->data['telegram_login'][$telegramUserId]['code'] = $code;
        $this->session->data['telegram_login'][$telegramUserId]['time'] = $time;
        $this->session->data['telegram_login'][$telegramUserId]['telegram_user_id'] = $telegramUserId;

        $this->model_customer_customer->addTelegramCode($telegramUserId, $code, $time);
        $this->telegramDebug('telegram_login_code.db_insert', [
            'request_id' => $requestId,
            'telegram_user_id' => $telegramUserId,
            'code' => $code,
            'time' => $time,
        ]);
        $this->telegramRecordCodeSend($telephone);

        $result = $this->sendTelegramLoginCode($telegramUserId, $code);
        $this->telegramDebug('telegram_login_code.telegram_send_result', [
            'request_id' => $requestId,
            'telegram_user_id' => $telegramUserId,
            'result' => $result,
        ], !empty($result['error']) ? 'WARN' : 'INFO');

        $this->telegramJsonResponse($result);
    }

    public function telegram_login_code_auth()
    {
        $requestId = $this->telegramRequestId();
        $this->telegramDebug('telegram_login_code_auth.enter', [
            'request_id' => $requestId,
            'method' => $this->request->server['REQUEST_METHOD'] ?? '',
            'ip' => $this->telegramGetClientIp(),
            'chat_id' => $this->request->post['chat_id'] ?? null,
            'message_id' => $this->request->post['message_id'] ?? null,
            'code_len' => strlen((string)($this->request->post['code'] ?? '')),
        ]);

        if ($this->request->server['REQUEST_METHOD'] !== 'POST') {
            $this->telegramDebug('telegram_login_code_auth.method_not_allowed', ['request_id' => $requestId], 'WARN');
            $this->telegramJsonResponse(['error' => 1, 'text' => 'Method Not Allowed'], 405);
            return;
        }

        $this->load->language('extension/module/regsms_telegram');

        if ($rate = $this->telegramCheckIpRateLimit()) {
            $this->telegramDebug('telegram_login_code_auth.rate_limit_ip', [
                'request_id' => $requestId,
                'rate' => $rate,
            ], 'WARN');
            $this->telegramJsonResponse($rate, 429);
            return;
        }

        $code = isset($this->request->post['code']) ? trim($this->request->post['code']) : '';
        $chat_id = isset($this->request->post['chat_id']) ? $this->request->post['chat_id'] : '';
        $message_id = isset($this->request->post['message_id']) ? $this->request->post['message_id'] : '';

        if (!$code || !$chat_id) {
            $this->telegramDebug('telegram_login_code_auth.invalid_input', [
                'request_id' => $requestId,
                'chat_id' => $chat_id,
                'has_code' => (bool)$code,
            ], 'WARN');
            if ($message_id) {
                $this->telegram->deleteMessage($chat_id, $message_id);
            }

            $this->telegramJsonResponse([
                'error' => 1,
                'text' => 'Виникла помилка, спробуйте ще раз',
            ]);
            return;
        }

        if ($lock = $this->telegramCheckAuthLocked($chat_id)) {
            $this->telegramDebug('telegram_login_code_auth.locked', [
                'request_id' => $requestId,
                'chat_id' => $chat_id,
                'lock' => $lock,
            ], 'WARN');
            $this->telegramJsonResponse($lock);
            return;
        }

        $this->load->model('customer/customer');

        $loginCode = $this->model_customer_customer->getTelegramCode($chat_id, $code);
        $this->telegramDebug('telegram_login_code_auth.lookup', [
            'request_id' => $requestId,
            'chat_id' => $chat_id,
            'found' => (bool)$loginCode,
            'db_time' => $loginCode['time'] ?? null,
        ]);

        $codeMaxAge = max($this->telegramGetCodeCooldownSeconds(), 900);

        if ($loginCode && !empty($loginCode['time']) && ((int)$loginCode['time'] + $codeMaxAge) < time()) {

            $this->model_customer_customer->deleteTelegramCode($chat_id);

            $this->telegramDebug('telegram_login_code_auth.expired_deleted', [
                'request_id' => $requestId,
                'chat_id' => $chat_id,
                'code_time' => (int)$loginCode['time'],
                'code_max_age' => $codeMaxAge,
            ], 'WARN');

            $loginCode = null;
        }

        if ($loginCode) {

            $userID = $this->model_customer_customer->getUserIdByTelegramCustomerId($chat_id);

            $result = $this->model_customer_customer->loginById($userID);

            $this->telegramDebug('telegram_login_code_auth.login_by_id', [
                'request_id' => $requestId,
                'chat_id' => $chat_id,
                'user_id' => $userID,
                'result' => (bool)$result,
            ], $result ? 'INFO' : 'WARN');

            if ($result) {
                $this->telegramClearAuthFailures($chat_id);

                $this->model_customer_customer->deleteTelegramCode($chat_id);

                $this->telegramClient->sendMessage([
                    'chat_id' => $chat_id,
                    'text' => '✅ Ви увійшли в особистий кабінет, будь ласка, [поверніться на сайт](https://getvape.com.ua/specials/) для продовження покупок.',
                    'parse_mode' => 'Markdown',
                    'reply_markup' => $this->returnDefaultKeyboard(),
                ]);

                if ($message_id) {
                    $this->telegram->deleteMessage($chat_id, $message_id);
                }

                $this->model_customer_customer->setTGlogin($chat_id);


                $this->telegramJsonResponse(['success' => true]);
                return;
            }

            if ($message_id) {
                $this->telegram->deleteMessage($chat_id, $message_id);
            }

            $this->telegramJsonResponse([
                'error' => 1,
                'text' => 'Виникла помилка, спробуйте ще раз',
            ]);
            return;
        }

        $fail = $this->telegramRecordAuthFailure($chat_id);
        $this->telegramDebug('telegram_login_code_auth.invalid_code', [
            'request_id' => $requestId,
            'chat_id' => $chat_id,
            'fail_state' => $fail,
        ], 'WARN');

//        if ($message_id) {
//            $this->telegram->deleteMessage($chat_id, $message_id);
//        }

        $this->telegramJsonResponse($fail ?: [
            'error' => 1,
            'text' => 'Невірний код',
        ]);
    }

    public function telegram_login_check()
    {
        $telephone = $this->validateTelephone($this->request->post['telephone']);

        $this->load->language('extension/module/regsms');

        if (!$telephone) {

            $result = [
                'error' => 1,
                'reset' => true,
                'session_id' => $this->session->getId(),
                'text' => 'Некоректний номер телефону',
            ];

        } else {
            $this->load->model('customer/customer');

            $customer_id = $this->model_customer_customer->getCustomerByPhone($telephone);

            file_put_contents(DIR_LOGS . 'telegram.log', '-------' . date('Y-m-d H:i:s') . '-------' . PHP_EOL . $telephone . PHP_EOL, FILE_APPEND);

            file_put_contents(DIR_LOGS . 'telegram.log', '-------' . date('Y-m-d H:i:s') . '-------' . PHP_EOL . $customer_id . PHP_EOL, FILE_APPEND);

            if ($customer_id) {

                $telegramUserId = $this->model_customer_customer->getTelegramCustomerByCustomerId($customer_id);

                if ($telegramUserId) {

                    $result = $this->sendTelegramLoginButton($telegramUserId);
                } else {
                    $result = [
                        'session_id' => $this->session->getId(),
                        'error' => 1,
                        'reset' => true,
                        'text' => 'Вам потрібно спочатку доєднатись до нашого телеграм бота!',
                    ];
                }

            } else {
                $result = [
                    'session_id' => $this->session->getId(),
                    'error' => 1,
                    'reset' => true,
                    'text' => 'Номер телефону не зареестровано',
                ];
            }

        }


        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($result));
    }


    public function sendTelegramLoginButton($chatId)
    {
        $sessionId = $this->session->getId();

        $callback_data = [
            'session_id' => $sessionId,
        ];

        $keyboard = json_encode([
            "inline_keyboard" => [
                [
                    [
                        "text" => "➡️ Увійти",
                        "callback_data" => json_encode($callback_data),
                    ],
                ]
            ]
        ]);


        $this->cache->set('sms_link' . $sessionId, true);

        $this->cache->delete('sms_code');

        try {

            $telegramMessageIds = json_decode(file_get_contents(DIR_LOGS . 'telegram_message_ids.log'), true);


            if ($telegramMessageIds && isset($telegramMessageIds[$chatId]) && $telegramMessageIds[$chatId]) {
                $this->telegram->deleteMessage($chatId, $telegramMessageIds[$chatId]);
            }


            $response = $this->telegramClient->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Підтвердьте вхід до особистого кабінету',
                    'reply_markup' => $keyboard
                ]
            );

            $messageId = $response->getMessageId();

            $chatId = $response['chat']['id'];

            $this->session->data['messageId'] = $messageId;

            $this->session->data['chatId'] = $chatId;

            if ($telegramMessageIds) {
//                $telegramMessageIds = json_decode($telegramMessageIds, true);
                $telegramMessageIds[$chatId] = $messageId;
            } else {
                $telegramMessageIds[$chatId] = $messageId;
            }

            file_put_contents(DIR_LOGS . 'telegram_message_ids.log', json_encode($telegramMessageIds));

            return [
                'error' => 0,
                'type' => 'telegram',
                'session_id' => $this->session->getId(),
                'messageId' => $messageId,
                'chatId' => $chatId,
                'text' => 'На ваш telegram було надіслано повідомлення для входу на сайт',
            ];
        } catch (\Exception $e) {
            return $this->telegramNeedBotResult(true);
        }
    }

    public function sendTelegramLoginCode($chatId, $code)
    {
        $this->cache->delete('sms_code');

        try {

            $telegramMessageIds = json_decode(file_get_contents(DIR_LOGS . 'telegram_message_ids.log'), true);


            if ($telegramMessageIds && isset($telegramMessageIds[$chatId]) && $telegramMessageIds[$chatId]) {
                $this->telegram->deleteMessage($chatId, $telegramMessageIds[$chatId]);
            }


            $response = $this->telegramClient->sendMessage([
                    'chat_id' => $chatId,
                    'parse_mode' => 'HTML',
                    'text' => "🔐 Код підтвердження: <code>$code</code>",
                ]
            );

            $messageId = $response->getMessageId();

            $chatId = $response['chat']['id'];

            $this->session->data['messageId'] = $messageId;

            $this->session->data['chatId'] = $chatId;

            if ($telegramMessageIds) {
//                $telegramMessageIds = json_decode($telegramMessageIds, true);
                $telegramMessageIds[$chatId] = $messageId;
            } else {
                $telegramMessageIds[$chatId] = $messageId;
            }

            file_put_contents(DIR_LOGS . 'telegram_message_ids.log', json_encode($telegramMessageIds));

            return [
                'error' => 0,
                'type' => 'telegram',
                'telegram_user_id' => $chatId,
                'session_id' => $this->session->getId(),
                'messageId' => $messageId,
                'chatId' => $chatId,
                'text' => 'На Ваш telegram було надіслано код підтвердження для входу на сайт',
            ];
        } catch (\Exception $e) {
            $this->telegramDebug('send_telegram_login_code.exception', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
            ], 'ERROR');
            return $this->telegramNeedBotResult(true);
        }
    }

    private function telegramRequestId()
    {
        if (empty($this->session->data['telegram_request_id'])) {
            $this->session->data['telegram_request_id'] = uniqid('tg_', true);
        }

        return $this->session->data['telegram_request_id'];
    }

    /**
     * JSONL debug-лог авторизації через Telegram.
     */
    private function telegramDebug($event, array $ctx = [], $level = 'INFO')
    {
        $record = [
            'ts' => date('c'),
            'level' => $level,
            'event' => $event,
            'route' => $this->request->get['route'] ?? null,
            'session_id' => $this->session->getId(),
            'ctx' => $ctx,
        ];

        file_put_contents(
            DIR_LOGS . 'telegram_auth_debug.log',
            json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND
        );
    }

    private function telegramJsonResponse(array $data, $httpCode = 200)
    {
        if ($httpCode === 429) {
            $this->response->addHeader('HTTP/1.1 429 Too Many Requests');
        } elseif ($httpCode === 405) {
            $this->response->addHeader('HTTP/1.1 405 Method Not Allowed');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($data));
    }

    private function telegramGetClientIp()
    {
        return $this->request->server['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    private function telegramGetCodeCooldownSeconds()
    {
        $seconds = (int)$this->config->get('module_regsms_time_session');

        return $seconds > 0 ? $seconds : 120;
    }

    /** Anti-flood: мін. інтервал між запитами з IP (cache, не session). */
    private function telegramCheckIpRateLimit()
    {
        $ip = $this->telegramGetClientIp();
        $key = 'tg_rl_ip_' . md5($ip);
        $last = (int)$this->cache->get($key);

        if ($last && (time() - $last) < self::TG_IP_LIMIT_SEC) {
            $retry = self::TG_IP_LIMIT_SEC - (time() - $last);

            return $this->telegramRateLimitResponse($retry);
        }

        $this->cache->set($key, time());

        return null;
    }

    /** Пауза між відправками коду (як module_regsms_time_session для SMS). */
    private function telegramCheckCodeSendCooldown($telephone, $telegramUserId)
    {
        $cooldown = $this->telegramGetCodeCooldownSeconds();
        $retryAfter = 0;

        if (!empty($this->session->data['telegram_code_sent'][$telephone]['time'])) {
            $retryAfter = max($retryAfter, $this->session->data['telegram_code_sent'][$telephone]['time'] + $cooldown - time());
        }

        $codeInfo = $this->model_customer_customer->getTelegramLoginCodeInfo($telegramUserId);

        if ($codeInfo && !empty($codeInfo['time'])) {
            $retryAfter = max($retryAfter, (int)$codeInfo['time'] + $cooldown - time());
        }

        if ($retryAfter > 0) {
            return $this->telegramRateLimitResponse($retryAfter);
        }

        return null;
    }

    /** Не більше TG_DAILY_MAX_SENDS реальних sendMessage на номер за 24 год. */
    private function telegramCheckDailySendLimit($telephone)
    {
        $key = 'tg_rl_day_' . md5($telephone);
        $data = $this->cache->get($key);

        if (!is_array($data) || empty($data['start']) || (time() - (int)$data['start']) > 86400) {
            return null;
        }

        if ((int)$data['count'] >= self::TG_DAILY_MAX_SENDS) {
            $this->load->language('extension/module/regsms_telegram');

            return [
                'error' => 1,
                'type' => 'rate_limit',
                'text' => $this->language->get('text_tg_daily_limit'),
            ];
        }

        return null;
    }

    private function telegramRecordCodeSend($telephone)
    {
        $cooldown = $this->telegramGetCodeCooldownSeconds();

        if (!isset($this->session->data['telegram_code_sent'])) {
            $this->session->data['telegram_code_sent'] = [];
        }

        $this->session->data['telegram_code_sent'][$telephone] = ['time' => time()];

        $key = 'tg_rl_day_' . md5($telephone);
        $data = $this->cache->get($key);

        if (!is_array($data) || empty($data['start']) || (time() - (int)$data['start']) > 86400) {
            $data = ['start' => time(), 'count' => 0];
        }

        $data['count'] = (int)$data['count'] + 1;
        $this->cache->set($key, $data);
    }

    private function telegramRateLimitResponse($retryAfter)
    {
        $this->load->language('extension/module/regsms_telegram');
        $retryAfter = max(1, (int)$retryAfter);

        return [
            'error' => 1,
            'type' => 'rate_limit',
            'retry_after' => $retryAfter,
            'text' => sprintf($this->language->get('text_tg_rate_limit'), $retryAfter),
        ];
    }

    private function telegramCheckAuthLocked($chat_id)
    {
        $key = 'tg_auth_lock_' . (int)$chat_id;
        $lockedUntil = (int)$this->cache->get($key);

        if ($lockedUntil > time()) {
            return $this->telegramAuthLockResponse($lockedUntil - time());
        }

        return null;
    }

    private function telegramRecordAuthFailure($chat_id)
    {
        $key = 'tg_auth_fail_' . (int)$chat_id;
        $fails = (int)$this->cache->get($key) + 1;

        $this->cache->set($key, $fails);

        if ($fails >= self::TG_AUTH_MAX_FAILS) {
            $lockedUntil = time() + self::TG_AUTH_LOCK_SEC;
            $this->cache->set('tg_auth_lock_' . (int)$chat_id, $lockedUntil);
            $this->cache->delete($key);

            return $this->telegramAuthLockResponse(self::TG_AUTH_LOCK_SEC);
        }

        return null;
    }

    private function telegramClearAuthFailures($chat_id)
    {
        $this->cache->delete('tg_auth_fail_' . (int)$chat_id);
        $this->cache->delete('tg_auth_lock_' . (int)$chat_id);
    }

    private function telegramAuthLockResponse($retryAfter)
    {
        $this->load->language('extension/module/regsms_telegram');
        $retryAfter = max(1, (int)$retryAfter);

        return [
            'error' => 1,
            'type' => 'rate_limit',
            'retry_after' => $retryAfter,
            'text' => sprintf($this->language->get('text_tg_auth_locked'), $retryAfter),
        ];
    }

    /**
     * Бот не привʼязаний до телефону (немає запису в telegram_users) або не вдалось надіслати в TG.
     */
    private function telegramNeedBotResult($sendFailed = false)
    {
        $this->load->language('extension/module/regsms_telegram');

        return [
            'error' => $sendFailed ? 1 : 0,
            'type' => 'need_bot',
            'session_id' => $this->session->getId(),
            'link' => TELEGRAM_BOT_LINK,
            'text' => $this->language->get($sendFailed ? 'text_tg_connect_send_failed' : 'text_tg_connect_api_hint'),
        ];
    }

    private function sendCheckMessage($chatId)
    {

        try {
            $response = $this->telegramClient->sendMessage([
                    'chat_id' => $chatId,
                    'text' => 'Вхід до особистого кабінету',
                ]
            );
            $messageId = $response->getMessageId();

            $chatId = $response['chat']['id'];

            $this->telegram->deleteMessage($chatId, $messageId);

            return [
                'error' => 0,
            ];
        } catch (\Exception $e) {
            return [
                'error' => 0,
                'link' => TELEGRAM_BOT_LINK
            ];
        }
    }

    private function returnDefaultKeyboard()
    {

        $inline_keyboard = [
            [
                [
                    'text' => self::BTN_GO_TO_SHOPPING,
                    'url' => 'https://getvape.com.ua/specials/',
                ],
            ],
//            [
//                [
//                    'text' => self::BTN_OUR_STORES,
//                    'url' => 'https://qrfy.io/p/iY7zmNbpok'
//                ],
//            ],
            [
                [
                    'text' => self::BTN_PERSONAL_DATA,
                    "callback_data" => self::BTN_PERSONAL_DATA,
                ],
            ],
            [
                [
                    'text' => self::BTN_CONTACT_THE_MANAGER,
                    'url' => 'https://t.me/vapors_vape_shop',
                ],
            ],
            [
                [
                    'text' => self::BTN_SEND_PROPOSAL,
                    'url' => 'https://docs.google.com/forms/d/e/1FAIpQLSfTr3xbIlY-cM9tbDDVGNMmgp2MG8XRDrIYM3htSdkmEyvdTw/viewform',
                ],
            ],
        ];

        $inline_keyboard = array_merge($inline_keyboard, $this->telegram->getSocialKeyboardRows($this->config));

        return json_encode(['inline_keyboard' => $inline_keyboard]);

    }


    private function validateTelephone($phone)
    {

        $phone = str_replace(")", "", $phone);
        $phone = str_replace("(", "", $phone);
        $phone = str_replace("-", "", $phone);
        $phone = str_replace(" ", "", $phone);
        $phone = str_replace("+", "", $phone);

        $first_3 = substr($phone, 0, 3);
        $first_1 = substr($phone, 0, 1);
        $error = 0;

        if (($first_3 == '380') || ($first_3 == '375')) {
            $last = substr($phone, 3);
            if (strlen($last) == 9) {
                $phone = "+" . $phone;
            } else {
                $error = 1;
            }

        } elseif ($first_1 == '7') {
            $last = substr($phone, 1);
            if (strlen($last) == 10) {
                $phone = "+" . $phone;
            } else {
                $error = 1;
            }

        } elseif ($first_1 == '8') {
            $last = substr($phone, 1);
            if (strlen($last) == 10) {
                $phone = "+7" . $last;
            } else {
                $error = 1;
            }
        } else {
            $error = 1;
        }

        if (!$error) {
            return $phone;
        } else {
            return false;
        }

    }

    public function getbonus($telephone)
    {

//		$url = 'http://5.9.8.211:8089/VPR_UNF/hs/api/0/sitebuyers/?auth_token=3698024';
//        $url = 'http://5.9.8.211/site_retail/hs/api/0/sitebuyers/?auth_token=3698024';
        $url = 'http://vaporstlgrm.biz.ua/site_retail/hs/api/0/sitebuyers/?auth_token=3698024';


        $telephone = str_replace(['(', ')', '-', ' '], '', $telephone);

        $data = array(
            "telephone" => $telephone,
        );
        $request = json_encode($data);
//$this->log->write($request);
        try {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $request);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Accept-Encoding: UTF-8', 'Content-Type: application/json; charset=UTF-8'));
            curl_setopt($curl, CURLOPT_TIMEOUT, 30);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($curl);
            $curl_errno = curl_errno($curl);
            $curl_error = curl_error($curl);
            $aInfo = @curl_getinfo($curl);

            //close curl
            curl_close($curl);
            //analysis of the information received

            if ($curl_errno != 0) {
                $this->log->write('CURL failed ' . $curl_error . '(' . $curl_errno . ')');
                return 'error';
            }
            if ($aInfo["http_code"] != '200') {
                $this->log->write('HTTP failed ' . $aInfo["http_code"] . '(' . $response . ')');
                return 'error';
            }


            $result = json_decode($response, true);

            if ($result['success'] && $result['success'] = 'true' && isset($result['balance'])) {
                $customer_id = $this->customer->getId();
                $this->addReward($customer_id, $result['balance'], 'обновление из 1с');
            }

            if ($result['success'] && $result['success'] = 'true' && isset($result['discount'])) {
                $customer_id = $this->customer->getId();
                $this->setdiscount($customer_id, $result['discount']);
            }


        } catch (Exception $e) {
            return false;
        }
    }

    public function addReward($customer_id, $points = '', $description = '', $order_id = 0)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_reward SET customer_id = '" . (int)$customer_id . "', order_id = '" . (int)$order_id . "', points = '" . (int)$points . "', description = '" . $this->db->escape($description) . "', date_added = NOW()");
    }

    public function setdiscount($customer_id, $discount = '')
    {
        $this->db->query("UPDATE " . DB_PREFIX . "customer SET discount = '" . (int)$discount . "' WHERE customer_id = '" . (int)$customer_id . "'");
    }

}


