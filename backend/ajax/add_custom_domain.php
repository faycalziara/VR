<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip']) && ($_SESSION['id_user']==$_SESSION['demo_user_id'])) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_user = (int)$_SESSION['id_user'];
session_write_close();
$settings = get_settings();
$user_info = get_user_info($id_user);
if($settings['enable_custom_domain']) {
    if($user_info['role']!='editor') {
        $can_create = get_plan_permission($id_user)['enable_custom_domain'];
        if(!$can_create) {
            die();
        }
    } else {
        die();
    }
} else {
    die();
}
if($user_info['role']=='customer') {
    $count_custom_domains = check_plan_custom_domain_count($id_user);
    if($count_custom_domains==0) {
        die();
    }
}
$custom_domain = strip_tags($_POST['custom_domain']);
$query = "SELECT id FROM svt_custom_domains WHERE custom_domain=? LIMIT 1;";
if($smt = $mysqli->prepare($query)) {
    $smt->bind_param('s', $custom_domain);
    $result = $smt->execute();
    if ($result) {
        $result = get_result($smt);
        if (count($result) == 1) {
            ob_end_clean();
            echo json_encode(array("status"=>"exist"));
            exit;
        }
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error"));
        exit;
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    exit;
}
$query = "INSERT INTO svt_custom_domains(id_user,custom_domain,date_time,status) VALUES(?,?,NOW(),0);";
if($smt = $mysqli->prepare($query)) {
    $smt->bind_param('is',  $id_user,$custom_domain);
    $result = $smt->execute();
    if ($result) {
        $insert_id = $mysqli->insert_id;
        ob_end_clean();
        echo json_encode(array("status"=>"ok","id"=>$insert_id));
        exit;
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error"));
        exit;
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    exit;
}