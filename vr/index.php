<?php
header('Access-Control-Allow-Origin: *');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
require_once("../db/connection.php");
require_once("../backend/functions.php");
if(check_maintenance_mode('viewer')) {
    if(file_exists("../error_pages/custom/maintenance_viewer.html")) {
        include("../error_pages/custom/maintenance_viewer.html");
    } else {
        include("../error_pages/default/maintenance_viewer.html");
    }
    exit;
}
if(isset($_SESSION['redirect_vr'])) {
    $new_window = $_SESSION['new_window'];
} else {
    $new_window = false;
}
if(isset($_SESSION['redirect_vr'])) {
    $redirect_vr = true;
} else {
    $redirect_vr = false;
}
$array_rooms = array();
$array_markers = array();
$array_pois = array();
$array_markers_icons = array();
$array_markers_icons_exists = array();
$array_pois_icons = array();
$array_pois_icons_exists = array();
$vr_icons_size = null;
$rooms_count = 0;
$s3Client = null;
$s3_enabled = false;
$s3_version = time();
if(isset($_GET['export'])) {
    $export=1;
} else {
    $export=0;
}
if(isset($_GET['export_s3'])) {
    $export_s3=$_GET['export_s3'];
} else {
    $export_s3=0;
}
$currentPath = $_SERVER['PHP_SELF'];
$pathInfo = pathinfo($currentPath);
$hostName = $_SERVER['HTTP_HOST'];
if (is_ssl()) { $protocol = 'https'; } else { $protocol = 'http'; }
$url = $protocol."://".$hostName.$pathInfo['dirname']."/";
if((isset($_GET['furl'])) || (isset($_GET['code']))) {
    if(isset($_GET['furl'])) {
        $furl = $_GET['furl'];
        $where = "(v.friendly_url = '$furl' OR v.code = '$furl')";
    }
    if(isset($_GET['code'])) {
        $code = $_GET['code'];
        $where = "v.code = '$code'";
    }
    $query = "SELECT v.id,v.code,v.id_user,v.name as name_virtualtour,v.author,v.language,v.ga_tracking_id,v.font_viewer,v.logo,v.background_image,v.song,v.show_audio,v.description,u.expire_plan_date,v.start_date,v.end_date,v.start_url,v.end_url,u.id_subscription_stripe,u.status_subscription_stripe,u.id_subscription_paypal,u.status_subscription_paypal,v.meta_title,v.meta_description,v.meta_image,v.nadir_logo,v.nadir_size,p.expire_tours,v.protect_type,v.password,v.protect_mc_form,v.vr_icons_size 
                FROM svt_virtualtours AS v
                JOIN svt_users AS u ON u.id=v.id_user
                LEFT JOIN svt_plans AS p ON p.id=u.id_plan
                WHERE v.active=1 AND $where LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows == 1) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            if(!empty($row['id_subscription_stripe'])) {
                if($row['status_subscription_stripe']==0 && $row['expire_tours']==1) {
                    if(file_exists("../error_pages/custom/expired_tour.html")) {
                        include("../error_pages/custom/expired_tour.html");
                    } else {
                        include("../error_pages/default/expired_tour.html");
                    }
                    exit;
                }
            }
            if(!empty($row['id_subscription_paypal'])) {
                if($row['status_subscription_paypal']==0 && $row['expire_tours']==1) {
                    if(file_exists("../error_pages/custom/expired_tour.html")) {
                        include("../error_pages/custom/expired_tour.html");
                    } else {
                        include("../error_pages/default/expired_tour.html");
                    }
                    exit;
                }
            }
            if(!empty($row['expire_plan_date'])) {
                if($row['expire_tours']==1) {
                    if (new DateTime() > new DateTime($row['expire_plan_date'])) {
                        if(file_exists("../error_pages/custom/expired_tour.html")) {
                            include("../error_pages/custom/expired_tour.html");
                        } else {
                            include("../error_pages/default/expired_tour.html");
                        }
                        exit;
                    }
                }
            }
            if((!empty($row['start_date'])) && ($row['start_date']!='0000-00-00')) {
                if (new DateTime() < new DateTime($row['start_date']." 00:00:00")) {
                    if(!empty($row['start_url'])) {
                        header("Location: ".$row['start_url']);
                        exit();
                    } else {
                        if(file_exists("../error_pages/custom/expired_tour.html")) {
                            include("../error_pages/custom/expired_tour.html");
                        } else {
                            include("../error_pages/default/expired_tour.html");
                        }
                        exit;
                    }
                }
            }
            if((!empty($row['end_date'])) && ($row['end_date']!='0000-00-00')) {
                if (new DateTime() > new DateTime($row['end_date']." 23:59:59")) {
                    if(!empty($row['end_url'])) {
                        header("Location: ".$row['end_url']);
                        exit();
                    } else {
                        if(file_exists("../error_pages/custom/expired_tour.html")) {
                            include("../error_pages/custom/expired_tour.html");
                        } else {
                            include("../error_pages/default/expired_tour.html");
                        }
                        exit;
                    }
                }
            }
            $protect_type = $row['protect_type'];
            $password_protected = 0;
            switch($protect_type) {
                case 'password':
                    if(!empty($row['password'])) $password_protected = 1;
                    break;
                case 'lead':
                    $password_protected = 1;
                    break;
                case 'mailchimp':
                    if(!empty($row['protect_mc_form'])) {
                        $password_protected = 1;
                    }
                    break;
            }
            if(!$redirect_vr && $export==0 && $password_protected==1) {
                echo "The tour is protected... Redirecting, please wait!";
                $url_viewer = $protocol."://".$hostName.str_replace("/vr/","/viewer/",$_SERVER['REQUEST_URI']);
                header("refresh:3;url=$url_viewer");
                exit;
            }
            $code = $row['code'];
            $id_virtualtour = $row['id'];
            if($export==0) {
                $s3_params = check_s3_tour_enabled($id_virtualtour);
                $s3_enabled = false;
                $s3_url = "";
                if(!empty($s3_params)) {
                    $s3_bucket_name = $s3_params['bucket'];
                    $s3_url = init_s3_client($s3_params);
                    if($s3_url!==false) {
                        $s3_enabled = true;
                    }
                }
            }
            $name_virtualtour = $row['name_virtualtour'];
            $author_virtualtour = $row['author'];
            $id_user = $row['id_user'];
            $ga_tracking_id = $row['ga_tracking_id'];
            $logo = $row['logo'];
            $song = $row['song'];
            if(!$row['show_audio']) $song = "";
            $font_viewer = $row['font_viewer'];
            $background_image = $row['background_image'];
            $description = $row['description'];
            $vt_language = $row['language'];
            $nadir_logo = $row['nadir_logo'];
            $nadir_size = $row['nadir_size'];
            $vr_icons_size = $row['vr_icons_size'];
            if(empty($row['meta_title'])) {
                $meta_title = $name_virtualtour;
            } else {
                $meta_title = $row['meta_title'];
            }
            if(empty($row['meta_description'])) {
                $meta_description = $description;
            } else {
                $meta_description = $row['meta_description'];
            }
            if(empty($row['meta_image'])) {
                $meta_image = $background_image;
            } else {
                $meta_image = $row['meta_image'];
            }
            $query_rooms = "SELECT id,name,northOffset,panorama_image,panorama_video,yaw FROM svt_rooms WHERE id_virtualtour=$id_virtualtour ORDER BY priority, id;";
            $result_rooms = $mysqli->query($query_rooms);
            if($result_rooms) {
                $rooms_count = $result_rooms->num_rows;
                if ($rooms_count > 0) {
                    while ($row_room = $result_rooms->fetch_array(MYSQLI_ASSOC)) {
                        array_push($array_rooms,$row_room);
                    }
                }
            }
            $query_markers = "SELECT m.id,m.yaw,m.pitch,m.id_room,m.id_room_target,m.id_icon_library,r.name as name_room_target FROM svt_markers AS m
                                JOIN svt_rooms AS r on m.id_room_target = r.id
                                WHERE m.id_room IN (SELECT DISTINCT id FROM svt_rooms WHERE id_virtualtour=$id_virtualtour ORDER BY priority, id);";
            $result_markers = $mysqli->query($query_markers);
            if($result_markers) {
                if ($result_markers->num_rows > 0) {
                    while ($row_marker = $result_markers->fetch_array(MYSQLI_ASSOC)) {
                        $id_room = $row_marker['id_room'];
                        $id_icon_library = $row_marker['id_icon_library'];
                        if(!array_key_exists($id_room,$array_markers)) {
                            $array_markers[$id_room] = array();
                        }
                        if($id_icon_library!=0) {
                            if(!in_array($id_icon_library,$array_markers_icons)) {
                                array_push($array_markers_icons,$id_icon_library);
                            }
                        }
                        array_push($array_markers[$id_room],$row_marker);
                    }
                }
            }
            $query_pois = "SELECT id,yaw,pitch,id_room,type,content,id_icon_library FROM svt_pois WHERE type IN ('image','video','object3d','html','audio','video360') AND content!='' AND id_room IN (SELECT DISTINCT id FROM svt_rooms WHERE id_virtualtour=$id_virtualtour ORDER BY priority, id);";
            $result_pois = $mysqli->query($query_pois);
            if($result_pois) {
                if ($result_pois->num_rows > 0) {
                    while ($row_poi = $result_pois->fetch_array(MYSQLI_ASSOC)) {
                        $id_room = $row_poi['id_room'];
                        $content = $row_poi['content'];
                        $type = $row_poi['type'];
                        $id_icon_library = $row_poi['id_icon_library'];
                        $skip = false;
                        switch ($type) {
                            case 'image':
                                if($s3_enabled) {
                                    list($width, $height, $type, $attr) = getimagesize("s3://$s3_bucket_name/viewer/".$content);
                                } else {
                                    if($export_s3==1) {
                                        list($width, $height, $type, $attr) = getimagesize(dirname(__FILE__).'/../services/export_tmp/'.$code."_vr".'/'.$content);
                                    } else {
                                        list($width, $height, $type, $attr) = getimagesize(dirname(__FILE__).'/../viewer/'.$content);
                                    }
                                }
                                $row_poi['aspect_ratio'] = $width / $height;
                                break;
                            case 'video':
                                if (strpos($content, 'http') === false && strpos($content, '.mp4') !== false) {
                                    include_once('vendor/getid3/getid3.php');
                                    $getID3 = new getID3();
                                    if($s3_enabled) {
                                        $video_content = file_get_contents("s3://$s3_bucket_name/viewer/$content");
                                        if(empty($video_content)) {
                                            $video_content = curl_get_file_contents($s3_url."viewer/$content");
                                        }
                                        $tmpfname = tempnam(sys_get_temp_dir(), "video_vr_");
                                        rename($tmpfname, $tmpfname .= '.mp4');
                                        file_put_contents($tmpfname,$video_content);
                                        $file = $getID3->analyze($tmpfname);
                                        unlink($tmpfname);
                                    } else {
                                        if($export_s3==1) {
                                            $file = $getID3->analyze(dirname(__FILE__).'/../services/export_tmp/'.$code."_vr".'/'.$content);
                                        } else {
                                            $file = $getID3->analyze(dirname(__FILE__).'/../viewer/'.$content);
                                        }
                                    }
                                    $width = $file['video']['resolution_x'];
                                    $height = $file['video']['resolution_y'];
                                    $row_poi['aspect_ratio'] = $width / $height;
                                } else {
                                    $skip = true;
                                }
                                break;
                        }
                        if(!$skip) {
                            if(!array_key_exists($id_room,$array_pois)) {
                                $array_pois[$id_room] = array();
                            }
                            array_push($array_pois[$id_room],$row_poi);
                            if($id_icon_library!=0) {
                                if(!in_array($id_icon_library,$array_pois_icons)) {
                                    array_push($array_pois_icons,$id_icon_library);
                                }
                            }
                        }
                    }
                }
            }
        } else {
            if(file_exists("../error_pages/custom/invalid_tour.html")) {
                include("../error_pages/custom/invalid_tour.html");
            } else {
                include("../error_pages/default/invalid_tour.html");
            }
            exit;
        }
    } else {
        if(file_exists("../error_pages/custom/invalid_tour.html")) {
            include("../error_pages/custom/invalid_tour.html");
        } else {
            include("../error_pages/default/invalid_tour.html");
        }
        exit;
    }
} else {
    if(file_exists("../error_pages/custom/invalid_tour.html")) {
        include("../error_pages/custom/invalid_tour.html");
    } else {
        include("../error_pages/default/invalid_tour.html");
    }
    exit;
}
$query = "SELECT enable_webvr FROM svt_plans as p LEFT JOIN svt_users AS u ON u.id_plan=p.id WHERE u.id = $id_user LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row=$result->fetch_array(MYSQLI_ASSOC);
        if($row['enable_webvr']==0) {
            die("Not allowed");
        }
    }
}
$lang_code = "en";
$font_provider = "google";
$query = "SELECT name,language,language_domain,font_provider FROM svt_settings LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $name_app = $row['name'];
        $font_provider = $row['font_provider'];
        if ($vt_language != '') {
            $language = $vt_language;
        } else {
            $language = $row['language'];
        }
        switch ($language) {
            case 'pt_BR':
                $lang_code = 'pt-BR';
                break;
            case 'pt_PT':
                $lang_code = 'pt-PT';
                break;
            case 'zh_CN':
                $lang_code = 'zh-CN';
                break;
            case 'zh_TW':
                $lang_code = 'zh-TW';
                break;
            case 'zh_HK':
                $lang_code = 'zh-hk';
                break;
            default:
                $lang_code = substr($language, 0, 2);
                break;
        }
        if (function_exists('gettext')) {
            if (defined('LC_MESSAGES')) {
                $result = setlocale(LC_MESSAGES, $language);
                if (!$result) {
                    setlocale(LC_MESSAGES, $language . '.UTF-8');
                }
                if (function_exists('putenv')) {
                    $result = putenv('LC_MESSAGES=' . $language);
                    if (!$result) {
                        putenv('LC_MESSAGES=' . $language . '.UTF-8');
                    }
                }
            } else {
                if (function_exists('putenv')) {
                    $result = putenv('LC_ALL=' . $language);
                    if (!$result) {
                        putenv('LC_ALL=' . $language . '.UTF-8');
                    }
                }
            }
            $domain = $row['language_domain'];
            if(!file_exists("../locale/".$language."/LC_MESSAGES/custom.mo")) {
                $domain = "default";
            }
            $result = bindtextdomain($domain, "../locale");
            if (!$result) {
                $domain = "default";
                bindtextdomain($domain, "../locale");
            }
            bind_textdomain_codeset($domain, 'UTF-8');
            textdomain($domain);
            if (!function_exists('_')) {
                function _($a) {
                    return gettext($a);
                }
            }
        } else {
            function _($a) {
                return $a;
            }
        }
    }
}
$ip_visitor = getIPAddress();
$ip_visitor = hash('sha256',$ip_visitor);
if($export==0) {
    if($s3_enabled) {
        $path = $s3_url.'viewer/';
    } else {
        $path = "../viewer/";
    }
    $mysqli->query("INSERT INTO svt_access_log(id_virtualtour,date_time,ip) VALUES($id_virtualtour,NOW(),'$ip_visitor');");
} else {
    $path = "";
}
$refer_url = $_SERVER['HTTP_REFERER'];
$part_viewer = "viewer";
if(!empty($refer_url)) {
    $url_parts = explode("/", $refer_url);
    $part_viewer = $url_parts[count($url_parts)-2];
    if(empty($part_viewer)) { $part_viewer = "viewer"; }
}
$default_z_positions = [
    'marker' => 10,
    'image' => 10,
    'video' => 10,
    'video360' => 10,
    'html' => 10,
    'audio' => 10,
    'object3d' => 10
];
$default_z_offsets = [
    'marker' => 9,
    'image' => 9,
    'video' => 9,
    'video360' => 9,
    'html' => 9,
    'audio' => 9,
    'object3d' => 9
];
$hidden_status = [
    'marker' => false,
    'image' => false,
    'video' => false,
    'video360' => false,
    'html' => false,
    'audio' => false,
    'object3d' => false
];
if (!empty($vr_icons_size)) {
    $vr_icons_size = json_decode($vr_icons_size, true);
    foreach ($default_z_positions as $type => $default_position) {
        if (isset($vr_icons_size[$type])) {
            switch ($vr_icons_size[$type]) {
                case 'hidden':
                    $hidden_status[$type] = true;
                    break;
                case 'extra_small':
                    $default_z_positions[$type] = 16;
                    $default_z_offsets[$type] = 5.6;
                    break;
                case 'small':
                    $default_z_positions[$type] = 13;
                    $default_z_offsets[$type] = 7;
                    break;
                case 'medium':
                    // Already set as default
                    break;
                case 'large':
                    $default_z_positions[$type] = 7;
                    $default_z_offsets[$type] = 13;
                    break;
                case 'extra_large':
                    $default_z_positions[$type] = 4;
                    $default_z_offsets[$type] = 23;
                    break;
            }
        }
    }
}
$useragent=$_SERVER['HTTP_USER_AGENT'];
$is_mobile = false;
if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4))) {
    $is_mobile = true;
}

