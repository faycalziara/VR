<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip']) && ($_SESSION['id_user']==$_SESSION['demo_user_id'])) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
session_write_close();
$id_virtualtour = (int)$_POST['id_virtualtour'];
$id_user = $_SESSION['id_user'];
if(!check_elem_ownership($id_user,$id_virtualtour)) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    die();
}
$array_images_priority = json_decode($_POST['array_images_priority'],true);
foreach ($array_images_priority as $priority=>$id) {
    $mysqli->query("UPDATE svt_intro_slider SET priority=$priority WHERE id=$id AND id_virtualtour=$id_virtualtour;");
}
ob_end_clean();