<?php
class ControllerApiOneboxsync extends Controller {
    

    public function getApiFromToken (){
	$this->load->model('oneboxsync/oneboxsync');
        $apiId = $this->model_oneboxsync_oneboxsync->getApiId($this->request->get['token']);

        if ($apiId) {
            $this->session->data['api_id'] = $apiId;
        } else {
            $this->load->model('account/api');
            
            $api_info = $this->model_account_api->login(
                $this->request->post['username'], $this->request->post['password']
            );

            if ($api_info['api_id']) {
                $this->session->data['api_id'] = $api_info['api_id'];
            }
        }
    }

    //-------------------------UPDATE CATEGORY ----------------------------------------------------------------------------
    public function updateCategory (){
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            $json_url = $this->request->post['json_url'];
            $languages = $this->request->post['languages'];
            $languages = json_decode(htmlspecialchars_decode($languages));
            
            $this->load->model('oneboxsync/oneboxsync');
            $onebox_json = file_get_contents($json_url);
            $onebox_content = json_decode($onebox_json);
            $lang = array();
            if (count($languages)){
                foreach ($languages as $language){
                    $langId = $this->model_oneboxsync_oneboxsync->getLanguageId($language);
                    $lang[] = array(
                                    'id' => $langId[0]['language_id'],
                                    'name_code' => 'name_'.$language
                                    );
                }
            }
            foreach ($onebox_content as $value) {
                $id = $value->id;
                $name = $value->name;
                $name_lang = array();
                if (count($lang)){
                    foreach ($lang as $lan){
                        $name_lang[] = array (
                                              'lang_id' => $lan['id'],
                                              'name' => $value->$lan['name_code']
                                              );
                    }
                } else {
                    $name_lang[] = array (
                                          'lang_id' => '1',
                                          'name' => $name
                                          );
                }
                
                $parentid = $value->parentid;
                
                $is_category = $this->model_oneboxsync_oneboxsync->getCategoryId($id);
                
                if ($is_category->num_rows == 0) {
                    $json = $this->model_oneboxsync_oneboxsync->newCategory($id, $parentid, $name, $name_lang);
                } else {
                    $json = $this->model_oneboxsync_oneboxsync->updateCategory($id,$parentid, $name, $name_lang);
                }
            }
        }
        if (isset($this->request->server['HTTP_ORIGIN'])) {
            $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
            $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            $this->response->addHeader('Access-Control-Max-Age: 1000');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json_url));
    }
    //----------------------------------------------------------------------------------------------------------------------
    
    //------------------------UPDATE PRODUCT--------------------------------------------------------------------------------
    public function updateProduct ()
    {
        set_time_limit(0);
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            $json_url = $this->request->post['json_url'];
            $languages = $this->request->post['languages'];
            $languages = json_decode(htmlspecialchars_decode($languages));
            
            $this->load->model('oneboxsync/oneboxsync');
            $onebox_json = file_get_contents($json_url);
            
            $lang = array();
            if (count($languages)){
                foreach ($languages as $language){
                    $langId = $this->model_oneboxsync_oneboxsync->getLanguageId($language);
                    $lang[] = array(
                                    'id' => $langId[0]['language_id'],
                                    'name_code' => 'name_'.$language
                                    );
                }
            }
            
            $onebox_content = json_decode($onebox_json);
            foreach ($onebox_content as $value) {
                $name = $value->name;
                $name_lang = array();
                
                if (count($lang)){
                    foreach ($lang as $lan){
                        $name_lang[] = array (
                                              'lang_id' => $lan['id'],
                                              'name' => $value->$lan['name_code']
                                              );
                    }
                } else {
                    $name_lang[] = array (
                                          'lang_id' => '1',
                                          'name' => $name
                                          );
                }
                
                $id = $value->id;
                
                $quantity = $value->avail;
                $description = $value->description;
                $imageArray = $value->imageArray;
                $articul = $value->articul;
                $model = $value->model;
                $brandname = $value->brandname;
                $price = $value->price;
                $priceold = $value->priceold;
                $priceoldend = $value->priceoldend;
                $pricesCountArray = (array) $value->pricesCountArray;
                $currency = $value->currency;
                $avail = '1';
                $availtext = $value->availtext;
                $categoryid = $value->categoryid;
                /*               if ($id == ''){
                 $is_articul = $this->model_oneboxsync_oneboxsync->getProductArticul($articul);
                 $id = $is_articul->rows['0']['product_id'];
                 $is_product1 = 1;
                 if ($id == ''){
                 $max_id = $this->model_oneboxsync_oneboxsync->getMaxProductId();
                 $id = $max_id->rows[0]['product_id']+1;
                 $is_product1 = 0;
                 }
                 
                 } else {
                 */                 $is_product = $this->model_oneboxsync_oneboxsync->getProductId($id);
                $is_product1 = $is_product->num_rows;
                //              }
                
                $is_currency = $this->model_oneboxsync_oneboxsync->getCurrencyValue($currency);
                
                if ($is_currency->num_rows == 0) {
                    $this->model_oneboxsync_oneboxsync->newCurrencyValue($currency, $currency, '1', '1');
                } else {
                    $ratio = $is_currency->row['value'];
                    $price = $price / $ratio;
                }
                
                $nImage = count($imageArray);
                $image = '';
                
                if ($nImage == 0) {
                    $image = '';
                } elseif ($nImage == 1) {
                    $file_name = basename($imageArray[0]);
                    if (!file_exists('image/catalog/oc_' . $id . $file_name)) {
                        if (!copy($imageArray[0], 'image/catalog/oc_' . $id . $file_name)) {
                            $str = "not copy image ..\n";
                        }
                    }
                    $image = preg_replace('/\//', '\/', 'catalog/oc_'. $id . $file_name);
                } else {
                    foreach ($imageArray as $idImage => $imageValue) {
                        if ($idImage == 0) {
                            $file_name = basename($imageValue);
                            if (!file_exists('image/catalog/oc_' . $id . $file_name)) {
                                if (!copy($imageValue, 'image/catalog/oc_' . $id . $file_name)) {
                                    $str = "not copy image ..\n";
                                }
                            }
                            $image = preg_replace('/\//', '\/', 'catalog/oc_' . $id . $file_name);
                        } else {
                            $file_name = basename($imageValue);
                            if (!file_exists('image/catalog/oc_' . $id . $file_name)) {
                                if (!copy($imageValue, 'image/catalog/oc_' . $id . $file_name)) {
                                    $str = "not copy image ..\n";
                                }
                            }
                            $image1 = preg_replace('/\//', '\/', 'catalog/oc_' . $id . $file_name);
                            
                            $isProductImage = $this->model_oneboxsync_oneboxsync->isImage($id, $image1);
                            if ($isProductImage->num_rows == 0) {
                                $this->model_oneboxsync_oneboxsync->newImageValue($id, $image1);
                            }
                        }
                    }
                }
                $isCategoryId = $this->model_oneboxsync_oneboxsync->isCategoryId($id, $categoryid);
                if ($isCategoryId->num_rows == 0) {
                    $this->model_oneboxsync_oneboxsync->newProductCategory($id, $categoryid);
                }
                if ($brandname != '') {
                    $is_manufacturer = $this->model_oneboxsync_oneboxsync->isBrand($brandname);
                    if ($is_manufacturer->num_rows == 0) {
                        $this->model_oneboxsync_oneboxsync->newBrand($brandname);
                        $is_manufacturer = $this->model_oneboxsync_oneboxsync->isBrand($brandname);
                        $manufacturer = $is_manufacturer->row['manufacturer_id'];
                    } else {
                        $manufacturer = $is_manufacturer->row ['manufacturer_id'];
                    }
                } else {
                    $manufacturer = '';
                }
                
                if ($is_product1 == 0) {
                    //*****
                    //NEW PRODUCT
                    //*****
                    $json = $this->model_oneboxsync_oneboxsync->newProduct($id, $articul, $model, $quantity, $image, $manufacturer, $price, $avail, $name, $name_lang, $description, $availtext);
                } else {
                    //*****
                    //PRODUCT is IN BASE
                    //*****
                    $json = $this->model_oneboxsync_oneboxsync->updateProduct($id, $articul, $model, $quantity, $image, $manufacturer, $price, $avail, $name, $name_lang, $description, $availtext, $priceold, $priceoldend, $pricesCountArray);
                }
            }
        }
        if (isset($this->request->server['HTTP_ORIGIN'])) {
            $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
            $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            $this->response->addHeader('Access-Control-Max-Age: 1000');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    //----------------------------------------------------------------------------------------------------------------------
    
    //------------------------UPDATE FILTRE NAME----------------------------------------------------------------------------
    
    public function updateFiltreName ()
    {
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            $json_url = $this->request->post['json_url'];
            $languages = $this->request->post['languages'];
            $languages = json_decode(htmlspecialchars_decode($languages));
            
            $this->load->model('oneboxsync/oneboxsync');
            $onebox_json = file_get_contents($json_url);
            $onebox_content = json_decode($onebox_json);
            
            $is_ocfilter = $this->model_oneboxsync_oneboxsync->isOcfilter();
            
            $lang = array();
            if (count($languages)){
                foreach ($languages as $language){
                    $langId = $this->model_oneboxsync_oneboxsync->getLanguageId($language);
                    $lang[] = array(
                                    'id' => $langId[0]['language_id'],
                                    'name_code' => 'name_'.$language
                                    );
                }
            }
            
            foreach ($onebox_content as $value) {
                $name = $value->name;
                $id = $value->id;
                $name_lang = array();
                
                if (count($lang)){
                    foreach ($lang as $lan){
                        $name_lang[] = array (
                                              'lang_id' => $lan['id'],
                                              'name' => $value->$lan['name_code']
                                              );
                    }
                } else {
                    $name_lang[] = array (
                                          'lang_id' => '1',
                                          'name' => $name
                                          );
                }
                $json = $this->model_oneboxsync_oneboxsync->updateStandartFiltreName($id, $name, $name_lang);
                
                if ($is_ocfilter->num_rows){
                    
                    $is_filtr = $this->model_oneboxsync_oneboxsync->getFilterId($id);
                    if ($is_filtr->num_rows == 0) {
                        $json = $this->model_oneboxsync_oneboxsync->newFiltreName($id, $name, $name_lang);
                    } else {
                        $json = $this->model_oneboxsync_oneboxsync->updateFiltreName($id, $name, $name_lang);
                    }
                }
            }
        }
        if (isset($this->request->server['HTTP_ORIGIN'])) {
            $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
            $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            $this->response->addHeader('Access-Control-Max-Age: 1000');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    //----------------------------------------------------------------------------------------------------------------------
    
    //------------------------UPDATE FILTRE VALUE----------------------------------------------------------------------------
    
    public function updateFiltreProductValue () {
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            
            $json_url = $this->request->post['json_url'];
            $this->load->model('oneboxsync/oneboxsync');
            $onebox_json = file_get_contents($json_url);
            $onebox_content = json_decode($onebox_json);
            foreach ($onebox_content as $value) {
                $json = $this->model_oneboxsync_oneboxsync->updateFiltreProductValueModel($value->filterid, $value->productid, $value->filtervalue);
                
            }
        }
        if (isset($this->request->server['HTTP_ORIGIN'])) {
            $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
            $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            $this->response->addHeader('Access-Control-Max-Age: 1000');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    //----------------------------------------------------------------------------------------------------------------------
    
    //------------------------GET ORDERS----------------------------------------------------------------------------
    
    public function getOrderValue() {
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            $this->load->model('oneboxsync/oneboxsync');
            
            $data = $this->model_oneboxsync_oneboxsync->getOrderValue();
            $json = array();
            
            foreach ($data as $key => $value) {
                
                $order_products = $this->model_oneboxsync_oneboxsync->getOrderProduct($value['order_id']);
                $order_coupon = $this->model_oneboxsync_oneboxsync->getOrderCoupon($value['order_id']);
                $order_product_value = array();
                foreach ($order_products as $product) {
                    $order_products_options = $this->model_oneboxsync_oneboxsync->getOrderProductOptions($value['order_id'], $product['order_product_id']);
                    $product_options = array();
                    foreach ($order_products_options as $product_option){
                        $product_options[] = array(
                                                   'product_option_id'         => $product_option['product_option_id'],
                                                   'product_option_value_id'   => $product_option['product_option_value_id'],
                                                   'product_option_name'       => $product_option['name'],
                                                   'product_option_value'      => $product_option['value']
                                                   );
                    }
                    
                    $order_product_value[] = array(
                                                   'product_id'         => $product['product_id'],
                                                   'product_name'       => $product['name'],
                                                   'product_model'      => $product['model'],
                                                   'product_quantity'   => $product['quantity'],
                                                   'product_price'      => $product['price'],
                                                   'product_total'      => $product['total'],
                                                   'product_tax'        => $product['tax'],
                                                   'product_reward'     => $product['reward'],
                                                   'product_options'    => $product_options
                                                   
                                                   );
                }
                
                $json[] = array(
                                'order_id'               => $value['order_id'],
                                'customer_id'            => $value['customer_id'],
                                'firstname'              => $value['firstname'],
                                'lastname'               => $value['lastname'],
                                'email'                  => $value['email'],
                                'telephone'              => $value['telephone'],
                                'payment_firstname'      => $value['payment_firstname'],
                                'payment_lastname'       => $value['payment_lastname'],
                                'payment_company'        => $value['payment_company'],
                                'payment_address_1'      => $value['payment_address_1'],
                                'payment_address_2'      => $value['payment_address_2'],
                                'payment_city'           => $value['payment_city'],
                                'payment_postcode'       => $value['payment_postcode'],
                                'payment_country'        => $value['payment_country'],
                                'payment_country_id'     => $value['payment_country_id'],
                                'payment_zone'           => $value['payment_zone'],
                                'payment_zone_id'        => $value['payment_zone_id'],
                                'payment_address_format' => $value['payment_address_format'],
                                'payment_custom_field'   => $value['payment_custom_field'],
                                'payment_method'         => $value['payment_method'],
                                'payment_code'           => $value['payment_code'],
                                'shipping_firstname'     => $value['shipping_firstname'],
                                'shipping_lastname'      => $value['shipping_lastname'],
                                'shipping_company'       => $value['shipping_company'],
                                'shipping_address_1'     => $value['shipping_address_1'],
                                'shipping_address_2'     => $value['shipping_address_2'],
                                'shipping_city'          => $value['shipping_city'],
                                'shipping_postcode'      => $value['shipping_postcode'],
                                'shipping_country'       => $value['shipping_country'],
                                'shipping_country_id'    => $value['shipping_country_id'],
                                'shipping_zone'          => $value['shipping_zone'],
                                'shipping_zone_id'       => $value['shipping_zone_id'],
                                'shipping_address_format'=> $value['shipping_address_format'],
                                'shipping_custom_field'  => $value['shipping_custom_field'],
                                'shipping_method'        => $value['shipping_method'],
                                'shipping_code'          => $value['shipping_code'],
                                'comment'                => $value['comment'],
                                'total'                  => $value['total'],
                                'order_status_id'        => $value['order_status_id'],
                                'affiliate_id'           => $value['affiliate_id'],
                                'commission'             => $value['commission'],
                                'marketing_id'           => $value['marketing_id'],
                                'tracking'               => $value['tracking'],
                                'language_id'            => $value['language_id'],
                                'currency_id'            => $value['currency_id'],
                                'currency_code'          => $value['currency_code'],
                                'currency_value'         => $value['currency_value'],
                                'customer_ip'            => $value['ip'],
                                'date_added'             => $value['date_added'],
                                'coupon_code'            => $order_coupon['coupon_code'],
                                'coupon_name'            => $order_coupon['coupon_name'],
                                'product'                => $order_product_value
                                );
            }
        }
        
        if (isset($this->request->server['HTTP_ORIGIN'])) {
            $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
            $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            $this->response->addHeader('Access-Control-Max-Age: 1000');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    //----------------------------------------------------------------------------------------------------------------------
    
    //-----------------------------Update Order Status ---------------------------------------------------------------------
    
    public function updateOrder (){
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            $order_id = $this->request->post['order_id'];
            $status_id = $this->request->post['status_id'];
            $this->load->model('oneboxsync/oneboxsync');
            $json = $this->model_oneboxsync_oneboxsync->updateStatus($status_id, $order_id);
            if (isset($this->request->server['HTTP_ORIGIN'])) {
                $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
                $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
                $this->response->addHeader('Access-Control-Max-Age: 1000');
                $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
        
    }
    
    //----------------------------------------------------------------------------------------------------------------------
    
    
    
    
    public function category() {
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            $this->load->model('oneboxsync/oneboxsync');
            $data['category'] = $this->model_oneboxsync_oneboxsync->getCategory();
            $json = array();
            $n_json = 0;
            
            foreach ($data['category'] as $key => $value) {
                if ($n_json && $value['category_id'] == $json[$n_json - 1]['id']) {
                    $name_lang = "name_" . $value['code'];
                    $json[$n_json - 1][$name_lang] = $value['name'];
                    continue;
                }
                $json[$n_json]['id'] = $value['category_id'];
                $json[$n_json]['name'] = $value['name'];
                $name_lang = "name_" . $value['code'];
                $json[$n_json][$name_lang] = $value['name'];
                $json[$n_json]['parentid'] = $value['parent_id'];
                $json[$n_json]['code1c'] = '';
                $n_json++;
            }
        }
        
        if (isset($this->request->server['HTTP_ORIGIN'])) {
            $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
            $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            $this->response->addHeader('Access-Control-Max-Age: 1000');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function product() {
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            $this->load->model('oneboxsync/oneboxsync');
            $data['currency'] = $this->model_oneboxsync_oneboxsync->getCurrency();
            $data['products'] = $this->model_oneboxsync_oneboxsync->getProducts();
            
            $json = array();
            $n_json = 0;
            
            foreach ($data['products'] as $key => $value) {
                
                if ($n_json && $value['product_id'] == $json[$n_json - 1]['id']) {
                    $name_lang = "name_" . $value['code'];
                    $json[$n_json - 1][$name_lang] = $value['name'];
                    $newCatId = 0;
                    foreach ($json[$n_json - 1]['categoryid'] as $cat) {
                        if ($cat != $value['category_id']) {
                            $newCatId = 1;
                        } else {
                            $newCatId = 0;
                            break;
                        }
                    }
                    if ($newCatId == 1) {
                        $json[$n_json - 1]['categoryid'][] = $value['category_id'];
                    }
                    
                    $newImage = 0;
                    foreach ($json[$n_json - 1]['imageArray'] as $cat) {
                        if ($cat != $value['image']) {
                            $newImage = 1;
                        } else {
                            $newImage = 0;
                            break;
                        }
                    }
                    if ($newImage == 1) {
                        $json[$n_json - 1]['imageArray'][] = $value['image'];
                    }
                    continue;
                }
                
                $json[$n_json]['id'] = $value['product_id'];
                $json[$n_json]['name'] = $value['name'];
                $json[$n_json]['description'] = $value['description'];
                $json[$n_json]['model'] = '';
                $json[$n_json]['articul'] = $value['model'];
                $json[$n_json]['categoryid'] = array();
                $json[$n_json]['categoryid'][] = $value['category_id'];
                $json[$n_json]['imageArray'] = array();
                $json[$n_json]['imageArray'][] = $value['image_osn'];
                $json[$n_json]['imageArray'][] = $value['image'];
                $json[$n_json]['code1c'] = '';
                $json[$n_json]['avail'] = $value['status'];
                $json[$n_json]['availtext'] = '';
                $json[$n_json]['price'] = $value['price'];
                $json[$n_json]['currency'] = $data['currency'];
                $json[$n_json]['brandname'] = $value['manufacturername'];
                $json[$n_json]['seriesname'] = '';
                $filtre = $this->model_oneboxsync_oneboxsync->getProductToFiltr ($value['product_id']);
                $json[$n_json]['filtre'] = array();
                $n_filtre = 0;
                foreach ($filtre as $filtrvalue){
                    $json[$n_json]['filtre'][$n_filtre]['filterid'] = $filtrvalue['option_id'];
                    $json[$n_json]['filtre'][$n_filtre]['filtervalue'] = $filtrvalue['keyword'];
                    $n_filtre++;
                }
                $n_json++;
            }
        }
        if (isset($this->request->server['HTTP_ORIGIN'])) {
            $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
            $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            $this->response->addHeader('Access-Control-Max-Age: 1000');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    
    public function statusname(){
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            $this->load->model('oneboxsync/oneboxsync');
            $data['status_name'] = $this->model_oneboxsync_oneboxsync->getStatusName();
            $json = array();
            $n_json = 0;
            
            foreach ($data['status_name'] as $key => $value) {
                if ($n_json && $value['order_status_id'] == $json[$n_json - 1]['order_status_id']) {
                    $name_lang = "name_" . $value['code'];
                    $json[$n_json - 1][$name_lang] = $value['name'];
                    continue;
                }
                $json[$n_json]['order_status_id'] = $value['order_status_id'];
                $json[$n_json]['name'] = $value['name'];
                $name_lang = "name_" . $value['code'];
                $json[$n_json][$name_lang] = $value['name'];
                $n_json++;
            }
        }
        
        if (isset($this->request->server['HTTP_ORIGIN'])) {
            $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
            $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            $this->response->addHeader('Access-Control-Max-Age: 1000');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function order() {
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            $this->load->model('oneboxsync/oneboxsync');
            $json['status'] = $this->model_oneboxsync_oneboxsync->getStatus();
            $json = $this->model_oneboxsync_oneboxsync->getStatusLimit();
        }
        
        if (isset($this->request->server['HTTP_ORIGIN'])) {
            $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
            $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            $this->response->addHeader('Access-Control-Max-Age: 1000');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function getStatusByOrder() {
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            $order_id = $this->request->post['order_id'];
            $this->load->model('oneboxsync/oneboxsync');
            $json = $this->model_oneboxsync_oneboxsync->getStatusByOrder($order_id);
            if (isset($this->request->server['HTTP_ORIGIN'])) {
                $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
                $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
                $this->response->addHeader('Access-Control-Max-Age: 1000');
                $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
            }
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function filtres (){
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            $this->load->model('oneboxsync/oneboxsync');
            $data['filtre'] = $this->model_oneboxsync_oneboxsync->getFiltre();
            $json = array();
            $n_json = 0;
            
            foreach ($data['filtre'] as $key => $value) {
                if ($n_json && $value['option_id'] == $json[$n_json - 1]['id']) {
                    $name_lang = "name_" . $value['code'];
                    $json[$n_json - 1][$name_lang] = $value['name'];
                    continue;
                }
                $json[$n_json]['id'] = $value['option_id'];
                $json[$n_json]['name'] = $value['name'];
                $name_lang = "name_" . $value['code'];
                $json[$n_json][$name_lang] = $value['name'];
                $n_json++;
            }
        }
        
        if (isset($this->request->server['HTTP_ORIGIN'])) {
            $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
            $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            $this->response->addHeader('Access-Control-Max-Age: 1000');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
        
    }
    
    public function filtrevalue() {
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            
            $this->load->model('oneboxsync/oneboxsync');
            
            $data['filtre_value'] = $this->model_oneboxsync_oneboxsync->getFiltrValue();
            $json = array();
            $n_json = 0;
            
            foreach ($data['filtre_value'] as $key => $value) {
                if ($n_json && $value['option_id'] == $json[$n_json - 1]['filterid']) {
                    $name_lang = "name_" . $value['code'];
                    $json[$n_json - 1][$name_lang] = $value['name'];
                    continue;
                }
                $json[$n_json]['categoryid'] = $value['category_id'];
                $json[$n_json]['filterid'] = $value['option_id'];
                $name_lang = "name_" . $value['code'];
                $json[$n_json][$name_lang] = $value['name'];
                $n_json++;
            }
        }
        
        if (isset($this->request->server['HTTP_ORIGIN'])) {
            $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
            $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            $this->response->addHeader('Access-Control-Max-Age: 1000');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    
    
    
    
    public function updateOrderValue () {
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            $json_url = $this->request->post['json_url'];
            $this->load->model('oneboxsync/oneboxsync');
            $onebox_json = file_get_contents($json_url);
            $onebox_content = json_decode($onebox_json);
            foreach ($onebox_content as $value) {
                $is_order = $this->model_oneboxsync_oneboxsync->isOrder ($value->order_id);
                if ($is_order->num_rows == 0) {
                    $json = $this->model_oneboxsync_oneboxsync->updateOrderValue ($value->order_id, $value->firstname, $value->lastname, $value->email, $value->telephone, $value->payment_firstname, $value->payment_lastname, $value->payment_company, $value->payment_address_1, $value->payment_address_2, $value->payment_city, $value->payment_postcode, $value->payment_country, $value->payment_country_id, $value->payment_zone, $value->payment_zone_id, $value->payment_address_format, $value->payment_custom_field, $value->payment_method, $value->payment_code, $value->shipping_firstname, $value->shipping_lastname, $value->shipping_company, $value->shipping_address_1, $value->shipping_address_2, $value->shipping_city, $value->shipping_postcode, $value->shipping_country, $value->shipping_country_id, $value->shipping_zone, $value->shipping_zone_id, $value->shipping_address_format, $value->shipping_custom_field, $value->shipping_method, $value->shipping_code, $value->comment, $value->total, $value->order_status_id, $value->affiliate_id, $value->commission, $value->marketing_id, $value->tracking, $value->language_id, $value->currency_id, $value->currency_code, $value->currency_value, $value->customer_ip, $value->date_added, $value->product_id, $value->product_name, $value->product_model, $value->product_quantity, $value->product_price, $value->product_total, $value->product_tax);
                    $this->model_oneboxsync_oneboxsync->updateOrderHistoryStatus ($value->order_id, $value->order_status_id);
                } else {
                    $json = $this->model_oneboxsync_oneboxsync->updateOrderValue ($value->order_id, $value->firstname, $value->lastname, $value->email, $value->telephone, $value->payment_firstname, $value->payment_lastname, $value->payment_company, $value->payment_address_1, $value->payment_address_2, $value->payment_city, $value->payment_postcode, $value->payment_country, $value->payment_country_id, $value->payment_zone, $value->payment_zone_id, $value->payment_address_format, $value->payment_custom_field, $value->payment_method, $value->payment_code, $value->shipping_firstname, $value->shipping_lastname, $value->shipping_company, $value->shipping_address_1, $value->shipping_address_2, $value->shipping_city, $value->shipping_postcode, $value->shipping_country, $value->shipping_country_id, $value->shipping_zone, $value->shipping_zone_id, $value->shipping_address_format, $value->shipping_custom_field, $value->shipping_method, $value->shipping_code, $value->comment, $value->total, $value->order_status_id, $value->affiliate_id, $value->commission, $value->marketing_id, $value->tracking, $value->language_id, $value->currency_id, $value->currency_code, $value->currency_value, $value->customer_ip, $value->date_added, $value->product_id, $value->product_name, $value->product_model, $value->product_quantity, $value->product_price, $value->product_total, $value->product_tax);
                    $is_orderHistoryStatus = $this->model_oneboxsync_oneboxsync->getOrderHistoryStatus ($value->order_id);
                    if ($is_orderHistoryStatus->row['order_status_id'] != $value->order_status_id) {
                        $this->model_oneboxsync_oneboxsync->updateOrderHistoryStatus ($value->order_id, $value->order_status_id);
                    }
                }
            }
        }
        if (isset($this->request->server['HTTP_ORIGIN'])) {
            $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
            $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            $this->response->addHeader('Access-Control-Max-Age: 1000');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function filtreproductvalue() {
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            $this->load->model('oneboxsync/oneboxsync');
            
            $json = $this->model_oneboxsync_oneboxsync->getFiltrProduct();
            
        }
        if (isset($this->request->server['HTTP_ORIGIN'])) {
            $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
            $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            $this->response->addHeader('Access-Control-Max-Age: 1000');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
    
    public function updateProductImage() {
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            $json_url = $this->request->post['json_url'];
            
            $this->load->model('oneboxsync/oneboxsync');
            $onebox_json = file_get_contents($json_url);
            
            $onebox_content = json_decode($onebox_json);
            foreach ($onebox_content as $value) {
                $id = $value->id;
                $nameimg = @$value->nameimg;
                
                $imageArray = $value->imageArray;
                
                $nImage = count($imageArray);
                $image = '';
                
                if ($nImage == 0) {
                    $image = '';
                } elseif ($nImage == 1) {
                    $file_name = basename($imageArray[0]);
                    $fileSearch = 'oc_' . $id . $file_name;
                    if ($nameimg) {
                        $fileSearch = $file_name;
                    }
                    if (!file_exists('image/catalog/' . $fileSearch)) {
                        if (!copy($imageArray[0], 'image/catalog/' . $fileSearch)) {
                            $str = "not copy image ..\n";
                        }
                    }
                    $image = preg_replace('/\//', '\/', 'catalog/' . $fileSearch);
                } else {
                    foreach ($imageArray as $idImage => $imageValue) {
                        if ($idImage == 0) {
                            $file_name = basename($imageValue);
                            $fileSearch = 'oc_' . $id . $file_name;
                            if ($nameimg) {
                                $fileSearch = $file_name;
                            }
                            if (!file_exists('image/catalog/' . $fileSearch)) {
                                if (!copy($imageValue, 'image/catalog/' . $fileSearch)) {
                                    $str = "not copy image ..\n";
                                }
                            }
                            $image = preg_replace('/\//', '\/', 'catalog/' . $fileSearch);
                        } else {
                            $file_name = basename($imageValue);
                            $fileSearch = 'oc_' . $id . $file_name;
                            if ($nameimg) {
                                $fileSearch = $file_name;
                            }
                            if (!file_exists('image/catalog/' . $fileSearch)) {
                                if (!copy($imageValue, 'image/catalog/' . $fileSearch)) {
                                    $str = "not copy image ..\n";
                                }
                            }
                            $image1 = preg_replace('/\//', '\/', 'catalog/' . $fileSearch);
                            
                            $isProductImage = $this->model_oneboxsync_oneboxsync->isImage($id, $image1);
                            if ($isProductImage->num_rows == 0) {
                                $this->model_oneboxsync_oneboxsync->newImageValue($id, $image1);
                            }
                        }
                    }
                }
                
                if ($image) {
                    $this->model_oneboxsync_oneboxsync->updateProductImage($id, $image);
                }
            }
        }
    }
    
    public function updateCategoryImage() {
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            $json_url = $this->request->post['json_url'];
            
            $this->load->model('oneboxsync/oneboxsync');
            $onebox_json = file_get_contents($json_url);
            
            $onebox_content = json_decode($onebox_json);
            foreach ($onebox_content as $value) {
                $id = $value->id;
                
                $image = $value->image;
                
                $file_name = basename($image);
                if (!file_exists('image/catalog/oc_' . $id . $file_name)) {
                    if (!copy($image, 'image/catalog/oc_' . $id . $file_name)) {
                        $str = "not copy image ..\n";
                    }
                }
                $image = preg_replace('/\//', '\/', 'catalog/oc_' . $id . $file_name);
                
                if ($image) {
                    $this->model_oneboxsync_oneboxsync->updateCategoryImage($id, $image);
                }
            }
        }
    }

    public function updateBrandImage() {
        $json_url = $this->request->post['json_url'];

        $this->load->model('oneboxsync/oneboxsync');
        $onebox_json = file_get_contents($json_url);

        $onebox_content = json_decode($onebox_json);
        foreach ($onebox_content as $value) {
            $id = $value->id;

            $image = $value->image;

            $file_name = basename($image);
            if (!file_exists('image/catalog/oc_' . $id . $file_name)) {
                if (!copy($image, 'image/catalog/oc_' . $id . $file_name)) {
                    $str = "not copy image ..\n";
                }
            }
            $image = preg_replace('/\//', '\/', 'catalog/oc_' . $id . $file_name);

            if ($image) {
                $this->model_oneboxsync_oneboxsync->updateBrandImage($id, $image);
            }
        }
    }
    
    public function addFiles (){
        $this->getApiFromToken();
        if (!isset($this->session->data['api_id'])) {
            $json['error'] = 'error_permission';
        } else {
            $json_url = $this->request->post['json_url'];
            $name = $this->request->post['name'];
            $onebox_json = "[" . file_get_contents($json_url) . "]";
            $onebox_json = preg_replace('/\}\s\{/', '},{', $onebox_json);
            $name = 'catalog/onebox/' . $name;
            $fp = fopen($name, 'w');
            fwrite($fp, $onebox_json);
            fclose($fp);
            $json['error'] = 'no error';
        }
        if (isset($this->request->server['HTTP_ORIGIN'])) {
            $this->response->addHeader('Access-Control-Allow-Origin: ' . $this->request->server['HTTP_ORIGIN']);
            $this->response->addHeader('Access-Control-Allow-Methods: GET, PUT, POST, DELETE, OPTIONS');
            $this->response->addHeader('Access-Control-Max-Age: 1000');
            $this->response->addHeader('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        }
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode($json));
    }
}
?>
