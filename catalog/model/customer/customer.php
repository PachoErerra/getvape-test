<?php

class ModelCustomerCustomer extends Model
{


    public function getTelegramUsers($onlyActive = false)
    {
        $sql = "SELECT * FROM `telegram_users` LEFT JOIN " . DB_PREFIX . "customer ON telegram_users.user_id =" . DB_PREFIX . "customer.customer_id";

        if ($onlyActive) {
            $sql .= " WHERE telegram_users.is_active = 1";
        }

        $sql .= " ORDER BY `telegram_users`.`first_name` DESC";

        $query = $this->db->query($sql);

        return $query->rows;
    }
 
    public function getTelegramUserIds($onlyActive = true)
    {
        $sql = "SELECT id FROM `telegram_users`";

        if ($onlyActive) {
            $sql .= " WHERE is_active = 1";
        }

        $query = $this->db->query($sql);

        $ids = [];

        foreach ($query->rows as $row) {
            $ids[] = $row['id'];
        }

        return $ids;
    }

    public function countTelegramUsers()
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `telegram_users`");

        return (int)$query->row['total'];
    }

    public function countActiveTelegramUsers()
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `telegram_users` WHERE is_active = 1");

        return (int)$query->row['total'];
    }

    public function setTelegramUserActive($chatId, $active)
    {
        $this->db->query("UPDATE `telegram_users` SET `is_active` = '" . ((int)$active ? 1 : 0) . "', `updated_at` = NOW() WHERE `id` = '" . (int)$chatId . "'");
    }


    public function isTelegramUnsubscribeError($message)
    {
        $message = mb_strtolower((string)$message);

        $needles = [
            'bot was blocked by the user',
            'user is deactivated',
            'chat not found',
            'forbidden: bot was blocked',
            'forbidden: user is deactivated',
            'peer_id_invalid',
        ];

        foreach ($needles as $needle) {
            if (strpos($message, $needle) !== false) {
                return true;
            }
        }

        return false;
    }

    public function getTelegramOrders3And6Months()
    {
        // Сначала узкий диапазон по 2 дням (3 и 6 мес.), потом проверка «это последний заказ».
        // UNION ALL вместо OR — лучше использует индекс по date_added.
        $sql = "
			SELECT cand.telephone, cand.date_added, tu.id AS telegram_user_id
			FROM (
				SELECT customer_id, telephone, date_added
				FROM `" . DB_PREFIX . "order`
				WHERE date_added >= DATE(DATE_SUB(NOW(), INTERVAL 3 MONTH))
					AND date_added < DATE(DATE_SUB(NOW(), INTERVAL 3 MONTH)) + INTERVAL 1 DAY
				UNION ALL
				SELECT customer_id, telephone, date_added
				FROM `" . DB_PREFIX . "order`
				WHERE date_added >= DATE(DATE_SUB(NOW(), INTERVAL 6 MONTH))
					AND date_added < DATE(DATE_SUB(NOW(), INTERVAL 6 MONTH)) + INTERVAL 1 DAY
			) cand
			INNER JOIN telegram_users tu ON tu.user_id = cand.customer_id AND tu.is_active = 1
			WHERE NOT EXISTS (
				SELECT 1
				FROM `" . DB_PREFIX . "order` newer
				WHERE newer.customer_id = cand.customer_id
					AND newer.date_added > cand.date_added
			)
			ORDER BY cand.date_added DESC";

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getLastOrderByCustomerId($customer_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE `customer_id` = '" . $this->db->escape($customer_id) . "' ORDER BY order_id DESC LIMIT 1");

        return $query->row;
    }

    public function getCustomerByPhone($telephone)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE `telephone` LIKE '%" . $this->db->escape($telephone) . "%'");

//        return $query->row['customer_id'];
        if ($query->num_rows) {
            return $query->row['customer_id'];
        } else {
            return 0;
        }
    }

    /** Цифры телефона для сравнения (380XXXXXXXXX). */
    public function normalizeTelephoneDigits($telephone)
    {
        $digits = preg_replace('/\D/', '', (string)$telephone);

        if (strlen($digits) === 10 && $digits[0] === '0') {
            $digits = '38' . $digits;
        }

        if (strlen($digits) === 11 && $digits[0] === '8') {
            $digits = '3' . $digits;
        }

        if (strlen($digits) === 9) {
            $digits = '380' . $digits;
        }

        return $digits;
    }

    /** Поиск customer_id по номеру с сайта или из Telegram-контакта. */
    public function getCustomerIdByTelephoneNormalized($telephone)
    {
        $digits = $this->normalizeTelephoneDigits($telephone);

        if (!$digits) {
            return 0;
        }

        $variants = array_unique([
            '+' . $digits,
            $digits,
        ]);

        if (strlen($digits) >= 12 && substr($digits, 0, 3) === '380') {
            $variants[] = '0' . substr($digits, 3);
        }

        foreach ($variants as $variant) {
            $customer_id = $this->getCustomerByPhone($variant);

            if ($customer_id) {
                return (int)$customer_id;
            }
        }

        if (strlen($digits) >= 9) {
            $last9 = substr($digits, -9);
            $query = $this->db->query("SELECT `customer_id` FROM `" . DB_PREFIX . "customer` WHERE REPLACE(REPLACE(REPLACE(REPLACE(REPLACE(`telephone`, '+', ''), ' ', ''), '-', ''), '(', ''), ')', '') LIKE '%" . $this->db->escape($last9) . "' ORDER BY `customer_id` DESC LIMIT 1");

            if ($query->num_rows) {
                return (int)$query->row['customer_id'];
            }
        }

        return 0;
    }

    /** Telegram id по привязанному customer_id для введённого на сайте телефона. */
    public function getTelegramCustomerIdByTelephone($telephone)
    {
        $customer_id = $this->getCustomerIdByTelephoneNormalized($telephone);

        if (!$customer_id) {
            return 0;
        }

        return $this->getTelegramCustomerByCustomerId($customer_id);
    }

    /** from из webhook: массив, JSON-строка или объект Telegram SDK. */
    public function telegramPayloadFrom($from)
    {
        if (is_array($from)) {
            return $from;
        }

        if (is_object($from)) {
            if (method_exists($from, 'toArray')) {
                return $from->toArray();
            }

            return (array)$from;
        }

        if (is_string($from)) {
            $decoded = json_decode($from, true);

            return is_array($decoded) ? $decoded : [];
        }

        return [];
    } 


    public function getCustomerPhoneById($customer_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE `customer_id` = '" . $this->db->escape($customer_id) . "'");

        return $query->row['telephone'];
    }

    public function getCustomerInfo($customer_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer` WHERE `customer_id` = '" . $this->db->escape($customer_id) . "'");

        return $query->row;
    }


    public function create($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer SET customer_group_id = '" . (int)$data['customer_group_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', custom_field = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : json_encode(array())) . "', newsletter = '" . (int)$data['newsletter'] . "', salt = '" . $this->db->escape($salt = token(9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "', status = '" . (int)$data['status'] . "', safe = '" . (int)$data['safe'] . "', date_added = NOW()");


        return $this->db->getLastId();

    }

    public function getTelegramCustomerByChatId($chatId)
    {
        $query = $this->db->query("SELECT * FROM `telegram_users` WHERE `id` = '" . $this->db->escape($chatId) . "'");

//        return $query->row['id'];
        if ($query->num_rows) {
            return $query->row['id'];
        } else {
            return 0;
        }
    }

    public function setTGlogin($id)
    {
        $query = $this->db->query("SELECT * FROM `telegram_users` WHERE `id` = '" . (int)$id . "'");

        if ($query->num_rows) {
            $telephone = $this->getCustomerPhoneById($query->row['user_id']);
            $this->db->query("INSERT INTO `telegram_login` SET tg_id = '" . (int)$query->row['id'] . "', user_id = '" . (int)$query->row['user_id'] . "', name = '" . $this->db->escape($query->row['first_name']) . "', telephone = '" . $this->db->escape($telephone) . "', date_added = NOW()");
        }
    }

    public function getTelegramCustomerByCustomerId($user_id, $onlyActive = true)
    {
        $sql = "SELECT * FROM `telegram_users` WHERE `user_id` = '" . $this->db->escape($user_id) . "'";

        if ($onlyActive) {
            $sql .= " AND is_active = 1";
        }

        $query = $this->db->query($sql);

        if ($query->num_rows) {
            return $query->row['id'];
        }

        return 0;
    }

    public function getTelegramLoginCodeInfo($tgId)
    {

        $query = $this->db->query("SELECT * FROM `telegram_login_code` WHERE `tg_id` = '" . $this->db->escape($tgId) . "'");

        return $query->row;
    }

    public function addTelegramCode($telegramUserId, $code, $time)
    {

        $this->db->query("DELETE FROM `telegram_login_code` WHERE tg_id = '" . $this->db->escape($telegramUserId) . "'");
        $this->db->query("INSERT INTO `telegram_login_code` SET tg_id = '" . $this->db->escape($telegramUserId) . "', code = '" . $this->db->escape($code) . "', time = '" . (int)$time . "'");
    }

    public function deleteTelegramCode($telegramUserId)
    {
        $this->db->query("DELETE FROM `telegram_login_code` WHERE tg_id = '" . $this->db->escape($telegramUserId) . "'");
    }


    public function getTelegramCode($telegramUserId, $code)
    {
        $query = $this->db->query("SELECT * FROM `telegram_login_code` WHERE `tg_id` = '" . $this->db->escape($telegramUserId) . "' AND `code` = '" . $this->db->escape($code) . "' ");

        return $query->row;
    }


    public function createTelegramCustomer($data, $user_id = 0)
    {
        $id = isset($data['id']) ? (int)$data['id'] : 0;

        if (!$id) {
            return 0;
        }

        $user_id = (int)$user_id;

        $existing = $this->getTelegramCustomerByChatId($id);

        if ($existing) {
            if ($user_id) {
                $this->updateTelegramCustomerUserId($user_id, $id);
            }

            $this->setTelegramUserActive($id, 1);

            return $existing;
        }

        $is_bot = !empty($data['is_bot']) ? 1 : 0;
        $first_name = isset($data['first_name']) ? (string)$data['first_name'] : '';
        $last_name = isset($data['last_name']) ? (string)$data['last_name'] : '';
        $username = isset($data['username']) ? (string)$data['username'] : '';
        $language_code = isset($data['language_code']) ? (int)$data['language_code'] : 0;

        $this->db->query("INSERT INTO telegram_users SET id = '" . $id . "', user_id = '" . $user_id . "', is_bot = '" . $is_bot . "', first_name = '" . $this->db->escape($first_name) . "', last_name = '" . $this->db->escape($last_name) . "', username = '" . $this->db->escape($username) . "', language_code = '" . $language_code . "', is_active = 1, created_at = NOW(), updated_at = NOW()");

        return $id;
    }


    public function updateTelegramCustomerUserId($user_id, $id)
    {
        $this->db->query("UPDATE `telegram_users` SET `user_id` = '" . (int)$user_id . "' WHERE `id` = '" . (int)$id . "'");
    }

    public function getUserIdByTelegramCustomerId($telegramCustomerId)
    {
        $query = $this->db->query("SELECT * FROM `telegram_users` WHERE `id` = '" . $this->db->escape($telegramCustomerId) . "'");

        return $query->row['user_id'];
    }


    public function updateTelegramLoginTokenByUserId($userId, $token, $tokenExpiresAt)
    {
        $userId = (int)$userId;

        $this->db->query("
        UPDATE oc_customer 
        SET 
            telegram_login_token = '" . $this->db->escape($token) . "',
            telegram_login_token_expires_at = '" . $this->db->escape($tokenExpiresAt) . "'
        WHERE customer_id = {$userId}
        ");
    }

    public function getCustomerByLoginToken($token)
    {
        $query = $this->db->query("
        SELECT * 
        FROM oc_customer 
        WHERE telegram_login_token = '" . $this->db->escape($token) . "'
        LIMIT 1
    ");

        return $query->row;
    }

    public function editToken($customer_id, $token)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "customer SET token = '" . $this->db->escape($token) . "' WHERE customer_id = '" . (int)$customer_id . "'");
    }

    public function loginById($customer_id)

    {

        $customer_info = $this->getCustomerInfo($customer_id);

        if ($customer_info) {

            $this->session->data['customer_id'] = $customer_info['customer_id'];

            $this->session->data['customer_group_id'] = $customer_info['customer_group_id'];

            $this->session->data['customer'] = $customer_info;

            return true;

        }

        return false;

    }

    public function clearLoginToken($customerId)
    {
        $customerId = (int)$customerId;

        $this->db->query("
        UPDATE oc_customer 
        SET 
            telegram_login_token = NULL,
            telegram_login_token_expires_at = NULL
        WHERE customer_id = {$customerId}
    ");
    }
}