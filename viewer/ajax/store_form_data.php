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
$form_data = $_POST;
$id_virtualtour = (int)$form_data['id_virtualtour'];
$id_room = (int)$form_data['id_room'];
$title = strip_tags($form_data['title']);
$email = strip_tags($form_data['email']);
if(isset($form_data['form_field_1'])) {
    $form_field_1 = $form_data['form_field_1'];
    $form_field_1 = strip_tags($form_field_1);
} else {
    $form_field_1 = "";
}
if(isset($form_data['form_field_2'])) {
    $form_field_2 = $form_data['form_field_2'];
    $form_field_2 = strip_tags($form_field_2);
} else {
    $form_field_2 = "";
}
if(isset($form_data['form_field_3'])) {
    $form_field_3 = $form_data['form_field_3'];
    $form_field_3 = strip_tags($form_field_3);
} else {
    $form_field_3 = "";
}
if(isset($form_data['form_field_4'])) {
    $form_field_4 = $form_data['form_field_4'];
    $form_field_4 = strip_tags($form_field_4);
} else {
    $form_field_4 = "";
}
if(isset($form_data['form_field_5'])) {
    $form_field_5 = $form_data['form_field_5'];
    $form_field_5 = strip_tags($form_field_5);
} else {
    $form_field_5 = "";
}
if(isset($form_data['form_field_6'])) {
    $form_field_6 = $form_data['form_field_6'];
    $form_field_6 = strip_tags($form_field_6);
} else {
    $form_field_6 = "";
}
if(isset($form_data['form_field_7'])) {
    $form_field_7 = $form_data['form_field_7'];
    $form_field_7 = strip_tags($form_field_7);
} else {
    $form_field_7 = "";
}
if(isset($form_data['form_field_8'])) {
    $form_field_8 = $form_data['form_field_8'];
    $form_field_8 = strip_tags($form_field_8);
} else {
    $form_field_8 = "";
}
if(isset($form_data['form_field_9'])) {
    $form_field_9 = $form_data['form_field_9'];
    $form_field_9 = strip_tags($form_field_9);
} else {
    $form_field_9 = "";
}
if(isset($form_data['form_field_10'])) {
    $form_field_10 = $form_data['form_field_10'];
    $form_field_10 = strip_tags($form_field_10);
} else {
    $form_field_10 = "";
}
if(!empty($_FILES)) {
    $fileCount = count($_FILES);
    if($fileCount>0) {
        if (!file_exists(dirname(__FILE__).'/../../backend/assets/form_files/')) {
            mkdir(dirname(__FILE__).'/../../backend/assets/form_files/', 0775);
        }
        foreach ($_FILES as $id_input => $file) {
            $timestamp = floor(microtime(true) * 1000);
            $file_name = $file['name'];
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $file_name_without_ext = pathinfo($file_name, PATHINFO_FILENAME);
            $new_file_name = $file_name_without_ext . '_' . $timestamp . '.' . $file_extension;
            $file_tmp = $file['tmp_name'];
            $destination = dirname(__FILE__).'/../../backend/assets/form_files/'.$new_file_name;
            if (move_uploaded_file($file_tmp, $destination)) {
                ${$id_input} = $file_name."|assets/form_files/".$new_file_name;
            }
        }
    }
}
$query = "INSERT INTO svt_forms_data(id_virtualtour,id_room,title,field1,field2,field3,field4,field5,field6,field7,field8,field9,field10,datetime) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?);";
if($smt = $mysqli->prepare($query)) {
    $smt->bind_param('iissssssssssss',  $id_virtualtour,$id_room,$title,$form_field_1,$form_field_2,$form_field_3,$form_field_4,$form_field_5,$form_field_6,$form_field_7,$form_field_8,$form_field_9,$form_field_10,$now);
    $result = $smt->execute();
    if ($result) {
        ob_end_clean();
        echo json_encode(array("status"=>"ok","email"=>$email));
        exit;
    }
}
ob_end_clean();
echo json_encode(array("status"=>"error"));
exit;