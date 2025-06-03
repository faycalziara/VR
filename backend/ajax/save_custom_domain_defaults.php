<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip']) && ($_SESSION['id_user']==$_SESSION['demo_user_id'])) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_user = (int) $_SESSION['id_user'];
session_write_close();
$id = (int)$_POST['id'];
$default_tour = (int)$_POST['default_tour'];
$default_showcase = (int)$_POST['default_showcase'];
$default_globe = (int)$_POST['default_globe'];
$query = "UPDATE svt_custom_domains SET default_tour=$default_tour,default_showcase=$default_showcase,default_globe=$default_globe WHERE id=$id;";
$result = $mysqli->query($query);
if($result) {
    if($default_tour==1) {
        $mysqli->query("UPDATE svt_custom_domains SET default_tour=0 WHERE id_user=$id_user AND id!=$id;");
    }
    if($default_showcase==1) {
        $mysqli->query("UPDATE svt_custom_domains SET default_showcase=0 WHERE id_user=$id_user AND id!=$id;");
    }
    if($default_globe==1) {
        $mysqli->query("UPDATE svt_custom_domains SET default_globe=0 WHERE id_user=$id_user AND id!=$id;");
    }
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
    exit;
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    exit;
}