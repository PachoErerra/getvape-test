<?php

use app\models\AssortmentTranslates;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Telegram\Bot\Api;
 
class ControllerCommonTelegram extends Controller
{


    const BTN_GO_TO_SHOPPING = '🌐 Перейти до покупок';

//    const BTN_OUR_STORES = '🛍️ Наші магазини';
    const BTN_PERSONAL_DATA = '👤 Мої бонуси та дані';
    const BTN_SHARE_BOT = '📢 Поділитися ботом';
    const BTN_CONTACT_THE_MANAGER = "📩 Зв'язатися з менеджером";

    public function index()
    {

        $this->load->model('customer/customer');

        $usersFilepath = DIR_DOWNLOAD . 'telegram-users.xlsx';


//        $countCustomersNoOrdersMoreThan3Months = $this->model_customer_customer->countCustomersNoOrdersMoreThan3MonthsWithTelegram();

  
        if (isset($this->request->get['download_qr'])) {
            $this->downloadEnvelopeQr((int)$this->request->get['download_qr']);
            return;
        }

        if (isset($this->request->get['download_file']) && file_exists($usersFilepath)) {

            $file_content = file_get_contents($usersFilepath);
            $file_name = basename($usersFilepath);

            // Set the response headers to force download
            $this->response->addHeader('Content-Type: application/octet-stream');
            $this->response->addHeader('Content-Disposition: attachment; filename="' . $file_name . '"');
            $this->response->addHeader('Content-Length: ' . filesize($usersFilepath));

            // Output the file content
            $this->response->setOutput($file_content);
        } else {

            $this->load->model('setting/setting');


            if ($this->request->server['REQUEST_METHOD'] == 'POST') {

                $postData = $this->request->post;

                $action = $postData['action'];


                if ($action == 'send_msg') {
                    $data = $this->actionSendMessages($postData);

                    if (isset($data['error_telegram_user'])) {
                        $this->cache->set('error_telegram_user', 1);
                    } elseif (isset($data['error_text'])) {
                        $this->cache->set('error_text', 1);
                    } elseif (isset($data['telegram_success'])) {
                        $this->cache->set('telegram_success', $data['telegram_success']);
                    } elseif (isset($data['telegram_error'])) {
                        $this->cache->set('telegram_error', $data['telegram_error']);
                    }

                    $this->cache->set('active_tab', 1);
                } elseif ($action == 'downloading_users') {
//                    $this->actionGenerateUsersFile($telegramUsers);
//                    $command = 'php ' . DIR_APPLICATION_CATALOG . 'index.php route=tool/generate_users_file > /dev/null 2>&1 &';
//                     exec($command);
                    $url = HTTPS_CATALOG . 'index.php?route=tool/generate_users_file&token=PfX54hQBZWfowQjhy4wt1MqpuIAFU';

                    $command = 'curl "' . $url . '" > /dev/null 2>&1 &';
                    exec($command);

                    $this->cache->set('telegram_success', 'Процесс генерации файла запущен');
                    $this->cache->set('active_tab', 2);
                } elseif ($action == 'sending_messages') {
                    $this->cache->set('telegram_success', 'Данные успешно сохранены');
                    $this->cache->set('active_tab', 3);
                    $text = $postData['text'];
                    $interval_day_sending_messages = $postData['interval_day_sending_messages'];
                    $this->model_setting_setting->editSettingValue('telegram_bot', 'sending_messages', $text);
                    $this->model_setting_setting->editSettingValue('telegram_bot', 'interval_day_sending_messages', $interval_day_sending_messages);

                } elseif ($action == 'order_statuses_messages') {
                    $this->cache->set('telegram_success', 'Данные успешно сохранены');
                    $this->cache->set('active_tab', 4);
                    $status_message = $postData['status_message'];
                    $this->model_setting_setting->editSettingValue('telegram_bot', 'status_message', $status_message);
                } elseif ($action == 'after_order') {
                    $this->cache->set('telegram_success', 'Данные успешно сохранены');
                    $this->cache->set('active_tab', 7);
                    $after_order = $postData['after_order'];
                    $this->model_setting_setting->editSettingValue('telegram_bot', 'after_order', $after_order);
                } elseif ($action == 'envelope_save') {
                    $this->actionEnvelopeSave($postData);
                } elseif ($action == 'envelope_delete') {
                    $this->actionEnvelopeDelete($postData);
                } elseif ($action == 'envelope_await_sticker') {
                    $this->actionEnvelopeAwaitSticker($postData);
                } elseif ($action == 'envelope_admins') {
                    $this->actionEnvelopeAdmins($postData);
                }

                $redirectTab = (int)$this->cache->get('active_tab');
                if (!$redirectTab) {
                    $redirectTab = isset($this->request->get['active_tab']) ? (int)$this->request->get['active_tab'] : 5;
                }

                $this->response->redirect($this->url->link('common/telegram', 'user_token=' . $this->session->data['user_token'] . '&active_tab=' . $redirectTab, true));

            }


            if (isset($this->request->get['active_tab'])) {
                $data['active_tab'] = (int)$this->request->get['active_tab'];
            } elseif ($this->cache->get('active_tab')) {

                $data['active_tab'] = $this->cache->get('active_tab');

                $this->cache->delete('active_tab');
            } else {
                $data['active_tab'] = 5;

            }

            if ($this->cache->get('telegram_success')) {

                $data['telegram_success'] = $this->cache->get('telegram_success');

                $this->cache->delete('telegram_success');
            }

            if ($this->cache->get('telegram_error')) {

                $data['telegram_error'] = $this->cache->get('telegram_error');

                $this->cache->delete('telegram_error');
            }

            if ($this->cache->get('error_telegram_user')) {

                $data['telegram_success'] = $this->cache->get('error_telegram_user');

                $this->cache->delete('error_telegram_user');
            }
            if ($this->cache->get('error_text')) {

                $data['error_text'] = $this->cache->get('error_text');

                $this->cache->delete('error_text');
            }


            $data['sending_messages'] = $this->model_setting_setting->getSettingValue('sending_messages');

            $data['status_message'] = json_decode($this->model_setting_setting->getSettingValue('status_message'), true);

            $data['after_order'] = json_decode($this->model_setting_setting->getSettingValue('after_order'), true);

            $data['interval_day_sending_messages'] = $this->model_setting_setting->getSettingValue('interval_day_sending_messages');

            if (file_exists($usersFilepath)) {
                $data['telegram_users_date_created'] = date('H:i d.m.Y', filemtime($usersFilepath));
                $data['action_download_file'] = $this->url->link('common/telegram', 'user_token=' . $this->session->data['user_token'] . "&download_file=1", true);
            }

            $data['action'] = $this->url->link('common/telegram', 'user_token=' . $this->session->data['user_token'], true);
            $data['autocomplete'] = $this->url->link('common/telegram/autocomplete', 'user_token=' . $this->session->data['user_token'], true);

            $data['header'] = $this->load->controller('common/header');

            $data['count_telegram_users'] = $this->model_customer_customer->countTelegramUsers();
            $data['count_active_telegram_users'] = $this->model_customer_customer->countActiveTelegramUsers();


            $data['column_left'] = $this->load->controller('common/column_left');

            $data['footer'] = $this->load->controller('common/footer');

//            $data['countCustomersNoOrdersMoreThan3Months'] = $countCustomersNoOrdersMoreThan3Months;
//$this->load->model('common/telegram'); 
            $data['logins'] = $this->getList();
            $data['api_sends'] = $this->getApiSendList();
            $data['envelopes_block'] = $this->getEnvelopesBlock();

            $this->response->setOutput($this->load->view('common/telegram', $data));
        }
    }

