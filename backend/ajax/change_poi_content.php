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
$id = (int)$_POST['id'];
if(!check_elem_ownership($id_user,$id_virtualtour,'poi',$id)) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    die();
}
session_write_close();
$content_type = strip_tags($_POST['content_type']);
if(empty($content_type)) {
    $query = "UPDATE svt_pois SET type=NULL,content=NULL WHERE id=$id;";
} else {
    if($content_type=='staging') {
        $query = "UPDATE svt_pois SET type='$content_type',content=NULL,icon_type=0,icon='fas fa-arrows-spin',color='#ffffff' WHERE id=$id;";
    } else {
        $query = "UPDATE svt_pois SET type='$content_type',content=NULL WHERE id=$id;";
    }
}
$result=$mysqli->query($query);
if($result) {
    $mysqli->query("UPDATE svt_pois_lang SET content=NULL,params=NULL WHERE id_poi=$id;");
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}