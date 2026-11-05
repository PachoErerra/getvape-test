<?php

class ControllerExtensionFeedBlackFriday2025 extends Controller
{

    private $date_start = '2025-12-22 00:00:01';
    private $date_end = '2025-12-30 00:00:01';
    private $token = 'sbmd2y%#ifR1bQV1Mn0YYXe43QcgMSxycoXbutwwr5k=';

    public function index()
    {
        /*
        $url = 'https://dev.getvape.com.ua/index.php?route=extension/feed/blackfriday2025/draw';


        $telephone = '+380500445554';

        $data = array(
            "action" => 'win',
            "token" => 'ENCRYPTED_ACCESS_TOKEN',
            "telephone" => $telephone,
        );

        try {
            $ch = curl_init($url);

            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Accept: application/json'
            ]);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $response = curl_exec($ch);
            curl_close($ch);

            echo $response;

        } catch (Exception $e) {
            return false;
        }
        */
    }

    public function draw()
    {

        $http_pass = 'jt8Fk5jFht84GJt';
        if ($http_pass != $_GET['http_pass']) die('Неверный ключ доступа!');

        // Получаем "сырое" тело POST-запроса
        $input = file_get_contents('php://input');

        // Преобразуем JSON → массив
        $data = json_decode($input, true);

        // Проверка на корректный JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "success" => false,
                "error" => "Invalid JSON"
            ]);
            exit;
        }

        // Проверяем влюючен ли модуль
        if (!$this->config->get('config_bfmenu2025')) {
            echo json_encode([
                "success" => false,
                "error" => "Black Friday is not active"
            ]);
            exit;
        }

        // Достаём параметры
        $token = $data['token'] ?? null;
        $telephone = $data['telephone'] ?? null;

        // Проверяем обязательные поля
        if (!$token || !$telephone) {
            echo json_encode([
                "success" => false,
                "error" => "telephone or token missing"
            ]);
            exit;
        }

        if ($token && $token != $this->token) {
            echo json_encode([
                "success" => false,
                "error" => "Invalid token"
            ]);
            exit;
        }

        if (!$this->validateTelephone($telephone)) {
            echo json_encode([
                "success" => false,
                "error" => "Invalid telephone format"
            ]);
            exit;
        }

        $this->load->model('extension/module/regsms');
        $this->load->model('extension/module/fortune');

        $customer_info = $this->model_extension_module_regsms->getCustomerByTelephone($telephone);

        if (!$customer_info) {
            echo json_encode([
                "success" => false,
                "error" => "User " . $telephone . " is not registered"
            ]);
            exit;
        }

        $win = $this->model_extension_module_fortune->checkwin2025($customer_info['customer_id'], $this->date_start, $this->date_end);

        if ($win) {
            echo json_encode([
                "success" => false,
//					"error" => $this->config->get('config_bf2025_stop')
//					"error" => "Ой-йой… здається, ви вже ловили свій шанс 😇\nОдин номер — один шанс на подарунок. Ваш промокод:"
                "error" => "Ой-йой… здається, ви вже ловили свій шанс 😇\nОдин номер — один шанс на подарунок. \n" . $win[0]['code']
            ]);
            exit;
        } else {
            $promocode = $this->win($customer_info);
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($promocode));
    }

    public function check()
    {

        $http_pass = 'jt8Fk5jFht84GJt';
        if ($http_pass != $_GET['http_pass']) die('Неверный ключ доступа!');

        // Получаем "сырое" тело POST-запроса
        $input = file_get_contents('php://input');

        // Преобразуем JSON → массив
        $data = json_decode($input, true);

        // Проверка на корректный JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "success" => false,
                "error" => "Invalid JSON"
            ]);
            exit;
        }

        // Проверяем влюючен ли модуль
        if (!$this->config->get('config_bfmenu2025')) {
            echo json_encode([
                "success" => false,
                "error" => "Black Friday is not active"
            ]);
            exit;
        }

        // Достаём параметры
        $token = $data['token'] ?? null;
        $promo_code = $data['promo_code'] ?? null;

        // Проверяем обязательные поля
        if (!$token || !$promo_code) {
            echo json_encode([
                "success" => false,
                "error" => "promo_code or token missing"
            ]);
            exit;
        }

        if ($token && $token != $this->token) {
            echo json_encode([
                "success" => false,
                "error" => "Invalid token"
            ]);
            exit;
        }

        $this->load->model('extension/module/fortune');

        $win = $this->model_extension_module_fortune->checkwincode($promo_code, $this->date_start, $this->date_end);

        if ($win) {
            if ($win['used']) {
                echo json_encode([
                    "success" => false,
                    "error" => 'Промокод вже використаний ' . $win['date_used']
                ]);
                exit;
            } else {
                echo json_encode([
                    "success" => true,
                    "gift" => $win['prize'],
                    "date_start" => $win['date_added'],
                    "date_end" => $win['date_valid']
                ]);
                exit;
            }
        } else {
            echo json_encode([
                "success" => false,
                "error" => 'Промокод недійсний'
            ]);
            exit;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(array()));
    }

    public function use()
    {

        $http_pass = 'jt8Fk5jFht84GJt';
        if ($http_pass != $_GET['http_pass']) die('Неверный ключ доступа!');

        // Получаем "сырое" тело POST-запроса
        $input = file_get_contents('php://input');

        // Преобразуем JSON → массив
        $data = json_decode($input, true);

        // Проверка на корректный JSON
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "success" => false,
                "error" => "Invalid JSON"
            ]);
            exit;
        }

        // Проверяем влюючен ли модуль
        if (!$this->config->get('config_bfmenu2025')) {
            echo json_encode([
                "success" => false,
                "error" => "Black Friday is not active"
            ]);
            exit;
        }

        // Достаём параметры
        $token = $data['token'] ?? null;
        $promo_code = $data['promo_code'] ?? null;

        // Проверяем обязательные поля
        if (!$token || !$promo_code) {
            echo json_encode([
                "success" => false,
                "error" => "promo_code or token missing"
            ]);
            exit;
        }

        if ($token && $token != $this->token) {
            echo json_encode([
                "success" => false,
                "error" => "Invalid token"
            ]);
            exit;
        }

        $this->load->model('extension/module/fortune');

        $win = $this->model_extension_module_fortune->checkwincode($promo_code, $this->date_start, $this->date_end);

        if ($win) {
            if ($win['used']) {
                echo json_encode([
                    "success" => false,
                    "error" => 'Промокод вже використаний ' . $win['date_used']
                ]);
                exit;
            } else {
                //списание промокода
                //проверяем наличие и активность купона
                $coupon_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "coupon` WHERE code = '" . $this->db->escape($win['code']) . "' AND status = '1'");
                if ($coupon_query->num_rows) {
                    $this->db->query("INSERT INTO `" . DB_PREFIX . "coupon_history` SET coupon_id = '" . (int)$coupon_query->row['coupon_id'] . "', order_id = 0, customer_id = '" . (int)$win['customer_id'] . "', amount = 0, date_added = NOW()");

                    $this->db->query("UPDATE `" . DB_PREFIX . "customer_fortune` SET used = (used + 1), date_used = NOW() WHERE code = '" . $this->db->escape($win['code']) . "'");
                    echo json_encode([
                        "success" => true,
                        "text" => 'Промокод використано'
                    ]);
                    exit;
                }
            }
        } else {
            echo json_encode([
                "success" => false,
                "error" => 'Промокод недійсний'
            ]);
            exit;
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(array()));
    }

    public function getRandomPrize()
    {
        // Призы и их вероятности в процентах
        $prizes = [
            '5p' => 25,
            '7p' => 20,
            '10p' => 15,
//			'bf100'      => 30,
            'secretgift' => 40
        ];

        // Получаем случайное число от 1 до 100
        $rand = mt_rand(1, 100);

        $current = 0;
        foreach ($prizes as $prize => $chance) {
            $current += $chance;
            if ($rand <= $current) {
                return $prize;
            }
        }
    }

    public function win($customer_info)
    {

        $this->load->model('extension/module/fortune');
        /*
        $prizes = array('10p', '15p', 'secretgift', 'bf100');
        $rand_key = array_rand($prizes,1);
        $prize = $prizes[$rand_key];
        */

        $prize = $this->getRandomPrize();

        $data_win = array();
        $data_win['telephone'] = $customer_info['telephone'];

//                    $coupons_array = array('10p', '15p', 'secretgift', 'bf100');
        $coupons_array = array('5p', '7p', '10p', '15p', 'secretgift');
        $chars = '1234567890';
        $numChars = strlen($chars);
        $string = '';
        for ($k = 0; $k < 7; $k++) {
            $string .= substr($chars, rand(1, $numChars) - 1, 1);
        }
        if (in_array($prize, $coupons_array)) {

            $data_win['customer_id'] = $customer_info['customer_id'];
            $data_win['type'] = 'P';
            $data_win['total'] = 0;
            $data_win['logged'] = 1;
            $data_win['shipping'] = 0;
            $data_win['uses_total'] = 1;
            $data_win['uses_customer'] = 1;
            $data_win['status'] = 1;

            if ($prize == '5p') {
                $data_win['name'] = 'Знижка 5%';
                $data_win['code'] = 'BF-' . $string;
                $data_win['discount'] = 5;
            }

            if ($prize == '7p') {
                $data_win['name'] = 'Знижка 7%';
                $data_win['code'] = 'BF-' . $string;
                $data_win['discount'] = 7;
            }

            if ($prize == '10p') {
                $data_win['name'] = 'Знижка 10%';
                $data_win['code'] = 'BF-' . $string;
                $data_win['discount'] = 10;
            }

            if ($prize == '15p') {
                $data_win['name'] = 'Знижка 15%';
                $data_win['code'] = 'BF-' . $string;
                $data_win['discount'] = 15;
            }

            if ($prize == 'secretgift') {
                $data_win['name'] = 'Таємний подарунок';
                $data_win['code'] = 'BF-' . $string;
                $data_win['type'] = 'T';
                $data_win['discount'] = 0;
            }

            if ($prize == 'bf100') {
                $data_win['name'] = '100 бонусів';
                $data_win['code'] = 'BF-' . $string;
                $data_win['type'] = 'B';
                $data_win['discount'] = 0;
            }
            $this->model_extension_module_fortune->addWin2025($data_win, $this->date_end);
            $coupon_id = $this->model_extension_module_fortune->addCoupon2025($data_win, $this->date_end);
            if ($coupon_id) {
                $json['success'] = $data_win['name'];
                $json['code'] = $data_win['code'];
                $this->session->data['coupon'] = $data_win['code'];
            }
        }

        return $json;
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
}
