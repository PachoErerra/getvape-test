<?php

class telegram_envelope
{
    private $registry;

    public function __construct($registry)
    {
        $this->registry = $registry;
    }

    public function __get($key)
    {
        return $this->registry->get($key);
    }

    /**
     * @return bool true если апдейт обработан конвертами (обычный flow бота пропускаем) 
     */
    public function handleUpdate($updates, $telegramClient)
    {
        try {
            if (!$this->tablesReady()) {
                return false;
            }

            $this->load->model('telegram/envelope');
            $this->load->model('customer/customer');

            if (!empty($updates['callback_query']['data'])) {
                $data = (string)$updates['callback_query']['data'];

                if (strpos($data, 'env_open:') === 0) {
                    $this->handleOpenCallback($updates, $telegramClient);
                    return true;
                }

                return false;
            }

            if (empty($updates['message'])) {
                return false;
            }

            $message = $updates['message'];
            $chatId = isset($message['from']['id']) ? (int)$message['from']['id'] : 0;

            if (!$chatId) {
                return false;
            }

            if (!empty($message['sticker']['file_id']) && $this->isAwaitingGlobalSticker()) {
                if (!$this->isEnvelopeAdmin($chatId)) {
                    $telegramClient->sendMessage([
                        'chat_id' => $chatId,
                        'text' => 'Стикер не прийнято.' . PHP_EOL
                            . 'Ваш Telegram ID: ' . $chatId . PHP_EOL
                            . 'Додайте цей ID в адмінці (Конверти → Ваш Telegram ID), збережіть, знову натисніть "Прив\'язати стикер" і перешліть стикер.',
                    ]);
                    $this->logError('sticker_rejected_not_admin', 'chat_id=' . $chatId);
                    return true;
                }

                return $this->handleAdminSticker($message, $telegramClient);
            }

            if (empty($message['text'])) {
                return false;
            }

            $text = trim((string)$message['text']);

            if (preg_match('~^/start(?:@\w+)?\s+(env_[A-Za-z0-9]+)~u', $text, $m)) {
                $code = substr($m[1], 4);
                $this->handleStart($chatId, $code, $message, $telegramClient);
                return true;
            }

            return false;
        } catch (Exception $e) {
            $this->logError('handleUpdate', $e->getMessage());
            return false;
        }
    }

    /**
     * После успешной привязки контакта: если ждали конверт - показать его.
     * @return bool
     */
    public function resumeAfterAuth($chatId, $customer_id, $telegramClient)
    {
        try {
            if (!$this->tablesReady()) {
                return false;
            }

            $this->load->model('telegram/envelope');

            $pending = $this->model_telegram_envelope->getPending($chatId);

            if (!$pending) {
                return false;
            }

            $envelope = $this->model_telegram_envelope->getEnvelopeById((int)$pending['envelope_id']);

            if (!$envelope || !(int)$envelope['status']) {
                $this->model_telegram_envelope->clearPending($chatId);
                return false;
            }

            $this->model_telegram_envelope->addEvent(
                (int)$envelope['envelope_id'],
                (int)$chatId,
                'auth',
                (int)$customer_id
            );

            $this->model_telegram_envelope->clearPending($chatId);
            $this->sendEnvelopeIntro($chatId, $envelope, $telegramClient);

            return true;
        } catch (Exception $e) {
            $this->logError('resumeAfterAuth', $e->getMessage());
            return false;
        }
    }

