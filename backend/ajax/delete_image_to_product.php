<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip']) && ($_SESSION['id_user']==$_SESSION['demo_user_id'])) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id = (int)$_POST['id'];
$id_user = $_SESSION['id_user'];
$id_virtualtour = $_SESSION['id_virtualtour_sel'];
if(!check_elem_ownership($id_user,$id_virtualtour)) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    die();
}
session_write_close();
$query = "DELETE FROM svt_product_images WHERE id=$id;";
$result = $mysqli->query($query);
if($result) {
    $mysqli->query("ALTER TABLE svt_product_images AUTO_INCREMENT = 1;");
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
    ob_end_clean();
    echo json_encode(array("status"=>"ok"));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}