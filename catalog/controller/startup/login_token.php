<?php

class ControllerStartupLoginToken extends Controller
{
    public function index()
    {


//        echo ini_get('session.save_path');
//        die;
        if ($this->customer->isLogged()) return;

        return;

        if (!isset($this->request->get['login_token'])) {
            return;
        }

        $token = $this->request->get['login_token'];

        $this->load->model('customer/customer');

        $customer = $this->model_customer_customer->getCustomerByLoginToken($token);

        if ($token && $customer && $customer['telegram_login_token_expires_at'] && $customer['telegram_login_token']) {

            if ($customer['telegram_login_token_expires_at'] > time()) {

                $this->model_customer_customer->loginById($customer['customer_id']);

                $this->response->redirect($_SERVER['REQUEST_URI']);

                exit;

//                $this->response->redirect(HTTP_CATALOG . 'index.php?route=account/login&token=' . $token);
//                exit;
//                $this->customer->loginById($customer['customer_id']);

//                $this->model_customer_customer->clearLoginToken($customer['customer_id']);

            } else {

                $this->model_customer_customer->clearLoginToken($customer['customer_id']);

            }
        }
    }
}