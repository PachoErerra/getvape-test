<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerAccountEdit extends Controller
{
    private $error = array();

    public function index()
    {
        if (!$this->customer->isLogged()) {
            $this->session->data['redirect'] = $this->url->link('account/edit', '', true);

            $this->response->redirect($this->url->link('account/login', '', true));
        }

        $this->load->language('account/edit');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->setRobots('noindex,follow');

        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
        $this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

        $this->load->model('account/customer');

        $customer_info = $this->model_account_customer->getCustomer($this->customer->getId());
        $data['name'] = $customer_info['firstname'] . ' ' . $customer_info['lastname'];

        $data['discount'] = $customer_info['discount'];

        $data['aname'] = mb_substr($data['name'], 0, 1);
		
		$affiliate_info = $this->model_account_customer->getAffiliate($this->customer->getId());
		
		if (!$affiliate_info) {	
			$affiliate = array();
			$affiliate['company'] = 'default';
			$affiliate['website'] = 'default';
			$affiliate['tax'] = '1';
			$affiliate['payment'] = 'bank';
			$affiliate['cheque'] = '1';
			$affiliate['paypal'] = '1';
			$affiliate['bank_name'] = 'bank';
			$affiliate['bank_branch_number'] = 'bank';
			$affiliate['bank_swift_code'] = 'bank';
			$affiliate['bank_account_name'] = 'bank';
			$affiliate['bank_account_number'] = 'bank';
			$affiliate['bank_name'] = 'bank';
			$this->model_account_customer->addAffiliate($this->customer->getId(), $affiliate);
		}

        $form = array();

        if ($this->request->server['REQUEST_METHOD'] == 'POST') {
            if ($this->validate()) {
                $postData = $this->request->post;

                if (isset($postData['fax']) && $postData['fax']) {
                    $postData['fax'] = date('d-m-Y', strtotime($postData['fax']));
                }

                $this->model_account_customer->editCustomer($this->customer->getId(), $postData);

                $this->response->redirect($this->url->link('account/edit', '', true));
            }

            // PRG: ошибки не оставляем на POST-ответе, иначе F5 снова шлет форму
            $this->session->data['account_edit'] = array(
                'error' => $this->error,
                'post' => $this->request->post
            );

            $this->response->redirect($this->url->link('account/edit', '', true));
        }

        if (!empty($this->session->data['account_edit'])) {
            $this->error = $this->session->data['account_edit']['error'];
            $form = $this->session->data['account_edit']['post'];
            unset($this->session->data['account_edit']);
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_account'),
            'href' => $this->url->link('account/account', '', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_edit'),
            'href' => $this->url->link('account/edit', '', true)
        );

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['firstname'])) {
            $data['error_firstname'] = $this->error['firstname'];
        } else {
            $data['error_firstname'] = '';
        }

        if (isset($this->error['lastname'])) {
            $data['error_lastname'] = $this->error['lastname'];
        } else {
            $data['error_lastname'] = '';
        }

        if (isset($this->error['email'])) {
            $data['error_email'] = $this->error['email'];
        } else {
            $data['error_email'] = '';
        }

        if (isset($this->error['telephone'])) {
            $data['error_telephone'] = $this->error['telephone'];
        } else {
            $data['error_telephone'] = '';
        }

        if (isset($this->error['custom_field'])) {
            $data['error_custom_field'] = $this->error['custom_field'];
        } else {
            $data['error_custom_field'] = array();
        }

        $data['action'] = $this->url->link('account/edit', '', true);

        if (isset($form['firstname'])) {
            $data['firstname'] = $form['firstname'];
        } elseif (!empty($customer_info)) {
            $data['firstname'] = $customer_info['firstname'];
        } else {
            $data['firstname'] = '';
        }

        if (isset($form['lastname'])) {
            $data['lastname'] = $form['lastname'];
        } elseif (!empty($customer_info)) {
            $data['lastname'] = $customer_info['lastname'];
        } else {
            $data['lastname'] = '';
        }

        if (isset($form['email'])) {
            $data['email'] = $form['email'];
        } elseif (!empty($customer_info)) {
            $data['email'] = $customer_info['email'];
        } else {
            $data['email'] = '';
        }

        if (isset($form['telephone'])) {
            $data['telephone'] = $form['telephone'];
        } elseif (!empty($customer_info)) {
            $data['telephone'] = $customer_info['telephone'];
        } else {
            $data['telephone'] = '';
        }

        if (isset($form['fax'])) {
            $data['fax'] = date('Y-M-d', strtotime($form['fax']));
        } elseif (!empty($customer_info)) {
            $data['fax'] = $customer_info['fax'] ? date('Y-m-d', strtotime($customer_info['fax'])) : '';
        } else {
            $data['fax'] = '';
        }

        // Custom Fields
        $data['custom_fields'] = array();

        $this->load->model('account/custom_field');

        $custom_fields = $this->model_account_custom_field->getCustomFields($this->config->get('config_customer_group_id'));

        foreach ($custom_fields as $custom_field) {
            if ($custom_field['location'] == 'account') {
                $data['custom_fields'][] = $custom_field;
            }
        }

        if (isset($form['custom_field']['account'])) {
            $data['account_custom_field'] = $form['custom_field']['account'];
        } elseif (isset($customer_info)) {
            $data['account_custom_field'] = json_decode($customer_info['custom_field'], true);
        } else {
            $data['account_custom_field'] = array();
        }

        $data['back'] = $this->url->link('account/account', '', true);

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('account/edit', $data));
    }

    protected function validate()
    {
        $firstname = trim($this->request->post['firstname']);
        $lastname = trim($this->request->post['lastname']);
        $name_pattern = '/^[\p{L}]+(?:[\'’\-\s][\p{L}]+)*$/u';

        if ((utf8_strlen($firstname) < 2) || (utf8_strlen($firstname) > 32)) {
            $this->error['firstname'] = $this->language->get('error_firstname');
        } elseif (!preg_match($name_pattern, $firstname)) {
            $this->error['firstname'] = $this->language->get('error_firstname_format');
        }

        if ((utf8_strlen($lastname) < 2) || (utf8_strlen($lastname) > 32)) {
            $this->error['lastname'] = $this->language->get('error_lastname');
        } elseif (!preg_match($name_pattern, $lastname)) {
            $this->error['lastname'] = $this->language->get('error_lastname_format');
        }

        if ((utf8_strlen($this->request->post['email']) > 96) || !filter_var($this->request->post['email'], FILTER_VALIDATE_EMAIL)) {
            $this->error['email'] = $this->language->get('error_email');
        } elseif (($this->customer->getEmail() != $this->request->post['email']) && $this->model_account_customer->getTotalCustomersByEmail($this->request->post['email'])) {
            $this->error['email'] = $this->language->get('error_exists');
        }

        if ((utf8_strlen($this->request->post['telephone']) < 3) || (utf8_strlen($this->request->post['telephone']) > 32)) {
            $this->error['telephone'] = $this->language->get('error_telephone');
        }

        // Custom field validation
        $this->load->model('account/custom_field');

        $custom_fields = $this->model_account_custom_field->getCustomFields('account', $this->config->get('config_customer_group_id'));

        foreach ($custom_fields as $custom_field) {
            if ($custom_field['location'] == 'account') {
                if ($custom_field['required'] && empty($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']])) {
                    $this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                } elseif (($custom_field['type'] == 'text') && !empty($custom_field['validation']) && !filter_var($this->request->post['custom_field'][$custom_field['location']][$custom_field['custom_field_id']], FILTER_VALIDATE_REGEXP, array('options' => array('regexp' => $custom_field['validation'])))) {
                    $this->error['custom_field'][$custom_field['custom_field_id']] = sprintf($this->language->get('error_custom_field'), $custom_field['name']);
                }
            }
        }

        return !$this->error;
    }
}