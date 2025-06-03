<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
require_once("../../db/connection.php");
$id_virtualtour = (int)$_POST['id_virtualtour'];
$view = 0;
$query = "SELECT COUNT(*) as num FROM svt_access_log WHERE id_virtualtour=$id_virtualtour LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row=$result->fetch_array(MYSQLI_ASSOC);
        $view = $row['num'];
    }
}
ob_end_clean();
echo $view;