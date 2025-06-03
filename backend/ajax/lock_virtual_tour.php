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
session_write_close();
$id_virtualtour = (int)$_POST['id_virtualtour'];
$lock = (int)$_POST['lock'];
if($lock==1) {
    $block_tour_msg = strip_tags($_POST['block_tour_msg']);
} else {
    $block_tour_msg = "";
}
if(get_user_role($id_user)!='administrator') {
   die();
}
$query = "UPDATE svt_virtualtours SET block_tour=?,block_tour_msg=? WHERE id=?;";
if($smt = $mysqli->prepare($query)) {
    $smt->bind_param('isi', $lock,$block_tour_msg,$id_virtualtour);
    $result = $smt->execute();
    if ($result) {
        ob_end_clean();
        echo json_encode(array("status"=>"ok"));
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error"));
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}