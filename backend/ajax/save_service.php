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
if(!get_user_role($id_user)=='administrator') {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    exit;
}
$id = (int)$_POST['id'];
$name = strip_tags($_POST['name']);
$position = (int)$_POST['position'];
$description = $_POST['description'];
if($description=='<p><br></p>' || $description=='<p></p>') $description="";
$price = str_replace(",",".",strip_tags($_POST['price']));
$price = (float)$price;
$credits = (int)$_POST['credits'];
$block_tour = (int)$_POST['block_tour'];
$visible = (int)$_POST['visible'];
$type = strip_tags($_POST['type']);
if($type=='generic') $block_tour = 0;
$currency = strip_tags($_POST['currency']);
$array_lang = json_decode($_POST['array_lang'],true);
$query = "UPDATE svt_services SET name=?,description=?,type=?,currency=?,price=?,block_tour=?,credits=?,visible=?,position=? WHERE id=?;";
if ($smt = $mysqli->prepare($query)) {
    $smt->bind_param('ssssdiiiii',$name,$description,$type,$currency,$price,$block_tour,$credits,$visible,$position,$id);
    $result = $smt->execute();
    if ($result) {
        save_input_langs($array_lang,'svt_services_lang','id_service',$id);
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