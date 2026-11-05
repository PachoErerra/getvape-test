<?php

set_time_limit(0);
$languages = array('ru');

$filename = __DIR__ . '/category.json';
$track = 'catalog/onebox/';
$host = 'https://getvape.com.ua/';
$apiKey = 'Default';
$userName = 'ujVNs9xUXfUJASgV8jwvexQOntNptMBMu9Y0e8qxI89TZcV4xCP2TNqXpKjcKHFWNCbDbJBQAezK0GXmzj4LwGFdX8WqcV6cvkcmhHeHhpcWlbY3owtXsoZZyEmn0lLpwjcnSRwKQoZPlgDmwnT4TlbVmPW8mNBTxZu3sXTn3OJtBvUveJ5CwLISd5ws5EMS5DnPf2Y7haIwkwmJgvOl7qgcbM0KLNznGaSjb4tGs3s8c3Tc3CMUvEfcx1pX4Blc';

$curl = curl_init( $host .'index.php?route=api/login' );
$post = array (
    'key' => $apiKey,
    'username' => $userName
);
curl_setopt_array( $curl, array(CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_POSTFIELDS => $post ) );
$response = json_decode(curl_exec( $curl ));
curl_close($curl);

if (isset($response->token)) {
    $token = $response->token;
} else if (isset($response->api_token)) {
    $token = $response->api_token;
}

if (file_exists(__DIR__ . '/images.json')) {
    $filename = 'images.json';
    $json_url = $track . $filename;
    $languages = json_encode($languages);
    $post = array ('json_url' => $json_url, 'languages' => $languages);
    $curl = curl_init( $host .'index.php?route=api/oneboxsync/updateProductImage/&token=' . $token . '&api_token=' . $token);
    curl_setopt_array( $curl, array(CURLOPT_RETURNTRANSFER=> TRUE, CURLOPT_POSTFIELDS => $post ) );
    $response = (curl_exec( $curl ));
    curl_close($curl);
    print_r ($response . "</br>");
    $filename1 = __DIR__ . '/' . $filename;

    $log = fopen(__DIR__ . '/log.txt', 'a');
    $mytext = date(DATE_RFC2822) . ": \r\n";
    fwrite($log,  'UPDATE IMAGE: ' . $response . "\r\n");
    fclose($log);
    unlink($filename1);
}

if (file_exists(__DIR__ . '/imagesBrand.json')) {
    $filename = 'imagesBrand.json';
    $json_url = $track . $filename;
    $post = array ('json_url' => $json_url);
    $curl = curl_init( $host .'index.php?route=feed/rest_api/updateBrandImage/&key=' . $token . '&api_token=' . $token);
    curl_setopt_array( $curl, array(CURLOPT_RETURNTRANSFER=> TRUE, CURLOPT_POSTFIELDS => $post ) );
    $response = (curl_exec( $curl ));
    curl_close($curl);

    print_r ($response . "</br>");
    $filename1 = __DIR__ . '/' . $filename;
    fwrite($log,  'UPDATE IMAGE BRAND: ' . $response . "\r\n");
    unlink($filename1);
}

if (file_exists(__DIR__ . '/categoryimages.json')) {
    $filename = 'categoryimages.json';
    $json_url = $track . $filename;
    $languages = json_encode($languages);
    $post = array ('json_url' => $json_url, 'languages' => $languages);
    $curl = curl_init( $host .'index.php?route=api/oneboxsync/updateCategoryImage/&token=' . $token . '&api_token=' . $token);
    curl_setopt_array( $curl, array(CURLOPT_RETURNTRANSFER=> TRUE, CURLOPT_POSTFIELDS => $post ) );
    $response = (curl_exec( $curl ));
    curl_close($curl);
    print_r ($response . "</br>");
    $filename1 = __DIR__ . '/' . $filename;
    $log = fopen(__DIR__ . '/log.txt', 'a');
    $mytext = date(DATE_RFC2822) . ": \r\n";
    fwrite($log,  'UPDATE CATEGORY IMAGE: ' . $response . "\r\n");
    fclose($log);
    unlink($filename1);
}

