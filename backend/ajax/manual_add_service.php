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
if(get_user_role($id_user)!='administrator') {
    die();
}
$id_service = (int)$_POST['id_service'];
$id_vt = (int)$_POST['id_tour'];
$use_credits = (int)$_POST['use_credits'];
$use_credits_val = (int)$_POST['use_credits_val'];
$id_user = (int)$_POST['id_user'];
$rooms_num = (int)$_POST['rooms_num'];
if($rooms_num==0) { $rooms_num = 'NULL'; }
$settings = get_settings();
$count = get_rooms_count($id_vt);
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
if($service['type']!='tour_service') {
    $count = 1;
}
if($use_credits) {
    $credits = $use_credits_val;
} else {
    $credits = 0;
}
$username = $user['username'];
$email_u = $user['email'];
$uid = uniqid();
set_user_log($id_user,'purchase_service',json_encode(array("id"=>$id_service,"name"=>$service_name,"id_vt"=>$id_vt,"name_vt"=>$name_vt)),date('Y-m-d H:i:s', time()));
$result = $mysqli->query("INSERT INTO svt_services_log(uid,id_user,id_service,id_virtualtour,date_time,credits_used,note,rooms_num) VALUES('$uid',$id_user,$id_service,$id_vt,NOW(),$credits,'',$rooms_num);");
if($result) {
    if($settings['notify_service_purchase']) {
        $subject = $settings['mail_service_purchased_subject'];
        $body = $settings['mail_service_purchased_body'];
        if(!empty($name_vt)) {
            $service_name = $service_name." (".$name_vt.")";
        }
        $body = str_replace("%USER_NAME%",$username,$body);
        $body = str_replace("%SERVICE_NAME%",$service_name,$body);
        $body = str_replace("%NOTE%",$service_name,'');
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