    private function actionEnvelopeSave($postData)
    {
        $this->load->model('telegram/envelope');
        $this->cache->set('active_tab', 9);

        if (!$this->envelopeTablesExist()) {
            $this->cache->set('telegram_error', 'Спочатку виконайте SQL: tools/telegram_envelopes.sql');
            return;
        }

        $envelope_id = isset($postData['envelope_id']) ? (int)$postData['envelope_id'] : 0;

        $this->ensureEnvelopeImageColumn();

        $data = [
            'name' => isset($postData['name']) ? trim($postData['name']) : '',
            'gift_name' => isset($postData['gift_name']) ? trim($postData['gift_name']) : '',
            'image' => isset($postData['image']) ? html_entity_decode(ltrim(trim($postData['image']), '/'), ENT_QUOTES, 'UTF-8') : '',
            'text_before' => isset($postData['text_before']) ? trim($postData['text_before']) : '',
            'text_after' => isset($postData['text_after']) ? trim($postData['text_after']) : '',
            'button_text' => isset($postData['button_text']) && trim($postData['button_text']) !== ''
                ? trim($postData['button_text'])
                : 'Відкрити конверт',
            'sticker_file_id' => isset($postData['sticker_file_id']) ? trim($postData['sticker_file_id']) : '',
            'ttl_minutes' => isset($postData['ttl_minutes']) ? max(1, (int)$postData['ttl_minutes']) : 30,
            'status' => !empty($postData['status']) ? 1 : 0,
        ];

        if ($data['name'] === '' || $data['gift_name'] === '') {
            $this->cache->set('telegram_error', 'Вкажіть назву конверта і назву подарунка');
            return;
        }

        if ($envelope_id) {
            $this->model_telegram_envelope->editEnvelope($envelope_id, $data);
            $this->cache->set('telegram_success', 'Конверт оновлено');
        } else {
            $this->model_telegram_envelope->addEnvelope($data);
            $this->cache->set('telegram_success', 'Конверт створено');
        }
    }

    private function envelopeTablesExist()
    {
        $tableCheck = $this->db->query("SHOW TABLES LIKE 'telegram_envelope'");

        return (bool)$tableCheck->num_rows;
    }

