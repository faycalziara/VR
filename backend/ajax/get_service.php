<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}
require(__DIR__.'/../../db/connection.php');
require(__DIR__.'/../functions.php');
session_write_close();
$id = (int)$_POST['id'];
$settings = get_settings();
$return = array();
$query = "SELECT * FROM svt_services WHERE id=$id LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row=$result->fetch_array(MYSQLI_ASSOC);
        $row['array_input_lang'] = array();
        $query_lang = "SELECT * FROM svt_services_lang WHERE id_service=$id;";
        $result_lang = $mysqli->query($query_lang);
        if($result_lang) {
            if ($result_lang->num_rows > 0) {
                while($row_lang = $result_lang->fetch_array(MYSQLI_ASSOC)) {
                    $language = $row_lang['language'];
                    unset($row_lang['id_service']);
                    unset($row_lang['language']);
                    if(!empty($row_lang['name']) || (!empty($row_lang['description']) && $row_lang['description']!='<p><br></p>')) {
                        $row['array_input_lang'][$language]=$row_lang;
                    }
                }
            }
        }
        $return=$row;
    }
}
ob_end_clean();
echo json_encode($return);