    private function handleStart($chatId, $code, $message, $telegramClient)
    {
        $envelope = $this->model_telegram_envelope->getActiveEnvelopeByCode($code);

        if (!$envelope) {
            $telegramClient->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Цей конверт недоступний або вимкнений.',
            ]);
            return;
        }

        if (!$this->model_customer_customer->getTelegramCustomerByChatId($chatId)) {
            $from = $this->model_customer_customer->telegramPayloadFrom($message['from']);
            if (empty($from['id'])) {
                $from['id'] = $chatId;
            }
            $this->model_customer_customer->createTelegramCustomer($from);
        }

        $customer_id = (int)$this->model_customer_customer->getUserIdByTelegramCustomerId($chatId);

        $this->model_telegram_envelope->addEvent(
            (int)$envelope['envelope_id'],
            (int)$chatId,
            'start',
            $customer_id
        );

        if (!$customer_id) {
            $this->model_telegram_envelope->setPending($chatId, (int)$envelope['envelope_id']);
            $this->askContact($chatId, $telegramClient);
            return;
        }

        $this->sendEnvelopeIntro($chatId, $envelope, $telegramClient);
    }

    private function handleOpenCallback($updates, $telegramClient)
    {
        $callback = $updates['callback_query'];
        $chatId = (int)$callback['from']['id'];
        $callbackId = $callback['id'];
        $data = (string)$callback['data'];
        $envelope_id = (int)substr($data, strlen('env_open:'));

        $envelope = $this->model_telegram_envelope->getEnvelopeById($envelope_id);

        if (!$envelope || !(int)$envelope['status']) {
            $this->answerCallback($callbackId, 'Конверт недоступний', true);
            return;
        }

        $customer_id = 0;
        if ($this->model_customer_customer->getTelegramCustomerByChatId($chatId)) {
            $customer_id = (int)$this->model_customer_customer->getUserIdByTelegramCustomerId($chatId);
        }

        if (!$customer_id) {
            $this->model_telegram_envelope->setPending($chatId, $envelope_id);
            $this->answerCallback($callbackId, 'Спочатку поділіться контактом', true);
            $this->askContact($chatId, $telegramClient);
            return;
        }

        $ttl = max(1, (int)$envelope['ttl_minutes']);
        $expires_at = date('Y-m-d H:i:s', time() + $ttl * 60);

        $this->model_telegram_envelope->addEvent(
            $envelope_id,
            $chatId,
            'open',
            $customer_id,
            $envelope['gift_name'],
            $expires_at
        );

        $this->answerCallback($callbackId, 'Відкрито!');

        // прибираємо повідомлення з кнопкою "Відкрити конверт"
        if (!empty($callback['message']['message_id'])) {
            $this->deleteTelegramMessage($chatId, (int)$callback['message']['message_id']);
        }

        $textAfter = trim((string)$envelope['text_after']);
        $gift = trim((string)$envelope['gift_name']);

        $text = $textAfter !== ''
            ? $textAfter
            : 'Ваш подарунок готовий. Отримайте його зараз у магазині у менеджера.';
        $text .= PHP_EOL . PHP_EOL . '🎁 Подарунок: ' . ($gift !== '' ? $gift : '—');

        // Telegram caption max 1024
        if (mb_strlen($text) > 1024) {
            $text = mb_substr($text, 0, 1021) . '...';
        }

        $imagePath = $this->getEnvelopeImagePath(isset($envelope['image']) ? $envelope['image'] : '');

        try {
            if ($imagePath !== '') {
                // одне повідомлення: фото + текст (файл з диска надійніше за URL)
                $this->sendPrizePhoto($chatId, $imagePath, $text);
            } else {
                $telegramClient->sendMessage([
                    'chat_id' => $chatId,
                    'text' => $text,
                ]);
            }
        } catch (Exception $e) {
            $this->logError('sendPrize', $e->getMessage());
            $telegramClient->sendMessage([
                'chat_id' => $chatId,
                'text' => $text,
            ]);
        }
    }

    private function getEnvelopeImagePath($image)
    {
        $image = ltrim(trim((string)$image), '/');

        if ($image === '') {
            return '';
        }

        $full = DIR_IMAGE . $image;

        return is_file($full) ? $full : '';
    }

    private function sendPrizePhoto($chatId, $imagePath, $caption)
    {
        $token = TELEGRAM_TOKEN;
        $url = 'https://api.telegram.org/bot' . $token . '/sendPhoto';

        if (class_exists('CURLFile')) {
            $photo = new CURLFile($imagePath);
        } else {
            $photo = '@' . $imagePath;
        }

        $post = [
            'chat_id' => $chatId,
            'caption' => $caption,
            'photo' => $photo,
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

        if ($errno || !$response) {
            throw new Exception('sendPhoto curl: ' . $error);
        }

        $json = json_decode($response, true);

        if (empty($json['ok'])) {
            throw new Exception('sendPhoto api: ' . $response);
        }
    }

    private function handleAdminSticker($message, $telegramClient)
    {
        $chatId = (int)$message['from']['id'];

        if (!$this->isAwaitingGlobalSticker()) {
            return false;
        }

        $fileId = $message['sticker']['file_id'];
        $this->saveBotSetting('telegram_envelope_sticker_file_id', $fileId);
        $this->saveBotSetting('telegram_envelope_await_sticker', '0');

        $telegramClient->sendMessage([
            'chat_id' => $chatId,
            'text' => '✅ Стикер збережено. Він буде для всіх конвертів.',
        ]);

        return true;
    }

    private function getGlobalStickerFileId()
    {
        $this->load->model('setting/setting');
        $value = $this->model_setting_setting->getSettingValue('telegram_envelope_sticker_file_id');

        return $value ? (string)$value : '';
    }

    private function isAwaitingGlobalSticker()
    {
        $this->load->model('setting/setting');
        return (string)$this->model_setting_setting->getSettingValue('telegram_envelope_await_sticker') === '1';
    }

    private function saveBotSetting($key, $value)
    {
        $this->load->model('setting/setting');
        $existing = $this->model_setting_setting->getSettingValue($key);

        if ($existing === null) {
            $this->db->query("INSERT INTO `" . DB_PREFIX . "setting` SET
                `store_id` = '0',
                `code` = 'telegram_bot',
                `key` = '" . $this->db->escape($key) . "',
                `value` = '" . $this->db->escape($value) . "',
                `serialized` = '0'");
        } else {
            $this->model_setting_setting->editSettingValue('telegram_bot', $key, $value);
        }
    }

    private function sendEnvelopeIntro($chatId, $envelope, $telegramClient)
    {
        $button = trim((string)$envelope['button_text']);
        if ($button === '') {
            $button = 'Відкрити конверт';
        }

        $text = trim((string)$envelope['text_before']);
        if ($text === '') {
            $text = 'Дякуємо, що ви з нами! Натисніть кнопку нижче, щоб дізнатися, що всередині.';
        }

        $keyboard = [
            'inline_keyboard' => [
                [
                    [
                        'text' => $button,
                        'callback_data' => 'env_open:' . (int)$envelope['envelope_id'],
                    ],
                ],
            ],
        ];

        $telegramClient->sendMessage([
            'chat_id' => $chatId,
            'text' => $text,
            'reply_markup' => json_encode($keyboard),
        ]);
    }

    private function askContact($chatId, $telegramClient)
    {
        $keyboard = [
            [
                [
                    'text' => '➡️ Поділитись контактом',
                    'request_contact' => true,
                ],
            ],
        ];

        $reply_markup = $telegramClient->replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true,
        ]);

        $telegramClient->sendMessage([
            'chat_id' => $chatId,
            'text' => '👋 Щоб відкрити конверт, натисніть "Поділитись контактом" ⤵️',
            'reply_markup' => $reply_markup,
        ]);
    }

    private function answerCallback($callbackId, $text, $alert = false)
    {
        $payload = [
            'callback_query_id' => $callbackId,
            'text' => $text,
            'show_alert' => $alert ? true : false,
        ];

        $ch = curl_init('https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/answerCallbackQuery');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_exec($ch);
        curl_close($ch);
    }

    private function deleteTelegramMessage($chatId, $messageId)
    {
        $url = 'https://api.telegram.org/bot' . TELEGRAM_TOKEN
            . '/deleteMessage?chat_id=' . rawurlencode($chatId)
            . '&message_id=' . (int)$messageId;

        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'timeout' => 5,
            ],
        ]);

        @file_get_contents($url, false, $context);
    }

    private function isEnvelopeAdmin($chatId)
    {
        $raw = (string)$this->config->get('telegram_envelope_admin_ids');

        if ($raw === '') {
            $this->load->model('setting/setting');
            $raw = (string)$this->model_setting_setting->getSettingValue('telegram_envelope_admin_ids');
        }

        if ($raw === '') {
            return false;
        }

        $ids = preg_split('~[\s,;]+~', $raw, -1, PREG_SPLIT_NO_EMPTY);

        return in_array((string)$chatId, $ids, true);
    }

    private function tablesReady()
    {
        static $ready = null;

        if ($ready !== null) {
            return $ready;
        }

        try {
            $q = $this->db->query("SHOW TABLES LIKE 'telegram_envelope'");
            $ready = (bool)$q->num_rows;
        } catch (Exception $e) {
            $ready = false;
        }

        return $ready;
    }

    private function logError($event, $message)
    {
        file_put_contents(
            DIR_LOGS . 'telegram_envelope.log',
            date('c') . ' ' . $event . ' ' . $message . PHP_EOL,
            FILE_APPEND
        );
    }
}