    private function actionEnvelopeDelete($postData)
    {
        $this->load->model('telegram/envelope');
        $this->cache->set('active_tab', 9);

        if (!$this->envelopeTablesExist()) {
            $this->cache->set('telegram_error', 'Спочатку виконайте SQL: tools/telegram_envelopes.sql');
            return;
        }

        $envelope_id = isset($postData['envelope_id']) ? (int)$postData['envelope_id'] : 0;

        if (!$envelope_id) {
            return;
        }

        if ($this->model_telegram_envelope->hasEvents($envelope_id)) {
            $this->cache->set('telegram_error', 'Неможливо видалити: по конверту вже є скани');
            return;
        }

        if ($this->model_telegram_envelope->deleteEnvelope($envelope_id)) {
            $this->cache->set('telegram_success', 'Конверт видалено');
        } else {
            $this->cache->set('telegram_error', 'Неможливо видалити: по конверту вже є скани');
        }
    }

    private function ensureEnvelopeImageColumn()
    {
        if (!$this->envelopeTablesExist()) {
            return;
        }

        $column = $this->db->query("SHOW COLUMNS FROM `telegram_envelope` LIKE 'image'");

        if (!$column->num_rows) {
            $this->db->query("ALTER TABLE `telegram_envelope` ADD `image` varchar(255) NOT NULL DEFAULT '' AFTER `gift_name`");
        }
    }

    private function actionEnvelopeAwaitSticker($postData)
    {
        $this->cache->set('active_tab', 9);
        $this->cache->set('telegram_success', 'Стикер у Telegram зараз вимкнено');
    }

    private function actionEnvelopeAdmins($postData)
    {
        $this->cache->set('active_tab', 9);

        $ids = isset($postData['telegram_envelope_admin_ids'])
            ? trim($postData['telegram_envelope_admin_ids'])
            : '';

        $this->saveTelegramBotSetting('telegram_envelope_admin_ids', $ids);
        $this->cache->set('telegram_success', 'Telegram ID збережено');
    }

    private function saveTelegramBotSetting($key, $value)
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

    private function getEnvelopesBlock()
    {
        $this->load->model('telegram/envelope');
        $this->load->model('setting/setting');

        $botLink = defined('TELEGRAM_BOT_LINK') ? TELEGRAM_BOT_LINK : 'https://t.me/Vapors_GetVape_bot';
        $userToken = $this->session->data['user_token'];
        $baseAction = $this->url->link('common/telegram', 'user_token=' . $userToken . '&active_tab=9', true);

        $rows = [];
        $envelopes = [];
        $totals = [
            'starts_total' => 0,
            'starts_uniq' => 0,
            'opens_total' => 0,
            'opens_uniq' => 0,
        ];

        if ($this->envelopeTablesExist()) {
            $this->ensureEnvelopeImageColumn();
            $envelopes = $this->model_telegram_envelope->getEnvelopes();
            $totals = $this->model_telegram_envelope->getTotalsAll();
        }

        $this->load->model('tool/image');
        $placeholder = $this->model_tool_image->resize('no_image.png', 100, 100);

        $historyEnvelopeId = isset($this->request->get['history_envelope_id'])
            ? (int)$this->request->get['history_envelope_id']
            : 0;
        $filterEventType = isset($this->request->get['filter_event_type'])
            ? (string)$this->request->get['filter_event_type']
            : '';
        $page = isset($this->request->get['env_page']) ? max(1, (int)$this->request->get['env_page']) : 1;
        $limit = 50;
        $start = ($page - 1) * $limit;

        foreach ($envelopes as $envelope) {
            $stats = $this->model_telegram_envelope->getStatsByEnvelope($envelope['envelope_id']);
            $link = rtrim($botLink, '/') . '?start=env_' . $envelope['code'];
            $eid = (int)$envelope['envelope_id'];
            $image = !empty($envelope['image']) ? $envelope['image'] : '';
            $thumb = $placeholder;

            if ($image && is_file(DIR_IMAGE . $image)) {
                $thumb = $this->model_tool_image->resize($image, 100, 100);
            }

            $rows[] = [
                'envelope_id' => $eid,
                'name' => $envelope['name'],
                'gift_name' => $envelope['gift_name'],
                'image' => $image,
                'thumb' => $thumb,
                'text_before' => $envelope['text_before'],
                'text_after' => $envelope['text_after'],
                'button_text' => $envelope['button_text'],
                'sticker_file_id' => $envelope['sticker_file_id'],
                'ttl_minutes' => $envelope['ttl_minutes'],
                'status' => (int)$envelope['status'],
                'date_added' => $envelope['date_added'],
                'date_modified' => $envelope['date_modified'],
                'can_delete' => !$this->model_telegram_envelope->hasEvents($eid),
                'link' => $link,
                'qr' => 'https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=' . rawurlencode($link),
                'qr_download' => $this->url->link(
                    'common/telegram',
                    'user_token=' . $userToken . '&download_qr=' . $eid,
                    true
                ),
                'stats' => $stats,
                'history' => $this->url->link('common/telegram', 'user_token=' . $userToken . '&active_tab=9&history_envelope_id=' . $eid, true),
                'history_active' => ($historyEnvelopeId === $eid),
            ];
        }

        $historyEnvelope = [];
        if ($historyEnvelopeId && $this->envelopeTablesExist()) {
            $historyEnvelope = $this->model_telegram_envelope->getEnvelope($historyEnvelopeId);
        }

        $events = [];
        $eventsTotal = 0;

        if ($historyEnvelopeId && $historyEnvelope) {
            $eventFilter = [
                'envelope_id' => $historyEnvelopeId,
                'event_type' => in_array($filterEventType, ['start', 'auth', 'open'], true) ? $filterEventType : '',
                'start' => $start,
                'limit' => $limit,
            ];
            $events = $this->model_telegram_envelope->getEventsFiltered($eventFilter);
            $eventsTotal = $this->model_telegram_envelope->getEventsFilteredTotal($eventFilter);
        }

        $paginationUrl = 'user_token=' . $userToken . '&active_tab=9&history_envelope_id=' . $historyEnvelopeId;
        if ($filterEventType) {
            $paginationUrl .= '&filter_event_type=' . urlencode($filterEventType);
        }
        $paginationUrl .= '&env_page={page}';

        $pagination = new Pagination();
        $pagination->total = $eventsTotal;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('common/telegram', $paginationUrl, true);

        $data = [
            'envelopes' => $rows,
            'envelopes_json' => json_encode($rows, JSON_UNESCAPED_UNICODE),
            'totals' => $totals,
            'events' => $events,
            'events_total' => $eventsTotal,
            'pagination' => $pagination->render(),
            'results' => sprintf('Показано %d з %d', min($eventsTotal, $start + count($events)), $eventsTotal),
            'history_envelope_id' => $historyEnvelopeId,
            'history_envelope' => $historyEnvelope,
            'filter_event_type' => $filterEventType,
            'user_token' => $this->session->data['user_token'],
            'action' => $baseAction,
            'placeholder' => $placeholder,
            'telegram_envelope_admin_ids' => $this->model_setting_setting->getSettingValue('telegram_envelope_admin_ids'),
            'global_sticker_ok' => false,
            'global_await_sticker' => false,
            'edit_envelope' => null,
        ];

        if (isset($this->request->get['envelope_id'])) {
            $edit = $this->model_telegram_envelope->getEnvelope((int)$this->request->get['envelope_id']);
            if ($edit) {
                $data['edit_envelope'] = $edit;
            }
        }

        return $this->load->view('common/telegramenvelopes', $data);
    }

