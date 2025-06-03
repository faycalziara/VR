<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
ob_start();
if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    die();
}
$id_virtualtour = (int)$_POST['id_virtualtour'];
if(isset($_SESSION['cart_key'])) {
    $cart_key = $_SESSION['cart_key'];
} else {
    $cart_key = "cart_$id_virtualtour".md5($_SERVER['HTTP_USER_AGENT'] . (!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR'])));
}
$woocommerce_store_url = $_POST['woocommerce_store_url'];
$woocommerce_store_cart = $_POST['woocommerce_store_cart'];
$woocommerce_store_checkout = $_POST['woocommerce_store_checkout'];
$woocommerce_cocart_url_api = $woocommerce_store_url."/wp-json/cocart/v2";
$cart = get_woocommerce_cart_total($cart_key);
$total = $cart[0];
$count = $cart[1];
$url_cart = $cart[2];
$url_checkout = $cart[3];
$items = $cart[4];
ob_end_clean();
echo json_encode(array("status"=>"ok","total"=>$total,"item_count"=>$count,"url_cart"=>$url_cart,"url_checkout"=>$url_checkout,"items"=>$items));
exit;

function get_woocommerce_cart_total($cart_key) {
    global $woocommerce_cocart_url_api,$woocommerce_store_url,$woocommerce_store_cart,$woocommerce_store_checkout;
    $cart_total = "--";
    $item_count = 0;
    $items = array();
    $curl = curl_init($woocommerce_cocart_url_api.'/cart/?cart_key='.$cart_key);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        'Accept: application/json',
        'User-Agent: CoCart API/v2',
        'Cache-Control: no-cache, no-store, max-age=0',
        'Pragma: no-cache',
        'Expires: 0'
    ));
    $response = curl_exec($curl);
    curl_close($curl);
    $result = json_decode($response,true);
    if(isset($result['totals'])) {
        $total = $result['totals']['total']/100;
        $item_count = $result['item_count'];
        $currency = $result['currency']['currency_symbol'];
        $currency_minor_unit = $result['currency']['currency_minor_unit'];
        $currency_decimal_separator = $result['currency']['currency_decimal_separator'];
        $currency_thousand_separator = $result['currency']['currency_thousand_separator'];
        $cart_total = number_format($total,$currency_minor_unit,$currency_decimal_separator,$currency_thousand_separator)." ".$currency;
        $items = $result['items'];
        foreach ($items as $index => $item) {
            $items[$index]['price_html'] = number_format(($item['price']/100),$currency_minor_unit,$currency_decimal_separator,$currency_thousand_separator)." ".$currency;
            $items[$index]['totals']['total_html'] = number_format($item['totals']['total'],$currency_minor_unit,$currency_decimal_separator,$currency_thousand_separator)." ".$currency;
        }
    }
    $url_cart = "$woocommerce_store_url/$woocommerce_store_cart?cocart-load-cart=$cart_key";
    $url_checkout = "$woocommerce_store_url/$woocommerce_store_checkout?cocart-load-cart=$cart_key";
    return [$cart_total,$item_count,$url_cart,$url_checkout,$items];
}