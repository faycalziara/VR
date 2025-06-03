<?php
header('Access-Control-Allow-Origin: *');
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
require_once("../db/connection.php");
include_once("../config/languages.inc.php");
require_once("../backend/functions.php");
if(check_maintenance_mode('viewer')) {
    if(file_exists("../error_pages/custom/maintenance_viewer.html")) {
        include("../error_pages/custom/maintenance_viewer.html");
    } else {
        include("../error_pages/default/maintenance_viewer.html");
    }
    exit;
}
try {
    $mysqli->query("SET GLOBAL sort_buffer_size = 1024 * 1024 * 4;");
} catch (mysqli_sql_exception $e) {}
$v = time();
$array_vt = array();
$array_cat = array();
$s_languages_enabled = array();
$array_s_lang = array();
$header_html = '';
$footer_html = '';
$s3Client = null;
$s3_url = '';
$open_target = 'self';
$cookie_consent = false;
$pwa_enable = true;
$ga_tracking_id = "";
$furl = "";
$s_language_force = "";
if((isset($_GET['furl'])) || (isset($_GET['code']))) {
    if(isset($_GET['furl'])) {
        $furl = $_GET['furl'];
        $furl = str_replace("'","\'",$_GET['furl']);
        if(strpos($furl, "@")!==false) {
            $parts = explode("@", $furl);
            $last_part = end($parts);
            if(strpos($last_part, "_")!==false) {
                $s_language_force = $last_part;
                $furl = str_replace("@$last_part","",$furl);
            }
        }
        $where = "(friendly_url = '$furl' OR code = '$furl')";
    }
    if(isset($_GET['lang'])) {
        $s_language_force = $_GET['lang'];
    }
    if(isset($_GET['code'])) {
        $code = $_GET['code'];
        $where = "code = '$code'";
    }
    $query = "SELECT id,code,name,banner,logo,bg_color,header_html,footer_html,meta_title,meta_description,meta_image,sort_settings,open_target,cookie_consent,ga_tracking_id,language,languages_enabled,pwa_enable FROM svt_showcases WHERE $where LIMIT 1;";
    $result = $mysqli->query($query);
    if ($result) {
        if ($result->num_rows==1) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $id_s = $row['id'];
            $s_language = $row['language'];
            if(empty($s_language)) $s_language='';
            if(!empty($row['languages_enabled'])) {
                $s_languages_enabled=json_decode($row['languages_enabled'],true);
            }
            if(!$row['pwa_enable']) {
                $pwa_enable = false;
            }
            $query_s = "SELECT language FROM svt_settings LIMIT 1;";
            $result_s = $mysqli->query($query_s);
            if($result_s) {
                if ($result_s->num_rows == 1) {
                    $row_s = $result_s->fetch_array(MYSQLI_ASSOC);
                    if(!empty($s_language)) {
                        $language = $s_language;
                    } else {
                        $language = $row_s['language'];
                    }
                    $default_language = $language;
                    if(array_key_exists($language,$s_languages_enabled)) {
                        $s_languages_enabled[$language]=1;
                    }
                    if(array_key_exists($s_language_force,$s_languages_enabled)) {
                        $language = $s_language_force;
                    }
                    $query_lang = "SELECT * FROM svt_showcases_lang WHERE language='$language' AND id_showcase=$id_s";
                    $result_lang = $mysqli->query($query_lang);
                    if($result_lang) {
                        if ($result_lang->num_rows == 1) {
                            $row_lang = $result_lang->fetch_array(MYSQLI_ASSOC);
                            unset($row_lang['id_showcase']);
                            unset($row_lang['language']);
                            $array_s_lang=$row_lang;
                        }
                    }
                }
            }
            if(!empty($row['name']) && !empty($array_s_lang['name'])) {
                $row['name']=$array_s_lang['name'];
            }
            if(!empty($row['header_html']) && !empty($array_s_lang['header_html'])) {
                $row['header_html']=$array_s_lang['header_html'];
            }
            if(!empty($row['footer_html']) && !empty($array_s_lang['footer_html'])) {
                $row['footer_html']=$array_s_lang['footer_html'];
            }
            if(!empty($row['meta_title']) && !empty($array_s_lang['meta_title'])) {
                $row['meta_title']=$array_s_lang['meta_title'];
            }
            if(!empty($row['meta_description']) && !empty($array_s_lang['meta_description'])) {
                $row['meta_description']=$array_s_lang['meta_description'];
            }
            $code = $row['code'];
            $name_s = $row['name'];
            $banner_s = $row['banner'];
            $logo_s = $row['logo'];
            $bg_color_s = $row['bg_color'];
            $header_html = $row['header_html'];
            $footer_html = $row['footer_html'];
            $sort_settings = $row['sort_settings'];
            $cookie_consent = $row['cookie_consent'];
            $ga_tracking_id = $row['ga_tracking_id'];
            $open_target = $row['open_target'];
            if(empty($row['meta_title'])) {
                $meta_title = $name_s;
            } else {
                $meta_title = $row['meta_title'];
            }
            if(empty($row['meta_description'])) {
                $meta_description = '';
            } else {
                $meta_description = $row['meta_description'];
            }
            if(empty($row['meta_image'])) {
                $meta_image = $row['banner'];
            } else {
                $meta_image = $row['meta_image'];
            }
            $query_list = "SELECT v.id,v.language,v.languages_enabled,v.date_created,s.type_viewer,s.priority,v.code,v.author,COALESCE(vl.name,v.name) as title,COALESCE(vl.description,v.description) as description,v.background_image as image,r.panorama_image,r.min_yaw,r.max_yaw,r.haov,r.vaov,r.hfov,GROUP_CONCAT(DISTINCT c.id) as id_category,GROUP_CONCAT(DISTINCT c.name) as name_category,'-' as total_access
                        FROM svt_showcase_list as s
                        JOIN svt_virtualtours as v ON s.id_virtualtour=v.id
                        LEFT JOIN svt_virtualtours_lang as vl ON vl.id_virtualtour=v.id AND vl.language='$language'
                        LEFT JOIN svt_category_vt_assoc as ca ON ca.id_virtualtour=v.id 
                        LEFT JOIN svt_categories as c ON c.id=ca.id_category
                        LEFT JOIN svt_rooms as r ON r.id_virtualtour=v.id AND r.id=(SELECT id FROM svt_rooms WHERE id_virtualtour=v.id ORDER BY priority LIMIT 1)
                        WHERE s.id_showcase=$id_s AND v.active=1
                        GROUP BY v.id,v.date_created,s.type_viewer,s.priority,v.code,v.author,v.name,vl.name,v.description,vl.description,v.background_image,r.panorama_image,r.min_yaw,r.max_yaw,r.haov,r.vaov,r.hfov;";
            $result_list = $mysqli->query($query_list);
            if($result_list) {
                if($result_list->num_rows>0) {
                    while($row_list = $result_list->fetch_array(MYSQLI_ASSOC)) {
                        $id_vt = $row_list['id'];
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
                        if(empty($row_list['image'])) {
                            if(!empty($row_list['panorama_image'])) {
                                if($s3_enabled) {
                                    $row_list['image']=$s3_url.'viewer/panoramas/preview/'.$row_list['panorama_image'];
                                } else {
                                    $row_list['image']='../viewer/panoramas/preview/'.$row_list['panorama_image'];
                                }
                            }
                        } else {
                            if($s3_enabled) {
                                $row_list['image']=$s3_url.'viewer/content/'.$row_list['image'];
                            } else {
                                $row_list['image']='../viewer/content/'.$row_list['image'];
                            }
                        }
                        $row_list['date']=strtotime($row_list['date_created']);
                        $row_list['s3'] = ($s3_enabled) ? 1 : 0;
                        if(!empty($row_list['languages_enabled'])) {
                            $row_list['languages_enabled']=json_decode($row_list['languages_enabled'],true);
                        } else {
                            $row_list['languages_enabled']=array();
                        }
                        $default_language = $row_list['language'];
                        if(empty($default_language)) {
                            $default_language = $row_s['language'];
                        }
                        $row_list['languages_enabled'][$default_language]=1;
                        $array_languages = array();
                        foreach ($row_list['languages_enabled'] as $lang=>$enabled) {
                            if($enabled==1) {
                                array_push($array_languages,$lang);
                            }
                        }
                        $row_list['default_language']=$default_language;
                        $row_list['languages']=$array_languages;
                        if(array_key_exists($language,$row_list['languages_enabled'])) {
                            if($row_list['languages_enabled'][$language]==1) {
                                $row_list['language']=$language;
                            }
                        }
                        $array_vt[] = $row_list;
                    }
                    $query_cat = "SELECT DISTINCT sc.id,COALESCE(scl.name,sc.name) as name,sc.icon,sc.background,sc.color,sc.position FROM svt_showcase_list as s
                                    JOIN svt_virtualtours sv on s.id_virtualtour = sv.id
                                    JOIN svt_category_vt_assoc scva on s.id_virtualtour = scva.id_virtualtour
                                    JOIN svt_categories sc on scva.id_category = sc.id
                                    LEFT JOIN svt_categories_lang scl on scl.id_category=sc.id AND scl.language='$language'
                                    WHERE s.id_showcase=$id_s AND sv.active=1
                                    ORDER BY sc.position;";
                    $result_cat = $mysqli->query($query_cat);
                    if($result_cat) {
                        if ($result_cat->num_rows > 0) {
                            while ($row_cat = $result_cat->fetch_array(MYSQLI_ASSOC)) {
                                $category = $row_cat['id']."|".$row_cat['name']."|".$row_cat['icon']."|".$row_cat['background']."|".$row_cat['color'];
                                if(!in_array($category,$array_cat)) {
                                    array_push($array_cat,$category);
                                }
                            }
                        }
                    }
                }
            } else {
                if(file_exists("../error_pages/custom/invalid_showcase.html")) {
                    include("../error_pages/custom/invalid_showcase.html");
                } else {
                    include("../error_pages/default/invalid_showcase.html");
                }
                exit;
            }
        } else {
            if(file_exists("../error_pages/custom/invalid_showcase.html")) {
                include("../error_pages/custom/invalid_showcase.html");
            } else {
                include("../error_pages/default/invalid_showcase.html");
            }
            exit;
        }
    } else {
        if(file_exists("../error_pages/custom/invalid_showcase.html")) {
            include("../error_pages/custom/invalid_showcase.html");
        } else {
            include("../error_pages/default/invalid_showcase.html");
        }
        exit;
    }
} else {
    if(file_exists("../error_pages/custom/invalid_showcase.html")) {
        include("../error_pages/custom/invalid_showcase.html");
    } else {
        include("../error_pages/default/invalid_showcase.html");
    }
    exit;
}
$font_provider = 'google';
$font_backend = "";
$cookie_policy = "";
$query = "SELECT language,language_domain,font_provider,font_backend,cookie_policy,social_wechat_id,social_wechat_secret,pwa_enable FROM svt_settings LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row=$result->fetch_array(MYSQLI_ASSOC);
        $font_provider = $row['font_provider'];
        $font_backend = $row['font_backend'];
        $cookie_policy = $row['cookie_policy'];
        $social_wechat_id = $row['social_wechat_id'];
        $social_wechat_secret = $row['social_wechat_secret'];
        if(!$row['pwa_enable']) {
            $pwa_enable = false;
        }
        switch($language) {
            case 'pt_BR':
                $lang_code='pt-BR';
                break;
            case 'pt_PT':
                $lang_code='pt-PT';
                break;
            case 'zh_CN':
                $lang_code='zh-CN';
                break;
            case 'zh_TW':
                $lang_code='zh-TW';
                break;
            case 'zh_HK':
                $lang_code='zh-hk';
                break;
            default:
                $lang_code = substr($language, 0, 2);
                break;
        }
        if (function_exists('gettext')) {
            if(defined('LC_MESSAGES')) {
                $result = setlocale(LC_MESSAGES, $language);
                if(!$result) {
                    setlocale(LC_MESSAGES, $language.'.UTF-8');
                }
                if (function_exists('putenv')) {
                    $result = putenv('LC_MESSAGES='.$language);
                    if(!$result) {
                        putenv('LC_MESSAGES='.$language.'.UTF-8');
                    }
                }
            } else {
                if (function_exists('putenv')) {
                    $result = putenv('LC_ALL='.$language);
                    if(!$result) {
                        putenv('LC_ALL='.$language.'.UTF-8');
                    }
                }
            }
            $domain = $row['language_domain'];
            if(!file_exists("../locale/".$language."/LC_MESSAGES/custom.mo")) {
                $domain = "default";
            }
            $result = bindtextdomain($domain, "../locale");
            if(!$result) {
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
$count_order=0;
$currentPath = $_SERVER['PHP_SELF'];
$pathInfo = pathinfo($currentPath);
$hostName = $_SERVER['HTTP_HOST'];
if (is_ssl()) { $protocol = 'https'; } else { $protocol = 'http'; }
$url = $protocol."://".$hostName.$pathInfo['dirname']."/";
$url = str_replace("/showcase/","/",$url);
$count_l = 0;
foreach ($s_languages_enabled as $check_l => $enabled_l) {
    if($enabled_l == 1 && $check_l != $default_language) {
        $count_l ++;
    }
}
switch($lang_code) {
    case 'ar':
    case 'he':
    case 'fa':
        $dir = "rtl";
        break;
    default:
        $dir = "ltr";
        break;
}
if(isset($_GET['no_pwa'])) {
    $no_pwa = $_GET['no_pwa'];
} else {
    $no_pwa = 0;
}
?>
<!DOCTYPE HTML>
<html dir="<?php echo $dir; ?>" lang="<?php echo $lang_code; ?>">
<head>
    <title><?php echo $meta_title; ?></title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no, maximum-scale=1, minimum-scale=1">
    <meta property="og:type" content="website">
    <meta property="twitter:card" content="summary_large_image">
    <meta property="og:url" content="<?php echo $url."showcase/index.php?code=".$code; ?>">
    <meta property="twitter:url" content="<?php echo $url."showcase/index.php?code=".$code; ?>">
    <meta itemprop="name" content="<?php echo $meta_title; ?>">
    <meta property="og:title" content="<?php echo $meta_title; ?>">
    <meta property="twitter:title" content="<?php echo $meta_title; ?>">
    <?php if($meta_image!='') : ?>
        <meta itemprop="image" content="<?php echo $url."viewer/content/".$meta_image; ?>">
        <meta property="og:image" content="<?php echo $url."viewer/content/".$meta_image; ?>" />
        <meta property="twitter:image" content="<?php echo $url."viewer/content/".$meta_image; ?>">
    <?php endif; ?>
    <?php if($meta_description!='') : ?>
        <meta itemprop="description" content="<?php echo $meta_description; ?>">
        <meta name="description" content="<?php echo $meta_description; ?>"/>
        <meta property="og:description" content="<?php echo $meta_description; ?>" />
        <meta property="twitter:description" content="<?php echo $meta_description; ?>">
    <?php endif; ?>
    <?php echo print_favicons_showcase($code,$logo_s,$bg_color_s); ?>
    <?php switch ($font_provider) {
        case 'google': ?>
            <?php if($cookie_consent) { ?>
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <script type="text/plain" data-category="functionality" data-service="Google Fonts">
                    (function(d, l, s) {
                        const fontName = '<?php echo $font_backend; ?>';
                            const e = d.createElement(l);
                            e.rel = s;
                            e.type = 'text/css';
                            e.href = `https://fonts.googleapis.com/css2?family=${fontName}`;
                            e.id = 'font_backend_link';
                            d.head.appendChild(e);
                          })(document, 'link', 'stylesheet');
                </script>
            <?php } else { ?>
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                <link rel='stylesheet' type="text/css" crossorigin="anonymous" id="font_backend_link" href="https://fonts.googleapis.com/css2?family=<?php echo $font_backend; ?>">
            <?php } ?>
        <?php break;
        case 'collabs': ?>
            <link rel="preconnect" href="https://api.fonts.coollabs.io" crossorigin>
            <link rel="stylesheet" type="text/css" id="font_backend_link" href="https://api.fonts.coollabs.io/css2?family=<?php echo $font_backend; ?>&display=swap">
        <?php break;
        default: ?>
            <link rel="stylesheet" type="text/css" crossorigin="anonymous" id="font_backend_link" href="">
            <?php break;
    } ?>
    <link rel="stylesheet" type="text/css" href="../viewer/vendor/fontawesome-free/css/fontawesome.min.css?v=6.5.1">
    <link rel="stylesheet" type="text/css" href="../viewer/vendor/fontawesome-free/css/solid.min.css?v=6.5.1">
    <link rel="stylesheet" type="text/css" href="../viewer/vendor/fontawesome-free/css/regular.min.css?v=6.5.1">
    <link rel="stylesheet" type="text/css" href="../viewer/vendor/fontawesome-free/css/brands.min.css?v=6.5.1">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type='text/css' href="../viewer/css/pannellum.css"/>
    <link rel="stylesheet" type="text/css" href="../viewer/vendor/tooltipster/css/tooltipster.bundle.min.css" />
    <link rel="stylesheet" type="text/css" href="../viewer/vendor/tooltipster/css/plugins/tooltipster/sideTip/themes/tooltipster-sideTip-borderless.min.css" />
    <?php if($cookie_consent) : ?>
        <link rel="stylesheet" type="text/css" href="../backend/vendor/cookieconsent/cookieconsent.min.css?v=3.0.1">
    <?php endif; ?>
    <link rel="stylesheet" type="text/css" href="css/index.css?v=<?php echo $v; ?>">
    <?php if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'custom_'.$code.'.css')) : ?>
        <link rel="stylesheet" type="text/css" href="css/custom_<?php echo $code; ?>.css?v=<?php echo $v; ?>">
    <?php endif; ?>
    <script type="text/javascript" src="js/jquery.min.js?v=3.7.1"></script>
    <script type="text/javascript" src="js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="js/jquery.searchable-1.1.0.min.js"></script>
    <script type="text/javascript" src="../viewer/js/libpannellum.js?v=<?php echo $v; ?>"></script>
    <script type="text/javascript" src="../viewer/js/pannellum.js?v=<?php echo $v; ?>"></script>
    <script type="text/javascript" src="../viewer/vendor/tooltipster/js/tooltipster.bundle.min.js"></script>
    <?php if($cookie_consent) : ?>
        <script type="text/javascript" src="../backend/vendor/cookieconsent/cookieconsent.min.js?v=3.0.1"></script>
    <?php endif; ?>
