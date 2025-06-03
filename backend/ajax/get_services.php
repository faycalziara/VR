<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}
require(__DIR__.'/ssp.class.php');
require(__DIR__.'/../../config/config.inc.php');
require(__DIR__.'/../functions.php');
$settings = get_settings();
$user_info = get_user_info($_SESSION['id_user']);
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
$for_image_label = _("for room");
$id_map = $_GET['id_map'];
$query = "SELECT s.*, 0 as purchased FROM svt_services as s";
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
    array( 'db' => 'position',  'dt' =>0 ),
    array( 'db' => 'name',  'dt' =>1 ),
    array( 'db' => 'type',  'dt' =>2, 'formatter' => function( $d, $row ) {
        global $generic_label, $tour_generic_label, $tour_service_label;
        switch ($d) {
            case 'generic':
                return $generic_label;
                break;
            case 'tour_generic':
                return $tour_generic_label;
                break;
            case 'tour_service':
                return $tour_service_label;
                break;
        }
    }),
    array( 'db' => 'price',  'dt' =>3, 'formatter' => function( $d, $row ) {
        global $for_image_label;
        if($d==0) {
            return "<i class='fa fa-times'></i>";
        } else {
            $price = format_currency($row['currency'],$d);
            switch ($row['type']) {
                case 'generic':
                case 'tour_generic':
                    return "<span style='white-space: nowrap'>".$price."</span>";
                    break;
                case 'tour_service':
                    return "<span style='white-space: nowrap'>".$price." (".$for_image_label.")</span>";
                    break;
            }
        }
    }),
    array( 'db' => 'credits',  'dt' =>4, 'formatter' => function( $d, $row ) {
        global $for_image_label;
        if($d==0) {
            return "<i class='fa fa-times'></i>";
        } else {
            switch ($row['type']) {
                case 'generic':
                case 'tour_generic':
                    return "<span style='white-space: nowrap'>".$d."</span>";
                    break;
                case 'tour_service':
                    return "<span style='white-space: nowrap'>".$d." (".$for_image_label.")</span>";
                    break;
            }
        }
    }),
    array( 'db' => 'block_tour',  'dt' =>5, 'formatter' => function( $d, $row ) {
        if($d) {
            return "<i class='fa fa-check'></i>";
        } else {
            return "<i class='fa fa-times'></i>";
        }
    }),
    array( 'db' => 'visible',  'dt' =>6, 'formatter' => function( $d, $row ) {
        if($d) {
            return "<i class='fa fa-check'></i>";
        } else {
            return "<i class='fa fa-times'></i>";
        }
    }),
    array( 'db' => 'purchased',  'dt' =>7 ),
);
$sql_details = array(
    'user' => DATABASE_USERNAME,
    'pass' => DATABASE_PASSWORD,
    'db' => DATABASE_NAME,
    'host' => DATABASE_HOST);
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);