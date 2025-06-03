<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
ob_start();
if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    die();
}
require_once("../../db/connection.php");
require_once("../../backend/functions.php");
session_write_close();
$settings = get_settings();
if(!empty($settings['timezone'])) {
    date_default_timezone_set($settings['timezone']);
}
$now = date('Y-m-d H:i:s');
$id_virtualtour = (int)$_POST['id_virtualtour'];
$email = strtolower(trim(strip_tags($_POST['email'])));
$name = trim(strip_tags($_POST['name']));
$company = trim(strip_tags($_POST['company']));
$phone = trim(strip_tags($_POST['phone']));
$check = (int)$_POST['check'];
$query_check = "SELECT * FROM svt_leads WHERE id_virtualtour=? AND email=? LIMIT 1;";
if($smt = $mysqli->prepare($query_check)) {
    $smt->bind_param('is', $id_virtualtour, $email);
    $result_check = $smt->execute();
    if ($result_check) {
        $result_check = get_result($smt);
        if (count($result_check) == 1) {
            ob_end_clean();
            echo json_encode(array("status"=>"ok"));
            exit;
        } else {
            if($check==0) {
                $query = "INSERT INTO svt_leads(id_virtualtour,name,company,email,phone,datetime) VALUES(?,?,?,?,?,?);";
                if($smt = $mysqli->prepare($query)) {
                    $smt->bind_param('isssss',  $id_virtualtour,$name,$company,$email,$phone,$now);
                    $result = $smt->execute();
                    if ($result) {
                        ob_end_clean();
                        echo json_encode(array("status"=>"ok"));
                        exit;
                    }
                }
            }
        }
    }
}
ob_end_clean();
echo json_encode(array("status"=>"error"));
exit;