$filename = __DIR__ . '/category.json';
if (file_exists($filename)) {

    $log = fopen(__DIR__ . '/log.txt', 'a');
    $mytext = date(DATE_RFC2822) . ": \r\n";
    fwrite($log, $mytext);

    $curl = curl_init( $host .'index.php?route=api/login/' );
    $post = array ('key' => $apiKey);
    curl_setopt_array( $curl, array(CURLOPT_RETURNTRANSFER => TRUE, CURLOPT_POSTFIELDS => $post ) );
    $response = json_decode(curl_exec( $curl ));
    curl_close($curl);
    if (isset($response->token)) {
        $token = $response->token;
    }
    //  print_r ($response);
    fwrite($log,   $response );

//***********************************************
//    echo('UPDATE CATEGORY:');
//***********************************************
    $filename = 'category.json';
    $json_url = $track . $filename;
    $languages = json_encode($languages);
    $post = array ('json_url' => $json_url, 'languages' => $languages);
    $curl = curl_init( $host .'index.php?route=api/oneboxsync/updateCategory/&token=' . $token . '&api_token=' . $token);
    curl_setopt_array( $curl, array(CURLOPT_RETURNTRANSFER=> TRUE, CURLOPT_POSTFIELDS => $post ) );
    $response = (curl_exec( $curl ));
    curl_close($curl);
    //   print_r ($response . "</br>");
    $filename1 = __DIR__ . '/' . $filename;
    fwrite($log,  'UPDATE CATEGORY: ' . $response . "\r\n");
    unlink($filename1);


//**********************************************
    //   echo('UPDATE PRODUCT:');
//**********************************************
    $filename = 'product.json';
    $json_url = $track . $filename;
    $languages = json_encode($languages);
    $post = array ('json_url' => $json_url, 'languages' => $languages);
    $curl = curl_init( $host .'index.php?route=api/oneboxsync/updateProduct/&token=' . $token . '&api_token=' . $token);
    curl_setopt_array( $curl, array(CURLOPT_RETURNTRANSFER=> TRUE, CURLOPT_POSTFIELDS => $post ) );
    $response = json_decode(curl_exec( $curl ));
    curl_close($curl);
//    print_r ($response . "</br>");
    $filename1 = __DIR__ . '/' . $filename;
    fwrite($log, 'UPDATE PRODUCT: ' . $response . "\r\n");
    unlink($filename1);


    //**********************************************
    //   echo('UPDATE STATUS:');
    //***********************************************

//*********************************************************
//NOVY STATUS - ORDER!!!!
    /*
        $order_id = '1';
        $status_id = '3';

        $post = array ('order_id' => $order_id, 'status_id' => $status_id);
        $curl = curl_init( $host .'index.php?route=api/oneboxsync/updateOrder/&token=' . $token );
        curl_setopt_array( $curl, array(CURLOPT_RETURNTRANSFER=> TRUE, CURLOPT_POSTFIELDS => $post ) );
        $response = json_decode(curl_exec( $curl ));
        curl_close($curl);
        print_r ($response);
    */
//*******************************************


//*******************************************
//   echo('UPDATE FIlTRENAME:');
//********************************************
    $filename = 'filtername.json';
    $json_url = $track . $filename;
    $languages = json_encode($languages);
    $post = array ('json_url' => $json_url, 'languages' => $languages);
    $curl = curl_init( $host .'index.php?route=api/oneboxsync/updateFiltreName/&token=' . $token . '&api_token=' . $token);
    curl_setopt_array( $curl, array(CURLOPT_RETURNTRANSFER=> TRUE, CURLOPT_POSTFIELDS => $post ) );
    $response = json_decode(curl_exec( $curl ));
    curl_close($curl);
//    print_r ($response . "</br>");
    $filename1 = __DIR__ . '/' . $filename;
    fwrite($log, 'UPDATE FIlTRENAME: ' . $response . "\r\n");
    unlink($filename1);


//*****************************************
//    echo('UPDATE ORDER VALUE:');
//*****************************************
    /*
        $filename = 'filtervalue.json';
        $json_url = $track . $filename;
        $post = array ('json_url' => $json_url);
        $curl = curl_init( $host .'index.php?route=api/oneboxsync/updateOrderValue/&token=' . $token );
        curl_setopt_array( $curl, array(CURLOPT_RETURNTRANSFER=> TRUE, CURLOPT_POSTFIELDS => $post ) );
        $response = json_decode(curl_exec( $curl ));
        curl_close($curl);
    //    print_r ($response);
        fwrite($log, 'UPDATE ORDER VALUE: ' . $response . "</br>");
        unlink($filename);

    */

//*********************************************
//    echo('UPDATE FILTRE PRODUCT VALUE:');
//***********************************************
    fwrite($log, 'UPDATE FILTRE PRODUCT VALUE: ');
    $filename = 'filtervalue.json';
    $json_url = $track . $filename;
    $post = array ('json_url' => $json_url);
    $curl = curl_init( $host .'index.php?route=api/oneboxsync/updateFiltreProductValue/&token=' . $token . '&api_token=' . $token);
    curl_setopt_array( $curl, array(CURLOPT_RETURNTRANSFER=> TRUE, CURLOPT_POSTFIELDS => $post ) );
    $response = json_decode(curl_exec( $curl ));
    curl_close($curl);
//    print_r ($response . "</br>");

    fwrite($log, $response . "\r\n");
    $filename1 = __DIR__ . '/' . $filename;
    unlink($filename1);

    fclose($log);
    /*
        echo('EXAMPLE NEW file:');
    //***********************************************
        $filename = $track . 'category.json'; //�������� ���� ���������� �������� �����
        $json_url = 'http://box.larus.ua/media/export/opencart/category.json';  //������ ���� �������
        $post = array ('json_url' => $json_url, 'name' => $filename);
        $curl = curl_init( $host .'index.php?route=api/oneboxsync/addFiles/&token=' . $token );
        curl_setopt_array( $curl, array(CURLOPT_RETURNTRANSFER=> TRUE, CURLOPT_POSTFIELDS => $post ) );
        $response = json_decode(curl_exec( $curl ));
        curl_close($curl);
        print_r ($response . "</br>");*/
    include $host . 'seo.php';


}
?>