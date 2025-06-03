<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip']) && ($_SESSION['id_user']==$_SESSION['demo_user_id'])) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
ini_set('max_execution_time', 9999);
require_once("../../db/connection.php");
require_once("../functions.php");
$settings = get_settings();
$user_info = get_user_info($_SESSION['id_user']);
if(!empty($user_info['language'])) {
    set_language($user_info['language'],$settings['language_domain']);
} else {
    set_language($settings['language'],$settings['language_domain']);
}
session_write_close();
$id_user = (int)$_POST['id_user'];
$id_virtualtour = (int)$_POST['id_virtualtour'];
$id_map = (int)$_POST['id_map'];
if(get_user_role($id_user)=='administrator') {
    $query = "SELECT * FROM svt_virtualtours WHERE id=$id_virtualtour; ";
} else {
    $query = "SELECT * FROM svt_virtualtours WHERE id_user=$id_user AND id=$id_virtualtour; ";
}
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==0) {
        ob_end_clean();
        echo json_encode(array("status"=>"unauthorized"));
        exit;
    }
}
$duplicated_label = _("duplicated");
$mysqli->query("CREATE TEMPORARY TABLE svt_map_tmp SELECT * FROM svt_maps WHERE id = $id_map;");
$mysqli->query("UPDATE svt_map_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_maps),name=CONCAT(name,' ($duplicated_label)'),id_room_default=NULL;");
$mysqli->query("INSERT INTO svt_maps SELECT * FROM svt_map_tmp;");
$id_map_new = $mysqli->insert_id;
$array_maps[$id_map] = $id_map_new;
$mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_map_tmp;");
$result_m = $mysqli->query("SELECT id_map FROM svt_maps_lang WHERE id_map=$id_map;");
if($result_m) {
    if($result_m->num_rows>0) {
        $mysqli->query("CREATE TEMPORARY TABLE svt_maps_lang_tmp SELECT * FROM svt_maps_lang WHERE id_map = $id_map;");
        $mysqli->query("UPDATE svt_maps_lang_tmp SET id_map=$id_map_new;");
        $mysqli->query("INSERT INTO svt_maps_lang SELECT * FROM svt_maps_lang_tmp;");
        $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_maps_lang_tmp;");
    }
}
ob_end_clean();
echo json_encode(array("status"=>"ok"));