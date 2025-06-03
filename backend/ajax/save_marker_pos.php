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
$id_virtualtour = $_SESSION['id_virtualtour_sel'];
$id_marker = (int)$_POST['id'];
if(!check_elem_ownership($id_user,$id_virtualtour,'marker',$id_marker)) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    die();
}
session_write_close();
$yaw = (float)$_POST['yaw'];
$pitch = (float)$_POST['pitch'];
$rotateX = (int)$_POST['rotateX'];
$rotateZ = (int)$_POST['rotateZ'];
$zIndex = (int)$_POST['zIndex'];
$size_scale = (float)$_POST['size_scale'];
if(!isset($_POST['scale'])) $scale=0; else $scale = (int)$_POST['scale'];
$embed_coords = strip_tags($_POST['embed_coords']);
$embed_size = strip_tags($_POST['embed_size']);
if(empty($embed_coords)) $embed_coords = NULL;
if(empty($embed_size)) $embed_size = NULL;
$embed_params = strip_tags($_POST['embed_params']);
$visible_multiview_ids = strip_tags($_POST['visible_multiview_ids']);
$sticky = (int)$_POST['sticky'];
$query = "UPDATE svt_markers SET yaw=?,pitch=?,rotateX=?,rotateZ=?,size_scale=?,scale=?,embed_coords=?,embed_size=?,embed_params=?,zIndex=?,visible_multiview_ids=?,sticky=? WHERE id=?;";
if($smt = $mysqli->prepare($query)) {
    $smt->bind_param('ddiidisssisii',$yaw,$pitch,$rotateX,$rotateZ,$size_scale,$scale,$embed_coords,$embed_size,$embed_params,$zIndex,$visible_multiview_ids,$sticky,$id_marker);
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