function clampAngle($angle) {
    return (($angle % 720) + 720) % 720 - 360;
}
?>
    <!DOCTYPE html>
    <html lang="<?php echo $lang_code; ?>">
    <head>
        <title><?php echo $meta_title; ?></title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, maximum-scale=1, minimum-scale=1">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta property="og:type" content="website">
        <meta property="twitter:card" content="summary_large_image">
        <meta property="og:url" content="<?php echo $url."index.php?code=".$code; ?>">
        <meta property="twitter:url" content="<?php echo $url."index.php?code=".$code; ?>">
        <meta itemprop="name" content="<?php echo $meta_title; ?>">
        <meta property="og:title" content="<?php echo $meta_title; ?>">
        <meta property="twitter:title" content="<?php echo $meta_title; ?>">
        <?php if($meta_image!='') : ?>
            <meta itemprop="image" content="<?php echo str_replace("/vr/","/viewer/",$url)."content/".$meta_image; ?>">
            <meta property="og:image" content="<?php echo str_replace("/vr/","/viewer/",$url)."content/".$meta_image; ?>" />
            <meta property="twitter:image" content="<?php echo str_replace("/vr/","/viewer/",$url)."content/".$meta_image; ?>">
        <?php endif; ?>
        <?php if($meta_description!='') : ?>
            <meta itemprop="description" content="<?php echo $meta_description; ?>">
            <meta name="description" content="<?php echo $meta_description; ?>"/>
            <meta property="og:description" content="<?php echo $meta_description; ?>" />
            <meta property="twitter:description" content="<?php echo $meta_description; ?>">
        <?php endif; ?>
        <?php echo print_favicons_vt($code,$logo,$export); ?>
        <?php switch ($font_provider) {
            case 'google': ?>
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link rel='stylesheet' type="text/css" crossorigin="anonymous" href="https://fonts.googleapis.com/css2?family=<?php echo $font_viewer; ?>">
                <?php break;
            case 'collabs': ?>
                <link rel="preconnect" href="https://api.fonts.coollabs.io" crossorigin>
                <link href="https://api.fonts.coollabs.io/css2?family=<?php echo $font_viewer; ?>&display=swap" rel="stylesheet">
                <?php break;
        } ?>
        <?php if($export==1) { ?>
            <link rel="stylesheet" type="text/css" href="css/style.css">
            <script type="text/javascript" src="js/script.js"></script>
        <?php } else { ?>
            <link rel="stylesheet" type="text/css" href="css/progress.css">
            <link rel="stylesheet" type="text/css" href="css/index.css?v=<?php echo time(); ?>">
            <?php if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'custom.css')) : ?>
            <link rel="stylesheet" type="text/css" href="../viewer/css/custom.css?v=<?php echo time(); ?>">
            <?php endif; ?>
            <?php if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'viewer'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'custom_'.$code.'.css')) : ?>
            <link rel="stylesheet" type="text/css" href="../viewer/css/custom_<?php echo $code; ?>.css?v=<?php echo time(); ?>">
            <?php endif; ?>
            <script type="text/javascript" src="js/progress.min.js"></script>
            <script type="text/javascript" src="js/aframe-master.min.js?v=1.7.0"></script>
            <script type="text/javascript" src="js/aframe-look-at-billboard-component.js?v=2"></script>
            <script type="text/javascript" src="js/aframe-stereo-component.min.js?v=2"></script>
        <?php } ?>
    </head>
    <body>
    <style>
        *:not(i) { font-family: '<?php echo $font_viewer; ?>', sans-serif; }
    </style>
    <div class="loading">
        <div class="progress-circle noselect"></div>
        <div class="progress">
            <?php if($logo!='') : ?>
                <img src="<?php echo $path; ?>content/<?php echo $logo.(($s3_enabled) ? '?v=s3='.$s3_version : ''); ?>" />
            <?php endif; ?>
            <h3 class="noselect" id="name_virtualtour"><?php echo $name_virtualtour; ?></h3>
            <h2 class="noselect <?php echo (empty($author_virtualtour)) ? 'hidden' : ''; ?>" id="author_virtualtour"><?php echo _("presented by")." ".$author_virtualtour; ?></h2>
        </div>
        <?php if(!empty($background_image)) : ?>
            <div id="background_loading" class="background_opacity" style="background-image: url('<?php echo $path; ?>content/<?php echo $background_image.(($s3_enabled) ? '?v=s3='.$s3_version : ''); ?>');"></div>
        <?php endif; ?>
    </div>
    <?php
    $timeout = $rooms_count*1500;
    if($timeout<=5000) $timeout=5000;
    if($timeout>=60000) $timeout=60000;
    $timeout_interval = $timeout/100;
    ?>
    <script>
        window.ip_visitor = '<?php echo $ip_visitor; ?>';
        window.assets_interval = <?php echo $timeout_interval; ?>;
        window.export = <?php echo $export; ?>;
    </script>
    <?php if($redirect_vr && !$new_window) { ?>
        <script> var exit_button = true; </script>
    <?php } else { ?>
        <script> var exit_button = false; </script>
    <?php } ?>
    <script type="text/javascript" src="js/index.js?v=<?php echo time(); ?>"></script>
    <button onclick="redirect_to_normal();" id="exit_vr_button"><?php echo _("EXIT VR"); ?></button>
    <a-scene id="vt_scene" xr-mode-ui="enabled: true" scenelistener light="defaultLightsEnabled: false" style="opacity: 0;pointer-events: none;">
        <a-assets timeout="<?php echo $timeout; ?>">
            <img crossorigin="anonymous" id="play" src="img/play.png?v=3" />
            <?php if (file_exists(dirname(__FILE__).'/img/'.$id_virtualtour.'/marker.png')) { ?>
                <img crossorigin="anonymous" id="marker" src="img/<?php echo $id_virtualtour; ?>/marker.png?v=<?php echo time(); ?>" />
            <?php } else { ?>
                <?php if (file_exists(dirname(__FILE__).'/img/custom/marker.png')) { ?>
                    <img crossorigin="anonymous" id="marker" src="img/custom/marker.png?v=<?php echo time(); ?>" />
                <?php } else { ?>
                    <img crossorigin="anonymous" id="marker" src="img/marker.png?v=3" />
                <?php } ?>
            <?php } ?>
            <?php if (file_exists(dirname(__FILE__).'/img/'.$id_virtualtour.'/close.png')) { ?>
                <img crossorigin="anonymous" id="close" src="img/<?php echo $id_virtualtour; ?>/close.png?v=<?php echo time(); ?>" />
            <?php } else { ?>
                <?php if (file_exists(dirname(__FILE__).'/img/custom/close.png')) { ?>
                    <img crossorigin="anonymous" id="close" src="img/custom/close.png?v=<?php echo time(); ?>" />
                <?php } else { ?>
                    <img crossorigin="anonymous" id="close" src="img/close.png?v=3" />
                <?php } ?>
            <?php } ?>
            <?php if (file_exists(dirname(__FILE__).'/img/'.$id_virtualtour.'/poi_image.png')) { ?>
                <img crossorigin="anonymous" id="poi_image" src="img/<?php echo $id_virtualtour; ?>/poi_image.png?v=<?php echo time(); ?>" />
            <?php } else { ?>
                <?php if (file_exists(dirname(__FILE__).'/img/custom/poi_image.png')) { ?>
                    <img crossorigin="anonymous" id="poi_image" src="img/custom/poi_image.png?v=<?php echo time(); ?>" />
                <?php } else { ?>
                    <img crossorigin="anonymous" id="poi_image" src="img/poi_image.png?v=3" />
                <?php } ?>
            <?php } ?>
            <?php if (file_exists(dirname(__FILE__).'/img/'.$id_virtualtour.'/poi_video.png')) { ?>
                <img crossorigin="anonymous" id="poi_video" src="img/<?php echo $id_virtualtour; ?>/poi_video.png?v=<?php echo time(); ?>" />
            <?php } else { ?>
                <?php if (file_exists(dirname(__FILE__).'/img/custom/poi_video.png')) { ?>
                    <img crossorigin="anonymous" id="poi_video" src="img/custom/poi_video.png?v=<?php echo time(); ?>" />
                <?php } else { ?>
                    <img crossorigin="anonymous" id="poi_video" src="img/poi_video.png?v=3" />
                <?php } ?>
            <?php } ?>
            <?php if (file_exists(dirname(__FILE__).'/img/'.$id_virtualtour.'/poi_video360.png')) { ?>
                <img crossorigin="anonymous" id="poi_video360" src="img/<?php echo $id_virtualtour; ?>/poi_video360.png?v=<?php echo time(); ?>" />
            <?php } else { ?>
                <?php if (file_exists(dirname(__FILE__).'/img/custom/poi_video360.png')) { ?>
                    <img crossorigin="anonymous" id="poi_video360" src="img/custom/poi_video360.png?v=<?php echo time(); ?>" />
                <?php } else { ?>
                    <img crossorigin="anonymous" id="poi_video360" src="img/poi_video360.png?v=3" />
                <?php } ?>
            <?php } ?>
            <?php if (file_exists(dirname(__FILE__).'/img/'.$id_virtualtour.'/poi_object3d.png')) { ?>
                <img crossorigin="anonymous" id="poi_object3d" src="img/<?php echo $id_virtualtour; ?>/poi_object3d.png?v=<?php echo time(); ?>" />
            <?php } else { ?>
                <?php if (file_exists(dirname(__FILE__).'/img/custom/poi_object3d.png')) { ?>
                    <img crossorigin="anonymous" id="poi_object3d" src="img/custom/poi_object3d.png?v=<?php echo time(); ?>" />
                <?php } else { ?>
                    <img crossorigin="anonymous" id="poi_object3d" src="img/poi_object3d.png?v=3" />
                <?php } ?>
            <?php } ?>
            <?php if (file_exists(dirname(__FILE__).'/img/'.$id_virtualtour.'/poi_html.png')) { ?>
                <img crossorigin="anonymous" id="poi_html" src="img/<?php echo $id_virtualtour; ?>/poi_html.png?v=<?php echo time(); ?>" />
            <?php } else { ?>
                <?php if (file_exists(dirname(__FILE__).'/img/custom/poi_html.png')) { ?>
                    <img crossorigin="anonymous" id="poi_html" src="img/custom/poi_html.png?v=<?php echo time(); ?>" />
                <?php } else { ?>
                    <img crossorigin="anonymous" id="poi_html" src="img/poi_html.png?v=3" />
                <?php } ?>
            <?php } ?>
            <?php if (file_exists(dirname(__FILE__).'/img/'.$id_virtualtour.'/poi_audio.png')) { ?>
                <img crossorigin="anonymous" id="poi_audio" src="img/<?php echo $id_virtualtour; ?>/poi_audio.png?v=<?php echo time(); ?>" />
            <?php } else { ?>
                <?php if (file_exists(dirname(__FILE__).'/img/custom/poi_audio.png')) { ?>
                    <img crossorigin="anonymous" id="poi_audio" src="img/custom/poi_audio.png?v=<?php echo time(); ?>" />
                <?php } else { ?>
                    <img crossorigin="anonymous" id="poi_audio" src="img/poi_audio.png?v=3" />
                <?php } ?>
            <?php } ?>
            <?php
            $first_room = 0;
            $first_north = 0;
            $first_id_room = 0;
            $first_is_stereo = 0;
            $first_room_name = '';
            $cam_rotation_fix = 0;
            $spot_fix = 90;
            $stereo_exist = false;
            $is_stereo_exist = false;
            $array_rooms_stereo = array();
            foreach ($array_rooms as $room) {
                $id_room = $room['id'];
                if(empty($room['northOffset'])) {
                    $north = 0;
                } else {
                    $north = -$room['northOffset'];
                }
                $panorama_image = $room['panorama_image'];
                $panorama_video = $room['panorama_video'];
                if(!empty($panorama_video)) {
                    echo '<a-entity position="0 0 -1.5" text="align: center;width: 6;wrapCount: 100;color: black;value: Click or tap to start video"></a-entity>';
                    echo "<video crossorigin='anonymous' loop='true' playsinline webkit-playsinline width='3' height='1.5' id='r_$id_room' class='panorama_video' data-north='$north' src='".$path."videos/$panorama_video".(($s3_enabled) ? '?v=s3='.$s3_version : '')."'></video>";
                } else {
                    if($s3_enabled) {
                        $stereo_exist = file_exists("s3://$s3_bucket_name/viewer/panoramas/stereo/$panorama_image");
                        $mobile_exist = file_exists("s3://$s3_bucket_name/viewer/panoramas/mobile/$panorama_image");
                    } else {
                        $stereo_exist = file_exists(dirname(__FILE__).'/../viewer/panoramas/stereo/'.$panorama_image);
                        $mobile_exist = file_exists(dirname(__FILE__).'/../viewer/panoramas/mobile/'.$panorama_image);
                    }
                    if($stereo_exist) {
                        array_push($array_rooms_stereo, $id_room);
                        $north = clampAngle($north+90);
                        echo "<img crossorigin='anonymous' id='r_$id_room' class='panorama_image' data-is-stereo='1' data-north='$north' src='".$path."panoramas/stereo/$panorama_image".(($s3_enabled) ? '?v=s3='.$s3_version : '')."' />";
                    } else {
                        if($is_mobile && $mobile_exist) {
                            echo "<img crossorigin='anonymous' id='r_$id_room' class='panorama_image' data-is-stereo='0' data-north='$north' src='".$path."panoramas/mobile/$panorama_image".(($s3_enabled) ? '?v=s3='.$s3_version : '')."' />";
                        } else {
                            echo "<img crossorigin='anonymous' id='r_$id_room' class='panorama_image' data-is-stereo='0' data-north='$north' src='".$path."panoramas/$panorama_image".(($s3_enabled) ? '?v=s3='.$s3_version : '')."' />";
                        }
                    }
                }
                if($first_room==0) {
                    if($stereo_exist) {
                        $first_is_stereo = 1;
                        $spot_fix = 0;
                    } else {
                        $spot_fix = 90;
                    }
                    $first_north = $north;
                    $first_id_room = $id_room;
                    if($stereo_exist) {
                        $cam_rotation_fix = clampAngle($north-$room['yaw']);
                    } else {
                        $cam_rotation_fix = clampAngle($north+90-$room['yaw']);
                    }
                    //$cam_rotation_fix = clampAngle($north+90-$room['yaw']);
                    $spot_fix = 90;
                    $first_room = $id_room;
                    $first_room_name = $room['name'];
                }
                if($stereo_exist) {
                    $is_stereo_exist = true;
                }
            }
            ?>
            <?php if(!empty($nadir_logo)) {
                echo "<img crossorigin='anonymous' id='nadir' src='".$path."content/$nadir_logo".(($s3_enabled) ? '?v=s3='.$s3_version : '')."' />";
            } ?>
            <?php if(!empty($song)) : ?>
                <audio crossorigin="anonymous" id="background_music" loop="true" src="<?php echo $path; ?>content/<?php echo $song; ?>"></audio>
            <?php endif; ?>
            <?php
            foreach ($array_pois as $id_room => $pois) {
                foreach ($pois as $poi) {
                    $id_poi = $poi['id'];
                    $type = $poi['type'];
                    $content = $poi['content'];
                    switch($type) {
                        case 'image':
                            echo "<img crossorigin='anonymous' id='p_$id_poi' class='poi' src='".$path."$content".(($s3_enabled) ? '?v=s3='.$s3_version : '')."' />";
                            break;
                        case 'video':
                            echo "<video preload='metadata' crossorigin='anonymous' playsinline webkit-playsinline id='p_$id_poi' class='poi' src='".$path."$content".(($s3_enabled) ? '?v=s3='.$s3_version : '')."#t=0.1'></video>";
                            break;
                        case 'object3d':
                            if (strpos($content, ',') !== false) {
                                $tmp_array = explode(",",$content);
                                foreach ($tmp_array as $tmp) {
                                    $tmp2 = strtolower($tmp);
                                    if ((strpos($tmp2, '.glb') !== false) || (strpos($tmp2, '.gltf') !== false)) {
                                        $content = $tmp;
                                    }
                                }
                            }
                            echo "<a-asset-item crossorigin='anonymous' id='p_$id_poi' class='poi' src='".$path."$content".(($s3_enabled) ? '?v=s3='.$s3_version : '')."'></a-asset-item>";
                            break;
                        case 'audio':
                            echo "<audio crossorigin='anonymous' id='p_$id_poi' class='poi' src='".$path."$content".(($s3_enabled) ? '?v=s3='.$s3_version : '')."'></audio>";
                            break;
                        case 'video360':
                            echo "<video crossorigin='anonymous' loop='true' playsinline webkit-playsinline width='3' height='1.5' id='p_$id_poi' class='poi' src='".$path."$content".(($s3_enabled) ? '?v=s3='.$s3_version : '')."'></video>";
                            break;
                    }
                }
            }
            $allowedExtensions = ['jpg', 'jpeg', 'png'];
            if(count($array_pois_icons) > 0) {
                $query = "SELECT id,image FROM svt_icons WHERE id IN (" . implode(",", $array_pois_icons) . ")";
                $result = $mysqli->query($query);
                if($result) {
                    if($result->num_rows>0) {
                        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                            $icon_id = $row['id'];
                            $icon_image = $row['image'];
                            $extension = pathinfo($icon_image, PATHINFO_EXTENSION);
                            if (in_array($extension, $allowedExtensions)) {
                                if($s3_enabled) {
                                    $icon_exist = file_exists("s3://$s3_bucket_name/viewer/icons/$icon_image");
                                } else {
                                    $icon_exist = file_exists(dirname(__FILE__).'/../viewer/icons/'.$icon_image);
                                }
                                if($icon_exist) {
                                    array_push($array_pois_icons_exists,$icon_id);
                                    echo '<img crossorigin="anonymous" id="icon_p_'.$icon_id.'" src="../viewer/icons/'.$icon_image.'" />';
                                }
                            }
                        }
                    }
                }
            }
            if(count($array_markers_icons) > 0) {
                $query = "SELECT id,image FROM svt_icons WHERE id IN (" . implode(",", $array_markers_icons) . ")";
                $result = $mysqli->query($query);
                if($result) {
                    if($result->num_rows>0) {
                        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                            $icon_id = $row['id'];
                            $icon_image = $row['image'];
                            $extension = pathinfo($icon_image, PATHINFO_EXTENSION);
                            if (in_array($extension, $allowedExtensions)) {
                                if($s3_enabled) {
                                    $icon_exist = file_exists("s3://$s3_bucket_name/viewer/icons/$icon_image");
                                } else {
                                    $icon_exist = file_exists(dirname(__FILE__).'/../viewer/icons/'.$icon_image);
                                }
                                if($icon_exist) {
                                    array_push($array_markers_icons_exists,$icon_id);
                                    echo '<img crossorigin="anonymous" id="icon_m_'.$icon_id.'" src="../viewer/icons/'.$icon_image.'" />';
                                }
                            }
                        }
                    }
                }
            }
            ?>
        </a-assets>
        <a-entity light="type: ambient; color: #FFF"></a-entity>
        <a-entity light="type: directional; color: #FFF; intensity: 0.9" position="5 5 1"></a-entity>
        <a-entity raycaster="objects:.landscape,.environmentGround,.environmentDressing; far:0.5;"></a-entity>
        <?php if(!empty($nadir_logo)) : ?>
            <a-entity id='nadir_entity' rotation='0 230 0' scale='1 1 1'>
                <a-entity rotation='-90 0 0'>
                    <a-image material='alpha-test:0.5;transparent:true;' src="#nadir" position='0 0 -50' scale="<?php echo ($nadir_size/10)." ".($nadir_size/10)." ".($nadir_size/10); ?>"></a-image>
                </a-entity>
            </a-entity>
        <?php endif; ?>
        <a-entity id="spots" rotation="0 <?php echo $first_north; ?> 0" hotspots>
            <?php
            foreach ($array_markers as $id_room => $markers) {
                if($id_room==$first_room) {
                    $scale_marker = "1 1 1";
                } else {
                    $scale_marker = "0 0 0";
                }
                $entity = "<a-entity id='markers_$id_room' rotation='0 $spot_fix 0' scale='$scale_marker'>";
                foreach ($markers as $marker) {
                    if(in_array($id_room, $array_rooms_stereo)) {
                        $yaw = -$marker['yaw']-90;
                    } else {
                        $yaw = -$marker['yaw'];
                    }
                    $pitch = $marker['pitch']+$default_z_offsets['marker']-(((abs($marker['pitch'])*$default_z_offsets['marker']/7.5)/20));
                    $id_room_target = $marker['id_room_target'];
                    $name_room_target = $marker['name_room_target'];
                    $id_icon_library = $marker['id_icon_library'];
                    $entity .= "<a-entity data-raycastable rotation='$pitch $yaw 0'>";
                    if($id_icon_library!=0 && in_array($id_icon_library, $array_markers_icons_exists)) {
                        $entity .= "<a-image class='marker_icon' data-raycastable material='alpha-test:0.5;transparent:true;' spot='object:marker;id_icon_library:$id_icon_library;type:;linksource:$id_room;linkto:#r_$id_room_target;spotgroup:markers_$id_room_target;room_name:$name_room_target;' position='0 0 -{$default_z_positions['marker']}'></a-image>";
                    } else {
                        $entity .= "<a-image class='marker_icon' data-raycastable material='alpha-test:0.5;transparent:true;' spot='object:marker;id_icon_library:0;type:;linksource:$id_room;linkto:#r_$id_room_target;spotgroup:markers_$id_room_target;room_name:$name_room_target;' position='0 0 -{$default_z_positions['marker']}'></a-image>";
                    }
                    $entity .= "</a-entity>";
                }
                $entity .= "</a-entity>";
                echo $entity;
            }
            foreach ($array_pois as $id_room => $pois) {
                if($id_room==$first_room) {
                    $scale_poi = "1 1 1";
                } else {
                    $scale_poi = "0 0 0";
                }
                $entity = "<a-entity id='pois_$id_room' rotation='0 $spot_fix 0' scale='$scale_poi'>";
                foreach ($pois as $poi) {
                    $id_poi = $poi['id'];
                    if(in_array($id_room, $array_rooms_stereo)) {
                        $yaw = -$poi['yaw']-90;
                    } else {
                        $yaw = -$poi['yaw'];
                    }
                    $type = $poi['type'];
                    if($hidden_status[$type]) continue;
                    $pitch = $poi['pitch']+$default_z_offsets[$type]-(((abs($poi['pitch'])*$default_z_offsets[$type]/7.5)/20));
                    $pitch_inv = -$pitch;
                    $id_icon_library = $poi['id_icon_library'];
                    $entity .= "<a-entity rotation='$pitch $yaw 0'>";
                    if($id_icon_library!=0 && in_array($id_icon_library, $array_pois_icons_exists)) {
                        $entity .= "<a-image class='poi_icon' data-raycastable material='alpha-test:0.5;transparent:true;' spot='object:poi;id_icon_library:$id_icon_library;type:$type;linkto:#poi_content_$id_poi;spotgroup:pois_$id_room' position='0 0 -{$default_z_positions[$type]}' scale='1 1 1'></a-image>";
                    } else {
                        $entity .= "<a-image class='poi_icon' data-raycastable material='alpha-test:0.5;transparent:true;' spot='object:poi;id_icon_library:0;type:$type;linkto:#poi_content_$id_poi;spotgroup:pois_$id_room' position='0 0 -{$default_z_positions[$type]}' scale='1 1 1'></a-image>";
                    }
                    switch($type) {
                        case 'image':
                            $aspect_ratio = $poi['aspect_ratio'];
                            $width = 13 * $aspect_ratio;
                            $height = 13;
                            $entity .= "<a-image data-width='$width' data-height='$height' data-raycastable id='poi_content_$id_poi' src='#p_$id_poi' rotation='-7 0 0' position='0 0 -11' scale='0 0 0'></a-image>";
                            break;
                        case 'video':
                            $aspect_ratio = $poi['aspect_ratio'];
                            $width = 13 * $aspect_ratio;
                            $height = 13;
                            $entity .= "<a-image id='play_video_poi' billboard data-raycastable material='alpha-test:0.5;transparent:true;' src='#play' scale='0 0 0' rotation='0 0 0' position='0 0.5 -6'></a-image>";
                            $entity .= "<a-video data-width='$width' data-height='$height' data-raycastable id='poi_content_$id_poi' src='#p_$id_poi' rotation='-7 0 0' position='0 0 -11' scale='0 0 0'></a-video>";
                            break;
                        case 'audio':
                            $entity .= "<a-audio data-raycastable id='poi_content_$id_poi' src='#p_$id_poi' rotation='-7 0 0' position='0 0 -11' scale='0 0 0'></a-audio>";
                            break;
                        case 'object3d':
                            if($pitch<0) {
                                $fix_pos_close = -11;
                            } else {
                                $fix_pos_close = -9;
                            }
                            $entity .= "<a-entity data-raycastable id='poi_content_$id_poi' rotation='$pitch_inv 0 0' position='0 -4 -11' scale='1 1 1' visible='false'>
                                        <a-entity id='object3d_$id_poi' data-pitch='$pitch_inv' natural-size='height:10;' gltf-model='#p_$id_poi' position='0 0 0' animation='property: rotation; to: 0 360 0; easing: linear; loop: true; dur: 10000'></a-entity>
                                    </a-entity>";
                            $entity .= "<a-image id='close_object3d_$id_poi' billboard data-raycastable material='alpha-test:0.5;transparent:true;' src='#close' scale='0 0 0' rotation='$pitch 0 0' position='0 $fix_pos_close -11'></a-image>";
                            break;
                        case 'html':
                            $text = $poi['content'];
                            $text = str_replace('<p>','',$text);
                            $text = str_replace('</p>','\n',$text);
                            $text = preg_replace('/ style="[^"]*"/', '', $text);
                            $text = strip_tags($text,'<li>');
                            $text = str_replace("<li>","- ",$text);
                            $text = str_replace("</li>","\n",$text);
                            $text = str_replace('"',"''",$text);
                            $entity .= "<a-entity data-width='13' data-height='13' data-raycastable id='poi_content_$id_poi' material='alpha-test:0.5;opacity:0.75;transparent:true;color:black;' geometry='primitive:plane;width:auto;height:auto;' text=\"width:auto;color:#fff;align:center;zOffset:1;value:$text;\" planepadder='padding:0.2;addPadding:true;' rotation='-7 0 0' position='0 0 -11' scale='0 0 0' visible='false'></a-entity>";
                            break;
                    }
                    $entity .= "</a-entity>";
                }
                $entity .= "</a-entity>";
                echo $entity;
            }
            ?>
        </a-entity>
        <a-entity id="video_play_wrapper" rotation="0 <?php echo $cam_rotation_fix; ?> 0">
            <a-entity rotation="0 <?php echo $first_north; ?> 0">
                <a-image id='play_video' billboard data-raycastable material='alpha-test:0.5;transparent:true;' src='#play' scale='0 0 0' rotation='0 0 0' position='0 0.5 -6'></a-image>
            </a-entity>
        </a-entity>
        <a-image id='close_video360' billboard data-raycastable material='alpha-test:0.5;transparent:true;' src='#close' scale='0 0 0' rotation='0 0 0' position='0 -11 0'></a-image>
        <?php if(!empty($song)) : ?>
            <a-sound src="#background_music"></a-sound>
        <?php endif; ?>
        <a-sky id="overlay" radius="400" opacity="0" color="#000000" position="0 0 0"></a-sky>
        <a-sky <?php if($first_is_stereo==1) { echo 'stereo="eye:left;split:vertical;mode:full;"'; } ?> id="skybox" data-id-room="<?php echo $first_id_room;?>" src="#r_<?php echo $first_id_room; ?>" position="0 0 0" rotation="0 <?php echo $first_north; ?> 0"></a-sky>
        <a-sky <?php if($first_is_stereo==1) { echo 'stereo="eye:right;split:vertical;mode:full;" visible="true"'; } else { echo 'visible="false"'; } ?> id="skybox_2" data-id-room="<?php echo $first_id_room;?>" src="#r_<?php echo $first_id_room; ?>" position="0 0 0" rotation="0 <?php echo $first_north; ?> 0"></a-sky>
        <a-entity id=“cam_wrapper” rotation="0 <?php echo $cam_rotation_fix; ?> 0">
            <a-entity stereocam="eye:left;" id="cam" position="0 1.6 0" camera="fov:90;" camera look-controls="touchEnabled: false"
                      animation__zoomin="property:camera.fov;dur:600;to:60;startEvents:zoomin;"
                      animation__zoomout="property:camera.fov;dur:400;to:80;startEvents:zoomout;">
                <a-text font="font/Roboto-Regular-msdf.json" font-image="font/Roboto-Regular.png" negate="false" id="room_name" value="<?php echo $first_room_name; ?>" color="white" align="center" position="0 0.25 -4" scale="0 0 0" opacity="0"
                        animation__fadein="property:opacity;to:1;dur:400;startEvents:roomNameFadeIn"
                        animation__fadeout="property:opacity;to:0;dur:400;startEvents:roomNameFadeOut"></a-text>
                <a-text font="font/Roboto-Regular-msdf.json" font-image="font/Roboto-Regular.png" negate="false" id="msg_close_video" value="<?php echo _("Look down to close the video"); ?>" color="white" align="center" position="0 0.25 -4" scale="0 0 0"></a-text>
                <a-text font="font/Roboto-Regular-msdf.json" font-image="font/Roboto-Regular.png" negate="false" id="msg_video_noaudio" value="<?php echo _("Click to start play the video"); ?>" color="white" align="center" position="0 0.25 -4" scale="0 0 0"></a-text>
                <a-entity id="cursor-visual" cursor="fuse:true;fuseTimeout:2000"
                          material="shader:flat;color:#ffffff;opacity:1;"
                          position="0 0 -0.7"
                          geometry="primitive: ring; radiusInner: 0.01; radiusOuter: 0.015; thetaStart: 0; thetaLength: 0;"
                          raycaster="objects: [data-raycastable]"
                          animation="property: geometry.thetaLength; dir: alternate; dur: 250; easing: easeInSine; from:0;to: 360;startEvents:startFuseFix;pauseEvents:stopFuse;autoplay:false"
                          animation__mouseenter="property: geometry.thetaLength; dir: alternate; dur: 2000; easing: easeInSine; from:0;to: 360;startEvents:startFuse;pauseEvents:stopFuse;autoplay:false"
                          animation__mouseleave="property: geometry.thetaLength; dir: alternate; dur: 500; easing: easeInSine; to: 0;startEvents:stopFuse;autoplay:false">
                    <a-entity id="cursor-visual-bg" position="0 0 -0.01" geometry="primitive: ring; radiusOuter: 0.015; radiusInner: 0.01;" material="shader: flat; color: #000000; opacity: 1;"></a-entity>
                    <a-entity position="0 0 -0.04" geometry="primitive: ring; radiusInner: 0.008; radiusOuter: 0.018;" material="shader: flat; color: #ffffff; opacity: 0.4;"></a-entity>
                </a-entity>
                <a-plane id="camfadeplane" rotation="10 0.5 0" position="0 0 -0.5" material="color:#000000;transparent:true;opacity:0" width="3" height="3"
                         animation__fadein="property:material.opacity;to:1;dur:300;startEvents:camFadeIn"
                         animation__fadeout="property:material.opacity;to:0;dur:200;startEvents:camFadeOut"></a-plane>
            </a-entity>
        </a-entity>
    </a-scene>
    <?php if($ga_tracking_id!='' && $export==0) : ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $ga_tracking_id; ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?php echo $ga_tracking_id; ?>');
        </script>
    <?php endif; ?>
    <?php if($export==0) : ?>
        <script>
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('service-worker.js', {
                    scope: '.'
                });
            }
        </script>
    <?php endif; ?>
    <script>
        window.id_frist_room = <?php echo $first_room; ?>;
        window.part_viewer = '<?php echo $part_viewer; ?>';
        window.first_is_stereo = <?php echo $first_is_stereo; ?>;
        window.is_stereo_exist = <?php echo ($is_stereo_exist) ? 1 : 0; ?>;
    </script>
    <script src="js/nosleep.min.js"></script>
    <script>
        var noSleep = new NoSleep();
        document.addEventListener('click', function enableNoSleep() {
            document.removeEventListener('click', enableNoSleep, false);
            noSleep.enable();
        }, false);
    </script>
    </body>
    </html>
