<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip']) && ($_SESSION['id_user']==$_SESSION['demo_user_id'])) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_user = $_SESSION['id_user'];
$id_virtualtour = (int)$_POST['id_virtualtour'];
if(!check_elem_ownership($id_user,$id_virtualtour)) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    die();
}
session_write_close();
$id_initial_room = (int)$_POST['id_initial_room'];
$query = "UPDATE svt_virtualtours SET id_room_initial=$id_initial_room WHERE id=$id_virtualtour;";
$mysqli->query($query);
$mysqli->close();