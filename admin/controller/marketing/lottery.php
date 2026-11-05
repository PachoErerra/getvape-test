<?php

class ControllerMarketingLottery extends Controller
{
    private $error = array();

    public function index()
    {

        $this->load->language('sale/order');

        $this->document->setTitle('Таємна нагорода');

        $this->load->model('marketing/lottery');

        $this->getList();
    }


    protected function getList()
    {
        if (isset($this->request->get['filter_order_id'])) {
            $filter_order_id = $this->request->get['filter_order_id'];
        } else {
            $filter_order_id = '';
        }

        if (isset($this->request->get['filter_prize'])) {
            $datap = $_GET['filter_prize'];
            $filter_prize = json_decode(urldecode($datap), true);

            if (is_array($filter_prize)) {
                foreach ($filter_prize as $key => $prize) {
                    if ($prize === '' || $prize === null) {
                        $filter_prize[$key] = '__empty__';
                    }
                }
            } else {
                $filter_prize = array();
            }
        } else {
            $filter_prize = array();
        }

        if (isset($this->request->get['filter_customer'])) {
            $filter_customer = $this->request->get['filter_customer'];
        } else {
            $filter_customer = '';
        }

        if (isset($this->request->get['filter_order_status'])) {
            $filter_order_status = $this->request->get['filter_order_status'];
        } else {
            $filter_order_status = '';
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $filter_order_status_id = $this->request->get['filter_order_status_id'];
        } else {
            $filter_order_status_id = '';
        }

        if (isset($this->request->get['filter_used'])) {
            $filter_used = $this->request->get['filter_used'];
        } else {
            $filter_used = '';
        }

        if (isset($this->request->get['filter_active'])) {
            $filter_active = $this->request->get['filter_active'];
        } else {
            $filter_active = '';
        }

        if (isset($this->request->get['filter_overdue'])) {
            $filter_overdue = $this->request->get['filter_overdue'];
        } else {
            $filter_overdue = '';
        }

        if (isset($this->request->get['filter_date_added_start'])) {
            $filter_date_added_start = $this->request->get['filter_date_added_start'];
        } else {
            $filter_date_added_start = '';
        }
        if (isset($this->request->get['filter_date_added_end'])) {
            $filter_date_added_end = $this->request->get['filter_date_added_end'];
        } else {
            $filter_date_added_end = '';
        }

        if (isset($this->request->get['filter_date_used_start'])) {
            $filter_date_used_start = $this->request->get['filter_date_used_start'];
        } else {
            $filter_date_used_start = '';
        }
        if (isset($this->request->get['filter_date_used_end'])) {
            $filter_date_used_end = $this->request->get['filter_date_used_end'];
        } else {
            $filter_date_used_end = '';
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $filter_date_modified = $this->request->get['filter_date_modified'];
        } else {
            $filter_date_modified = '';
        }

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'lottery_history_id';
        }

        if (isset($this->request->get['order'])) {
            $order = $this->request->get['order'];
        } else {
            $order = 'DESC';
        }

        if (isset($this->request->get['page'])) {
            $page = (int)$this->request->get['page'];
        } else {
            $page = 1;
        }

        $url = '';

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_prize'])) {
            $url .= '&filter_prize=' . $this->request->get['filter_prize'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
        }

