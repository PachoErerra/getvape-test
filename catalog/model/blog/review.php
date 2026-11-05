<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ModelBlogReview extends Model {
	public function addReview($article_id, $data) {
		$this->db->query("INSERT INTO " . DB_PREFIX . "review_article SET author = '" . $this->db->escape($data['name']) . "', email = '" . $this->db->escape($data['email']) . "', customer_id = '" . (int)$this->customer->getId() . "', article_id = '" . (int)$article_id . "', text = '" . $this->db->escape($data['text']) . "', rating = '" . (int)$data['rating'] . "', date_added = NOW()");

		$review_id = $this->db->getLastId();

		if ($this->config->get('configblog_review_mail')) {
			$this->load->language('blog/mail/review');
			$this->load->model('blog/article');
			
			$article_info = $this->model_blog_article->getArticle($article_id);

			$subject = sprintf($this->language->get('text_subject'), html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));

			$message  = $this->language->get('text_waiting') . "\n";
			$message .= sprintf($this->language->get('text_article'), html_entity_decode($article_info['name'], ENT_QUOTES, 'UTF-8')) . "\n";
			$message .= sprintf($this->language->get('text_reviewer'), html_entity_decode($data['name'], ENT_QUOTES, 'UTF-8')) . "\n";
//			$message .= sprintf($this->language->get('text_rating'), $data['rating']) . "\n";
			$message .= $this->language->get('text_review') . "\n";
			$message .= html_entity_decode($data['text'], ENT_QUOTES, 'UTF-8') . "\n\n";

			$mail = new Mail();
			$mail->protocol = $this->config->get('config_mail_protocol');
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
			$emails = explode(',', $this->config->get('config_alert_email'));

			foreach ($emails as $email) {
				if ($email && preg_match('/^[^\@]+@.*.[a-z]{2,15}$/i', $email)) {
					$mail->setTo($email);
					$mail->send();
				}
			}
		}

	}

	public function getReviewsByArticleId($article_id, $start = 0, $limit = 20) {
		if ($start < 0) {
			$start = 0;
		}

		if ($limit < 1) {
			$limit = 20;
		}

		$query = $this->db->query("SELECT r.review_article_id, r.author, r.answer, r.rating, r.text, p.article_id, pd.name, p.image, r.date_added FROM " . DB_PREFIX . "review_article r LEFT JOIN " . DB_PREFIX . "article p ON (r.article_id = p.article_id) LEFT JOIN " . DB_PREFIX . "article_description pd ON (p.article_id = pd.article_id) WHERE p.article_id = '" . (int)$article_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "' ORDER BY r.date_added DESC LIMIT " . (int)$start . "," . (int)$limit);

		return $query->rows;
	}

	public function getTotalReviewsByArticleId($article_id) {
		$query = $this->db->query("SELECT COUNT(*) AS total FROM " . DB_PREFIX . "review_article r LEFT JOIN " . DB_PREFIX . "article p ON (r.article_id = p.article_id) LEFT JOIN " . DB_PREFIX . "article_description pd ON (p.article_id = pd.article_id) WHERE p.article_id = '" . (int)$article_id . "' AND p.date_available <= NOW() AND p.status = '1' AND r.status = '1' AND pd.language_id = '" . (int)$this->config->get('config_language_id') . "'");

		return $query->row['total'];
	}
	
    public function checkUserIp($ip, $article_id) {
        $query = $this->db->query("SELECT * FROM " . DB_PREFIX . "article_reputation WHERE ip = '" . $this->db->escape($ip) . "' AND article_id = '" . (int)$article_id . "'");

        return $query->rows;
    }

    public function hasUserVoted($ip, $article_id) {
        $query = $this->db->query("SELECT reputation_id FROM " . DB_PREFIX . "article_reputation WHERE ip = '" . $this->db->escape($ip) . "' AND article_id = '" . (int)$article_id . "' LIMIT 1");

        return (bool)$query->num_rows;
    }
	
    public function addReputation($data = array()) {
        $sql = "INSERT INTO " . DB_PREFIX . "article_reputation SET article_id = '" . (int)$data['article_id'] . "', ip = '" . $this->db->escape($data['ip']) . "', reputation = '" . (int)$data['reputation'] . "'";

        $this->db->query($sql);
    }
	
	public function getRating($article_id) {
		// База: 5 голосов по 5 звезд. Реальные голоса добавляются сверху (первый = 6-й).
		$base_votes = 5;
		$base_sum = 25; // 5 * 5

		$query = $this->db->query("SELECT COUNT(*) AS total, COALESCE(SUM(reputation), 0) AS sum_reputation FROM " . DB_PREFIX . "article_reputation WHERE article_id = '" . (int)$article_id . "'");

		$total = (int)$query->row['total'];
		$sum = (float)$query->row['sum_reputation'];

		return ($base_sum + $sum) / ($base_votes + $total);
	}
}