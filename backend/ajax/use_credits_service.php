<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip']) && ($_SESSION['id_user']==$_SESSION['demo_user_id'])) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
if(file_exists("../config/demo.inc.php")) {
    require_once("../config/demo.inc.php");
    if($_SERVER['SERVER_ADDR']==DEMO_SERVER_IP && DEMO_DISABLE_CHANGE_PLAN==1) {
        //DEMO MODE
        die();
    }
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_user = $_SESSION['id_user'];
session_write_close();
$id_vt = (int)$_POST["id_virtualtour"];
$id_service = (int)$_POST["id_service"];
$count = (int)$_POST["count"];
$note = strip_tags($_POST["note"]);
$settings = get_settings();
$user = get_user_info($id_user);
$service = get_service($id_service);
if($id_vt!=0) {
    $virtual_tour = get_virtual_tour($id_vt,$id_user);
    $name_vt = $virtual_tour['name'];
} else {
    $name_vt = "";
    $id_vt = "NULL";
}
$service_name = $service['name'];
$credits = $service['credits'];
if($service['type']!='tour_service') {
    $count = 1;
}
$credits = $count * $credits;
$username = $user['username'];
$email_u = $user['email'];
$note_q = str_replace("'","\'",$note);
$uid = uniqid();
set_user_log($id_user,'purchase_service',json_encode(array("id"=>$id_service,"name"=>$service_name,"id_vt"=>$id_vt,"name_vt"=>$name_vt)),date('Y-m-d H:i:s', time()));
$result = $mysqli->query("INSERT INTO svt_services_log(uid,id_user,id_service,id_virtualtour,date_time,credits_used,note,rooms_num) VALUES('$uid',$id_user,$id_service,$id_vt,NOW(),$credits,'$note_q',$count);");
if($result) {
    if($id_vt!="NULL" && $service['block_tour']) {
        $mysqli->query("UPDATE svt_virtualtours SET block_tour=1 WHERE id=$id_vt;");
    }
    if($settings['notify_service_purchase']) {
        $subject = $settings['mail_service_purchased_subject'];
        $body = $settings['mail_service_purchased_body'];
        if(!empty($name_vt)) {
            $service_name = $service_name." (".$name_vt.")";
        }
        $body = str_replace("%USER_NAME%",$username,$body);
        $body = str_replace("%SERVICE_NAME%",$service_name,$body);
        $body = str_replace("%NOTE%",$note,$body);
        $body = str_replace('<p><br></p>','<br>',$body);
        $body = str_replace('<p>','<p style="padding:0;margin:0;">',$body);
        $subject_q = str_replace("'","\'",$subject);
        $body_q = str_replace("'","\'",$body);
        $mysqli->query("INSERT INTO svt_notifications(id_user,subject,body,notify_user,notified) VALUES($id_user,'$subject_q','$body_q',1,0);");
    }
    ob_end_clean();
    echo json_encode(array("status"=>"ok","uid"=>$uid));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}
