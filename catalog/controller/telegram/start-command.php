<?php

namespace Telegram\start;

use Telegram\Bot\Api;
use Telegram\Bot\Commands\Command;

class StartCommand extends Command
{

    /**
     * @var string Command Name
     */
    protected $name = 'start';

    /**
     * @var string Command Description
     */
    protected $description = 'Команда для старта работы с GetVape';

    /**
     * {@inheritdoc}
     */
    public function handle($arguments)
    {

        $telegram = new Api(TELEGRAM_TOKEN);

        $updates = $telegram->getWebhookUpdates();

        $chat_id = $updates['message']['from']['id'];

        $keyboard = [
            [
                [
                    'text' => '➡️ Поділитись контактом',
                    'request_contact' => true,
                ]
            ],
        ];
        $reply_markup = $telegram->replyKeyboardMarkup([
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'one_time_keyboard' => true
        ]);

        try {


            $response = $telegram->sendMessage([
                'chat_id' => $chat_id,
                'text' => '👋 Вітаємо!' . PHP_EOL . 'Натисніть "Поділитись контактом" ⤵️, щоб отримати доступ до переваг програми лояльності Vapors.' . PHP_EOL . '⚠️ Лише важливі повідомлення: замовлення та відповіді на запитання.',
                'reply_markup' => $reply_markup
            ]);

            $messageId = $response->getMessageId();

            $telegramMessageIds = [];

            $telegramMessageIds = file_get_contents(DIR_LOGS . 'telegram_message_ids.log');

            if ($telegramMessageIds) {
                $telegramMessageIds = json_decode($telegramMessageIds, true);
                $telegramMessageIds[$chat_id] = $messageId;
            } else {
                $telegramMessageIds[$chat_id] = $messageId;
            }

            file_put_contents(DIR_LOGS . 'telegram_message_ids.log', json_encode($telegramMessageIds));
        } catch (\Exception $e) {

        }
    }
}