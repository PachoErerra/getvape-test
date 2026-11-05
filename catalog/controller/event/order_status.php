<?php

class ControllerEventOrderStatus extends Controller
{
    const REPLACE_SHORT_CODES = ['[order_id]', '[ttn]', '[departure]', '[post_office_addresses]', '[bonus]'];
    const SHIPPING_CODE_NP_DEPARTMENT = 'novaposhta.department';
    const SHIPPING_CODE_NP_POSHTOMAT = 'novaposhta.poshtomat';
    const SHIPPING_CODE_NP_TO_DOR = 'novaposhta.doors';
    const SHIPPING_CODE_COURIER_LVIV = 'flat.flat';

    public function onStatusChange($route, $args, $output)
    {
        // Никогда не роняем/не подвешиваем checkout из-за Telegram/balance.
        try {
            $this->notifyTelegram($args);
        } catch (\Throwable $e) {
            try {
                $this->log->write('order_status telegram notify failed: ' . $e->getMessage());
            } catch (\Throwable $ignored) {
            }
        }
    }

    private function notifyTelegram($args)
    {
        $order_id = $args[0] ?? 0;
        $order_status_id = $args[1] ?? 0;

        if (!$order_id || !$order_status_id) {
            return;
        }

        $this->load->model('checkout/order');
        $this->load->model('setting/setting');

        $order_info = $this->model_checkout_order->getOrder($order_id);

        if (!$order_info || empty($order_info['customer_id'])) {
            return;
        }

        $date_added = $order_info['date_added'];
        $currentDate = new DateTime();
        $targetDate = new DateTime($date_added);
        $interval = $currentDate->diff($targetDate);

        if ($targetDate > $currentDate || $interval->m >= 1 || $interval->y !== 0) {
            return;
        }

        $old_status_id = $this->model_checkout_order->getLastOrderStatus($order_id);

        if ($old_status_id == $order_status_id) {
            return;
        }

        $this->load->model('customer/customer');

        // Без фильтра is_active здесь не критично для UX checkout — но если колонки нет, не валим запрос.
        $telegramUserId = $this->model_customer_customer->getTelegramCustomerByCustomerId($order_info['customer_id']);

        if (!$telegramUserId) {
            return;
        }

        $status_messages = json_decode($this->model_setting_setting->getSettingValue('status_message'), true);
        $shipping_code = $order_info['shipping_code'];
        $message = '';
        $link_name = '';
        $link = '';

        switch ($order_status_id) {
            case 2: // В обробці
            case 5: // Завершено
                $message = $status_messages[$order_status_id]['message'] ?? null;
                $link_name = $status_messages[$order_status_id]['link_name'] ?? null;
                $link = $status_messages[$order_status_id]['link'] ?? null;
                break;

            case 18: // Готовий до відправлення
            case 3: // Доставлено
                $key = '';

                if ($shipping_code == self::SHIPPING_CODE_NP_DEPARTMENT) {
                    $key = 'np_department';
                } elseif ($shipping_code == self::SHIPPING_CODE_NP_POSHTOMAT) {
                    $key = 'np_poshtomat';
                } elseif ($shipping_code == self::SHIPPING_CODE_NP_TO_DOR) {
                    $key = 'np_courier';
                } elseif ($shipping_code == self::SHIPPING_CODE_COURIER_LVIV) {
                    $key = 'courier_lviv';
                }

                $message = $status_messages[$order_status_id][$key]['message'] ?? null;
                $link_name = $status_messages[$order_status_id][$key]['link_name'] ?? null;
                $link = $status_messages[$order_status_id][$key]['link'] ?? null;
                break;
        }

        if (!$message) {
            return;
        }

        $ttn = $order_info['novaposhta_cn_number'];
        $departure = $order_info['shipping_city'] . ', ' . $order_info['shipping_address_1'];
        $post_office_addresses = $order_info['shipping_address_1'] ?? $order_info['shipping_address_2'];

        $balance = '(Інформація про баланс поки відсутня)';
        $needsBonus = (strpos($message, '[bonus]') !== false)
            || (strpos((string)$link_name, '[bonus]') !== false)
            || (strpos((string)$link, '[bonus]') !== false);

        if ($needsBonus) {
            $this->load->library('balance_api');
            $balanceInfo = $this->balance_api->getBalanceInfoByPhone($order_info['telephone']);

            if ($balanceInfo) {
                $balance = ($balanceInfo['balance'] ? $balanceInfo['balance'] : 0) . ' ₴';
            }
        }

        $message = str_replace(self::REPLACE_SHORT_CODES, [$order_id, $ttn, $departure, $post_office_addresses, $balance], $message);
        $link_name = str_replace(self::REPLACE_SHORT_CODES, [$order_id, $ttn, $departure, $post_office_addresses, $balance], $link_name);
        $link = str_replace(self::REPLACE_SHORT_CODES, [$order_id, $ttn, $departure, $post_office_addresses, $balance], $link);

        $data = [
            'chat_id' => $telegramUserId,
            'text' => $message,
        ];

        if ($link_name && $link) {
            $data['reply_markup'] = json_encode([
                'inline_keyboard' => [[
                    [
                        'text' => $link_name,
                        'url' => $link,
                    ],
                ]],
            ]);
        }

        $this->load->library('telegram');
        $telegramClient = $this->registry->get('telegram')->getClient();

        if (method_exists($telegramClient, 'setTimeOut')) {
            $telegramClient->setTimeOut(5);
        }

        if (method_exists($telegramClient, 'setConnectTimeOut')) {
            $telegramClient->setConnectTimeOut(3);
        }

        $telegramClient->sendMessage($data);
    }
}
