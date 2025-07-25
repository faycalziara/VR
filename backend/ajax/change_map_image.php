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
$id_map = (int)$_POST['id_map'];
if(!check_elem_ownership($id_user,$id_virtualtour,'map',$id_map)) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    die();
}
$s3_params = check_s3_tour_enabled($id_virtualtour);
$s3_enabled = false;
$s3_url = "";
if(!empty($s3_params)) {
    $s3_bucket_name = $s3_params['bucket'];
    $s3_region = $s3_params['region'];
    $s3_url = init_s3_client($s3_params);
    if($s3_url!==false) {
        $s3_enabled = true;
    }
}
$map_image = strip_tags($_POST['map_image']);
if($s3_enabled) {
    $map_image = str_replace($s3_url."viewer/maps/","",$map_image);
} else {
    $map_image = str_replace("../viewer/maps/","",$map_image);
}
$query = "UPDATE svt_maps SET map=? WHERE id=? AND id_virtualtour=?;";
if($smt = $mysqli->prepare($query)) {
    $smt->bind_param('sii',  $map_image,$id_map,$id_virtualtour);
    $result = $smt->execute();
    if ($result) {
        $map_image_gt = $map_image;
        include("../../services/generate_thumb.php");
        update_user_space_storage($id_user,false);
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