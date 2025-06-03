<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
require_once(__DIR__."/../functions.php");
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}
$id_vt = (int)$_POST['id_vt'];
$virtual_tour = get_virtual_tour($id_vt,$_SESSION['id_user']);
if($virtual_tour!==false) {
    $html = get_option_products_wc($virtual_tour);
    ob_end_clean();
    echo json_encode(array("status"=>"ok","products"=>$html));
    exit;
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    exit;
}