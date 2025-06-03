<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip']) && ($_SESSION['id_user']==$_SESSION['demo_user_id'])) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_virtualtour = $_SESSION['id_virtualtour_sel'];
$id_user = $_SESSION['id_user'];
session_write_close();
if(!check_elem_ownership($id_user,$id_virtualtour)) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    die();
}
$id = (int)$_POST['id'];
$id_video = (int)$_POST['id_video'];
if(empty($_POST['id_room'])) {
    $id_room = null;
} else {
    $id_room = (int)$_POST['id_room'];
}
$type = strip_tags($_POST['type']);
$file = strip_tags($_POST['file']);
if(empty($file)) $file=null;
if(empty($_POST['duration'])) {
    $duration = null;
} else {
    $duration = (float)$_POST['duration'];
}
$params = $_POST['params'];
if($type=='logo' || $type=='text') {
    $font = "NotoSans-Regular.ttf";
} else {
    $font = null;
}
$s3_params = check_s3_tour_enabled($id_virtualtour);
$s3_enabled = false;
$s3_bucket_name = "";
if(!empty($s3_params)) {
    $s3_bucket_name = $s3_params['bucket'];
    $s3_url = init_s3_client($s3_params);
    if($s3_url!==false) {
        $s3_enabled = true;
    }
}
if($file!==null && strpos($file,"/gallery/") !== false) {
    $file = basename($file);
    if($s3_enabled) {
        $path_source = "s3://$s3_bucket_name/viewer/gallery/$file";
        $path_dest = "s3://$s3_bucket_name/video/assets/$id_virtualtour/$file";
    } else {
        $path_source = dirname(__FILE__).'/../../viewer/gallery/'.$file;
        $path_dest =dirname(__FILE__).'/../../video/assets/'.$id_virtualtour.'/'.$file;
    }
    copy($path_source,$path_dest);
} else {
    $file = basename($file);
}
if($id==null) {
    $priority = 1;
    $query = "SELECT MAX(priority) as priority FROM svt_video_project_slides WHERE id_video_project=$id_video;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $priority = $row['priority']+1;
        }
    }
    $query = "INSERT INTO svt_video_project_slides(id_video_project,type,id_room,file,duration,params,priority,font) VALUES(?,?,?,?,?,?,?,?);";
} else {
    $query = "UPDATE svt_video_project_slides SET file=?,duration=?,params=?,id_room=?,font=? WHERE id=? AND id_video_project=?;";
}
if($smt = $mysqli->prepare($query)) {
    if($id==null) {
        $smt->bind_param('isisdsis',$id_video,$type,$id_room,$file,$duration,$params,$priority,$font);
    } else {
        $smt->bind_param('sdsisii',$file,$duration,$params,$id_room,$font,$id,$id_video);
    }
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
    echo json_encode(array("status" => "error"));
}