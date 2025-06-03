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
if(empty($_POST['id_virtualtour'])) {
    die();
}
$id_virtualtour = (int)$_POST['id_virtualtour'];
$code = "";
$logo_exist = "";
$favicon_ok = 1;
switch(get_user_role($id_user)) {
    case 'customer':
        $where = " AND id_user=$id_user ";
        break;
    case 'editor':
        $where = " AND id IN () ";
        $query = "SELECT GROUP_CONCAT(id_virtualtour) as ids FROM svt_assign_virtualtours WHERE id_user=$id_user;";
        $result = $mysqli->query($query);
        if($result) {
            if($result->num_rows==1) {
                $row=$result->fetch_array(MYSQLI_ASSOC);
                $ids = $row['ids'];
                $where = " AND id IN ($ids) ";
            }
        }
        break;
}
$query = "SELECT code,logo FROM svt_virtualtours WHERE id=$id_virtualtour $where LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $code = $row['code'];
        $logo_exist = $row['logo'];
    }
}
if(empty($code)) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    die();
}
$name = strip_tags($_POST['name']);
$author = strip_tags($_POST['author']);
$id_user = (int)$_POST['id_user'];
$hfov = (int)$_POST['hfov'];
$min_hfov = (int)$_POST['min_hfov'];
$max_hfov = (int)$_POST['max_hfov'];
$hfov_mobile_ratio = (float)$_POST['hfov_mobile_ratio'];
$pan_speed = (float)$_POST['pan_speed'];
$pan_speed_mobile = (float)$_POST['pan_speed_mobile'];
$friction = (float)$_POST['friction'];
$friction_mobile = (float)$_POST['friction_mobile'];
$zoom_friction = (float)$_POST['zoom_friction'];
$zoom_friction_mobile = (float)$_POST['zoom_friction_mobile'];
$mouse_follow_feedback = (float)$_POST['mouse_follow_feedback'];
$zoom_to_pointer = (int)$_POST['zoom_to_pointer'];
$quality_viewer = (float)$_POST['quality_viewer'];
$song = strip_tags($_POST['song']);
$flyin = (int)$_POST['flyin'];
$flyin_duration = (int)$_POST['flyin_duration'];
if(empty($flyin_duration)) $flyin_duration=2000;
$logo = strip_tags($_POST['logo']);
$link_logo = strip_tags($_POST['link_logo']);
$background_image = strip_tags($_POST['background_image']);
$background_video = strip_tags($_POST['background_video']);
$background_image_mobile = strip_tags($_POST['background_image_mobile']);
$background_video_mobile = strip_tags($_POST['background_video_mobile']);
$background_video_delay = $_POST['background_video_delay'];
if(empty($background_video_delay)) $background_video_delay=0;
$background_video_delay = (int)$background_video_delay;
$background_video_delay_mobile = $_POST['background_video_delay_mobile'];
if(empty($background_video_delay_mobile)) $background_video_delay_mobile=0;
$background_video_delay_mobile = (int)$background_video_delay_mobile;
$background_video_skip = (int)$_POST['background_video_skip'];
$background_video_skip_mobile = (int)$_POST['background_video_skip_mobile'];
$nadir_logo = strip_tags($_POST['nadir_logo']);
$intro_desktop = strip_tags($_POST['intro_desktop']);
$intro_mobile = strip_tags($_POST['intro_mobile']);
$intro_desktop_hide = $_POST['intro_desktop_hide'];
$intro_mobile_hide = $_POST['intro_mobile_hide'];
if(empty($intro_desktop_hide) || $intro_desktop_hide<0) $intro_desktop_hide=0;
if(empty($intro_mobile_hide) || $intro_mobile_hide<0) $intro_mobile_hide=0;
$intro_desktop_hide = (int)$intro_desktop_hide;
$intro_mobile_hide = (int)$intro_mobile_hide;
$auto_start = (int)$_POST['auto_start'];
$hide_loading = (int)$_POST['hide_loading'];
$show_background = (int)$_POST['show_background'];
$sameAzimuth = (int)$_POST['sameAzimuth'];
$description = strip_tags($_POST['description']);
$ga_tracking_id = strip_tags($_POST['ga_tracking_id']);
$compress_jpg = $_POST['compress_jpg'];
if($compress_jpg=="") $compress_jpg=90;
$compress_jpg = (int)$compress_jpg;
$max_width_compress = $_POST['max_width_compress'];
if($max_width_compress=="") $max_width_compress=0;
$max_width_compress = (int)$max_width_compress;
$enable_multires = (int)$_POST['enable_multires'];
$preload_panoramas = (int)$_POST['preload_panoramas'];
$mobile_panoramas = (int)$_POST['mobile_panoramas'];
$keep_original_panorama = (int)$_POST['keep_original_panorama'];
$transition_time = $_POST['transition_time'];
if($transition_time=='') $transition_time = 300;
$transition_time = (int)$transition_time;
$transition_fadeout = $_POST['transition_fadeout'];
if($transition_fadeout=='') $transition_fadeout = 200;
$transition_fadeout = (int)$transition_fadeout;
$transition_zoom = (int)$_POST['transition_zoom'];
$transition_loading = (int)$_POST['transition_loading'];
$transition_effect = strip_tags($_POST['transition_effect']);
$transition_hfov = (int)$_POST['transition_hfov'];
$transition_hfov_time = (int)$_POST['transition_hfov_time'];
$markers_default_lookat = (int)$_POST['markers_default_lookat'];
$markers_default_backlink = (int)$_POST['markers_default_backlink'];
$note = strip_tags($_POST['note']);
$language = strip_tags($_POST['language']);
$languages_enabled = $_POST['languages_enabled'];
$external_url = strip_tags($_POST['external_url']);
$id_categories = $_POST['id_categories'];
$keyboard_mode = (int)$_POST['keyboard_mode'];
$shop_type = strip_tags($_POST['shop_type']);
$woocommerce_store_url = rtrim(strip_tags($_POST['woocommerce_store_url']),'/');
$woocommerce_store_cart = strip_tags($_POST['woocommerce_store_cart']);
$woocommerce_store_checkout = strip_tags($_POST['woocommerce_store_checkout']);
$woocommerce_show_stock_quantity = (int)$_POST['woocommerce_show_stock_quantity'];
$woocommerce_modal = (int)$_POST['woocommerce_modal'];
$woocommerce_customer_key = strip_tags($_POST['woocommerce_customer_key']);
$woocommerce_customer_secret = strip_tags($_POST['woocommerce_customer_secret']);
$snipcart_api_key = strip_tags($_POST['snipcart_api_key']);
$snipcart_currency = strip_tags($_POST['snipcart_currency']);
$ar_camera_align = (int)$_POST['ar_camera_align'];
$click_anywhere = (int)$_POST['click_anywhere'];
$hide_markers = (int)$_POST['hide_markers'];
$hover_markers = (int)$_POST['hover_markers'];
$initial_feedback = $_POST['initial_feedback'];
if(empty($initial_feedback)) $initial_feedback=0;
$initial_feedback = (int)$initial_feedback;
$loading_background_color = strip_tags($_POST['loading_background_color']);
$loading_text_color = strip_tags($_POST['loading_text_color']);
$custom_html = htmlspecialchars_decode($_POST['custom_html']);
$context_info = $_POST['context_info'];
$mouse_zoom = (int)$_POST['mouse_zoom'];
if($context_info=='<p><br></p>') $context_info="";
$xist_background = get_virtual_tour($id_virtualtour,$id_user)['background_image'];
$form_content = $_POST['form_content'];
$array_lang = json_decode($_POST['array_lang'],true);
$poweredby_type = strip_tags($_POST['poweredby_type']);
$poweredby_link = strip_tags($_POST['poweredby_link']);
$poweredby_image = strip_tags($_POST['poweredby_image']);
$poweredby_text = strip_tags($_POST['poweredby_text'],'<br>');
$song_bg_volume = (float)$_POST['song_bg_volume'];
$cookie_consent = (int)$_POST['cookie_consent'];
$avatar_video = strip_tags($_POST['avatar_video']);
$add_room_sort = strip_tags($_POST['add_room_sort']);
$avatar_video_autoplay = (int)$_POST['avatar_video_autoplay'];
$avatar_video_pause = (int)$_POST['avatar_video_pause'];
$avatar_video_hide_end = (int)$_POST['avatar_video_hide_end'];
$intro_slider_delay = $_POST['intro_slider_delay'];
if(empty($intro_slider_delay)) $intro_slider_delay=6;
$intro_slider_delay = (int)$intro_slider_delay;
$array_versions = json_decode($_POST['array_versions'],true);
$leave_poi_open = (int)$_POST['leave_poi_open'];
$close_poi_click_outside = (int)$_POST['close_poi_click_outside'];
$pwa_enable = (int)$_POST['pwa_enable'];
$vr_icons_size = strip_tags($_POST['vr_icons_size']);
$nadir_round = (int)$_POST['nadir_round'];
$learning_mode = (int)$_POST['learning_mode'];
$learning_unlock_marker = (int)$_POST['learning_unlock_marker'];
$learning_poi_progressive  = (int)$_POST['learning_poi_progressive'];
$learning_show_modal = (int)$_POST['learning_show_modal'];
$learning_modal_icon = strip_tags($_POST['learning_modal_icon']);
$learning_modal_title = strip_tags($_POST['learning_modal_title']);
$learning_modal_subtitle = strip_tags($_POST['learning_modal_subtitle']);
$learning_modal_description = $_POST['learning_modal_description'];
$learning_show_email = (int)$_POST['learning_show_email'];
$learning_mandatory_email = (int)$_POST['learning_mandatory_email'];
$learning_restore_session = (int)$_POST['learning_restore_session'];
$learning_modal_color = strip_tags($_POST['learning_modal_color']);
$learning_modal_background = strip_tags($_POST['learning_modal_background']);
$learning_modal_color_text = strip_tags($_POST['learning_modal_color_text']);
$learning_modal_button_background = strip_tags($_POST['learning_modal_button_background']);
$learning_modal_button_color = strip_tags($_POST['learning_modal_button_color']);
$learning_summary_style = strip_tags($_POST['learning_summary_style']);
$learning_summary_title = strip_tags($_POST['learning_summary_title']);
$learning_summary_background = strip_tags($_POST['learning_summary_background']);
$learning_summary_color = strip_tags($_POST['learning_summary_color']);
$learning_summary_partial_title = strip_tags($_POST['learning_summary_partial_title']);
$learning_summary_partial_color = strip_tags($_POST['learning_summary_partial_color']);
$learning_summary_global_title = strip_tags($_POST['learning_summary_global_title']);
$learning_summary_global_color = strip_tags($_POST['learning_summary_global_color']);
$learning_check_icon = strip_tags($_POST['learning_check_icon']);
$learning_check_background = strip_tags($_POST['learning_check_background']);
$learning_check_color = strip_tags($_POST['learning_check_color']);
$learning_modal_button = strip_tags($_POST['learning_modal_button']);
$learning_placeholder_email = strip_tags($_POST['learning_placeholder_email']);
$query = "UPDATE svt_virtualtours SET name=?,id_user=?,author=?,hfov=?,min_hfov=?,max_hfov=?,hfov_mobile_ratio=?,pan_speed=?,pan_speed_mobile=?,friction=?,friction_mobile=?,zoom_friction=?,zoom_friction_mobile=?,zoom_to_pointer=?,song=?,logo=?,background_image=?,background_video=?,background_video_delay=?,nadir_logo=?,auto_start=?,hide_loading=?,sameAzimuth=?,description=?,ga_tracking_id=?,compress_jpg=?,link_logo=?,max_width_compress=?,quality_viewer=?,intro_desktop=?,intro_mobile=?,intro_desktop_hide=?,intro_mobile_hide=?,enable_multires=?,transition_time=?,transition_zoom=?,transition_loading=?,transition_effect=?,transition_fadeout=?,markers_default_lookat=?,markers_default_backlink=?,note=?,flyin=?,language=?,external_url=?,keyboard_mode=?,preload_panoramas=?,mobile_panoramas=?,keep_original_panorama=?,click_anywhere=?,hide_markers=?,hover_markers=?,snipcart_currency=?,custom_html=?,context_info=?,ar_camera_align=?,initial_feedback=?,mouse_follow_feedback=?,background_video_delay_mobile=?,background_image_mobile=?,background_video_mobile=?,loading_background_color=?,loading_text_color=?,mouse_zoom=?,flyin_duration=?,shop_type=?,woocommerce_store_url=?,woocommerce_store_cart=?,woocommerce_store_checkout=?,form_content=?,poweredby_type=?,poweredby_image=?,poweredby_text=?,poweredby_link=?,background_video_skip=?,background_video_skip_mobile=?,song_bg_volume=?,cookie_consent=?,transition_hfov=?,transition_hfov_time=?,avatar_video=?,add_room_sort=?,avatar_video_autoplay=?,avatar_video_hide_end=?,intro_slider_delay=?,woocommerce_show_stock_quantity=?,languages_enabled=?,show_background=?,avatar_video_pause=?,woocommerce_modal=?,leave_poi_open=?,close_poi_click_outside=?,pwa_enable=?,vr_icons_size=?,learning_mode=?,learning_unlock_marker=?,learning_poi_progressive=?,learning_show_modal=?,learning_modal_icon=?,learning_modal_title=?,learning_modal_subtitle=?,learning_modal_description=?,learning_show_email=?,learning_restore_session=?,learning_modal_color=?,nadir_round=?,learning_mandatory_email=?,learning_summary_style=?,learning_summary_title=?,learning_summary_background=?,learning_summary_color=?,learning_summary_partial_title=?,learning_summary_partial_color=?,learning_summary_global_title=?,learning_summary_global_color=?,learning_check_icon=?,learning_check_background=?,learning_check_color=?,learning_modal_button=?,learning_placeholder_email=?,learning_modal_background=?,learning_modal_color_text=?,learning_modal_button_background=?,learning_modal_button_color=? WHERE id=?;";
if($smt = $mysqli->prepare($query)) {
    $smt->bind_param('sisiiidddddddissssisiiissisidssiiiiiisiiisissiiiiiiisssiidissssiisssssssssiidiiissiiiisiiiiiisiiiissssiisiisssssssssssssssssi',$name,$id_user,$author,$hfov,$min_hfov,$max_hfov,$hfov_mobile_ratio,$pan_speed,$pan_speed_mobile,$friction,$friction_mobile,$zoom_friction,$zoom_friction_mobile,$zoom_to_pointer,$song,$logo,$background_image,$background_video,$background_video_delay,$nadir_logo,$auto_start,$hide_loading,$sameAzimuth,$description,$ga_tracking_id,$compress_jpg,$link_logo,$max_width_compress,$quality_viewer,$intro_desktop,$intro_mobile,$intro_desktop_hide,$intro_mobile_hide,$enable_multires,$transition_time,$transition_zoom,$transition_loading,$transition_effect,$transition_fadeout,$markers_default_lookat,$markers_default_backlink,$note,$flyin,$language,$external_url,$keyboard_mode,$preload_panoramas,$mobile_panoramas,$keep_original_panorama,$click_anywhere,$hide_markers,$hover_markers,$snipcart_currency,$custom_html,$context_info,$ar_camera_align,$initial_feedback,$mouse_follow_feedback,$background_video_delay_mobile,$background_image_mobile,$background_video_mobile,$loading_background_color,$loading_text_color,$mouse_zoom,$flyin_duration,$shop_type,$woocommerce_store_url,$woocommerce_store_cart,$woocommerce_store_checkout,$form_content,$poweredby_type,$poweredby_image,$poweredby_text,$poweredby_link,$background_video_skip,$background_video_skip_mobile,$song_bg_volume,$cookie_consent,$transition_hfov,$transition_hfov_time,$avatar_video,$add_room_sort,$avatar_video_autoplay,$avatar_video_hide_end,$intro_slider_delay,$woocommerce_show_stock_quantity,$languages_enabled,$show_background,$avatar_video_pause,$woocommerce_modal,$leave_poi_open,$close_poi_click_outside,$pwa_enable,$vr_icons_size,$learning_mode,$learning_unlock_marker,$learning_poi_progressive,$learning_show_modal,$learning_modal_icon,$learning_modal_title,$learning_modal_subtitle,$learning_modal_description,$learning_show_email,$learning_restore_session,$learning_modal_color,$nadir_round,$learning_mandatory_email,$learning_summary_style,$learning_summary_title,$learning_summary_background,$learning_summary_color,$learning_summary_partial_title,$learning_summary_partial_color,$learning_summary_global_title,$learning_summary_global_color,$learning_check_icon,$learning_check_background,$learning_check_color,$learning_modal_button,$learning_placeholder_email,$learning_modal_background,$learning_modal_color_text,$learning_modal_button_background,$learning_modal_button_color,$id_virtualtour);
    $result = $smt->execute();
    if ($result) {
        $mysqli->query("DELETE FROM svt_category_vt_assoc WHERE id_virtualtour=$id_virtualtour;");
        foreach ($id_categories as $id_category) {
            $id_category = (int)$id_category;
            $mysqli->query("INSERT INTO svt_category_vt_assoc(id_virtualtour,id_category) VALUES($id_virtualtour,$id_category);");
        }
        if($snipcart_api_key!="keep_snipcart_public_key") {
            $query = "UPDATE svt_virtualtours SET snipcart_api_key=? WHERE id=?;";
            if($smt = $mysqli->prepare($query)) {
                $smt->bind_param('si',$snipcart_api_key,$id_virtualtour);
                $smt->execute();
            }
        }
        if($woocommerce_customer_key!="keep_woocommerce_customer_key") {
            $query = "UPDATE svt_virtualtours SET woocommerce_customer_key=? WHERE id=?;";
            if($smt = $mysqli->prepare($query)) {
                $smt->bind_param('si',$woocommerce_customer_key,$id_virtualtour);
                $smt->execute();
            }
        }
        if($woocommerce_customer_secret!="keep_woocommerce_customer_secret") {
            $query = "UPDATE svt_virtualtours SET woocommerce_customer_secret=? WHERE id=?;";
            if($smt = $mysqli->prepare($query)) {
                $smt->bind_param('si',$woocommerce_customer_secret,$id_virtualtour);
                $smt->execute();
            }
        }
        $query = "UPDATE svt_rooms SET transition_time=?,transition_zoom=?,transition_fadeout=? WHERE id_virtualtour=? AND transition_override=0;";
        if($smt = $mysqli->prepare($query)) {
            $smt->bind_param('iiii',$transition_time,$transition_zoom,$transition_fadeout,$id_virtualtour);
            $smt->execute();
        }
        save_input_langs($array_lang,'svt_virtualtours_lang','id_virtualtour',$id_virtualtour);
        foreach ($array_versions as $id_version => $version) {
            $auto_start_v = (int)$version['auto_start'];
            $hide_loading_v = (int)$version['hide_loading'];
            $show_background_v = (int)$version['show_background'];
            $flyin_v = (int)$version['flyin'];
            $flyin_duration_v = (int)$version['flyin_duration'];
            $loading_background_color_v = strip_tags($version['loading_background_color']);
            $loading_text_color_v = strip_tags($version['loading_text_color']);
            $query_v = "UPDATE svt_virtualtours_versions SET auto_start=?,hide_loading=?,show_background=?,flyin=?,flyin_duration=?,loading_background_color=?,loading_text_color=? WHERE id=? AND id_virtualtour=?;";
            if($smt_v = $mysqli->prepare($query_v)) {
                $smt_v->bind_param('iiiiissii',$auto_start_v,$hide_loading_v,$show_background_v,$flyin_v,$flyin_duration_v,$loading_background_color_v,$loading_text_color_v,$id_version,$id_virtualtour);
                $smt_v->execute();
            }
        }
        $path = __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR;
        if($logo!=$logo_exist) {
            if(file_exists($path . "favicons" . DIRECTORY_SEPARATOR . "v_$code")) {
                array_map('unlink', glob($path . "favicons" . DIRECTORY_SEPARATOR . "v_$code" . DIRECTORY_SEPARATOR ."*.*"));
                rmdir($path . "favicons" . DIRECTORY_SEPARATOR . "v_$code" . DIRECTORY_SEPARATOR);
            }
        }
        $favicon_ok = generate_favicons('vt',$id_virtualtour);
        $s3_params = check_s3_tour_enabled($id_virtualtour);
        $s3_enabled = false;
        if(!empty($s3_params)) {
            $s3_bucket_name = $s3_params['bucket'];
            $s3_url = init_s3_client($s3_params);
            if($s3_url!==false) {
                $s3_enabled = true;
            }
        }
        generate_multires(false,$id_virtualtour);
        update_user_space_storage($id_user,false);
        if(!empty($background_image) && $xist_background!=$background_image) {
            $content_image_gt = $background_image;
            require_once("../../services/generate_thumb.php");
        }
        ob_end_clean();
        echo json_encode(array("status"=>"ok","favicon"=>$favicon_ok));
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error"));
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
}