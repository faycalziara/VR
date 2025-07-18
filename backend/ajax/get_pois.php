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
$id_room = (int)$_POST['id_room'];
$edit_room = (int)$_POST['edit_room'];
$array = array();
$room = array();
$array_lang = array();
$query = "SELECT * FROM svt_pois_lang WHERE id_poi IN(SELECT id FROM svt_pois WHERE id_room=$id_room);";
$result = $mysqli->query($query);
if($result) {
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $id_poi=$row['id_poi'];
            if(!array_key_exists($id_poi,$array_lang)) $array_lang[$id_poi]=array();
            array_push($array_lang[$id_poi],$row);
        }
    }
}
$array_staging_lang = array();
$query = "SELECT * FROM svt_poi_staging_lang WHERE id_staging IN(SELECT id FROM svt_poi_staging WHERE id_poi IN(SELECT id FROM svt_pois WHERE id_room=$id_room));";
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
if($edit_room==0) {
    $query = "SELECT r.panorama_image,r.panorama_video,v.enable_multires,r.yaw,r.pitch,r.h_pitch,r.h_roll,r.allow_pitch,r.min_pitch,r.max_pitch,r.min_yaw,r.max_yaw,r.haov,r.vaov,r.type,r.id_poi_autoopen FROM svt_rooms as r 
            JOIN svt_virtualtours as v ON v.id=r.id_virtualtour
            WHERE r.id = $id_room LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $room['id_poi_autoopen'] = $row['id_poi_autoopen'];
            $room['yaw'] = $row['yaw'];
            $room['pitch'] = $row['pitch'];
            $room['h_pitch'] = $row['h_pitch'];
            $room['h_roll'] = $row['h_roll'];
            $room['min_yaw'] = $row['min_yaw'];
            $room['max_yaw'] = $row['max_yaw'];
            $room['allow_pitch'] = $row['allow_pitch'];
            $room['min_pitch'] = $row['min_pitch'];
            $room['max_pitch'] = $row['max_pitch'];
            $room['haov'] = $row['haov'];
            $room['vaov'] = $row['vaov'];
            $room['panorama_video'] = $row['panorama_video'];
            $room['room_type'] = $row['type'];
            if($row['enable_multires']) {
                $room_pano = str_replace('.jpg','',$row['panorama_image']);
                if($s3_enabled) {
                    $multires_config_file = "s3://$s3_bucket_name/viewer/panoramas/multires/$room_pano/config.json";
                } else {
                    $multires_config_file = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'panoramas'.DIRECTORY_SEPARATOR.'multires'.DIRECTORY_SEPARATOR.$room_pano.DIRECTORY_SEPARATOR.'config.json';
                }
                if(file_exists($multires_config_file)) {
                    $multires_tmp = file_get_contents($multires_config_file);
                    $multires_array = json_decode($multires_tmp,true);
                    $multires_config = $multires_array['multiRes'];
                    if($s3_enabled) {
                        $multires_config['basePath'] = $s3_url.'viewer/panoramas/multires/'.$room_pano;
                    } else {
                        $multires_config['basePath'] = '../viewer/panoramas/multires/'.$room_pano;
                    }
                    $room['multires']=1;
                    $room['multires_config']=json_encode($multires_config);
                    if($s3_enabled) {
                        $room['multires_dir'] = $s3_url.'viewer/panoramas/multires/'.$room_pano;
                    } else {
                        $room['multires_dir']='../viewer/panoramas/multires/'.$room_pano;
                    }
                } else {
                    $room['multires']=0;
                    $room['multires_config']='';
                    $room['multires_dir']='';
                }
            } else {
                $room['multires']=0;
                $room['multires_config']='';
                $room['multires_dir']='';
            }
        }
    }
}
$query = "SELECT 'poi' as what,p.*,IFNULL(i.id,0) as id_icon_library, i.image as img_icon_library,i.id_virtualtour as id_vt_library FROM svt_pois as p
            LEFT JOIN svt_icons as i ON i.id=p.id_icon_library
            WHERE p.id_room=$id_room;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $id_poi = $row['id'];
            $row['array_lang']=array();
            if($row['type']=='html_sc') {
                $row['content'] = htmlspecialchars_decode($row['content']);
            }
            if($row['label']==null) $row['label']='';
            if($row['sound']==null) $row['sound']='';
            if($row['type']==null) $row['type']='';
            if($row['params']==null) $row['params']='';
            if($row['embed_type']==null) $row['embed_type']='';
            if($row['embed_content']==null) $row['embed_content']='';
            if($row['embed_params']==null) $row['embed_params']='';
            if($row['embed_coords']==null) $row['embed_coords']='';
            if($row['embed_size']==null) $row['embed_size']='';
            if($row['content']==null) $row['content']='';
            if($edit_room==1 && $row['embed_type']=='') continue;
            if($row['embed_type']=='gallery') {
                $id_poi = $row['id'];
                $query_g = "SELECT image FROM svt_poi_embedded_gallery WHERE id_poi=$id_poi ORDER BY priority LIMIT 1;";
                $result_g = $mysqli->query($query_g);
                if($result_g) {
                    if ($result_g->num_rows == 1) {
                        $row_g = $result_g->fetch_array(MYSQLI_ASSOC);
                        $row['embed_content'] = $row_g['image'];
                    }
                }
            }
            $row['switch_panorama_image'] = '';
            switch($row['type']) {
                case 'switch_pano':
                    $id_room_alt = $row['content'];
                    if($id_room_alt!='' && $id_room_alt!=0) {
                        $query_ra = "SELECT panorama_image FROM svt_rooms_alt WHERE id=$id_room_alt LIMIT 1;";
                        $result_ra = $mysqli->query($query_ra);
                        if($result_ra) {
                            if ($result_ra->num_rows == 1) {
                                $row_ra = $result_ra->fetch_array(MYSQLI_ASSOC);
                                $row['switch_panorama_image'] = "panoramas/".$row_ra['panorama_image'];
                            }
                        }
                    }
                    break;
                case 'staging':
                    $query_ra = "SELECT * FROM svt_poi_staging WHERE id_poi=$id_poi;";
                    $result_ra = $mysqli->query($query_ra);
                    $row['staging_items'] = array();
                    if($result_ra) {
                        if ($result_ra->num_rows > 0) {
                            while($row_ra = $result_ra->fetch_array(MYSQLI_ASSOC)) {
                                $id_staging = $row_ra['id'];
                                if(array_key_exists($id_staging,$array_staging_lang)) {
                                    $row_ra['array_lang']=$array_staging_lang[$id_staging];
                                } else {
                                    $row_ra['array_lang']=array();
                                }
                                array_push($row['staging_items'], $row_ra);
                            }
                        }
                    }
                    break;
            }
            if(empty($row['id_vt_library'])) $row['id_vt_library']='';
            if(!empty($row["img_icon_library"])) {
                if($s3_enabled && !empty($row['id_vt_library'])) {
                    $row['base64_icon_library'] = convert_image_to_base64("s3://$s3_bucket_name/viewer/icons/".$row["img_icon_library"]);
                } else {
                    $row['base64_icon_library'] = convert_image_to_base64(dirname(__FILE__).'/../../viewer/icons/'.$row["img_icon_library"]);
                }
            } else {
                if($row['style']==1) {
                    $row["style"] = 0;
                }
                $row["img_icon_library"] = '';
                $row['base64_icon_library'] = '';
            }
            if($row['embed_type']=='text') {
                if (strpos($row['embed_content'], 'border-width') === false) {
                    $row['embed_content'] = $row['embed_content']." border-width:0px;";
                }
            }
            if(!empty($row['sound'])) {
                $row['sound'] = str_replace("content/","",$row['sound']);
            }
            if(array_key_exists($id_poi,$array_lang)) {
                foreach ($array_lang[$id_poi] as $array_l) {
                    switch($row['type']) {
                        case 'form':
                            if(!empty($array_l['content'])) {
                                $form_content = json_decode($row['content'],true);
                                $array_l['content']=json_decode($array_l['content'],true);
                                for($i=0;$i<=10;$i++) {
                                    if($i==0) {
                                        if($array_l['content'][0]['title']==$form_content[0]['title']) {
                                            $array_l['content'][0]['title']="";
                                        }
                                        if($array_l['content'][0]['button']==$form_content[0]['button']) {
                                            $array_l['content'][0]['button']="";
                                        }
                                        if($array_l['content'][0]['response']==$form_content[0]['response']) {
                                            $array_l['content'][0]['response']="";
                                        }
                                        if($array_l['content'][0]['description']==$form_content[0]['description']) {
                                            $array_l['content'][0]['description']="";
                                        }
                                    } else {
                                        if($array_l['content'][$i]['label']==$form_content[$i]['label']) {
                                            $array_l['content'][$i]['label']="";
                                        }
                                    }
                                }
                            } else {
                                $array_l['content']=array();
                            }
                            $array_l['content']=json_encode($array_l['content']);
                            break;
                        case 'callout':
                            if(!empty($array_l['params'])) {
                                $callout_params = json_decode($row['params'],true);
                                $array_l['params']=json_decode($array_l['params'],true);
                                if($array_l['params']['title']==$callout_params['title']) {
                                    $array_l['params']['title']="";
                                }
                                if($array_l['params']['description']==$callout_params['description']) {
                                    $array_l['params']['description']="";
                                }
                            } else {
                                $array_l['params']=array();
                            }
                            $array_l['params']=json_encode($array_l['params']);
                            break;
                    }
                    $row['array_lang'][]=$array_l;
                }
            }
            $array[]=$row;
        }
    }
}
if($edit_room==0) {
    $query = "SELECT 'marker' as what,m.*,r.name as name_room_target,r.panorama_image as marker_preview,r.id as id_room_target,IFNULL(i.id,0) as id_icon_library, i.image as img_icon_library,i.id_virtualtour as id_vt_library FROM svt_markers AS m
          JOIN svt_rooms AS r ON m.id_room_target = r.id 
          JOIN svt_virtualtours as v ON v.id = r.id_virtualtour
          LEFT JOIN svt_icons as i ON i.id=m.id_icon_library
          WHERE m.id_room=$id_room;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows>0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                if($row['label']==null) $row['label']='';
                if($row['embed_type']==null) $row['embed_type']='';
                if($row['embed_content']==null) $row['embed_content']='';
                if($row['embed_coords']==null) $row['embed_coords']='';
                if($row['embed_size']==null) $row['embed_size']='';
                if($row['embed_params']==null) $row['embed_params']='';
                if(!empty($row["img_icon_library"])) {
                    if($s3_enabled && !empty($row['id_vt_library'])) {
                        $row['base64_icon_library'] = convert_image_to_base64("s3://$s3_bucket_name/viewer/icons/".$row["img_icon_library"]);
                    } else {
                        $row['base64_icon_library'] = convert_image_to_base64(dirname(__FILE__).'/../../viewer/icons/'.$row["img_icon_library"]);
                    }
                } else {
                    if($row["show_room"] == 4) {
                        $row["show_room"] = 0;
                    }
                    $row["img_icon_library"] = '';
                    $row['base64_icon_library'] = '';
                }
                if(!empty($row["marker_preview"])) {
                    if($s3_enabled) {
                        $row['base64_marker_preview'] = convert_image_to_base64("s3://$s3_bucket_name/viewer/panoramas/preview/".$row["marker_preview"]);
                    } else {
                        $row['base64_marker_preview'] = convert_image_to_base64(dirname(__FILE__).'/../../viewer/panoramas/preview/'.$row["marker_preview"]);
                    }
                } else {
                    $row['base64_marker_preview'] = '';
                }
                $array[]=$row;
            }
        }
    }
}
ob_end_clean();
echo json_encode(array("pois"=>$array,"room"=>$room));