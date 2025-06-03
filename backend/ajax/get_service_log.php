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
$query = "SELECT l.*,s.name as service_name,s.type as service_type,v.name as tour_name FROM svt_services_log as l
           JOIN svt_services as s ON l.id_service = s.id
           LEFT JOIN svt_virtualtours as v ON l.id_virtualtour = v.id
           WHERE l.id=$id LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row=$result->fetch_array(MYSQLI_ASSOC);
        $return=$row;
    }
}
ob_end_clean();
echo json_encode($return);