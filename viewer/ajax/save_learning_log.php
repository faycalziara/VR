<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if(empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    die();
}
require_once("../../db/connection.php");
require_once("../../backend/functions.php");
$settings = get_settings();
if(!empty($settings['timezone'])) {
    date_default_timezone_set($settings['timezone']);
}
session_write_close();
$id_learning = (int)$_POST['id_learning'];
$id_virtualtour = (int)$_POST['id_virtualtour'];
$ip_visitor = strip_tags($_POST['ip_visitor']);
$email = strip_tags($_POST['email']);
$score_global = (int)$_POST['score_global'];
$learning_poi_room = strip_tags($_POST['learning_poi_room']);
$score_global_exist = 0;
$pois_visited = array();
if($id_learning == 0) {
    if (!empty($email)) {
        $query_check = "SELECT id,score_global FROM svt_learning_log WHERE id_virtualtour=? AND email=? LIMIT 1;";
        $stmt_check = $mysqli->prepare($query_check);
        $stmt_check->bind_param("is", $id_virtualtour, $email);
    } else {
        $query_check = "SELECT id,score_global FROM svt_learning_log WHERE id_virtualtour=? AND ip=? LIMIT 1;";
        $stmt_check = $mysqli->prepare($query_check);
        $stmt_check->bind_param("is", $id_virtualtour, $ip_visitor);
    }
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();
    if ($result_check && $result_check->num_rows == 1) {
        $row_check = $result_check->fetch_array(MYSQLI_ASSOC);
        $id_learning = $row_check['id'];
        $score_global_exist = $row_check['score_global'];
    } else {
        $query_insert = "INSERT INTO svt_learning_log (ip,email,date_time,last_date_time,id_virtualtour,score_global) 
                         VALUES (?,?,NOW(),NOW(),?,0);";
        $stmt_insert = $mysqli->prepare($query_insert);
        $stmt_insert->bind_param("ssi", $ip_visitor, $email, $id_virtualtour);
        $stmt_insert->execute();
        $id_learning = $stmt_insert->insert_id;
        $stmt_insert->close();
    }
    $stmt_check->close();
}
if($id_learning != 0 && $score_global!=$score_global_exist) {
    $mysqli->query("UPDATE svt_learning_log SET last_date_time=NOW(),score_global=$score_global WHERE id=$id_learning;");
}
if(empty($learning_poi_room)) {
    $query = "SELECT ll.id_room, ll.id_poi, ll.visited FROM svt_learning_poi_log as ll 
              JOIN svt_pois as p ON p.id = ll.id_poi AND p.learning=1 AND p.type IS NOT NULL AND p.type <> ''
              WHERE ll.id_learning=$id_learning
              ORDER BY p.learning_priority ASC;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows > 0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_room = (int)$row['id_room'];
                $id_poi = (int)$row['id_poi'];
                $visited = (int)$row['visited'];
                if (!isset($pois_visited["r".$id_room])) {
                    $pois_visited["r".$id_room] = [];
                }
                $pois_visited["r".$id_room]["p".$id_poi] = $visited;
            }
        }
    }
} else {
    $learning_poi_room = json_decode($learning_poi_room, true);
    foreach($learning_poi_room as $id_room => $pois) {
        foreach ($learning_poi_room as $id_room => $pois) {
            foreach ($pois as $id_poi => $visited) {
                $id_room = str_replace("r","",$id_room);
                $id_poi = str_replace("p","",$id_poi);
                $query = "INSERT INTO svt_learning_poi_log (id_learning, id_room, id_poi, visited)
                      VALUES (?, ?, ?, ?)
                      ON DUPLICATE KEY UPDATE visited = VALUES(visited);";
                $stmt = $mysqli->prepare($query);
                if ($stmt) {
                    $stmt->bind_param("iiii", $id_learning, $id_room, $id_poi, $visited);
                    $stmt->execute();
                    $stmt->close();
                }
            }
        }
    }
}
$mysqli->close();
ob_end_flush();
echo json_encode(array("status"=>"ok","id"=>$id_learning,"learning_poi_room"=>$pois_visited));