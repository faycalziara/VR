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
$id_virtualtour = $_SESSION['id_virtualtour_sel'];
$id_room = (int)$_POST['id_room'];
if(!check_elem_ownership($id_user,$id_virtualtour,'room',$id_room)) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    die();
}
$position = strip_tags($_POST['position']);
$map_type = strip_tags($_POST['map_type']);
$tmp = explode(",",$position);
$top = $tmp[0];
$left = $tmp[1];
switch ($map_type) {
    case 'floorplan':
        $query = "UPDATE svt_rooms SET map_top=?,map_left=? WHERE id=?;";
        if($smt = $mysqli->prepare($query)) {
            $smt->bind_param('iii', $top,$left,$id_room);
            $result = $smt->execute();
            if($result) {
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
        break;
    case 'map':
        $query = "UPDATE svt_rooms SET lat=?,lon=? WHERE id=?;";
        if($smt = $mysqli->prepare($query)) {
            $smt->bind_param('ssi', $top,$left,$id_room);
            $result = $smt->execute();
            if($result) {
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
        break;
}