<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}
require_once("../../db/connection.php");
session_write_close();
$custom_domain = strip_tags($_POST['custom_domain']);
sleep(1);
$ip = gethostbyname($custom_domain);
if ($ip === $custom_domain) {
    ob_end_clean();
    echo json_encode(array("status" => "error"));
} else {
    ob_end_clean();
    echo json_encode(array("status" => "ok","ip"=>$ip));
}