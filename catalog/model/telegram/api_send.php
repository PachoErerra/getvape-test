<?php

class ModelTelegramApiSend extends Model
{
    public function add($data)
    {
        $this->db->query("
            INSERT INTO `telegram_api_send`
            SET phone = '" . $this->db->escape($data['phone'] ?? '') . "',
                customer_id = '" . (int)($data['customer_id'] ?? 0) . "',
                chat_id = '" . (int)($data['chat_id'] ?? 0) . "',
                message = '" . $this->db->escape($data['message'] ?? '') . "',
                status = '" . $this->db->escape($data['status'] ?? '') . "',
                error = '" . $this->db->escape($data['error'] ?? '') . "',
                ip = '" . $this->db->escape($data['ip'] ?? '') . "',
                date_added = NOW()
        ");

        return $this->db->getLastId();
    }

    public function getApiSends($data = [])
    {
        $sql = "SELECT * FROM `telegram_api_send` WHERE 1";

        $sql .= $this->buildFilters($data);

        $sort = 'date_added';
        $order = 'DESC';

        if (!empty($data['sort']) && in_array($data['sort'], ['date_added', 'phone', 'status'], true)) {
            $sort = $data['sort'];
        }

        if (!empty($data['order']) && strtoupper($data['order']) === 'ASC') {
            $order = 'ASC';
        }

        $sql .= " ORDER BY `" . $sort . "` " . $order;

        $start = isset($data['start']) ? max(0, (int)$data['start']) : 0;
        $limit = isset($data['limit']) ? max(1, (int)$data['limit']) : 20;

        $sql .= " LIMIT " . $start . "," . $limit;

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getTotalApiSends($data = [])
    {
        $sql = "SELECT COUNT(*) AS total FROM `telegram_api_send` WHERE 1" . $this->buildFilters($data);

        $query = $this->db->query($sql);

        return (int)$query->row['total'];
    }

    public function getUniquePhonesCount($data = [])
    {
        $sql = "SELECT COUNT(DISTINCT phone) AS total FROM `telegram_api_send` WHERE 1" . $this->buildFilters($data);

        $query = $this->db->query($sql);

        return (int)$query->row['total'];
    }

    public function getSentCount($data = [])
    {
        $filter = $data;
        $filter['filter_status'] = 'sent';

        return $this->getTotalApiSends($filter);
    }

    private function buildFilters($data)
    {
        $sql = '';

        if (!empty($data['filter_phone'])) {
            $sql .= " AND phone LIKE '%" . $this->db->escape($data['filter_phone']) . "%'";
        }

        if (!empty($data['filter_status'])) {
            $sql .= " AND status = '" . $this->db->escape($data['filter_status']) . "'";
        }

        if (!empty($data['filter_date_added_start']) && !empty($data['filter_date_added_end'])) {
            $sql .= " AND date_added >= '" . $this->db->escape($data['filter_date_added_start']) . "' AND date_added <= '" . $this->db->escape($data['filter_date_added_end']) . "'";
        } elseif (!empty($data['filter_date_added_start'])) {
            $sql .= " AND date_added >= '" . $this->db->escape($data['filter_date_added_start']) . "'";
        } elseif (!empty($data['filter_date_added_end'])) {
            $sql .= " AND date_added <= '" . $this->db->escape($data['filter_date_added_end']) . "'";
        }

        return $sql;
    }
}
