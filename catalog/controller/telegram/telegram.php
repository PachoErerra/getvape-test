<?php

use Telegram\Bot\Api;

class ControllerTelegramTelegram extends Controller
{

    private $telegram;

    private $token = '8F8IRujoiQYFgs78xxS1DXXWDsawL';

    const BTN_GO_TO_SHOPPING = '🌐 Перейти до покупок';
    const SITE = '🌐 Сайт';

    const BTN_OUR_STORES = '🛍️ Наші магазини';

    const DRAW = '🎁 Розіграш';
    const BTN_PERSONAL_DATA = '👤 Мої бонуси та дані';
    const BTN_CONTACT_THE_MANAGER = "📩 Зв'язатися з менеджером";

    const REPLACE_SHORT_CODES = ['[bonus]', '[link]', '[manager]', '[promocode]', '[percent]'];

    const QUEUE_MAX_ATTEMPTS = 3;

    const QUEUE_BATCH_LIMIT = 80;

    const QUEUE_LOCK_TTL = 120;

    const QUEUE_WORKERS = 4;

    const QUEUE_PURGE_DAYS = 14;

    const QUEUE_PURGE_BATCH = 5000;

    const QUEUE_PURGE_SECONDS = 15;

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->library('telegram');

        $this->telegramClient = $registry->get('telegram')->getClient();
        $this->telegram = $registry->get('telegram');
    }


