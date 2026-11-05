<?php
class ControllerExtensionFeedUnixmlRozetka extends Controller {
  public function index() {

    $feed = 'rozetka';
    $controller = str_replace($feed, 'startup', $this->request->get['route']);
    $startup = $this->load->controller($controller, array('feed'=>$feed));
    $xml = false;

    //XML_body
      //headerXML
      $xml .= '<?xml version="1.0" encoding="UTF-8"?>';
      $xml .= '<!DOCTYPE yml_catalog SYSTEM "shops.dtd"> ';
      $xml .= '<yml_catalog date="' . date('Y-m-d H:i', time()) . '">';
      $xml .= '<shop>';
      $xml .= '<url>' . HTTPS_SERVER . '</url>';
      $xml .= '<name>' . $this->config->get('config_name') . '</name>';
      $xml .= '<company>' . $startup['name'] . '</company>';
      $xml .= '<phone>' . $this->config->get('config_telephone') . '</phone>';
      $xml .= '<currencies>';
      $xml .= '<currency id="' . $startup['currency_xml'] . '" rate="1"></currency>';
      $xml .= '</currencies>';

      if($startup['categories_xml']) {
        $xml .= '<categories>';
        foreach($startup['categories_xml'] as $category) {
          if($category['parent_id']){
            $xml .= '<category id="' . $category['category_id'] .'" ' . (($category['market_id'])?'rz_id="' . $category['market_id'] . '"':'') . ' parentId="' . $category['parent_id'] . '">' . $category['name'] .'</category>';
          } else{
            $xml .= '<category id="' . $category['category_id'] .'" ' . (($category['market_id'])?'rz_id="' . $category['market_id'] . '"':'') . '>' . $category['name'] .'</category>';
          }
        }
        $xml .= '</categories>';
      }

      $xml .= '<offers>';

      $xml = $this->unixml->exportToXml($startup, $xml, "start");
      //headerXML

      //generateXML
        for($startup['iteration'] = 0; 1; $startup['iteration']++){

          $controller_data = $this->load->controller($controller, $startup);
          $startup['stat'] = $controller_data['data']['stat'];

          if($controller_data['products']){

            foreach($controller_data['products'] as $product_id => $product){

              $group_id = '';
              if(isset($product['group_id'])){
                $group_id = 'group_id="' . $product['group_id'] . '"';
              }
              $xml .= '<offer id="' . $product_id . '" available="' . ($product['stock']?'true':'false') .'" ' . $group_id . '>';
              $xml .= '<name>' . $product['name'] .  '</name>';
              if(isset($product['langdata'])){
                foreach($product['langdata'] as $langdata){
                  $xml .= '<name_ua>' . $langdata['name'] .  '</name_ua>';
                }
              }
              $xml .= '<vendorCode>' . $product['model'] .  '</vendorCode>';
              $xml .= '<url>' . $product['url'] .  '</url>';
              $xml .= '<currencyId>' . $startup['currency_xml'] . '</currencyId>';
              $xml .= '<categoryId>' . $product['category_id'] .  '</categoryId>';

              if($product['special']){
                $xml .= '<price>' . $product['special'] .  '</price>';
                $xml .= '<price_old>' . $product['price'] .  '</price_old>';
              }else{
                $xml .= '<price>' . $product['price'] .  '</price>';
              }

              if($product['image']){
                $xml .= '<picture>' . $product['image'] .  '</picture>';
              }

              if($product['images']){
                foreach($product['images'] as $image){
                  $xml .= '<picture>' . $image .  '</picture>';
                }
              }

             if (!empty($product['manufacturer'])) {
    $xml .= '<vendor>' . $product['manufacturer'] . '</vendor>';
} else {
    $xml .= '<vendor>Без бренду</vendor>';
}



$description_clean = preg_replace('/[\x{1F600}-\x{1F64F}]|[\x{1F300}-\x{1F5FF}]|[\x{1F680}-\x{1F6FF}]|[\x{2600}-\x{26FF}]|[\x{2700}-\x{27BF}]|[\x{1F900}-\x{1F9FF}]|[\x{1FA70}-\x{1FAFF}]/u', '', $product['description']);

$xml .= '<description><![CDATA[' . $description_clean . ']]></description>';

              if(isset($product['langdata'])){
                foreach($product['langdata'] as $langdata){
                  $xml .= '<description_ua><![CDATA[' . $langdata['description'] .  ']]></description_ua>';
                }
              }
              $xml .= '<quantity_in_stock>' . $product['quantity'] .  '</quantity_in_stock>';
              foreach($product['attributes_full'] as $attribute){
                if(isset($attribute['decode']) && $attribute['decode']){
                  $attribute['text'] = html_entity_decode($attribute['text'], ENT_QUOTES, 'UTF-8');
                }
                $xml .= '<' . $attribute['name'] . '>' . $attribute['text'] .  '</' . $attribute['end'] . '>';
              }
              if($product['attributes']){
                foreach($product['attributes'] as $attribute){
                  $xml .= '<param name="' . $attribute['name'] . '"' . str_replace(array('paramcode', 'valuecode'), array('paramid', 'valueid'), $attribute['additional']) . '>' . $attribute['text'] .  '</param>';
                }
              }
              $xml .= '</offer>';
            }
          } else {
            break;
          }

          $xml = $this->unixml->exportToXml($controller_data['data'], $xml);
        }
      //generateXML

      //footerXML
      $xml .= '</offers>';
      $xml .= '</shop>';
      $xml .= '</yml_catalog>';

      $this->unixml->exportToXml($controller_data['data'], $xml, "finish");
      //footerXML
    //XML_body

  }
}
