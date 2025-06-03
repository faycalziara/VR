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
$id_virtualtour = (int)$_POST['id_virtualtour'];
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
$code = "";
$query = "SELECT code FROM svt_virtualtours WHERE id=$id_virtualtour $where LIMIT 1;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows==1) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        $code = $row['code'];
    }
}
if(empty($code)) {
    ob_end_clean();
    echo json_encode(array("status"=>"error"));
    die();
}
$arrows_nav = (int)$_POST['arrows_nav'];
$voice_commands = (int)$_POST['voice_commands'];
$compass = (int)$_POST['compass'];
$auto_show_slider = (int)$_POST['auto_show_slider'];
$nav_slider = (int)$_POST['nav_slider'];
$nav_slider_mode = (int)$_POST['nav_slider_mode'];
$show_list_alt = (int)$_POST['show_list_alt'];
$show_info = (int)$_POST['show_info'];
$info_box_type = strip_tags($_POST['info_box_type']);
$show_dollhouse = (int)$_POST['show_dollhouse'];
$show_custom = (int)$_POST['show_custom'];
$show_custom2 = (int)$_POST['show_custom2'];
$show_custom3 = (int)$_POST['show_custom3'];
$show_custom4 = (int)$_POST['show_custom4'];
$show_custom5 = (int)$_POST['show_custom5'];
$show_location = (int)$_POST['show_location'];
$show_media = (int)$_POST['show_media'];
$show_snapshot = (int)$_POST['show_snapshot'];
$show_gallery = (int)$_POST['show_gallery'];
$show_icons_toggle = (int)$_POST['show_icons_toggle'];
$show_measures_toggle = (int)$_POST['show_measures_toggle'];
$show_autorotation_toggle = (int)$_POST['show_autorotation_toggle'];
$show_nav_control = (int)$_POST['show_nav_control'];
$show_presentation = (int)$_POST['show_presentation'];
$show_main_form = (int)$_POST['show_main_form'];
$show_share = (int)$_POST['show_share'];
$share_providers = strip_tags($_POST['share_providers']);
$show_device_orientation = (int)$_POST['show_device_orientation'];
$drag_device_orientation = (int)$_POST['drag_device_orientation'];
$show_webvr = (int)$_POST['show_webvr'];
$webvr_new_window = (int)$_POST['webvr_new_window'];
$show_audio = (int)$_POST['show_audio'];
$show_vt_title = (int)$_POST['show_vt_title'];
$show_fullscreen = (int)$_POST['show_fullscreen'];
$show_map = (int)$_POST['show_map'];
$show_map_tour = (int)$_POST['show_map_tour'];
$show_language = (int)$_POST['show_language'];
$live_session = (int)$_POST['live_session'];
$meeting = (int)$_POST['meeting'];
$show_annotations = (int)$_POST['show_annotations'];
$autoclose_menu = (int)$_POST['autoclose_menu'];
$autoclose_list_alt = (int)$_POST['autoclose_list_alt'];
$autoclose_slider = (int)$_POST['autoclose_slider'];
$grouped_list_alt = (int)$_POST['grouped_list_alt'];
$autoclose_map = (int)$_POST['autoclose_map'];
$fb_messenger = (int)$_POST['fb_messenger'];
$whatsapp_chat = (int)$_POST['whatsapp_chat'];
$show_logo = (int)$_POST['show_logo'];
$logo_opacity = (float)$_POST['logo_opacity'];
$logo_opacity_hover = (float)$_POST['logo_opacity_hover'];
$poweredby_opacity = (float)$_POST['poweredby_opacity'];
$poweredby_opacity_hover = (float)$_POST['poweredby_opacity_hover'];
$array_colors = $_POST['array_colors'];
$array_positions = $_POST['array_positions'];
$array_orders = $_POST['array_orders'];
$array_icons = $_POST['array_icons'];
$array_library_icons = $_POST['array_library_icons'];
$annotation_position = strip_tags($_POST['annotation_position']);
$map_position = strip_tags($_POST['map_position']);
$logo_position = strip_tags($_POST['logo_position']);
$logo_height = $_POST['logo_height'];
if(empty($logo_height)) $logo_height=40;
$logo_height = (int)$logo_height;
$logo_padding_left = $_POST['logo_padding_left'];
if(empty($logo_padding_left)) $logo_padding_left=0;
$logo_padding_left = (int)$logo_padding_left;
$logo_padding_top = $_POST['logo_padding_top'];
if(empty($logo_padding_top)) $logo_padding_top=0;
$logo_padding_top = (int)$logo_padding_top;
$logo_padding_right = $_POST['logo_padding_right'];
if(empty($logo_padding_right)) $logo_padding_right=0;
$logo_padding_right = (int)$logo_padding_right;
$form_enable = (int)$_POST['form_enable'];
$custom_title = strip_tags($_POST['custom_title']);
$custom_content = $_POST['custom_content'];
$custom_content = htmlspecialchars_decode($custom_content);
$custom2_title = strip_tags($_POST['custom2_title']);
$custom2_content = $_POST['custom2_content'];
$custom2_content = htmlspecialchars_decode($custom2_content);
$custom3_title = strip_tags($_POST['custom3_title']);
$custom3_content = $_POST['custom3_content'];
$custom3_content = htmlspecialchars_decode($custom3_content);
$custom4_title = strip_tags($_POST['custom4_title']);
$custom4_content = $_POST['custom4_content'];
$custom4_content = htmlspecialchars_decode($custom4_content);
$custom5_title = strip_tags($_POST['custom5_title']);
$custom5_content = $_POST['custom5_content'];
$custom5_content = htmlspecialchars_decode($custom5_content);
$location_title = strip_tags($_POST['location_title']);
$location_content = $_POST['location_content'];
$media_title = strip_tags($_POST['media_title']);
$markers_icon = strip_tags($_POST['markers_icon']);
$markers_icon_type = strip_tags($_POST['markers_icon_type']);
$markers_id_icon_library = (int)$_POST['markers_id_icon_library'];
$markers_color = strip_tags($_POST['markers_color']);
$markers_background = strip_tags($_POST['markers_background']);
$markers_show_room = (int)$_POST['markers_show_room'];
if($markers_show_room!=4) $markers_id_icon_library=0;
$markers_tooltip_type = strip_tags($_POST['markers_tooltip_type']);
$markers_tooltip_visibility = strip_tags($_POST['markers_tooltip_visibility']);
$markers_tooltip_background = strip_tags($_POST['markers_tooltip_background']);
$markers_tooltip_color = strip_tags($_POST['markers_tooltip_color']);
$markers_default_scale = (int)$_POST['markers_default_scale'];
$markers_default_sticky = (int)$_POST['markers_default_sticky'];
$markers_default_rotateX = (int)$_POST['markers_default_rotateX'];
$markers_default_rotateZ = (int)$_POST['markers_default_rotateZ'];
$markers_default_size_scale = (float)$_POST['markers_default_size_scale'];
$markers_default_sound = strip_tags($_POST['markers_default_sound']);
$markers_animation = strip_tags($_POST['markers_animation']);
$pois_icon = strip_tags($_POST['pois_icon']);
$pois_icon_type = strip_tags($_POST['pois_icon_type']);
$pois_id_icon_library = (int)$_POST['pois_id_icon_library'];
$pois_color = strip_tags($_POST['pois_color']);
$pois_background = strip_tags($_POST['pois_background']);
$pois_style = strip_tags($_POST['pois_style']);
if($pois_style!=1) $pois_id_icon_library=0;
$pois_tooltip_type = strip_tags($_POST['pois_tooltip_type']);
$pois_tooltip_visibility = strip_tags($_POST['pois_tooltip_visibility']);
$pois_tooltip_background = strip_tags($_POST['pois_tooltip_background']);
$pois_tooltip_color = strip_tags($_POST['pois_tooltip_color']);
$pois_default_scale = (int)$_POST['pois_default_scale'];
$pois_default_sticky = (int)$_POST['pois_default_sticky'];
$pois_default_rotateX = (int)$_POST['pois_default_rotateX'];
$pois_default_rotateZ = (int)$_POST['pois_default_rotateZ'];
$pois_default_size_scale = (float)$_POST['pois_default_size_scale'];
$pois_default_sound = strip_tags($_POST['pois_default_sound']);
$pois_animation = strip_tags($_POST['pois_animation']);
$position_list = $array_positions['position_list'];
$preview_room_slider = (int)$_POST['preview_room_slider'];
$vt_title_height = (int)$_POST['vt_title_height'];
if($position_list!='default') {
    $tmp = explode("_",$position_list);
    $type_list = $tmp[0];
    $position_list = $tmp[1];
} else {
    $type_list = "default";
    $position_list = "";
}
$position_arrows = $array_positions['position_arrows'];
if($position_arrows!='default') {
    $tmp = explode("_",$position_arrows);
    $type_arrows = $tmp[0];
    $position_arrows = $tmp[1];
} else {
    $type_arrows = "default";
    $position_arrows = "";
}
foreach($array_orders as $key => $value) {
    $array_orders[str_replace(['_left','_center','_right','_menu'], '', $key)] = $value;
    unset($array_orders[$key]);
}
if(!isset($array_orders['controls_arrow'])) $array_orders['controls_arrow']=0;
if(!isset($array_colors['title_background'])) $array_colors['title_background']='';
$font_viewer = strip_tags($_POST['font_viewer']);
$id_preset = $_POST['id_preset'];
$name_preset = strip_tags($_POST['name_preset']);
$preset_public = (int)$_POST['preset_public'];
$apply_preset = (int)$_POST['apply_preset'];
$nadir_size = strip_tags($_POST['nadir_size']);
$icons_tooltips = (int)$_POST['icons_tooltips'];
$autorotate_speed = $_POST['autorotate_speed'];
if($autorotate_speed=="") $autorotate_speed=0;
if($autorotate_speed>=10) $autorotate_speed=10;
if($autorotate_speed<=-10) $autorotate_speed=-10;
$autorotate_speed = (int)$autorotate_speed;
$autorotate_inactivity = $_POST['autorotate_inactivity'];
if($autorotate_inactivity=="") $autorotate_inactivity=0;
$autorotate_inactivity = (int)$autorotate_inactivity;
$song_autoplay = (int)$_POST['song_autoplay'];
$fb_page_id = strip_tags($_POST['fb_page_id']);
$whatsapp_number = strip_tags($_POST['whatsapp_number']);
$password_meeting = strip_tags($_POST['password_meeting']);
$password_livesession = strip_tags($_POST['password_livesession']);
$show_comments = (int)$_POST['show_comments'];
$disqus_shortname = strip_tags($_POST['disqus_shortname']);
$disqus_public_key = strip_tags($_POST['disqus_public_key']);
$language = strip_tags($_POST['language']);
$languages_enabled = $_POST['languages_enabled'];
$show_poweredby = (int)$_POST['show_poweredby'];
$poweredby_height = (int)$_POST['poweredby_height'];
$poweredby_font_size = (int)$_POST['poweredby_font_size'];
$poweredby_font_color = strip_tags($_POST['poweredby_font_color']);
$poweredby_position = strip_tags($_POST['poweredby_position']);
$media_file = strip_tags($_POST['media_file']);
$show_avatar_video = $_POST['show_avatar_video'];
$avatar_video_position = $_POST['avatar_video_position'];
$avatar_video_height = $_POST['avatar_video_height'];
if(empty($avatar_video_height)) $avatar_video_height=300;
$avatar_video_height = (int)$avatar_video_height;
$avatar_video_width = $_POST['avatar_video_width'];
if(empty($avatar_video_width)) $avatar_video_height=170;
$avatar_video_width = (int)$avatar_video_width;
$avatar_video_padding_left = $_POST['avatar_video_padding_left'];
if(empty($avatar_video_padding_left)) $avatar_video_padding_left=0;
$avatar_video_padding_left = (int)$avatar_video_padding_left;
$avatar_video_padding_bottom = $_POST['avatar_video_padding_bottom'];
if(empty($avatar_video_padding_bottom)) $avatar_video_padding_bottom=0;
$avatar_video_padding_bottom = (int)$avatar_video_padding_bottom;
$avatar_video_padding_right = $_POST['avatar_video_padding_right'];
if(empty($avatar_video_padding_right)) $avatar_video_padding_right=0;
$avatar_video_padding_right = (int)$avatar_video_padding_right;
$multiple_room_views_size = $_POST['multiple_room_views_size'];
if(empty($multiple_room_views_size)) $multiple_room_views_size=30;
$multiple_room_views_size = (int)$multiple_room_views_size;
$multiple_room_views_border = $_POST['multiple_room_views_border'];
if(empty($multiple_room_views_border)) $multiple_room_views_border=30;
$multiple_room_views_border = (int)$multiple_room_views_border;
$multiple_room_views_style = strip_tags($_POST['multiple_room_views_style']);
$enable_visitor_rt = (int)$_POST['enable_visitor_rt'];
$enable_views_stat = (int)$_POST['enable_views_stat'];
$enable_rooms_multiple = (int)$_POST['enable_rooms_multiple'];
$interval_visitor_rt = $_POST['interval_visitor_rt'];
if($interval_visitor_rt=="") $interval_visitor_rt=1000;
if($interval_visitor_rt<0) $interval_visitor_rt=0;
$interval_visitor_rt = (int)$interval_visitor_rt;
$buttons_style = strip_tags($_POST['buttons_style']);
$buttons_size = strip_tags($_POST['buttons_size']);
$id_version_sel = (int)$_POST['id_version_sel'];
$id_version = (int)$_POST['id_version'];
$name_version = strip_tags($_POST['name_version']);
$name_version = str_replace("_"," ",$name_version);
$array_lang = json_decode($_POST['array_lang'],true);
save_input_langs($array_lang,'svt_virtualtours_lang','id_virtualtour',$id_virtualtour);
$ui_style = [
    'buttons_style'=>$buttons_style,
    'buttons_size'=>$buttons_size,
    'icons_tooltips'=>$icons_tooltips,
    'preview_room_slider'=>$preview_room_slider,
    'items'=>[
        'list'=>[
            'background_initial'=>'',
            'background'=>$array_colors['slider_background']
        ],
        'annotation'=>[
            'position'=>$annotation_position,
            'color'=>$array_colors['annotation_color'],
            'background'=>$array_colors['annotation_background']
        ],
        'title'=>[
            'color'=>$array_colors['title_color'],
            'background'=>rtrim(str_replace("rgb","rgba",$array_colors['title_background']), ")"),
            'background_height'=>$vt_title_height
        ],
        'multiple_room_views'=>[
            'size'=>$multiple_room_views_size,
            'style'=>$multiple_room_views_style,
            'border'=>$multiple_room_views_border,
            'color'=>$array_colors['multiple_room_views_border_color']
        ],
        'visitors_rt_stats'=>[
            'background'=>$array_colors['visitors_rt_stats_background'],
            'color'=>$array_colors['visitors_rt_stats_color']
        ],
        'comments'=>[
            'color'=>$array_colors['comments_color']
        ],
        'nav_control'=>[
            'color'=>$array_colors['nav_control_color'],
            'color_hover'=>$array_colors['nav_control_color_hover'],
            'background'=>$array_colors['nav_background']
        ],
        'logo'=>[
            'position'=>$logo_position,
            'height'=>$logo_height,
            'padding_top'=>$logo_padding_top,
            'padding_left'=>$logo_padding_left,
            'padding_right'=>$logo_padding_right,
            'opacity'=>$logo_opacity,
            'opacity_hover'=>$logo_opacity_hover,
        ],
        'map'=>[
            'position'=>$map_position,
            'color'=>$array_colors['map_bar_color'],
            'color_hover'=>$array_colors['map_bar_color_hover'],
            'background'=>$array_colors['map_bar_background']
        ],
        'poweredby'=>[
            'position'=>$poweredby_position,
            'image_height'=>$poweredby_height,
            'font_size'=>$poweredby_font_size,
            'font_color'=>$poweredby_font_color,
            'opacity'=>$poweredby_opacity,
            'opacity_hover'=>$poweredby_opacity_hover,
        ],
        'avatar_video'=>[
            'position'=>$avatar_video_position,
            'width'=>$avatar_video_width,
            'height'=>$avatar_video_height,
            'padding_bottom'=>$avatar_video_padding_bottom,
            'padding_left'=>$avatar_video_padding_left,
            'padding_right'=>$avatar_video_padding_right
        ],
    ],
    'icons'=>[
        'menu'=>[
            'color'=>$array_colors['menu_color'],
            'color_hover'=>$array_colors['menu_color_hover']
        ],
        'list_alt'=>[
            'color'=>$array_colors['list_alt_color'],
            'color_hover'=>$array_colors['list_alt_color_hover']
        ],
        'audio'=>[
            'color'=>$array_colors['audio_color'],
            'color_hover'=>$array_colors['audio_color_hover']
        ],
        'floorplan'=>[
            'color'=>$array_colors['floorplan_color'],
            'color_hover'=>$array_colors['floorplan_color_hover']
        ],
        'map'=>[
            'color'=>$array_colors['map_color'],
            'color_hover'=>$array_colors['map_color_hover']
        ],
        'fullscreen'=>[
            'color'=>$array_colors['fullscreen_color'],
            'color_hover'=>$array_colors['fullscreen_color_hover']
        ]
    ],
    'controls'=>[
        'fullscreen_alt'=>[
            'type'=>explode("_",$array_positions['position_fullscreen_alt'])[0],
            'position'=>explode("_",$array_positions['position_fullscreen_alt'])[1],
            'order'=>$array_orders['fullscreen_alt_control'],
            'style'=>'background-color:'.$array_colors['fullscreen_alt_background'].';color:'.$array_colors['fullscreen_alt_color'].';',
            'style_hover'=>'background-color:'.$array_colors['fullscreen_alt_background_hover'].';color:'.$array_colors['fullscreen_alt_color_hover'].';',
        ],
        'list_alt_menu'=>[
            'style'=>'background-color:'.$array_colors['list_alt_menu_background'].';color:'.$array_colors['list_alt_menu_color'].';',
            'style_hover'=>'background-color:'.$array_colors['list_alt_menu_background_hover'].';color:'.$array_colors['list_alt_menu_color_hover'].';',
            'icon_color'=>$array_colors['list_alt_menu_icon_color'],
            'icon_color_hover'=>$array_colors['list_alt_menu_icon_color_hover']
        ],
        'list'=>[
            'type'=>explode("_",$array_positions['position_list'])[0],
            'position'=>explode("_",$array_positions['position_list'])[1],
            'order'=>$array_orders['controls_arrow'],
            'style'=>'background-color:'.$array_colors['list_background'].';color:'.$array_colors['list_color'].';',
            'style_hover'=>'background-color:'.$array_colors['list_background_hover'].';color:'.$array_colors['list_color_hover'].';'
        ],
        'arrows'=>[
            'type'=>explode("_",$array_positions['position_arrows'])[0],
            'position'=>explode("_",$array_positions['position_arrows'])[1],
            'order'=>$array_orders['controls_arrow'],
            'style'=>'background-color:'.$array_colors['arrows_background'].';color:'.$array_colors['arrows_color'].';',
            'style_hover'=>'background-color:'.$array_colors['arrows_background_hover'].';color:'.$array_colors['arrows_color_hover'].';'
        ],
        'nav_arrows'=>[
            'style'=>'background-color:transparent;color:'.$array_colors['nav_arrows_color'].';',
            'style_hover'=>'background-color:transparent;color:'.$array_colors['nav_arrows_color_hover'].';'
        ],
        'voice'=>[
            'type'=>'button',
            'position'=>'left',
            'order'=>0
        ],
        'custom'=>[
            'type'=>explode("_",$array_positions['position_custom'])[0],
            'position'=>explode("_",$array_positions['position_custom'])[1],
            'order'=>$array_orders['custom_control'],
            'style'=>'background-color:'.$array_colors['custom_background'].';color:'.$array_colors['custom_color'].';',
            'style_hover'=>'background-color:'.$array_colors['custom_background_hover'].';color:'.$array_colors['custom_color_hover'].';',
            'icon'=>$array_icons['custom'],
            'icon_library'=>$array_library_icons['custom'],
            'label'=>$custom_title
        ],
        'custom2'=>[
            'type'=>explode("_",$array_positions['position_custom2'])[0],
            'position'=>explode("_",$array_positions['position_custom2'])[1],
            'order'=>$array_orders['custom2_control'],
            'style'=>'background-color:'.$array_colors['custom2_background'].';color:'.$array_colors['custom2_color'].';',
            'style_hover'=>'background-color:'.$array_colors['custom2_background_hover'].';color:'.$array_colors['custom2_color_hover'].';',
            'icon'=>$array_icons['custom2'],
            'icon_library'=>$array_library_icons['custom2'],
            'label'=>$custom2_title
        ],
        'custom3'=>[
            'type'=>explode("_",$array_positions['position_custom3'])[0],
            'position'=>explode("_",$array_positions['position_custom3'])[1],
            'order'=>$array_orders['custom3_control'],
            'style'=>'background-color:'.$array_colors['custom3_background'].';color:'.$array_colors['custom3_color'].';',
            'style_hover'=>'background-color:'.$array_colors['custom3_background_hover'].';color:'.$array_colors['custom3_color_hover'].';',
            'icon'=>$array_icons['custom3'],
            'icon_library'=>$array_library_icons['custom3'],
            'label'=>$custom3_title
        ],
        'custom4'=>[
            'type'=>explode("_",$array_positions['position_custom4'])[0],
            'position'=>explode("_",$array_positions['position_custom4'])[1],
            'order'=>$array_orders['custom4_control'],
            'style'=>'background-color:'.$array_colors['custom4_background'].';color:'.$array_colors['custom4_color'].';',
            'style_hover'=>'background-color:'.$array_colors['custom4_background_hover'].';color:'.$array_colors['custom4_color_hover'].';',
            'icon'=>$array_icons['custom4'],
            'icon_library'=>$array_library_icons['custom4'],
            'label'=>$custom4_title
        ],
        'custom5'=>[
            'type'=>explode("_",$array_positions['position_custom5'])[0],
            'position'=>explode("_",$array_positions['position_custom5'])[1],
            'order'=>$array_orders['custom5_control'],
            'style'=>'background-color:'.$array_colors['custom5_background'].';color:'.$array_colors['custom5_color'].';',
            'style_hover'=>'background-color:'.$array_colors['custom5_background_hover'].';color:'.$array_colors['custom5_color_hover'].';',
            'icon'=>$array_icons['custom5'],
            'icon_library'=>$array_library_icons['custom5'],
            'label'=>$custom5_title
        ],
        'info'=>[
            'type'=>explode("_",$array_positions['position_info'])[0],
            'position'=>explode("_",$array_positions['position_info'])[1],
            'order'=>$array_orders['info_control'],
            'style'=>'background-color:'.$array_colors['info_background'].';color:'.$array_colors['info_color'].';',
            'style_hover'=>'background-color:'.$array_colors['info_background_hover'].';color:'.$array_colors['info_color_hover'].';',
            'icon'=>$array_icons['info'],
            'icon_library'=>$array_library_icons['info'],
        ],
        'dollhouse'=>[
            'type'=>explode("_",$array_positions['position_dollhouse'])[0],
            'position'=>explode("_",$array_positions['position_dollhouse'])[1],
            'order'=>$array_orders['dollhouse_control'],
            'style'=>'background-color:'.$array_colors['dollhouse_background'].';color:'.$array_colors['dollhouse_color'].';',
            'style_hover'=>'background-color:'.$array_colors['dollhouse_background_hover'].';color:'.$array_colors['dollhouse_color_hover'].';',
            'icon'=>$array_icons['dollhouse'],
            'icon_library'=>$array_library_icons['dollhouse'],
        ],
        'gallery'=>[
            'type'=>explode("_",$array_positions['position_gallery'])[0],
            'position'=>explode("_",$array_positions['position_gallery'])[1],
            'order'=>$array_orders['gallery_control'],
            'style'=>'background-color:'.$array_colors['gallery_background'].';color:'.$array_colors['gallery_color'].';',
            'style_hover'=>'background-color:'.$array_colors['gallery_background_hover'].';color:'.$array_colors['gallery_color_hover'].';',
            'icon'=>$array_icons['gallery'],
            'icon_library'=>$array_library_icons['gallery'],
        ],
        'facebook'=>[
            'type'=>explode("_",$array_positions['position_facebook'])[0],
            'position'=>explode("_",$array_positions['position_facebook'])[1],
            'order'=>$array_orders['facebook_control'],
            'style'=>'background-color:'.$array_colors['facebook_background'].';color:'.$array_colors['facebook_color'].';',
            'style_hover'=>'background-color:'.$array_colors['facebook_background_hover'].';color:'.$array_colors['facebook_color_hover'].';',
            'icon'=>$array_icons['facebook'],
            'icon_library'=>$array_library_icons['facebook'],
        ],
        'whatsapp'=>[
            'type'=>explode("_",$array_positions['position_whatsapp'])[0],
            'position'=>explode("_",$array_positions['position_whatsapp'])[1],
            'order'=>$array_orders['whatsapp_control'],
            'style'=>'background-color:'.$array_colors['whatsapp_background'].';color:'.$array_colors['whatsapp_color'].';',
            'style_hover'=>'background-color:'.$array_colors['whatsapp_background_hover'].';color:'.$array_colors['whatsapp_color_hover'].';',
            'icon'=>$array_icons['whatsapp'],
            'icon_library'=>$array_library_icons['whatsapp'],
        ],
        'presentation'=>[
            'type'=>explode("_",$array_positions['position_presentation'])[0],
            'position'=>explode("_",$array_positions['position_presentation'])[1],
            'order'=>$array_orders['presentation_control'],
            'style'=>'background-color:'.$array_colors['presentation_background'].';color:'.$array_colors['presentation_color'].';',
            'style_hover'=>'background-color:'.$array_colors['presentation_background_hover'].';color:'.$array_colors['presentation_color_hover'].';',
            'icon'=>$array_icons['presentation'],
            'icon_library'=>$array_library_icons['presentation'],
        ],
        'share'=>[
            'type'=>explode("_",$array_positions['position_share'])[0],
            'position'=>explode("_",$array_positions['position_share'])[1],
            'order'=>$array_orders['share_control'],
            'style'=>'background-color:'.$array_colors['share_background'].';color:'.$array_colors['share_color'].';',
            'style_hover'=>'background-color:'.$array_colors['share_background_hover'].';color:'.$array_colors['share_color_hover'].';',
            'icon'=>$array_icons['share'],
            'icon_library'=>$array_library_icons['share'],
            'providers'=>$share_providers
        ],
        'form'=>[
            'type'=>explode("_",$array_positions['position_form'])[0],
            'position'=>explode("_",$array_positions['position_form'])[1],
            'order'=>$array_orders['form_control'],
            'style'=>'background-color:'.$array_colors['form_background'].';color:'.$array_colors['form_color'].';',
            'style_hover'=>'background-color:'.$array_colors['form_background_hover'].';color:'.$array_colors['form_color_hover'].';',
            'icon'=>$array_icons['form'],
            'icon_library'=>$array_library_icons['form'],
        ],
        'live'=>[
            'type'=>explode("_",$array_positions['position_live'])[0],
            'position'=>explode("_",$array_positions['position_live'])[1],
            'order'=>$array_orders['live_control'],
            'style'=>'background-color:'.$array_colors['live_background'].';color:'.$array_colors['live_color'].';',
            'style_hover'=>'background-color:'.$array_colors['live_background_hover'].';color:'.$array_colors['live_color_hover'].';',
            'icon'=>$array_icons['live'],
            'icon_library'=>$array_library_icons['live'],
        ],
        'meeting'=>[
            'type'=>explode("_",$array_positions['position_meeting'])[0],
            'position'=>explode("_",$array_positions['position_meeting'])[1],
            'order'=>$array_orders['meeting_control'],
            'style'=>'background-color:'.$array_colors['meeting_background'].';color:'.$array_colors['meeting_color'].';',
            'style_hover'=>'background-color:'.$array_colors['meeting_background_hover'].';color:'.$array_colors['meeting_color_hover'].';',
            'icon'=>$array_icons['meeting'],
            'icon_library'=>$array_library_icons['meeting'],
        ],
        'vr'=>[
            'type'=>explode("_",$array_positions['position_vr'])[0],
            'position'=>explode("_",$array_positions['position_vr'])[1],
            'order'=>$array_orders['vr_control'],
            'style'=>'background-color:'.$array_colors['vr_background'].';color:'.$array_colors['vr_color'].';',
            'style_hover'=>'background-color:'.$array_colors['vr_background_hover'].';color:'.$array_colors['vr_color_hover'].';',
            'icon'=>$array_icons['vr'],
            'icon_library'=>$array_library_icons['vr'],
        ],
        'compass'=>[
            'type'=>explode("_",$array_positions['position_compass'])[0],
            'position'=>explode("_",$array_positions['position_compass'])[1],
            'order'=>$array_orders['compass_control'],
            'style'=>'background-color:'.$array_colors['compass_background'].';color:'.$array_colors['compass_color'].';',
            'style_hover'=>'background-color:'.$array_colors['compass_background_hover'].';color:'.$array_colors['compass_color_hover'].';'
        ],
        'icons'=>[
            'type'=>explode("_",$array_positions['position_icons'])[0],
            'position'=>explode("_",$array_positions['position_icons'])[1],
            'order'=>$array_orders['icons_control'],
            'style'=>'background-color:'.$array_colors['icons_background'].';color:'.$array_colors['icons_color'].';',
            'style_hover'=>'background-color:'.$array_colors['icons_background_hover'].';color:'.$array_colors['icons_color_hover'].';',
            'icon'=>$array_icons['icons'],
            'icon_library'=>$array_library_icons['icons'],
        ],
        'measures'=>[
            'type'=>explode("_",$array_positions['position_measures'])[0],
            'position'=>explode("_",$array_positions['position_measures'])[1],
            'order'=>$array_orders['measures_control'],
            'style'=>'background-color:'.$array_colors['measures_background'].';color:'.$array_colors['measures_color'].';',
            'style_hover'=>'background-color:'.$array_colors['measures_background_hover'].';color:'.$array_colors['measures_color_hover'].';',
            'icon'=>$array_icons['measures'],
            'icon_library'=>$array_library_icons['measures'],
        ],
        'autorotate'=>[
            'type'=>explode("_",$array_positions['position_autorotate'])[0],
            'position'=>explode("_",$array_positions['position_autorotate'])[1],
            'order'=>$array_orders['autorotate_control'],
            'style'=>'background-color:'.$array_colors['autorotate_background'].';color:'.$array_colors['autorotate_color'].';',
            'style_hover'=>'background-color:'.$array_colors['autorotate_background_hover'].';color:'.$array_colors['autorotate_color_hover'].';',
            'icon'=>$array_icons['autorotate'],
            'icon_library'=>$array_library_icons['autorotate'],
        ],
        'orient'=>[
            'type'=>explode("_",$array_positions['position_orient'])[0],
            'position'=>explode("_",$array_positions['position_orient'])[1],
            'order'=>$array_orders['orient_control'],
            'style'=>'background-color:'.$array_colors['orient_background'].';color:'.$array_colors['orient_color'].';',
            'style_hover'=>'background-color:'.$array_colors['orient_background_hover'].';color:'.$array_colors['orient_color_hover'].';',
            'icon'=>$array_icons['orient'],
            'icon_library'=>$array_library_icons['orient'],
        ],
        'annotations'=>[
            'type'=>explode("_",$array_positions['position_annotations'])[0],
            'position'=>explode("_",$array_positions['position_annotations'])[1],
            'order'=>$array_orders['annotations_control'],
            'style'=>'background-color:'.$array_colors['annotations_background'].';color:'.$array_colors['annotations_color'].';',
            'style_hover'=>'background-color:'.$array_colors['annotations_background_hover'].';color:'.$array_colors['annotations_color_hover'].';',
            'icon'=>$array_icons['annotations'],
            'icon_library'=>$array_library_icons['annotations'],
        ],
        'location'=>[
            'type'=>explode("_",$array_positions['position_location'])[0],
            'position'=>explode("_",$array_positions['position_location'])[1],
            'order'=>$array_orders['location_control'],
            'style'=>'background-color:'.$array_colors['location_background'].';color:'.$array_colors['location_color'].';',
            'style_hover'=>'background-color:'.$array_colors['location_background_hover'].';color:'.$array_colors['location_color_hover'].';',
            'icon'=>$array_icons['location'],
            'icon_library'=>$array_library_icons['location'],
            'label'=>$location_title
        ],
        'media'=>[
            'type'=>explode("_",$array_positions['position_media'])[0],
            'position'=>explode("_",$array_positions['position_media'])[1],
            'order'=>$array_orders['media_control'],
            'style'=>'background-color:'.$array_colors['media_background'].';color:'.$array_colors['media_color'].';',
            'style_hover'=>'background-color:'.$array_colors['media_background_hover'].';color:'.$array_colors['media_color_hover'].';',
            'icon'=>$array_icons['media'],
            'icon_library'=>$array_library_icons['media'],
            'label'=>$media_title
        ],
        'snapshot'=>[
            'type'=>explode("_",$array_positions['position_snapshot'])[0],
            'position'=>explode("_",$array_positions['position_snapshot'])[1],
            'order'=>$array_orders['snapshot_control'],
            'style'=>'background-color:'.$array_colors['snapshot_background'].';color:'.$array_colors['snapshot_color'].';',
            'style_hover'=>'background-color:'.$array_colors['snapshot_background_hover'].';color:'.$array_colors['snapshot_color_hover'].';',
            'icon'=>$array_icons['snapshot'],
            'icon_library'=>$array_library_icons['snapshot'],
        ],
    ]
];
$ui_style = json_encode($ui_style,JSON_UNESCAPED_UNICODE);
if($id_preset!=null && $apply_preset==0) {
    if($id_preset==0) {
        $query = "INSERT INTO svt_editor_ui_presets(id_user,name,public,ui_style) VALUES(?,?,?,?);";
        if($smt = $mysqli->prepare($query)) {
            $smt->bind_param('isis',$id_user,$name_preset,$preset_public,$ui_style);
            $result = $smt->execute();
            if($result) {
                $id_new_preset = $mysqli->insert_id;
                $id_preset = $id_new_preset;
            }
        }
    } else {
        $query = "UPDATE svt_editor_ui_presets SET name=?,public=?,ui_style=? WHERE id=?;";
        if($smt = $mysqli->prepare($query)) {
            $smt->bind_param('sisi',$name_preset,$preset_public,$ui_style,$id_preset);
            $smt->execute();
        }
        $id_new_preset = 0;
    }
    $query = "SELECT id_preset FROM svt_editor_ui_presets_values WHERE id_preset=$id_preset LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $query_up = "UPDATE svt_editor_ui_presets_values SET font_viewer=?,info_box_type=?,arrows_nav=?,voice_commands=?,compass=?,auto_show_slider=?,nav_slider=?,nav_slider_mode=?,show_custom=?,show_custom2=?,show_custom3=?,show_custom4=?,show_custom5=?,show_info=?,show_gallery=?,show_icons_toggle=?,show_measures_toggle=?,show_autorotation_toggle=?,show_nav_control=?,show_presentation=?,show_main_form=?,show_share=?,show_device_orientation=?,drag_device_orientation=?,show_webvr=?,webvr_new_window=?,show_audio=?,show_vt_title=?,show_fullscreen=?,show_map=?,show_map_tour=?,live_session=?,show_annotations=?,show_list_alt=?,fb_messenger=?,whatsapp_chat=?,meeting=?,autoclose_menu=?,autoclose_list_alt=?,autoclose_slider=?,autoclose_map=?,show_logo=?,form_enable=?,show_dollhouse=?,autorotate_speed=?,autorotate_inactivity=?,song_autoplay=?,show_location=?,show_comments=?,show_language=?,show_poweredby=?,show_media=?,show_avatar_video=?,enable_visitor_rt=?,enable_views_stat=?,interval_visitor_rt=?,markers_icon=?,markers_icon_type=?,markers_id_icon_library=?,markers_background=?,markers_color=?,markers_show_room=?,markers_tooltip_type=?,markers_tooltip_visibility=?,markers_tooltip_background=?,markers_tooltip_color=?,markers_default_scale=?,markers_default_rotateX=?,markers_default_rotateZ=?,markers_default_size_scale=?,markers_default_sound=?,markers_animation=?,pois_icon=?,pois_icon_type=?,pois_id_icon_library=?.pois_background=?,pois_color=?,pois_style=?,pois_tooltip_type=?,pois_tooltip_visibility=?,pois_tooltip_background=?,pois_tooltip_color=?,pois_default_scale=?,pois_default_rotateX=?,pois_default_rotateZ=?,pois_default_size_scale=?,pois_default_sound=?,pois_animation=?,nadir_size=?,markers_default_sticky=?,pois_default_sticky=?,show_snapshot=?,grouped_list_alt=?,enable_rooms_multiple=? WHERE id_preset=?";
            if ($smt_up = $mysqli->prepare($query_up)) {
                $smt_up->bind_param('ssiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiissississssiiidssssississssiiidsssiiiiii',$font_viewer,$info_box_type,$arrows_nav,$voice_commands,$compass,$auto_show_slider,$nav_slider,$nav_slider_mode,$show_custom,$show_custom2,$show_custom3,$show_custom4,$show_custom5,$show_info,$show_gallery,$show_icons_toggle,$show_measures_toggle,$show_autorotation_toggle,$show_nav_control,$show_presentation,$show_main_form,$show_share,$show_device_orientation,$drag_device_orientation,$show_webvr,$webvr_new_window,$show_audio,$show_vt_title,$show_fullscreen,$show_map,$show_map_tour,$live_session,$show_annotations,$show_list_alt,$fb_messenger,$whatsapp_chat,$meeting,$autoclose_menu,$autoclose_list_alt,$autoclose_slider,$autoclose_map,$show_logo,$form_enable,$show_dollhouse,$autorotate_speed,$autorotate_inactivity,$song_autoplay,$show_location,$show_comments,$show_language,$show_poweredby,$show_media,$show_avatar_video,$enable_visitor_rt,$enable_views_stat,$interval_visitor_rt,$markers_icon,$markers_icon_type,$markers_id_icon_library,$markers_background,$markers_color,$markers_show_room,$markers_tooltip_type,$markers_tooltip_visibility,$markers_tooltip_background,$markers_tooltip_color,$markers_default_scale,$markers_default_rotateX,$markers_default_rotateZ,$markers_default_size_scale,$markers_default_sound,$markers_animation,$pois_icon,$pois_icon_type,$pois_id_icon_library,$pois_background,$pois_color,$pois_style,$pois_tooltip_type,$pois_tooltip_visibility,$pois_tooltip_background,$pois_tooltip_color,$pois_default_scale,$pois_default_rotateX,$pois_default_rotateZ,$pois_default_size_scale,$pois_default_sound,$pois_animation,$nadir_size,$markers_default_sticky,$pois_default_sticky,$show_snapshot,$grouped_list_alt,$enable_rooms_multiple,$id_preset);
                $smt_up->execute();
            }
        } else {
            $query_ip = "INSERT INTO svt_editor_ui_presets_values(id_preset,font_viewer,info_box_type,arrows_nav,voice_commands,compass,auto_show_slider,nav_slider,nav_slider_mode,show_custom,show_custom2,show_custom3,show_custom4,show_custom5,show_info,show_gallery,show_icons_toggle,show_measures_toggle,show_autorotation_toggle,show_nav_control,show_presentation,show_main_form,show_share,show_device_orientation,drag_device_orientation,show_webvr,webvr_new_window,show_audio,show_vt_title,show_fullscreen,show_map,show_map_tour,live_session,show_annotations,show_list_alt,fb_messenger,whatsapp_chat,meeting,autoclose_menu,autoclose_list_alt,autoclose_slider,autoclose_map,show_logo,form_enable,show_dollhouse,autorotate_speed,autorotate_inactivity,song_autoplay,show_location,show_comments,show_language,show_poweredby,show_media,show_avatar_video,enable_visitor_rt,enable_views_stat,interval_visitor_rt,markers_icon,markers_icon_type,markers_id_icon_library,markers_background,markers_color,markers_show_room,markers_tooltip_type,markers_tooltip_visibility,markers_tooltip_background,markers_tooltip_color,markers_default_scale,markers_default_rotateX,markers_default_rotateZ,markers_default_size_scale,markers_default_sound,markers_animation,pois_icon,pois_icon_type,pois_id_icon_library,pois_background,pois_color,pois_style,pois_tooltip_type,pois_tooltip_visibility,pois_tooltip_background,pois_tooltip_color,pois_default_scale,pois_default_rotateX,pois_default_rotateZ,pois_default_size_scale,pois_default_sound,pois_animation,nadir_size,markers_default_sticky,pois_default_sticky,show_snapshot,grouped_list_alt,enable_rooms_multiple) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            if($smt_ip = $mysqli->prepare($query_ip)) {
                $smt_ip->bind_param('issiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiissississssiiidssssississssiiidsssiiiii',$id_preset,$font_viewer,$info_box_type,$arrows_nav,$voice_commands,$compass,$auto_show_slider,$nav_slider,$nav_slider_mode,$show_custom,$show_custom2,$show_custom3,$show_custom4,$show_custom5,$show_info,$show_gallery,$show_icons_toggle,$show_measures_toggle,$show_autorotation_toggle,$show_nav_control,$show_presentation,$show_main_form,$show_share,$show_device_orientation,$drag_device_orientation,$show_webvr,$webvr_new_window,$show_audio,$show_vt_title,$show_fullscreen,$show_map,$show_map_tour,$live_session,$show_annotations,$show_list_alt,$fb_messenger,$whatsapp_chat,$meeting,$autoclose_menu,$autoclose_list_alt,$autoclose_slider,$autoclose_map,$show_logo,$form_enable,$show_dollhouse,$autorotate_speed,$autorotate_inactivity,$song_autoplay,$show_location,$show_comments,$show_language,$show_poweredby,$show_media,$show_avatar_video,$enable_visitor_rt,$enable_views_stat,$interval_visitor_rt,$markers_icon,$markers_icon_type,$markers_id_icon_library,$markers_background,$markers_color,$markers_show_room,$markers_tooltip_type,$markers_tooltip_visibility,$markers_tooltip_background,$markers_tooltip_color,$markers_default_scale,$markers_default_rotateX,$markers_default_rotateZ,$markers_default_size_scale,$markers_default_sound,$markers_animation,$pois_icon,$pois_icon_type,$pois_id_icon_library,$pois_background,$pois_color,$pois_style,$pois_tooltip_type,$pois_tooltip_visibility,$pois_tooltip_background,$pois_tooltip_color,$pois_default_scale,$pois_default_rotateX,$pois_default_rotateZ,$pois_default_size_scale,$pois_default_sound,$pois_animation,$nadir_size,$markers_default_sticky,$pois_default_sticky,$show_snapshot,$grouped_list_alt,$enable_rooms_multiple);
                $smt_ip->execute();
            }
        }
    }
    ob_end_clean();
    echo json_encode(array("status"=>"ok","id_preset"=>$id_new_preset));
    exit;
}
if($apply_preset==1) {
    $query_p = "SELECT ui_style FROM svt_editor_ui_presets WHERE id=$id_preset LIMIT 1;";
    $result_p = $mysqli->query($query_p);
    if($result_p) {
        if($result_p->num_rows==1) {
            $row_p = $result_p->fetch_array(MYSQLI_ASSOC);
            $ui_style = $row_p['ui_style'];
            if (!empty($ui_style)) {
                $ui_style_array = json_decode($ui_style, true);
                foreach ($ui_style_array['controls'] as $key => $item) {
                    if(!empty($item['icon_library']) && $item['icon_library']!=0) {
                        $id_icon = $item['icon_library'];
                        $query_check_icon = "SELECT * FROM svt_icons WHERE id=$id_icon AND id_virtualtour=$id_virtualtour";
                        $result_check_icon = $mysqli->query($query_check_icon);
                        if($result_check_icon->num_rows==0) {
                            $mysqli->query("CREATE TEMPORARY TABLE svt_icon_tmp SELECT * FROM svt_icons WHERE id=$id_icon;");
                            $mysqli->query("UPDATE svt_icon_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_icons),id_virtualtour=$id_virtualtour;");
                            $mysqli->query("INSERT INTO svt_icons SELECT * FROM svt_icon_tmp;");
                            $id_icon_new = $mysqli->insert_id;
                            $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_icon_tmp;");
                        } else {
                            $id_icon_new = $id_icon;
                        }
                        $ui_style_array['controls'][$key]['icon_library'] = $id_icon_new;
                    }
                }
                $ui_style = str_replace("'", "\'", json_encode($ui_style_array, JSON_UNESCAPED_UNICODE));
            }
        }
    }
    $query_p = "SELECT * FROM svt_editor_ui_presets_values WHERE id_preset=$id_preset LIMIT 1;";
    $result_p = $mysqli->query($query_p);
    if($result_p) {
        if($result_p->num_rows==1) {
            $row_p = $result_p->fetch_array(MYSQLI_ASSOC);
            $font_viewer = $row_p['font_viewer'];
            $info_box_type = $row_p['info_box_type'];
            $arrows_nav = $row_p['arrows_nav'];
            $voice_commands = $row_p['voice_commands'];
            $compass = $row_p['compass'];
            $auto_show_slider = $row_p['auto_show_slider'];
            $nav_slider = $row_p['nav_slider'];
            $nav_slider_mode = $row_p['nav_slider_mode'];
            $show_custom = $row_p['show_custom'];
            $show_custom2 = $row_p['show_custom2'];
            $show_custom3 = $row_p['show_custom3'];
            $show_custom4 = $row_p['show_custom4'];
            $show_custom5 = $row_p['show_custom5'];
            $show_info = $row_p['show_info'];
            $show_gallery = $row_p['show_gallery'];
            $show_icons_toggle = $row_p['show_icons_toggle'];
            $show_measures_toggle = $row_p['show_measures_toggle'];
            $show_autorotation_toggle = $row_p['show_autorotation_toggle'];
            $show_nav_control = $row_p['show_nav_control'];
            $show_presentation = $row_p['show_presentation'];
            $show_main_form = $row_p['show_main_form'];
            $show_share = $row_p['show_share'];
            $show_device_orientation = $row_p['show_device_orientation'];
            $drag_device_orientation = $row_p['drag_device_orientation'];
            $show_webvr = $row_p['show_webvr'];
            $webvr_new_window = $row_p['webvr_new_window'];
            $show_audio = $row_p['show_audio'];
            $show_vt_title = $row_p['show_vt_title'];
            $show_fullscreen = $row_p['show_fullscreen'];
            $show_map = $row_p['show_map'];
            $show_map_tour = $row_p['show_map_tour'];
            $live_session = $row_p['live_session'];
            $show_annotations = $row_p['show_annotations'];
            $show_list_alt = $row_p['show_list_alt'];
            $fb_messenger = $row_p['fb_messenger'];
            $whatsapp_chat = $row_p['whatsapp_chat'];
            $meeting = $row_p['meeting'];
            $autoclose_menu = $row_p['autoclose_menu'];
            $autoclose_list_alt = $row_p['autoclose_list_alt'];
            $autoclose_slider = $row_p['autoclose_slider'];
            $grouped_list_alt = $row_p['grouped_list_alt'];
            $autoclose_map = $row_p['autoclose_map'];
            $show_logo = $row_p['show_logo'];
            $form_enable = $row_p['form_enable'];
            $show_dollhouse = $row_p['show_dollhouse'];
            $autorotate_speed = $row_p['autorotate_speed'];
            $autorotate_inactivity = $row_p['autorotate_inactivity'];
            $song_autoplay = $row_p['song_autoplay'];
            $show_location = $row_p['show_location'];
            $show_comments = $row_p['show_comments'];
            $show_language = $row_p['show_language'];
            $show_poweredby = $row_p['show_poweredby'];
            $show_media = $row_p['show_media'];
            $show_snapshot = $row_p['show_snapshot'];
            $show_avatar_video = $row_p['show_avatar_video'];
            $enable_visitor_rt = $row_p['enable_visitor_rt'];
            $enable_views_stat = $row_p['enable_views_stat'];
            $enable_rooms_multiple = $row_p['enable_rooms_multiple'];
            $interval_visitor_rt = $row_p['interval_visitor_rt'];
            $markers_icon = $row_p['markers_icon'];
            $markers_icon_type = $row_p['markers_icon_type'];
            $markers_id_icon_library = $row_p['markers_id_icon_library'];
            $markers_background = $row_p['markers_background'];
            $markers_color = $row_p['markers_color'];
            $markers_show_room = $row_p['markers_show_room'];
            $markers_tooltip_type = $row_p['markers_tooltip_type'];
            $markers_tooltip_visibility = $row_p['markers_tooltip_visibility'];
            $markers_tooltip_background = $row_p['markers_tooltip_background'];
            $markers_tooltip_color = $row_p['markers_tooltip_color'];
            $markers_default_scale = $row_p['markers_default_scale'];
            $markers_default_sticky = $row_p['markers_default_sticky'];
            $markers_default_rotateX = $row_p['markers_default_rotateX'];
            $markers_default_rotateZ = $row_p['markers_default_rotateZ'];
            $markers_default_size_scale = $row_p['markers_default_size_scale'];
            $markers_default_sound = $row_p['markers_default_sound'];
            $markers_animation = $row_p['markers_animation'];
            $pois_icon = $row_p['pois_icon'];
            $pois_icon_type = $row_p['pois_icon_type'];
            $pois_id_icon_library = $row_p['pois_id_icon_library'];
            $pois_background = $row_p['pois_background'];
            $pois_color = $row_p['pois_color'];
            $pois_style = $row_p['pois_style'];
            $pois_tooltip_type = $row_p['pois_tooltip_type'];
            $pois_tooltip_visibility = $row_p['pois_tooltip_visibility'];
            $pois_tooltip_background = $row_p['pois_tooltip_background'];
            $pois_tooltip_color = $row_p['pois_tooltip_color'];
            $pois_default_scale = $row_p['pois_default_scale'];
            $pois_default_sticky = $row_p['pois_default_sticky'];
            $pois_default_rotateX = $row_p['pois_default_rotateX'];
            $pois_default_rotateZ = $row_p['pois_default_rotateZ'];
            $pois_default_size_scale = $row_p['pois_default_size_scale'];
            $pois_default_sound = $row_p['pois_default_sound'];
            $pois_animation = $row_p['pois_animation'];
            $nadir_size = $row_p['nadir_size'];
        }
    }
}
$mode_version = "";
if(!empty($id_version)) {
    switch($id_version) {
        case -1:
            $query_a = "SELECT auto_start,hide_loading,show_background,loading_background_color,loading_text_color,flyin,flyin_duration FROM svt_virtualtours WHERE id=$id_virtualtour LIMIT 1;";
            $result_a = $mysqli->query($query_a);
            if($result_a) {
                if($result_a->num_rows==1) {
                    $row_a = $result_a->fetch_array(MYSQLI_ASSOC);
                    $auto_start = $row_a['auto_start'];
                    $hide_loading = $row_a['hide_loading'];
                    $show_background = $row_a['show_background'];
                    $loading_background_color = $row_a['loading_background_color'];
                    $loading_text_color = $row_a['loading_text_color'];
                    $flyin = $row_a['flyin'];
                    $flyin_duration = $row_a['flyin_duration'];
                    $query_v = "INSERT INTO svt_virtualtours_versions(id_virtualtour,version,ui_style,font_viewer,info_box_type,arrows_nav,voice_commands,compass,auto_show_slider,nav_slider,nav_slider_mode,show_custom,show_custom2,show_custom3,show_custom4,show_custom5,show_info,show_gallery,show_icons_toggle,show_measures_toggle,show_autorotation_toggle,show_nav_control,show_presentation,show_main_form,show_share,show_device_orientation,drag_device_orientation,show_webvr,webvr_new_window,show_audio,show_vt_title,show_fullscreen,show_map,show_map_tour,live_session,show_annotations,show_list_alt,fb_messenger,whatsapp_chat,meeting,autoclose_menu,autoclose_list_alt,autoclose_slider,autoclose_map,show_logo,form_enable,show_dollhouse,autorotate_speed,autorotate_inactivity,song_autoplay,show_location,show_comments,show_language,show_poweredby,show_media,show_avatar_video,enable_visitor_rt,enable_views_stat,interval_visitor_rt,auto_start,hide_loading,show_background,loading_background_color,loading_text_color,flyin,flyin_duration,show_snapshot,grouped_list_alt,enable_rooms_multiple) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                    if($smt_v = $mysqli->prepare($query_v)) {
                        $smt_v->bind_param('issssiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiissiiiii',$id_virtualtour,$name_version,$ui_style,$font_viewer,$info_box_type,$arrows_nav,$voice_commands,$compass,$auto_show_slider,$nav_slider,$nav_slider_mode,$show_custom,$show_custom2,$show_custom3,$show_custom4,$show_custom5,$show_info,$show_gallery,$show_icons_toggle,$show_measures_toggle,$show_autorotation_toggle,$show_nav_control,$show_presentation,$show_main_form,$show_share,$show_device_orientation,$drag_device_orientation,$show_webvr,$webvr_new_window,$show_audio,$show_vt_title,$show_fullscreen,$show_map,$show_map_tour,$live_session,$show_annotations,$show_list_alt,$fb_messenger,$whatsapp_chat,$meeting,$autoclose_menu,$autoclose_list_alt,$autoclose_slider,$autoclose_map,$show_logo,$form_enable,$show_dollhouse,$autorotate_speed,$autorotate_inactivity,$song_autoplay,$show_location,$show_comments,$show_language,$show_poweredby,$show_media,$show_avatar_video,$enable_visitor_rt,$enable_views_stat,$interval_visitor_rt,$auto_start,$hide_loading,$show_background,$loading_background_color,$loading_text_color,$flyin,$flyin_duration,$show_snapshot,$grouped_list_alt,$enable_rooms_multiple);
                        $result_v = $smt_v->execute();
                        if($result_v) {
                            $id_version = $mysqli->insert_id;
                            $mode_version = "add";
                        }
                    }
                }
            }
            break;
        default:
            $query_v = "UPDATE svt_virtualtours_versions SET version=? WHERE id=? AND id_virtualtour=?";
            if ($smt_v = $mysqli->prepare($query_v)) {
                $smt_v->bind_param('sii',$name_version,$id_version,$id_virtualtour);
                $result_v = $smt_v->execute();
                $mode_version = "update";
            }
            break;
    }
}
if($id_version_sel==0) {
    $query = "UPDATE svt_virtualtours SET font_viewer=?,arrows_nav=?,voice_commands=?,compass=?,auto_show_slider=?,nav_slider=?,nav_slider_mode=?,show_custom=?,show_custom2=?,show_custom3=?,show_custom4=?,show_custom5=?,show_info=?,info_box_type=?,show_gallery=?,show_icons_toggle=?,show_measures_toggle=?,show_autorotation_toggle=?,show_nav_control=?,show_presentation=?,show_main_form=?,show_share=?,show_device_orientation=?,drag_device_orientation=?,show_webvr=?,webvr_new_window=?,show_audio=?,show_vt_title=?,show_fullscreen=?,show_map=?,show_map_tour=?,live_session=?,show_annotations=?,show_list_alt=?,fb_messenger=?,whatsapp_chat=?,meeting=?,autoclose_menu=?,autoclose_list_alt=?,autoclose_slider=?,autoclose_map=?,show_logo=?,ui_style=?,form_enable=?,custom_content=?,custom2_content=?,custom3_content=?,markers_icon=?,markers_icon_type=?,markers_id_icon_library=?,markers_color=?,markers_background=?,markers_show_room=?,pois_icon=?,pois_icon_type=?,pois_id_icon_library=?,pois_color=?,pois_background=?,pois_style=?,show_dollhouse=?,markers_tooltip_type=?,markers_tooltip_visibility=?,markers_tooltip_background=?,markers_tooltip_color=?,markers_default_scale=?,pois_tooltip_type=?,pois_tooltip_visibility=?,pois_tooltip_background=?,pois_tooltip_color=?,pois_default_scale=?,nadir_size=?,autorotate_speed=?,autorotate_inactivity=?,song_autoplay=?,fb_page_id=?,whatsapp_number=?,location_content=?,show_location=?,show_comments=?,disqus_shortname=?,markers_default_sound=?,pois_default_sound=?,language=?,languages_enabled=?,show_language=?,custom4_content=?,custom5_content=?,markers_animation=?,pois_animation=?,show_poweredby=?,markers_default_rotateX=?,markers_default_rotateZ=?,markers_default_size_scale=?,pois_default_rotateX=?,pois_default_rotateZ=?,pois_default_size_scale=?,show_media=?,media_file=?,show_avatar_video=?,enable_visitor_rt=?,enable_views_stat=?,interval_visitor_rt=?,markers_default_sticky=?,pois_default_sticky=?,show_snapshot=?,grouped_list_alt=?,enable_rooms_multiple=? WHERE id=?;";
    if($smt = $mysqli->prepare($query)) {
        $smt->bind_param('siiiiiiiiiiiisiiiiiiiiiiiiiiiiiiiiiiiiiiiisisssssissississiissssissssisiiisssiisssssissssiiidiidisiiiiiiiiii',$font_viewer,$arrows_nav,$voice_commands,$compass,$auto_show_slider,$nav_slider,$nav_slider_mode,$show_custom,$show_custom2,$show_custom3,$show_custom4,$show_custom5,$show_info,$info_box_type,$show_gallery,$show_icons_toggle,$show_measures_toggle,$show_autorotation_toggle,$show_nav_control,$show_presentation,$show_main_form,$show_share,$show_device_orientation,$drag_device_orientation,$show_webvr,$webvr_new_window,$show_audio,$show_vt_title,$show_fullscreen,$show_map,$show_map_tour,$live_session,$show_annotations,$show_list_alt,$fb_messenger,$whatsapp_chat,$meeting,$autoclose_menu,$autoclose_list_alt,$autoclose_slider,$autoclose_map,$show_logo,$ui_style,$form_enable,$custom_content,$custom2_content,$custom3_content,$markers_icon,$markers_icon_type,$markers_id_icon_library,$markers_color,$markers_background,$markers_show_room,$pois_icon,$pois_icon_type,$pois_id_icon_library,$pois_color,$pois_background,$pois_style,$show_dollhouse,$markers_tooltip_type,$markers_tooltip_visibility,$markers_tooltip_background,$markers_tooltip_color,$markers_default_scale,$pois_tooltip_type,$pois_tooltip_visibility,$pois_tooltip_background,$pois_tooltip_color,$pois_default_scale,$nadir_size,$autorotate_speed,$autorotate_inactivity,$song_autoplay,$fb_page_id,$whatsapp_number,$location_content,$show_location,$show_comments,$disqus_shortname,$markers_default_sound,$pois_default_sound,$language,$languages_enabled,$show_language,$custom4_content,$custom5_content,$markers_animation,$pois_animation,$show_poweredby,$markers_default_rotateX,$markers_default_rotateZ,$markers_default_size_scale,$pois_default_rotateX,$pois_default_rotateZ,$pois_default_size_scale,$show_media,$media_file,$show_avatar_video,$enable_visitor_rt,$enable_views_stat,$interval_visitor_rt,$markers_default_sticky,$pois_default_sticky,$show_snapshot,$grouped_list_alt,$enable_rooms_multiple,$id_virtualtour);
        $result = $smt->execute();
        if($result) {
            if($password_meeting!="keep_password") {
                $query = "UPDATE svt_virtualtours SET password_meeting=? WHERE id=?;";
                if($smt = $mysqli->prepare($query)) {
                    $smt->bind_param('si',$password_meeting,$id_virtualtour);
                    $smt->execute();
                }
            }
            if($password_livesession!="keep_password") {
                $query = "UPDATE svt_virtualtours SET password_livesession=? WHERE id=?;";
                if($smt = $mysqli->prepare($query)) {
                    $smt->bind_param('si',$password_livesession,$id_virtualtour);
                    $smt->execute();
                }
            }
            if($disqus_public_key!="keep_disqus_public_key") {
                $query = "UPDATE svt_virtualtours SET disqus_public_key=? WHERE id=?;";
                if($smt = $mysqli->prepare($query)) {
                    $smt->bind_param('si',$disqus_public_key,$id_virtualtour);
                    $smt->execute();
                }
            }
            ob_end_clean();
            echo json_encode(array("status"=>"ok","id_version"=>$id_version,"mode_version"=>$mode_version));
        } else {
            ob_end_clean();
            echo json_encode(array("status"=>"error"));
        }
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error"));
    }
} else {
    $query = "UPDATE svt_virtualtours_versions SET ui_style=?,font_viewer=?,info_box_type=?,arrows_nav=?,voice_commands=?,compass=?,auto_show_slider=?,nav_slider=?,nav_slider_mode=?,show_custom=?,show_custom2=?,show_custom3=?,show_custom4=?,show_custom5=?,show_info=?,show_gallery=?,show_icons_toggle=?,show_measures_toggle=?,show_autorotation_toggle=?,show_nav_control=?,show_presentation=?,show_main_form=?,show_share=?,show_device_orientation=?,drag_device_orientation=?,show_webvr=?,webvr_new_window=?,show_audio=?,show_vt_title=?,show_fullscreen=?,show_map=?,show_map_tour=?,live_session=?,show_annotations=?,show_list_alt=?,fb_messenger=?,whatsapp_chat=?,meeting=?,autoclose_menu=?,autoclose_list_alt=?,autoclose_slider=?,autoclose_map=?,show_logo=?,form_enable=?,show_dollhouse=?,autorotate_speed=?,autorotate_inactivity=?,song_autoplay=?,show_location=?,show_comments=?,show_language=?,show_poweredby=?,show_media=?,show_avatar_video=?,enable_visitor_rt=?,enable_views_stat=?,interval_visitor_rt=?,show_snapshot=?,grouped_list_alt=?,enable_rooms_multiple=? WHERE id=? AND id_virtualtour=?";
    if ($smt = $mysqli->prepare($query)) {
        $smt->bind_param('sssiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiiii',$ui_style,$font_viewer,$info_box_type,$arrows_nav,$voice_commands,$compass,$auto_show_slider,$nav_slider,$nav_slider_mode,$show_custom,$show_custom2,$show_custom3,$show_custom4,$show_custom5,$show_info,$show_gallery,$show_icons_toggle,$show_measures_toggle,$show_autorotation_toggle,$show_nav_control,$show_presentation,$show_main_form,$show_share,$show_device_orientation,$drag_device_orientation,$show_webvr,$webvr_new_window,$show_audio,$show_vt_title,$show_fullscreen,$show_map,$show_map_tour,$live_session,$show_annotations,$show_list_alt,$fb_messenger,$whatsapp_chat,$meeting,$autoclose_menu,$autoclose_list_alt,$autoclose_slider,$autoclose_map,$show_logo,$form_enable,$show_dollhouse,$autorotate_speed,$autorotate_inactivity,$song_autoplay,$show_location,$show_comments,$show_language,$show_poweredby,$show_media,$show_avatar_video,$enable_visitor_rt,$enable_views_stat,$interval_visitor_rt,$show_snapshot,$grouped_list_alt,$enable_rooms_multiple,$id_version_sel,$id_virtualtour);
        $result = $smt->execute();
        if($result) {
            ob_end_clean();
            echo json_encode(array("status"=>"ok","id_version"=>$id_version,"mode_version"=>$mode_version));
        } else {
            ob_end_clean();
            echo json_encode(array("status"=>"error"));
        }
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error"));
    }
}