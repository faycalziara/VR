<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}
require(__DIR__.'/ssp.class.php');
require(__DIR__.'/../../config/config.inc.php');
require(__DIR__.'/../functions.php');
$id_user = $_SESSION['id_user'];
session_write_close();
$settings = get_settings();
$default_language = $settings['language'];
switch(get_user_role($id_user)) {
    case 'administrator':
        $where = "";
        break;
    case 'customer':
        $where = " WHERE s.id_user=$id_user ";
        break;
    default:
        exit;
}
$query = "SELECT s.id,s.name,CONCAT(s.language,'|',s.languages_enabled) as languages,COUNT(l.id_virtualtour) as vt_count,u.username FROM svt_showcases AS s
LEFT JOIN svt_showcase_list AS l ON l.id_showcase=s.id
LEFT JOIN svt_users as u ON u.id=s.id_user
$where
GROUP BY s.id";
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
    array( 'db' => 'username',  'dt' =>1 ),
    array( 'db' => 'languages',  'dt' =>2, 'formatter' => function( $d, $row ) {
        global $default_language;
        $tmp = explode('|', $d);
        $language = $tmp[0];
        if(empty($language)) {
            $language = $default_language;
        }
        $languages_enabled = $tmp[1];
        $html_lang = "<img class='lang_showcase_list' src='img/flags_lang/$language.png?v=2' />";
        if(!empty($languages_enabled)) {
            $languages_enabled = json_decode($languages_enabled,true);
            foreach($languages_enabled as $language => $enabled) {
                if($language != $default_language) {
                    if($enabled==1) {
                        $html_lang .= "<img class='lang_showcase_list' src='img/flags_lang/$language.png?v=2' />";
                    }
                }
            }
        }
        return $html_lang;
    }),
    array( 'db' => 'vt_count',  'dt' =>3 ),
);
$sql_details = array(
    'user' => DATABASE_USERNAME,
    'pass' => DATABASE_PASSWORD,
    'db' => DATABASE_NAME,
    'host' => DATABASE_HOST);
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);