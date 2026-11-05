<?php

use Telegram\Bot\Api;

class ControllerTelegramApi extends Controller
{
    /** @var Log|null */
    private $apiLogFile;


    public function __construct($registry)
    {
        parent::__construct($registry);

        $this->load->library('telegram');

        $this->telegramClient = $registry->get('telegram')->getClient();
    }


    public function send()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(405, ['success' => false, 'error' => 'method_not_allowed', 'message' => 'Only POST requests are allowed']);
            return;
        }

        if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
            header('WWW-Authenticate: Basic realm="Restricted Area"');
            $this->jsonResponse(401, ['success' => false, 'error' => 'unauthorized', 'message' => 'Authorization required']);
            return;
        }

        if ($_SERVER['PHP_AUTH_USER'] !== TELEGRAM_API_LOGIN || $_SERVER['PHP_AUTH_PW'] !== TELEGRAM_API_PASSWORD) {
            $this->apiLog('Invalid credentials from IP ' . $this->getClientIp());
            $this->jsonResponse(403, ['success' => false, 'error' => 'invalid_credentials', 'message' => 'Invalid credentials']);
            return;
        }

        $json = file_get_contents('php://input');
        $data = json_decode($json, true);

        if (!$data) {
            $this->jsonResponse(400, ['success' => false, 'error' => 'invalid_json', 'message' => 'Invalid JSON']);
            return;
        }

        if (!isset($data['phone']) || !$data['phone'] || !isset($data['message']) || !$data['message']) {
            $this->jsonResponse(400, ['success' => false, 'error' => 'validation_error', 'message' => 'Phone and message are required']);
            return;
        }

        $rawPhone = (string)$data['phone'];
        $message = (string)$data['message'];
        $phone = $this->normalizeAndValidateTelephone($rawPhone);

        if (!$phone) {
            $this->apiLog('Invalid phone: ' . $rawPhone);
            $this->recordApiSend([
                'phone' => $rawPhone,
                'message' => $message,
                'status' => 'invalid_phone',
                'error' => 'Invalid telephone number',
            ]);
            $this->jsonResponse(400, ['success' => false, 'error' => 'invalid_phone', 'message' => 'Invalid telephone number']);
            return;
        }

        $this->load->model('customer/customer');

        $customer_id = $this->model_customer_customer->getCustomerIdByTelephoneNormalized($phone);

        if (!$customer_id) {
            $this->apiLog('customer_not_found phone=' . $phone);
            $this->recordApiSend([
                'phone' => $phone,
                'message' => $message,
                'status' => 'customer_not_found',
                'error' => 'Customer with this phone number was not found',
            ]);
            $this->jsonResponse(404, ['success' => false, 'error' => 'customer_not_found', 'message' => 'Customer with this phone number was not found']);
            return;
        }

        $telegramUserId = $this->model_customer_customer->getTelegramCustomerByCustomerId($customer_id);

        if (!$telegramUserId) {
            $inactiveTelegramUserId = $this->model_customer_customer->getTelegramCustomerByCustomerId($customer_id, false);

            if ($inactiveTelegramUserId) {
                $this->apiLog('unsubscribed phone=' . $phone . ' customer_id=' . $customer_id . ' chat_id=' . $inactiveTelegramUserId);
                $this->recordApiSend([
                    'phone' => $phone,
                    'customer_id' => $customer_id,
                    'chat_id' => $inactiveTelegramUserId,
                    'message' => $message,
                    'status' => 'unsubscribed',
                    'error' => 'Customer unsubscribed from Telegram bot',
                ]);
                $this->jsonResponse(200, ['success' => false, 'error' => 'unsubscribed', 'message' => 'Customer unsubscribed from Telegram bot']);
                return;
            }

            $this->apiLog('no_telegram phone=' . $phone . ' customer_id=' . $customer_id);
            $this->recordApiSend([
                'phone' => $phone,
                'customer_id' => $customer_id,
                'message' => $message,
                'status' => 'no_telegram',
                'error' => 'Customer is not subscribed to Telegram bot',
            ]);
            $this->jsonResponse(200, ['success' => false, 'error' => 'no_telegram', 'message' => 'Customer is not subscribed to Telegram bot']);
            return;
        }

        try {
            $response = $this->telegramClient->sendMessage([
                'chat_id' => $telegramUserId,
                'text' => $message,
            ]);

            if ($response) {
                $this->model_customer_customer->setTelegramUserActive($telegramUserId, 1);
                $this->apiLog('sent phone=' . $phone . ' customer_id=' . $customer_id . ' chat_id=' . $telegramUserId . ' message_length=' . mb_strlen($message));
                $this->recordApiSend([
                    'phone' => $phone,
                    'customer_id' => $customer_id,
                    'chat_id' => $telegramUserId,
                    'message' => $message,
                    'status' => 'sent',
                ]);
                $this->jsonResponse(200, ['success' => true, 'channel' => 'telegram']);
                return;
            }

            $this->apiLog('telegram_send_failed phone=' . $phone . ' customer_id=' . $customer_id . ' chat_id=' . $telegramUserId . ' reason=empty_response');
            $this->recordApiSend([
                'phone' => $phone,
                'customer_id' => $customer_id,
                'chat_id' => $telegramUserId,
                'message' => $message,
                'status' => 'telegram_send_failed',
                'error' => 'empty_response',
            ]);
        } catch (Exception $exception) {
            if ($this->model_customer_customer->isTelegramUnsubscribeError($exception->getMessage())) {
                $this->model_customer_customer->setTelegramUserActive($telegramUserId, 0);
                $this->apiLog('unsubscribed phone=' . $phone . ' customer_id=' . $customer_id . ' chat_id=' . $telegramUserId . ' error=' . $exception->getMessage());
                $this->recordApiSend([
                    'phone' => $phone,
                    'customer_id' => $customer_id,
                    'chat_id' => $telegramUserId,
                    'message' => $message,
                    'status' => 'unsubscribed',
                    'error' => $exception->getMessage(),
                ]);
                $this->jsonResponse(200, ['success' => false, 'error' => 'unsubscribed', 'message' => 'Customer unsubscribed from Telegram bot']);
                return;
            }

            $this->apiLog('telegram_send_failed phone=' . $phone . ' customer_id=' . $customer_id . ' chat_id=' . $telegramUserId . ' error=' . $exception->getMessage());
            $this->recordApiSend([
                'phone' => $phone,
                'customer_id' => $customer_id,
                'chat_id' => $telegramUserId,
                'message' => $message,
                'status' => 'telegram_send_failed',
                'error' => $exception->getMessage(),
            ]);
        }

        $this->jsonResponse(502, ['success' => false, 'error' => 'telegram_send_failed', 'message' => 'Failed to send Telegram message']);
    }

    private function recordApiSend(array $data)
    {
        try {
            $this->load->model('telegram/api_send');
            $data['ip'] = $this->getClientIp();
            $this->model_telegram_api_send->add($data);
        } catch (Exception $e) {
            $this->apiLog('record_api_send_failed error=' . $e->getMessage());
        }
    }

    private function apiLog($message)
    {
        if (!$this->apiLogFile) {
            $this->apiLogFile = new Log('telegram_api.log');
        }

        $this->apiLogFile->write($message);
    }

    private function getClientIp()
    {
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);

            return trim($ips[0]);
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    private function jsonResponse($statusCode, array $payload)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    /**
     * Нормализует телефон к формату +380..., +375... или +7...
     * Поддерживает: 380990352448, +380 (99) 035-24-48, 0990352448, 990352448, 89991234567
     */
    private function normalizeAndValidateTelephone($phone)
    {
        $digits = preg_replace('/\D/', '', (string)$phone);

        if ($digits === '') {
            return false;
        }

        if (preg_match('/^0\d{9}$/', $digits)) {
            $digits = '380' . substr($digits, 1);
        } elseif (preg_match('/^8\d{10}$/', $digits)) {
            $digits = '7' . substr($digits, 1);
        } elseif (preg_match('/^\d{9}$/', $digits)) {
            $digits = '380' . $digits;
        }

        if (preg_match('/^380\d{9}$/', $digits)
            || preg_match('/^375\d{9}$/', $digits)
            || preg_match('/^7\d{10}$/', $digits)) {
            return '+' . $digits;
        }

        return false;
    }


}