//    public function index()
//    {
////        $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
////        $this->response->setOutput('404 Not Found');
////        return $this->response;
////
////        die();
//
//        $this->registerWebhook();
////        print_r(123);
////        die;
////        print_r(file_get_contents(DIR_LOGS . 'telegram.log'));
//
////        $telegramMessageIds = file_get_contents(DIR_LOGS . 'telegram_message_ids.log');
////        print_r($telegramMessageIds);
////        echo 2;
////        die;
////
////        print_r($this->session->data['login_user']);
////        die;
////        $this->load->model('customer/customer');
////
////
////        $telephone = 380665877275;
////        $customer_id = $this->model_customer_customer->getCustomerByPhone($telephone);
////        print_r($customer_id);
////        die;
//
//
////        print_r($this->session->getId());
////        $this->session->start('31f295d40cd5613fc5211f9d6d1');
////        print_r($this->session->getId());
//
////        $this->registerWebhook();
//
//        $response = $this->telegram->setWebhook(['url' => 'https://getvape.com.ua/telegram-webhook/?token=ehs1O7aV6Dm3FOTHFrGv']);
//
//    }


    public function cron()
    {

        $lockFile = DIR_STORAGE . 'telegram_cron.lock';

        if (file_exists($lockFile)) {

            if (filemtime($lockFile) > time() - 1800) {
                echo 'File locked';
                return;
            }
            unlink($lockFile);
        }

        file_put_contents($lockFile, getmypid());

        try {

            if (isset($this->request->get['token']) && $this->request->get['token'] == $this->token) {

                $this->load->model('setting/setting');

                $sending_messages = html_entity_decode($this->model_setting_setting->getSettingValue('sending_messages'));

                $interval_day_sending_messages = $this->model_setting_setting->getSettingValue('interval_day_sending_messages');

                if ($sending_messages && $interval_day_sending_messages) {

                    $this->load->model('customer/customer');

                    $today = date("Y-m-d H:i:s");

                    $today = new DateTime($today);

                    $telegramUsers = $this->model_customer_customer->getTelegramUsers(true);

                    if ($telegramUsers) {

                        foreach ($telegramUsers as $telegramUser) {

                            $chatId = $telegramUser['id'];

                            $lastOrder = $this->model_customer_customer->getLastOrderByCustomerId($telegramUser['user_id']);

                            if ($lastOrder) {

                                $date_added = new DateTime($lastOrder['date_added']);

                                $interval = $today->diff($date_added);

//                            if ($interval->days >= $interval_day_sending_messages) {

                                if ($this->isWholeNumberDivision($interval->days, $interval_day_sending_messages)) {
                                    try {
                                        $response = $this->telegramClient->sendMessage([
                                                'chat_id' => $chatId,
                                                'text' => $sending_messages,
                                                'reply_markup' => $this->returnKeyboard(),
                                            ]
                                        );
                                    } catch (Exception $exception) {

                                    }
                                }
                            }
                        }
                    }
                }

                $this->load->model('customer/customer');

                $orders = $this->model_customer_customer->getTelegramOrders3And6Months();

                if ($orders) {

                    $this->load->model('setting/setting');

                    $this->load->library('balance_api');

                    $after_order = json_decode($this->model_setting_setting->getSettingValue('after_order'), true);

                    $this->load->model('telegram/queue');

                    $date3 = date('Y-m-d', strtotime('-3 months'));

                    $date6 = date('Y-m-d', strtotime('-6 months'));

                    foreach ($orders as $order) {

                        $orderDate = date('Y-m-d', strtotime($order['date_added']));

                        if ($orderDate === $date3) {

                            $balanceInfo = $this->balance_api->getBalanceInfoByPhone($order['telephone']);

                            if ($balanceInfo) {
                                $balance = ($balanceInfo['balance'] ? $balanceInfo['balance'] : 0) . ' ₴';
                            } else {
                                $balance = '(Інформація про баланс поки відсутня)';
                            }

                            $message = $after_order[2]['message'] ?? null;

                            $link = $after_order[2]['link'] ?? null;

                            $manager = $after_order[2]['manager'] ?? null;

                            $message = str_replace(self::REPLACE_SHORT_CODES, [$balance, $link, $manager], $message);

                        } else {

                            $message = $after_order[3]['message'] ?? null;

                            $link = $after_order[3]['link'] ?? null;

                            $manager = $after_order[3]['manager'] ?? null;

                            $this->load->model('marketing/coupon');

                            $after_order = json_decode($this->model_setting_setting->getSettingValue('after_order'), true);

                            $code = 'TS0-' . $this->model_marketing_coupon->generateCode();

                            $percent = $after_order[3]['percent'] ?? 10;

                            $data = [
                                'name' => '6 місяців без покупки',
                                'code' => $code,
                                'type' => 'P',
                                'discount' => $percent,
                                'total' => null,
                                'conditions' => 'Відправка промокода після 6 місяців без покупки',
                                'logged' => 1,
                                'shipping' => 0,
                                'product' => null,
                                'category' => null,
                                'category_required' => null,
                                'date_start' => date('Y-m-d'),
                                'date_end' => date('Y-m-d', strtotime('+10 years')),
                                'uses_total' => 1,
                                'uses_customer' => 1,
                                'status' => 1,
                            ];

                            $coupon_id = $this->model_marketing_coupon->addCoupon($data);

                            if (!$coupon_id) {
                                continue;
                            }

                            $message = str_replace(self::REPLACE_SHORT_CODES, ['', $link, $manager, $code, $percent], $message);

                        }

                        $dataMessage = [
                            'chat_id' => $order['telegram_user_id'],
//                        'chat_id' => 382620651,
                            'parse_mode' => 'html',
                            'text' => $message,
                        ];

                        $this->model_telegram_queue->addQueue(false, $dataMessage);

                    }
                }
            }
        } finally {

            if (file_exists($lockFile)) {

                unlink($lockFile);

            }
        }
    }

    public function queue()
    {
        set_time_limit(self::QUEUE_LOCK_TTL);
        ignore_user_abort(true);

        $worker = isset($this->request->get['worker']) ? (int)$this->request->get['worker'] : 1;

        if ($worker < 1 || $worker > self::QUEUE_WORKERS) {
            echo 'Invalid worker';
            return;
        }

        $queueType = 'default_' . $worker;
        $lockFile = DIR_STORAGE . 'telegram_queue_' . $worker . '.lock';

        if (file_exists($lockFile)) {
            if (filemtime($lockFile) > time() - self::QUEUE_LOCK_TTL) {
                echo 'File locked';
                return;
            }
            @unlink($lockFile);
        }

        file_put_contents($lockFile, getmypid());

        try {

            if (isset($this->request->get['token']) && $this->request->get['token'] == $this->token) {

                $this->load->model('telegram/queue');

                $today = date("Y-m-d H:i");

                if ($today == '2025-12-22 06:00') {

                    $this->load->model('customer/customer');

                    $telegramUsers = $this->model_customer_customer->getTelegramUsers(true);

                    $text_promo = "🎄 Новорічний настрій уже тут! 🎄\nТисни «Розіграш» у боті з 22.12 по 29.12 та отримай гарантований секретний подарунок! \nВведи промокод на сайті. Не зволікай — подарунки активні лише до 29.12 🎁 \nА серед усіх замовлень із секретним подарунком ми розіграємо головні призи від Vapors та Punch 30.12.";

                    foreach ($telegramUsers as $telegramUser) {

                        $dataMessage = [
                            'chat_id' => $telegramUser['id'],
                            'parse_mode' => 'html',
                            'text' => $text_promo,
                            'reply_markup' => $this->returnKeyboardBlackFriday(),
                        ];

                        $this->model_telegram_queue->addQueue(false, $dataMessage, 'black_friday');

                    }
                } else {
                    $types = [$queueType];

                    if ($worker === 1) {
                        $types[] = 'default';
                    }

                    foreach ($types as $type) {
                        $queues = $this->model_telegram_queue->getNewQueue(self::QUEUE_BATCH_LIMIT, $type);

                        foreach ($queues as $queue) {
                            if ((int)$queue['status'] === 0) {
                                $this->processQueueItem($queue);
                            }
                        }
                    }

                    if ($worker === 1) {
                        $this->purgeOldQueue();
                    }
                }

            }

        } finally {
            @unlink($lockFile);
        }
    }

    private function processQueueItem($queue)
    {
        $queueId = $queue['id'];
        $data = json_decode($queue['data'], true);
        $chatId = isset($data['chat_id']) ? (int)$data['chat_id'] : 0;

        try {
            if ($queue['has_image']) {
                $this->telegramClient->sendPhoto($data);
            } else {
                $this->telegramClient->sendMessage($data);
            }

            $this->model_telegram_queue->updateQueue($queueId, 1);

            if ($chatId) {
                $this->load->model('customer/customer');
                $this->model_customer_customer->setTelegramUserActive($chatId, 1);
            }
        } catch (Exception $e) {
            $this->load->model('customer/customer');

            if ($chatId && $this->model_customer_customer->isTelegramUnsubscribeError($e->getMessage())) {
                $this->model_customer_customer->setTelegramUserActive($chatId, 0);
                $this->model_telegram_queue->updateQueue($queueId, 2);
            } else {
                $this->model_telegram_queue->markQueueAttemptFailed($queueId, self::QUEUE_MAX_ATTEMPTS);
            }

        }

        usleep(300000);
    }

    private function purgeOldQueue()
    {
        $stampFile = DIR_STORAGE . 'telegram_queue_purge.stamp';

        if (file_exists($stampFile) && filemtime($stampFile) >= strtotime('today')) {
            return;
        }

        $deadline = time() + self::QUEUE_PURGE_SECONDS;

        do {
            $deleted = $this->model_telegram_queue->deleteProcessedOlderThan(self::QUEUE_PURGE_DAYS, self::QUEUE_PURGE_BATCH);

            if ($deleted < self::QUEUE_PURGE_BATCH) {
                touch($stampFile);
                return;
            }
        } while (time() < $deadline);
    }


    private function isWholeNumberDivision($numerator, $denominator)
    {
        // Проверяем, чтобы знаменатель не был равен нулю
        if ($denominator == 0 || $numerator == 0) {
            return false; // деление на ноль недопустимо
        }

        // Если остаток от деления равен нулю, значит результат деления целое число
        return $numerator % $denominator == 0;
    }

    private function returnKeyboard()
    {


        $inline_keyboard = [
            [
                [
                    'text' => self::BTN_GO_TO_SHOPPING,
                    'url' => 'https://getvape.com.ua/specials/'
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
        ];

        $inline_keyboard = array_merge($inline_keyboard, $this->telegram->getSocialKeyboardRows($this->config));

        return json_encode(['inline_keyboard' => $inline_keyboard]);
    }

    private function returnKeyboardBlackFriday()
    {


        $inline_keyboard = [

            [
                [
                    'text' => self::DRAW,
                    "callback_data" => self::DRAW,
                ],
            ],
            [
                [
                    'text' => self::BTN_OUR_STORES,
                    'url' => 'https://qrfy.io/p/iY7zmNbpok'
                ],
            ],
            [
                [
                    'text' => self::SITE,
                    'url' => 'https://getvape.com.ua/'
                ],
            ],
        ];

        $inline_keyboard = array_merge($inline_keyboard, $this->telegram->getSocialKeyboardRows($this->config));

        return json_encode(['inline_keyboard' => $inline_keyboard]);
    }


    private function registerWebhook()
    {


//        $response = $this->telegram->setWebhook([
//            'url' => 'https://getvape.com.ua/telegram-webhook/?token=ehs1O7aV6Dm3FOTHFrGv',
//            'drop_pending_updates' => true
//        ]);

        $response = $this->telegramClient->getWebhookInfo();

        print_r($response);
        die;
    }


    public static function send()
    {
        echo 2;
        die;
    }
}