</head>
<body style="background: <?php echo $bg_color_s; ?>">
<script>
    $(document).ready(function(){
        var btn = $('#backToTop');
        $(window).on('scroll', function() {
            if ($(window).scrollTop() > 300) {
                btn.addClass('show');
            } else {
                btn.removeClass('show');
            }
        });
        btn.on('click', function(e) {
            e.preventDefault();
            $('html, body').animate({
                scrollTop: 0
            }, '300');
        });
    });
</script>
<a href="javascript:void(0);" id="backToTop" class="back-to-top">
    <i class="arrow"></i><i class="arrow"></i>
</a>
<style>
    :root {
        --bg_color: <?php echo $bg_color_s; ?>;
    }
    .header:before {
        <?php if(!empty($banner_s)) { ?>
        background-image: url('../viewer/content/<?php echo $banner_s; ?>');
        <?php } else { ?>
        background-color: rgba(0,0,0,0.4);
        <?php } ?>
    }
    .frame_banner:before {
    <?php if(!empty($banner_s)) : ?>
        background-image: url('../viewer/content/<?php echo $banner_s; ?>');
    <?php endif; ?>
    }
    <?php if(empty($banner_s)) { ?>
    .header {
        height: auto;
        min-height: 100px;
    }
    .info {
        padding-top: 25px;
    }
    .info h1 {
        margin-bottom: 0;
    }
    .header:after {
        background: none;
    }
    .logo img {
        margin-top: 10px;
        margin-bottom: 25px;
    }
    .frame_banner:before {
        background: none;
    }
    <?php } ?>
