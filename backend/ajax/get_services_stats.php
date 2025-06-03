<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_user = $_SESSION['id_user'];
$settings = get_settings();
$user_info = get_user_info($_SESSION['id_user']);
if($user_info['role']!='administrator') {
    die();
}
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
$stats['purchased_services'] = 0;
$stats['revenue_services'] = 0;
$stats['services'] = array();
$query = "SELECT s.id,s.name,SUM(l.credits_used) as credits_used,SUM(l.price) as price,MIN(l.currency) as currency,COUNT(l.id) as purchased,FORMAT((COUNT(l.id) /
            (SELECT COUNT(*) FROM svt_services_log)) * 100, 2) as percentage FROM svt_services_log as l 
    JOIN svt_services as s ON l.id_service=s.id
    GROUP BY s.id,s.name
    ORDER BY purchased DESC;";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $stats['purchased_services']=$stats['purchased_services']+$row['purchased'];
            if($row['price']>0) {
                $stats['revenue_services']=$stats['revenue_services']+$row['price'];
                $currency = $row['currency'];
            }
            $stats['services'][] = $row;
        }
        $stats['revenue_services'] = format_currency($currency,$stats['revenue_services']);
    }
}
ob_end_clean();
echo json_encode($stats);