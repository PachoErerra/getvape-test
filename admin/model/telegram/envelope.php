<?php

class ModelTelegramEnvelope extends Model
{
    public function getEnvelopes()
    {
        $query = $this->db->query("SELECT * FROM `telegram_envelope` ORDER BY `envelope_id` DESC");

        return $query->rows;
    }

    public function getEnvelope($envelope_id)
    {
        $query = $this->db->query("SELECT * FROM `telegram_envelope` WHERE `envelope_id` = '" . (int)$envelope_id . "' LIMIT 1");

        return $query->num_rows ? $query->row : [];
    }

    public function addEnvelope($data)
    {
        $code = $this->generateUniqueCode(); 

        $this->db->query("INSERT INTO `telegram_envelope` SET
            `code` = '" . $this->db->escape($code) . "',
            `name` = '" . $this->db->escape($data['name']) . "',
            `gift_name` = '" . $this->db->escape($data['gift_name']) . "',
            `image` = '" . $this->db->escape(isset($data['image']) ? $data['image'] : '') . "',
            `text_before` = '" . $this->db->escape($data['text_before']) . "',
            `text_after` = '" . $this->db->escape($data['text_after']) . "',
            `button_text` = '" . $this->db->escape($data['button_text']) . "',
            `sticker_file_id` = '" . $this->db->escape($data['sticker_file_id']) . "',
            `ttl_minutes` = '" . (int)$data['ttl_minutes'] . "',
            `status` = '" . (int)$data['status'] . "',
            `await_sticker` = 0,
            `date_added` = NOW(),
            `date_modified` = NOW()");

        return (int)$this->db->getLastId();
    }

    public function editEnvelope($envelope_id, $data)
    {
        $this->db->query("UPDATE `telegram_envelope` SET
            `name` = '" . $this->db->escape($data['name']) . "',
            `gift_name` = '" . $this->db->escape($data['gift_name']) . "',
            `image` = '" . $this->db->escape(isset($data['image']) ? $data['image'] : '') . "',
            `text_before` = '" . $this->db->escape($data['text_before']) . "',
            `text_after` = '" . $this->db->escape($data['text_after']) . "',
            `button_text` = '" . $this->db->escape($data['button_text']) . "',
            `sticker_file_id` = '" . $this->db->escape($data['sticker_file_id']) . "',
            `ttl_minutes` = '" . (int)$data['ttl_minutes'] . "',
            `status` = '" . (int)$data['status'] . "',
            `date_modified` = NOW()
            WHERE `envelope_id` = '" . (int)$envelope_id . "'");
    }

    public function hasEvents($envelope_id)
    {
        $query = $this->db->query("SELECT COUNT(*) AS total FROM `telegram_envelope_event`
            WHERE `envelope_id` = '" . (int)$envelope_id . "'");

        return (int)$query->row['total'] > 0;
    }

    public function deleteEnvelope($envelope_id)
    {
        if ($this->hasEvents($envelope_id)) {
            return false;
        }

        $this->db->query("DELETE FROM `telegram_envelope` WHERE `envelope_id` = '" . (int)$envelope_id . "'");
        $this->db->query("DELETE FROM `telegram_envelope_pending` WHERE `envelope_id` = '" . (int)$envelope_id . "'");

        return true;
    }

    public function setAwaitSticker($envelope_id)
    {
        $this->db->query("UPDATE `telegram_envelope` SET `await_sticker` = 0 WHERE `await_sticker` = 1");
        $this->db->query("UPDATE `telegram_envelope` SET `await_sticker` = 1, `date_modified` = NOW() WHERE `envelope_id` = '" . (int)$envelope_id . "'");
    }

    public function clearAwaitSticker($envelope_id = 0)
    {
        if ($envelope_id) {
            $this->db->query("UPDATE `telegram_envelope` SET `await_sticker` = 0, `date_modified` = NOW() WHERE `envelope_id` = '" . (int)$envelope_id . "'");
        } else {
            $this->db->query("UPDATE `telegram_envelope` SET `await_sticker` = 0 WHERE `await_sticker` = 1");
        }
    }

    public function getStatsByEnvelope($envelope_id)
    {
        $envelope_id = (int)$envelope_id;

        $starts = $this->db->query("SELECT COUNT(*) AS total, COUNT(DISTINCT chat_id) AS uniq
            FROM `telegram_envelope_event`
            WHERE `envelope_id` = '" . $envelope_id . "' AND `event_type` = 'start'");

        $auths = $this->db->query("SELECT COUNT(*) AS total, COUNT(DISTINCT chat_id) AS uniq
            FROM `telegram_envelope_event`
            WHERE `envelope_id` = '" . $envelope_id . "' AND `event_type` = 'auth'");

        $opens = $this->db->query("SELECT COUNT(*) AS total, COUNT(DISTINCT chat_id) AS uniq
            FROM `telegram_envelope_event`
            WHERE `envelope_id` = '" . $envelope_id . "' AND `event_type` = 'open'");

        return [
            'starts_total' => (int)$starts->row['total'],
            'starts_uniq' => (int)$starts->row['uniq'],
            'auths_total' => (int)$auths->row['total'],
            'auths_uniq' => (int)$auths->row['uniq'],
            'opens_total' => (int)$opens->row['total'],
            'opens_uniq' => (int)$opens->row['uniq'],
        ];
    }

    public function getEvents($envelope_id, $start = 0, $limit = 50)
    {
        return $this->getEventsFiltered([
            'envelope_id' => (int)$envelope_id,
            'start' => (int)$start,
            'limit' => (int)$limit,
        ]);
    }

    public function getEventsTotal($envelope_id)
    {
        return $this->getEventsFilteredTotal(['envelope_id' => (int)$envelope_id]);
    }

    public function getEventsFiltered($filter = [])
    {
        $sql = "SELECT e.*, en.name AS envelope_name, en.gift_name AS envelope_gift,
                tu.first_name, tu.last_name, tu.username
            FROM `telegram_envelope_event` e
            LEFT JOIN `telegram_envelope` en ON en.envelope_id = e.envelope_id
            LEFT JOIN `telegram_users` tu ON tu.id = e.chat_id
            WHERE 1";

        if (!empty($filter['envelope_id'])) {
            $sql .= " AND e.envelope_id = '" . (int)$filter['envelope_id'] . "'";
        }

        if (!empty($filter['event_type'])) {
            $sql .= " AND e.event_type = '" . $this->db->escape($filter['event_type']) . "'";
        }

        $sql .= " ORDER BY e.event_id DESC";

        $start = isset($filter['start']) ? (int)$filter['start'] : 0;
        $limit = isset($filter['limit']) ? (int)$filter['limit'] : 50;

        if ($start < 0) {
            $start = 0;
        }

        if ($limit < 1) {
            $limit = 50;
        }

        $sql .= " LIMIT " . $start . "," . $limit;

        $query = $this->db->query($sql);

        return $query->rows;
    }

    public function getEventsFilteredTotal($filter = [])
    {
        $sql = "SELECT COUNT(*) AS total FROM `telegram_envelope_event` e WHERE 1";

        if (!empty($filter['envelope_id'])) {
            $sql .= " AND e.envelope_id = '" . (int)$filter['envelope_id'] . "'";
        }

        if (!empty($filter['event_type'])) {
            $sql .= " AND e.event_type = '" . $this->db->escape($filter['event_type']) . "'";
        }

        $query = $this->db->query($sql);

        return (int)$query->row['total'];
    }

    public function getTotalsAll()
    {
        $starts = $this->db->query("SELECT COUNT(*) AS total, COUNT(DISTINCT chat_id) AS uniq
            FROM `telegram_envelope_event` WHERE `event_type` = 'start'");
        $opens = $this->db->query("SELECT COUNT(*) AS total, COUNT(DISTINCT chat_id) AS uniq
            FROM `telegram_envelope_event` WHERE `event_type` = 'open'");

        return [
            'starts_total' => (int)$starts->row['total'],
            'starts_uniq' => (int)$starts->row['uniq'],
            'opens_total' => (int)$opens->row['total'],
            'opens_uniq' => (int)$opens->row['uniq'],
        ];
    }

    private function generateUniqueCode()
    {
        for ($i = 0; $i < 10; $i++) {
            $code = bin2hex(random_bytes(6));
            $query = $this->db->query("SELECT envelope_id FROM `telegram_envelope` WHERE `code` = '" . $this->db->escape($code) . "' LIMIT 1");

            if (!$query->num_rows) {
                return $code;
            }
        }

        return bin2hex(random_bytes(8));
    }
}