</style>
<div class="showcase noselect">
    <div class="header">
        <div class="info">
            <h1><?php echo $name_s; ?></h1>
            <?php if(!empty($logo_s)) : ?>
                <div class="logo">
                    <img src="../viewer/content/<?php echo $logo_s; ?>" />
                </div>
            <?php endif; ?>
        </div>
    </div>
    <div class="language_selector <?php echo ($count_l==0) ? 'd-none' : ''; ?>" dir="ltr">
        <?php if ((!(array_key_exists('en_US', $s_languages_enabled) && $s_languages_enabled['en_US'] == 1) && (array_key_exists('en_GB', $s_languages_enabled) && $s_languages_enabled['en_GB'] == 1)) || ((array_key_exists('en_US', $s_languages_enabled) && $s_languages_enabled['en_US'] == 1) && !(array_key_exists('en_GB', $s_languages_enabled) && $s_languages_enabled['en_GB'] == 1))) {
            $languages_list['en_GB']['name'] = "English";
            $languages_list['en_US']['name'] = "English";
        }
        foreach($languages_list as $lang_code => $lang_data) {
            if(array_key_exists($lang_code, $s_languages_enabled) && $s_languages_enabled[$lang_code] == 1) : ?>
                <div class="<?php echo ($language==$lang_code) ? 'active' : ''; ?>" onclick="change_language_s('<?php echo $language; ?>','<?php echo $lang_code; ?>')">
                    <img class="tooltip_l" title="<?php echo $lang_data['name']; ?>" src="../viewer/css/flags_lang/<?php echo $lang_code; ?>.png?v=2" />
                </div>
        <?php endif; } ?>
    </div>
    <div class="custom_header <?php echo (empty($header_html)) ? 'd-none' : ''; ?>">
        <?php echo html_entity_decode($header_html); ?>
    </div>
    <div class='categories'>
        <?php
        $array_cat_icons = array();
        if(count($array_cat)>1) {
            echo "<button style='background-color:black;color:white' id='btn_cat_all' onclick=\"filter_cat('all');\" class='btn btn_cat btn-sm mb-1 active'>"._("Show All")."&nbsp;&nbsp;<i class='icon_cat_check fas fa-circle-check'></i></button>";
            foreach ($array_cat as $category) {
                $res = explode("|",$category);
                $id_cat = $res[0];
                $name_cat = $res[1];
                $icon_cat = $res[2];
                if(!empty($icon_cat)) {
                    $name_cat = "<i class='$icon_cat'></i> ".$name_cat;
                }
                $background_cat = $res[3];
                $color_cat = $res[4];
                if(!array_key_exists($id_cat, $array_cat_icons)) {
                    if(empty($icon_cat)) {
                        $array_cat_icons[$id_cat] = "<div style='background-color:{$background_cat};color:{$color_cat}'></div>";
                    } else {
                        $array_cat_icons[$id_cat] = "<div style='background-color:{$background_cat};color:{$color_cat}'><i class='$icon_cat'></i></div>";
                    }
                }
                echo "<button style='background-color:{$background_cat};color:{$color_cat}' id='btn_cat_$id_cat' onclick='filter_cat($id_cat);' class='btn btn_cat btn-sm mb-1'>$name_cat&nbsp;&nbsp;<i class='icon_cat_check far fa-circle'></i></button>";
            }
        }
        if(!empty($sort_settings)) {
            $sort_settings = json_decode($sort_settings,true);
        } else {
            $sort_settings = array();
            $sort_settings['date']=1;
            $sort_settings['relevance']=1;
            $sort_settings['name']=1;
            $sort_settings['category']=1;
            $sort_settings['author']=1;
            $sort_settings['views']=1;
            $sort_settings['default']='date|asc';
        }
        $count_sort = 0;
        if($sort_settings['date']==1) $count_sort++;
        if($sort_settings['relevance']==1) $count_sort++;
        if($sort_settings['name']==1) $count_sort++;
        if($sort_settings['category']==1) $count_sort++;
        if($sort_settings['author']==1) $count_sort++;
        if($sort_settings['views']==1) $count_sort++;
        $default_sort_type = explode("|",$sort_settings['default'])[0];
        $default_sort_by = explode("|",$sort_settings['default'])[1];
        if($sort_settings[$default_sort_type]==0) {
            foreach ($sort_settings as $index => $value) {
                if ($value === 1) {
                    $default_sort_type = $index;
                    $default_sort_by = 'asc';
                    break;
                }
            }
        }
        ?>
        <div class="mt-3 d-block">
            <div id="btn_sort_by" class="btn-group mb-1 <?php echo ($count_sort<=1) ? 'd-none' : ''; ?>">
                <button id="btn_sort_by_type" type="button" class="btn btn-sm bg-light text-dark border border-secondary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <?php echo _("Date"); ?>
                </button>
                <div class="dropdown-menu">
                    <a id="sort_date" onclick="change_sorty_by_type('date');" class="dropdown-item <?php echo ($sort_settings['date']==1) ? '' : 'd-none'; ?>" href="#"><?php echo _("Date"); ?></a>
                    <a id="sort_relevance" onclick="change_sorty_by_type('relevance');" class="dropdown-item <?php echo ($sort_settings['relevance']==1) ? '' : 'd-none'; ?>" href="#"><?php echo _("Relevance"); ?></a>
                    <a id="sort_name" onclick="change_sorty_by_type('name');" class="dropdown-item <?php echo ($sort_settings['name']==1) ? '' : 'd-none'; ?>" href="#"><?php echo _("Name"); ?></a>
                    <a id="sort_category" onclick="change_sorty_by_type('category');" class="dropdown-item <?php echo ($sort_settings['category']==1) ? '' : 'd-none'; ?>" href="#"><?php echo _("Category"); ?></a>
                    <a id="sort_author" onclick="change_sorty_by_type('author');" class="dropdown-item <?php echo ($sort_settings['author']==1) ? '' : 'd-none'; ?>" href="#"><?php echo _("Author"); ?></a>
                    <a id="sort_views" onclick="change_sorty_by_type('views');" class="dropdown-item disabled <?php echo ($sort_settings['views']==1) ? '' : 'd-none'; ?>" href="#"><?php echo _("Views"); ?></a>
                </div>
                <button onclick="change_sorty_by_order();" id="btn_sort_by_order" class="btn btn-sm bg-light text-dark border border-secondary"><i class="fas fa-sort-alpha-down"></i></button>
            </div>
            <div id="search_div" class="mb-1 d-inline-block">
                <input placeholder="<?php echo _("Search"); ?>..." type="text" class="form-control form-control-sm search_input" style="width:160px">
            </div>
        </div>
    </div>
    <section>
        <div class="container">
            <div id="showcase_container" class="d-flex flex-row flex-wrap">
                <?php foreach ($array_vt as $vt) {
                    $count_order++;
                    ?>
                    <div id="vt_<?php echo $vt['id']; ?>" style="order: <?php echo $count_order; ?>;" class="col-xl-3 col-lg-4 col-sm-6 col-xs-12 div_vt">
                        <div data-id="<?php echo $vt['id']; ?>" data-s3="<?php echo $vt['s3']; ?>" data-name="<?php echo $vt['title']; ?>" data-author="<?php echo $vt['author']; ?>" data-panorama="<?php echo $vt['panorama_image']; ?>" data-min_yaw="<?php echo $vt['min_yaw']; ?>" data-max_yaw="<?php echo $vt['max_yaw']; ?>" data-haov="<?php echo $vt['haov']; ?>" data-vaov="<?php echo $vt['vaov']; ?>" data-hfov="<?php echo $vt['hfov']; ?>" data-image="<?php echo $vt['image']; ?>" data-type="<?php echo $vt['type_viewer']; ?>" data-priority="<?php echo $vt['priority']; ?>" data-category="<?php echo $vt['id_category']; ?>" data-category-name="<?php echo $vt['name_category']; ?>" data-views="<?php echo $vt['total_access']; ?>" data-date="<?php echo $vt['date']; ?>" data-code="<?php echo $vt['code']; ?>" data-lang="<?php echo $vt['language']; ?>" class="card vt-card">
                            <div class="card-img-block">
                                <div id="panorama_preview_<?php echo $vt['id']; ?>" class="panorama_preview"></div>
                                <div class="overlay"></div>
                                <i class="fas fa-play-circle"></i>
                                <?php if(empty($vt['image'])) { ?>
                                    <div style="height: 115px;background-color: darkgrey" class="card-img-top"></div>
                                <?php } else { ?>
                                    <img loading="lazy" class="card-img-top" src="<?php echo $vt['image']; ?>" alt="card image">
                                <?php } ?>
                                <div class="card-access"><i class="far fa-eye"></i> <span><?php echo $vt['total_access']; ?></span></div>
                            </div>
                            <div class="card-body pt-0">
                                <div class="vt_icon_cat">
                                    <?php
                                    $categories = explode(',', $vt['id_category']);
                                    foreach ($categories as $id_cat) {
                                        echo isset($array_cat_icons[$id_cat]) ? $array_cat_icons[$id_cat] : '';
                                    }
                                    ?>
                                </div>
                                <h5 class="card-title"><?php echo $vt['title']; ?></h5>
                                <p class="card-author"><?php echo $vt['author']; ?></p>
                                <div class="vt_langs">
                                    <?php
                                    foreach ($vt['languages_enabled'] as $vt_lang => $enabled) {
                                        if($enabled==1) {
                                            echo "<img class=\"lang_vt_list\" src=\"../viewer/css/flags_lang/$vt_lang.png?v=2\">";
                                        }
                                    }
                                    ?>
                                </div>
                                <p class="card-text"><?php echo $vt['description']; ?></p>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        </div>
    </section>
    <div class="custom_footer <?php echo (empty($footer_html)) ? 'd-none' : ''; ?>">
        <?php echo html_entity_decode($footer_html); ?>
    </div>
    <?php if($cookie_consent) : ?>
        <div data-cc="show-consentModal" id="cookie_consent_preferences"><i class="fa-solid fa-cookie-bite"></i><span>&nbsp;&nbsp;<?php echo _("Cookie Preferences"); ?></span></div>
    <?php endif; ?>
</div>
<div class="vt_viewer">
    <i class="fa fa-spin fa-circle-notch loading_icon"></i>
    <div class="frame_banner noselect">
        <?php if(!empty($logo_s)) : ?>
            <img src="../viewer/content/<?php echo $logo_s; ?>" />
        <?php endif; ?>
        <span><?php echo $name_s; ?></span>
        <i onclick="show_showcase()" class="fas fa-arrow-circle-left"></i>
    </div>
    <iframe referrerpolicy="origin" allow="accelerometer; camera; display-capture; fullscreen; geolocation; gyroscope; magnetometer; microphone; midi; xr-spatial-tracking;" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src=""></iframe>
</div>
<div class="ripple-wrap"><div class="ripple"><i class="fa fa-spin fa-circle-notch"></i></div></div>

<?php if(!empty($cookie_policy) && $cookie_policy!='<p></p>') : ?>
    <div id="modal_cookie_policy_b" class="modal" tabindex="-1" role="dialog">
        <div style="max-width: 1280px;" class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo _("Cookie Policy"); ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?php echo $cookie_policy; ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo _("Close"); ?></button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
    window.sort_type = '<?php echo $default_sort_type; ?>';
    window.sort_by = '<?php echo $default_sort_by; ?>';
    window.array_vt = [];
    window.s3_url = '<?php echo $s3_url; ?>';
    window.open_target = '<?php echo $open_target; ?>';
    window.default_language = '<?php echo $default_language; ?>';
    $(document).ready(function() {
        populate_array_vt();
        switch(window.sort_by) {
            case 'asc':
                $('#btn_sort_by_order i').removeClass('fa-sort-alpha-up').addClass('fa-sort-alpha-down');
                break;
            case 'desc':
                $('#btn_sort_by_order i').removeClass('fa-sort-alpha-down').addClass('fa-sort-alpha-up');
                break;
        }
        change_sorty_by_type(window.sort_type);
        $('#showcase_container').searchable({
            selector      : '.div_vt',
            childSelector : 'div',
            searchField   : '.search_input',
            searchType    : 'default',
            clearOnLoad   : false
        });
        $('.tooltip_l').tooltipster({
            theme: 'tooltipster-borderless',
            animation: 'grow',
            delay: 0,
            arrow: false
        });
        var height_footer = $('.custom_footer ').outerHeight();
        $('section').css('margin-bottom',height_footer+'px');
        var ripple_wrap = $('.ripple-wrap'), rippler = $('.ripple'), finish = false, vt_code='', vt_lang='', type='viewer', image_sel='',
        monitor = function(el) {
            var computed = window.getComputedStyle(el, null),
                borderwidth = parseFloat(computed.getPropertyValue('border-left-width'));
            if (!finish && borderwidth >= 1500) {
                el.style.WebkitAnimationPlayState = "paused";
                el.style.animationPlayState = "paused";
            }
            if (finish) {
                el.style.WebkitAnimationPlayState = "running";
                el.style.animationPlayState = "running";
                return;
            } else {
                window.requestAnimationFrame(function() {monitor(el)});
            }
        };
        rippler.bind("webkitAnimationEnd oAnimationEnd msAnimationEnd mozAnimationEnd animationend", function(e){
            $('.ripple i').hide();
            ripple_wrap.removeClass('goripple');
        });
        $('body').on('click', '.vt-card', function(e) {
            vt_code = $(this).attr('data-code');
            vt_lang = $(this).attr('data-lang');
            type = $(this).attr('data-type');
            if(window.open_target=='new') {
                window.open('../'+type+'/index.php?code='+vt_code+((vt_lang!='')?'&lang='+vt_lang:''),'_blank');
            } else {
                image_sel = $(this).attr('data-image');
                $('body').css('overflow-y','hidden');
                $('.vt_viewer').css('height','100vh');
                $('.ripple i').show();
                rippler.css('left', e.clientX + 'px');
                rippler.css('top', e.clientY + 'px');
                e.preventDefault();
                finish = false;
                ripple_wrap.addClass('goripple');
                setTimeout(function () {
                    swapContent();
                },1000);
                window.requestAnimationFrame(function() {monitor(rippler[0])});
            }
        });
        function swapContent() {
            $('.vt_viewer iframe').attr('src','../'+type+'/index.php?code='+vt_code+((vt_lang!='')?'&lang='+vt_lang:'')+'&ignore_embedded=1&no_pwa=1');
            switch(type) {
                case 'viewer':
                    $('.vt_viewer iframe').attr('scrolling','no');
                    break;
                case 'landing':
                    $('.vt_viewer iframe').attr('scrolling','yes');
                    break;
            }
            $('.vt_viewer').show();
            $('.showcase').hide();
            if(!image_sel.includes('preview')) {
                $('.vt_viewer').css('background-image','url('+image_sel+')');
            } else {
                $('.vt_viewer').css('background-image','none');
            }
            $('.ripple i').fadeOut(500);
            setTimeout(function() {
                finish = true;
            },500);
        }
    });

    var show_showcase = function() {
        $('.vt_viewer').fadeOut(function () {
            $('.vt_viewer iframe').attr('src','about:blank');
            $('.showcase').fadeIn();
            $('body').css('overflow-y','auto');
        });
    };
    var filter_cat = function (id) {
        $('.btn_cat').removeClass('active');
        $('.btn_cat .icon_cat_check').addClass('far fa-circle').removeClass('fas fa-circle-check');
        $('#btn_cat_'+id).addClass('active');
        $('#btn_cat_'+id+' .icon_cat_check').removeClass('far fa-circle').addClass('fas fa-circle-check');
        filter_cats();
    }

    function filter_cats() {
        var all_disabled = true;
        $('.vt-card').parent().addClass('d-none');
        $('.categories .active').each(function(i, obj) {
            all_disabled = false;
            var id = $(this).attr('id').replace('btn_cat_','');
            $('.vt-card').each(function() {
                var id_categories = $(this).attr('data-category');
                var array_categories = id_categories.split(',');
                for(var i=0;i<array_categories.length;i++) {
                    if(id=='all' || parseInt(id)==parseInt(array_categories[i])) {
                        $(this).parent().removeClass('d-none');
                    }
                }
            });
        });
        if(all_disabled) {
            $('.vt-card').parent().removeClass('d-none');
        }
    }

    function change_sorty_by_order() {
        if(window.sort_by == 'asc') {
            window.sort_by='desc';
        } else if(window.sort_by == 'desc') {
            window.sort_by='asc';
        }
        switch(window.sort_by) {
            case 'asc':
                $('#btn_sort_by_order i').removeClass('fa-sort-alpha-up').addClass('fa-sort-alpha-down');
                break;
            case 'desc':
                $('#btn_sort_by_order i').removeClass('fa-sort-alpha-down').addClass('fa-sort-alpha-up');
                break;
        }
        change_sorty_by_type(window.sort_type);
    }

    function change_sorty_by_type(type) {
        $('#btn_sort_by_type').html($('#sort_'+type).html());
        var array_vt_tmp = array_vt;
        var reverse = false;
        if(sort_by=='desc') reverse = true;
        switch(type) {
            case 'name':
                array_vt_tmp.sort(sort_by_f('name', reverse, (a) =>  a.toUpperCase()));
                break;
            case 'author':
                array_vt_tmp.sort(sort_by_f('author', reverse, (a) =>  a.toUpperCase()));
                break;
            case 'category':
                array_vt_tmp.sort(sort_by_f('category', reverse, (a) =>  a.toUpperCase()));
                break;
            case 'date':
                array_vt_tmp.sort(sort_by_f('date', reverse, parseInt));
                break;
            case 'views':
                array_vt_tmp.sort(sort_by_f('views', reverse, parseInt));
                break;
            case 'relevance':
                array_vt_tmp.sort(sort_by_f('relevance', reverse, parseInt));
                break;
        }
        window.sort_type = type;
        jQuery.each(array_vt_tmp, function (index,vt) {
            var id = vt.id;
            $('#vt_'+id).css('order',index);
        });
    }

    let array_vt = [];
    let ajaxQueue = [];
    let activeRequests = 0;
    const maxConcurrentRequests = 8;
    const delayBetweenRequests = 100;

    function populate_array_vt() {
        $('.vt-card').each(function () {
            let id = $(this).attr('data-id');
            let name = $(this).attr('data-name');
            let author = $(this).attr('data-author');
            let category = $(this).attr('data-category-name');
            let date = $(this).attr('data-date');
            let priority = $(this).attr('data-priority');
            let tmp = {
                id: id,
                name: name,
                author: author,
                category: category,
                date: date,
                views: 0,
                relevance: priority
            };
            array_vt.push(tmp);
            ajaxQueue.push(id);
        });
        processQueue();
    }

    function processQueue() {
        while (ajaxQueue.length > 0 && activeRequests < maxConcurrentRequests) {
            let id = ajaxQueue.shift();
            activeRequests++;
            (function(id) {
                setTimeout(function () {
                    get_view_vt(id, function () {
                        activeRequests--;
                        processQueue();
                    });
                }, delayBetweenRequests);
            })(id);
        }
        if (ajaxQueue.length === 0 && activeRequests === 0) {
            $('#sort_views').removeClass('disabled');
        }
    }

    function get_view_vt(id, callback) {
        $.ajax({
            url: "ajax/get_view_vt.php",
            type: "POST",
            data: {
                id_virtualtour: id
            },
            async: true,
            success: function (rsp) {
                let viewsCount = parseInt(rsp);
                let item = array_vt.find(obj => obj.id === id);
                if (item) {
                    item.views = viewsCount;
                }
                $('.vt-card[data-id="' + id + '"] .card-access span').html(rsp);
            },
            error: function (xhr, status, error) {},
            complete: function () {
                if (callback) callback();
            }
        });
    }

    var panorama_preview = null, timeout_destroy;
    function initialize_panorama_preview(id,image,s3,min_yaw,max_yaw,haov,vaov,hfov) {
        if(hfov==0) { hfov=90; } else { hfov=hfov*0.8; }
        try {
            panorama_preview.destroy();
        } catch (e) {}
        panorama_preview = pannellum.viewer('panorama_preview_'+id, {
            "type": "equirectangular",
            "autoLoad": true,
            "autoRotate": -20,
            "showControls": false,
            "compass": false,
            "minYaw": parseInt(min_yaw),
            "maxYaw": parseInt(max_yaw),
            "haov": parseInt(haov),
            "vaov": parseInt(vaov),
            "hfov": parseInt(hfov),
            "panorama": (s3==1) ? window.s3_url+"viewer/panoramas/lowres/"+image : "../viewer/panoramas/lowres/"+image
        });
        panorama_preview.on('load',function () {
            setTimeout(function () {
                $('#panorama_preview_'+id).css('opacity',1);
            },50);
        });
        $('.panorama_preview').css('opacity',0);
    }

    $('.vt-card').on('mouseenter', function () {
        var id = $(this).attr('data-id');
        var image = $(this).attr('data-panorama');
        if(image!='') {
            var s3 = parseInt($(this).attr('data-s3'));
            var min_yaw = $(this).attr('data-min_yaw');
            var max_yaw = $(this).attr('data-max_yaw');
            var haov = $(this).attr('data-haov');
            var vaov = $(this).attr('data-vaov');
            var hfov = $(this).attr('data-hfov');
            clearTimeout(timeout_destroy);
            initialize_panorama_preview(id,image,s3,min_yaw,max_yaw,haov,vaov,hfov);
        }
    });

    $('.vt-card').on('mouseleave', function () {
        $('.panorama_preview').css('opacity',0);
        timeout_destroy = setTimeout(function() {
            try {
                panorama_preview.destroy();
            } catch (e) {}
        },300);
    });

    const sort_by_f = (field, reverse, primer) => {
        const key = primer ?
            function(x) {
                return primer(x[field])
            } :
            function(x) {
                return x[field]
            };
        reverse = !reverse ? 1 : -1;
        return function(a, b) {
            return a = key(a), b = key(b), reverse * ((a > b) - (b > a));
        }
    }

    function change_language_s(lang_sel,lang) {
        if(lang_sel==lang) return;
        var current_url = location.href.replace('#','');
        if (current_url.indexOf('code=') !== -1 || current_url.indexOf('?') !== -1) {
            if (current_url.indexOf('lang=') !== -1) {
                if(window.default_language==lang) {
                    var new_url = current_url.replace(/&lang=[^&]+/,``);
                } else {
                    var new_url = current_url.replace(/lang=[^&]+/, `lang=${lang}`);
                }
            } else {
                if(window.default_language==lang) {
                    var new_url = current_url;
                } else {
                    var new_url = current_url+`&lang=${lang}`;
                }
            }
        } else {
            if (current_url.indexOf('@') !== -1) {
                if(window.default_language==lang) {
                    var new_url = current_url.replace(/@[^/]+/, ``);
                } else {
                    var new_url = current_url.replace(/@[^/]+/, `@${lang}`);
                }
            } else {
                if(window.default_language==lang) {
                    var new_url = current_url;
                } else {
                    var new_url = current_url+`@${lang}`;
                }
            }
        }
        location.href = new_url;
    }
</script>
<?php if(!empty($ga_tracking_id)) : ?>
    <?php if($cookie_consent) { ?>
        <script type="text/plain" data-category="analytics" data-service="Google Analytics" async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $ga_tracking_id; ?>"></script>
        <script type="text/plain" data-category="analytics" data-service="Google Analytics">
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?php echo $ga_tracking_id; ?>');
        </script>
    <?php } else { ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $ga_tracking_id; ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?php echo $ga_tracking_id; ?>');
        </script>
    <?php } ?>
<?php endif; ?>
<?php if($cookie_consent) : ?>
    <?php require_once('cookie_consent.php'); ?>
<?php endif; ?>
<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('service-worker.js', {
            scope: '.'
        });
    }
