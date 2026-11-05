<?php

class ControllerExtensionFeedUnixmlFacebook extends Controller
{
    public function index()
    {

        $feed = 'facebook';
        $controller = str_replace($feed, 'startup', $this->request->get['route']);
        $startup = $this->load->controller($controller, array('feed' => $feed));
        $xml = false;

        //XML_body
        //headerXML
        $xml = '<?xml version="1.0"?>';
        $xml .= '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:g="http://base.google.com/ns/1.0">';
        $xml .= '<title>' . $startup['name'] . '</title>';
        $xml .= '<link>' . HTTPS_SERVER . '</link>';
        $xml .= '<updated>' . date('Y-m-d H:i', time()) . '</updated>';

        $xml = $this->unixml->exportToXml($startup, $xml, "start");
        //headerXML

        //generateXML
        for ($startup['iteration'] = 0; 1; $startup['iteration']++) {


            $controller_data = $this->load->controller($controller, $startup);


            $startup['stat'] = $controller_data['data']['stat'];

            if ($controller_data['products'] || (isset($controller_data['products_exception_use_in_feed']) && $controller_data['products_exception_use_in_feed'])) {
                if ($controller_data['products']) {
                    foreach ($controller_data['products'] as $product_id => $product) {

                        $title = (isset($product['name_for_feed']) && $product['name_for_feed']) ? $product['name_for_feed'] : $product['name'];

                        $pattern = '/\bPod\b/';
                        // Заменяем найденное слово "Pod" на пустую строку
                        $result = preg_replace($pattern, '', $title);
                        // Убираем лишние пробелы, которые могли образоваться после замены
                        $result = preg_replace('/\s+/', ' ', $result);
                        // Убираем пробелы в начале и конце строки
                        $title = trim($result);

                        $description = (isset($product['description_for_feed']) && $product['description_for_feed']) ? $product['description_for_feed'] : 'Відкрийте для себе новий рівень задоволення разом з цією системою, яка поєднує в собі найсучасніші технології та бездоганний дизайн!';

                        $xml .= '<entry>';
                        $xml .= '<g:title>' . $title . '</g:title>';
                        $xml .= '<g:link>' . $product['url'] . '</g:link>';
                        $xml .= '<g:id>' . $product_id . '</g:id>';
                        if (isset($product['group_id'])) {
                            $xml .= '<g:item_group_id>' . $product['group_id'] . '</g:item_group_id>';
                        }
                        if ($product['special']) {
                            $xml .= '<g:price>' . $product['price'] . ' ' . $startup['currency_xml'] . '</g:price>';
                            $xml .= '<g:sale_price>' . $product['special'] . ' ' . $startup['currency_xml'] . '</g:sale_price>';
                            $xml .= '<g:sale_price_effective_date>' . (($product['special_start'] == '0000-00-00') ? date("Y-m-d", strtotime("yesterday")) : $product['special_start']) . '/' . (($product['special_end'] == '0000-00-00') ? date("Y-m-d", strtotime("tomorrow")) : $product['special_end']) . '</g:sale_price_effective_date>';
                            //$xml .= '<g:sale_price_effective_date>' . (($product['special_start'] == '0000-00-00')?date("Y-m-d", strtotime("-1 month")):$product['special_start']) . '/' . (($product['special_end'] == '0000-00-00')?date("Y-m-d", strtotime("+1 month")):$product['special_end']) . '</g:sale_price_effective_date>';
                        } else {
                            $xml .= '<g:price>' . $product['price'] . ' ' . $startup['currency_xml'] . '</g:price>';
                        }

                        $xml .= '<g:description><![CDATA[' . $description . ']]></g:description>';

                        if (isset($startup['categories_xml'][$product['category_id']]['market_id']) && $startup['categories_xml'][$product['category_id']]['market_id']) {
                            $xml .= '<g:google_product_category>' . $startup['categories_xml'][$product['category_id']]['market_id'] . '</g:google_product_category>';
                        }
                        $xml .= '<g:brand>' . $product['manufacturer'] . '</g:brand>';
                        $xml .= '<g:condition>new</g:condition>';


                        if ($product['images']) {
                            $xml .= '<g:image_link>' . $product['images'][0] . '</g:image_link>';
                            if (count($product['images']) > 1) {
                                unset($product['images'][0]);
                                foreach ($product['images'] as $image) {
                                    $xml .= '<g:additional_image_link>' . $image . '</g:additional_image_link>';
                                }
                            }
                        } else {
                            $xml .= '<g:image_link>https://getvape.com.ua/image/catalog/fb_feeb/' . $product_id . '.png</g:image_link>';
                        }
                        $xml .= '<g:availability>' . ($product['quantity'] ? 'in stock' : 'out of stock') . '</g:availability>';
                        foreach ($product['attributes_full'] as $attribute) {
                            if (isset($attribute['decode']) && $attribute['decode']) {
                                $attribute['text'] = html_entity_decode($attribute['text'], ENT_QUOTES, 'UTF-8');
                            }
                            $xml .= '<' . $attribute['name'] . '>' . $attribute['text'] . '</' . $attribute['end'] . '>';
                        }
                        foreach ($product['attributes'] as $attribute) {
                            $xml .= '<g:product_detail>';
                            $xml .= '<g:attribute_name>' . $attribute['name'] . '</g:attribute_name>';
                            $xml .= '<g:attribute_value>' . $attribute['text'] . '</g:attribute_value>';
                            $xml .= '</g:product_detail>';
                        }
                        if ($startup['delivery_cost']) {
                            $xml .= '<g:shipping><g:service>' . $startup['delivery_time'] . '</g:service><g:price>' . $startup['delivery_cost'] . '</g:price></g:shipping>';
                        }
                        $xml .= '</entry>';
                    }
                }

                if (isset($controller_data['products_exception_use_in_feed']) && $controller_data['products_exception_use_in_feed']) {

                    foreach ($controller_data['products_exception_use_in_feed'] as $product_id => $product) {

                        $title = $product['name'];

                        $pattern = '/\bPod\b/';
                        // Заменяем найденное слово "Pod" на пустую строку
                        $result = preg_replace($pattern, '', $title);
                        // Убираем лишние пробелы, которые могли образоваться после замены
                        $result = preg_replace('/\s+/', ' ', $result);
                        // Убираем пробелы в начале и конце строки
                        $title = trim($result);

                        $description = 'Придбати ' . $title . ' ⚡️ в інтернет-магазині Vapors. Відгуки, опис, характеристики ✓ Швидка доставка ✓ Оригінальні брендові пристрої ✓ Телефонуйте за ☎️: +38 (063) 071-44-86';

                        $xml .= '<entry>';
                        $xml .= '<g:title>' . $title . '</g:title>';
                        $xml .= '<g:link>' . $product['url'] . '</g:link>';
                        $xml .= '<g:id>' . $product_id . '</g:id>';
                        if (isset($product['group_id'])) {
                            $xml .= '<g:item_group_id>' . $product['group_id'] . '</g:item_group_id>';
                        }
                        if ($product['special']) {
                            $xml .= '<g:price>' . $product['price'] . ' ' . $startup['currency_xml'] . '</g:price>';
                            $xml .= '<g:sale_price>' . $product['special'] . ' ' . $startup['currency_xml'] . '</g:sale_price>';
                            $xml .= '<g:sale_price_effective_date>' . (($product['special_start'] == '0000-00-00') ? date("Y-m-d", strtotime("yesterday")) : $product['special_start']) . '/' . (($product['special_end'] == '0000-00-00') ? date("Y-m-d", strtotime("tomorrow")) : $product['special_end']) . '</g:sale_price_effective_date>';
                            //$xml .= '<g:sale_price_effective_date>' . (($product['special_start'] == '0000-00-00')?date("Y-m-d", strtotime("-1 month")):$product['special_start']) . '/' . (($product['special_end'] == '0000-00-00')?date("Y-m-d", strtotime("+1 month")):$product['special_end']) . '</g:sale_price_effective_date>';
                        } else {
                            $xml .= '<g:price>' . $product['price'] . ' ' . $startup['currency_xml'] . '</g:price>';
                        }

                        $xml .= '<g:description><![CDATA[' . $description . ']]></g:description>';

                        if (isset($startup['categories_xml'][$product['category_id']]['market_id']) && $startup['categories_xml'][$product['category_id']]['market_id']) {
                            $xml .= '<g:google_product_category>' . $startup['categories_xml'][$product['category_id']]['market_id'] . '</g:google_product_category>';
                        }
                        $xml .= '<g:brand>' . $product['manufacturer'] . '</g:brand>';
                        $xml .= '<g:condition>new</g:condition>';

                        if ($product['image']) {
                            $xml .= '<g:image_link>' . $product['image'] . '</g:image_link>';

                            if ($product['images']) {
                                foreach ($product['images'] as $image) {
                                    $xml .= '<g:additional_image_link>' . $image . '</g:additional_image_link>';
                                }
                            }
                        } else {
                            $xml .= '<g:image_link>https://getvape.com.ua/image/catalog/fb_feeb/' . $product_id . '.png</g:image_link>';
                        }

                        $xml .= '<g:availability>' . ($product['quantity'] ? 'in stock' : 'out of stock') . '</g:availability>';
                        foreach ($product['attributes_full'] as $attribute) {
                            if (isset($attribute['decode']) && $attribute['decode']) {
                                $attribute['text'] = html_entity_decode($attribute['text'], ENT_QUOTES, 'UTF-8');
                            }
                            $xml .= '<' . $attribute['name'] . '>' . $attribute['text'] . '</' . $attribute['end'] . '>';
                        }
                        foreach ($product['attributes'] as $attribute) {
                            $xml .= '<g:product_detail>';
                            $xml .= '<g:attribute_name>' . $attribute['name'] . '</g:attribute_name>';
                            $xml .= '<g:attribute_value>' . $attribute['text'] . '</g:attribute_value>';
                            $xml .= '</g:product_detail>';
                        }
                        if ($startup['delivery_cost']) {
                            $xml .= '<g:shipping><g:service>' . $startup['delivery_time'] . '</g:service><g:price>' . $startup['delivery_cost'] . '</g:price></g:shipping>';
                        }
                        $xml .= '</entry>';
                    }

                }
            } else {
                break;
            }

            $xml = $this->unixml->exportToXml($controller_data['data'], $xml);
        }
        //generateXML

        //footerXML
        $xml .= '</feed>';

        $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
        //footerXML
        //XML_body

    }
}
