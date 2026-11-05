<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerCheckoutSuccess extends Controller
{
    const REPLACE_SHORT_CODES = ['[bonus]'];

    public function index()
    {
        $this->load->language('checkout/success');

        $data['total_reward'] = 0;

        if (isset($this->session->data['order_id'])) {

            foreach ($this->cart->getProducts() as $product) {
                $data['total_reward'] = $data['total_reward'] + $product['reward'];
            }

            $this->session->data['total_reward'] = $data['total_reward'];

            $this->session->data['last_order_id'] = $this->session->data['order_id'];

            $this->cart->clear();

            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['guest']);
            unset($this->session->data['comment']);
            unset($this->session->data['order_id']);
            unset($this->session->data['coupon']);
            unset($this->session->data['coupon_gift']);
            unset($this->session->data['coupon_gift_addmore']);
            unset($this->session->data['noski']);
            unset($this->session->data['reward']);
            unset($this->session->data['voucher']);
            unset($this->session->data['vouchers']);
            unset($this->session->data['totals']);

            unset($this->session->data['discount']);

            if (isset($this->session->data['customer_purchase'])) {
                $this->session->data['customer_purchase']++;
            }

//            if (isset($this->session->data['customer']['customer_id'])) {
//
//                $this->load->model('setting/setting');
//
//                $this->load->model('customer/customer');
//
//                $this->load->library('balance_api');
//
//                $this->load->library('telegram');
//
//                $customer_id = $this->session->data['customer']['customer_id'];
//
//                $telephone = $this->session->data['customer']['telephone'];
//
//                $telegramUserId = $this->model_customer_customer->getTelegramCustomerByCustomerId($customer_id);
//
//                if ($telegramUserId) {
//
//                    $balanceInfo = $this->balance_api->getBalanceInfoByPhone($telephone);
//
//                    if ($balanceInfo) {
//                        $balance = ($balanceInfo['balance'] ? $balanceInfo['balance'] : 0) . ' ₴';
//                    } else {
//                        $balance = '(Інформація про баланс поки відсутня, спробуйте пізніше)';
//                    }
//
//
//                    $after_order = json_decode($this->model_setting_setting->getSettingValue('after_order'), true);
//
//                    $message = $after_order[1]['message'] ?? null;
//
//                    $message = str_replace(self::REPLACE_SHORT_CODES, [$balance], $message);
//
//                    $word = $after_order[1]['word'] ?? null;
//
//                    $word_link = $after_order[1]['word_link'] ?? null;
//
//                    if ($word && $word_link) {
//
//                        $message = str_replace(
//                            $word,
//                            '<a href="' . $word_link . '">' . $word . '</a>',
//                            $message
//                        );
//                    }
//
//                    $this->telegram
//                        ->getClient()
//                        ->sendMessage([
//                            'chat_id' => $telegramUserId,
//                            'parse_mode' => 'html',
//                            'text' => $message,
//                        ]);
//                }
//            }
        }

        if (!empty($this->session->data['last_order_id'])) {
            $this->document->setTitle(sprintf($this->language->get('heading_title_customer'), $this->session->data['last_order_id']));
            $this->document->setRobots('noindex,follow');
        } else {
            $this->document->setTitle($this->language->get('heading_title'));
            $this->document->setRobots('noindex,follow');
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_basket'),
            'href' => $this->url->link('checkout/cart')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_checkout'),
            'href' => $this->url->link('checkout/checkout', '', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_success'),
            'href' => $this->url->link('checkout/success')
        );

        if (!empty($this->session->data['last_order_id'])) {
            $data['heading_title'] = sprintf($this->language->get('heading_title_customer'), $this->session->data['last_order_id']);
        } else {
            $data['heading_title'] = $this->language->get('heading_title');
        }

        if ($this->customer->isLogged() && !empty($this->session->data['last_order_id'])) {
            $data['text_message'] = sprintf($this->language->get('text_customer'), $this->url->link('account/account', '', true), $this->url->link('account/order', '', true), $this->url->link('information/contact'), $this->url->link('product/special'), $this->session->data['last_order_id'], $this->url->link('account/download', '', true));
        } else {
            $data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
        }

        $data['text_reward'] = sprintf($this->language->get('text_reward'), $this->session->data['total_reward']);

        $data['telegram_text'] = $this->language->get('telegram_text');

        $data['continue'] = $this->url->link('common/home');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('common/success', $data));
    }
}
