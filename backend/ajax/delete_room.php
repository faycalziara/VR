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
$id_room = (int)$_POST['id_room'];
if(!check_elem_ownership($id_user,$id_virtualtour,'room',$id_room)) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    die();
}
session_write_close();
$query = "SELECT name FROM svt_rooms WHERE id=$id_room LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $name = $row['name'];
    }
}
$query = "DELETE FROM svt_rooms WHERE id=$id_room; ";
$result = $mysqli->query($query);
if($result) {
    $mysqli->query("ALTER TABLE svt_rooms AUTO_INCREMENT = 1;");
    $mysqli->query("ALTER TABLE svt_rooms_alt AUTO_INCREMENT = 1;");
    $mysqli->query("ALTER TABLE svt_rooms_access_log AUTO_INCREMENT = 1;");
    $mysqli->query("ALTER TABLE svt_markers AUTO_INCREMENT = 1;");
    $mysqli->query("ALTER TABLE svt_pois AUTO_INCREMENT = 1;");
    $mysqli->query("ALTER TABLE svt_poi_gallery AUTO_INCREMENT = 1;");
    $mysqli->query("ALTER TABLE svt_poi_gallery_lang AUTO_INCREMENT = 1;");
    $mysqli->query("ALTER TABLE svt_products AUTO_INCREMENT = 1;");
    $mysqli->query("ALTER TABLE svt_product_images AUTO_INCREMENT = 1;");
    $mysqli->query("ALTER TABLE svt_poi_embedded_gallery AUTO_INCREMENT = 1;");
    $mysqli->query("ALTER TABLE svt_poi_objects360 AUTO_INCREMENT = 1;");
    $mysqli->query("UPDATE svt_rooms SET video_end_goto=0 WHERE video_end_goto=$id_room;");
    $mysqli->query("UPDATE svt_virtualtours SET presentation_stop_id_room=0 WHERE presentation_stop_id_room=$id_room;");
    $mysqli->query("UPDATE svt_virtualtours SET id_room_initial=-2 WHERE id_room_initial=$id_room;");
    $mysqli->query("UPDATE svt_autoenhance_log SET deleted=1 WHERE id_room=$id_room;");
    if(isEnabled('shell_exec')) {
        require_once(__DIR__ . "/../../config/config.inc.php");
        if (defined('PHP_PATH')) {
            $path_php = PHP_PATH;
        } else {
            $path_php = '';
        }
        try {
            if(empty($path_php)) {
                $command = 'command -v php 2>&1';
                $output = shell_exec($command);
                if(empty($output)) $output = PHP_BINARY;
                $path_php = trim($output);
                $path_php = str_replace("sbin/php-fpm","bin/php",$path_php);
            }
            $path = realpath(dirname(__FILE__).'/../../services');
            $command = $path_php." ".$path.DIRECTORY_SEPARATOR."clean_images.php $id_user > /dev/null &";
            shell_exec($command);
        } catch (Exception $e) {}
    } else {
        include("../../services/clean_images.php");
    }
    update_user_space_storage($id_user,false);
    set_user_log($id_user,'delete_room',json_encode(array("id"=>$id_room,"name"=>$name)),date('Y-m-d H:i:s', time()));
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}