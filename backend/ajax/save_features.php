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
session_write_close();
if(!get_user_role($id_user)=='administrator') {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    exit;
}
$array_features = json_decode($_POST['array_features'],true);
$array_features_lang = json_decode($_POST['array_features_lang'],true);
foreach ($array_features as $array_feature) {
    $feature = $array_feature['feature'];
    $name = $array_feature['name'];
    $content = $array_feature['content'];
    if($content=='<p><br></p>') $content='';
    $query = "INSERT INTO svt_features(feature,name,content) VALUES(?,?,?) ON DUPLICATE KEY UPDATE content=VALUES(content),name=VALUES(name);";
    if($smt = $mysqli->prepare($query)) {
        $smt->bind_param('sss',$feature,$name,$content);
        $result = $smt->execute();
        if(!$result) {
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
$array_features_mapping = array();
$query = "SELECT id,feature FROM svt_features";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_feature = $row['id'];
            $name_feature = $row['feature'];
            $array_features_mapping[$name_feature] = $id_feature;
        }
    }
}
foreach ($array_features_lang as $array_feature_lang) {
    $lang = $array_feature_lang['lang'];
    $feature = $array_feature_lang['feature'];
    $name = $array_feature_lang['name'];
    $content = $array_feature_lang['content'];
    if ($content == '<p><br></p>') $content = '';
    $id_feature = $array_features_mapping[$feature];
    if(!empty($id_feature)) {
        $query = "INSERT INTO svt_features_lang(id_feature,language,name,content) VALUES(?,?,?,?) ON DUPLICATE KEY UPDATE content=VALUES(content),name=VALUES(name);";
        if($smt = $mysqli->prepare($query)) {
            $smt->bind_param('isss', $id_feature, $lang, $name, $content);
            $smt->execute();
        }
    }
}
ob_end_clean();
echo json_encode(array("status"=>"ok"));