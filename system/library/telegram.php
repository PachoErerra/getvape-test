<?php

use Telegram\Bot\Api;

class Telegram
{
    private $client;

    public function __construct()
    {
        $this->client = new Api(TELEGRAM_TOKEN);
    }

    public function sendMessage(array $params)
    {
        return $this->client->sendMessage($params);
    }

    public function sendPhoto(array $params)
    {
        return $this->client->sendPhoto($params);
    }

    public function getClient()
    {
        return $this->client;
    }

    public function deleteMessage($chatId, $messageId)
    {
        $url = 'https://api.telegram.org/bot' . TELEGRAM_TOKEN . '/deleteMessage?chat_id=' . rawurlencode($chatId) . '&message_id=' . (int)$messageId;

        $context = stream_context_create([
            'http' => [
                'ignore_errors' => true,
                'timeout' => 5,
            ],
        ]);

        return @file_get_contents($url, false, $context);
    }

    public function getSocialKeyboardRows($config)
    {
        $socials = [
            ['label' => '📸 Instagram', 'url' => $config->get('config_instagram')],
            ['label' => '🎵 TikTok', 'url' => $config->get('config_tiktok')],
            ['label' => '✈️ Telegram', 'url' => $config->get('config_telegram')],
        ];

        $buttons = [];

        foreach ($socials as $social) {
            if (!empty($social['url'])) {
                $buttons[] = [
                    'text' => $social['label'],
                    'url' => $social['url'],
                ];
            }
        }

        if (empty($buttons)) {
            return [];
        }

        return [$buttons];
    }
}