        if (isset($this->request->get['filter_used'])) {
            $url .= '&filter_used=' . $this->request->get['filter_used'];
        }
        if (isset($this->request->get['filter_active'])) {
            $url .= '&filter_active=' . $this->request->get['filter_active'];
        }
        if (isset($this->request->get['filter_overdue'])) {
            $url .= '&filter_overdue=' . $this->request->get['filter_overdue'];
        }
        if (isset($this->request->get['filter_date_added_start'])) {
            $url .= '&filter_date_added_start=' . $this->request->get['filter_date_added_start'];
        }
        if (isset($this->request->get['filter_date_added_end'])) {
            $url .= '&filter_date_added_end=' . $this->request->get['filter_date_added_end'];
        }
        if (isset($this->request->get['filter_date_used_start'])) {
            $url .= '&filter_date_used_start=' . $this->request->get['filter_date_used_start'];
        }
        if (isset($this->request->get['filter_date_used_end'])) {
            $url .= '&filter_date_used_end=' . $this->request->get['filter_date_used_end'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
        );

        $data['breadcrumbs'][] = array(
            'text' => 'Таємна нагорода',
            'href' => $this->url->link('marketing/lottery', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        $data['invoice'] = $this->url->link('sale/order/invoice', 'user_token=' . $this->session->data['user_token'], true);
        $data['shipping'] = $this->url->link('sale/order/shipping', 'user_token=' . $this->session->data['user_token'], true);
        $data['add'] = $this->url->link('sale/order/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('marketing/lottery/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

        $data['orders'] = array();

        $filter_data = array(
            'filter_order_id' => $filter_order_id,
            'filter_customer' => $filter_customer,
            'filter_prize' => $filter_prize,
            'filter_order_status_id' => $filter_order_status_id,
            'filter_used' => $filter_used,
            'filter_active' => $filter_active,
            'filter_overdue' => $filter_overdue,
            'filter_date_added_start' => $filter_date_added_start,
            'filter_date_added_end' => $filter_date_added_end,
            'filter_date_used_start' => $filter_date_used_start,
            'filter_date_used_end' => $filter_date_used_end,
            'filter_date_modified' => $filter_date_modified,
            'sort' => $sort,
            'order' => $order,
            'start' => ($page - 1) * $this->config->get('config_limit_admin'),
            'limit' => $this->config->get('config_limit_admin')
        );


        $data['prizes'] = array();
        $raw_prizes = $this->model_marketing_lottery->getPrizes();

        foreach ($raw_prizes as $prize) {
            if (is_array($prize) && isset($prize['value'])) {
                $data['prizes'][] = $prize;
            } else {
                $prize_type = (string)$prize;
                $data['prizes'][] = array(
                    'value' => ($prize_type === '') ? '__empty__' : $prize_type,
                    'label' => $this->getLotteryPrizeLabel($prize_type)
                );
            }
        }

        $order_total = $this->model_marketing_lottery->getTotalWins($filter_data);
        $totals = $this->calculateLotteryTotals($filter_data);

        $data['total_claims'] = (int)$totals['claims'];
        $data['total_points'] = (int)$totals['points'];
        $data['total_ultra'] = (int)$totals['ultra'];
        $data['total_empty'] = (int)$totals['empty'];
        $data['totals'] = $totals;
        $data['totals_text'] = 'Підсумок за фільтром: видач ' . $data['total_claims']
            . ', бонусів ' . $data['total_points']
            . ', ультра ' . $data['total_ultra'];

        $results = $this->model_marketing_lottery->getWins($filter_data);

        $this->load->model('customer/customer');

        foreach ($results as $result) {
            $date_valid = !empty($result['date_valid']) ? $result['date_valid'] : '';
            $date_used = !empty($result['date_used']) ? $result['date_used'] : '';
            $used = isset($result['used']) ? (int)$result['used'] : 0;
            $lottery_id = isset($result['lottery_history_id']) ? (int)$result['lottery_history_id'] : (isset($result['lottery_id']) ? (int)$result['lottery_id'] : 0);

            $overdue = false;
            if ($date_valid) {
                $targetTime = new DateTime($date_valid);
                $interval = (new DateTime())->diff($targetTime);
                $overdue = ($interval->invert == 1 && $used < 1);
            }

            $prize_type = isset($result['prize_type']) ? $result['prize_type'] : '';

            $data['orders'][] = array(
                'bp_id' => $lottery_id,
                'lottery_id' => $lottery_id,
                'used' => $used,
                'code' => (int)$result['amount'],
                'overdue' => $overdue,
                'customer' => $result['telephone'],
                'prize' => $this->getLotteryPrizeLabel($prize_type),
                'prize_type' => $prize_type,
                'date_added' => date('d.m.y H:i:s', strtotime($result['date_added'])),
                'date_valid' => $date_valid ? date('d.m.y H:i:s', strtotime($date_valid)) : '',
                'date_used' => (($date_used && $date_used != '0000-00-00 00:00:00') ? date('d.m.y H:i:s', strtotime($date_used)) : '') . (($used >= 2) ? (' (' . $used . ')') : ''),
            );
        }

        $data['user_token'] = $this->session->data['user_token'];

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->session->data['success'])) {
            $data['success'] = $this->session->data['success'];

            unset($this->session->data['success']);
        } else {
            $data['success'] = '';
        }

        if (isset($this->request->post['selected'])) {
            $data['selected'] = (array)$this->request->post['selected'];
        } else {
            $data['selected'] = array();
        }

        $url = '';

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_prize'])) {
            $url .= '&filter_prize=' . $this->request->get['filter_prize'];
        }

        if (isset($this->request->get['filter_order_status_id'])) {
            $url .= '&filter_order_status_id=' . $this->request->get['filter_order_status_id'];
        }

        if (isset($this->request->get['filter_used'])) {
            $url .= '&filter_used=' . $this->request->get['filter_used'];
        }
        if (isset($this->request->get['filter_active'])) {
            $url .= '&filter_active=' . $this->request->get['filter_active'];
        }
        if (isset($this->request->get['filter_overdue'])) {
            $url .= '&filter_overdue=' . $this->request->get['filter_overdue'];
        }
        if (isset($this->request->get['filter_date_added'])) {
            $url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
        }
        if (isset($this->request->get['filter_date_added_start'])) {
            $url .= '&filter_date_added_start=' . $this->request->get['filter_date_added_start'];
        }
        if (isset($this->request->get['filter_date_added_end'])) {
            $url .= '&filter_date_added_end=' . $this->request->get['filter_date_added_end'];
        }
        if (isset($this->request->get['filter_date_used_start'])) {
            $url .= '&filter_date_used_start=' . $this->request->get['filter_date_used_start'];
        }
        if (isset($this->request->get['filter_date_used_end'])) {
            $url .= '&filter_date_used_end=' . $this->request->get['filter_date_used_end'];
        }


        if ($order == 'ASC') {
            $url .= '&order=DESC';
        } else {
            $url .= '&order=ASC';
        }

        if (isset($this->request->get['page'])) {
            $url .= '&page=' . $this->request->get['page'];
        }

        $data['sort_order'] = $this->url->link('marketing/lottery', 'user_token=' . $this->session->data['user_token'] . '&sort=lottery_history_id' . $url, true);
        $data['sort_telephone'] = $this->url->link('marketing/lottery', 'user_token=' . $this->session->data['user_token'] . '&sort=telephone' . $url, true);
        $data['sort_prize'] = $this->url->link('marketing/lottery', 'user_token=' . $this->session->data['user_token'] . '&sort=prize_type' . $url, true);
        $data['sort_date_added'] = $this->url->link('marketing/lottery', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url, true);
        $data['sort_date_used'] = $this->url->link('marketing/lottery', 'user_token=' . $this->session->data['user_token'] . '&sort=date_used' . $url, true);

        $url = '';

        if (isset($this->request->get['filter_order_id'])) {
            $url .= '&filter_order_id=' . $this->request->get['filter_order_id'];
        }

        if (isset($this->request->get['filter_customer'])) {
            $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
        }

        if (isset($this->request->get['filter_order_status'])) {
            $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
        }

        if (isset($this->request->get['filter_prize'])) {
            $url .= '&filter_prize=' . $this->request->get['filter_prize'];
        }

        if (isset($this->request->get['filter_used'])) {
            $url .= '&filter_used=' . $this->request->get['filter_used'];
        }
        if (isset($this->request->get['filter_active'])) {
            $url .= '&filter_active=' . $this->request->get['filter_active'];
        }
        if (isset($this->request->get['filter_overdue'])) {
            $url .= '&filter_overdue=' . $this->request->get['filter_overdue'];
        }
        if (isset($this->request->get['filter_date_added_start'])) {
            $url .= '&filter_date_added_start=' . $this->request->get['filter_date_added_start'];
        }
        if (isset($this->request->get['filter_date_added_end'])) {
            $url .= '&filter_date_added_end=' . $this->request->get['filter_date_added_end'];
        }
        if (isset($this->request->get['filter_date_used_start'])) {
            $url .= '&filter_date_used_start=' . $this->request->get['filter_date_used_start'];
        }
        if (isset($this->request->get['filter_date_used_end'])) {
            $url .= '&filter_date_used_end=' . $this->request->get['filter_date_used_end'];
        }

        if (isset($this->request->get['filter_date_modified'])) {
            $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
        }

        if (isset($this->request->get['sort'])) {
            $url .= '&sort=' . $this->request->get['sort'];
        }

        if (isset($this->request->get['order'])) {
            $url .= '&order=' . $this->request->get['order'];
        }

        $pagination = new Pagination();
        $pagination->total = $order_total;
        $pagination->page = $page;
        $pagination->limit = $this->config->get('config_limit_admin');
        $pagination->url = $this->url->link('marketing/lottery', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

        $data['pagination'] = $pagination->render();

        $data['results'] = sprintf($this->language->get('text_pagination'), ($order_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($order_total - $this->config->get('config_limit_admin'))) ? $order_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $order_total, ceil($order_total / $this->config->get('config_limit_admin')));

        $data['filter_order_id'] = $filter_order_id;
        $data['filter_customer'] = $filter_customer;
        $data['filter_prize'] = $filter_prize;
        $data['filter_order_status_id'] = $filter_order_status_id;
        $data['filter_used'] = $filter_used;
        $data['filter_active'] = $filter_active;
        $data['filter_overdue'] = $filter_overdue;
        $data['filter_date_added_start'] = $filter_date_added_start;
        $data['filter_date_added_end'] = $filter_date_added_end;
        $data['filter_date_used_start'] = $filter_date_used_start;
        $data['filter_date_used_end'] = $filter_date_used_end;
        $data['filter_date_modified'] = $filter_date_modified;

        $data['sort'] = $sort;
        $data['order'] = $order;

        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

        // API login
        $data['catalog'] = $this->request->server['HTTPS'] ? HTTPS_CATALOG : HTTP_CATALOG;

        // API login
        $this->load->model('user/api');

        $api_info = $this->model_user_api->getApi($this->config->get('config_api_id'));

        if ($api_info && $this->user->hasPermission('modify', 'sale/order')) {
            $session = new Session($this->config->get('session_engine'), $this->registry);

            $session->start();

            $this->model_user_api->deleteApiSessionBySessionId($session->getId());

            $this->model_user_api->addApiSession($api_info['api_id'], $session->getId(), $this->request->server['REMOTE_ADDR']);

            $session->data['api_id'] = $api_info['api_id'];

            $data['api_token'] = $session->getId();
        } else {
            $data['api_token'] = '';
        }

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('marketing/lottery', $data));
    }

