<?php

/*
@author    Dmitriy Kubarev
@link    http://www.simpleopencart.com
*/

include_once(DIR_SYSTEM . 'library/simple/simple_controller.php');

class ControllerCheckoutSimplecheckoutDontForgetToAdd extends SimpleController
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

    private $_templateData = array();

    public function index($products = [])
    {
        $productsIds = array();
        $categoriesIds = array();
        $cartFingerprintParts = array();

        foreach ($products as $product) {
            $product_id = (int)$product['product_id'];
            $productsIds[$product_id] = $product_id;
            $cartFingerprintParts[] = $product_id . 'x' . (int)$product['quantity'];

            if (!empty($product['categories'])) {
                $categoriesIds = array_unique(array_merge($categoriesIds, $product['categories']));
            }
        }

        sort($cartFingerprintParts);

        $customerId = $this->customer->isLogged() ? (int)$this->customer->getId() : 0;
        $cacheKey = md5($customerId . '|' . implode(',', $cartFingerprintParts));

        if (
            !empty($this->session->data['dont_forget_to_add_cache']['key'])
            && $this->session->data['dont_forget_to_add_cache']['key'] === $cacheKey
            && isset($this->session->data['dont_forget_to_add_cache']['data'])
            && (!empty($this->session->data['dont_forget_to_add_cache']['expires']) && $this->session->data['dont_forget_to_add_cache']['expires'] > time())
        ) {
            return $this->session->data['dont_forget_to_add_cache']['data'];
        }

        $productsNeedAdd = array();

        if ($customerId) {
            $this->load->model('checkout/order');
            $this->load->model('catalog/product');

            if (!isset($this->session->data['dont_forget_has_order'][$customerId])) {
                $this->session->data['dont_forget_has_order'][$customerId] = $this->model_checkout_order->customerHasOrder($customerId);
            }

            $hasOrder = $this->session->data['dont_forget_has_order'][$customerId];
            $extraProducts = [$this->productVidkrivachkaID, $this->productKullerHolodokID, $this->productPidkisljuvachID];

            if (!$hasOrder) {
                if ($this->model_catalog_product->productCategoryRelationExists($productsIds, $this->catPodSystem)) {
                    $productsNeedAdd = $this->model_catalog_product->getProductsForNeedAddByCategory($this->catAromatizatorisolovi, $extraProducts, $productsIds);
                } elseif ($this->model_catalog_product->productCategoryRelationExists($productsIds, $this->catCartridges)) {
                    $productsNeedAdd = $this->model_catalog_product->getProductsForNeedAddByCategory($this->catAromatizatorisolovi, $extraProducts, $productsIds);
                } elseif ($this->model_catalog_product->productCategoryRelationExists($productsIds, $this->catLiquids)) {
                    $productsNeedAdd = $this->model_catalog_product->getProductsForNeedAddByCategory($this->catCartridge, $extraProducts, $productsIds);
                }
            } else {
                $hasAllCategories = count(array_intersect($this->requiredCategories, $categoriesIds)) === count($this->requiredCategories);

                if ($hasAllCategories) {
                    foreach ($extraProducts as $productId) {
                        if (!isset($productsIds[$productId])) {
                            $product = $this->model_catalog_product->getProduct($productId);

                            if ($product) {
                                $productsNeedAdd[$productId] = $product;
                            }
                        }
                    }
                } elseif (!empty(array_intersect($this->catCartridges, $categoriesIds))) {
                    $productsNeedAdd = $this->model_catalog_product->getProductsForNeedAddByCategory($this->catAromatizatorisolovi, $extraProducts, $productsIds);
                } elseif (!empty(array_intersect($this->catLiquids, $categoriesIds))) {
                    $productsNeedAdd = $this->model_catalog_product->getProductsForNeedAddIfOrderLiquids($customerId, $this->catCartridges, $this->catPodSystem, $extraProducts, $productsIds);
                } elseif (!empty(array_intersect($this->catPodSystem, $categoriesIds))) {
                    $productsNeedAdd = $this->model_catalog_product->getProductsForNeedAddByCategory($this->catAromatizatorisolovi, $extraProducts, $productsIds);
                }
            }
        }

        $this->_templateData['products'] = array();

        if ($productsNeedAdd) {
            $this->load->model('tool/image');

            $image_cart_width = $this->config->get('config_image_cart_width') ?: 96;
            $image_cart_height = $this->config->get('config_image_cart_height') ?: 96;

            foreach ($productsNeedAdd as $product) {
                if (empty($product) || empty($product['product_id'])) {
                    continue;
                }

                if (!empty($product['image'])) {
                    $image = $this->model_tool_image->resize($product['image'], $image_cart_width, $image_cart_height);
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

                $this->_templateData['products'][] = array(
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

        $this->_templateData['products_grouped'] = array_chunk(
            $this->_templateData['products'],
            6
        );

        $this->session->data['dont_forget_to_add_cache'] = array(
            'key' => $cacheKey,
            'expires' => time() + 600,
            'data' => $this->_templateData['products_grouped']
        );

        return $this->_templateData['products_grouped'];
    }
}
