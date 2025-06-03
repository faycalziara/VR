<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
ob_start();
if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    die();
}
require_once("../../db/connection.php");
require_once("../../backend/functions.php");
$settings = get_settings();
if(!empty($settings['timezone'])) {
    date_default_timezone_set($settings['timezone']);
}
$headers = getallheaders();
if ($headers["Content-Type"] == "application/json; charset=UTF-8") {
    $_POST = json_decode(file_get_contents("php://input"), true) ?: [];
}
session_write_close();
$type = $_POST['type'];
$id = (int)$_POST['id'];
$ip_visitor = strip_tags($_POST['ip_visitor']);
$now = date('Y-m-d H:i:s');
switch ($type) {
    case 'poi':
        $mysqli->query("UPDATE svt_pois SET access_count=access_count+1 WHERE id=$id;");
        $mysqli->query("INSERT INTO svt_access_log_poi(id_poi,date_time,ip) VALUES($id,'$now','$ip_visitor');");
        break;
    case 'room':
        $mysqli->query("UPDATE svt_rooms SET access_count=access_count+1 WHERE id=$id;");
        $mysqli->query("INSERT INTO svt_access_log_room(id_room,date_time,ip) VALUES($id,'$now','$ip_visitor');");
        break;
    case 'room_time':
        $access_time_avg = $_POST['access_time_avg'];
        $mysqli->query("INSERT INTO svt_rooms_access_log(id_room,time,ip) VALUES($id,$access_time_avg,'$ip_visitor');");
        break;
}