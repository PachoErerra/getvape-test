<?php
// *	@source		See SOURCE.txt for source and other copyright.
// *	@license	GNU General Public License version 3; see LICENSE.txt

class ControllerCheckoutCheckout extends Controller
{

    private $catAromatizatorisolovi = 3013;  // Ароматизатори сольові
    private $catCartridge = 1271;  // Катріджи

    private $productVidkrivachkaID = 17524; // Відкривачка Vapors
    private $productKullerHolodokID = 16443; // Куллер Холодок Uliq
    private $productPidkisljuvachID = 16390; // Підкислювач Uliq

    protected $catPodSystem = [1122];  // подсистемы
    protected $catCartridges = [1271]; // картридж
    protected $catLiquids = [3013, 2810]; // жидкости

    protected $requiredCategories = [1122, 1271, 3013, 2810];


    public function index()
    {


        // Validate cart has products and has stock.
        if ((!$this->cart->hasProducts() && empty($this->session->data['vouchers'])) || (!$this->cart->hasStock() && !$this->config->get('config_stock_checkout'))) {
            $this->response->redirect($this->url->link('checkout/cart'));
        }

        // Validate minimum quantity requirements.
        $products = $this->cart->getProducts();

        $productsIds = array();

        $categoriesIds = array();


        foreach ($products as $product) {

            $product_total = 0;

            $productsIds[$product['product_id']] = $product['product_id'];

            if (isset($product['categories']) && $product['categories']) {

                $categoriesIds = array_unique(array_merge($categoriesIds, $product['categories']));
            }

            foreach ($products as $product_2) {
                if ($product_2['product_id'] == $product['product_id']) {
                    $product_total += $product_2['quantity'];
                }
            }

            if ($product['minimum'] > $product_total) {
                $this->response->redirect($this->url->link('checkout/cart'));
            }
        }

        if ($this->customer->isLogged()) {

            $customerId = $this->customer->getId();

            $this->load->model('checkout/order');

            $this->load->model('catalog/product');

            $hasOrder = $this->model_checkout_order->customerHasOrder($customerId);

            $productsNeedAdd = array();

            if (!$hasOrder) {

                if ($this->model_catalog_product->productCategoryRelationExists($productsIds, $this->catPodSystem)) {
                    $productsNeedAdd = $this->model_catalog_product->getProductsForNeedAddByCategory($this->catAromatizatorisolovi, [$this->productVidkrivachkaID, $this->productKullerHolodokID, $this->productPidkisljuvachID], $productsIds);
                } elseif ($this->model_catalog_product->productCategoryRelationExists($productsIds, $this->catCartridges)) {
                    $productsNeedAdd = $this->model_catalog_product->getProductsForNeedAddByCategory($this->catAromatizatorisolovi, [$this->productVidkrivachkaID, $this->productKullerHolodokID, $this->productPidkisljuvachID], $productsIds);
                } elseif ($this->model_catalog_product->productCategoryRelationExists($productsIds, $this->catLiquids)) {
                    $productsNeedAdd = $this->model_catalog_product->getProductsForNeedAddByCategory($this->catCartridge, [$this->productVidkrivachkaID, $this->productKullerHolodokID, $this->productPidkisljuvachID], $productsIds);
                }
            } else {
                $hasAllCategories = count(array_intersect($this->requiredCategories, $categoriesIds)) === count($this->requiredCategories);

                if ($hasAllCategories) {

                    foreach ([$this->productVidkrivachkaID, $this->productKullerHolodokID, $this->productPidkisljuvachID] as $productId) {

                        if (!isset($productsIds[$productId])) {
                            $productsNeedAdd[$productId] = $this->model_catalog_product->getProduct($productId);
                        }
                    }
                } elseif (!empty(array_intersect($this->catCartridges, $categoriesIds))) {
                    $productsNeedAdd = $this->model_catalog_product->getProductsForNeedAddByCategory($this->catAromatizatorisolovi, [$this->productVidkrivachkaID, $this->productKullerHolodokID, $this->productPidkisljuvachID], $productsIds);
                } elseif (!empty(array_intersect($this->catLiquids, $categoriesIds))) {
                    $productsNeedAdd = $this->model_catalog_product->getProductsForNeedAddIfOrderLiquids($customerId, $this->catCartridges, $this->catPodSystem, [$this->productVidkrivachkaID, $this->productKullerHolodokID, $this->productPidkisljuvachID], $productsIds);
                } elseif (!empty(array_intersect($this->catPodSystem, $categoriesIds))) {
                    $productsNeedAdd = $this->model_catalog_product->getProductsForNeedAddByCategory($this->catAromatizatorisolovi, [$this->productVidkrivachkaID, $this->productKullerHolodokID, $this->productPidkisljuvachID], $productsIds);
                }

            }
        }

        $data['products_need_add'] = array();

        if ($productsNeedAdd) {

            $this->load->model('tool/image');


            foreach ($productsNeedAdd as $product) {

                if ($product['image']) {
                    $image = $this->model_tool_image->resize($product['image'], 96, 96);
                } else {
                    $image = $this->model_tool_image->resize('placeholder.png', 96, 96);
                }

                if ($this->customer->isLogged() || !$this->config->get('config_customer_price')) {
                    $price = $this->currency->format($this->tax->calculate($product['price'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                } else {
                    $price = false;
                }

                if (!is_null($product['special']) && (float)$product['special'] >= 0) {
                    $special = $this->currency->format($this->tax->calculate($product['special'], $product['tax_class_id'], $this->config->get('config_tax')), $this->session->data['currency']);
                    $tax_price = (float)$product['special'];
                } else {
                    $special = false;
                    $tax_price = (float)$product['price'];
                }

                $data['products_need_add'][] = array(
                    'product_id' => $product['product_id'],
                    'thumb' => $image,
                    'name' => $product['name'],
                    'quantity' => $product['quantity'] + $product['quantity_sklad'],
                    'quantity_shop' => $product['quantity'],
                    'price' => $price,
                    'special' => $special,
                    'tax_price' => $tax_price,
                    'minimum' => $product['minimum'] > 0 ? $product['minimum'] : 1,
                    'href' => $this->url->link('product/product', 'product_id=' . $product['product_id'])
                );

            }
        }



        $this->load->language('checkout/checkout');

        $this->document->setTitle($this->language->get('heading_title'));
        $this->document->setRobots('noindex,follow');

        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/moment/moment-with-locales.min.js');
        $this->document->addScript('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.js');
        $this->document->addStyle('catalog/view/javascript/jquery/datetimepicker/bootstrap-datetimepicker.min.css');

        // Required by klarna
        if ($this->config->get('payment_klarna_account') || $this->config->get('payment_klarna_invoice')) {
            $this->document->addScript('http://cdn.klarna.com/public/kitt/toc/v1.0/js/klarna.terms.min.js');
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_cart'),
            'href' => $this->url->link('checkout/cart')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('checkout/checkout', '', true)
        );

        $data['text_checkout_option'] = sprintf($this->language->get('text_checkout_option'), 1);
        $data['text_checkout_account'] = sprintf($this->language->get('text_checkout_account'), 2);
        $data['text_checkout_payment_address'] = sprintf($this->language->get('text_checkout_payment_address'), 2);
        $data['text_checkout_shipping_address'] = sprintf($this->language->get('text_checkout_shipping_address'), 3);
        $data['text_checkout_shipping_method'] = sprintf($this->language->get('text_checkout_shipping_method'), 4);

        if ($this->cart->hasShipping()) {
            $data['text_checkout_payment_method'] = sprintf($this->language->get('text_checkout_payment_method'), 5);
            $data['text_checkout_confirm'] = sprintf($this->language->get('text_checkout_confirm'), 6);
        } else {
            $data['text_checkout_payment_method'] = sprintf($this->language->get('text_checkout_payment_method'), 3);
            $data['text_checkout_confirm'] = sprintf($this->language->get('text_checkout_confirm'), 4);
        }

        if (isset($this->session->data['error'])) {
            $data['error_warning'] = $this->session->data['error'];
            unset($this->session->data['error']);
        } else {
            $data['error_warning'] = '';
        }

        $data['logged'] = $this->customer->isLogged();

        if (isset($this->session->data['account'])) {
            $data['account'] = $this->session->data['account'];
        } else {
            $data['account'] = '';
        }

        $data['shipping_required'] = $this->cart->hasShipping();

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('checkout/checkout', $data));
    }

    public function country()
    {
        $json = array();

        $this->load->model('localisation/country');

        $country_info = $this->model_localisation_country->getCountry($this->request->get['country_id']);

        if ($country_info) {
            $this->load->model('localisation/zone');

            $json = array(
                'country_id' => $country_info['country_id'],
                'name' => $country_info['name'],
                'iso_code_2' => $country_info['iso_code_2'],
                'iso_code_3' => $country_info['iso_code_3'],
                'address_format' => $country_info['address_format'],
                'postcode_required' => $country_info['postcode_required'],
                'zone' => $this->model_localisation_zone->getZonesByCountryId($this->request->get['country_id']),
                'status' => $country_info['status']
            );
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }

    public function customfield()
    {
        $json = array();

        $this->load->model('account/custom_field');

        // Customer Group
        if (isset($this->request->get['customer_group_id']) && is_array($this->config->get('config_customer_group_display')) && in_array($this->request->get['customer_group_id'], $this->config->get('config_customer_group_display'))) {
            $customer_group_id = $this->request->get['customer_group_id'];
        } else {
            $customer_group_id = $this->config->get('config_customer_group_id');
        }

        $custom_fields = $this->model_account_custom_field->getCustomFields($customer_group_id);

        foreach ($custom_fields as $custom_field) {
            $json[] = array(
                'custom_field_id' => $custom_field['custom_field_id'],
                'required' => $custom_field['required']
            );
        }

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}