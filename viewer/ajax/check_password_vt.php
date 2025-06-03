<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
ob_start();
if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    die();
}
require_once("../../db/connection.php");
session_write_close();
$code = strip_tags($_POST['code']);
$password = str_replace("'","\'",strip_tags($_POST['password']));
$query = "SELECT id FROM svt_virtualtours WHERE code='$code' AND password=MD5('$password');";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows == 1) {
        ob_end_clean();
        echo json_encode(array("status"=>"ok"));
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"incorrect"));
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}