</script>
<?php if($pwa_enable) : ?>
    <script src="js/pwa-install.js?v=3"></script>
    <?php
    $manifest = get_manifest($code);
    if(!empty($manifest) && $no_pwa==0) { ?>
        <pwa-install id="pwa-install" use-local-storage="false" manifest-url="<?php echo $manifest; ?>"></pwa-install>
    <?php } ?>
<?php endif; ?>
<?php if(!empty($social_wechat_id) && !empty($social_wechat_secret)) :
    require_once("../viewer/vendor/jssdk/jssdk.php");
    $jssdk = new JSSDK($social_wechat_id, $social_wechat_secret);
    $signPackage = $jssdk->GetSignPackage();
    ?>
    <script src="https://res.wx.qq.com/open/js/jweixin-1.6.0.js"></script>
    <script>
        wx.config({
            debug: false,
            appId: '<?php echo $signPackage['appId']; ?>',
            timestamp: <?php echo $signPackage['timestamp']; ?>,
            nonceStr: '<?php echo $signPackage['nonceStr']; ?>',
            signature: '<?php echo $signPackage['signature']; ?>',
            jsApiList: ['onMenuShareTimeline', 'onMenuShareAppMessage']
        });
        wx.ready(function() {
            var shareData = {
                title: `<?php echo $meta_title; ?>`,
                desc: `<?php echo $meta_description; ?>`,
                link: location.href,
                imgUrl: `<?php echo (!empty($meta_image)) ? ((($s3_enabled) ? $s3_url : $url)."content/".$meta_image) : ''; ?>`
            };
            wx.onMenuShareAppMessage(shareData);
            wx.onMenuShareTimeline(shareData);
            wx.onMenuShareQQ(shareData);
            wx.onMenuShareQZone(shareData);
        });
        wx.error(function(res) {
            console.log(res.errMsg);
        });
    </script>
