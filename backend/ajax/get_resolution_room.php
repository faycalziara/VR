<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
require_once('../../services/getid3/getid3.php');
$getID3 = new getID3();
session_write_close();
$id_user = (int)$_POST['id_user'];
$id_virtualtour = (int)$_POST['id_virtualtour'];
$id_room = (int)$_POST['id_room'];
$stats = array();
$array_sizes = get_resolution_room($id_user,$id_virtualtour,$id_room);
$stats['resolution_original'] = $array_sizes[0];
$stats['resolution_compressed'] = $array_sizes[1];
ob_end_clean();
echo json_encode($stats);