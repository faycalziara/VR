<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip']) && ($_SESSION['id_user']==$_SESSION['demo_user_id'])) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
session_write_close();
$name = strip_tags($_POST['name']);
$icon = strip_tags($_POST['icon']);
$background = strip_tags($_POST['background']);
$color = strip_tags($_POST['color']);
$position = (int)$_POST['position'];
$query = "INSERT INTO svt_categories(name,icon,background,color,position) VALUES(?,?,?,?,?);";
if($smt = $mysqli->prepare($query)) {
    $smt->bind_param('ssssi',  $name,$icon,$background,$color,$position);
    $result = $smt->execute();
    if ($result) {
        $insert_id = $mysqli->insert_id;
        ob_end_clean();
        echo json_encode(array("status"=>"ok","id"=>$insert_id));
        exit;
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error"));
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}