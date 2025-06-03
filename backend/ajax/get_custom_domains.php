<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}
require(__DIR__.'/ssp.class.php');
require_once(__DIR__."/../functions.php");
$id_user = (int)$_SESSION['id_user'];
session_write_close();
$user_info = get_user_info($id_user);
$settings = get_settings();
if(!isset($_SESSION['lang'])) {
    if(!empty($user_info['language'])) {
        $language = $user_info['language'];
    } else {
        $language = $settings['language'];
    }
} else {
    $language = $_SESSION['lang'];
}
$user_role = $user_info['role'];
$status_waiting_label = _("Pending approval");
$status_approved_label = _("Approved");
$status_rejected_label = _("Rejected");
$status_remove_label = _("Pending deletion");
switch ($user_role) {
    case 'administrator':
        $query = "SELECT d.id,d.date_time,CONCAT(d.status,',',d.id_user) as status,d.custom_domain,u.username,CONCAT(COUNT(DISTINCT t.id_virtualtour),',',COUNT(DISTINCT s.id_showcase),',',COUNT(DISTINCT g.id_globe),',',d.default_tour,',',d.default_showcase,',',d.default_globe) as count_connected
                    FROM svt_custom_domains as d
                    JOIN svt_users as u ON u.id=d.id_user
                    LEFT JOIN svt_custom_domains_tours_assoc as t ON t.id_custom_domain=d.id
                    LEFT JOIN svt_custom_domains_showcase_assoc as s ON s.id_custom_domain=d.id
                    LEFT JOIN svt_custom_domains_globe_assoc as g ON g.id_custom_domain=d.id
                    GROUP BY d.id,d.date_time,d.custom_domain,u.username";
        break;
    default:
        $query = "SELECT d.id,d.date_time,CONCAT(d.status,',',d.id_user) as status,d.custom_domain,u.username,CONCAT(COUNT(DISTINCT t.id_virtualtour),',',COUNT(DISTINCT s.id_showcase),',',COUNT(DISTINCT g.id_globe),',',d.default_tour,',',d.default_showcase,',',d.default_globe) as count_connected 
                    FROM svt_custom_domains as d 
                    JOIN svt_users as u ON u.id=d.id_user 
                    LEFT JOIN svt_custom_domains_tours_assoc as t ON t.id_custom_domain=d.id
                    LEFT JOIN svt_custom_domains_showcase_assoc as s ON s.id_custom_domain=d.id
                    LEFT JOIN svt_custom_domains_globe_assoc as g ON g.id_custom_domain=d.id
                    WHERE d.id_user=$id_user
                    GROUP BY d.id,d.date_time,d.custom_domain,u.username";
        break;
}
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
    array( 'db' => 'date_time',  'dt' =>0, 'formatter' => function( $d, $row ) {
        global $language;
        if(empty($d)) {
            return "--";
        } else {
            return "<span style='display:none;'>".strtotime($d)."</span>".formatTime("dd MMM y",$language,strtotime($d));
        }
    }),
    array( 'db' => 'username',  'dt' =>1 ),
    array( 'db' => 'custom_domain',  'dt' =>2, 'formatter' => function( $d, $row ) {
        return '<span class="badge badge-light"><i class="fa-solid fa-globe"></i>&nbsp;&nbsp;'.$d.'</span>';
    }),
    array( 'db' => 'status',  'dt' =>3, 'formatter' => function( $d, $row ) {
        global $status_waiting_label, $status_approved_label, $status_rejected_label, $status_remove_label;
        $status_tmp = explode(",", $d);
        $status = $status_tmp[0];
        $status_return = "";
        switch($status) {
            case 0:
                $status_return = '<span class="badge badge-warning"><i class="fa-solid fa-clock"></i>&nbsp;&nbsp;'.$status_waiting_label.'</span>';
                break;
            case 1:
                $status_return = '<span class="badge badge-success"><i class="fa-solid fa-circle-check"></i>&nbsp;&nbsp;'.$status_approved_label.'</span>';
                break;
            case -1:
                $status_return = '<span class="badge badge-danger"><i class="fa-solid fa-circle-xmark"></i>&nbsp;&nbsp;'.$status_rejected_label.'</span>';
                break;
            case -2:
                $status_return = '<span class="badge badge-dark"><i class="fa-solid fa-circle-xmark"></i>&nbsp;&nbsp;'.$status_remove_label.'</span>';
                break;
        }
        return $status_return;
    }),
    array( 'db' => 'count_connected',  'dt' =>4, 'formatter' => function( $d, $row ) {
        $connected = "";
        $count_connected = explode(",", $d);
        $default_tour = $count_connected[3];
        $default_showcase = $count_connected[4];
        $default_globe = $count_connected[5];
        $status_tmp = explode(",", $row['status']);
        $status = $status_tmp[0];
        switch($status) {
            case 1:
            case -2:
                $connected = "<div><i class='fas fa-route'></i>&nbsp;<i class='".(($default_tour) ? 'fas' : 'far')." fa-star'></i>&nbsp;{$count_connected[0]}</div><div><i class='fas fa-object-group'></i>&nbsp;<i class='".(($default_showcase) ? 'fas' : 'far')." fa-star'></i>&nbsp;{$count_connected[1]}</div><div><i class='fas fa-globe-americas'></i>&nbsp;<i class='".(($default_globe) ? 'fas' : 'far')." fa-star'></i>&nbsp;{$count_connected[2]}</div>";
                break;
        }
        return $connected;
    }),
    array( 'db' => 'status',  'dt' =>5, 'formatter' => function( $d, $row ) {
        global $user_role, $id_user;
        $status_tmp = explode(",", $d);
        $status = $status_tmp[0];
        $id_user_d = $status_tmp[1];
        $custom_domain = $row["custom_domain"];
        $id = $row['id'];
        $count_connected = explode(",", $row['count_connected']);
        $default_tour = $count_connected[3];
        $default_showcase = $count_connected[4];
        $default_globe = $count_connected[5];
        $actions = "";
        switch($user_role) {
            case 'customer':
                switch($status) {
                    case 0:
                        $actions .= "<button onclick=\"open_verify_custom_domain('$custom_domain');\" title='"._("VERIFY")."' class='btn btn-sm btn-primary mx-1 tooltip_btn'><i class='fas fa-search'></i></button>";
                        break;
                    case 1:
                        $actions .= "<button onclick=\"open_verify_custom_domain('$custom_domain');\" title='"._("VERIFY")."' class='btn btn-sm btn-primary mx-1 tooltip_btn'><i class='fas fa-search'></i></button>";
                        $actions .= "<button onclick=\"open_set_defaults_custom_domain($id,'$custom_domain',$default_tour,$default_showcase,$default_globe);\" title='"._("SET DEFAULTS")."' class='btn btn-sm btn-primary mx-1 tooltip_btn'><i class='fas fa-star'></i></button>";
                        $actions .= "<button onclick=\"open_apply_bulk_custom_domain($id,'$custom_domain');\" title='"._("APPLY")."' class='btn btn-sm btn-primary mx-1 tooltip_btn'><i class='fas fa-check-double'></i></button>";
                        $actions .= "<button onclick=\"save_custom_domain($id,'remove');\" title='"._("DELETE")."' class='btn btn-sm btn-danger mx-1 tooltip_btn'><i class='fas fa-trash'></i></button>";
                        break;
                }
                break;
            case 'administrator':
                switch($status) {
                    case 0:
                        $actions .= "<button onclick=\"open_verify_custom_domain('$custom_domain');\" title='"._("VERIFY")."' class='btn btn-sm btn-primary mx-1 tooltip_btn'><i class='fas fa-search'></i></button>";
                        $actions .= "<button onclick=\"save_custom_domain($id,'approve');\" title='"._("APPROVE")."' class='btn btn-sm btn-success mx-1 tooltip_btn'><i class='fas fa-check'></i></button>";
                        $actions .= "<button onclick=\"save_custom_domain($id,'reject');\" title='"._("REJECT")."' class='btn btn-sm btn-dark mx-1 tooltip_btn'><i class='fas fa-xmark'></i></button>";
                        break;
                    case 1:
                        $actions .= "<button onclick=\"open_verify_custom_domain('$custom_domain');\" title='"._("VERIFY")."' class='btn btn-sm btn-primary mx-1 tooltip_btn'><i class='fas fa-search'></i></button>";
                        if($id_user==$id_user_d) {
                            $actions .= "<button onclick=\"open_set_defaults_custom_domain($id,'$custom_domain',$default_tour,$default_showcase,$default_globe);\" title='"._("SET DEFAULTS")."' class='btn btn-sm btn-primary mx-1 tooltip_btn'><i class='fas fa-star'></i></button>";
                            $actions .= "<button onclick=\"open_apply_bulk_custom_domain($id,'$custom_domain');\" title='"._("APPLY")."' class='btn btn-sm btn-primary mx-1 tooltip_btn'><i class='fas fa-check-double'></i></button>";
                        }
                        $actions .= "<button onclick=\"delete_custom_domain($id);\" title='"._("DELETE")."' class='btn btn-sm btn-danger mx-1 tooltip_btn'><i class='fas fa-trash'></i></button>";
                        break;
                    case -1:
                    case -2:
                        $actions .= "<button onclick=\"delete_custom_domain($id);\" title='"._("DELETE")."' class='btn btn-sm btn-danger mx-1 tooltip_btn'><i class='fas fa-trash'></i></button>";
                    break;
                }
                break;
        }
        return $actions;
    }),
);
$sql_details = array(
    'user' => DATABASE_USERNAME,
    'pass' => DATABASE_PASSWORD,
    'db' => DATABASE_NAME,
    'host' => DATABASE_HOST);
echo json_encode(
    SSP::simple( $_GET, $sql_details, $table, $primaryKey, $columns )
);