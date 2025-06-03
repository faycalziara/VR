<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
ob_start();
if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    die();
}
require_once("../../db/connection.php");
session_write_close();
$id_virtualtour = (int)$_POST['id_virtualtour'];
$language = strip_tags($_POST['language']);
$count_languages_enabled = (int)$_POST['count_languages_enabled'];
$info_box = "";
$info_box_type = "popup";
$query = "SELECT info_box,info_box_type FROM svt_virtualtours WHERE id=$id_virtualtour AND show_info>0 LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row=$result->fetch_array(MYSQLI_ASSOC);
        $info_box = $row['info_box'];
        if($count_languages_enabled>1) {
            $query_lang = "SELECT info_box FROM svt_virtualtours_lang WHERE language='$language' AND id_virtualtour=$id_virtualtour";
            $result_lang = $mysqli->query($query_lang);
            if($result_lang) {
                if ($result_lang->num_rows == 1) {
                    $row_lang = $result_lang->fetch_array(MYSQLI_ASSOC);
                    if(($row_lang['info_box']!="<p><br></p>") && (!empty($row_lang['info_box']))) {
                        $info_box = $row_lang['info_box'];
                    }
                }
            }
        }
        $info_box_type = $row['info_box_type'];
        if(($info_box=="<p><br></p>") || (empty($info_box))) $info_box="";
    }
}
ob_end_clean();
echo json_encode(array("info_box"=>$info_box,"info_box_type"=>$info_box_type));