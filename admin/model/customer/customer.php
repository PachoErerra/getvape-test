<?php

class ModelCustomerCustomer extends Model
{
    public function addCustomer($data)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer SET customer_group_id = '" . (int)$data['customer_group_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', custom_field = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : json_encode(array())) . "', newsletter = '" . (int)$data['newsletter'] . "', salt = '" . $this->db->escape($salt = token(9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "', status = '" . (int)$data['status'] . "', safe = '" . (int)$data['safe'] . "', date_added = NOW()");

        $customer_id = $this->db->getLastId();

        if (isset($data['address'])) {
            foreach ($data['address'] as $address) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "address SET customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($address['firstname']) . "', lastname = '" . $this->db->escape($address['lastname']) . "', company = '" . $this->db->escape($address['company']) . "', address_1 = '" . $this->db->escape($address['address_1']) . "', address_2 = '" . $this->db->escape($address['address_2']) . "', city = '" . $this->db->escape($address['city']) . "', postcode = '" . $this->db->escape($address['postcode']) . "', country_id = '" . (int)$address['country_id'] . "', zone_id = '" . (int)$address['zone_id'] . "', custom_field = '" . $this->db->escape(isset($address['custom_field']) ? json_encode($address['custom_field']) : json_encode(array())) . "'");

                if (isset($address['default'])) {
                    $address_id = $this->db->getLastId();

                    $this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
                }
            }
        }

        if ($data['affiliate']) {
            $this->db->query("INSERT INTO " . DB_PREFIX . "customer_affiliate SET customer_id = '" . (int)$customer_id . "', company = '" . $this->db->escape($data['company']) . "', website = '" . $this->db->escape($data['website']) . "', tracking = '" . $this->db->escape($data['tracking']) . "', commission = '" . (float)$data['commission'] . "', tax = '" . $this->db->escape($data['tax']) . "', payment = '" . $this->db->escape($data['payment']) . "', cheque = '" . $this->db->escape($data['cheque']) . "', paypal = '" . $this->db->escape($data['paypal']) . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "', custom_field = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : json_encode(array())) . "', status = '" . (int)$data['affiliate'] . "', date_added = NOW()");
        }

        return $customer_id;
    }

    public function editCustomer($customer_id, $data)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "customer SET customer_group_id = '" . (int)$data['customer_group_id'] . "', firstname = '" . $this->db->escape($data['firstname']) . "', lastname = '" . $this->db->escape($data['lastname']) . "', email = '" . $this->db->escape($data['email']) . "', telephone = '" . $this->db->escape($data['telephone']) . "', fax = '" . $this->db->escape($data['fax']) . "', custom_field = '" . $this->db->escape(isset($data['custom_field']) ? json_encode($data['custom_field']) : json_encode(array())) . "', newsletter = '" . (int)$data['newsletter'] . "', status = '" . (int)$data['status'] . "', safe = '" . (int)$data['safe'] . "' WHERE customer_id = '" . (int)$customer_id . "'");

        if ($data['password']) {
            $this->db->query("UPDATE " . DB_PREFIX . "customer SET salt = '" . $this->db->escape($salt = token(9)) . "', password = '" . $this->db->escape(sha1($salt . sha1($salt . sha1($data['password'])))) . "' WHERE customer_id = '" . (int)$customer_id . "'");
        }

        $this->db->query("DELETE FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$customer_id . "'");

        if (isset($data['address'])) {
            foreach ($data['address'] as $address) {
                $this->db->query("INSERT INTO " . DB_PREFIX . "address SET address_id = '" . (int)$address['address_id'] . "', customer_id = '" . (int)$customer_id . "', firstname = '" . $this->db->escape($address['firstname']) . "', lastname = '" . $this->db->escape($address['lastname']) . "', company = '" . $this->db->escape($address['company']) . "', address_1 = '" . $this->db->escape($address['address_1']) . "', address_2 = '" . $this->db->escape($address['address_2']) . "', city = '" . $this->db->escape($address['city']) . "', postcode = '" . $this->db->escape($address['postcode']) . "', country_id = '" . (int)$address['country_id'] . "', zone_id = '" . (int)$address['zone_id'] . "', custom_field = '" . $this->db->escape(isset($address['custom_field']) ? json_encode($address['custom_field']) : json_encode(array())) . "'");

                if (isset($address['default'])) {
                    $address_id = $this->db->getLastId();

                    $this->db->query("UPDATE " . DB_PREFIX . "customer SET address_id = '" . (int)$address_id . "' WHERE customer_id = '" . (int)$customer_id . "'");
                }
            }
        }

        if ($data['affiliate']) {
            $this->db->query("REPLACE INTO " . DB_PREFIX . "customer_affiliate SET customer_id = '" . (int)$customer_id . "', company = '" . $this->db->escape($data['company']) . "', website = '" . $this->db->escape($data['website']) . "', tracking = '" . $this->db->escape($data['tracking']) . "', commission = '" . (float)$data['commission'] . "', tax = '" . $this->db->escape($data['tax']) . "', payment = '" . $this->db->escape($data['payment']) . "', cheque = '" . $this->db->escape($data['cheque']) . "', paypal = '" . $this->db->escape($data['paypal']) . "', bank_name = '" . $this->db->escape($data['bank_name']) . "', bank_branch_number = '" . $this->db->escape($data['bank_branch_number']) . "', bank_swift_code = '" . $this->db->escape($data['bank_swift_code']) . "', bank_account_name = '" . $this->db->escape($data['bank_account_name']) . "', bank_account_number = '" . $this->db->escape($data['bank_account_number']) . "', status = '" . (int)$data['affiliate'] . "', date_added = NOW()");
        }
    }

    public function editToken($customer_id, $token)
    {
        $this->db->query("UPDATE " . DB_PREFIX . "customer SET token = '" . $this->db->escape($token) . "' WHERE customer_id = '" . (int)$customer_id . "'");
    }

    public function deleteCustomer($customer_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_activity WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_affiliate WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_approval WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_history WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_transaction WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_ip WHERE customer_id = '" . (int)$customer_id . "'");
        $this->db->query("DELETE FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$customer_id . "'");
    }

    public function getCustomer($customer_id)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "customer WHERE customer_id = '" . (int)$customer_id . "'");

        return $query->row;
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

    public function searchTelegramUsers($search, $limit = 30)
    {
        $search = trim((string)$search);

        if ($search === '') {
            return [];
        }

        $escaped = $this->db->escape($search);
        $like = "'%" . $escaped . "%'";
        $limit = max(1, min(50, (int)$limit));

        $sql = "
            SELECT tu.id, tu.first_name, tu.last_name, tu.username, c.telephone
            FROM `telegram_users` tu
            LEFT JOIN " . DB_PREFIX . "customer c ON tu.user_id = c.customer_id
            WHERE tu.first_name LIKE " . $like . "
               OR tu.last_name LIKE " . $like . "
               OR tu.username LIKE " . $like . "
               OR c.telephone LIKE " . $like . "
               OR CAST(tu.id AS CHAR) LIKE " . $like . "
            ORDER BY tu.first_name ASC
            LIMIT " . $limit;

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getLastOrderByCustomerId($customer_id)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "order` WHERE `customer_id` = '" . $this->db->escape($customer_id) . "' ORDER BY order_id DESC LIMIT 1");

        return $query->row;
    }

    public function getCustomersNoOrdersUpTo3MonthsWithTelegram(): array
    {
        $query = $this->db->query("
        SELECT o.customer_id, MAX(o.date_added) AS last_order_date, tu.*
        FROM `" . DB_PREFIX . "order` o
        INNER JOIN telegram_users tu ON o.customer_id = tu.user_id AND tu.is_active = 1
        GROUP BY o.customer_id
        HAVING last_order_date < NOW() - INTERVAL 1 MONTH
           AND last_order_date >= NOW() - INTERVAL 3 MONTH
        ORDER BY last_order_date DESC
    ");

        return $query->rows;
    }

    public function getCustomersNoOrdersMoreThan3MonthsWithTelegram(): array
    {
        $query = $this->db->query("
        SELECT o.customer_id, MAX(o.date_added) AS last_order_date, tu.*
        FROM `" . DB_PREFIX . "order` o
        INNER JOIN telegram_users tu ON o.customer_id = tu.user_id AND tu.is_active = 1
        GROUP BY o.customer_id
        HAVING last_order_date < NOW() - INTERVAL 3 MONTH
        ORDER BY last_order_date DESC
    ");

        return $query->rows;
    }

    public function countCustomersNoOrdersMoreThan3MonthsWithTelegram(): int
    {
        $query = $this->db->query("
        SELECT COUNT(*) AS total
        FROM (
            SELECT o.customer_id
            FROM `" . DB_PREFIX . "order` o
            INNER JOIN telegram_users tu ON o.customer_id = tu.user_id AND tu.is_active = 1
            GROUP BY o.customer_id
            HAVING MAX(o.date_added) < NOW() - INTERVAL 3 MONTH
        ) AS sub
    ");

        return (int)$query->row['total'];
    }

    public function getCustomerByEmail($email)
    {
        $query = $this->db->query("SELECT DISTINCT * FROM " . DB_PREFIX . "customer WHERE LCASE(email) = '" . $this->db->escape(utf8_strtolower($email)) . "'");

        return $query->row;
    }

    public function getCustomers($data = array())
    {
        $sql = "SELECT *, CONCAT(c.firstname, ' ', c.lastname) AS name, cgd.name AS customer_group FROM " . DB_PREFIX . "customer c LEFT JOIN " . DB_PREFIX . "customer_group_description cgd ON (c.customer_group_id = cgd.customer_group_id)";

        if (!empty($data['filter_affiliate'])) {
            $sql .= " LEFT JOIN " . DB_PREFIX . "customer_affiliate ca ON (c.customer_id = ca.customer_id)";
        }

        $sql .= " WHERE cgd.language_id = '" . (int)$this->config->get('config_language_id') . "'";

        $implode = array();

        if (!empty($data['filter_name'])) {
            $implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_email'])) {
            $implode[] = "c.email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
        }

        if (!empty($data['filter_telephone'])) {
            $implode[] = "c.telephone LIKE '%" . $this->db->escape($data['filter_telephone']) . "%'";
        }

        if (isset($data['filter_newsletter']) && !is_null($data['filter_newsletter'])) {
            $implode[] = "c.newsletter = '" . (int)$data['filter_newsletter'] . "'";
        }

        if (!empty($data['filter_customer_group_id'])) {
            $implode[] = "c.customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
        }

        if (!empty($data['filter_affiliate'])) {
            $implode[] = "ca.status = '" . (int)$data['filter_affiliate'] . "'";
        }

        if (!empty($data['filter_ip'])) {
            $implode[] = "c.customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '" . $this->db->escape($data['filter_ip']) . "')";
        }

        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $implode[] = "c.status = '" . (int)$data['filter_status'] . "'";
        }

        if (!empty($data['filter_date_added'])) {
            $implode[] = "DATE(c.date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }

        if (!empty($data['filter_date_start'])) {
            $implode[] = "DATE(c.date_added) >= DATE('" . $this->db->escape($data['filter_date_start']) . "')";
        }

        if (!empty($data['filter_date_end'])) {
            $implode[] = "DATE(c.date_added) <= DATE('" . $this->db->escape($data['filter_date_end']) . "')";
        }

        if ($implode) {
            $sql .= " AND " . implode(" AND ", $implode);
        }

        $sort_data = array(
            'name',
            'c.email',
            'customer_group',
            'c.status',
            'c.ip',
            'c.date_added'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY name";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getAddress($address_id)
    {
        $address_query = $this->db->query("SELECT * FROM " . DB_PREFIX . "address WHERE address_id = '" . (int)$address_id . "'");

        if ($address_query->num_rows) {
            $country_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "country` WHERE country_id = '" . (int)$address_query->row['country_id'] . "'");

            if ($country_query->num_rows) {
                $country = $country_query->row['name'];
                $iso_code_2 = $country_query->row['iso_code_2'];
                $iso_code_3 = $country_query->row['iso_code_3'];
                $address_format = $country_query->row['address_format'];
            } else {
                $country = '';
                $iso_code_2 = '';
                $iso_code_3 = '';
                $address_format = '';
            }

            $zone_query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "zone` WHERE zone_id = '" . (int)$address_query->row['zone_id'] . "'");

            if ($zone_query->num_rows) {
                $zone = $zone_query->row['name'];
                $zone_code = $zone_query->row['code'];
            } else {
                $zone = '';
                $zone_code = '';
            }

            return array(
                'address_id' => $address_query->row['address_id'],
                'customer_id' => $address_query->row['customer_id'],
                'firstname' => $address_query->row['firstname'],
                'lastname' => $address_query->row['lastname'],
                'company' => $address_query->row['company'],
                'address_1' => $address_query->row['address_1'],
                'address_2' => $address_query->row['address_2'],
                'postcode' => $address_query->row['postcode'],
                'city' => $address_query->row['city'],
                'zone_id' => $address_query->row['zone_id'],
                'zone' => $zone,
                'zone_code' => $zone_code,
                'country_id' => $address_query->row['country_id'],
                'country' => $country,
                'iso_code_2' => $iso_code_2,
                'iso_code_3' => $iso_code_3,
                'address_format' => $address_format,
                'custom_field' => json_decode($address_query->row['custom_field'], true)
            );
        }
    }

    public function getAddresses($customer_id)
    {
        $address_data = array();

        $query = $this->db->query("SELECT address_id FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$customer_id . "'");

        foreach ($query->rows as $result) {
            $address_info = $this->getAddress($result['address_id']);

            if ($address_info) {
                $address_data[$result['address_id']] = $address_info;
            }
        }

        return $address_data;
    }

    public function getTotalCustomers($data = array())
    {
        $sql = "SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer c";

        $implode = array();

        if (!empty($data['filter_name'])) {
            $implode[] = "CONCAT(firstname, ' ', lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if (!empty($data['filter_email'])) {
            $implode[] = "email LIKE '" . $this->db->escape($data['filter_email']) . "%'";
        }

        if (!empty($data['filter_telephone'])) {
            $implode[] = "telephone LIKE '%" . $this->db->escape($data['filter_telephone']) . "%'";
        }

        if (isset($data['filter_newsletter']) && !is_null($data['filter_newsletter'])) {
            $implode[] = "newsletter = '" . (int)$data['filter_newsletter'] . "'";
        }

        if (!empty($data['filter_customer_group_id'])) {
            $implode[] = "customer_group_id = '" . (int)$data['filter_customer_group_id'] . "'";
        }

        if (!empty($data['filter_ip'])) {
            $implode[] = "customer_id IN (SELECT customer_id FROM " . DB_PREFIX . "customer_ip WHERE ip = '" . $this->db->escape($data['filter_ip']) . "')";
        }

        if (isset($data['filter_status']) && $data['filter_status'] !== '') {
            $implode[] = "status = '" . (int)$data['filter_status'] . "'";
        }

        if (!empty($data['filter_date_added'])) {
            $implode[] = "DATE(date_added) = DATE('" . $this->db->escape($data['filter_date_added']) . "')";
        }

        if (!empty($data['filter_date_start'])) {
            $implode[] = "DATE(c.date_added) >= DATE('" . $this->db->escape($data['filter_date_start']) . "')";
        }

        if (!empty($data['filter_date_end'])) {
            $implode[] = "DATE(c.date_added) <= DATE('" . $this->db->escape($data['filter_date_end']) . "')";
        }

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getAffliateByTracking($tracking)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_affiliate WHERE tracking = '" . $this->db->escape($tracking) . "'");

        return $query->row;
    }

    public function getAffiliate($customer_id)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_affiliate WHERE customer_id = '" . (int)$customer_id . "'");

        return $query->row;
    }

    public function getAffiliates($data = array())
    {
        $sql = "SELECT DISTINCT *, CONCAT(c.firstname, ' ', c.lastname) AS name FROM " . DB_PREFIX . "customer_affiliate ca LEFT JOIN " . DB_PREFIX . "customer c ON (ca.customer_id = c.customer_id)";

        $implode = array();

        if (!empty($data['filter_name'])) {
            $implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql . "ORDER BY name");

        return $query->rows;
    }

    public function getTotalAffiliates($data = array())
    {
        $sql = "SELECT DISTINCT COUNT(*) AS total FROM " . DB_PREFIX . "customer_affiliate ca LEFT JOIN " . DB_PREFIX . "customer c ON (ca.customer_id = c.customer_id)";

        $implode = array();

        if (!empty($data['filter_name'])) {
            $implode[] = "CONCAT(c.firstname, ' ', c.lastname) LIKE '%" . $this->db->escape($data['filter_name']) . "%'";
        }

        if ($implode) {
            $sql .= " WHERE " . implode(" AND ", $implode);
        }

        return $query->row['total'];
    }

    public function getTotalAddressesByCustomerId($customer_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "address WHERE customer_id = '" . (int)$customer_id . "'");

        return $query->row['total'];
    }

    public function getTotalAddressesByCountryId($country_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "address WHERE country_id = '" . (int)$country_id . "'");

        return $query->row['total'];
    }

    public function getTotalAddressesByZoneId($zone_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "address WHERE zone_id = '" . (int)$zone_id . "'");

        return $query->row['total'];
    }

    public function getTotalCustomersByCustomerGroupId($customer_group_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer WHERE customer_group_id = '" . (int)$customer_group_id . "'");

        return $query->row['total'];
    }

    public function addHistory($customer_id, $comment)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_history SET customer_id = '" . (int)$customer_id . "', comment = '" . $this->db->escape(strip_tags($comment)) . "', date_added = NOW()");
    }

    public function getHistories($customer_id, $start = 0, $limit = 10)
    {
        if ($start < 0) {
            $start = 0;
        }

        if ($limit < 1) {
            $limit = 10;
        }

        $query = $this->db->query("SELECT comment, date_added FROM " . DB_PREFIX . "customer_history WHERE customer_id = '" . (int)$customer_id . "' ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

        return $query->rows;
    }

    public function getTotalHistories($customer_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_history WHERE customer_id = '" . (int)$customer_id . "'");

        return $query->row['total'];
    }

    public function getCustomerOrders($customer_id, $start = 0, $limit = 10)
    {
        if ($start < 0) {
            $start = 0;
        }

        if ($limit < 1) {
            $limit = 10;
        }

        $query = $this->db->query("SELECT o.order_id, o.order_status_id, o.total, o.currency_code, o.currency_value, o.date_added, (SELECT os.name FROM " . DB_PREFIX . "order_status os WHERE os.order_status_id = o.order_status_id AND os.language_id = '" . (int)$this->config->get('config_language_id') . "') AS order_status FROM `" . DB_PREFIX . "order` o WHERE o.customer_id = '" . (int)$customer_id . "' ORDER BY o.order_id DESC LIMIT " . (int)$start . "," . (int)$limit);

        return $query->rows;
    }

    public function getTotalCustomerOrders($customer_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `" . DB_PREFIX . "order` WHERE customer_id = '" . (int)$customer_id . "'");

        return $query->row['total'];
    }

    public function getCustomerOrderSummary($customer_id)
    {
        $complete_statuses = $this->getOrderStatusIds('config_complete_status', array('заверш%', 'complete%', 'виконан%'));
        $processing_statuses = $this->getOrderStatusIds('config_processing_status', array('оброб%', 'дороз%', 'очіку%', 'processing%', 'нов%', 'в дорозі%'));
        $cancelled_statuses = $this->getOrderStatusIdsByName(array('скас%', 'ануль%', 'cancel%', 'відмін%'));

        $complete_sql = $this->buildOrderStatusCondition($complete_statuses);
        $processing_sql = $this->buildOrderStatusCondition($processing_statuses);

        if ($cancelled_statuses) {
            $cancelled_sql = $this->buildOrderStatusCondition($cancelled_statuses);
        } else {
            $excluded_statuses = array_unique(array_merge($complete_statuses, $processing_statuses, array(0)));
            $cancelled_sql = $excluded_statuses ? "order_status_id > 0 AND order_status_id NOT IN (" . implode(',', $excluded_statuses) . ")" : "0";
        }

        $query = $this->db->query("SELECT COUNT(*) AS total, SUM(CASE WHEN " . $complete_sql . " THEN 1 ELSE 0 END) AS complete_count, SUM(CASE WHEN " . $complete_sql . " THEN total ELSE 0 END) AS complete_sum, SUM(CASE WHEN " . $processing_sql . " THEN 1 ELSE 0 END) AS processing_count, SUM(CASE WHEN " . $cancelled_sql . " THEN 1 ELSE 0 END) AS cancelled_count FROM `" . DB_PREFIX . "order` WHERE customer_id = '" . (int)$customer_id . "'");

        return $query->row;
    }

    private function getOrderStatusIds($config_key, array $name_patterns)
    {
        $ids = array();

        if ($config_key) {
            $config_value = $this->config->get($config_key);

            if (is_array($config_value)) {
                $ids = array_map('intval', $config_value);
            }
        }

        $name_ids = $this->getOrderStatusIdsByName($name_patterns);

        return array_values(array_unique(array_filter(array_merge($ids, $name_ids))));
    }

    private function getOrderStatusIdsByName(array $name_patterns)
    {
        $conditions = array();

        foreach ($name_patterns as $pattern) {
            $conditions[] = "LCASE(name) LIKE '" . $this->db->escape(utf8_strtolower($pattern)) . "'";
        }

        if (!$conditions) {
            return array();
        }

        $query = $this->db->query("SELECT order_status_id FROM " . DB_PREFIX . "order_status WHERE language_id = '" . (int)$this->config->get('config_language_id') . "' AND (" . implode(' OR ', $conditions) . ")");

        $ids = array();

        foreach ($query->rows as $row) {
            $ids[] = (int)$row['order_status_id'];
        }

        return $ids;
    }

    private function buildOrderStatusCondition(array $status_ids)
    {
        return $status_ids ? "order_status_id IN (" . implode(',', $status_ids) . ")" : "0";
    }

    public function addTransaction($customer_id, $description = '', $amount = '', $order_id = 0)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_transaction SET customer_id = '" . (int)$customer_id . "', order_id = '" . (int)$order_id . "', description = '" . $this->db->escape($description) . "', amount = '" . (float)$amount . "', date_added = NOW()");
    }

    public function deleteTransactionByOrderId($order_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_transaction WHERE order_id = '" . (int)$order_id . "'");
    }

    public function getTransactions($customer_id, $start = 0, $limit = 10)
    {
        if ($start < 0) {
            $start = 0;
        }

        if ($limit < 1) {
            $limit = 10;
        }

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_transaction WHERE customer_id = '" . (int)$customer_id . "' ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

        return $query->rows;
    }

    public function getTotalTransactions($customer_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total  FROM " . DB_PREFIX . "customer_transaction WHERE customer_id = '" . (int)$customer_id . "'");

        return $query->row['total'];
    }

    public function getTransactionTotal($customer_id)
    {
        $query = $this->db->query("SELECT SUM(amount) AS total FROM " . DB_PREFIX . "customer_transaction WHERE customer_id = '" . (int)$customer_id . "'");

        return $query->row['total'];
    }

    public function getTotalTransactionsByOrderId($order_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_transaction WHERE order_id = '" . (int)$order_id . "'");

        return $query->row['total'];
    }

    public function addReward($customer_id, $description = '', $points = '', $order_id = 0)
    {
        $this->db->query("INSERT INTO " . DB_PREFIX . "customer_reward SET customer_id = '" . (int)$customer_id . "', order_id = '" . (int)$order_id . "', points = '" . (int)$points . "', description = '" . $this->db->escape($description) . "', date_added = NOW()");
    }

    public function deleteReward($order_id)
    {
        $this->db->query("DELETE FROM " . DB_PREFIX . "customer_reward WHERE order_id = '" . (int)$order_id . "' AND points > 0");
    }

    public function getRewards($customer_id, $start = 0, $limit = 10)
    {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$customer_id . "' ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

        return $query->rows;
    }

    public function getTotalRewards($customer_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$customer_id . "'");

        return $query->row['total'];
    }

    public function getRewardTotal($customer_id)
    {
        $query = $this->db->query("SELECT SUM(points) AS total FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$customer_id . "'");

        return $query->row['total'];
    }

    public function getTotalCustomerRewardsByOrderId($order_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_reward WHERE order_id = '" . (int)$order_id . "' AND points > 0");

        return $query->row['total'];
    }

    public function getIps($customer_id, $start = 0, $limit = 10)
    {
        if ($start < 0) {
            $start = 0;
        }
        if ($limit < 1) {
            $limit = 10;
        }

        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "customer_ip WHERE customer_id = '" . (int)$customer_id . "' ORDER BY date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

        return $query->rows;
    }

    public function getTotalIps($customer_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_ip WHERE customer_id = '" . (int)$customer_id . "'");

        return $query->row['total'];
    }

    public function getTotalCustomersByIp($ip)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "customer_ip WHERE ip = '" . $this->db->escape($ip) . "'");

        return $query->row['total'];
    }

    public function getTotalLoginAttempts($email)
    {
        $query = $this->db->query("SELECT * FROM `" . DB_PREFIX . "customer_login` WHERE `email` = '" . $this->db->escape($email) . "'");

        return $query->row;
    }

    public function deleteLoginAttempts($email)
    {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "customer_login` WHERE `email` = '" . $this->db->escape($email) . "'");
    }


    public function getTotalLogins($data)
    {

        $date_now = new DateTime(date('Y-m-d H:i:s'));

        $sql = "SELECT COUNT(*) AS total FROM `telegram_login` WHERE login_id != 0 ";

        if (!empty($data['filter_prize'])) {
            $sql .= " AND prize IN ('" . implode("','", $data['filter_prize']) . "')";
        }
        if (!empty($data['filter_order_id'])) {
            $sql .= " AND order_id = '" . (int)$data['filter_order_id'] . "'";
        }

        if (!empty($data['filter_customer'])) {
            $sql .= " AND telephone LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
        }

        if (!empty($data['filter_date_added_start']) && !empty($data['filter_date_added_end'])) {
            $sql .= " AND date_added >= '" . $this->db->escape($data['filter_date_added_start']) . "' AND date_added <='" . $this->db->escape($data['filter_date_added_end']) . "'";
        }
        if (!empty($data['filter_date_added_start']) && empty($data['filter_date_added_end'])) {
            $sql .= " AND date_added >= '" . $this->db->escape($data['filter_date_added_start']) . "'";
        }
        if (empty($data['filter_date_added_start']) && !empty($data['filter_date_added_end'])) {
            $sql .= " AND date_added <='" . $this->db->escape($data['filter_date_added_end']) . "'";
        }

        if (!empty($data['filter_date_used_start']) && !empty($data['filter_date_used_end'])) {
            $sql .= " AND date_used >= '" . $this->db->escape($data['filter_date_used_start']) . "' AND date_used <='" . $this->db->escape($data['filter_date_used_end']) . "'";
        }
        if (!empty($data['filter_date_used_start']) && empty($data['filter_date_used_end'])) {
            $sql .= " AND date_used >= '" . $this->db->escape($data['filter_date_used_start']) . "'";
        }
        if (empty($data['filter_date_used_start']) && !empty($data['filter_date_used_end'])) {
            $sql .= " AND date_used <='" . $this->db->escape($data['filter_date_used_end']) . "'";
        }

        if (!empty($data['filter_date_modified'])) {
            $sql .= " AND DATE(date_modified) = DATE('" . $this->db->escape($data['filter_date_modified']) . "')";
        }

        if (!empty($data['filter_used'])) {
            $sql .= " AND used >= '" . (float)$data['filter_used'] . "'";
        }

        if (!empty($data['filter_overdue'])) {
            $sql .= " AND date_valid <= '" . $date_now->format('Y-m-d H:i:s') . "'";
            $sql .= " AND used < 1 ";
        }
        if (!empty($data['filter_active'])) {
            $sql .= " AND date_valid >= '" . $date_now->format('Y-m-d H:i:s') . "'";
            $sql .= " AND used < 1 ";
        }

        $query = $this->db->query($sql);

        return $query->row['total'];
    }

    public function getLogins($data)
    {

        $date_now = new DateTime(date('Y-m-d H:i:s'));

        $sql = "SELECT * FROM `telegram_login` WHERE login_id != 0 ";

        if (!empty($data['filter_prize'])) {
            $sql .= " AND prize IN ('" . implode("','", $data['filter_prize']) . "')";
        }
        if (!empty($data['filter_order_id'])) {
            $sql .= " AND o.order_id = '" . (int)$data['filter_order_id'] . "'";
        }

        if (!empty($data['filter_customer'])) {
            $sql .= " AND telephone LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
        }


        if (!empty($data['filter_date_added_start']) && !empty($data['filter_date_added_end'])) {
            $sql .= " AND date_added >= '" . $this->db->escape($data['filter_date_added_start']) . "' AND date_added <='" . $this->db->escape($data['filter_date_added_end']) . "'";
        }
        if (!empty($data['filter_date_added_start']) && empty($data['filter_date_added_end'])) {
            $sql .= " AND date_added >= '" . $this->db->escape($data['filter_date_added_start']) . "'";
        }
        if (empty($data['filter_date_added_start']) && !empty($data['filter_date_added_end'])) {
            $sql .= " AND date_added <='" . $this->db->escape($data['filter_date_added_end']) . "'";
        }

        if (!empty($data['filter_date_used_start']) && !empty($data['filter_date_used_end'])) {
            $sql .= " AND date_used >= '" . $this->db->escape($data['filter_date_used_start']) . "' AND date_used <='" . $this->db->escape($data['filter_date_used_end']) . "'";
        }
        if (!empty($data['filter_date_used_start']) && empty($data['filter_date_used_end'])) {
            $sql .= " AND date_used >= '" . $this->db->escape($data['filter_date_used_start']) . "'";
        }
        if (empty($data['filter_date_used_start']) && !empty($data['filter_date_used_end'])) {
            $sql .= " AND date_used <='" . $this->db->escape($data['filter_date_used_end']) . "'";
        }


        if (!empty($data['filter_used'])) {
            $sql .= " AND used >= '" . (float)$data['filter_used'] . "'";
        }

        if (!empty($data['filter_overdue'])) {
            $sql .= " AND date_valid <= '" . $date_now->format('Y-m-d H:i:s') . "'";
            $sql .= " AND used < 1 ";
        }
        if (!empty($data['filter_active'])) {
            $sql .= " AND date_valid >= '" . $date_now->format('Y-m-d H:i:s') . "'";
            $sql .= " AND used < 1 ";
        }

        $sort_data = array(
            'login_id',
            'telephone',
            'customer',
            'prize',
            'date_added',
            'date_used',
            'login_id'
        );

        if (isset($data['sort']) && in_array($data['sort'], $sort_data)) {
            $sql .= " ORDER BY " . $data['sort'];
        } else {
            $sql .= " ORDER BY login_id";
        }

        if (isset($data['order']) && ($data['order'] == 'DESC')) {
            $sql .= " DESC";
        } else {
            $sql .= " ASC";
        }

        if (isset($data['start']) || isset($data['limit'])) {
            if ($data['start'] < 0) {
                $data['start'] = 0;
            }

            if ($data['limit'] < 1) {
                $data['limit'] = 20;
            }

            $sql .= " LIMIT " . (int)$data['start'] . "," . (int)$data['limit'];
        }

        $query = $this->db->query($sql);

        return $query->rows;
    }


}
