<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip']) && ($_SESSION['id_user']==$_SESSION['demo_user_id'])) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_user = (int)$_SESSION['id_user'];
session_write_close();
$user_info = get_user_info($id_user);
$id = (int)$_POST['id'];
$mode = strip_tags($_POST['mode']);
if($mode!='remove') {
    if($user_info['role']!='administrator') {
        die();
    }
}
switch($mode) {
    case 'approve':
        $query = "UPDATE svt_custom_domains SET status=1 WHERE id=$id;";
        break;
    case 'reject':
        $query = "UPDATE svt_custom_domains SET status=-1 WHERE id=$id;";
        break;
    case 'remove':
        $query = "UPDATE svt_custom_domains SET status=-2 WHERE id=$id;";
        break;
}
$result = $mysqli->query($query);
if($result) {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
    exit;
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    exit;
}