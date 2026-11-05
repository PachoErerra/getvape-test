<?php
class ControllerExtensionFeedBonuses extends Controller {
	
	


	public function index() {

ini_set('error_reporting', E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$data = [
    "telephone" => "+380500445554",
    "addbonus" => 99,
	"description" => ''
];

$data_string = json_encode($data);
echo $data_string;
$result = file_get_contents('https://getvape.com.ua/index.php?route=extension/feed/bonuses/addbonus', null, stream_context_create(array(
            'http' => array(
                'method' => 'POST',
                    'header' => array(
                        "Content-Type: application/json; charset=utf-8\r\n" .
                        "Content-Length: " . strlen($data_string) . "\r\n" .
                        "Host: getvape.com.ua"
                    ),
                'content' => $data_string
            )
        )
    )
);

header('Content-Type: application/json; charset=UTF-8');


echo $result;

	}
	
	public function getbonuses() {
		$json = array();
		$postData = file_get_contents('php://input');
		$data = json_decode($postData, true);
		
		if (!$data) {
			$json['error'] = 'Данные не полученны';
		} else {
			if (isset($data['telephone']) && $data['telephone']) {
		
				$query = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer WHERE telephone = '" . $this->db->escape($data['telephone']) . "'");

				if ($query->num_rows) {
					
					$query2 = $this->db->query("SELECT SUM(points) AS total FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$query->row['customer_id'] . "'");
					
					if ($query2->num_rows) {
						$json['success'] = true;
						$json['bonuses'] = $query2->row['total'];
					} else {
						$json['error'] = 'У данного покупателя нет бонусов';
						$json['bonuses'] = 0;
					}
					
				} else {
					$json['error'] = 'Покупателя с таким номером не существует';
				}
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function addbonus() {
		$json = array();
		$postData = file_get_contents('php://input');
		$data = json_decode($postData, true);

		if (!$data) {
			$json['error'] = 'Данные не полученны';
		} else {
			if (isset($data['telephone']) && $data['telephone'] && isset($data['addbonus']) && $data['addbonus']) {
		
				$query = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customer WHERE telephone = '" . $this->db->escape($data['telephone']) . "'");

				if ($query->num_rows) {
					
					$this->addReward($query->row['customer_id'],$data['addbonus'],$data['description']);
					
					$query2 = $this->db->query("SELECT SUM(points) AS total FROM " . DB_PREFIX . "customer_reward WHERE customer_id = '" . (int)$query->row['customer_id'] . "'");
					
					if ($query2->num_rows) {
						$json['success'] = true;
						$json['totalbonuses'] = $query2->row['total'];
					} else {
						$json['error'] = 'У данного покупателя нет бонусов';
						$json['totalbonuses'] = 0;
					}
					
				} else {
					$json['error'] = 'Покупателя с таким номером не существует';
				}
			}
		}
		
		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
	
	public function addReward($customer_id, $points = '', $description = '', $order_id = 0) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "customer_reward SET customer_id = '" . (int)$customer_id . "', order_id = '" . (int)$order_id . "', points = '" . (int)$points . "', description = '" . $this->db->escape($description) . "', date_added = NOW()");
	}	
}
