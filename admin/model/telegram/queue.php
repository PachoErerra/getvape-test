<?php

class ModelTelegramQueue extends Model
{
    public function addQueue($hasImage, $data, $type = 'default')
    {

        $jsonData = json_encode($data);

        $this->db->query("INSERT INTO telegram_queue SET has_image = '" . (int)$hasImage . "', data= '" . $this->db->escape($jsonData) . "', `type`='" . $this->db->escape($type) . "', created_at = NOW()");

        $queue_id = $this->db->getLastId();

    }
}