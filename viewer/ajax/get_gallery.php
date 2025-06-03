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
$array = array();
$array_gallery_lang = array();
if($count_languages_enabled > 1) {
    $query = "SELECT * FROM svt_gallery_lang WHERE language='$language' AND id_gallery IN (SELECT id FROM svt_gallery WHERE id_virtualtour=$id_virtualtour AND visible=1);";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_gallery = $row['id_gallery'];
                unset($row['id_map']);
                unset($row['language']);
                $array_gallery_lang[$id_gallery]=$row;
            }
        }
    }
}
$query = "SELECT * FROM svt_gallery WHERE id_virtualtour=$id_virtualtour AND visible=1 ORDER BY priority;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $id_gallery=$row['id'];
            if(empty($row['title'])) $row['title']="";
            if(empty($row['description'])) $row['description']="";
            if(array_key_exists($id_gallery,$array_gallery_lang)) {
                if (!empty($row['title']) && !empty($array_gallery_lang[$id_gallery]['title'])) {
                    $row['title'] = $array_gallery_lang[$id_gallery]['title'];
                }
                if (!empty($row['description']) && !empty($array_gallery_lang[$id_gallery]['description'])) {
                    $row['description'] = $array_gallery_lang[$id_gallery]['description'];
                }
            }
            $array[]=$row;
        }
    }
}
ob_end_clean();
echo json_encode($array);