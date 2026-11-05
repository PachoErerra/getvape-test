<?php

class ModelTelegramQueue extends Model
{
    public function getNewQueue($limit = 80, $type = 'default')
    {
        $query = $this->db->query("
            SELECT *
            FROM `telegram_queue`
            WHERE `status` = 0
              AND `type` = '" . $this->db->escape($type) . "'
            ORDER BY `id` ASC
            LIMIT " . (int)$limit . "
        ");

        return $query->rows;
    }

    public function updateQueue($queueID, $status)
    {
        $this->db->query("UPDATE telegram_queue SET status = '" . (int)$status . "', sent_at = NOW() WHERE id = '" . (int)$queueID . "'");
    }

    public function markQueueAttemptFailed($queueID, $maxAttempts = 3)
    {
        $this->db->query("
            UPDATE telegram_queue
            SET attempts = attempts + 1,
                sent_at = NOW(),
                status = IF(attempts + 1 >= '" . (int)$maxAttempts . "', 2, 0)
            WHERE id = '" . (int)$queueID . "'
        ");
    }

    public function getNewBlackFridayQueue($limit = 80)
    {
        $query = $this->db->query("
            SELECT *
            FROM `telegram_queue`
            WHERE `status` = 0
              AND `type` = 'black_friday'
            ORDER BY `id` ASC
            LIMIT " . (int)$limit . "
        ");

        return $query->rows;
    }

    public function addQueue($hasImage, $data, $type = 'default')
    {
        $jsonData = json_encode($data);

        $this->db->query("INSERT INTO telegram_queue SET has_image = '" . (int)$hasImage . "', data= '" . $this->db->escape($jsonData) . "', `type`='" . $this->db->escape($type) . "', created_at = NOW()");

        $this->db->getLastId();
    }

    public function deleteProcessedOlderThan($days = 14, $limit = 5000)
    {
        $this->db->query("
            DELETE FROM `telegram_queue`
            WHERE `status` IN (1, 2)
              AND `created_at` < NOW() - INTERVAL " . (int)$days . " DAY
            LIMIT " . (int)$limit . "
        ");

        return $this->db->countAffected();
    }
}
