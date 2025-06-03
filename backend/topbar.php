<?php
switch($user_info['plan_status']) {
    case 'active':
        $icon_plan = "<i class='fa fa-circle mt-1' style='color: green'></i>";
        break;
    case 'expiring':
        $icon_plan = "<i class='fa fa-circle mt-1' style='color: darkorange'></i>";
        break;
    case 'expired':
    case 'invalid_payment':
        $icon_plan = "<i class='fa fa-circle mt-1' style='color: red'></i>";
        break;
}
$settings = get_settings();
$enable_ai_room = $settings['enable_ai_room'];
$enable_autoenhance_room = $settings['enable_autoenhance_room'];
if(file_exists('../gsv/index.php')) {
    $gsv_installed = true;
} else {
    $gsv_installed = false;
}
if(empty($_SESSION['lang'])) {
    $lang = $settings['language'];
} else {
    $lang = $_SESSION['lang'];
}
$sub_header = '';
switch ($page) {
    case 'dashboard':
        $menu_title = '<i class="fas fa-fw fa-tachometer-alt text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Dashboard").'</span>';
        break;
    case 'edit_virtual_tour':
        $sub_header = '<div class="vt_select_header"><a id="save_btn" href="#" onclick="save_virtualtour(false);return false;" class="btn btn-sm btn-success btn-icon-split '.(($demo) ? 'disabled':'').'"><span class="icon text-white-50"><i class="far fa-circle"></i></span><span class="text">'._("SAVE").'</span></a></div>';
        $menu_title = '<i class="fas fa-fw fa-route text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Edit Tour").'</span> <i id="subtitle_header" class="text-gray-700">.........</i>';
        break;
    case 'virtual_tours':
        $menu_title = '<i class="fas fa-fw fa-list text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("List Tours").'</span>';
        break;
    case 'edit_virtual_tour_ui':
        $sub_header = print_virtualtour_selector('no');
        $menu_title = '<i class="fas fa-fw fa-swatchbook text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Editor UI").'</span>';
        break;
    case 'dollhouse':
        $sub_header = print_virtualtour_selector('dollhouse');
        $virtualtour = get_virtual_tour($_SESSION['id_virtualtour_sel'],$_SESSION['id_user']);
        $show_in_ui = $virtualtour['show_dollhouse'];
        $show_ui_icon = '<i style="font-size:10px;vertical-align:middle;color:'.(($show_in_ui>0)?'green':'orange').'" '.(($show_in_ui==0)?'title="'._("Not visible in the tour, enable it in the Editor UI").'"':'').' class="'.(($show_in_ui==0)?'help_t':'').' fas fa-circle"></i>';
        $menu_title = '<i class="fas fa-fw fa-cube text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("3D View").'</span> '.$show_ui_icon;
        break;
    case 'edit_room':
        $id_room = $_GET['id'];
        $next_prev_room = get_next_prev_room_id($id_room,$_SESSION['id_virtualtour_sel']);
        $id_next_room = $next_prev_room[0];
        $id_prev_room = $next_prev_room[1];
        $btn_nav_rooms = '<a title="'._("EDIT PREVIOUS ROOM").'" href="index.php?p=edit_room&id='.$id_prev_room.'" class="btn btn-sm tooltip_arrows btn-primary btn-icon-split '.(($id_room==$id_prev_room) ? 'disabled':'').'">
        <span class="icon text-white-50">
          <i class="fas fa-angle-left"></i>
        </span>
        </a>
        <a title="'._("EDIT NEXT ROOM").'" href="index.php?p=edit_room&id='.$id_next_room.'" class="btn btn-sm tooltip_arrows btn-primary btn-icon-split '.(($id_room==$id_next_room) ? 'disabled':'').'">
        <span class="icon text-white-50">
          <i class="fas fa-angle-right"></i>
        </span>
        </a>';
        if($user_info['role']=="editor") {
            $editor_permissions = get_editor_permissions($_SESSION['id_user'],$_SESSION['id_virtualtour_sel']);
            if($editor_permissions['delete_rooms']==1) {
                $delete_permission = true;
            } else {
                $delete_permission = false;
            }
        } else {
            $delete_permission = true;
        }
        $sub_header = '<div class="vt_select_header"><div class="justify-content-end">'.$btn_nav_rooms.'<a style="margin-left: 5px" id="save_btn" href="#" onclick="save_room(null,0);return false;" class="btn btn-sm btn-success btn-icon-split '.(($demo) ? 'disabled':'').'"><span class="icon text-white-50"><i class="far fa-circle"></i></span><span class="text">'._("SAVE").'</span></a>&nbsp;&nbsp;&nbsp;<button data-toggle="modal" data-target="#modal_delete_room" type="button" class="btn btn-sm btn-danger '.(($delete_permission) ? '' : 'd-none').'">'._("DELETE").'</button></div></div>';
        $menu_title = '<i class="fas fa-fw fa-vector-square text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Edit Room").'</span> <i id="subtitle_header" class="text-gray-700">.........</i>';
        break;
    case 'rooms':
    case 'rooms_bulk':
        $sub_header = print_virtualtour_selector('rooms');
        $menu_title = '<i class="fas fa-fw fa-vector-square text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Rooms").'</span>';
        break;
    case 'edit_blur':
        $menu_title = '<i class="fas fa-fw fa-fire-extinguisher text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Edit Blur").'</span>';
        break;
    case 'markers':
        $sub_header = print_virtualtour_selector('markers');
        $menu_title = '<i class="fas fa-fw fa-caret-square-up text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Markers").'</span> <i id="subtitle_header" class="text-gray-700">.........</i>';
        break;
    case 'measurements':
        $sub_header = print_virtualtour_selector('measures');
        $menu_title = '<i class="fas fa-fw fa-ruler-combined text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Measurements").'</span> <i id="subtitle_header" class="text-gray-700">.........</i>';
        break;
    case 'pois':
        $sub_header = print_virtualtour_selector('pois');
        $menu_title = '<i class="fas fa-fw fa-bullseye text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("POIs").'</span> <i id="subtitle_header" class="text-gray-700">.........</i>';
        break;
    case 'maps':
    case 'maps_bulk':
        $sub_header = print_virtualtour_selector('maps');
        $menu_title = '<i class="fas fa-fw fa-map-marked-alt text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Maps").'</span>';
        break;
    case 'edit_map':
        $sub_header = '<div class="vt_select_header"><a id="save_btn" href="#" onclick="save_map_settings(false);return false;" class="btn btn-sm btn-success btn-icon-split '.(($demo) ? 'disabled':'').'"><span class="icon text-white-50"><i class="far fa-circle"></i></span><span class="text">'._("SAVE").'</span></a></div>';
        $menu_title = '<i class="fas fa-fw fa-map-marked-alt text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Edit Map").'</span>';
        break;
    case 'products':
        $sub_header = print_virtualtour_selector('products');
        $menu_title = '<i class="fas fa-fw fa-shopping-cart text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Products").'</span>';
        break;
    case 'edit_product':
        $id_product = $_GET['id'];
        $sub_header = '<div class="vt_select_header"><div><a id="save_btn" href="#" onclick="save_product('.$id_product.');return false;" class="btn btn-sm btn-success btn-icon-split '.(($demo) ? 'disabled':'').'"><span class="icon text-white-50"><i class="far fa-circle"></i></span><span class="text">'._("SAVE").'</span></a><button '.(($demo) ? 'disabled':'').' onclick="modal_delete_product('.$id_product.');" class="btn btn-sm btn-danger ml-2">'._("DELETE").'</button></div></div>';
        $menu_title = '<i class="fas fa-fw fa-shopping-cart text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Edit Product").'</span>';
        break;
    case 'info':
        $sub_header = print_virtualtour_selector('info_box');
        $virtualtour = get_virtual_tour($_SESSION['id_virtualtour_sel'],$_SESSION['id_user']);
        $show_in_ui = $virtualtour['show_info'];
        $show_ui_icon = '<i style="font-size:10px;vertical-align:middle;color:'.(($show_in_ui>0)?'green':'orange').'" '.(($show_in_ui==0)?'title="'._("Not visible in the tour, enable it in the Editor UI").'"':'').' class="'.(($show_in_ui==0)?'help_t':'').' fas fa-circle"></i>';
        $menu_title = '<i class="fas fa-fw fa-info-circle text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Info Box").'</span> '.$show_ui_icon;
        break;
    case 'gallery':
        $sub_header = print_virtualtour_selector('gallery');
        $virtualtour = get_virtual_tour($_SESSION['id_virtualtour_sel'],$_SESSION['id_user']);
        $show_in_ui = $virtualtour['show_gallery'];
        $show_ui_icon = '<i style="font-size:10px;vertical-align:middle;color:'.(($show_in_ui>0)?'green':'orange').'" '.(($show_in_ui==0)?'title="'._("Not visible in the tour, enable it in the Editor UI").'"':'').' class="'.(($show_in_ui==0)?'help_t':'').' fas fa-circle"></i>';
        $menu_title = '<i class="fas fa-fw fa-images text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Gallery").'</span> '.$show_ui_icon;
        break;
    case 'icons_library':
        $sub_header = print_virtualtour_selector('icons_library');
        $menu_title = '<i class="fas fa-fw fa-icons text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Icons Library").'</span>';
        break;
    case 'media_library':
        $sub_header = print_virtualtour_selector('media_library');
        $menu_title = '<i class="fas fa-fw fa-photo-video text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Media Library").'</span>';
        break;
    case 'music_library':
        $sub_header = print_virtualtour_selector('music_library');
        $menu_title = '<i class="fas fa-fw fa-music text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Music Library").'</span>';
        break;
    case 'sound_library':
        $sub_header = print_virtualtour_selector('sound_library');
        $menu_title = '<i class="fas fa-fw fa-volume-up text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Sound Library").'</span>';
        break;
    case 'presentation':
        $sub_header = print_virtualtour_selector('no');
        $virtualtour = get_virtual_tour($_SESSION['id_virtualtour_sel'],$_SESSION['id_user']);
        $show_in_ui = $virtualtour['show_presentation'];
        $show_ui_icon = '<i style="font-size:10px;vertical-align:middle;color:'.(($show_in_ui>0)?'green':'orange').'" '.(($show_in_ui==0)?'title="'._("Not visible in the tour, enable it in the Editor UI").'"':'').' class="'.(($show_in_ui==0)?'help_t':'').' fas fa-circle"></i>';
        $menu_title = '<i class="fas fa-fw fa-directions text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Presentation").'</span> '.$show_ui_icon;
        break;
    case 'video360':
        $sub_header = print_virtualtour_selector('video_360');
        $menu_title = '<i class="fas fa-fw fa-video text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("360 Video Tour").'</span>';
        break;
    case 'forms_data':
        $sub_header = print_virtualtour_selector('forms');
        $menu_title = '<i class="fas fa-fw fa-database text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Forms").'</span>';
        break;
    case 'leads':
        $sub_header = print_virtualtour_selector('leads');
        $menu_title = '<i class="fas fa-fw fa-user-tag text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Leads").'</span>';
        break;
    case 'statistics':
        $sub_header = print_virtualtour_selector('no');
        $menu_title = '<i class="fas fa-fw fa-route text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Statistics").' - '._("Tour").'</span>';
        break;
    case 'statistics_all':
        $menu_title = '<i class="fas fa-fw fa-chart-area text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Statistics").' - '._("Overall").'</span>';
        break;
    case 'statistics_learning':
        $sub_header = print_virtualtour_selector('no');
        $menu_title = '<i class="fas fa-fw fa-graduation-cap text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Statistics").' - '._("Learning").'</span>';
        break;
    case 'landing':
        $sub_header = print_virtualtour_selector('landing');
        $menu_title = '<i class="fas fa-fw fa-file-alt text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Publish").' - '._("Landing").'</span>';
        break;
    case 'showcases':
        $menu_title = '<i class="fas fa-fw fa-object-group text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Publish").' - '._("Showcases").'</span>';
        break;
    case 'edit_showcase':
        $id_showcase = $_GET['id'];
        $sub_header = '<div class="vt_select_header"><div><a id="save_btn" href="#" onclick="save_showcase('.$id_showcase.',false);return false;" class="btn btn-sm btn-success btn-icon-split '.(($demo) ? 'disabled':'').'"><span class="icon text-white-50"><i class="far fa-circle"></i></span><span class="text">'._("SAVE").'</span></a><button '.(($demo) ? 'disabled':'').' onclick="modal_delete_showcase('.$id_showcase.');" class="btn btn-sm btn-danger ml-2">'._("DELETE").'</button></div></div>';
        $menu_title = '<i class="fas fa-fw fa-object-group text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Edit Showcase").'</span>';
        break;
    case 'globes':
        $menu_title = '<i class="fas fa-fw fa-globe-americas text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Publish").' - '._("Globes").'</span>';
        break;
    case 'edit_globe':
        $id_globe = $_GET['id'];
        $sub_header = '<div class="vt_select_header"><div><a id="save_btn" href="#" onclick="save_globe('.$id_globe.',false);return false;" class="btn btn-sm btn-success btn-icon-split '.(($demo) ? 'disabled':'').'"><span class="icon text-white-50"><i class="far fa-circle"></i></span><span class="text">'._("SAVE").'</span></a><button '.(($demo) ? 'disabled':'').' onclick="modal_delete_globe('.$id_globe.');" class="btn btn-sm btn-danger ml-2">'._("DELETE").'</button></div></div>';
        $menu_title = '<i class="fas fa-fw fa-globe-americas text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Edit Globe").'</span>';
        break;
    case 'preview':
        $sub_header = print_virtualtour_selector('preview');
        $menu_title = '<i class="fas fa-fw fa-eye text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Preview").'</span>';
        break;
    case 'publish':
        $sub_header = print_virtualtour_selector('no');
        $menu_title = '<i class="fas fa-fw fa-route text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Publish").' - '._("Tour"). '</span>';
        break;
    case 'settings':
        $sub_header = '<div class="vt_select_header"><a id="save_btn" href="#" onclick="save_settings(false);return false;" class="btn btn-sm btn-success btn-icon-split '.(($demo) ? 'disabled':'').'"><span class="icon text-white-50"><i class="far fa-circle"></i></span><span class="text">'._("SAVE").'</span></a></div>';
        $menu_title = '<i class="fas fa-fw fa-cogs text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Settings").'</span>';
        break;
    case 'updater':
        $menu_title = '<i class="fas fa-fw fa-download text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Upgrade").'</span>';
        break;
    case 'change_plan':
        $menu_title = '<i class="fas fa-fw fa-credit-card text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Subscription").'</span>';
        break;
    case 'users':
        $menu_title = '<i class="fas fa-fw fa-users text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Users").'</span>';
        break;
    case "edit_user":
        $id_user_edit = $_GET['id'];
        $btn_del_user = '';
        if($_SESSION['id_user']!=$id_user_edit) {
            $id_user_crypt = xor_obfuscator($id_user_edit);
            $btn_del_user = '<button '.(($demo) ? 'disabled':'').' onclick="modal_delete_user(\''.$id_user_crypt.'\');" class="btn btn-sm btn-danger ml-2">'._("DELETE").'</button>';
        }
        $next_prev_user = get_next_prev_user($id_user_edit);
        $id_next_user = $next_prev_user[0];
        $id_prev_user = $next_prev_user[1];
        $btn_nav_users = '<a title="'._("EDIT PREVIOUS USER").'" href="index.php?p=edit_user&id='.$id_prev_user.'" class="btn btn-sm tooltip_arrows btn-primary btn-icon-split '.(($id_next_user==$id_prev_user) ? 'disabled':'').'">
        <span class="icon text-white-50">
          <i class="fas fa-angle-left"></i>
        </span>
        </a>
        <a title="'._("EDIT NEXT USER").'" href="index.php?p=edit_user&id='.$id_next_user.'" class="btn btn-sm tooltip_arrows btn-primary btn-icon-split '.(($id_next_user==$id_prev_user) ? 'disabled':'').'">
        <span class="icon text-white-50">
          <i class="fas fa-angle-right"></i>
        </span>
        </a>';
        $sub_header = '<div class="vt_select_header">'.$btn_nav_users.'<a style="margin-left: 5px" id="save_btn" href="#" onclick="save_user('.$id_user_edit.');return false;" class="btn btn-sm btn-success btn-icon-split '.(($demo) ? 'disabled':'').'"><span class="icon text-white-50"><i class="far fa-circle"></i></span><span class="text">'._("SAVE").'</span></a>'.$btn_del_user.'</div>';
        $menu_title = '<i class="fas fa-fw fa-users text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Edit User").'</span>';
        break;
    case 'plans':
        $menu_title = '<i class="fas fa-fw fa-crown text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Plans").'</span>';
        break;
    case 'services':
        $menu_title = '<i class="fas fa-fw fa-hand-holding-dollar text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Services").'</span>';
        break;
    case 'buy_service':
        $menu_title = '<i class="fas fa-fw fa-hand-holding-dollar text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Services").'</span>';
        break;
    case 'advertisements':
        $menu_title = '<i class="fas fa-fw fa-ad text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Advertisements").'</span>';
        break;
    case 'edit_advertisement':
        $id_advertisement = $_GET['id'];
        $sub_header = '<div class="vt_select_header"><div><a id="save_btn" href="#" onclick="save_advertisement('.$id_advertisement.');return false;" class="btn btn-sm btn-success btn-icon-split '.(($demo) ? 'disabled':'').'"><span class="icon text-white-50"><i class="far fa-circle"></i></span><span class="text">'._("SAVE").'</span></a><button '.(($demo) ? 'disabled':'').' onclick="modal_delete_advertisement('.$id_advertisement.');" class="btn btn-sm btn-danger ml-2">'._("DELETE").'</button></div></div>';
        $menu_title = '<i class="fas fa-fw fa-ad text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Edit Advertisement").'</span>';
        break;
    case 'edit_profile':
        $id_user_edit = $_SESSION['id_user'];
        $to_complete = check_profile_to_complete($id_user_edit);
        if(!$to_complete) {
            $sub_header = '<div class="vt_select_header"><a id="save_btn" href="#" onclick="save_profile(false);return false;" class="btn btn-sm btn-success btn-icon-split '.(($demo) ? 'disabled':'').'"><span class="icon text-white-50"><i class="far fa-circle"></i></span><span class="text">'._("SAVE").'</span></a></div>';
        }
        $menu_title = '<i class="fas fa-fw fa-user text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Profile").'</span>';
        break;
    case 'video':
        $sub_header = print_virtualtour_selector('video_projects');
        $menu_title = '<i class="fas fa-fw fa-film text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Video Projects").'</span>';
        break;
    case 'edit_video':
        $id_video = $_GET['id'];
        $sub_header = '<div class="vt_select_header"><div><a id="save_btn" href="#" onclick="save_video('.$id_video.',false);return false;" class="btn btn-sm btn-success btn-icon-split '.(($demo) ? 'disabled':'').'"><span class="icon text-white-50"><i class="far fa-circle"></i></span><span class="text">'._("SAVE").'</span></a><button '.(($demo) ? 'disabled':'').' onclick="modal_delete_video_project('.$id_video.');" class="btn btn-sm btn-danger ml-2">'._("DELETE").'</button></div></div>';
        $menu_title = '<i class="fas fa-fw fa-film text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Edit Video Project").'</span>';
        break;
    case 'features':
        $sub_header = '<div class="vt_select_header"><a id="save_btn" href="#" onclick="save_features();return false;" class="btn btn-sm btn-success btn-icon-split '.(($demo) ? 'disabled':'').'"><span class="icon text-white-50"><i class="far fa-circle"></i></span><span class="text">'._("SAVE").'</span></a></div>';
        $menu_title = '<i class="fas fa-fw fa-tasks text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Features").'</span>';
        break;
    case 'custom1':
        $settings = get_settings();
        $extra_menu_items = $settings['extra_menu_items'];
        if(!empty($extra_menu_items)) {
            $extra_menu_items = json_decode($extra_menu_items, true);
        }
        $menu_title = '<i class="fa-fw '.$extra_menu_items[0]['icon'].' text-gray-700"></i> <span class="mb-0 text-gray-800">'.$extra_menu_items[0]['name'].'</span>';
        break;
    case 'custom2':
        $settings = get_settings();
        $extra_menu_items = $settings['extra_menu_items'];
        if(!empty($extra_menu_items)) {
            $extra_menu_items = json_decode($extra_menu_items, true);
        }
        $menu_title = '<i class="fa-fw '.$extra_menu_items[1]['icon'].' text-gray-700"></i> <span class="mb-0 text-gray-800">'.$extra_menu_items[1]['name'].'</span>';
        break;
    case 'custom3':
        $settings = get_settings();
        $extra_menu_items = $settings['extra_menu_items'];
        if(!empty($extra_menu_items)) {
            $extra_menu_items = json_decode($extra_menu_items, true);
        }
        $menu_title = '<i class="fa-fw '.$extra_menu_items[2]['icon'].' text-gray-700"></i> <span class="mb-0 text-gray-800">'.$extra_menu_items[2]['name'].'</span>';
        break;
    case 'custom4':
        $settings = get_settings();
        $extra_menu_items = $settings['extra_menu_items'];
        if(!empty($extra_menu_items)) {
            $extra_menu_items = json_decode($extra_menu_items, true);
        }
        $menu_title = '<i class="fa-fw '.$extra_menu_items[3]['icon'].' text-gray-700"></i> <span class="mb-0 text-gray-800">'.$extra_menu_items[3]['name'].'</span>';
        break;
    case 'custom5':
        $settings = get_settings();
        $extra_menu_items = $settings['extra_menu_items'];
        if(!empty($extra_menu_items)) {
            $extra_menu_items = json_decode($extra_menu_items, true);
        }
        $menu_title = '<i class="fa-fw '.$extra_menu_items[4]['icon'].' text-gray-700"></i> <span class="mb-0 text-gray-800">'.$extra_menu_items[4]['name'].'</span>';
        break;
    case 'bulk_translate':
        $sub_header = print_virtualtour_selector('bulk_translate');
        $menu_title = '<i class="fas fa-fw fa-language text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Bulk Translate").'</span>';
        break;
    case 'gsv':
        $menu_title = '<i class="fas fa-fw fa-street-view text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Publish").' - '._("Google Street View"). '</span> <i id="subtitle_header" class="text-gray-700">.........</i>';
        break;
    case 'custom_domain':
        $menu_title = '<i class="fas fa-fw fa-sitemap text-gray-700"></i> <span class="mb-0 text-gray-800">'. _("Custom Domains").'</span>';
        break;
}
?>

