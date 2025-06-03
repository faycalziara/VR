<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}
require(__DIR__.'/ssp.class.php');
require(__DIR__.'/../functions.php');
$id_user = $_SESSION['id_user'];
if(get_user_role($id_user)!='administrator') {
    die();
}
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
$generic_label = _("Generic");
$tour_generic_label = _("Tour Add-ons");
$tour_service_label = _("Tour Creation");
$id_user_edit = (int)$_POST['id_user_edit'];
$query = "SELECT l.id,l.date_time,COALESCE(sl.name,s.name) as name,IF(vt.name IS NULL,'--',vt.name) as vt_name,IF(l.note IS NULL OR l.note='','--',l.note) as note,l.credits_used,IF(l.rooms_num IS NULL,'--',l.rooms_num) as rooms_num,l.price,l.currency,s.type FROM svt_services_log as l 
    LEFT JOIN svt_services as s ON s.id=l.id_service 
    LEFT JOIN svt_services_lang as sl ON sl.id_service = s.id AND sl.language='$language' 
    LEFT JOIN svt_virtualtours as vt ON vt.id = l.id_virtualtour
    WHERE l.id_user=$id_user_edit
    ORDER BY l.date_time DESC";
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
    array( 'db' => 'name',  'dt' =>0 ),
    array( 'db' => 'type',  'dt' =>1, 'formatter' => function( $d, $row ) {
        global $generic_label,$tour_generic_label,$tour_service_label;
        switch ($d) {
            case 'tour_service':
                $d = $tour_service_label;
                break;
            case 'tour_generic':
                $d = $tour_generic_label;
                break;
            case 'generic':
                $d = $generic_label;
                break;
            default:
                $d = "--";
                break;
        }
        return $d;
    }),
    array( 'db' => 'vt_name' ,'dt' =>2 ),
    array( 'db' => 'rooms_num' ,'dt' =>3 ),
    array( 'db' => 'price' ,'dt' =>4, 'formatter' => function( $d, $row ) {
        if(empty($d)) { $d="--"; } else {
            $d = format_currency($row['currency'],$d);
        }
        return $d;
    }),
    array( 'db' => 'credits_used' ,'dt' =>5, 'formatter' => function( $d, $row ) {
        if(empty($d)) { $d="--"; }
        return $d;
    }),
    array( 'db' => 'note' ,'dt' =>6 ),
    array( 'db' => 'date_time', 'dt' =>7, 'formatter' => function( $d, $row ) {
        global $language;
        $date_time = formatTime("dd MMM y - HH:mm",$language,strtotime($d));
        return $date_time;
    }),
);
$sql_details = array(
    'user' => DATABASE_USERNAME,
    'pass' => DATABASE_PASSWORD,
    'db' => DATABASE_NAME,
    'host' => DATABASE_HOST);
echo json_encode(
    SSP::simple( $_POST, $sql_details, $table, $primaryKey, $columns )
);