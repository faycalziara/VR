<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
ob_start();
if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    die();
}
require_once("../../db/connection.php");
session_write_close();
$query = "SELECT * FROM svt_voice_commands LIMIT 1;";
$result = $mysqli->query($query);
$voice_commands = array();
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $voice_commands[] = $row;
        }
        ob_end_clean();
        echo json_encode(array("status"=>"ok","voice_commands"=>$voice_commands));
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error"));
    }
}