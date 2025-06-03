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
$id_poi = (int)$_POST['id_poi'];
if(!check_elem_ownership($id_user,$id_virtualtour,'poi',$id_poi)) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    die();
}
session_write_close();
$id = (int)$_POST['id'];
$tooltip = strip_tags($_POST['tooltip']);
$default = (int)$_POST['default'];
$icon = strip_tags($_POST['icon']);
$image = strip_tags($_POST['image']);
$array_lang = json_decode($_POST['array_lang'],true);
if($id==0) {
    $query = "INSERT INTO svt_poi_staging (id_poi,tooltip,`default`,icon,image) VALUES (?,?,?,?,?)";
    if($smt = $mysqli->prepare($query)) {
        $smt->bind_param('isiss',$id_poi,$tooltip,$default,$icon,$image);
        $result = $smt->execute();
        if ($result) {
            $id = $mysqli->insert_id;
            if($default == 1) {
                $mysqli->query("UPDATE svt_poi_staging SET `default`=0 WHERE id_poi=$id_poi AND id!=$id;");
            }
        } else {
            ob_end_clean();
            echo json_encode(array("status"=>"error"));
            exit;
        }
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error"));
        exit;
    }
} else {
    $query = "UPDATE svt_poi_staging SET tooltip=?,`default`=?,icon=?,image=? WHERE id=? AND id_poi=?;";
    if($smt = $mysqli->prepare($query)) {
        $smt->bind_param('sissii',$tooltip,$default,$icon,$image,$id,$id_poi);
        $result = $smt->execute();
        if ($result) {
            if($default == 1) {
                $mysqli->query("UPDATE svt_poi_staging SET `default`=0 WHERE id_poi=$id_poi AND id!=$id;");
            }
        } else {
            ob_end_clean();
            echo json_encode(array("status"=>"error"));
            exit;
        }
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error"));
        exit;
    }
}
save_input_langs($array_lang,'svt_poi_staging_lang','id_staging',$id);
$array_staging_lang = array();
$query = "SELECT * FROM svt_poi_staging_lang WHERE id_staging IN(SELECT id FROM svt_poi_staging WHERE id_poi=$id_poi);";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_staging=$row['id_staging'];
            if(!array_key_exists($id_staging,$array_staging_lang)) $array_staging_lang[$id_staging]=array();
            array_push($array_staging_lang[$id_staging],$row);
        }
    }
}
$staging_items = array();
$query = "SELECT * FROM svt_poi_staging WHERE id_poi=$id_poi;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows > 0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_staging = $row['id'];
            if(array_key_exists($id_staging,$array_staging_lang)) {
                $row['array_lang']=$array_staging_lang[$id_staging];
            } else {
                $row['array_lang']=array();
            }
            array_push($staging_items, $row);
        }
    }
}
ob_end_clean();
echo json_encode(array("status"=>"ok","staging_items"=>$staging_items));
exit;