    private function downloadEnvelopeQr($envelope_id)
    {
        $this->load->model('telegram/envelope');

        $envelope = $this->model_telegram_envelope->getEnvelope((int)$envelope_id);

        if (!$envelope) {
            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');
            $this->response->setOutput('Envelope not found');
            return;
        }

        $botLink = defined('TELEGRAM_BOT_LINK') ? TELEGRAM_BOT_LINK : 'https://t.me/Vapors_GetVape_bot';
        $link = rtrim($botLink, '/') . '?start=env_' . $envelope['code'];
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=500x500&data=' . rawurlencode($link);

        $image = false;

        if (function_exists('curl_init')) {
            $ch = curl_init($qrUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $image = curl_exec($ch);
            $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode < 200 || $httpCode >= 300) {
                $image = false;
            }
        }

        if ($image === false || $image === '') {
            $image = @file_get_contents($qrUrl);
        }

        if ($image === false || $image === '') {
            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 502 Bad Gateway');
            $this->response->setOutput('QR generate failed');
            return;
        }

        $filename = 'qr-envelope-' . (int)$envelope_id . '-500x500.png';

        if (ob_get_length()) {
            ob_end_clean();
        }

        header('Content-Type: image/png');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($image));
        header('Cache-Control: no-store, no-cache, must-revalidate');
        echo $image;
        exit;
    }

    public function autocomplete()
    {
        $this->load->model('customer/customer');

        $json = [];
        $filter = isset($this->request->get['filter_name']) ? $this->request->get['filter_name'] : '';

        if (mb_strlen($filter) >= 2) {
            $results = $this->model_customer_customer->searchTelegramUsers($filter, 30);

            foreach ($results as $result) {
                $label = trim($result['first_name'] . ' ' . $result['last_name']);

                if (!empty($result['username'])) {
                    $label .= ' @' . $result['username'];
                }

                if (!empty($result['telephone'])) {
                    $label .= ' ' . $result['telephone'];
                }

                $json[] = [
                    'id' => $result['id'],
                    'text' => $label !== '' ? $label : (string)$result['id'],
                ];
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    protected function getList()
    {
        if (isset($this->request->get['filter_order_id'])) {
            $filter_order_id = $this->request->get['filter_order_id'];
        } else {
            $filter_order_id = '';
        }

        if (isset($this->request->get['filter_prize'])) {
            $datap = $_GET['filter_prize'];
            $filter_prize = json_decode(urldecode($datap), true);
        } else {
            $filter_prize = '';
        }

        if (isset($this->request->get['filter_customer'])) {
            $filter_customer = $this->request->get['filter_customer'];
        } else {
            $filter_customer = '';
        }

        if (isset($this->request->get['filter_order_status'])) {
            $filter_order_status = $this->request->get['filter_order_status'];
        } else {
            $filter_order_status = '';
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $filter_order_status_id = $this->request->get['filter_order_status_id'];
        } else {
            $filter_order_status_id = '';
        }

        if (isset($this->request->get['filter_used'])) {
            $filter_used = $this->request->get['filter_used'];
        } else {
            $filter_used = '';
        }

        if (isset($this->request->get['filter_active'])) {
            $filter_active = $this->request->get['filter_active'];
        } else {
            $filter_active = '';
        }

        if (isset($this->request->get['filter_overdue'])) {
            $filter_overdue = $this->request->get['filter_overdue'];
        } else {
            $filter_overdue = '';
        }

        if (isset($this->request->get['filter_date_added_start'])) {
            $filter_date_added_start = $this->request->get['filter_date_added_start'];
        } else {
            $filter_date_added_start = '';
        }
        if (isset($this->request->get['filter_date_added_end'])) {
            $filter_date_added_end = $this->request->get['filter_date_added_end'];
        } else {
            $filter_date_added_end = '';
        }

        if (isset($this->request->get['filter_date_used_start'])) {
            $filter_date_used_start = $this->request->get['filter_date_used_start'];
        } else {
            $filter_date_used_start = '';
        }
        if (isset($this->request->get['filter_date_used_end'])) {
            $filter_date_used_end = $this->request->get['filter_date_used_end'];
        } else {
            $filter_date_used_end = '';
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $filter_date_modified = $this->request->get['filter_date_modified'];
        } else {
            $filter_date_modified = '';
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'fortune_id';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'DESC';
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_prize'])) {
            $url .= '&filter_prize=' . $this->request->get['filter_prize'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
        }

        if (isset($this->request->get['filter_used'])) {
            $url .= '&filter_used=' . $this->request->get['filter_used'];
        }
        if (isset($this->request->get['filter_active'])) {
            $url .= '&filter_active=' . $this->request->get['filter_active'];
        }
        if (isset($this->request->get['filter_overdue'])) {
            $url .= '&filter_overdue=' . $this->request->get['filter_overdue'];
        }
        if (isset($this->request->get['filter_date_added_start'])) {
            $url .= '&filter_date_added_start=' . $this->request->get['filter_date_added_start'];
        }
        if (isset($this->request->get['filter_date_added_end'])) {
            $url .= '&filter_date_added_end=' . $this->request->get['filter_date_added_end'];
        }
        if (isset($this->request->get['filter_date_used_start'])) {
            $url .= '&filter_date_used_start=' . $this->request->get['filter_date_used_start'];
        }
        if (isset($this->request->get['filter_date_used_end'])) {
            $url .= '&filter_date_used_end=' . $this->request->get['filter_date_used_end'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Фонтуна',
            'href' => $this->url->link('common/telegram', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        $data['invoice'] = $this->url->link('sale/order/invoice', 'user_token=' . $this->session->data['user_token'], true);
        $data['shipping'] = $this->url->link('sale/order/shipping', 'user_token=' . $this->session->data['user_token'], true);
        $data['add'] = $this->url->link('sale/order/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('common/telegram/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['orders'] = array();

        $filter_data = array(
            'filter_order_id' => $filter_order_id,
            'filter_customer' => $filter_customer,
            'filter_prize' => $filter_prize,
            'filter_order_status_id' => $filter_order_status_id,
            'filter_used' => $filter_used,
            'filter_active' => $filter_active,
            'filter_overdue' => $filter_overdue,
            'filter_date_added_start' => $filter_date_added_start,
            'filter_date_added_end' => $filter_date_added_end,
            'filter_date_used_start' => $filter_date_used_start,
            'filter_date_used_end' => $filter_date_used_end,
            'filter_date_modified' => $filter_date_modified,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );

        $order_total = $this->model_customer_customer->getTotalLogins($filter_data);

        $results = $this->model_customer_customer->getLogins($filter_data);

        $this->load->model('customer/customer');

        $now = new DateTime();

        foreach ($results as $result) {
            $targetTime = new DateTime($result['date_valid']);
            $interval = $now->diff($targetTime);

            if ($interval->invert == 0) {
                $overdue = false;
            } else {
                $overdue = true;
            }

            $data['orders'][] = array(
                'fortune_id' => $result['fortune_id'],
                'used' => $result['used'],
                'code' => $result['code'],
                'overdue' => $overdue,
                'customer' => $result['telephone'],
                'prize' => $result['prize'],
                'date_added' => date('d.m.y H:i:s', strtotime($result['date_added'])),
                'date_valid' => date('d.m.y H:i:s', strtotime($result['date_valid'])),
                'date_used' => (($result['date_used'] != '0000-00-00 00:00:00') ? date('d.m.y H:i:s', strtotime($result['date_used'])) : '') . (($result['used'] >= 2) ? (' (' . $result['used'] . ')') : ''),
            );
        }

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array)$this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $url = '';

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_prize'])) {
            $url .= '&filter_prize=' . $this->request->get['filter_prize'];
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
        }

        if (isset($this->request->get['filter_used'])) {
            $url .= '&filter_used=' . $this->request->get['filter_used'];
        }
        if (isset($this->request->get['filter_active'])) {
            $url .= '&filter_active=' . $this->request->get['filter_active'];
        }
        if (isset($this->request->get['filter_overdue'])) {
            $url .= '&filter_overdue=' . $this->request->get['filter_overdue'];
        }
        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }
        if (isset($this->request->get['filter_date_added_start'])) {
            $url .= '&filter_date_added_start=' . $this->request->get['filter_date_added_start'];
        }
        if (isset($this->request->get['filter_date_added_end'])) {
            $url .= '&filter_date_added_end=' . $this->request->get['filter_date_added_end'];
        }
        if (isset($this->request->get['filter_date_used_start'])) {
            $url .= '&filter_date_used_start=' . $this->request->get['filter_date_used_start'];
        }
        if (isset($this->request->get['filter_date_used_end'])) {
            $url .= '&filter_date_used_end=' . $this->request->get['filter_date_used_end'];
        }


        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_order'] = $this->url->link('common/telegram', 'user_token=' . $this->session->data['user_token'] . '&sort=fortune_id' . $url, true);
        $data['sort_telephone'] = $this->url->link('common/telegram', 'user_token=' . $this->session->data['user_token'] . '&sort=telephone' . $url, true);
        $data['sort_prize'] = $this->url->link('common/telegram', 'user_token=' . $this->session->data['user_token'] . '&sort=prize' . $url, true);
        $data['sort_date_added'] = $this->url->link('common/telegram', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url, true);
        $data['sort_date_used'] = $this->url->link('common/telegram', 'user_token=' . $this->session->data['user_token'] . '&sort=date_used' . $url, true);

        $url = '';

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }

        if (isset($this->request->get['filter_prize'])) {
            $url .= '&filter_prize=' . $this->request->get['filter_prize'];
        }

        if (isset($this->request->get['filter_used'])) {
            $url .= '&filter_used=' . $this->request->get['filter_used'];
        }
        if (isset($this->request->get['filter_active'])) {
            $url .= '&filter_active=' . $this->request->get['filter_active'];
        }
        if (isset($this->request->get['filter_overdue'])) {
            $url .= '&filter_overdue=' . $this->request->get['filter_overdue'];
        }
        if (isset($this->request->get['filter_date_added_start'])) {
            $url .= '&filter_date_added_start=' . $this->request->get['filter_date_added_start'];
        }
        if (isset($this->request->get['filter_date_added_end'])) {
            $url .= '&filter_date_added_end=' . $this->request->get['filter_date_added_end'];
        }
        if (isset($this->request->get['filter_date_used_start'])) {
            $url .= '&filter_date_used_start=' . $this->request->get['filter_date_used_start'];
        }
        if (isset($this->request->get['filter_date_used_end'])) {
            $url .= '&filter_date_used_end=' . $this->request->get['filter_date_used_end'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $order_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('common/telegram', 'user_token=' . $this->session->data['user_token'] . $url . '&active_tab=5&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

        $data['filter_order_id'] = $filter_order_id;
        $data['filter_customer'] = $filter_customer;
        $data['filter_prize'] = $filter_prize;
        $data['filter_order_status_id'] = $filter_order_status_id;
        $data['filter_used'] = $filter_used;
        $data['filter_active'] = $filter_active;
        $data['filter_overdue'] = $filter_overdue;
        $data['filter_date_added_start'] = $filter_date_added_start;
        $data['filter_date_added_end'] = $filter_date_added_end;
        $data['filter_date_used_start'] = $filter_date_used_start;
        $data['filter_date_used_end'] = $filter_date_used_end;
        $data['filter_date_modified'] = $filter_date_modified;

        $data['sort'] = $sort;
        $data['order'] = $order;


        return $this->load->view('common/telegramlogins', $data);
    }

    protected function getApiSendList()
    {
        $this->load->model('telegram/api_send');

        $statuses = [
            'sent' => 'Відправлено',
            'no_telegram' => 'Немає Telegram',
            'unsubscribed' => 'Відписаний',
            'customer_not_found' => 'Клієнта не знайдено',
            'invalid_phone' => 'Невірний телефон',
            'telegram_send_failed' => 'Помилка відправки',
        ];

        $filter_api_phone = isset($this->request->get['filter_api_phone']) ? $this->request->get['filter_api_phone'] : '';
        $filter_api_status = isset($this->request->get['filter_api_status']) ? $this->request->get['filter_api_status'] : '';
        $filter_api_date_start = isset($this->request->get['filter_api_date_start']) ? $this->request->get['filter_api_date_start'] : '';
        $filter_api_date_end = isset($this->request->get['filter_api_date_end']) ? $this->request->get['filter_api_date_end'] : '';
        $page = isset($this->request->get['page_api']) ? max(1, (int)$this->request->get['page_api']) : 1;

        $url = '&active_tab=8';

        if ($filter_api_phone !== '') {
            $url .= '&filter_api_phone=' . urlencode(html_entity_decode($filter_api_phone, ENT_QUOTES, 'UTF-8'));
        }

        if ($filter_api_status !== '') {
            $url .= '&filter_api_status=' . urlencode($filter_api_status);
        }

        if ($filter_api_date_start !== '') {
            $url .= '&filter_api_date_start=' . urlencode($filter_api_date_start);
        }

        if ($filter_api_date_end !== '') {
            $url .= '&filter_api_date_end=' . urlencode($filter_api_date_end);
        }

        $limit = (int)$this->config->get('config_limit_admin');

        if ($limit < 1) {
            $limit = 20;
        }

        $filter_data = [
            'filter_phone' => $filter_api_phone,
            'filter_status' => $filter_api_status,
            'filter_date_added_start' => $filter_api_date_start,
            'filter_date_added_end' => $filter_api_date_end,
            'sort' => 'date_added',
            'order' => 'DESC',
            'start' => ($page - 1) * $limit,
            'limit' => $limit,
        ];

        $total = $this->model_telegram_api_send->getTotalApiSends($filter_data);
        $total_sent = $this->model_telegram_api_send->getSentCount([
            'filter_phone' => $filter_api_phone,
            'filter_date_added_start' => $filter_api_date_start,
            'filter_date_added_end' => $filter_api_date_end,
        ]);
        $total_unique = $this->model_telegram_api_send->getUniquePhonesCount($filter_data);
        $results = $this->model_telegram_api_send->getApiSends($filter_data);

        $data['rows'] = [];

        foreach ($results as $result) {
            $status = $result['status'];

            $data['rows'][] = [
                'phone' => $result['phone'],
                'status' => $status,
                'status_label' => isset($statuses[$status]) ? $statuses[$status] : $status,
                'message' => $result['message'],
                'error' => $result['error'],
                'date_added' => date('d.m.Y H:i:s', strtotime($result['date_added'])),
            ];
        }

        $pagination = new Pagination();
        $pagination->total = $total;
        $pagination->page = $page;
        $pagination->limit = $limit;
        $pagination->url = $this->url->link('common/telegram', 'user_token=' . $this->session->data['user_token'] . $url . '&page_api={page}', true);

        $data['pagination'] = $pagination->render();
        $data['results'] = sprintf($this->language->get('text_pagination'), ($total) ? (($page - 1) * $limit) + 1 : 0, ((($page - 1) * $limit) > ($total - $limit)) ? $total : ((($page - 1) * $limit) + $limit), $total, ceil($total / $limit));
        $data['total'] = $total;
        $data['total_sent'] = $total_sent;
        $data['total_unique'] = $total_unique;
        $data['statuses'] = $statuses;
        $data['filter_api_phone'] = $filter_api_phone;
        $data['filter_api_status'] = $filter_api_status;
        $data['filter_api_date_start'] = $filter_api_date_start;
        $data['filter_api_date_end'] = $filter_api_date_end;
        $data['user_token'] = $this->session->data['user_token'];

        return $this->load->view('common/telegramapisends', $data);
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
//                    'url' => 'https://qrfy.io/p/4L32roBKhi'
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
                    'text' => self::BTN_SHARE_BOT,
                    'url' => 'https://t.me/share/url?url=' . TELEGRAM_BOT_LINK
                ],
            ]
        ];


        return json_encode(['inline_keyboard' => $inline_keyboard]);
    }


    private
    function actionSendMessages($postData)
    {

        $error = 0;


        if (!$postData['telegram_user']) {
            $error = 1;
            $data['error_telegram_user'] = $error;
        }

        if (!$postData['text']) {
            $error = 1;
            $data['error_text'] = $error;
        }


        if (!$error) {


            if (in_array("all", $postData['telegram_user'])) {

                $index = array_search("all", $postData['telegram_user']);

                if ($index !== false) {
                    unset($postData['telegram_user'][$index]);
                }

                $postData['telegram_user'] = $this->model_customer_customer->getTelegramUserIds(true);

            } elseif (in_array("order_for_up_to_3_months", $postData['telegram_user'])) {

                $index = array_search("order_for_up_to_3_months", $postData['telegram_user']);

                if ($index !== false) {
                    unset($postData['telegram_user'][$index]);
                }


                $orders = $this->model_customer_customer->getCustomersNoOrdersUpTo3MonthsWithTelegram();

                if ($orders) {

                    foreach ($orders as $order) {
                        $postData['telegram_user'][] = $order['id'];
                    }
                }
            } elseif (in_array("order_for_tree_months_or_more", $postData['telegram_user'])) {

                $index = array_search("order_for_tree_months_or_more", $postData['telegram_user']);

                if ($index !== false) {
                    unset($postData['telegram_user'][$index]);
                }


                $orders = $this->model_customer_customer->getCustomersNoOrdersMoreThan3MonthsWithTelegram();

                if ($orders) {

                    foreach ($orders as $order) {
                        $postData['telegram_user'][] = $order['id'];
                    }
                }
            }


            $result = $this->sendToTelegram($postData);

            if ($result['error']) {
                $data['telegram_error'] = 'Сообщения не отправлены, убедитесь что отправляемые данные корректные';
            } else {
                $data['telegram_success'] = 'Сообщения отправлены в очередь на рассылку';
            }
        }

        return $data;
    }

    private
    function sendToTelegram($data)
    {

        try {


            $this->load->model('tool/image');

            $this->load->model('telegram/queue');

            $shards = ['default_1', 'default_2', 'default_3', 'default_4'];
            $shardIndex = 0;

            foreach ($data['telegram_user'] as $telegramUser) {

                $dataMessage = [
                    'chat_id' => $telegramUser,
                    'parse_mode' => 'html',
                    'text' => $data['text'],
                ];

                $keyboard = [];
                if (isset($data['links']) && count($data['links'])) {
                    foreach ($data['links'] as $link) {
                        if ($link['name'] && $link['link']) {
                            $keyboard['inline_keyboard'][][] = [
                                "text" => $link['name'],
                                "url" => $link['link'],
                            ];
                        }
                    }
                    if ($keyboard) {
                        $dataMessage['reply_markup'] = json_encode($keyboard);
                    }
                }

                if (!$keyboard) {
                    $dataMessage['reply_markup'] = $this->returnKeyboard();

                }

                try {

                    $hasImage = 0;

                    if ($data['image']) {
                        $hasImage = 1;

                        $image = $this->model_tool_image->resize($data['image'], 500, 500);
                        $dataMessage = [
                            'chat_id' => $telegramUser,
                            'photo' => $image,
                            'parse_mode' => 'html',
                            'reply_markup' => $dataMessage['reply_markup'],
                            'caption' => $data['text']
                        ];
                    }

                    $this->model_telegram_queue->addQueue($hasImage, $dataMessage, $shards[$shardIndex % 4]);
                    $shardIndex++;


                } catch (Exception $e) {

                    return [
                        'error' => 1,
                        'Не удалось создать рассылку'
                    ];
                }

            }
            return [
                'error' => 0,
            ];
        } catch (Exception $exception) {

            return [
                'error' => 1,
                'Не удалось создать рассылку'
            ];
        }
    }

    private
    function actionGenerateUsersFile($telegramUsers)
    {
        $inputFilePath = DIR_DOWNLOAD . 'telegram-users.xlsx';


        $today = date("Y-m-d H:i:s");

        $today = new DateTime($today);


        unlink($inputFilePath);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $cntCell = 1;
        $spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(150, 'pt');
        $spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(100, 'pt');
        $spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(100, 'pt');
        $spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(150, 'pt');

        $sheet->setCellValue('A' . $cntCell, 'ФИО');
        $sheet->setCellValue('B' . $cntCell, 'Номер тел.');
        $sheet->setCellValue('C' . $cntCell, 'Telegram Username');
        $sheet->setCellValue('D' . $cntCell, 'Дата последнего заказа');
        $cntCell++;


        foreach ($telegramUsers as $telegramUser) {

            $lastOrderDate = '-';

            $color = '';

            $lastOrder = $this->model_customer_customer->getLastOrderByCustomerId($telegramUser['user_id']);

            if ($lastOrder) {

                $interval_day_sending_messages = $this->model_setting_setting->getSettingValue('interval_day_sending_messages');

                $lastOrderDate = '№' . $lastOrder['order_id'] . ' ' . date('d.m.Y', strtotime($lastOrder['date_added']));

                $date_added = new DateTime($lastOrder['date_added']);

                $interval = $today->diff($date_added);

                if ($interval->days > $interval_day_sending_messages) {
                    $color = \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED;
                }

            } else {
                $color = \PhpOffice\PhpSpreadsheet\Style\Color::COLOR_RED;
            }


            if ($telegramUser['firstname'] || $telegramUser['lastname']) {
                $fullName = $telegramUser['firstname'] . ' ' . $telegramUser['lastname'];
            } else {
                $fullName = $telegramUser['first_name'] . ' ' . $telegramUser['last_name'];
            }

            $telephone = $telegramUser['telephone'];
            $telegramId = $telegramUser['username'];
            $sheet->setCellValue('A' . $cntCell, $fullName);
            $sheet->setCellValue('B' . $cntCell, $telephone);
            $sheet->setCellValue('C' . $cntCell, ($telegramId) ? '@' . $telegramId : '');
            $sheet->setCellValue('D' . $cntCell, $lastOrderDate);

            if ($color) {
                $spreadsheet
                    ->getActiveSheet()
                    ->getStyle('D' . $cntCell)
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB($color);
            }
            $cntCell++;


        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($inputFilePath);

    }
}