<?php
function print_favicons_vt($code,$logo,$export) {
    $path = '';
    $version = time();
    $path_m = 'vr_'.$code.'/';
    if (file_exists(dirname(__FILE__).'/../favicons/vr_'.$code.'/favicon.ico')) {
        $path = $path_m;
        $version = preg_replace('/[^0-9]/', '', $logo);
    } else if (file_exists(dirname(__FILE__).'/../favicons/custom/favicon.ico')) {
        $path = 'custom/';
    }
    if($export==1) {
        $path = "favicons/".$path;
        $manifest = "";
    } else {
        $path = "../favicons/".$path;
        if (file_exists(dirname(__FILE__).'/../favicons/vr_'.$code.'/site.webmanifest')) {
            $manifest = '<link rel="manifest" href="../favicons/'.$path_m.'site.webmanifest?v='.$version.'">';
        } else {
            $manifest = "";
        }
    }
    return '<link rel="apple-touch-icon" sizes="180x180" href="'.$path.'apple-touch-icon.png?v='.$version.'">
    <link rel="icon" type="image/png" sizes="32x32" href="'.$path.'favicon-32x32.png?v='.$version.'">
    <link rel="icon" type="image/png" sizes="16x16" href="'.$path.'favicon-16x16.png?v='.$version.'">
    '.$manifest.'
    <link rel="mask-icon" href="'.$path.'safari-pinned-tab.svg?v='.$version.'" color="#ffffff">
    <link rel="shortcut icon" href="'.$path.'favicon.ico?v='.$version.'">
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-config" content="'.$path.'browserconfig.xml?v='.$version.'">
    <meta name="theme-color" content="#ffffff">';
}
function getIPAddress() {
    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else{
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
?>