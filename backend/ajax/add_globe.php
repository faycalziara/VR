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
$name = strip_tags($_POST['name']);
$query = "INSERT INTO svt_globes(id_user,name) VALUES(?,?); ";
if($smt = $mysqli->prepare($query)) {
    $smt->bind_param('is',  $id_user,$name);
    $result = $smt->execute();
    if ($result) {
        $insert_id = $mysqli->insert_id;
        $code = md5($insert_id);
        $mysqli->query("UPDATE svt_globes SET code='$code' WHERE id=$insert_id;");
        $query = "SELECT id FROM svt_custom_domains WHERE id_user=$id_user AND default_globe=1 LIMIT 1;";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows==1) {
                $row=$result->fetch_array(MYSQLI_ASSOC);
                $id_custom_domain=$row['id'];
                $mysqli->query("INSERT INTO svt_custom_domains_globe_assoc(id_globe,id_custom_domain) VALUES($insert_id,$id_custom_domain);");
            }
        }
        ob_end_clean();
        echo json_encode(array("status"=>"ok","id"=>$insert_id));
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error"));
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}