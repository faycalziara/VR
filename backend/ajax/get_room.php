<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$s3_params = check_s3_tour_enabled($_SESSION['id_virtualtour_sel']);
$s3_enabled = false;
if(!empty($s3_params)) {
    $s3_bucket_name = $s3_params['bucket'];
    $s3_region = $s3_params['region'];
    $s3_url = init_s3_client($s3_params);
    if($s3_url!==false) {
        $s3_enabled = true;
    }
}
session_write_close();
$id = (int)$_POST['id'];
$row = array();
$query = "SELECT r.*,v.enable_multires FROM svt_rooms as r JOIN svt_virtualtours as v ON v.id=r.id_virtualtour WHERE r.id=$id LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        if($row['enable_multires']) {
            $room_pano = str_replace('.jpg','',$row['panorama_image']);
            if($s3_enabled) {
                $multires_config_file = "s3://$s3_bucket_name/viewer/panoramas/multires/$room_pano/config.json";
            } else {
                $multires_config_file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'panoramas'.DIRECTORY_SEPARATOR.'multires'.DIRECTORY_SEPARATOR.$room_pano.DIRECTORY_SEPARATOR.'config.json';
            }
            $room['multires_config_file']=$multires_config_file;
            if(file_exists($multires_config_file)) {
                $multires_tmp = file_get_contents($multires_config_file);
                $multires_array = json_decode($multires_tmp,true);
                $multires_config = $multires_array['multiRes'];
                if($s3_enabled) {
                    $multires_config['basePath'] = $s3_url.'viewer/panoramas/multires/'.$room_pano;
                } else {
                    $multires_config['basePath'] = '../viewer/panoramas/multires/'.$room_pano;
                }
                $row['multires']=1;
                $row['multires_config']=json_encode($multires_config);
                if($s3_enabled) {
                    $row['multires_dir'] = $s3_url.'viewer/panoramas/multires/'.$room_pano;
                } else {
                    $row['multires_dir']='../viewer/panoramas/multires/'.$room_pano;
                }
            } else {
                $row['multires']=0;
                $row['multires_config']='';
                $row['multires_dir']='';
            }
        } else {
            $row['multires']=0;
            $row['multires_config']='';
            $row['multires_dir']='';
        }
    }
}
ob_end_clean();
echo json_encode($row);