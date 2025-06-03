<?php
$debug=false;
if($debug) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
    ob_start();
}
session_start();
if($_SESSION['svt_si']!=session_id()) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
if(file_exists('../../gsv/index.php')) {
    $gsv_installed = true;
} else {
    $gsv_installed = false;
}
$id_user = $_SESSION['id_user'];
$id_category = (int)$_POST['id_category'];
$id_user_f = (int)$_POST['id_user_f'];
$settings = get_settings();
$user_info = get_user_info($id_user);
$plan_permissions = get_plan_permission($id_user);
if(!isset($_SESSION['lang'])) {
    if(!empty($user_info['language'])) {
        $language = $user_info['language'];
    } else {
        $language = $settings['language'];
    }
} else {
    $language = $_SESSION['lang'];
}
session_write_close();
$where = $where_f = "";
switch(get_user_role($id_user)) {
    case 'customer':
        $where = $where_f = " AND v.id_user=$id_user ";
        break;
    case 'editor':
        $where = $where_f = " AND v.id IN () ";
        $query = "SELECT GROUP_CONCAT(id_virtualtour) as ids FROM svt_assign_virtualtours WHERE id_user=$id_user;";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows==1) {
                $row=$result->fetch_array(MYSQLI_ASSOC);
                $ids = $row['ids'];
                $where = $where_f = " AND v.id IN ($ids) ";
            }
        }
        break;
}
if($id_category!=0) {
    $where .= " AND ca.id_category = $id_category ";
}
if($id_user_f!=0) {
    $where .= " AND v.id_user = $id_user_f ";
}
$array_users = array();
$array_cat = array();
$query = "SELECT v.id,COALESCE(cl.name,c.name) as category_name,c.id as category_id,v.id_user,u.username FROM svt_virtualtours as v
            LEFT JOIN svt_users as u ON u.id=v.id_user
            LEFT JOIN svt_category_vt_assoc as scva on scva.id_virtualtour=v.id
            LEFT JOIN svt_categories as c ON c.id=scva.id_category
            LEFT JOIN svt_categories_lang as cl ON cl.id_category=c.id AND cl.language='$language'
            WHERE 1=1 $where_f
            GROUP BY v.id,scva.id_category,c.name,cl.name,c.id,v.id_user,u.username;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            if(!empty($row['category_id'])) {
                if(!in_array($row['category_id']."|".$row['category_name'],$array_cat)) {
                    $array_cat[] = $row['category_id']."|".$row['category_name'];
                }
            }
            if(!empty($row['id_user'])) {
                if(!in_array($row['id_user']."|".$row['username'],$array_users)) {
                    $array_users[] = $row['id_user']."|".$row['username'];
                }
            }
        }
    }
}
$array_vt = array();
if($_SESSION['full_group_by']===true) {
    $group_by = "v.id,v.block_tour,v.aws_s3,v.date_created,p.expire_tours,v.external,v.name,v.author,v.id_user,u.username,v.start_date,v.end_date,v.active,u.expire_plan_date,v.info_box,v.background_image,v.note,v.dollhouse,v.dollhouse_glb,v.languages_enabled,v.language";
} else {
    $group_by = "v.id,v.date_created";
}
$s3Client = null;
$light_mode = ($settings['tour_list_mode']=='default') ? false : true;
if($light_mode) {
    $count_light_mode = (int) str_replace("light_","",$settings['tour_list_mode']);
    if(!empty($count_light_mode)) {
        $query = "SELECT COUNT(DISTINCT v.id) as count_tours
             FROM svt_virtualtours as v
             LEFT JOIN svt_category_vt_assoc as ca ON ca.id_virtualtour=v.id
             WHERE 1=1 $where LIMIT 1;";
        $result = $mysqli->query($query);
        if($result) {
            if ($result->num_rows == 1) {
                $row = $result->fetch_array(MYSQLI_ASSOC);
                $count_tours = $row['count_tours'];
                if($count_tours<=$count_light_mode) {
                    $light_mode=false;
                }
            }
        }
    }
}
$array_vt_gsv = array();
$array_link_gsv = array();
if($gsv_installed && $plan_permissions['enable_gsv_publish']) {
    $query = "SELECT t.id_virtualtour, (SELECT google_share_link FROM svt_gsv_rooms WHERE id_gsv_tour=t.id AND uploaded = 1 AND google_share_link IS NOT NULL ORDER BY priority LIMIT 1) as google_share_link
                FROM svt_gsv_tours as t
                JOIN svt_virtualtours as v ON v.id=t.id_virtualtour
                LEFT JOIN svt_category_vt_assoc as ca ON ca.id_virtualtour=v.id
                WHERE 1=1 $where
                GROUP BY t.id,t.id_virtualtour;";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                if(!empty($row['google_share_link'])) {
                    $array_link_gsv[$row['id_virtualtour']] = $row['google_share_link'];
                }
            }
        }
    }
    $query = "SELECT v.id,COUNT(r.id) as count_r,t.published FROM svt_gsv_tours as t
                JOIN svt_virtualtours as v ON v.id=t.id_virtualtour
                LEFT JOIN svt_gsv_rooms as r ON t.id=r.id_gsv_tour
                LEFT JOIN svt_category_vt_assoc as ca ON ca.id_virtualtour=v.id
                WHERE 1=1 $where
                GROUP BY v.id,t.published;";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                if($row['published']==1 && $row['count_r']>0) {
                    $array_vt_gsv[$row['id']] = 2;
                } else {
                    if($row['count_r']>0) {
                        $array_vt_gsv[$row['id']] = 1;
                    } else {
                        $array_vt_gsv[$row['id']] = 0;
                    }
                }
            }
        }
    }
}
$array_last_access = array();
if(!$light_mode && $settings['tour_inactive_days']>0) {
    $query = "SELECT l.id_virtualtour,MAX(l.date_time) as last_access FROM svt_access_log as l
                JOIN svt_virtualtours as v ON v.id=l.id_virtualtour
                WHERE 1=1 $where_f
                GROUP BY id_virtualtour;";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $last_access = $row['last_access'];
                $currentDate = new DateTime();
                $dateToCheck = new DateTime($last_access);
                $thresholdDate = (clone $currentDate)->modify("-{$settings['tour_inactive_days']} days");
                if ($dateToCheck < $thresholdDate) {
                    $old = 1;
                } else {
                    $old = 0;
                }
                $array_last_access[$row['id_virtualtour']] = array("old"=>$old,"date"=>formatTime("dd MMM y",$language,strtotime($last_access)));
            }
        }
    }
}
$s3_url = "";
if($light_mode) {
    $query = "SELECT IFNULL(p.expire_tours,1) as expire_tours,v.id,v.block_tour,v.aws_s3,GROUP_CONCAT(DISTINCT COALESCE(cl.name,c.name) SEPARATOR ', ') as category_name,GROUP_CONCAT(DISTINCT c.id) as category_id,v.external,v.name,v.date_created,v.author,v.id_user,u.username,v.start_date,v.end_date,v.active,u.expire_plan_date,v.background_image,v.note,v.dollhouse,v.dollhouse_glb,v.languages_enabled,v.language
             FROM svt_virtualtours as v
             LEFT JOIN svt_users as u ON u.id=v.id_user
             LEFT JOIN svt_category_vt_assoc as ca ON ca.id_virtualtour=v.id
             LEFT JOIN svt_categories as c ON c.id=ca.id_category
             LEFT JOIN svt_categories_lang as cl ON cl.id_category=c.id AND cl.language='$language'
             LEFT JOIN svt_plans AS p ON p.id=u.id_plan
             WHERE 1=1 $where
             GROUP BY $group_by
             ORDER BY v.date_created DESC,v.id DESC;";
} else {
    $query = "SELECT IFNULL(p.expire_tours,1) as expire_tours,v.id,v.block_tour,v.aws_s3,GROUP_CONCAT(DISTINCT COALESCE(cl.name,c.name) SEPARATOR ', ') as category_name,GROUP_CONCAT(DISTINCT c.id) as category_id,v.external,v.name,v.date_created,v.author,v.id_user,u.username,v.start_date,v.end_date,v.active,u.expire_plan_date,COUNT(DISTINCT r.id) as count_rooms,COUNT(DISTINCT m.id) as count_maps,COUNT(DISTINCT g.id) as count_gallery,COUNT(DISTINCT svp.id) as count_video_projects,IF(v.info_box IS NULL OR v.info_box = '' OR v.info_box='<p><br></p>',0,1) as info_box_check,GROUP_CONCAT(DISTINCT r.panorama_image ORDER BY r.priority ASC) as panoramas_list,v.background_image,v.note,v.dollhouse,v.dollhouse_glb,v.languages_enabled,v.language,COUNT(DISTINCT vv.id) as count_versions
            FROM svt_virtualtours as v 
            LEFT JOIN svt_virtualtours_versions as vv ON vv.id_virtualtour=v.id
            LEFT JOIN svt_rooms as r ON r.id_virtualtour=v.id
            LEFT JOIN svt_users as u ON u.id=v.id_user
            LEFT JOIN svt_maps as m ON m.id_virtualtour=v.id
            LEFT JOIN svt_gallery as g ON g.id_virtualtour=v.id
            LEFT JOIN svt_category_vt_assoc as ca ON ca.id_virtualtour=v.id
            LEFT JOIN svt_categories as c ON c.id=ca.id_category
            LEFT JOIN svt_categories_lang as cl ON cl.id_category=c.id AND cl.language='$language'
            LEFT JOIN svt_plans AS p ON p.id=u.id_plan
            LEFT JOIN svt_video_projects svp on v.id = svp.id_virtualtour
            WHERE 1=1 $where
            GROUP BY $group_by
            ORDER BY v.date_created DESC,v.id DESC;";
}
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $id_vt = $row['id'];
            $row['link_gsv']="";
            if($gsv_installed && $plan_permissions['enable_gsv_publish']) {
                if(array_key_exists($id_vt,$array_vt_gsv)) {
                    $row['status_gsv'] = $array_vt_gsv[$id_vt];
                } else {
                    $row['status_gsv'] = 0;
                }
                if(array_key_exists($id_vt,$array_link_gsv)) {
                    $row['link_gsv'] = $array_link_gsv[$id_vt];
                }
            } else {
                $row['status_gsv'] = -1;
            }
            $s3_params = check_s3_tour_enabled($id_vt);
            $s3_enabled = false;
            if(!empty($s3_params)) {
                $s3_bucket_name = $s3_params['bucket'];
                if($s3Client==null) {
                    $s3Client = init_s3_client_no_wrapper($s3_params);
                    if($s3Client==null) {
                        $s3_enabled = false;
                    } else {
                        if(!empty($s3_params['custom_domain'])) {
                            $s3_url = "https://".$s3_params['custom_domain']."/";
                        } else {
                            try {
                                $s3_url = $s3Client->getObjectUrl($s3_bucket_name, '.');
                            } catch (Aws\Exception\S3Exception $e) {}
                        }
                        $s3_enabled = true;
                    }
                } else {
                    $s3_enabled = true;
                }
            }
            if($user_info['role']=='editor') {
                $editor_permissions = get_editor_permissions($id_user,$row['id']);
                if($editor_permissions['edit_virtualtour']==1) {
                    $row['edit_permission']=true;
                } else {
                    $row['edit_permission']=false;
                }
                if($editor_permissions['edit_virtualtour_ui']==1) {
                    $row['edit_ui_permission']=true;
                } else {
                    $row['edit_ui_permission']=false;
                }
                if($editor_permissions['edit_3d_view']==1) {
                    $row['edit_3d_view_permission']=true;
                } else {
                    $row['edit_3d_view_permission']=false;
                }
            } else {
                $row['edit_permission']=true;
                $row['edit_ui_permission']=true;
                $row['edit_3d_view_permission']=true;
            }
            if($row['active']==0) {
                $row['status']=0;
            } else {
                $row['status']=1;
            }
            $row['date_created'] = formatTime("dd MMM y",$language,strtotime($row['date_created']));
            if(!empty($row['expire_plan_date'])) {
                if($row['expire_tours']==1) {
                    if (new DateTime() > new DateTime($row['expire_plan_date'])) {
                        $row['status'] = 0;
                    }
                }
            }
            if((!empty($row['start_date'])) && ($row['start_date']!='0000-00-00')) {
                if (new DateTime() < new DateTime($row['start_date']." 00:00:00")) {
                    $row['status']=0;
                }
                $row['start_date'] = formatTime("dd MMM y",$language,strtotime($row['start_date']));
            } else {
                $row['start_date'] = "";
            }
            if((!empty($row['end_date'])) && ($row['end_date']!='0000-00-00')) {
                if (new DateTime() > new DateTime($row['end_date']." 23:59:59")) {
                    $row['status']=0;
                }
                $row['end_date'] = formatTime("dd MMM y",$language,strtotime($row['end_date']));
            } else {
                $row['end_date'] = "";
            }
            $row['last_access'] = "";
            if(array_key_exists($id_vt,$array_last_access)) {
                $row['last_access'] = $array_last_access[$id_vt];
            }
            if(($row['author']!=$row['username']) && (!empty($row['author']))) {
                $row['author'] = $row['username']." (".$row['author'].")";
            } else {
                $row['author'] = $row['username'];
            }
            $row['name'] = htmlentities($row['name']);
            $row['author'] = htmlentities($row['author']);
            if(empty($row['category_name'])) $row['category_name']='';
            if(empty($row['panoramas_list'])) $row['panoramas_list']='';
            if(empty($row['background_image'])) $row['background_image']='';
            if(empty($row['note'])) {
                $row['note']='';
            } else {
                if (strlen($row['note']) > 100) $row['note'] = substr($row['note'], 0, 100) . '...';
            }
            $dollhouse = $row['dollhouse'];
            $dollhouse_glb = $row['dollhouse_glb'];
            $count_video360 = 0;
            if(!$light_mode) {
                if(!empty($dollhouse_glb)) {
                    $row['dollhouse'] = 1;
                } else {
                    if(!empty($dollhouse)) {
                        $dollhouse = json_decode($dollhouse,true);
                        if(count($dollhouse['rooms'])>0) {
                            $row['dollhouse'] = 1;
                        } else {
                            $row['dollhouse'] = 0;
                        }
                    } else {
                        $row['dollhouse'] = 0;
                    }
                }
                if($s3_enabled) {
                    if($s3Client->doesObjectExist($s3_bucket_name,'video360/'.$row['id'].'/')) {
                        $objects = $s3Client->getIterator('ListObjects', array(
                            "Bucket" => $s3_bucket_name,
                            "Prefix" => 'video360/'.$row['id'].'/'
                        ));
                        foreach ($objects as $object) {
                            $file_ext = strtolower(substr($object['Key'], strrpos($object['Key'], '.')+1));
                            if ($file_ext == 'mp4') $count_video360++;
                        }
                    }
                } else {
                    $path = dirname(__FILE__).'/../../video360/'.$row['id'].'/';
                    if(file_exists($path)) {
                        $dir = new DirectoryIterator($path);
                        foreach ($dir as $fileinfo) {
                            if (!$fileinfo->isDot() && ($fileinfo->isFile())) {
                                $file_ext = $fileinfo->getExtension();
                                if ($file_ext == 'mp4') $count_video360++;
                            }
                        }
                    }
                }
                if($s3_enabled) {
                    if($s3Client->doesObjectExist($s3_bucket_name,'viewer/gallery/'.$row['id'].'_slideshow.mp4')) {
                        $row['slideshow_check']=1;
                    } else {
                        $row['slideshow_check']=0;
                    }
                } else {
                    if(file_exists('../../viewer/gallery/'.$row['id'].'_slideshow.mp4')) {
                        $row['slideshow_check']=1;
                    } else {
                        $row['slideshow_check']=0;
                    }
                }
            }
            $row['count_video360']=$count_video360;
            if($s3_enabled) {
                $row['aws_s3_url'] = $s3_url;
                $row['aws_s3']=1;
            } else {
                $row['aws_s3']=0;
            }
            if(!empty($row['languages_enabled'])) {
                $row['languages_enabled']=json_decode($row['languages_enabled'],true);
            } else {
                $row['languages_enabled']=array();
            }
            $default_language = $row['language'];
            if(empty($default_language)) {
                $default_language = $settings['language'];
            }
            $row['languages_enabled'][$default_language]=0;
            $array_languages = array();
            foreach ($row['languages_enabled'] as $lang=>$enabled) {
                if($enabled==1) {
                    array_push($array_languages,$lang);
                }
            }
            $row['default_language']=$default_language;
            $row['languages']=$array_languages;
            $array_vt[]=$row;
        }
    }
}
if(!$debug) ob_end_clean();
echo json_encode(array("vt_list"=>$array_vt,"aws_s3_enabled"=>$settings['aws_s3_enabled'],"categories"=>$array_cat,"users"=>$array_users), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);