    public function delete()
    {
        $this->load->language('catalog/product');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('marketing/lottery');

        if (isset($this->request->post['selected'])) {
            foreach ($this->request->post['selected'] as $product_id) {
                $this->model_marketing_lottery->deletelottery($product_id);
            }

            $this->session->data['success'] = $this->language->get('text_success');

            $url = '';


            if (isset($this->request->get['filter_customer'])) {
                $url .= '&filter_customer=' . urlencode(html_entity_decode($this->request->get['filter_customer'], ENT_QUOTES, 'UTF-8'));
            }

            if (isset($this->request->get['filter_order_status'])) {
                $url .= '&filter_order_status=' . $this->request->get['filter_order_status'];
            }

            if (isset($this->request->get['filter_prize'])) {
                $url .= '&filter_prize=' . $this->request->get['filter_prize'];
            }

            if (isset($this->request->get['filter_used'])) {
                $url .= '&filter_used=' . $this->request->get['filter_used'];
            }
            if (isset($this->request->get['filter_active'])) {
                $url .= '&filter_active=' . $this->request->get['filter_active'];
            }
            if (isset($this->request->get['filter_overdue'])) {
                $url .= '&filter_overdue=' . $this->request->get['filter_overdue'];
            }
            if (isset($this->request->get['filter_date_added_start'])) {
                $url .= '&filter_date_added_start=' . $this->request->get['filter_date_added_start'];
            }
            if (isset($this->request->get['filter_date_added_end'])) {
                $url .= '&filter_date_added_end=' . $this->request->get['filter_date_added_end'];
            }
            if (isset($this->request->get['filter_date_used_start'])) {
                $url .= '&filter_date_used_start=' . $this->request->get['filter_date_used_start'];
            }
            if (isset($this->request->get['filter_date_used_end'])) {
                $url .= '&filter_date_used_end=' . $this->request->get['filter_date_used_end'];
            }

            if (isset($this->request->get['filter_date_modified'])) {
                $url .= '&filter_date_modified=' . $this->request->get['filter_date_modified'];
            }

            if (isset($this->request->get['sort'])) {
                $url .= '&sort=' . $this->request->get['sort'];
            }

            if (isset($this->request->get['order'])) {
                $url .= '&order=' . $this->request->get['order'];
            }

            if (isset($this->request->get['page'])) {
                $url .= '&page=' . $this->request->get['page'];
            }

            $this->response->redirect($this->url->link('marketing/lottery', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getList();
    }

    private function getLotteryPrizeLabel($prize_type) {
        if ($prize_type === 'points') {
            return 'Бонусні бали';
        }

        if ($prize_type === 'ultra') {
            return 'Ультра';
        }

        if ($prize_type === 'none') {
            return 'Без призу (none)';
        }

        if ($prize_type === '' || $prize_type === null) {
            return 'Порожній запис';
        }

        return (string)$prize_type;
    }

    private function calculateLotteryTotals($data) {
        if (method_exists($this->model_marketing_lottery, 'getTotals')) {
            $totals = $this->model_marketing_lottery->getTotals($data);

            return array(
                'claims' => isset($totals['claims']) ? (int)$totals['claims'] : 0,
                'points' => isset($totals['points']) ? (int)$totals['points'] : 0,
                'ultra' => isset($totals['ultra']) ? (int)$totals['ultra'] : 0,
                'empty' => isset($totals['empty']) ? (int)$totals['empty'] : 0
            );
        }

        $sql = "SELECT
            COUNT(*) AS claims,
            COALESCE(SUM(CASE WHEN prize_type = 'points' THEN amount ELSE 0 END), 0) AS points,
            COALESCE(SUM(CASE WHEN prize_type = 'ultra' THEN amount ELSE 0 END), 0) AS ultra,
            COALESCE(SUM(CASE WHEN prize_type IN ('', 'none') OR prize_type IS NULL OR amount <= 0 THEN 1 ELSE 0 END), 0) AS empty_claims
            FROM `" . DB_PREFIX . "customer_lottery_history`
            WHERE lottery_history_id != 0";

        if (!empty($data['filter_prize'])) {
            $prizes = array();

            foreach ((array)$data['filter_prize'] as $prize) {
                if ($prize === '__empty__') {
                    $prizes[] = '';
                } else {
                    $prizes[] = $this->db->escape($prize);
                }
            }

            $sql .= " AND prize_type IN ('" . implode("','", $prizes) . "')";
        }

        if (!empty($data['filter_customer'])) {
            $sql .= " AND telephone LIKE '%" . $this->db->escape($data['filter_customer']) . "%'";
        }

        if (!empty($data['filter_date_added_start']) && !empty($data['filter_date_added_end'])) {
            $sql .= " AND date_added >= '" . $this->db->escape($data['filter_date_added_start']) . "' AND date_added <='" . $this->db->escape($data['filter_date_added_end']) . "'";
        } elseif (!empty($data['filter_date_added_start'])) {
            $sql .= " AND date_added >= '" . $this->db->escape($data['filter_date_added_start']) . "'";
        } elseif (!empty($data['filter_date_added_end'])) {
            $sql .= " AND date_added <='" . $this->db->escape($data['filter_date_added_end']) . "'";
        }

        $query = $this->db->query($sql);

        return array(
            'claims' => isset($query->row['claims']) ? (int)$query->row['claims'] : 0,
            'points' => isset($query->row['points']) ? (int)$query->row['points'] : 0,
            'ultra' => isset($query->row['ultra']) ? (int)$query->row['ultra'] : 0,
            'empty' => isset($query->row['empty_claims']) ? (int)$query->row['empty_claims'] : 0
        );
    }
}