<?php endif; ?>
</body>
</html>

<?php
function print_favicons_showcase($code,$logo,$theme_color) {
    global $pwa_enable;
    $path = '';
    $path_m = 's_'.$code.'/';
    if (file_exists(dirname(__FILE__).'/../favicons/s_'.$code.'/favicon.ico')) {
        $path = 's_'.$code.'/';
    } else if (file_exists(dirname(__FILE__).'/../favicons/custom/favicon.ico')) {
        $path = 'custom/';
    }
    $version = preg_replace('/[^0-9]/', '', $logo);
    $manifest = "";
    if($pwa_enable) {
        if (file_exists(dirname(__FILE__).'/../favicons/s_'.$code.'/site.webmanifest')) {
            $manifest = '<link rel="manifest" href="../favicons/'.$path_m.'site.webmanifest?v='.$version.'">';
        }
    }
    $favicon16 = "";
    if (file_exists(dirname(__FILE__).'/../favicons/'.$path_m.'/favicon-16x16.png')) {
        $favicon16 = '<link rel="icon" type="image/png" sizes="16x16" href="'.$path.'favicon-16x16.png?v='.$version.'">';
    }
    $favicon32 = "";
    if (file_exists(dirname(__FILE__).'/../favicons/'.$path_m.'/favicon-32x32.png')) {
        $favicon32 = '<link rel="icon" type="image/png" sizes="32x32" href="'.$path.'favicon-32x32.png?v='.$version.'">';
    }
    $favicon96 = "";
    if (file_exists(dirname(__FILE__).'/../favicons/'.$path_m.'/favicon-96x96.png')) {
        $favicon96 = '<link rel="icon" type="image/png" sizes="96x96" href="'.$path.'favicon-96x96.png?v='.$version.'">';
    }
    return '<link rel="apple-touch-icon" sizes="180x180" href="../favicons/'.$path.'apple-touch-icon.png?v='.$version.'">
    '.$favicon16.$favicon32.$favicon96.'
    '.$manifest.'
    <link rel="mask-icon" href="../favicons/'.$path.'safari-pinned-tab.svg?v='.$version.'" color="'.$theme_color.'">
    <link rel="shortcut icon" href="../favicons/'.$path.'favicon.ico?v='.$version.'">
    <meta name="msapplication-TileColor" content="'.$theme_color.'">
    <meta name="msapplication-config" content="../favicons/'.$path.'browserconfig.xml?v='.$version.'">
    <meta name="theme-color" content="'.$theme_color.'">';
}
function get_manifest($code) {
    $version = time();
    $path_m = 's_'.$code.'/';
    if (file_exists(dirname(__FILE__).'/../favicons/s_'.$code.'/site.webmanifest')) {
        $manifest = '../favicons/'.$path_m.'site.webmanifest?v='.$version;
    } else {
        $manifest = "";
    }
    return $manifest;
}
?>
