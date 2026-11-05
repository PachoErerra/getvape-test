<?php
class ModelCatalogReviewshop extends Model {
	public function addReview($data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "review_shop SET author = '" . $this->db->escape($data['name']) . "', customer_id = '" . (int)$this->customer->getId() . "', text = '" . $this->db->escape($data['text']) . "', quality = '" . (int)$data['quality'] . "', delivery = '" . (int)$data['delivery'] . "', price = '" . (int)$data['price'] . "', support = '" . (int)$data['support'] . "', date_added = NOW()");

		$review_id = $this->db->getLastId();

		if (in_array('review', (array)$this->config->get('config_mail_alert'))) {
			$this->load->language('information/reviews');

			$subject = sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

			$message  = $this->language->get('text_waiting') . "\n";
			$message .= sprintf($this->language->get('text_reviewer'), html_entity_decode($data['name'], ENT_QUOTES, 'UTF-8')) . "\n";
			$message .= sprintf($this->language->get('text_quality'), $data['quality']) . "\n";
			$message .= sprintf($this->language->get('text_delivery'), $data['delivery']) . "\n";
			$message .= sprintf($this->language->get('text_price'), $data['price']) . "\n";
			$message .= sprintf($this->language->get('text_support'), $data['support']) . "\n";
			$message .= $this->language->get('text_review') . "\n";
			$message .= html_entity_decode($data['text'], ENT_QUOTES, 'UTF-8') . "\n\n";

			$mail = new Mail($this->config->get('config_mail_engine'));
			$mail->parameter = $this->config->get('config_mail_parameter');
			$mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
			$mail->smtp_username = $this->config->get('config_mail_smtp_username');
			$mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
			$mail->smtp_port = $this->config->get('config_mail_smtp_port');
			$mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

			$mail->setTo($this->config->get('config_email'));
			$mail->setFrom($this->config->get('config_email'));
			$mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
			$mail->setSubject($subject);
			$mail->setText($message);
			$mail->send();

			// Send to additional alert emails
			$emails = explode(',', $this->config->get('config_mail_alert_email'));

			foreach ($emails as $email) {
				if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
					$mail->setTo($email);
					$mail->send();
				}
			}
		}
	}

	public function getReviews($start = 0, $limit = 12) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 12;
		}

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "review_shop r WHERE r.status = '1' ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalReviews() {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review_shop r WHERE r.status = '1'");

		return $query->row['total'];
	}
	
	public function getTotalReviewsCount() {
		$review_data = array();
		$query = $this->db->query("SELECT quality, delivery, price, support FROM " . DB_PREFIX . "review_shop r WHERE r.status = '1'");
		$m5 = 0;
		$m4 = 0;
		$m3 = 0;
		$m2 = 0;
		$m1 = 0;
		$mt = 0;
		$review_data['r']['delivery'] = 0;
		$review_data['r']['price'] = 0;
		$review_data['r']['support'] = 0;
		$review_data['r']['quality'] = 0;
		foreach ($query->rows as $row) {
			
			foreach ($row as $key=>$result) {
				
				$total = $review_data['r'][$key] + $result;
				$review_data['r'][$key] = $total;

				if ($result == 5) {
					$m5++;
				}
				if ($result == 4) {
					$m4++;
				}
				if ($result == 3) {
					$m3++;
				}
				if ($result == 2) {
					$m2++;
				}
				if ($result == 1) {
					$m1++;
				}
				$mt++;
				
				$mark_data['m'][5] = $m5;
				$mark_data['m'][4] = $m4;
				$mark_data['m'][3] = $m3;
				$mark_data['m'][2] = $m2;
				$mark_data['m'][1] = $m1;
				$mark_data['m']['t'] = $mt;
			}
		}

		$result = array_merge($mark_data, $review_data);

		return $result;
	}
}