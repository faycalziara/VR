<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}
if(($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip']) && ($_SESSION['id_user']==$_SESSION['demo_user_id'])) {
    $demo = true;
} else {
    $demo = false;
}
require_once("../functions.php");
require_once("../../db/connection.php");
$id_virtualtour = (int)$_GET['id_virtualtour'];
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
require(__DIR__.'/ssp.class.php');
require(__DIR__.'/../../config/config.inc.php');
$query = "SELECT l.id,l.email,l.date_time,l.last_date_time,
            (SELECT COUNT(DISTINCT svt_learning_poi_log.id_room) FROM svt_learning_poi_log JOIN svt_pois ON svt_pois.id=svt_learning_poi_log.id_poi AND svt_pois.learning=1 AND svt_pois.type IS NOT NULL AND svt_pois.type <> '' AND svt_pois.type!='grouped' WHERE visited=1 AND id_learning=l.id) AS rooms_visited,
            (SELECT COUNT(DISTINCT svt_learning_poi_log.id_room) FROM svt_learning_poi_log JOIN svt_pois ON svt_pois.id=svt_learning_poi_log.id_poi AND svt_pois.learning=1 AND svt_pois.type IS NOT NULL AND svt_pois.type <> '' AND svt_pois.type!='grouped' WHERE id_learning=l.id) AS rooms_tot,
            (SELECT COUNT(id) as num FROM svt_pois WHERE learning=1 AND type IS NOT NULL AND type <> '' AND type!='grouped' AND id_room IN (SELECT id FROM svt_rooms WHERE id_virtualtour=$id_virtualtour)) AS tot_num_pois, 
            (SELECT COUNT(*) FROM svt_learning_poi_log JOIN svt_pois ON svt_pois.id=svt_learning_poi_log.id_poi AND svt_pois.learning=1 AND svt_pois.type IS NOT NULL AND svt_pois.type <> '' AND svt_pois.type!='grouped' WHERE id_learning=l.id AND visited=1) AS score 
             FROM svt_learning_log as l WHERE l.id_virtualtour=$id_virtualtour AND l.id IN (SELECT DISTINCT id_learning FROM svt_learning_poi_log)";
$table = "( $query ) t";
$primaryKey = 'id';
$columns = array(
    array(
        'db' => 'id',
        'dt' => 'DT_RowId',
        'formatter' => function( $d, $row ) {
            return $d;
        }
    ),
    array( 'db' => 'email',  'dt' =>0, 'formatter' => function( $d, $row ) {
        global $demo;
        if(empty($d)) {
            return "--";
        } else {
            if($demo) {
                return obfuscateEmail($d);
            } else {
                return $d;
            }
        }
    }),
    array( 'db' => 'date_time',  'dt' =>1, 'formatter' => function( $d, $row ) {
        global $language;
        return formatTime("dd MMM y - HH:mm",$language,strtotime($d));
    }),
    array( 'db' => 'last_date_time',  'dt' =>2, 'formatter' => function( $d, $row ) {
        global $language;
        return formatTime("dd MMM y - HH:mm",$language,strtotime($d));
    }),
    array( 'db' => 'rooms_visited',  'dt' =>3, 'formatter' => function( $d, $row ) {
        $rooms_tot = $row['rooms_tot'];
        $percentage = ($rooms_tot > 0) ? ($d / $rooms_tot) * 100 : 0;
        return '<div class="progress" style="height: 20px;">
                <div class="progress-bar bg-info" role="progressbar" style="width: '.$percentage.'%;" aria-valuenow="'.$d.'" aria-valuemin="0" aria-valuemax="'.$rooms_tot.'">
                    '.$d.' / '.$rooms_tot.'
                </div>
            </div>';
    }),
    array( 'db' => 'score',  'dt' =>4, 'formatter' => function( $d, $row ) {
        $tot_num_pois = $row['tot_num_pois'];
        $percentage = ($tot_num_pois > 0) ? ($d / $tot_num_pois) * 100 : 0;
        return '<div class="progress" style="height: 20px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: '.$percentage.'%;" aria-valuenow="'.$d.'" aria-valuemin="0" aria-valuemax="'.$tot_num_pois.'">
                    '.$d.' / '.$tot_num_pois.'
                </div>
            </div>';
    }),
);
$sql_details = array(
    'user' => DATABASE_USERNAME,
    'pass' => DATABASE_PASSWORD,
    'db' => DATABASE_NAME,
    'host' => DATABASE_HOST);
ob_end_clean();
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);