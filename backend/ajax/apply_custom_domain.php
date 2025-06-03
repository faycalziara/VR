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
$id = (int)$_POST['id'];
$id_custom_domain = (int)$_POST['id_custom_domain'];
$type = strip_tags($_POST['type']);
switch($type) {
    case 'vt':
        switch(get_user_role($id_user)) {
            case 'customer':
                $where = " AND id_user=$id_user ";
                break;
            case 'editor':
                $where = " AND id IN () ";
                $query = "SELECT GROUP_CONCAT(id_virtualtour) as ids FROM svt_assign_virtualtours WHERE id_user=$id_user;";
                $result = $mysqli->query($query);
                if($result) {
                    if($result->num_rows==1) {
                        $row=$result->fetch_array(MYSQLI_ASSOC);
                        $ids = $row['ids'];
                        $where = " AND id IN ($ids) ";
                    }
                }
                break;
        }
        $code = "";
        $query = "SELECT code FROM svt_virtualtours WHERE id=$id $where LIMIT 1;";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows==1) {
                $row = $result->fetch_array(MYSQLI_ASSOC);
                $code = $row['code'];
            }
        }
        if(empty($code)) {
            ob_end_clean();
            echo json_encode(array("status"=>"error"));
            die();
        }
        $mysqli->query("DELETE FROM svt_custom_domains_tours_assoc WHERE id_virtualtour=$id;");
        $query = "INSERT INTO svt_custom_domains_tours_assoc(id_virtualtour,id_custom_domain) VALUES(?,?);";
        break;
    case 'showcase':
        $query = "SELECT id FROM svt_showcases WHERE id_user=$id_user;";
        $result = $mysqli->query($query);
        if($result) {
            if ($result->num_rows == 0) {
                ob_end_clean();
                echo json_encode(array("status"=>"error"));
                die();
            }
        }
        $mysqli->query("DELETE FROM svt_custom_domains_showcase_assoc WHERE id_showcase=$id;");
        $query = "INSERT INTO svt_custom_domains_showcase_assoc(id_showcase,id_custom_domain) VALUES(?,?);";
        break;
    case 'globe':
        $query = "SELECT id FROM svt_globes WHERE id_user=$id_user;";
        $result = $mysqli->query($query);
        if($result) {
            if ($result->num_rows == 0) {
                ob_end_clean();
                echo json_encode(array("status"=>"error"));
                die();
            }
        }
        $mysqli->query("DELETE FROM svt_custom_domains_globe_assoc WHERE id_globe=$id;");
        $query = "INSERT INTO svt_custom_domains_globe_assoc(id_globe,id_custom_domain) VALUES(?,?);";
        break;
}
if($id_custom_domain!=0) {
    if ($smt = $mysqli->prepare($query)) {
        $smt->bind_param('ii',$id,$id_custom_domain);
        $result = $smt->execute();
        if ($result) {
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
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
}