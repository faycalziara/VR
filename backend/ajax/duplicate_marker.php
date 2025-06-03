<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip']) && ($_SESSION['id_user']==$_SESSION['demo_user_id'])) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
ini_set('max_execution_time', 9999);
require_once("../../db/connection.php");
session_write_close();
$id_marker = (int)$_POST['id_marker'];
$mysqli->query("CREATE TEMPORARY TABLE svt_marker_tmp SELECT * FROM svt_markers WHERE id = $id_marker;");
$mysqli->query("UPDATE svt_marker_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_markers),yaw=yaw+4;");
$mysqli->query("INSERT INTO svt_markers SELECT * FROM svt_marker_tmp;");
$id_marker_new = $mysqli->insert_id;
$mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_marker_tmp;");
ob_end_clean();
echo json_encode(array("status"=>"ok","id"=>$id_marker_new));