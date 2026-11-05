<?php

use App\Enums\TelegramEnum;
use App\TelegramUser;
use App\User;

class ControllerTelegramWebhook extends Controller
{

    private $telegram;
    private $log_file;

    const BTN_FIND_OUT_BONUSES = '💰 Дізнатися бонуси';
//    const BTN_OUR_STORES = '🛍️ Наші магазини';
    const BTN_PERSONAL_DATA = '👤 Мої бонуси та дані';
    const DRAW = '🎁 Розіграш';
    const BTN_GO_TO_SHOPPING = '🌐 Перейти до покупок';

    const BTN_CONTACT_THE_MANAGER = "📩 Зв'язатися з менеджером";

    const BTN_SEND_PROPOSAL = "📝 Скарги та пропозиції";

    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->library('telegram');

        $this->telegramClient = $registry->get('telegram')->getClient();

        $this->telegram = $registry->get('telegram');

        $this->log_file = DIR_LOGS . 'telegram.log';

        $this->registerCommands();
    }


    public function index()
    {
        try {

            $updates = $this->telegramClient->getWebhookUpdates();

            // Конверти: окремий flow (стикер у TG поки вимкнено)
            $this->load->library('telegram_envelope');
            if ($this->telegram_envelope->handleUpdate($updates, $this->telegramClient)) {
                $this->response->addHeader('Content-Type: text/plain');
                $this->response->setOutput('OK');
                return;
            }

            $this->telegramClient->commandsHandler(true);

            $this->load->model('customer/customer');

            $telegramMessageIds = json_decode(file_get_contents(DIR_LOGS . 'telegram_message_ids.log'), true);

            if (isset($updates['update_id']) && isset($updates['callback_query'])) {

                $callbackQueryData = $updates['callback_query']['data'];

                $chatId = $updates['callback_query']['from']['id'];

                $message_id = $updates['callback_query']['message']['message_id'];

                switch ($callbackQueryData) {

                    case self::BTN_FIND_OUT_BONUSES:

                        $this->telegram->deleteMessage($chatId, $message_id);

                        $telegramUserId = $this->model_customer_customer->getTelegramCustomerByChatId($chatId);

                        if ($telegramUserId) {

                            $userID = $this->model_customer_customer->getUserIdByTelegramCustomerId($telegramUserId);

                            if ($userID) {

                                if ($telegramMessageIds && isset($telegramMessageIds[$chatId]) && $telegramMessageIds[$chatId]) {
                                    $this->telegram->deleteMessage($chatId, $telegramMessageIds[$chatId]);
                                }

                                $phone = $this->model_customer_customer->getCustomerPhoneById($userID);


                                $balanceInfo = $this->getBalanceInfoByPhone($phone);


                                if ($balanceInfo) {

                                    $text = '💰Твій баланс: ' . ($balanceInfo['balance'] ? $balanceInfo['balance'] : 0) . ' ₴' . PHP_EOL . PHP_EOL;

                                    if (isset($balanceInfo['discount']) && $balanceInfo['discount']) {
                                        $text .= '🤑 Твоя знижка: ' . $balanceInfo['discount'] . '%' . PHP_EOL . PHP_EOL;
                                    }

                                    $text .= '🛍 Повертайся до нас знов, та витрачай накопичені бонуси';

                                    $response = $this->telegramClient->sendMessage([
                                        'chat_id' => $chatId,
                                        'text' => $text,
                                        'reply_markup' => $this->returnNewDefaultKeyboard($chatId, false),
                                    ]);

                                    $messageId = $response->getMessageId();

                                    if ($telegramMessageIds) {
//                                    $telegramMessageIds = json_decode($telegramMessageIds, true);
                                        $telegramMessageIds[$chatId] = $messageId;
                                    } else {
                                        $telegramMessageIds[$chatId] = $messageId;
                                    }

                                    file_put_contents(DIR_LOGS . 'telegram_message_ids.log', json_encode($telegramMessageIds));
                                    return;
                                }
                            }
                        }

                        $response = $this->telegramClient->sendMessage([
                            'chat_id' => $chatId,
                            'text' => 'Виникла помилка, спробуйте пізніше',
                            'reply_markup' => $this->returnNewDefaultKeyboard($chatId),
                        ]);

                        $messageId = $response->getMessageId();

                        if ($telegramMessageIds) {
//                        $telegramMessageIds = json_decode($telegramMessageIds, true);
                            $telegramMessageIds[$chatId] = $messageId;
                        } else {
                            $telegramMessageIds[$chatId] = $messageId;
                        }

                        file_put_contents(DIR_LOGS . 'telegram_message_ids.log', json_encode($telegramMessageIds));

                        break;

                    case self::BTN_PERSONAL_DATA:

                        $callbackQueryId = $updates['callback_query']['id'];


                        $userID = $this->model_customer_customer->getUserIdByTelegramCustomerId($chatId);

                        $customerInfo = $this->model_customer_customer->getCustomerInfo($userID);

                        if ($customerInfo) {

                            $text = $customerInfo['firstname'] . ' ' . $customerInfo['lastname'] . (($customerInfo['fax']) ? ' (' . date('d.m.Y', strtotime($customerInfo['fax'])) . ')' : '') . PHP_EOL . PHP_EOL;


                            $phone = $this->model_customer_customer->getCustomerPhoneById($userID);

                            $balanceInfo = $this->getBalanceInfoByPhone($phone);

                            if ($balanceInfo) {
                                $text .= '💰Твій баланс: ' . ($balanceInfo['balance'] ? $balanceInfo['balance'] : 0) . ' ₴' . PHP_EOL . PHP_EOL;

                                if (isset($balanceInfo['discount']) && $balanceInfo['discount']) {
                                    $text .= '🤑 Твоя знижка: ' . $balanceInfo['discount'] . '%' . PHP_EOL . PHP_EOL;
                                }

                            } else {
                                $text .= 'Інформація про баланс поки відсутня, спробуйте пізніше' . PHP_EOL . PHP_EOL;

                            }

                            $this->tlgrm('answerCallbackQuery', ['callback_query_id' => $callbackQueryId, 'text' => $text, 'show_alert' => true], $chatId);

                        }

                        break;

                    case self::DRAW:

                        $callbackQueryId = $updates['callback_query']['id'];

                        $userID = $this->model_customer_customer->getUserIdByTelegramCustomerId($chatId);

                        $customerInfo = $this->model_customer_customer->getCustomerInfo($userID);

                        if ($this->config->get('config_bfmenu2025')) {


                            if ($customerInfo) {

                                $phone = $this->model_customer_customer->getCustomerPhoneById($userID);

                                $result = $this->getBlackFridayPromoCode($phone);

                                if ($result && isset($result['code']) && $result['code']) {
                                    $text = "🎁 Сюрприз! Ви спіймали секретний подарунок! \nВведіть промокод на сайті під час замовлення та автоматично станьте учасником головного розіграшу від Vapors та Punch 30.12. \n\nВаш промокод:";
//
//                                    $this->telegramClient->sendPhoto([
//                                        'chat_id' => $chatId,
//                                        'photo' => 'https://getvape.com.ua/image/catalog/site/vapors-bf-2025.jpg',
//                                        'caption' => $text,
//                                    ]);

                                    $this->telegramClient->sendMessage([
                                        'chat_id' => $chatId,
                                        'text' => $text,
                                    ]);


                                    // Отдельное сообщение только с кодом
                                    $this->telegramClient->sendMessage([
                                        'chat_id' => $chatId,
                                        'parse_mode' => 'Markdown',
                                        'text' => '`' . $result['code'] . '`',
                                        'reply_markup' => $this->returnNewDefaultKeyboard($chatId),
                                    ]);
                                } else {
                                    $text = $result['error'] ?? 'Виникла помилка, спробуйте пізніше';

                                    $this->telegramClient->sendMessage([
                                        'chat_id' => $chatId,
                                        'text' => $text,
                                        'reply_markup' => $this->returnNewDefaultKeyboard($chatId),
                                    ]);
                                }

                                //$this->tlgrm('answerCallbackQuery', ['callback_query_id' => $callbackQueryId, 'text' => $text, 'show_alert' => true], $chatId);
                            }
                        } else {
//                            $this->tlgrm('answerCallbackQuery', ['callback_query_id' => $callbackQueryId, 'text' => '⏳ Розіграш уже закінчився.
//Слідкуй за ботом, попереду ще багато подарунків!', 'show_alert' => true], $chatId);

                            $this->telegramClient->sendMessage([
                                'chat_id' => $chatId,
                                'text' => '⏳ Розіграш уже закінчився.
Слідкуй за ботом, попереду ще багато подарунків!',
                                'reply_markup' => $this->returnNewDefaultKeyboard($chatId),
                            ]);

                        }
                        break;

                    default:
                        $data = json_decode($updates['callback_query']['data'], true);

                        $sessionId = $data['session_id'];

                        $telegramUserId = $this->model_customer_customer->getTelegramCustomerByChatId($chatId);

                        $userID = $this->model_customer_customer->getUserIdByTelegramCustomerId($telegramUserId);

                        $this->session->start($sessionId);

                        $this->session->data['login_user'] = $userID;

                        $message_id = $updates['callback_query']['message']['message_id'];

                        $this->telegram->deleteMessage($chatId, $message_id);

                        $token = bin2hex(random_bytes(16));

                        $tokenExpiresAt = time() + 15 * 60;  // +15 хв

                        $this->model_customer_customer->updateTelegramLoginTokenByUserId($userID, $token, $tokenExpiresAt);

                        $response = $this->telegramClient->sendMessage([
                            'chat_id' => $chatId,
//                            'text' => '✅ Ви увійшли в особистий кабінет, будь ласка, [поверніться на сайт](https://getvape.com.ua/specials/?login_token=' . $token . ') для продовження покупок.',
                            'text' => '✅ Ви увійшли в особистий кабінет, будь ласка, [поверніться на сайт](https://getvape.com.ua/specials/) для продовження покупок.',
                            'parse_mode' => 'Markdown',
                            'reply_markup' => $this->returnNewDefaultKeyboard($chatId, true, $token),
                        ]);

                        $messageId = $response->getMessageId();

                        if ($telegramMessageIds) {
//                        $telegramMessageIds = json_decode($telegramMessageIds, true);
                            $telegramMessageIds[$chatId] = $messageId;
                        } else {
                            $telegramMessageIds[$chatId] = $messageId;
                        }

                        $this->model_customer_customer->setTGlogin($telegramUserId);

                        file_put_contents(DIR_LOGS . 'telegram_message_ids.log', json_encode($telegramMessageIds));

                        $ch = curl_init('https://telegram.getvape.com.ua/telegram-login');
                        curl_setopt($ch, CURLOPT_POST, true);
                        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
                            'session_id' => $sessionId,
                            'user_id' => $userID,
                        ]));
                        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                        $response = curl_exec($ch);
                        curl_close($ch);


                }

            } else { 

                if (isset($updates['update_id']) && isset($updates['my_chat_member']['from'])) {

                    $from = $this->model_customer_customer->telegramPayloadFrom($updates['my_chat_member']['from']);
                    $chatId = !empty($from['id']) ? (int)$from['id'] : 0;
                    $memberStatus = isset($updates['my_chat_member']['new_chat_member']['status'])
                        ? $updates['my_chat_member']['new_chat_member']['status']
                        : '';

                    if ($chatId) {
                        if (!$this->model_customer_customer->getTelegramCustomerByChatId($chatId)) {
                            $this->model_customer_customer->createTelegramCustomer($from);
                        }

                        if (in_array($memberStatus, ['kicked', 'left'], true)) {
                            $this->model_customer_customer->setTelegramUserActive($chatId, 0);
                        } elseif (in_array($memberStatus, ['member', 'restricted'], true)) {
                            $this->model_customer_customer->setTelegramUserActive($chatId, 1);
                            $this->telegramClient->commandsHandler(true);
                        }

                    }

                } else {
                    if (isset($updates['message'])) {
                        if (isset($updates['message']['contact']) && $updates['message']['contact']['phone_number']) {

                            $phone = $updates['message']['contact']['phone_number'];

                            $user_id = $updates['message']['contact']['user_id'];

                            $chatId = $updates['message']['from']['id'];

                            $message_id = $updates['message']['message_id'];
                            $this->telegramBindDebug('contact_received', [
                                'chat_id' => $chatId,
                                'contact_user_id' => $user_id,
                                'phone_raw' => $phone,
                            ]);

                            if ($user_id != $chatId) {
                                $this->telegramBindDebug('contact_rejected_foreign', [
                                    'chat_id' => $chatId,
                                    'contact_user_id' => $user_id,
                                ], 'WARN');
                                $this->telegramClient->sendMessage([
                                    'chat_id' => $chatId,
                                    'text' => 'Виникла помилка, надішліть будь ласка особистий контакт',
                                ]);

                                $this->telegram->deleteMessage($chatId, $message_id);

                            } else {

                                $phone = preg_replace('~[^0-9]~', '', $phone);
                                $this->telegramBindDebug('contact_normalized', [
                                    'chat_id' => $chatId,
                                    'phone_digits' => $phone,
                                ]);

                                $customer_id = $this->model_customer_customer->getCustomerIdByTelephoneNormalized($phone);
                                $this->telegramBindDebug('customer_resolved', [
                                    'chat_id' => $chatId,
                                    'customer_id' => $customer_id,
                                ], $customer_id ? 'INFO' : 'WARN');


                                if (!$customer_id) {
                                    $this->load->model('extension/module/regsms');

                                    $customer_data = array(
                                        'firstname' => $updates['message']['from']['first_name'],
                                        'lastname' => $updates['message']['from']['last_name'],
                                        'fax' => '',
                                        'telephone' => '+' . $phone,
                                    );

                                    $customer_id = $this->model_extension_module_regsms->addCustomer($customer_data);
                                    $this->telegramBindDebug('customer_created', [
                                        'chat_id' => $chatId,
                                        'customer_id' => $customer_id,
                                        'telephone' => '+' . $phone,
                                    ]);

                                    $address_data = array(
                                        'firstname' => $updates['message']['from']['first_name'],
                                        'lastname' => $updates['message']['from']['last_name'],
                                        'telephone' => '+' . $phone,
                                        'zone_id' => $this->config->get('config_zone_id'),
                                        'country_id' => $this->config->get('config_country_id'),
                                        'default' => true
                                    );

                                    $this->model_extension_module_regsms->addAddress($customer_id, $address_data);

                                }
 

                                $telegramUserId = $this->model_customer_customer->getTelegramCustomerByChatId($chatId);
                                $this->telegramBindDebug('telegram_user_lookup', [
                                    'chat_id' => $chatId,
                                    'telegram_user_id' => $telegramUserId,
                                ], $telegramUserId ? 'INFO' : 'WARN');

                                if (!$telegramUserId) {
                                    $from = $this->model_customer_customer->telegramPayloadFrom($updates['message']['from']);
                                    $tgId = !empty($from['id']) ? (int)$from['id'] : (int)$chatId;

                                    if ($tgId) {
                                        if (empty($from['id'])) {
                                            $from['id'] = $tgId;
                                        }

                                        $this->model_customer_customer->createTelegramCustomer($from, $customer_id);
                                        $this->telegramBindDebug('telegram_user_created', [
                                            'chat_id' => $chatId,
                                            'telegram_user_id' => $tgId,
                                            'customer_id' => $customer_id,
                                        ]);
                                    }

                                    $telegramUserId = $this->model_customer_customer->getTelegramCustomerByChatId($chatId);
                                    $this->telegramBindDebug('telegram_user_lookup_after_create', [
                                        'chat_id' => $chatId,
                                        'telegram_user_id' => $telegramUserId,
                                    ], $telegramUserId ? 'INFO' : 'WARN');
                                }

                                if ($telegramUserId && $customer_id) {
                                    $this->model_customer_customer->updateTelegramCustomerUserId($customer_id, $telegramUserId);
                                    $this->model_customer_customer->setTelegramUserActive($telegramUserId, 1);
                                    $this->telegramBindDebug('bind_success', [
                                        'chat_id' => $chatId,
                                        'telegram_user_id' => $telegramUserId,
                                        'customer_id' => $customer_id,
                                    ]);
                                }
//

                                if ($telegramMessageIds && isset($telegramMessageIds[$chatId]) && $telegramMessageIds[$chatId]) {
                                    $this->telegram->deleteMessage($chatId, $telegramMessageIds[$chatId]);
                                }


                                $this->telegram->deleteMessage($chatId, $message_id);


                                $this->telegramBindDebug('sendTelegramUserLoginMessage', [
                                    'chat_id' => $chatId,
                                    'telegram_user_id' => $telegramUserId,
                                    'customer_id' => $customer_id,
                                ]);

                                if ($telegramUserId) {
                                    $this->load->library('telegram_envelope');
                                    $envelopeResumed = $this->telegram_envelope->resumeAfterAuth(
                                        $chatId,
                                        (int)$customer_id,
                                        $this->telegramClient
                                    );

                                    if (!$envelopeResumed) {
                                        $this->sendTelegramUserLoginMessage($telegramUserId);
                                    }
                                }
                            }
                        } else {
                            $telegram = $updates['message'];
                            $text = $telegram['text'];
                            $chatId = $telegram['from']['id'];
                            switch ($text) {
                                case '/start':


                                    if (!$telegramUserId = $this->model_customer_customer->getTelegramCustomerByChatId($chatId)) {
                                        $from = $this->model_customer_customer->telegramPayloadFrom($updates['message']['from']);
                                        $tgId = !empty($from['id']) ? (int)$from['id'] : (int)$chatId;

                                        if ($tgId) {
                                            if (empty($from['id'])) {
                                                $from['id'] = $tgId;
                                            }

                                            $this->model_customer_customer->createTelegramCustomer($from);
                                        }

                                        $telegramUserId = $this->model_customer_customer->getTelegramCustomerByChatId($chatId);

                                        $this->telegramClient->commandsHandler(true);

                                    } else {
                                        if ($telegramUserId) {

                                            $userID = $this->model_customer_customer->getUserIdByTelegramCustomerId($telegramUserId);


                                            if (!$userID) {

                                                $this->telegramClient->commandsHandler(true);
                                            } else {
//                                                $this->telegramClient->sendMessage([
//                                                    'chat_id' => $telegramUserId,
//                                                    'text' => 'Вітаємо!',
//                                                ]);
                                            }
                                        } else {

                                            $this->telegramClient->commandsHandler(true);
                                        }
                                    }
                                    break;

                                case 'Войти':
                                    // login
                                    $response = $this->telegramClient->sendMessage([
                                        'chat_id' => $telegram['from']['id'],
                                        'text' => 'Ви увійшли',
                                    ]);
                                    $messageId = $response->getMessageId();
                                    $chatId = $response['chat']['id'];
                                    sleep(10);
                                    $this->telegram->deleteMessage($chatId, $messageId);
                                    break;
                                case self::BTN_FIND_OUT_BONUSES:

                                    $telegramUserId = $this->model_customer_customer->getTelegramCustomerByChatId($chatId);

                                    if ($telegramUserId) {

                                        $userID = $this->model_customer_customer->getUserIdByTelegramCustomerId($telegramUserId);

                                        if ($userID) {


                                            $phone = $this->model_customer_customer->getCustomerPhoneById($userID);


                                            $balanceInfo = $this->getBalanceInfoByPhone($phone);


                                            if ($balanceInfo) {
                                                $inline_keyboard = [
                                                    [
                                                        [
                                                            'text' => 'Використати',
                                                            'url' => 'https://getvape.com.ua/specials/',
                                                        ],
                                                    ],
                                                ];

                                                $reply_markup = json_encode(['inline_keyboard' => $inline_keyboard]);

                                                $response = $this->telegramClient->sendMessage([
                                                    'chat_id' => $chatId,
                                                    'text' => 'На вашому бонусному рахунку: ' . ($balanceInfo['balance'] ? $balanceInfo['balance'] : 0) . ' ₴',
                                                    'reply_markup' => $reply_markup,
                                                ]);
                                                return;
                                            }
                                        }
                                    }

                                    $response = $this->telegramClient->sendMessage([
                                        'chat_id' => $chatId,
                                        'text' => 'Виникла помилка, спробуйте пізніше',
                                        'reply_markup' => $this->returnNewDefaultKeyboard($chatId),
                                    ]);

                                    break;
                                default:
//                                    $response = $this->telegramClient->sendMessage([
//                                        'chat_id' => $chatId,
//                                        'text' => '1',
//                                        'reply_markup' => $this->returnNewDefaultKeyboard($chatId),
//                                    ]);
                                    break;
                            }
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->telegramBindDebug('webhook_exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ], 'ERROR');
        }

        $this->response->addHeader('Content-Type: text/plain');
        $this->response->setOutput('OK');
    }


    private function setLog($text)
    {
        file_put_contents($this->log_file, '-------' . date('Y-m-d H:i:s') . '-------' . PHP_EOL . $text . PHP_EOL, FILE_APPEND);
    }

    /**
     * JSONL debug-лог привʼязки Telegram-контакту до customer.
     */
    private function telegramBindDebug($event, array $ctx = [], $level = 'INFO')
    {
        $record = [
            'ts' => date('c'),
            'level' => $level,
            'event' => $event,
            'route' => $this->request->get['route'] ?? null,
            'ctx' => $ctx,
        ];

        file_put_contents(
            DIR_LOGS . 'telegram_webhook_bind.log',
            json_encode($record, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
            FILE_APPEND
        );
    }

    private function registerCommands()
    {
        include 'start-command.php';

        $command = new \Telegram\start\StartCommand();

        $response = $this->telegramClient->addCommand($command);

    }

    private function returnNewDefaultKeyboard($chatId, $backToSite = true, $loginToken = null)
    {

        if ($backToSite) {
            $firstBtn = [
                [
                    'text' => self::BTN_FIND_OUT_BONUSES,
                    "callback_data" => self::BTN_FIND_OUT_BONUSES,
                ]
            ];
//            $secondBtn = [
//                [
//                    'text' => '🌐 Повернутись на сайт',
//                    'url' => 'https://getvape.com.ua/edit-account/',
//                ]
//            ];
        } else {
            $firstBtn = [
                [
                    'text' => '💰 Використати бонуси',
                    'url' => 'https://getvape.com.ua/specials/',
                ]
            ];
        }

        $drawBtn = [];

        // TODO проверка на черную пятницу

        if ($this->config->get('config_bfmenu2025')) {
//        if (($chatId == '382620651' || $chatId == '277196404' || $chatId == '6567921484' || $chatId == '401535014' || $chatId == '343807274') && $this->config->get('config_bfmenu2025')) {

//        if ($chatId == '277196404') {
            $drawBtn = [
                [
                    'text' => self::DRAW,
                    "callback_data" => self::DRAW,
                ],
            ];
        }

        $inline_keyboard = [
            [
                [
                    'text' => self::BTN_GO_TO_SHOPPING,
//                    'url' => 'https://getvape.com.ua/specials/' . (($loginToken) ? '?login_token=' . $loginToken : ''),
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

        array_unshift($inline_keyboard, $firstBtn);

        if ($drawBtn) {
            array_unshift($inline_keyboard, $drawBtn);
        }

        return json_encode(['inline_keyboard' => $inline_keyboard]);
    }

    private function sendTelegramUserLoginMessage($chatId)
    {

        try {


            $response = $this->telegramClient->sendMessage([
                'chat_id' => $chatId,
                'text' => 'Дякую, що підписалися! Тепер ви можете авторизуватися на сайті',
                'reply_markup' => $this->returnNewDefaultKeyboard($chatId),
            ]);

            $messageId = $response->getMessageId();

            $telegramMessageIds = json_decode(file_get_contents(DIR_LOGS . 'telegram_message_ids.log'), true);

            if ($telegramMessageIds) {
//            $telegramMessageIds = json_decode($telegramMessageIds, true);
                $telegramMessageIds[$chatId] = $messageId;
            } else {
                $telegramMessageIds[$chatId] = $messageId;
            }

            file_put_contents(DIR_LOGS . 'telegram_message_ids.log', json_encode($telegramMessageIds));

//
//        $chatId = $response['chat']['id'];
//        sleep(10);
//        $this->deleteMessage($chatId, $messageId);
        } catch (Exception $e) {

        }
    }

    private function getBalanceInfoByPhone($telephone)
    {
        $this->load->library('balance_api');

        return $this->balance_api->getBalanceInfoByPhone($telephone);
    }

    private function getBlackFridayPromoCode($telephone)
    {

        // TODO Черная пятница

        $url = 'https://getvape.com.ua/index.php?route=extension/feed/blackfriday2025/draw&http_pass=jt8Fk5jFht84GJt';
        $telephone = str_replace(['(', ')', '-', ' '], '', $telephone);

        $data = array(
            "telephone" => $telephone,
            "token" => "sbmd2y%#ifR1bQV1Mn0YYXe43QcgMSxycoXbutwwr5k="
        );

        $request = json_encode($data);

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
                $this->setLog('CURL failed ' . $curl_error . '(' . $curl_errno . ')');
                return false;
            }
            if ($aInfo["http_code"] != '200') {

                $this->setLog('HTTP failed ' . $aInfo["http_code"] . '(' . $response . ')');

                return false;
            }


            return json_decode($response, true);

        } catch (Exception $e) {
            return false;
        }

    }

    private function checkShowBalance($userID)
    {

        $lastRequest = $this->cache->get($userID . '_last_request');

        $timeNow = time();

        if (!$lastRequest || ($timeNow - $lastRequest) > 300) {

            $this->cache->set($userID . '_last_request', $timeNow);

            return true;
        }
        return false;
    }

    private function tlgrm($method, $data, $client)
    {
        $data["chat_id"] = $client;
        $data["parse_mode"] = "html";
        $ch = curl_init("https://api.telegram.org/bot" . TELEGRAM_TOKEN . "/$method");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $r = json_decode(curl_exec($ch), true);
        curl_close($ch);

        return $r;
    }

}