<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerBlogArticle extends Controller
{
    private $error = array();

    public function index()
    {
        $this->load->language('blog/article');
		$this->load->language('blog/articlev');
		$this->load->model('tool/image');
        $data['breadcrumbs'] = array();

        $data['blog'] = $this->url->link('blog/latest');
	
        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home'),
            'separator' => false
        );

        $configblog_name = $this->config->get('configblog_name');

        if (!empty($configblog_name)) {
            $name = $this->config->get('configblog_name');
        } else {
            $name = $this->language->get('text_blog');
        }
/*
        $data['breadcrumbs'][] = array(
            'text' => $name,
            'href' => $this->url->link('blog/latest')
        );
*/
        $this->load->model('blog/category');
		$this->load->model('blog/review');

        $data['sprite_version'] = $this->registry->get('sprite_version');

        if (isset($this->request->get['blog_category_id'])) {
            $blog_category_id = '';

            foreach (explode('_', $this->request->get['blog_category_id']) as $path_id) {
                if (!$blog_category_id) {
                    $blog_category_id = $path_id;
                } else {
                    $blog_category_id .= '_' . $path_id;
                }

                $category_info = $this->model_blog_category->getCategory($path_id);

                if ($category_info) {
                    $data['breadcrumbs'][] = array(
                        'text' => $category_info['name'],
                        'href' => $this->url->link('blog/category', 'blog_category_id=' . $blog_category_id)
                    );
                }

                $data['back'] = $this->url->link('blog/category', 'blog_category_id=' . $blog_category_id);
            }
        }


        if (isset($this->request->get['filter_name']) || isset($this->request->get['filter_tag'])) {
            $url = '';

            if (isset($this->request->get['filter_name'])) {
                $url .= '&filter_name=' . $this->request->get['filter_name'];
            }

            if (isset($this->request->get['filter_tag'])) {
                $url .= '&filter_tag=' . $this->request->get['filter_tag'];
            }

            if (isset($this->request->get['filter_description'])) {
                $url .= '&filter_description=' . $this->request->get['filter_description'];
            }

            if (isset($this->request->get['filter_news_id'])) {
                $url .= '&filter_news_id=' . $this->request->get['filter_news_id'];
            }

        }

        if (isset($this->request->get['article_id'])) {
            $article_id = (int)$this->request->get['article_id'];
        } else {
            $article_id = 0;
        }

        $this->load->model('blog/article');

        $article_info = $this->model_blog_article->getArticle($article_id);

        if ($article_info) {
            $url = '';
			
							$reputation_value = round((float)$this->model_blog_review->getRating($article_id) * 2) / 2;

			$data['reputation'] = $this->formatArticleReputation($reputation_value);
			$data['reputation_full'] = (int)floor($reputation_value);
			$data['reputation_half'] = (fmod($reputation_value, 1.0) >= 0.5) ? ($data['reputation_full'] + 1) : 0;
			$data['already_voted'] = $this->model_blog_review->hasUserVoted($this->request->server['REMOTE_ADDR'], $article_id);
		
            if (isset($this->request->get['blog_category_id'])) {
                $url .= '&blog_category_id=' . $this->request->get['blog_category_id'];
            }

            if (isset($this->request->get['filter_name'])) {
                $url .= '&filter_name=' . $this->request->get['filter_name'];
            }

            if (isset($this->request->get['filter_tag'])) {
                $url .= '&filter_tag=' . $this->request->get['filter_tag'];
            }

            if (isset($this->request->get['filter_description'])) {
                $url .= '&filter_description=' . $this->request->get['filter_description'];
            }

            if (isset($this->request->get['filter_news_id'])) {
                $url .= '&filter_news_id=' . $this->request->get['filter_news_id'];
            }

            $data['breadcrumbs'][] = array(
                'text' => $article_info['name'],
                'href' => $this->url->link('blog/article', 'article_id=' . $this->request->get['article_id'])
            );

            $data['thislink'] = $this->url->link('blog/article', 'article_id=' . $this->request->get['article_id']);

            if ($article_info['meta_title']) {
                $this->document->setTitle($article_info['meta_title']);
            } else {
                $this->document->setTitle($article_info['name']);
            }

            if ($article_info['noindex'] <= 0 && $this->config->get('config_noindex_status')) {
                $this->document->setRobots('noindex,follow');
            }
            if ($article_info['meta_description']) {
                $this->document->setDescription($article_info['meta_description']);
            } else {
                $this->document->setDescription($article_info['name'] . ' 🚀 Читайте відповіді на ці та інші питання у статті від наших експертів на сайті Vapors');
            }
            $this->document->setKeywords($article_info['meta_keyword']);
            $this->document->addLink($this->url->link('blog/article', 'article_id=' . $this->request->get['article_id']), 'canonical');

            if (!empty($article_info['image'])) {
                $image = HTTP_SERVER . 'image/' . $article_info['image'];
				$data['thumb'] = $this->model_tool_image->resize($article_info['image'], $this->config->get('configblog_image_related_width'), $this->config->get('configblog_image_related_height'));
                $this->document->setOgImage($image);
            }

			$data['date_added'] = date($this->language->get('date_format_short'), strtotime($article_info['date_added']));
			$data['date_modified'] = mb_substr($article_info['date_modified'],0,10);
			$data['href'] = $this->url->link('blog/article', 'article_id=' . $this->request->get['article_id']);
			
            /*
            $this->document->addScript('catalog/view/javascript/jquery/magnific/jquery.magnific-popup.min.js');
            $this->document->addStyle('catalog/view/javascript/jquery/magnific/magnific-popup.css');
            $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment.js');
            $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
            $this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');
*/
            if ($article_info['meta_h1']) {
                $data['heading_title'] = $article_info['meta_h1'];
            } else {
                $data['heading_title'] = $article_info['name'];
            }
			
			$data['author'] = $article_info['author'];

            $data['text_select'] = $this->language->get('text_select');
            $data['text_write'] = $this->language->get('text_write');
            $data['text_login'] = sprintf($this->language->get('text_login'), $this->url->link('account/login', '', true), $this->url->link('account/register', '', true));
            $data['text_loading'] = $this->language->get('text_loading');
            $data['text_note'] = $this->language->get('text_note');
            $data['text_share'] = $this->language->get('text_share');
            $data['text_wait'] = $this->language->get('text_wait');
            $data['button_cart'] = $this->language->get('button_cart');
            $data['button_wishlist'] = $this->language->get('button_wishlist');
            $data['button_compare'] = $this->language->get('button_compare');
            $data['entry_name'] = $this->language->get('entry_name');
            $data['entry_review'] = $this->language->get('entry_review');
            $data['entry_rating'] = $this->language->get('entry_rating');
            $data['entry_good'] = $this->language->get('entry_good');
            $data['entry_bad'] = $this->language->get('entry_bad');
            $data['entry_captcha'] = $this->language->get('entry_captcha');


            $data['button_continue'] = $this->language->get('button_continue');

            $this->load->model('blog/review');

            $data['text_related'] = $this->language->get('text_related');
            $data['text_related_product'] = $this->language->get('text_related_product');

            $data['article_id'] = $this->request->get['article_id'];

            $data['review_status'] = $this->config->get('configblog_review_status');

            if ($this->config->get('configblog_review_guest') || $this->customer->isLogged()) {
                $data['review_guest'] = true;
            } else {
                $data['review_guest'] = false;
            }

            if ($this->customer->isLogged()) {
                $data['customer_name'] = $this->customer->getFirstName() . '&nbsp;' . $this->customer->getLastName();
            } else {
                $data['customer_name'] = '';
            }

            // Captcha
            if ($this->config->get($this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
                $data['captcha'] = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha'));
            } else {
                $data['captcha'] = '';
            }

            $data['article_review'] = (int)$article_info['article_review'];
            $data['reviews'] = sprintf($this->language->get('text_reviews'), (int)$article_info['reviews']);
            $data['rating'] = (int)$article_info['rating'];
            $data['gstatus'] = (int)$article_info['gstatus'];
            if (isset($this->request->get['blog_category_id']) && $this->request->get['blog_category_id'] == 72) {
            $data['description'] = html_entity_decode($article_info['description'], ENT_QUOTES, 'UTF-8');
            } else {
            $data['description'] = $this->model_blog_article->parseArticleDescription($article_info['description']);
            }


            $data['shortdescription'] = utf8_substr(trim(strip_tags(html_entity_decode($article_info['description'], ENT_QUOTES, 'UTF-8'))), 0, $this->config->get('theme_' . $this->config->get('config_theme') . '_product_description_length')) . '..';


            $data['salary'] = $article_info['salary'];
            $data['location'] = $article_info['location'];
            $data['employment'] = $article_info['employment'];
            $data['place'] = $article_info['place'];
            $data['experience'] = $article_info['experience'];

            $data['questions'] = explode(PHP_EOL, $article_info['questions']);

            $data['articles'] = array();

            $data['button_more'] = $this->language->get('button_more');
            $data['text_views'] = $this->language->get('text_views');


            $description_text = strip_tags(html_entity_decode($article_info['description'], ENT_QUOTES, 'UTF-8'));

            $word_count = mb_strlen($description_text) / 7; // Среднее слово = 7 символов
            $data['read_time'] = ceil($word_count / 120);
			
			$data['viewed'] = $article_info['viewed'];

            $this->load->model('tool/image');

            $results = $this->model_blog_article->getArticleRelated($this->request->get['article_id']);

            foreach ($results as $result) {
                if ($result['image']) {
                    $image = $this->model_tool_image->resize($result['image'], $this->config->get('configblog_image_related_width'), $this->config->get('configblog_image_related_height'));
                } else {
                    $image = false;
                }

                if ($this->config->get('configblog_review_status')) {
                    $rating = (int)$result['rating'];
                } else {
                    $rating = false;
                }

                $data['articles'][] = array(
                    'article_id' => $result['article_id'],
                    'thumb' => $image,
                    'name' => $result['name'],
                    'salary' => $result['salary'],
                    'location' => $result['location'],
                    'employment' => $result['employment'],
                    'place' => $result['place'],
                    'experience' => $result['experience'],
                    'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('configblog_article_description_length')) . '..',
                    'rating' => $rating,
                    'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                    'viewed' => $result['viewed'],
                    'reviews' => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
                    'href' => $this->url->link('blog/article', 'blog_category_id=' . $blog_category_id .'&article_id=' . $result['article_id']),
                );
            }

			$data['reviews'] = $this->load->controller('blog/article/review');

            $this->load->model('tool/image');
            $data['products'] = array();


            $results = $this->model_blog_article->getArticleRelatedProduct($this->request->get['article_id']);

            foreach ($results as $result) {
                if ($result['articles']) {
                    $image = $this->model_tool_image->resize($result['image'], $this->config->get('configblog_image_related_width'), $this->config->get('configblog_image_related_height'));
                } else {
                    $image = false;
                }
                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format($this->tax->calculate($result['price'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $price = false;
                }

                if ((float)$result['special']) {
                    $special = $this->currency->format($this->tax->calculate($result['special'], $result['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $special = false;
                }

                if ($this->config->get('config_tax')) {
                    $tax = $this->currency->format((float)$result['special'] ? $result['special'] : $result['price'], $this->session->data['currency']);
                } else {
                    $tax = false;
                }

                if ($this->config->get('configblog_review_status')) {
                    $rating = (int)$result['rating'];
                } else {
                    $rating = false;
                }

                $data['text_tax'] = $this->language->get('text_tax');

                $data['products'][] = array(
                    'product_id' => $result['product_id'],
                    'thumb' => $image,
                    'name' => $result['name'],
                    'description' => utf8_substr(strip_tags(html_entity_decode($result['description'], ENT_QUOTES, 'UTF-8')), 0, $this->config->get('configblog_article_description_length')) . '..',
                    'price' => $price,
                    'special' => $special,
                    'rating' => $rating,
                    'tax' => $tax,
                    'minimum' => $result['minimum'] > 0 ? $result['minimum'] : 1,
					'rating'      => $rating,
                    'reviews' => sprintf($this->language->get('text_reviews'), (int)$result['reviews']),
                    'href' => $this->url->link('product/product', 'product_id=' . $result['product_id']),
                );
            }

            $data['download_status'] = $this->config->get('configblog_article_download');

            $data['downloads'] = array();

            $results = $this->model_blog_article->getDownloads($this->request->get['article_id']);

            foreach ($results as $result) {
                if (file_exists(DIR_DOWNLOAD . $result['filename'])) {
                    $size = filesize(DIR_DOWNLOAD . $result['filename']);

                    $i = 0;

                    $suffix = array(
                        'B',
                        'KB',
                        'MB',
                        'GB',
                        'TB',
                        'PB',
                        'EB',
                        'ZB',
                        'YB'
                    );

                    while (($size / 10024) > 1) {
                        $size = $size / 10024;
                        $i++;
                    }

                    $data['downloads'][] = array(
                        'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
                        'name' => $result['name'],
                        'size' => round(substr($size, 0, strpos($size, '.') + 4), 2) . $suffix[$i],
                        'href' => $this->url->link('blog/article/download', '&article_id=' . $this->request->get['article_id'] . '&download_id=' . $result['download_id'])
                    );
                }
            }

            $this->model_blog_article->updateViewed($this->request->get['article_id']);

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            if (isset($this->request->get['blog_category_id']) && $this->request->get['blog_category_id'] == 72) {
                $this->response->setOutput($this->load->view('blog/articlev', $data));
            } else {
                $this->response->setOutput($this->load->view('blog/article', $data));
            }
        } else {
            $url = '';

            if (isset($this->request->get['blog_category_id'])) {
                $url .= '&blog_category_id=' . $this->request->get['blog_category_id'];
            }

            if (isset($this->request->get['filter_name'])) {
                $url .= '&filter_name=' . $this->request->get['filter_name'];
            }

            if (isset($this->request->get['filter_tag'])) {
                $url .= '&filter_tag=' . $this->request->get['filter_tag'];
            }

            if (isset($this->request->get['filter_description'])) {
                $url .= '&filter_description=' . $this->request->get['filter_description'];
            }

            if (isset($this->request->get['filter_news_id'])) {
                $url .= '&filter_news_id=' . $this->request->get['filter_news_id'];
            }

            $data['breadcrumbs'][] = array(
                'text' => $this->language->get('text_error'),
                'href' => $this->url->link('product/product', $url . '&product_id=' . $article_id)
            );

            $this->document->setTitle($this->language->get('text_error'));

            $data['heading_title'] = $this->language->get('text_error');

            $data['text_error'] = $this->language->get('text_error');

            $data['button_continue'] = $this->language->get('button_continue');

            $data['continue'] = $this->url->link('common/home');

            $this->response->addHeader($this->request->server['SERVER_PROTOCOL'] . ' 404 Not Found');

            $data['column_left'] = $this->load->controller('common/column_left');
            $data['column_right'] = $this->load->controller('common/column_right');
            $data['content_top'] = $this->load->controller('common/content_top');
            $data['content_bottom'] = $this->load->controller('common/content_bottom');
            $data['footer'] = $this->load->controller('common/footer');
            $data['header'] = $this->load->controller('common/header');

            $this->response->setOutput($this->load->view('error/not_found', $data));
        }
    }

    public function download()
    {

        $this->load->model('blog/article');

        if (isset($this->request->get['download_id'])) {
            $download_id = $this->request->get['download_id'];
        } else {
            $download_id = 0;
        }

        if (isset($this->request->get['article_id'])) {
            $article_id = $this->request->get['article_id'];
        } else {
            $article_id = 0;
        }

        $download_info = $this->model_blog_article->getDownload($article_id, $download_id);


        if ($download_info) {
            $file = DIR_DOWNLOAD . $download_info['filename'];
            $mask = basename($download_info['mask']);

            if (!headers_sent()) {
                if (file_exists($file)) {
                    header('Content-Description: File Transfer');
                    header('Content-Type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="' . ($mask ? $mask : basename($file)) . '"');
                    header('Content-Transfer-Encoding: binary');
                    header('Expires: 0');
                    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
                    header('Pragma: public');
                    header('Content-Length: ' . filesize($file));

                    readfile($file, 'rb');


                    exit;
                } else {
                    exit('Error: Could not find file ' . $file . '!');
                }
            } else {
                exit('Error: Headers already sent out!');
            }
        } else {
            $this->redirect(HTTP_SERVER . 'index.php?route=account/download');
        }
    }

    public function review()
    {

        $this->language->load('blog/article');
        $this->language->load('blog/review');

        $this->load->model('blog/review');

        $data['text_on'] = $this->language->get('text_on');
        $data['text_no_reviews'] = $this->language->get('text_no_reviews');
        $data['text_pokazaty_bilshe_komentariv'] = $this->language->get('text_pokazaty_bilshe_komentariv');
        $data['ajax'] = isset($this->request->get['ajax']);

        if (isset($this->request->get['page'])) {
            $page = $this->request->get['page'];
        } else {
            $page = 1;
        }

        $data['reviews'] = array();

        $review_total = $this->model_blog_review->getTotalReviewsByArticleId($this->request->get['article_id']);

        $results = $this->model_blog_review->getReviewsByArticleId($this->request->get['article_id'], ($page - 1) * 5, 5);

        foreach ($results as $result) {
            $date_added = strtotime($result['date_added']);
            $today = strtotime('today'); // сегодняшняя дата

// Если дата добавления не сегодняшняя, добавляем 1 день
            if ($date_added != $today) {
                $date_answered = strtotime('+1 day', $date_added);
            } else {
                $date_answered = $date_added; // оставляем сегодняшнюю дату
            }
            $data['reviews'][] = array(
                'author' => $result['author'],
                'text' => $result['text'],
				'answer' => $result['answer'],
                'rating' => (int)$result['rating'],
                'reviews' => sprintf($this->language->get('text_reviews'), (int)$review_total),
				'date_added' => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'date_answered' => date('d.m.Y', $date_answered)
            );
        }

        $pagination = new Pagination();
        $pagination->total = $review_total;
        $pagination->page = $page;
        $pagination->limit = 5;
        $pagination->url = $this->url->link('blog/article/review', 'article_id=' . $this->request->get['article_id'] . '&page={page}&ajax=1');

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($review_total) ? (($page - 1) * 5) + 1 : 0, ((($page - 1) * 5) > ($review_total - 5)) ? $review_total : ((($page - 1) * 5) + 5), $review_total, ceil($review_total / 5));
if (isset($this->request->get['ajax'])) {
        $this->response->setOutput($this->load->view('blog/review', $data));
} else {		
		return $this->load->view('blog/review', $data);
}
    }

    public function write()
    {
        $this->load->language('blog/article');

        $json = array();

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
			
            if (empty($this->request->post['email']) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
 //               $json['error'] = $this->language->get('error_email');
//				$json['email'] = 1;
            }			
			
            if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
                $json['error'] = $this->language->get('error_name');
				$json['name'] = 1;
            }

            if ((utf8_strlen($this->request->post['text']) < 10) || (utf8_strlen($this->request->post['text']) > 1000)) {
                $json['error'] = $this->language->get('error_text');
				$json['text'] = 1;
            }


			


            if ((utf8_strlen($this->request->post['text']) < 10) || (utf8_strlen($this->request->post['text']) > 1000)) {
                $json['error'] = $this->language->get('error_text');
				$json['text'] = 1;
            }
            if (!empty($this->request->post['human'])) {
                $json['error'] = 'Ви бот!';
            }			
            // Captcha
            if ($this->config->get($this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
                $captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

                if ($captcha) {
                    $json['error'] = $captcha;
                }
            }

            if (!isset($json['error'])) {
                $this->load->model('blog/review');

                $this->model_blog_review->addReview($this->request->get['article_id'], $this->request->post);

                $json['success'] = $this->language->get('text_success');
            }
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function ReviewReputation() {
        $json = array();
		
		$this->load->language('blog/article');
        $this->load->model('blog/review');

        $article_id = isset($this->request->get['article_id']) ? (int)$this->request->get['article_id'] : 0;
        $reputation = isset($this->request->get['reputation']) ? (int)$this->request->get['reputation'] : 0;

        if ($article_id < 1 || $reputation < 1 || $reputation > 5) {
            $json['error'] = $this->language->get('error_rating');
        } elseif ($this->model_blog_review->hasUserVoted($this->request->server['REMOTE_ADDR'], $article_id)) {
            $json['error'] = $this->language->get('error_ip_exist');
            $reputation_value = round((float)$this->model_blog_review->getRating($article_id) * 2) / 2;
            $json['reputation'] = $this->formatArticleReputation($reputation_value);
            $json['reputation_full'] = (int)floor($reputation_value);
            $json['reputation_half'] = (fmod($reputation_value, 1.0) >= 0.5) ? ($json['reputation_full'] + 1) : 0;
        } else {
            $this->model_blog_review->addReputation(array(
                'article_id' => $article_id,
                'ip' => $this->request->server['REMOTE_ADDR'],
                'reputation' => $reputation,
            ));

            $reputation_value = round((float)$this->model_blog_review->getRating($article_id) * 2) / 2;
            $json['reputation'] = $this->formatArticleReputation($reputation_value);
            $json['reputation_full'] = (int)floor($reputation_value);
            $json['reputation_half'] = (fmod($reputation_value, 1.0) >= 0.5) ? ($json['reputation_full'] + 1) : 0;
            $json['success'] = $this->language->get('text_ip_vote_success');
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function formatArticleReputation($value) {
        $value = round((float)$value * 2) / 2;

        if (floor($value) == $value) {
            return (string)(int)$value;
        }

        return number_format($value, 1, '.', '');
    }

    public function sendmail()
    {
        $this->load->language('mail/resume');

        $json = array();

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            if ($this->request->post['question']) {
                $json['error_question'] = array();
                foreach ($this->request->post['question'] as $key => $question) {
                    if (utf8_strlen($question['answer']) < 2) {
                        $json['error'] = $this->language->get('error_question');
                        $json['error_question'][$key] = $key;
                    }

                }
            }

            // Captcha
            if ($this->config->get($this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
                $captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

                if ($captcha) {
                    $json['error'] = $captcha;
                }
            }

            if (!$this->validateDate($this->request->post['date'], 'd-m-Y')) {
                $json['error'] = $this->language->get('error_date');
                $json['errormain']['date'] = 'date';
            }

            if ((utf8_strlen($this->request->post['phone']) != 18)) {
                $json['error'] = $this->language->get('error_phone');
                $json['errormain']['phone'] = 'phone';
            }

            if ((utf8_strlen($this->request->post['last_name']) < 3) || (utf8_strlen($this->request->post['last_name']) > 25)) {
                $json['error'] = $this->language->get('error_last_name');
                $json['errormain']['last_name'] = 'last_name';
            }

            if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
                $json['error'] = $this->language->get('error_name');
                $json['errormain']['name'] = 'name';
            }

            if (!isset($json['error'])) {
                $this->load->model('blog/article');
                $article_info = $this->model_blog_article->getArticle($this->request->get['article_id']);

                if ($this->request->post['file']) {
                    $uploadfile = $this->request->post['file'];
                }
                $data = array();
                $data['ip'] = $this->request->server['REMOTE_ADDR'];

                $data['name'] = $this->request->post['name'];
                $data['last_name'] = $this->request->post['last_name'];
                $data['phone'] = $this->request->post['phone'];
                $data['date'] = $this->request->post['date'];

                $data['questions'] = $this->request->post['question'];

                $mail = new Mail($this->config->get('config_mail_engine'));
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

                //	$mail->setTo($this->config->get('config_email'));
                //	$mail->setTo('ims@ukr.net');
                $mail->setFrom($this->config->get('config_email'));
                $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
                $mail->setSubject(html_entity_decode(sprintf($this->language->get('text_subject'), $article_info['name']), ENT_QUOTES, 'UTF-8'));
                $mail->setText($this->load->view('mail/resume', $data));
                if ($this->request->post['file']) {
                    $mail->addAttachment(DIR_UPLOAD . $uploadfile, basename($uploadfile));
                }
                //	$mail->send();

                // Send to additional alert emails
                $emails = explode(',', $this->config->get('configblog_emails'));

                foreach ($emails as $email) {
                    $email = trim($email);
                    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $mail->setTo($email);
                        $mail->send();
                    }
                }

                $json['success'] = true;
            }

        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
	
    public function sendmail2()
    {
        $this->load->language('mail/resume');

        $json = array();

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {

            // Captcha
            if ($this->config->get($this->config->get('config_captcha') . '_status') && in_array('review', (array)$this->config->get('config_captcha_page'))) {
                $captcha = $this->load->controller('extension/captcha/' . $this->config->get('config_captcha') . '/validate');

                if ($captcha) {
                    $json['error'] = $captcha;
                }
            }

            if ((utf8_strlen($this->request->post['phone']) != 18)) {
                $json['error'] = $this->language->get('error_phone');
                $json['errormain']['phone'] = 'phone';
            }

            if ((utf8_strlen($this->request->post['name']) < 3) || (utf8_strlen($this->request->post['name']) > 25)) {
                $json['error'] = $this->language->get('error_name');
                $json['errormain']['name'] = 'name';
            }
			
            if (!isset($this->request->post['file']) || !$this->request->post['file']) {
 //               $json['error'] = $this->language->get('error_file');
  //              $json['errormain']['file'] = 'file';
            }			

            if (!isset($json['error'])) {
                $this->load->model('blog/article');
                $article_info = $this->model_blog_article->getArticle($this->request->get['article_id']);

                if ($this->request->post['file']) {
                    $uploadfile = $this->request->post['file'];
                }
                $data = array();
                $data['ip'] = $this->request->server['REMOTE_ADDR'];

                $data['name'] = $this->request->post['name'];
                $data['last_name'] = $this->request->post['last_name'];
                $data['phone'] = $this->request->post['phone'];
                $data['date'] = $this->request->post['date'];

                $data['questions'] = $this->request->post['question'];

                $mail = new Mail($this->config->get('config_mail_engine'));
                $mail->parameter = $this->config->get('config_mail_parameter');
                $mail->smtp_hostname = $this->config->get('config_mail_smtp_hostname');
                $mail->smtp_username = $this->config->get('config_mail_smtp_username');
                $mail->smtp_password = html_entity_decode($this->config->get('config_mail_smtp_password'), ENT_QUOTES, 'UTF-8');
                $mail->smtp_port = $this->config->get('config_mail_smtp_port');
                $mail->smtp_timeout = $this->config->get('config_mail_smtp_timeout');

         //       	$mail->setTo($this->config->get('config_email'));
         //       	$mail->setTo('ims@ukr.net');
                $mail->setFrom($this->config->get('config_email'));
                $mail->setSender(html_entity_decode($this->config->get('config_name'), ENT_QUOTES, 'UTF-8'));
                $mail->setSubject(html_entity_decode($this->language->get('text_subject2')));
                $mail->setText($this->load->view('mail/resume', $data));
                if ($this->request->post['file']) {
                    $mail->addAttachment(DIR_UPLOAD . $uploadfile, basename($uploadfile));
                }
            //    	$mail->send();

                // Send to additional alert emails
                $emails = explode(',', $this->config->get('configblog_emails'));

                foreach ($emails as $email) {
                    $email = trim($email);
                    if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $mail->setTo($email);
                        $mail->send();
                    }
                }

                $json['success'] = true;
            }

        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    private function validateDate($date, $format = 'Y-m-d H:i:s')
    {
        $d = Datetime::createFromFormat($format, $date);
        return $d && $d->format($format) == $date;
    }
}

?>