<nav class="navbar navbar-expand navbar-light bg-white topbar static-top <?php echo (empty($sub_header) ? 'shadow' : '') ?>">
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-0">
        <i class="fa fa-bars"></i>
    </button>
    <div class="header_menu_title noselect ml-1"><?php echo $menu_title; ?></div>
    <ul class="navbar-nav ml-auto">
        <?php if($user_info['role']!='editor') : ?>
            <?php if($user_info['id_plan']!=0) : ?>
                <li class="nav-item dropdown no-arrow">
                    <a class="nav-link dropdown-toggle" href="#" id="planDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span class="nav-link text-gray-600 small"><span class="px-2 py-1" style="margin-top:1px;border:1px solid #c4c4c4;border-radius:20px"><?php echo $icon_plan; ?>&nbsp;&nbsp;<?php echo $user_info['plan']; ?></span></span>
                    </a>
                    <div style="cursor: default;" class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="planDropdown">
                        <div style="pointer-events:none;" class="dropdown-item">
                            <?php echo _("Status"); ?>:&nbsp;<?php
                            switch($user_info['plan_status']) {
                                case 'active':
                                    echo "<span style='color:green'><b>"._("Active")."</b></span>";
                                    break;
                                case 'expiring':
                                    $expires_msg = "";
                                    if($user_info['expire_plan_date']) {
                                        $diff_days = dateDiffInDays(date('Y-m-d',strtotime($user_info['expire_plan_date'])),date('Y-m-d',strtotime('today')));
                                        $expires_msg = sprintf(_("- expires in %s days"),abs($diff_days));
                                    }
                                    echo "<span style='color:darkorange'><b>"._("Active")."</b></span> $expires_msg";
                                    break;
                                case 'expired':
                                    echo "<span style='color:red'><b>"._("Expired")."</b></span>";
                                    break;
                                case 'invalid_payment':
                                    echo "<span style='color:red'><b>"._("Invalid payment")."</b></span>";
                                    break;
                            }
                            ?>
                        </div>
                        <div style="pointer-events:none;" class="dropdown-item">
                            <?php
                            if($plan_info['n_virtual_tours']>0) {
                                $perc_tours = number_format(calculatePercentage($user_stats['count_virtual_tours'],$plan_info['n_virtual_tours']));
                                if($perc_tours>=(($plan_info['n_virtual_tours']==2) ? 50 : 75) && $perc_tours<100) {
                                    $perc_tours_bg = "warning";
                                } else if($perc_tours>=100) {
                                    $perc_tours = 100;
                                    $perc_tours_bg = "danger";
                                } else {
                                    $perc_tours_bg = "success";
                                }
                            } else {
                                $perc_tours = 100;
                                $perc_tours_bg = "success";
                            }
                            if($plan_info['n_virtual_tours_month']>0) {
                                $perc_tours_m = number_format(calculatePercentage($user_stats['count_virtual_tours_month'],$plan_info['n_virtual_tours_month']));
                                if($perc_tours_m>=(($plan_info['n_virtual_tours_month']==2) ? 50 : 75) && $perc_tours_m<100) {
                                    $perc_tours_bg_m = "warning";
                                } else if($perc_tours_m>=100) {
                                    $perc_tours_m = 100;
                                    $perc_tours_bg_m = "danger";
                                } else {
                                    $perc_tours_bg_m = "success";
                                }
                            } else {
                                $perc_tours_m = 100;
                                $perc_tours_bg_m = "success";
                            }
                            if($plan_info['n_rooms']>0) {
                                $perc_rooms = number_format(calculatePercentage($user_stats['count_rooms'],$plan_info['n_rooms']));
                                if($perc_rooms>=(($plan_info['n_rooms']==2) ? 50 : 75) && $perc_rooms<100) {
                                    $perc_rooms_bg = "warning";
                                } else if($perc_rooms>=100) {
                                    $perc_rooms = 100;
                                    $perc_rooms_bg = "danger";
                                } else {
                                    $perc_rooms_bg = "success";
                                }
                            } else {
                                $perc_rooms = 100;
                                $perc_rooms_bg = "success";
                            }
                            if($plan_info['n_markers']>0) {
                                $perc_markers = number_format(calculatePercentage($user_stats['count_markers'],$plan_info['n_markers']));
                                if($perc_markers>=(($plan_info['n_markers']==2) ? 50 : 75) && $perc_markers<100) {
                                    $perc_markers_bg = "warning";
                                } else if($perc_markers>=100) {
                                    $perc_markers = 100;
                                    $perc_markers_bg = "danger";
                                } else {
                                    $perc_markers_bg = "success";
                                }
                            } else {
                                $perc_markers = 100;
                                $perc_markers_bg = "success";
                            }
                            if($plan_info['n_pois']>0) {
                                $perc_pois = number_format(calculatePercentage($user_stats['count_pois'],$plan_info['n_pois']));
                                if($perc_pois>=(($plan_info['n_pois']==2) ? 50 : 75) && $perc_pois<100) {
                                    $perc_pois_bg = "warning";
                                } else if($perc_pois>=100) {
                                    $perc_pois = 100;
                                    $perc_pois_bg = "danger";
                                } else {
                                    $perc_pois_bg = "success";
                                }
                            } else {
                                $perc_pois = 100;
                                $perc_pois_bg = "success";
                            }
                            if($plan_info['n_custom_domain']>0) {
                                $perc_custom_domain = number_format(calculatePercentage($user_stats['count_custom_domain'],$plan_info['n_custom_domain']));
                                if($perc_custom_domain>=(($plan_info['n_custom_domain']==2) ? 50 : 75) && $perc_custom_domain<100) {
                                    $perc_custom_domain_bg = "warning";
                                } else if($perc_custom_domain>=100) {
                                    $perc_custom_domain = 100;
                                    $perc_custom_domain_bg = "danger";
                                } else {
                                    $perc_custom_domain_bg = "success";
                                }
                            } else {
                                $perc_custom_domain = 100;
                                $perc_custom_domain_bg = "success";
                            }
                            if($plan_info['max_storage_space']>0) {
                                $perc_size = number_format(calculatePercentage($user_info['storage_space'],$plan_info['max_storage_space']));
                                if($perc_size>=75 && $perc_size<100) {
                                    $perc_size_bg = "warning";
                                } else if($perc_size>=100) {
                                    $perc_size = 100;
                                    $perc_size_bg = "danger";
                                } else {
                                    $perc_size_bg = "success";
                                }
                                if($user_info['storage_space']>=1000) {
                                    $actual_storage = ($user_info['storage_space']/1000)." GB";
                                } else {
                                    $actual_storage = $user_info['storage_space']." MB";
                                }
                                if($plan_info['max_storage_space']>=1000) {
                                    $max_storage = ($plan_info['max_storage_space']/1000)." GB";
                                } else {
                                    $max_storage = $plan_info['max_storage_space']." MB";
                                }
                            }
                            if($enable_ai_room && $plan_info['enable_ai_room']) {
                                switch ($plan_info['ai_generate_mode']) {
                                    case 'month':
                                        $n_ai_generate_month = $plan_info['n_ai_generate_month'];
                                        $ai_generated = get_user_ai_generated($user_info['id'],$plan_info['ai_generate_mode']);
                                        if($n_ai_generate_month!=-1) {
                                            $perc_ai = number_format(calculatePercentage($ai_generated,$n_ai_generate_month));
                                            if($perc_ai>=75 && $perc_ai<100) {
                                                $perc_ai_bg = "warning";
                                            } else if($perc_ai>=100) {
                                                $perc_ai = 100;
                                                $perc_ai_bg = "danger";
                                            } else {
                                                $perc_ai_bg = "success";
                                            }
                                        } else {
                                            $perc_ai = 100;
                                            $perc_ai_bg = "success";
                                        }
                                        break;
                                    case 'credit':
                                        $ai_credits = $user_info['ai_credits'];
                                        $ai_generated = get_user_ai_generated($user_info['id'],$plan_info['ai_generate_mode']);
                                        if($ai_credits>0) {
                                            $perc_ai = number_format(calculatePercentage($ai_generated,$ai_credits));
                                            if($perc_ai>=75 && $perc_ai<100) {
                                                $perc_ai_bg = "warning";
                                            } else if($perc_ai>=100) {
                                                $perc_ai = 100;
                                                $perc_ai_bg = "danger";
                                            } else {
                                                $perc_ai_bg = "success";
                                            }
                                        } else {
                                            $perc_ai = 0;
                                            $perc_ai_bg = "";
                                        }
                                        break;
                                }
                            }
                            if($enable_autoenhance_room && $plan_info['enable_autoenhance_room']) {
                                switch ($plan_info['autoenhance_generate_mode']) {
                                    case 'month':
                                        $n_autoenhance_generate_month = $plan_info['n_autoenhance_generate_month'];
                                        $autoenhance_generated = get_user_autoenhance_generated($user_info['id'],$plan_info['autoenhance_generate_mode']);
                                        if($n_autoenhance_generate_month!=-1) {
                                            $perc_autoenhance = number_format(calculatePercentage($autoenhance_generated,$n_autoenhance_generate_month));
                                            if($perc_autoenhance>=75 && $perc_autoenhance<100) {
                                                $perc_autoenhance_bg = "warning";
                                            } else if($perc_autoenhance>=100) {
                                                $perc_autoenhance = 100;
                                                $perc_autoenhance_bg = "danger";
                                            } else {
                                                $perc_autoenhance_bg = "success";
                                            }
                                        } else {
                                            $perc_autoenhance = 100;
                                            $perc_autoenhance_bg = "success";
                                        }
                                        break;
                                    case 'credit':
                                        $autoenhance_credits = $user_info['autoenhance_credits'];
                                        $autoenhance_generated = get_user_autoenhance_generated($user_info['id'],$plan_info['autoenhance_generate_mode']);
                                        if($autoenhance_credits>0) {
                                            $perc_autoenhance = number_format(calculatePercentage($autoenhance_generated,$autoenhance_credits));
                                            if($perc_autoenhance>=75 && $perc_autoenhance<100) {
                                                $perc_autoenhance_bg = "warning";
                                            } else if($perc_autoenhance>=100) {
                                                $perc_autoenhance = 100;
                                                $perc_autoenhance_bg = "danger";
                                            } else {
                                                $perc_autoenhance_bg = "success";
                                            }
                                        } else {
                                            $perc_autoenhance = 0;
                                            $perc_autoenhance_bg = "";
                                        }
                                        break;
                                }
                            }
                            if($settings['buy_services']) {
                                $services_credits = $user_info['services_credits'];
                                $services_used = get_user_service_used($user_info['id']);
                                if($services_used>0) {
                                    $perc_services_used = number_format(calculatePercentage($services_used,$services_credits));
                                    if($perc_services_used>=75 && $perc_services_used<100) {
                                        $perc_services_used_bg = "warning";
                                    } else if($perc_services_used>=100) {
                                        $perc_services_used = 100;
                                        $perc_services_used_bg = "danger";
                                    } else {
                                        $perc_services_used_bg = "success";
                                    }
                                } else {
                                    $perc_services_used = 0;
                                    $perc_services_used_bg = "";
                                }
                            }
                            if($gsv_installed && $plan_info['enable_gsv_publish']) {
                                switch ($plan_info['gsv_publish_mode']) {
                                    case 'month':
                                        $n_gsv_publish_month = $plan_info['n_gsv_publish_month'];
                                        $gsv_published = get_user_gsv_published($user_info['id'],$plan_info['gsv_publish_mode']);
                                        if($n_gsv_publish_month!=-1) {
                                            $perc_gsv_publish = number_format(calculatePercentage($gsv_published,$n_gsv_publish_month));
                                            if($perc_gsv_publish>=75 && $perc_gsv_publish<100) {
                                                $perc_gsv_publish_bg = "warning";
                                            } else if($perc_gsv_publish>=100) {
                                                $perc_gsv_publish = 100;
                                                $perc_gsv_publish_bg = "danger";
                                            } else {
                                                $perc_gsv_publish_bg = "success";
                                            }
                                        } else {
                                            $perc_gsv_publish = 100;
                                            $perc_gsv_publish_bg = "success";
                                        }
                                        break;
                                    case 'credit':
                                        $gsv_publish_credits = $user_info['gsv_publish_credits'];
                                        $gsv_published = get_user_gsv_published($user_info['id'],$plan_info['gsv_publish_mode']);
                                        if($gsv_publish_credits>0) {
                                            $perc_gsv_publish = number_format(calculatePercentage($gsv_published,$gsv_publish_credits));
                                            if($perc_gsv_publish>=75 && $perc_gsv_publish<100) {
                                                $perc_gsv_publish_bg = "warning";
                                            } else if($perc_gsv_publish>=100) {
                                                $perc_gsv_publish = 100;
                                                $perc_gsv_publish_bg = "danger";
                                            } else {
                                                $perc_gsv_publish_bg = "success";
                                            }
                                        } else {
                                            $perc_gsv_publish = 0;
                                            $perc_gsv_publish_bg = "";
                                        }
                                        break;
                                }
                            }
                            ?>
                            <div id="progress_plan_vt" class="progress mb-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                                <div style="width:<?php echo $perc_tours; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_tours_bg; ?>" role="progressbar" aria-valuenow="<?php echo $perc_tours; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                <div style="padding: 0 10px" class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("Virtual Tours"); ?>: <?php echo $user_stats['count_virtual_tours']." "._("of")."&nbsp;".(($plan_info['n_virtual_tours']<0) ? '<i style="vertical-align: middle;margin-top: 2px;" class="fas fa-infinity"></i>' : '<b>'.$plan_info['n_virtual_tours']).'</b>'; ?></div>
                            </div>
                            <?php if($plan_info['n_virtual_tours_month']>0) : ?>
                                <div id="progress_plan_vt_m" class="progress mb-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                                    <div style="width:<?php echo $perc_tours_m; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_tours_bg_m; ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div style="padding: 0 10px" class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("Virtual Tours"); ?>: <?php echo $user_stats['count_virtual_tours_month']." "._("of")."&nbsp;".(($plan_info['n_virtual_tours_month']<0) ? '<i style="vertical-align: middle;margin-top: 2px;" class="fas fa-infinity"></i>' : '<b>'.$plan_info['n_virtual_tours_month']).'</b>&nbsp;&nbsp;'."("._("monthly").")"; ?></div>
                                </div>
                            <?php endif; ?>
                            <div id="progress_plan_room" class="progress mb-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                                <div style="width:<?php echo $perc_rooms; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_rooms_bg; ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                <div style="padding: 0 10px" class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("Rooms"); ?>: <?php echo $user_stats['count_rooms']." "._("of")."&nbsp;".(($plan_info['n_rooms']<0) ? '<i style="vertical-align: middle;margin-top: 2px;" class="fas fa-infinity"></i>' : '<b>'.$plan_info['n_rooms']).'</b>'; ?></div>
                            </div>
                            <div id="progress_plan_marker" class="progress mb-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                                <div style="width:<?php echo $perc_markers; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_markers_bg; ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                <div style="padding: 0 10px" class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("Markers"); ?>: <?php echo $user_stats['count_markers']." "._("of")."&nbsp;".(($plan_info['n_markers']<0) ? '<i style="vertical-align: middle;margin-top: 2px;" class="fas fa-infinity"></i>' : '<b>'.$plan_info['n_markers']).'</b>'; ?></div>
                            </div>
                            <div id="progress_plan_poi" class="progress mb-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                                <div style="width:<?php echo $perc_pois; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_pois_bg; ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                <div style="padding: 0 10px" class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("POIs"); ?>: <?php echo $user_stats['count_pois']." "._("of")."&nbsp;".(($plan_info['n_pois']<0) ? '<i style="vertical-align: middle;margin-top: 2px;" class="fas fa-infinity"></i>' : '<b>'.$plan_info['n_pois']).'</b>'; ?></div>
                            </div>
                            <?php if($settings['enable_custom_domain'] && $plan_info['enable_custom_domain']) : ?>
                                <div id="progress_plan_custom_domain" class="progress mb-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                                    <div style="width:<?php echo $perc_custom_domain; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_custom_domain_bg; ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div style="padding: 0 10px" class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("Custom Domains"); ?>: <?php echo $user_stats['count_custom_domain']." "._("of")."&nbsp;".(($plan_info['n_custom_domain']<0) ? '<i style="vertical-align: middle;margin-top: 2px;" class="fas fa-infinity"></i>' : '<b>'.$plan_info['n_custom_domain']).'</b>'; ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if($plan_info['max_storage_space']>0) : ?>
                                <div id="progress_plan_size" class="progress mb-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                                    <div style="width:<?php echo $perc_size; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_size_bg; ?>" role="progressbar" aria-valuenow="<?php echo $perc_size; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div style="padding: 0 10px" class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("Storage Quota"); ?>: <?php echo $actual_storage."&nbsp;/&nbsp;".'<b>'.$max_storage.'</b>'; ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if($enable_ai_room && $plan_info['enable_ai_room']) : ?>
                                <?php switch($plan_info['ai_generate_mode']) {
                                    case 'month': ?>
                                        <div id="progress_plan_ai" class="progress mb-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                                            <div style="width:<?php echo $perc_ai; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_ai_bg; ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                            <div style="padding: 0 10px" class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("A.I. Panorama"); ?>: <?php echo $ai_generated." "._("of")."&nbsp;".(($n_ai_generate_month<0) ? '<i style="vertical-align: middle;margin-top: 2px;" class="fas fa-infinity"></i>' : '<b>'.$n_ai_generate_month).'</b>&nbsp;&nbsp;'."("._("monthly").")"; ?></div>
                                        </div>
                                        <?php break;
                                    case 'credit': ?>
                                        <div id="progress_plan_ai" class="progress mb-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                                            <div style="width:<?php echo $perc_ai; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_ai_bg; ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                            <div style="padding: 0 10px" class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("A.I. Panorama"); ?>: <?php echo $ai_generated." "._("of")."&nbsp;".('<b>'.$ai_credits.'</b>'); ?></div>
                                        </div>
                                        <?php break;
                                } ?>
                            <?php endif; ?>
                            <?php if($enable_autoenhance_room && $plan_info['enable_autoenhance_room']) : ?>
                                <?php switch($plan_info['autoenhance_generate_mode']) {
                                    case 'month': ?>
                                        <div id="progress_plan_autoenhance" class="progress mb-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                                            <div style="width:<?php echo $perc_autoenhance; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_autoenhance_bg; ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                            <div style="padding: 0 10px" class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("A.I. Enhancement"); ?>: <?php echo $autoenhance_generated." "._("of")."&nbsp;".(($n_autoenhance_generate_month<0) ? '<i style="vertical-align: middle;margin-top: 2px;" class="fas fa-infinity"></i>' : '<b>'.$n_autoenhance_generate_month).'</b>&nbsp;&nbsp;'."("._("monthly").")"; ?></div>
                                        </div>
                                        <?php break;
                                    case 'credit': ?>
                                        <div id="progress_plan_autoenhance" class="progress mb-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                                            <div style="width:<?php echo $perc_autoenhance; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_autoenhance_bg; ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                            <div style="padding: 0 10px" class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("A.I. Enhancement"); ?>: <?php echo $autoenhance_generated." "._("of")."&nbsp;".('<b>'.$autoenhance_credits.'</b>'); ?></div>
                                        </div>
                                        <?php break;
                                } ?>
                            <?php endif; ?>
                            <?php if($settings['buy_services']) : ?>
                                <div id="progress_plan_services_used" class="progress mb-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                                    <div style="width:<?php echo $perc_services_used; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_services_used_bg; ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                    <div style="padding: 0 10px" class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("Services credits"); ?>: <?php echo $services_used." "._("of")."&nbsp;".('<b>'.$services_credits.'</b>'); ?></div>
                                </div>
                            <?php endif; ?>
                            <?php if($gsv_installed && $plan_info['enable_gsv_publish']) : ?>
                                <?php switch($plan_info['gsv_publish_mode']) {
                                    case 'month': ?>
                                        <div id="progress_plan_gsv_publish" class="progress mb-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                                            <div style="width:<?php echo $perc_gsv_publish; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_gsv_publish_bg; ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                            <div style="padding: 0 10px" class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("GSV Publish"); ?>: <?php echo $gsv_published." "._("of")."&nbsp;".(($n_gsv_publish_month<0) ? '<i style="vertical-align: middle;margin-top: 2px;" class="fas fa-infinity"></i>' : '<b>'.$n_gsv_publish_month).'</b>&nbsp;&nbsp;'."("._("monthly").")"; ?></div>
                                        </div>
                                        <?php break;
                                    case 'credit': ?>
                                        <div id="progress_plan_gsv_publish" class="progress mb-1 position-relative" style="background-color:#b0b0b0;line-height:16px;">
                                            <div style="width:<?php echo $perc_gsv_publish; ?>%" class="progress-bar d-inline-block bg-<?php echo $perc_gsv_publish_bg; ?>" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100"></div>
                                            <div style="padding: 0 10px" class="justify-content-center d-flex position-absolute w-100 text-white"><?php echo _("GSV Publish"); ?>: <?php echo $gsv_published." "._("of")."&nbsp;".('<b>'.$gsv_publish_credits.'</b>'); ?></div>
                                        </div>
                                        <?php break;
                                } ?>
                            <?php endif; ?>
                        </div>
                        <?php if($settings['change_plan'] || $settings['buy_services']) { ?>
                            <div class="dropdown-item change_plan_dropdown" style="background-color:white;pointer-events:none;">
                                <?php if($settings['change_plan']) { ?>
                                <a href="index.php?p=change_plan" style="pointer-events:initial" class="btn btn-primary btn-block btn-sm">
                                    <i class="fas fa-credit-card align-middle"></i>&nbsp;&nbsp;&nbsp;<span class="align-middle text-uppercase"><?php echo _("Subscription"); ?></span>
                                </a>
                                <?php } ?>
                                 <?php if($settings['buy_services']) { ?>
                                <a href="index.php?p=buy_service" style="pointer-events:initial" class="btn btn-primary btn-block btn-sm">
                                    <i class="fas fa-hand-holding-usd align-middle"></i>&nbsp;&nbsp;&nbsp;<span class="align-middle text-uppercase"><?php echo _("Services"); ?></span>
                                </a>
                                 <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                </li>
            <?php endif; ?>
        <?php endif; ?>
        <li class="nav-item dropdown no-arrow lang_switcher">
            <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" <?php echo ($settings['languages_count']==1) ? 'style="cursor:default;pointer-events:none;"' : ''; ?> >
                <img style="height: 14px;" src="img/flags_lang/<?php echo $lang; ?>.png?v=2" />
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="langDropdown">
                <?php if((!check_language_enabled('en_US',$settings['languages_enabled']) && check_language_enabled('en_GB',$settings['languages_enabled'])) || (check_language_enabled('en_US',$settings['languages_enabled']) && !check_language_enabled('en_GB',$settings['languages_enabled']))) {
                    $languages_list['en_GB']['name'] = "English";
                    $languages_list['en_US']['name'] = "English";
                }
                foreach ($languages_list as $lang_code => $lang_data): ?>
                    <?php if (check_language_enabled($lang_code, $settings['languages_enabled'])): ?>
                        <span style="cursor: pointer;" onclick="switch_language('<?php echo $lang_code; ?>');" class="<?php echo ($lang == $lang_code) ? 'lang_active' : ''; ?> noselect dropdown-item align-middle">
                            <img class="mb-1" src="img/flags_lang/<?php echo $lang_code; ?>.png?v=2" />
                            <span class="ml-2"><?php echo $lang_data['name']; ?></span>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </li>
        <?php if($settings['dark_mode']==1) : ?>
        <li class="nav-item dropdown no-arrow dark_mode_switcher ml-2">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i onclick="switch_dark_mode();" id='btn_light_mode' style="opacity:0;" class='btn_mode_switcher fas fa-sun'></i>
                <script>
                    if (localStorage.getItem("dark_mode") === null) {
                        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                            $('.btn_mode_switcher').attr('id','btn_dark_mode');
                            $('.btn_mode_switcher').removeClass('fa-sun').addClass('fa-moon');
                        }
                    } else {
                        if(localStorage.getItem('dark_mode')==1) {
                            $('.btn_mode_switcher').attr('id','btn_dark_mode');
                            $('.btn_mode_switcher').removeClass('fa-sun').addClass('fa-moon');
                        }
                    }
                    $('.btn_mode_switcher').css('opacity',1);
                </script>
            </a>
        </li>
        <?php endif; ?>
        <div class="topbar-divider d-none d-sm-block"></div>
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span style="padding-top:2px;" class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $user_info['username']; ?></span>&nbsp;
                <img class="img-profile rounded-circle" src="<?php echo $user_info['avatar']; ?>">
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <?php if(!empty($settings['help_url'])) : ?>
                    <a class="dropdown-item" target="_blank" href="<?php echo $settings['help_url']; ?>">
                        <i class="fas fa-question fa-sm fa-fw mr-2 text-gray-400"></i>
                        <?php echo _("Help"); ?>
                    </a>
                <?php endif; ?>
                <a class="dropdown-item" href="index.php?p=edit_profile">
                    <i class="fas fa-lock fa-sm fa-fw mr-2 text-gray-400"></i>
                    <?php echo _("Profile"); ?>
                </a>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                    <?php echo _("Logout"); ?>
                </a>
            </div>
        </li>
    </ul>
</nav>
<?php echo $sub_header; ?>
<div class="mb-3"></div>
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Ready to Leave?"); ?></h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body"><?php echo _("Select Logout below if you are ready to end your current session."); ?></div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal"><?php echo _("Cancel"); ?></button>
                <button class="btn btn-primary" onclick="logout();"><?php echo _("Logout"); ?></button>
            </div>
        </div>
    </div>
</div>
<script>
    $('.quick_action').tooltip();
    $("#sidebarToggleTop").click(function(){
        if($('#accordionSidebar').hasClass('toggled')) {
            sessionStorage.setItem("sidebar_accord", 1);
            $(".nav-item.active .collapse").addClass('show');
            if($('#sidebar_logo_small').length) {
                $('#sidebar_logo').show();
                $('#sidebar_logo_small').hide();
            }
        } else {
            sessionStorage.setItem("sidebar_accord", 0);
            $(".collapse").removeClass('show');
            if($('#sidebar_logo_small').length) {
                $('#sidebar_logo').hide();
                $('#sidebar_logo_small').show();
            }
        }
    });
    $('#virtualtour_selector').on('rendered.bs.select', function (e, clickedIndex, isSelected, previousValue) {
        $('#loading_header').hide();
        setTimeout(function() {
            $('#virtualtour_selector').css('opacity',1);
        },50);
        setTimeout(function() {
            $('.vt_select_header .quick_action').css('opacity',1);
        },100);
    });
</script>