<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}
require_once("../functions.php");
$id_user = $_SESSION['id_user'];
session_write_close();
$id_service = (int)$_POST['id_service'];
$id_vt = (int)$_POST['id_vt'];
$count = (int)$_POST['count'];
if($id_vt!=0) {
    $virtual_tour = get_virtual_tour($id_vt,$id_user);
    $name_vt = $virtual_tour['name'];
} else {
    $name_vt = "";
}
$service = get_service($id_service);
if($service['type']!='tour_service') {
    $count = 1;
}
$price = (float)$service['price'];
$currency = $service['currency'];
$total_price = $price * $count;
$unique_id = uniqid();
$params = [
    'id_service' => $id_service,
    'id_vt' => $id_vt,
    'count' => $count,
    'name_vt' => $name_vt,
    'uid' => $unique_id
];
ob_end_clean();
echo json_encode(array("price"=>$total_price,"currency"=>$currency,"params"=>json_encode($params)));