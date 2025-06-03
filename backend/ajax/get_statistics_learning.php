<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}
require_once("../functions.php");
require_once("../../db/connection.php");
$id_virtualtour = (int)$_POST['id_virtualtour'];
$elem = $_POST["elem"];
$id_user = $_SESSION['id_user'];
$settings = get_settings();
$user_info = get_user_info($id_user);
if(!isset($_SESSION['lang'])) {
    if(!empty($user_info['language'])) {
        $language = $user_info['language'];
    } else {
        $language = $settings['language'];
    }
} else {
    $language = $_SESSION['lang'];
}
set_language($language,$settings['language_domain']);
session_write_close();
$stats = array();

switch($elem) {
    case 'summary':
        $stats['num_sessions']=0;
        $stats['num_rooms_visited'] = 0;
        $stats['num_pois_total'] = 0;
        $stats['num_pois_visited'] = 0;
        $stats['average_score'] = 0;
        $stats['total_score'] = 0;
        $query = "SELECT COUNT(id) AS num FROM svt_learning_log WHERE id_virtualtour=$id_virtualtour AND score_global>0; ";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows==1) {
                $row=$result->fetch_array(MYSQLI_ASSOC);
                $stats['num_sessions']=$row['num'];
            }
        }
        $query = "SELECT COUNT(id) as num FROM svt_pois WHERE learning=1 AND type IS NOT NULL AND type <> '' AND type!='grouped' AND id_room IN (SELECT id FROM svt_rooms WHERE id_virtualtour=$id_virtualtour);";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows==1) {
                $row=$result->fetch_array(MYSQLI_ASSOC);
                $stats['total_score']=$row['num'];
            }
        }
        $query = "SELECT COUNT(DISTINCT l.id_room,l.id_learning) AS num FROM svt_learning_poi_log as l 
                  JOIN svt_pois as p ON p.id=l.id_poi AND p.learning=1 AND p.type IS NOT NULL AND p.type <> '' AND p.type!='grouped'
                  WHERE l.visited=1 AND l.id_learning IN (SELECT id FROM svt_learning_log WHERE id_virtualtour=$id_virtualtour AND score_global>0); ";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows==1) {
                $row=$result->fetch_array(MYSQLI_ASSOC);
                $stats['num_rooms_visited']=$row['num'];
            }
        }
        $query = "SELECT COUNT(l.id_poi) AS num FROM svt_learning_poi_log as l
                  JOIN svt_pois as p ON p.id=l.id_poi AND p.learning=1 AND p.type IS NOT NULL AND p.type <> '' AND p.type!='grouped'
                  WHERE l.id_learning IN (SELECT id FROM svt_learning_log WHERE id_virtualtour=$id_virtualtour AND score_global>0)";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows==1) {
                $row=$result->fetch_array(MYSQLI_ASSOC);
                $stats['num_pois_total']=$row['num'];
            }
        }
        $query = "SELECT COUNT(l.id_poi) AS num FROM svt_learning_poi_log as l
                  JOIN svt_pois as p ON p.id=l.id_poi AND p.learning=1 AND p.type IS NOT NULL AND p.type <> '' AND p.type!='grouped'
                  WHERE l.visited=1 AND l.id_learning IN (SELECT id FROM svt_learning_log WHERE id_virtualtour=$id_virtualtour AND score_global>0)";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows==1) {
                $row=$result->fetch_array(MYSQLI_ASSOC);
                $stats['num_pois_visited']=$row['num'];
            }
        }
        $stats['average_score'] = number_format(($stats['num_pois_visited'])/$stats['num_sessions'],1);
        break;
    case 'chart_learning_sessions':
        $stats['labels'] = array();
        $stats['data'] = array();
        $query = "SELECT COUNT(*) as num,DAY(date_time) as d,MONTH(date_time) as m,YEAR(date_time) as y FROM svt_learning_log 
                    WHERE id_virtualtour=$id_virtualtour AND score_global>0
                    GROUP BY DAY(date_time),MONTH(date_time),YEAR(date_time);";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows>0) {
                while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $date_time = strtotime($row['y']."-".$row['m']."-".$row['d'])*1000;
                    $num = intval($row['num']);
                    $tmp = array();
                    $tmp[0]=$date_time;
                    $tmp[1]=$num;
                    $stats['data'][] = $tmp;
                }
            }
        }
        usort($stats['data'], 'sortByOrder');
        break;
}
ob_end_clean();
echo json_encode($stats);

function sortByOrder($a, $b) {
    return $a[0] - $b[0];
}