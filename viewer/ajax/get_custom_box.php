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
$array_vt_lang = array();
if($count_languages_enabled > 1) {
    $query_lang = "SELECT location_content,custom_content,custom2_content,custom3_content,custom4_content,custom5_content FROM svt_virtualtours_lang WHERE language='$language' AND id_virtualtour=$id_virtualtour";
    $result_lang = $mysqli->query($query_lang);
    if($result_lang) {
        if ($result_lang->num_rows == 1) {
            $row_lang = $result_lang->fetch_array(MYSQLI_ASSOC);
            $array_vt_lang=$row_lang;
        }
    }
}
$custom_content = "";
$custom2_content = "";
$custom3_content = "";
$location_content = "";
$query = "SELECT custom_content,custom2_content,custom3_content,custom4_content,custom5_content,show_custom,show_custom2,show_custom3,show_custom4,show_custom5,show_location,location_content FROM svt_virtualtours WHERE id=$id_virtualtour LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row=$result->fetch_array(MYSQLI_ASSOC);
        $show_custom = $row['show_custom'];
        $show_custom2 = $row['show_custom2'];
        $show_custom3 = $row['show_custom3'];
        $show_custom4 = $row['show_custom4'];
        $show_custom5 = $row['show_custom5'];
        $show_location = $row['show_location'];
        if(!empty($row['location_content'] && $row['location_content']!='|') && (!empty($array_vt_lang['location_content']) && $array_vt_lang['location_content']!='|')) {
            $row['location_content']=$array_vt_lang['location_content'];
        }
        if(!empty($row['custom_content'] && $row['custom_content']!='<div></div>') && (!empty($array_vt_lang['custom_content']) && $array_vt_lang['custom_content']!='<div></div>')) {
            $row['custom_content']=$array_vt_lang['custom_content'];
        }
        if(!empty($row['custom2_content'] && $row['custom2_content']!='<div></div>') && (!empty($array_vt_lang['custom2_content']) && $array_vt_lang['custom2_content']!='<div></div>')) {
            $row['custom2_content']=$array_vt_lang['custom2_content'];
        }
        if(!empty($row['custom3_content'] && $row['custom3_content']!='<div></div>') && (!empty($array_vt_lang['custom3_content']) && $array_vt_lang['custom3_content']!='<div></div>')) {
            $row['custom3_content']=$array_vt_lang['custom3_content'];
        }
        if(!empty($row['custom4_content'] && $row['custom4_content']!='<div></div>') && (!empty($array_vt_lang['custom4_content']) && $array_vt_lang['custom4_content']!='<div></div>')) {
            $row['custom4_content']=$array_vt_lang['custom4_content'];
        }
        if(!empty($row['custom5_content'] && $row['custom5_content']!='<div></div>') && (!empty($array_vt_lang['custom5_content']) && $array_vt_lang['custom5_content']!='<div></div>')) {
            $row['custom5_content']=$array_vt_lang['custom5_content'];
        }
        if($show_custom) {
            $custom_content = $row['custom_content'];
            if(($custom_content=="<div></div>") || (empty($custom_content))) $custom_content="";
        }
        if($show_custom2) {
            $custom2_content = $row['custom2_content'];
            if(($custom2_content=="<div></div>") || (empty($custom2_content))) $custom2_content="";
        }
        if($show_custom3) {
            $custom3_content = $row['custom3_content'];
            if(($custom3_content=="<div></div>") || (empty($custom3_content))) $custom3_content="";
        }
        if($show_custom4) {
            $custom4_content = $row['custom4_content'];
            if(($custom4_content=="<div></div>") || (empty($custom4_content))) $custom4_content="";
        }
        if($show_custom5) {
            $custom5_content = $row['custom5_content'];
            if(($custom5_content=="<div></div>") || (empty($custom5_content))) $custom5_content="";
        }
        if($show_location) {
            $location_content = $row['location_content'];
            if($location_content=="|" || empty($location_content)) $location_content="";
        }
    }
}
ob_end_clean();
echo json_encode(array("custom_box"=>$custom_content,"custom2_box"=>$custom2_content,"custom3_box"=>$custom3_content,"custom4_box"=>$custom4_content,"custom5_box"=>$custom5_content,"location_box"=>$location_content));