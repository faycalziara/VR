<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}
require_once("../../db/connection.php");
session_write_close();
$id_poi = (int)$_POST['id_poi'];
$array = array();
$query = "SELECT * FROM svt_poi_gallery WHERE id_poi=$id_poi ORDER BY priority;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $row['array_lang'] = array();
            $query_l = "SELECT * FROM svt_poi_gallery_lang WHERE id_poi_gallery=".$row['id'];
            $result_l = $mysqli->query($query_l);
            if($result_l) {
                if ($result_l->num_rows > 0) {
                    while ($row_l = $result_l->fetch_array(MYSQLI_ASSOC)) {
                        $row['array_lang'][]=$row_l;
                    }
                }
            }
            if(empty($row['title'])) $row['title']="";
            if(empty($row['description'])) $row['description']="";
            $row['visible']=(int)$row['visible'];
            $array[]=$row;
        }
    }
}
ob_end_clean();
echo json_encode($array);