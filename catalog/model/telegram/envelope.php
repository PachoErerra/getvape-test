<?php

class ModelTelegramEnvelope extends Model
{
    public function getEnvelopeById($envelope_id)
    {
        $query = $this->db->query("SELECT * FROM `telegram_envelope` WHERE `envelope_id` = '" . (int)$envelope_id . "' LIMIT 1");

        return $query->num_rows ? $query->row : [];
    }

    public function getEnvelopeByCode($code)
    {
        $code = preg_replace('~[^a-zA-Z0-9]~', '', (string)$code);

        if ($code === '') {
            return [];
        }

        $query = $this->db->query("SELECT * FROM `telegram_envelope` WHERE `code` = '" . $this->db->escape($code) . "' LIMIT 1");

        return $query->num_rows ? $query->row : [];
    }

    public function getActiveEnvelopeByCode($code)
    {
        $envelope = $this->getEnvelopeByCode($code);

        if (!$envelope || !(int)$envelope['status']) {
            return [];
        }

        return $envelope;
    }

    public function addEvent($envelope_id, $chat_id, $event_type, $customer_id = 0, $gift_name = '', $expires_at = null)
    {
        $expiresSql = $expires_at
            ? "'" . $this->db->escape($expires_at) . "'"
            : 'NULL';

        $this->db->query("INSERT INTO `telegram_envelope_event` SET
            `envelope_id` = '" . (int)$envelope_id . "',
            `chat_id` = '" . (int)$chat_id . "',
            `customer_id` = '" . (int)$customer_id . "',
            `event_type` = '" . $this->db->escape($event_type) . "',
            `gift_name` = '" . $this->db->escape($gift_name) . "',
            `expires_at` = " . $expiresSql . ",
            `date_added` = NOW()");

        return (int)$this->db->getLastId();
    }

    public function setPending($chat_id, $envelope_id)
    {
        $this->db->query("REPLACE INTO `telegram_envelope_pending` SET
            `chat_id` = '" . (int)$chat_id . "',
            `envelope_id` = '" . (int)$envelope_id . "',
            `date_added` = NOW()");
    }

    public function getPending($chat_id)
    {
        $query = $this->db->query("SELECT * FROM `telegram_envelope_pending` WHERE `chat_id` = '" . (int)$chat_id . "' LIMIT 1");

        return $query->num_rows ? $query->row : [];
    }

    public function clearPending($chat_id)
    {
        $this->db->query("DELETE FROM `telegram_envelope_pending` WHERE `chat_id` = '" . (int)$chat_id . "'");
    }

    public function getAwaitingStickerEnvelope()
    {
        $query = $this->db->query("SELECT * FROM `telegram_envelope` WHERE `await_sticker` = 1 ORDER BY `date_modified` DESC LIMIT 1");

        return $query->num_rows ? $query->row : [];
    }

    public function setStickerFileId($envelope_id, $file_id)
    {
        $this->db->query("UPDATE `telegram_envelope` SET
            `sticker_file_id` = '" . $this->db->escape($file_id) . "',
            `await_sticker` = 0,
            `date_modified` = NOW()
            WHERE `envelope_id` = '" . (int)$envelope_id . "'");
    }

    public function clearAwaitSticker($envelope_id = 0)
    {
        if ($envelope_id) {
            $this->db->query("UPDATE `telegram_envelope` SET `await_sticker` = 0, `date_modified` = NOW() WHERE `envelope_id` = '" . (int)$envelope_id . "'");
        } else {
            $this->db->query("UPDATE `telegram_envelope` SET `await_sticker` = 0 WHERE `await_sticker` = 1");
        }
    }
}
