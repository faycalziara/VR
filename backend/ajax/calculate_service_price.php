<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}
require_once("../functions.php");
$count = (int)$_POST['count'];
$type = $_POST['type'];
if($type!='tour_service') {
    $count = 1;
}
$price = (float)$_POST['price'];
$currency = $_POST['currency'];
$credits = (int)$_POST['credits'];
$html_price = "";
$html_credits = "";
$total_price = $price * $count;
$total_credits = $credits * $count;
if($price>0 && $credits==0) {
    $html_price = format_currency($currency,$total_price);
} else if($price==0 && $credits>0) {
    $html_credits = $total_credits;
} else {
    $html_price = format_currency($currency,$total_price);
    $html_credits = $total_credits;
}
ob_end_clean();
echo json_encode(array("html_price"=>$html_price,"html_credits"=>$html_credits,"price"=>$total_price,"credits"=>$total_credits));