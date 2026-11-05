<?php

class ControllerMarketingFortune extends Controller
{
    private $error = array();

    public function index()
    {

        $this->load->language('sale/order');

        $this->document->setTitle('Фортуна');

        $this->load->model('marketing/fortune');

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
        } else {
            $filter_prize = '';
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

        if (isset($this->request->get['sort'])) {
            $sort = $this->request->get['sort'];
        } else {
            $sort = 'fortune_id';
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
            'text' => 'Фонтуна',
            'href' => $this->url->link('marketing/fortune', 'user_token=' . $this->session->data['user_token'] . $url, true)
        );

        $data['invoice'] = $this->url->link('sale/order/invoice', 'user_token=' . $this->session->data['user_token'], true);
        $data['shipping'] = $this->url->link('sale/order/shipping', 'user_token=' . $this->session->data['user_token'], true);
        $data['add'] = $this->url->link('sale/order/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('marketing/fortune/delete', 'user_token=' . $this->session->data['user_token'] . $url, true);

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


        $data['prizes'] = $this->model_marketing_fortune->getPrizes();

        $order_total = $this->model_marketing_fortune->getTotalWins($filter_data);


        $results = $this->model_marketing_fortune->getWins($filter_data);

        $this->load->model('customer/customer');

        $now = new DateTime();

        foreach ($results as $result) {
            $targetTime = new DateTime($result['date_valid']);
            $interval = $now->diff($targetTime);

            if ($interval->invert == 0) {
                $overdue = false;
            } else {
                $overdue = true;
            }

            $data['orders'][] = array(
                'fortune_id' => $result['fortune_id'],
                'used' => $result['used'],
                'code' => $result['code'],
                'overdue' => $overdue,
                'customer' => $result['telephone'],
                'prize' => $result['prize'],
                'date_added' => date('d.m.y H:i:s', strtotime($result['date_added'])),
                'date_valid' => date('d.m.y H:i:s', strtotime($result['date_valid'])),
                'date_used' => (($result['date_used'] != '0000-00-00 00:00:00') ? date('d.m.y H:i:s', strtotime($result['date_used'])) : '') . (($result['used'] >= 2) ? (' (' . $result['used'] . ')') : ''),
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

        $data['sort_order'] = $this->url->link('marketing/fortune', 'user_token=' . $this->session->data['user_token'] . '&sort=fortune_id' . $url, true);
        $data['sort_telephone'] = $this->url->link('marketing/fortune', 'user_token=' . $this->session->data['user_token'] . '&sort=telephone' . $url, true);
        $data['sort_prize'] = $this->url->link('marketing/fortune', 'user_token=' . $this->session->data['user_token'] . '&sort=prize' . $url, true);
        $data['sort_date_added'] = $this->url->link('marketing/fortune', 'user_token=' . $this->session->data['user_token'] . '&sort=date_added' . $url, true);
        $data['sort_date_used'] = $this->url->link('marketing/fortune', 'user_token=' . $this->session->data['user_token'] . '&sort=date_used' . $url, true);

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
        $pagination->url = $this->url->link('marketing/fortune', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

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

        $this->response->setOutput($this->load->view('marketing/fortune', $data));
    }

    public function delete()
    {
        $this->load->language('catalog/product');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('marketing/fortune');

        if (isset($this->request->post['selected'])) {
            foreach ($this->request->post['selected'] as $product_id) {
                $this->model_marketing_fortune->deleteFortune($product_id);
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

            $this->response->redirect($this->url->link('marketing/fortune', 'user_token=' . $this->session->data['user_token'] . $url, true));
        }

        $this->getList();
    }
}
