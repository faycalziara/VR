<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip']) && ($_SESSION['id_user']==$_SESSION['demo_user_id'])) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_user = $_SESSION['id_user'];
$id_virtualtour = $_SESSION['id_virtualtour_sel'];
if(!check_elem_ownership($id_user,$id_virtualtour)) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    die();
}
session_write_close();
$p = strip_tags($_POST['p']);
$rotateX = (int)$_POST['rotateX'];
$rotateZ = (int)$_POST['rotateZ'];
$size = (float)$_POST['size'];
$scale = (int)$_POST['scale'];
$sticky = (int)$_POST['sticky'];
$apply_perspective = (int)$_POST['apply_perspective'];
$apply_size = (int)$_POST['apply_size'];
$apply_scale = (int)$_POST['apply_scale'];
$apply_sticky = (int)$_POST['apply_sticky'];
$set_as_default = (int)$_POST['set_as_default'];
$query_add = "";
if($apply_perspective==1) {
    $query_add .= "rotateX=$rotateX,rotateZ=$rotateZ,";
}
if($apply_size==1) {
    $query_add .= "size_scale=$size,";
}
if($apply_scale==1) {
    $query_add .= "scale=$scale,";
}
if($apply_sticky==1) {
    $query_add .= "sticky=$sticky,";
}
$query_add = rtrim($query_add,",");
switch ($p) {
    case 'markers':
        $query_a = "UPDATE svt_markers SET $query_add WHERE embed_type IS NULL AND id_room IN (SELECT DISTINCT id FROM svt_rooms WHERE id_virtualtour=$id_virtualtour);";
        if($set_as_default==1) {
            $query_b_add = "";
            if($apply_perspective==1) {
                $query_b_add .= "markers_default_rotateX=$rotateX,markers_default_rotateZ=$rotateZ,";
            }
            if($apply_size==1) {
                $query_b_add .= "markers_default_size_scale=$size,";
            }
            if($apply_scale==1) {
                $query_b_add .= "markers_default_scale=$scale,";
            }
            if($apply_sticky==1) {
                $query_b_add .= "markers_default_sticky=$sticky,";
            }
            if(!empty($query_b_add)) {
                $query_b_add = rtrim($query_b_add,",");
                $query_b = "UPDATE svt_virtualtours SET $query_b_add WHERE id=$id_virtualtour;";
            }
        }
        break;
    case 'pois':
        $query_a = "UPDATE svt_pois SET $query_add WHERE (type NOT IN ('grouped','callout') OR type IS NULL) AND embed_type IS NULL AND id_room IN (SELECT DISTINCT id FROM svt_rooms WHERE id_virtualtour=$id_virtualtour);";
        if($set_as_default==1) {
            $query_b_add = "";
            if($apply_perspective==1) {
                $query_b_add .= "pois_default_rotateX=$rotateX,pois_default_rotateZ=$rotateZ,";
            }
            if($apply_size==1) {
                $query_b_add .= "pois_default_size_scale=$size,";
            }
            if($apply_scale==1) {
                $query_b_add .= "pois_default_scale=$scale,";
            }
            if($apply_sticky==1) {
                $query_b_add .= "pois_default_sticky=$sticky,";
            }
            if(!empty($query_b_add)) {
                $query_b_add = rtrim($query_b_add,",");
                $query_b = "UPDATE svt_virtualtours SET $query_b_add WHERE id=$id_virtualtour;";
            }
        }
        break;
}
$result_a = $mysqli->query($query_a);
if($result_a) {
    if(!empty($query_b)) {
        $result_b = $mysqli->query($query_b);
        if($result_b) {
            ob_end_clean();
            echo json_encode(array("status"=>"ok"));
            exit;
        } else {
            ob_end_clean();
            echo json_encode(array("status"=>"error","msg"=>$mysqli->error));
            exit;
        }
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"ok"));
        exit;
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>$mysqli->error));
}