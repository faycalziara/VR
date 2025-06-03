<?php
session_start();
$id_virtual_tour = $_GET['id'];
$virtual_tour = get_virtual_tour($id_virtual_tour,$_SESSION['id_user']);
$vt_versions = get_virtual_tour_versions($id_virtual_tour);
$tmp_languages = get_languages_vt();
$array_languages = $tmp_languages[0];
$default_language = $tmp_languages[1];
$plan_permissions = get_plan_permission($_SESSION['id_user']);
if($virtual_tour['external']==1) {
    $hide_external = "d-none";
    $show_external = "";
    $col2 = "6";
    $tab = "active";
} else {
    $hide_external = "";
    $show_external = "d-none";
    $col2 = "4";
    $tab = "fade";
}
$icons_library = $plan_permissions['enable_icons_library'];
$shop = $plan_permissions['enable_shop'];
$learning = $plan_permissions['enable_learning'];
$intro_slider = $plan_permissions['enable_intro_slider'];
if($user_info['role']=='editor') {
    $editor_permissions = get_editor_permissions($_SESSION['id_user'],$id_virtual_tour);
    if($editor_permissions['edit_virtualtour']==0) {
        $virtual_tour=false;
    }
    if($editor_permissions['shop']==0) {
        $shop = 0;
    }
    if($editor_permissions['icons_library']==0) {
        $icons_library = 0;
    }
}
$custom_html = $plan_permissions['enable_custom_html'];
$loading_iv = $plan_permissions['enable_loading_iv'];
$first_panorama = get_first_room_panorama($id_virtual_tour);
$first_panorama_image = $first_panorama['panorama_image'];
if(!isset($_SESSION['lang'])) {
    if(!empty($user_info['language'])) {
        $language = $user_info['language'];
    } else {
        $language = $settings['language'];
    }
} else {
    $language = $_SESSION['lang'];
}
$language_user = $language;
$_SESSION['id_virtualtour_sel'] = $id_virtual_tour;
$_SESSION['name_virtualtour_sel'] = $virtual_tour['name'];
?>

<?php include("check_block_tour.php"); ?>

<?php if(!$virtual_tour): ?>
    <div class="text-center">
        <div class="error mx-auto" data-text="401">401</div>
        <p class="lead text-gray-800 mb-5"><?php echo _("Permission denied"); ?></p>
        <p class="text-gray-500 mb-0"><?php echo _("It looks like that you do not have permission to access this page"); ?></p>
        <a href="index.php?p=dashboard">← <?php echo _("Back to Dashboard"); ?></a>
    </div>
    <script>
        $('.vt_select_header').remove();
    </script>
<?php die(); endif; ?>
<?php
$change_plan = get_settings()['change_plan'];
if($change_plan) {
    $msg_change_plan = "<a class='text-white' href='index.php?p=change_plan'><b>"._("Click here to change your plan")."</b></a>";
} else {
    $msg_change_plan = "";
}
$user_role = get_user_role($_SESSION['id_user']);
$users = get_users($virtual_tour['id_user']);
$show_in_ui_audio = $virtual_tour['show_audio'];
$show_in_ui_logo = $virtual_tour['show_logo'];
$show_in_ui_poweredby = $virtual_tour['show_poweredby'];
$show_in_ui_form = $virtual_tour['show_main_form'];
$show_in_ui_avatar_video = $virtual_tour['show_avatar_video'];
$s3_params = check_s3_tour_enabled($id_virtual_tour);
$s3_enabled = false;
$s3_url = "";
$path_base_url = "../viewer/";
if(!empty($s3_params)) {
    $s3_bucket_name = $s3_params['bucket'];
    $s3_region = $s3_params['region'];
    $s3_url = init_s3_client($s3_params);
    if($s3_url!==false) {
        $path_base_url = $s3_url."viewer/";
        $s3_enabled = true;
    }
}
$url_background_image = "";
$url_background_image_mobile = "";
$url_background_video = "";
$url_background_video_mobile = "";
$url_logo = "";
$url_poweredby = "";
$url_nadir_logo = "";
$url_song = "";
$url_intro_desktop = "";
$url_intro_mobile = "";
$url_avatar_video = "";
if(!empty($virtual_tour['background_image'])) {
    $url_background_image = $path_base_url."content/".$virtual_tour['background_image'];
}
if(!empty($virtual_tour['background_image_mobile'])) {
    $url_background_image_mobile = $path_base_url."content/".$virtual_tour['background_image_mobile'];
}
if(!empty($virtual_tour['background_video'])) {
    $url_background_video = $path_base_url."content/".$virtual_tour['background_video']."#t=2";
}
if(!empty($virtual_tour['background_video_mobile'])) {
    $url_background_video_mobile = $path_base_url."content/".$virtual_tour['background_video_mobile']."#t=2";
}
if(!empty($virtual_tour['logo'])) {
    $url_logo = $path_base_url."content/".$virtual_tour['logo'];
}
if(!empty($virtual_tour['poweredby_image'])) {
    $url_poweredby = $path_base_url."content/".$virtual_tour['poweredby_image'];
}
if(!empty($virtual_tour['nadir_logo'])) {
    $url_nadir_logo = $path_base_url."content/".$virtual_tour['nadir_logo'];
}
if(!empty($virtual_tour['song'])) {
    $url_song = $path_base_url."content/".$virtual_tour['song'];
}
if(!empty($virtual_tour['intro_desktop'])) {
    $url_intro_desktop = $path_base_url."content/".$virtual_tour['intro_desktop'];
}
if(!empty($virtual_tour['intro_mobile'])) {
    $url_intro_mobile = $path_base_url."content/".$virtual_tour['intro_mobile'];
}
if(!empty($virtual_tour['avatar_video'])) {
    $exists_videos = $virtual_tour['avatar_video'];
    $array_videos = [];
    if ($exists_videos != '') {
        $array_videos = explode(",", $exists_videos);
    }
    $mov_video = '';
    $webm_video = '';
    foreach ($array_videos as $video_s) {
        $extension = strtolower(pathinfo($video_s, PATHINFO_EXTENSION));
        if ($extension == 'mov') {
            $mov_video = $video_s;
        }
        if ($extension == 'webm') {
            $webm_video = $video_s;
        }
    }
    if ($webm_video != '' && $mov_video != '') {
        $url_avatar_video = $path_base_url.$webm_video;
    } else if ($webm_video != '' && $mov_video == '') {
        $url_avatar_video = $path_base_url.$webm_video;
    } else if ($webm_video == '' && $mov_video != '') {
        $url_avatar_video = $path_base_url.$mov_video;
    }
}
$form_content = $virtual_tour['form_content'];
if(!empty($form_content)) {
    $form_content = json_decode($form_content,true);
}
$array_input_lang = array();
$query_lang = "SELECT * FROM svt_virtualtours_lang WHERE id_virtualtour=$id_virtual_tour;";
$result_lang = $mysqli->query($query_lang);
if($result_lang) {
    if ($result_lang->num_rows > 0) {
        while($row_lang = $result_lang->fetch_array(MYSQLI_ASSOC)) {
            $url_avatar_video_l = "";
            $language = $row_lang['language'];
            if(!empty($row_lang['form_content'])) {
                $row_lang['form_content']=json_decode($row_lang['form_content'],true);
                for($i=0;$i<=10;$i++) {
                    if($i==0) {
                        if($row_lang['form_content'][0]['title']==$form_content[0]['title']) {
                            $row_lang['form_content'][0]['title']="";
                        }
                        if($row_lang['form_content'][0]['button']==$form_content[0]['button']) {
                            $row_lang['form_content'][0]['button']="";
                        }
                        if($row_lang['form_content'][0]['response']==$form_content[0]['response']) {
                            $row_lang['form_content'][0]['response']="";
                        }
                        if($row_lang['form_content'][0]['description']==$form_content[0]['description']) {
                            $row_lang['form_content'][0]['description']="";
                        }
                    } else {
                        if($row_lang['form_content'][$i]['label']==$form_content[$i]['label']) {
                            $row_lang['form_content'][$i]['label']="";
                        }
                    }
                }
            } else {
                $row_lang['form_content']=array();
            }
            if(!empty($row_lang['avatar_video'])) {
                $exists_videos = $row_lang['avatar_video'];
                $array_videos = [];
                if ($exists_videos != '') {
                    $array_videos = explode(",", $exists_videos);
                }
                $mov_video_l = '';
                $webm_video_l = '';
                foreach ($array_videos as $video_s) {
                    $extension = strtolower(pathinfo($video_s, PATHINFO_EXTENSION));
                    if ($extension == 'mov') {
                        $mov_video_l = $video_s;
                    }
                    if ($extension == 'webm') {
                        $webm_video_l = $video_s;
                    }
                }
                if ($webm_video_l != '' && $mov_video_l != '') {
                    $url_avatar_video_l = $path_base_url.$webm_video_l;
                } else if ($webm_video_l != '' && $mov_video_l == '') {
                    $url_avatar_video_l = $path_base_url.$webm_video_l;
                } else if ($webm_video_l == '' && $mov_video_l != '') {
                    $url_avatar_video_l = $path_base_url.$mov_video_l;
                }
                $row_lang['mov_video']=$mov_video_l;
                $row_lang['webm_video']=$webm_video_l;
                $row_lang['url_avatar_video']=$url_avatar_video_l;
            } else {
                $row_lang['avatar_video']="";
            }
            unset($row_lang['id_virtualtour']);
            unset($row_lang['language']);
            $array_input_lang[$language]=$row_lang;
        }
    }
}
$array_gallery_images = array();
$query = "SELECT image FROM svt_gallery WHERE id_virtualtour=$id_virtual_tour ORDER BY priority;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
            $array_gallery_images[]=$row['image'];
        }
    }
}
$vr_icons_size = $virtual_tour['vr_icons_size'];
if(!empty($vr_icons_size)) {
    $vr_icons_size = json_decode($vr_icons_size,true);
} else {
    $vr_icons_size = array();
}
?>

<link rel="stylesheet" href="../viewer/css/pannellum.css"/>
<script type="text/javascript" src="../viewer/js/libpannellum.js"></script>
<script type="text/javascript" src="../viewer/js/pannellum.js"></script>
<style>
    .pnlm-control {
        opacity: 1;
    }
</style>

<?php include("check_plan.php"); ?>

<ul class="nav bg-white nav-pills nav-fill mb-2 <?php echo $hide_external; ?>">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="pill" href="#settings_tab"><i class="fas fa-cogs"></i> <?php echo strtoupper(_("SETTINGS")); ?></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="pill" href="#content_tab"><i class="fas fa-photo-video"></i> <?php echo strtoupper(_("CONTENTS")); ?></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" data-toggle="pill" href="#loading_tab"><i class="fas fa-spinner"></i> <?php echo strtoupper(_("LOADING")); ?></a>
    </li>
    <li class="nav-item">
        <a onclick="initialize_hfov();" class="nav-link" data-toggle="pill" href="#hfov_tab"><i class="fas fa-binoculars"></i> <?php echo strtoupper(_("HFOV / INTERACTION")); ?></a>
    </li>
    <?php if($plan_permissions['enable_forms']==1): ?>
    <li class="nav-item">
        <a class="nav-link" data-toggle="pill" href="#form_tab"><i class="fas fa-file-signature"></i> <?php echo strtoupper(_("FORM")); ?></a>
    </li>
    <?php endif; ?>
    <?php if($settings['vr_button']==1) : ?>
    <li class="nav-item">
        <a class="nav-link" data-toggle="pill" href="#vr_tab"><i class="fas fa-vr-cardboard"></i> VR</a>
    </li>
    <?php endif; ?>
    <?php if($shop==1): ?>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#shop_tab"><i class="fas fa-shopping-cart"></i> <?php echo strtoupper(_("SHOP")); ?></a>
        </li>
    <?php endif; ?>
    <?php if($learning==1): ?>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#learning_tab"><i class="fas fa-graduation-cap"></i> <?php echo strtoupper(_("LEARNING")); ?></a>
        </li>
    <?php endif; ?>
    <?php if($user_info['role']=='administrator'): ?>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#note_tab"><i class="far fa-sticky-note"></i> <?php echo strtoupper(_("NOTE")); ?></a>
        </li>
        <li class="nav-item">
            <a onclick="click_editors();" class="nav-link" data-toggle="pill" href="#editors_tab"><i class="fas fa-users-cog"></i> <?php echo strtoupper(_("EDITORS")); ?></a>
        </li>
    <?php endif; ?>
</ul>

<div class="tab-content">
    <div class="tab-pane active" id="settings_tab">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-cog"></i> <?php echo _("General"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name"><?php echo _("Name"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'name'); ?>
                                    <input type="text" class="form-control" id="name" value="<?php echo htmlspecialchars($virtual_tour['name']); ?>" />
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                        <input style="display:none;" type="text" class="form-control input_lang" data-target-id="name" data-lang="<?php echo $lang; ?>" value="<?php echo htmlspecialchars($array_input_lang[$lang]['name']); ?>" />
                                    <?php endif;
                                    } ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="author"><?php echo _("Author"); ?></label>
                                    <input type="text" class="form-control" id="author" value="<?php echo htmlspecialchars($virtual_tour['author']); ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="user"><?php echo _("User"); ?> <i title="<?php echo _("owner of the virtual tour"); ?>" class="help_t fas fa-question-circle"></i></label>
                                    <select id="user" class="form-control" <?php echo ($user_role=='administrator' && $users['count']>1) ? '' : 'disabled' ?> >
                                        <?php echo $users['options']; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="category"><?php echo _("Category"); ?></label><br>
                                    <div class="input-group">
                                        <select multiple id="category" data-selected-text-format="count > 3" data-count-selected-text="{0} <?php echo _("items selected"); ?>" data-none-selected-text="<?php echo _("Nothing selected"); ?>" class="form-control selectpicker">
                                            <?php echo get_categories_option($id_virtual_tour); ?>
                                        </select>
                                        <script type="text/javascript">$('#category').selectpicker('render');</script>
                                        <?php if ($user_info['role']=='administrator') : ?>
                                        <div class="input-group-append">
                                            <button data-toggle="modal" data-target="#modal_add_category" class="btn btn-primary btn-xs <?php echo ($demo) ? 'disabled' : ''; ?>" type="button"><i style="color: white" class="fas fa-plus"></i></button>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="description"><?php echo _("Description"); ?> <i title="<?php echo _("description used as preview for share"); ?>" class="help_t fas fa-question-circle"></i></label><?php echo print_language_input_selector($array_languages,$default_language,'description'); ?><br>
                                    <input type="text" class="form-control" id="description" value="<?php echo htmlspecialchars($virtual_tour['description']); ?>" />
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <input style="display:none;" type="text" class="form-control input_lang" data-target-id="description" data-lang="<?php echo $lang; ?>" value="<?php echo htmlspecialchars($array_input_lang[$lang]['description']); ?>" />
                                        <?php endif;
                                    } ?>
                                </div>
                            </div>
                            <div class="col-md-6 <?php echo $show_external; ?>">
                                <div class="form-group">
                                    <label for="external_url"><?php echo _("External Link"); ?> <i title="<?php echo _("link that will be displayed when the virtual tour opens (must be compatible for embedding)"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="text" class="form-control" id="external_url" value="<?php echo $virtual_tour['external_url']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3 <?php echo $hide_external; ?>">
                                <div class="form-group">
                                    <label for="add_room_sort"><?php echo _("Adding Room - Sorting"); ?> <i title="<?php echo _("positioning of the room in the list when added"); ?>" class="help_t fas fa-question-circle"></i></label>
                                    <select id="add_room_sort" class="form-control">
                                        <option <?php echo ($virtual_tour['add_room_sort']=='start') ? 'selected' : ''; ?> id="start"><?php echo _("As first"); ?></option>
                                        <option <?php echo ($virtual_tour['add_room_sort']=='end') ? 'selected' : ''; ?> id="end"><?php echo _("As last"); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div id="ga_tracking_id_div" class="col-md-3">
                                <div class="form-group">
                                    <label for="ga_tracking_id"><?php echo _("Google Analytics Tracking ID"); ?> <i title="<?php echo _("Google Analytics Tracking ID (G-XXXXXXXXX). Note: Use the Friendly URL in Google Analytics's property url setting."); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="text" class="form-control" id="ga_tracking_id" value="<?php echo $virtual_tour['ga_tracking_id']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="cookie_consent"><?php echo _("Enable Cookie Consent"); ?></label><br>
                                    <input type="checkbox" id="cookie_consent" <?php echo ($virtual_tour['cookie_consent'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3 <?php echo $hide_external; ?> <?php echo ($virtual_tour['ar_simulator']) ? '' : 'd-none'; ?>">
                                <div class="form-group">
                                    <label for="ar_camera_align"><?php echo _("AR Camera alignment"); ?> <i title="<?php echo _("enables the initial step to align the camera with the environment"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="checkbox" id="ar_camera_align" <?php echo ($virtual_tour['ar_camera_align']==1) ? 'checked':''; ?>>
                                </div>
                            </div>
                            <div class="col-md-3 <?php echo (!$settings['pwa_enable']) ? 'd-none' : ''; ?>">
                                <div class="form-group">
                                    <label for="pwa_enable"><?php echo _("Enable")." PWA"; ?></label><br>
                                    <input type="checkbox" id="pwa_enable" <?php echo ($virtual_tour['pwa_enable'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3 <?php echo ($virtual_tour['external']==0) ? 'd-none' : ''; ?>">
                                <div class="form-group">
                                    <label for="language"><?php echo _("Default Language"); ?></label>
                                    <select class="form-control" id="language">
                                        <?php if((!check_language_enabled_viewer('en_US',$settings['languages_enabled']) && check_language_enabled_viewer('en_GB',$settings['languages_enabled'])) || (check_language_enabled_viewer('en_US',$settings['languages_enabled']) && !check_language_enabled_viewer('en_GB',$settings['languages_enabled']))) {
                                            $languages_list['en_GB']['name'] = "English";
                                            $languages_list['en_US']['name'] = "English";
                                        } ?>
                                        <option <?php echo ($virtual_tour['language']=='') ? 'selected':''; ?> id=""><?php echo _("Default")." ({$settings['language']})"; ?></option>
                                        <?php foreach ($languages_list as $lang_code => $lang_data): ?>
                                            <?php if (check_language_enabled_viewer($lang_code, $settings['languages_viewer_enabled'])): ?>
                                                <option <?php echo ($virtual_tour['language']==$lang_code) ? 'selected':''; ?> id="<?php echo $lang_code; ?>"><?php echo $lang_data['name']." - ".$lang_data['native_name']." ($lang_code)"; ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <script type="text/javascript">
                                        var initialSelectedValue = $('#language option:selected').attr('id');
                                        $('#language').on('change', function (e) {
                                            var currentSelectedValue = $('#language option:selected').attr('id');
                                            var currentSelectedValues = $('#languages_enabled').val();
                                            if (currentSelectedValue!=initialSelectedValue && currentSelectedValues.length>0) {
                                                $('#modal_change_languages').modal('show');
                                            }
                                        });
                                    </script>
                                </div>
                            </div>
                            <div class="col-md-3 <?php echo ($virtual_tour['external']==0) ? 'd-none' : ''; ?>">
                                <div class="form-group">
                                    <label for="languages_enabled"><?php echo _("Languages Enabled"); ?></label>
                                    <select <?php echo (!$plan_permissions['enable_multilanguage']) ? 'disabled' : '' ; ?> style="height: 125px" multiple class="form-control selectpicker" id="languages_enabled" data-container="body" data-actions-box="true" data-selected-text-format="count > 2" data-count-selected-text="{0} <?php echo _("items selected"); ?>" data-deselect-all-text="<?php echo _("Deselect All"); ?>" data-select-all-text="<?php echo _("Select All"); ?>" data-none-selected-text="<?php echo _("Only default"); ?>" data-none-results-text="<?php echo _("No results matched"); ?> {0}">
                                        <?php if((!check_language_enabled_viewer('en_US',$settings['languages_enabled']) && check_language_enabled_viewer('en_GB',$settings['languages_enabled'])) || (check_language_enabled_viewer('en_US',$settings['languages_enabled']) && !check_language_enabled_viewer('en_GB',$settings['languages_enabled']))) {
                                            $languages_list['en_GB']['name'] = "English";
                                            $languages_list['en_US']['name'] = "English";
                                        }
                                        foreach ($languages_list as $lang_code => $lang_data): ?>
                                            <?php if (check_language_enabled_viewer($lang_code, $settings['languages_viewer_enabled'])): ?>
                                                <option <?php echo (check_language_enabled_vt($lang_code,$settings['languages_viewer_enabled'],$virtual_tour['languages_enabled'])) ? 'selected':''; ?> id="ls_<?php echo $lang_code; ?>"><?php echo $lang_data['name']." - ".$lang_data['native_name']." ($lang_code)"; ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                    <script type="text/javascript">
                                        var initialSelectedValues = $('#languages_enabled').val().slice();
                                        $('#languages_enabled').selectpicker('render');
                                        $('#languages_enabled').on('hidden.bs.select', function (e) {
                                            var currentSelectedValues = $('#languages_enabled').val();
                                            if (currentSelectedValues.length !== initialSelectedValues.length ||
                                                currentSelectedValues.some(value => !initialSelectedValues.includes(value)) ||
                                                initialSelectedValues.some(value => !currentSelectedValues.includes(value))) {
                                                $('#modal_change_languages').modal('show');
                                            }
                                        });
                                    </script>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row <?php echo $hide_external; ?> <?php echo ($virtual_tour['ar_simulator']) ? 'd-none' : ''; ?>">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-retweet"></i> <?php echo _("Transition"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card-group mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            <i class="fa-solid fa-arrow-right-to-bracket"></i>&nbsp;&nbsp;<?php echo _("Before"); ?>
                                        </div>
                                        <div class="card-body pb-0">
                                            <div class="form-group">
                                                <label for="transition_zoom"><?php echo _("Zoom In"); ?> (<span id="transition_zoom_val"><?php echo $virtual_tour['transition_zoom']; ?></span>) <i title="<?php echo _("zoom level before entering the next room"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                                <input style="margin-top: 10px; margin-bottom: 28px;" oninput="change_transition_zoom();" type="range" min="0" max="100" class="form-control-range" id="transition_zoom" value="<?php echo $virtual_tour['transition_zoom']; ?>" />
                                            </div>
                                            <div class="form-group">
                                                <label for="transition_time"><?php echo _("Zoom In - Duration"); ?> <i title="<?php echo _("zoom duration in milliseconds before entering the next room"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                                <div class="input-group">
                                                    <input type="number" min="0" class="form-control" id="transition_time" value="<?php echo $virtual_tour['transition_time']; ?>" />
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">ms</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <i class="fa-solid fa-arrows-left-right-to-line"></i>&nbsp;&nbsp;<?php echo _("Through"); ?>
                                        </div>
                                        <div class="card-body pb-0">
                                            <div class="form-group">
                                                <label for="transition_effect"><?php echo _("Transition Effect"); ?> <i title="<?php echo _("animation of transition effect between rooms"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                                <select id="transition_effect" class="form-control">
                                                    <option <?php echo ($virtual_tour['transition_effect']=='blind') ? 'selected':''; ?> id="blind">Blind</option>
                                                    <option <?php echo ($virtual_tour['transition_effect']=='bounce') ? 'selected':''; ?> id="bounce">Bounce</option>
                                                    <option <?php echo ($virtual_tour['transition_effect']=='clip') ? 'selected':''; ?> id="clip">Clip</option>
                                                    <option <?php echo ($virtual_tour['transition_effect']=='drop') ? 'selected':''; ?> id="drop">Drop</option>
                                                    <option <?php echo ($virtual_tour['transition_effect']=='fade') ? 'selected':''; ?> id="fade">Fade</option>
                                                    <option <?php echo ($virtual_tour['transition_effect']=='puff') ? 'selected':''; ?> id="puff">Puff</option>
                                                    <option <?php echo ($virtual_tour['transition_effect']=='pulsate') ? 'selected':''; ?> id="pulsate">Pulsate</option>
                                                    <option <?php echo ($virtual_tour['transition_effect']=='scale') ? 'selected':''; ?> id="scale">Scale</option>
                                                    <option <?php echo ($virtual_tour['transition_effect']=='shake') ? 'selected':''; ?> id="shake">Shake</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="transition_fadeout"><?php echo _("Transition Effect - Duration"); ?> <i title="<?php echo _("duration of the transition effect in milliseconds between rooms"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                                <div class="input-group">
                                                    <input type="number" min="0" class="form-control" id="transition_fadeout" value="<?php echo $virtual_tour['transition_fadeout']; ?>" />
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">ms</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <i class="fa-solid fa-arrow-right-from-bracket"></i>&nbsp;&nbsp;<?php echo _("After"); ?>
                                        </div>
                                        <div class="card-body pb-0">
                                            <div class="form-group">
                                                <label for="transition_hfov"><?php echo _("Zoom In/Out"); ?> (<span id="transition_hfov_val"><?php echo $virtual_tour['transition_hfov']; ?></span>) <i title="<?php echo _("zoom level after entering the next room"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                                <input style="margin-top: 10px; margin-bottom: 28px;" oninput="change_transition_hfov();" type="range" min="-100" max="100" class="form-control-range" id="transition_hfov" value="<?php echo $virtual_tour['transition_hfov']; ?>" />
                                            </div>
                                            <div class="form-group">
                                                <label for="transition_hfov_time"><?php echo _("Zoom In/Out - Duration"); ?> <i title="<?php echo _("zoom duration in milliseconds after entering the next room"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                                <div class="input-group">
                                                    <input type="number" min="0" class="form-control" id="transition_hfov_time" value="<?php echo $virtual_tour['transition_hfov_time']; ?>" />
                                                    <div class="input-group-append">
                                                        <span class="input-group-text">ms</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="transition_loading"><?php echo _("Transition Loading icon"); ?> <i title="<?php echo _("shows the loading icon before loading rooms"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="checkbox" id="transition_loading" <?php echo ($virtual_tour['transition_loading']==1) ? 'checked':''; ?>>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="sameAzimuth"><?php echo _("Same Azimuth"); ?> <i title="<?php echo _("maintain the same direction with regard to north while navigate between rooms (you must set the north position in all rooms)"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="checkbox" id="sameAzimuth" <?php echo ($virtual_tour['sameAzimuth'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="markers_default_backlink"><?php echo _("Default Add Back Markers"); ?> <i title="<?php echo _("default 'Add Marker to go back' setting when adding a new marker"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="checkbox" id="markers_default_backlink" <?php echo ($virtual_tour['markers_default_backlink'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="click_anywhere"><?php echo _("Click Anywhere"); ?> <i title="<?php echo _("allows you to click near the marker to go to the corresponding room"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input onchange="change_click_anywhere();" type="checkbox" id="click_anywhere" <?php echo ($virtual_tour['click_anywhere'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="hide_markers"><?php echo _("Hide Markers"); ?> <i title="<?php echo _("hide all the markers (only when click anywhere is enabled)"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input onchange="change_click_anywhere();" <?php echo (!$virtual_tour['click_anywhere'])?'disabled':''; ?> type="checkbox" id="hide_markers" <?php echo ($virtual_tour['hide_markers'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="hover_markers"><?php echo _("Hover Markers"); ?> <i title="<?php echo _("shows hidden markers when approaching them with the mouse"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input <?php echo (!$virtual_tour['click_anywhere'] || !$virtual_tour['hide_markers'])?'disabled':''; ?> type="checkbox" id="hover_markers" <?php echo ($virtual_tour['hover_markers'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div style="margin-bottom: 5px;" class="form-group">
                                    <label for="markers_default_lookat"><?php echo _("Default Markers LookAt"); ?> <i title="<?php echo _("default 'lookat' setting when adding a new marker"); ?>" class="help_t fas fa-question-circle"></i></label>
                                    <select id="markers_default_lookat" class="form-control">
                                        <option <?php echo ($virtual_tour['markers_default_lookat']==0) ? 'selected' : ''; ?> id="0"><?php echo _("Disabled"); ?></option>
                                        <option <?php echo ($virtual_tour['markers_default_lookat']==1) ? 'selected' : ''; ?> id="1"><?php echo _("Horizontal only"); ?></option>
                                        <option <?php echo ($virtual_tour['markers_default_lookat']==2) ? 'selected' : ''; ?> id="2"><?php echo _("Horizontal and Vertical"); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row <?php echo $hide_external; ?>">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-bullseye"></i> <?php echo _("POIs"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="leave_poi_open"><?php echo _("Allow multiple open"); ?> <i title="<?php echo _("allow multiple items to be open, otherwise only one at a time will remain open"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="checkbox" id="leave_poi_open" <?php echo ($virtual_tour['leave_poi_open'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="close_poi_click_outside"><?php echo _("Close on click outside"); ?> <i title="<?php echo _("click outside the element to close it, otherwise keep it open"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="checkbox" id="close_poi_click_outside" <?php echo ($virtual_tour['close_poi_click_outside'])?'checked':''; ?> />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row <?php echo $hide_external; ?> <?php echo ($virtual_tour['ar_simulator']) ? 'd-none' : ''; ?>">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="far fa-keyboard"></i> <?php echo _("Controls"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="keyboard_mode"><?php echo _("Keyboard Mode"); ?></label><br>
                                    <select onchange="change_keyboard_mode();" id="keyboard_mode" class="form-control">
                                        <option <?php echo ($virtual_tour['keyboard_mode']==0) ? 'selected':''; ?> id="0"><?php echo _("Disabled"); ?></option>
                                        <option <?php echo ($virtual_tour['keyboard_mode']==1) ? 'selected':''; ?> id="1"><?php echo _("Enabled, mode 1"); ?></option>
                                        <option <?php echo ($virtual_tour['keyboard_mode']==2) ? 'selected':''; ?> id="2"><?php echo _("Enabled, mode 2"); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <label style="opacity:0">.</label><br>
                                <div class="<?php echo ($virtual_tour['keyboard_mode']==0) ? '':'d-none'; ?>" id="keyboard_msg_0"><?php echo _("Keyboard controls are disabled."); ?></div>
                                <div class="<?php echo ($virtual_tour['keyboard_mode']==1) ? '':'d-none'; ?>" id="keyboard_msg_1"><i class="fas fa-arrow-left"></i> <i class="fas fa-arrow-up"></i> <i class="fas fa-arrow-down"></i> <i class="fas fa-arrow-right"></i> <b>WASD</b> <?php echo _("to look around"); ?>&nbsp;&nbsp;&nbsp;<b>SPACE</b> <?php echo _("to click"); ?>&nbsp;&nbsp;&nbsp;<b>Z</b> <?php echo _("to go previous room"); ?>&nbsp;&nbsp;&nbsp;<b>X</b> <?php echo _("to go next room"); ?>&nbsp;&nbsp;&nbsp;<i class="fas fa-minus"></i> <i class="fas fa-plus"></i> <?php echo _("to zoom in/out"); ?></div>
                                <div class="<?php echo ($virtual_tour['keyboard_mode']==2) ? '':'d-none'; ?>" id="keyboard_msg_2"><i class="fas fa-arrow-left"></i> <i class="fas fa-arrow-right"></i> <b>WASD</b> <?php echo _("to look around"); ?>&nbsp;&nbsp;&nbsp;<b>SPACE</b> <?php echo _("to click"); ?>&nbsp;&nbsp;&nbsp;<i class="fas fa-arrow-down"></i> <?php echo _("to go previous room"); ?>&nbsp;&nbsp;&nbsp;<i class="fas fa-arrow-up"></i> <?php echo _("to go next room"); ?>&nbsp;&nbsp;&nbsp;<i class="fas fa-minus"></i> <i class="fas fa-plus"></i> <?php echo _("to zoom in/out"); ?></div>
                            </div>
                            <div class="col-md-12 <?php echo (!$plan_permissions['enable_context_info']) ? 'd-none' : '' ; ?>">
                                <div class="form-group">
                                    <label for="context_info"><?php echo _("Right Click Content"); ?> <i title="<?php echo _("content displayed when the right button is pressed. leave empty for disable"); ?>" class="help_t fas fa-question-circle"></i></label>
                                    <div id="context_info"><?php echo $virtual_tour['context_info']; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row <?php echo $hide_external; ?>">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-bolt"></i> <?php echo _("Performance"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="quality_viewer"><?php echo _("Viewer quality"); ?> <i title="<?php echo _("lower values means faster view (poor quality), higher value means slow view (high quality)."); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input min="0.3" max="1.1" step="0.1" type="range" class="form-control-range" id="quality_viewer" value="<?php echo $virtual_tour['quality_viewer']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="compress_jpg"><?php echo _("Compress images quality"); ?> <i title="<?php echo _("10 to 100: lower values means faster loading (poor quality), higher value means slow loading (high quality). 100 to disable compression."); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input min="10" max="100" type="number" class="form-control" id="compress_jpg" value="<?php echo $virtual_tour['compress_jpg']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="max_width_compress"><?php echo _("Max width panorama"); ?> <i title="<?php echo _("maximum width in pixels of panoramic images. if they exceed this width the images will be resized. 0 to disable resize."); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <div class="input-group">
                                        <input type="number" class="form-control" id="max_width_compress" value="<?php echo $virtual_tour['max_width_compress']; ?>" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">px</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="preload_panoramas"><?php echo _("Preload panoramas"); ?> <i title="<?php echo _("preload all panorama images for faster loading between rooms"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="checkbox" id="preload_panoramas" <?php echo ($virtual_tour['preload_panoramas'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="mobile_panoramas"><?php echo _("Mobile panoramas"); ?> <i title="<?php echo _("uses a version of the panorama image optimized for mobile devices"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="checkbox" id="mobile_panoramas" <?php echo ($virtual_tour['mobile_panoramas'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="enable_multires"><?php echo _("Enable multi resolution"); ?> <i title="<?php echo _("splits the panorama image into multiple sectors and loads them in parallel to reduce loading times"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input <?php echo (!$plan_permissions['enable_multires']) ? 'disabled' : '' ; ?> type="checkbox" id="enable_multires" <?php echo ($virtual_tour['enable_multires'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo _("Regenerate panoramas"); ?> <i title="<?php echo _("force regenerate all panoramas images"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <button id="btn_regenerate_panoramas" onclick="regenerate_panoramas();" class="btn btn-block btn-primary <?php echo ($demo) ? 'disabled':''; ?>"><?php echo _("Regenerate All"); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row <?php echo $hide_external; ?> <?php echo ($user_info['role']!='administrator') ? 'd-none' : ''; ?>">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-eraser"></i> <?php echo _("Clean Up"); ?> <i style="font-size:12px;"><?php echo _("(only visible to administrators)"); ?></i></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="keep_original_panorama"><?php echo _("Keep original panoramas"); ?> <i title="<?php echo _("keep a copy of the original uploaded file. N.B. if you don't keep them you won't be able to regenerate the panoramas"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="checkbox" id="keep_original_panorama" <?php echo ($virtual_tour['keep_original_panorama'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><?php echo _("Recoverable disk space"); ?> <i title="<?php echo _("Disk space recovered after deleting original panoramas"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <div id="disk_space_original" class="h5 mb-0 font-weight-bold text-gray-800">
                                        <button style="line-height:1;opacity:1" onclick="get_disk_space_original();" class="btn btn-sm btn-primary p-1"><i class="fab fa-digital-ocean"></i> <?php echo _("analyze"); ?></button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><?php echo _("Original panoramas"); ?> <i title="<?php echo _("deletes all the original uploaded file. N.B. if you don't keep them you won't be able to regenerate the panoramas"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <button id="btn_delete_original_panoramas" onclick="delete_original_panoramas();" class="btn btn-block btn-danger <?php echo ($demo) ? 'disabled':''; ?>"><?php echo _("Delete All"); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane <?php echo $tab; ?>" id="loading_tab">
        <div class="row <?php echo ($virtual_tour['external']==1) ? 'd-block' : ''; ?>">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-2">
                        <div class="row">
                            <div class="col-lg-8 col-md-6 col-sm-6 mb-2 mb-sm-0 mb-md-0 mb-lg-0">
                                <h6 style="vertical-align: bottom" class="m-0 d-inline-block font-weight-bold text-primary"><i class="fas fa-spinner"></i> <?php echo _("Loading Settings"); ?></h6>
                            </div>
                            <?php if(count($vt_versions)>0) : ?>
                                <div class="col-lg-4 col-md-6 col-sm-6">
                                    <div class="form-group form-group-sm mb-0">
                                        <select onchange="change_vt_version_loading();" class="form-control form-control-sm" id="vt_version">
                                            <option id="0"><?php echo _("Main Version"); ?></option>
                                            <?php
                                            foreach ($vt_versions as $vt_version) { ?>
                                                <option id="<?php echo $vt_version['id']; ?>"><?php echo $vt_version['version']; ?></option>
                                            <?php } ?>
                                        </select>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div id="loading_settings" class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="auto_start"><?php echo _("Auto start"); ?> <i title="<?php echo _("start the virtual tour automatically on loading"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <select id="auto_start" class="form-control">
                                        <option <?php echo ($virtual_tour['auto_start']==0)?'selected':''; ?> id="0"><?php echo _("Disabled"); ?></option>
                                        <option <?php echo ($virtual_tour['auto_start']==1)?'selected':''; ?> id="1"><?php echo _("Enabled"); ?></option>
                                        <option <?php echo ($virtual_tour['auto_start']==2)?'selected':''; ?> id="2"><?php echo _("Enabled (Disabled when embedded)"); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="hide_loading"><?php echo _("Loading Info"); ?>  <i title="<?php echo _("display logo, name and progress bar during initial loading"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="checkbox" id="hide_loading" <?php echo ($virtual_tour['hide_loading'])?'':'checked'; ?> />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="show_background"><?php echo _("Loading Background"); ?>  <i title="<?php echo _("display background image, video or intro slider during initial loading"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="checkbox" id="show_background" <?php echo ($virtual_tour['show_background'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3 <?php echo $hide_external; ?>">
                                <div class="form-group">
                                    <label for="flyin"><?php echo _("Fly-In"); ?> <i title="<?php echo _("start the fly-in animation at the first entrance to the virtual tour"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <select <?php echo (!$plan_permissions['enable_flyin']) ? 'disabled' : '' ; ?> id="flyin" class="form-control">
                                        <option <?php echo ($virtual_tour['flyin']==0)?'selected':''; ?> id="0"><?php echo _("Disabled"); ?></option>
                                        <option <?php echo ($virtual_tour['flyin']==1)?'selected':''; ?> id="1"><?php echo _("Enabled (Sphere zoom effect)"); ?></option>
                                        <option <?php echo ($virtual_tour['flyin']==2)?'selected':''; ?> id="2"><?php echo _("Enabled (Little Planet effect)"); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 <?php echo $hide_external; ?>">
                                <div class="form-group">
                                    <label for="flyin_duration"><?php echo _("Fly-In Duration"); ?> <i title="<?php echo _("duration in milliseconds of the fly-in animation."); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <div class="input-group">
                                        <input type="number" min="0" class="form-control" id="flyin_duration" value="<?php echo $virtual_tour['flyin_duration']; ?>" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">ms</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="loading_background_color"><?php echo _("Loading Background Color"); ?></label><br>
                                    <input type="text" class="form-control" id="loading_background_color" value="<?php echo $virtual_tour['loading_background_color']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="loading_text_color"><?php echo _("Loading Text Color"); ?></label><br>
                                    <input type="text" class="form-control" id="loading_text_color" value="<?php echo $virtual_tour['loading_text_color']; ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php foreach ($vt_versions as $vt_version) { ?>
                        <div style="display:none;" id="loading_settings_v<?php echo $vt_version['id']; ?>" data-id-version="<?php echo $vt_version['id']; ?>" class="card-body loading_settings_v">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="auto_start_v<?php echo $vt_version['id']; ?>"><?php echo _("Auto start"); ?> <i title="<?php echo _("start the virtual tour automatically on loading"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                        <select id="auto_start_v<?php echo $vt_version['id']; ?>" class="form-control">
                                            <option <?php echo ($vt_version['auto_start']==0)?'selected':''; ?> id="0"><?php echo _("Disabled"); ?></option>
                                            <option <?php echo ($vt_version['auto_start']==1)?'selected':''; ?> id="1"><?php echo _("Enabled"); ?></option>
                                            <option <?php echo ($vt_version['auto_start']==2)?'selected':''; ?> id="2"><?php echo _("Enabled (Disabled when embedded)"); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="hide_loading_v<?php echo $vt_version['id']; ?>"><?php echo _("Loading Info"); ?>  <i title="<?php echo _("display logo, name and progress bar during initial loading"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                        <input type="checkbox" id="hide_loading_v<?php echo $vt_version['id']; ?>" <?php echo ($vt_version['hide_loading'])?'':'checked'; ?> />
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label for="show_background_v<?php echo $vt_version['id']; ?>"><?php echo _("Loading Background"); ?>  <i title="<?php echo _("display background image, video or intro slider during initial loading"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                        <input type="checkbox" id="show_background_v<?php echo $vt_version['id']; ?>" <?php echo ($vt_version['show_background'])?'checked':''; ?> />
                                    </div>
                                </div>
                                <div class="col-md-3 <?php echo $hide_external; ?>">
                                    <div class="form-group">
                                        <label for="flyin_v<?php echo $vt_version['id']; ?>"><?php echo _("Fly-In"); ?> <i title="<?php echo _("start the fly-in animation at the first entrance to the virtual tour"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                        <select <?php echo (!$plan_permissions['enable_flyin']) ? 'disabled' : '' ; ?> id="flyin_v<?php echo $vt_version['id']; ?>" class="form-control">
                                            <option <?php echo ($vt_version['flyin']==0)?'selected':''; ?> id="0"><?php echo _("Disabled"); ?></option>
                                            <option <?php echo ($vt_version['flyin']==1)?'selected':''; ?> id="1"><?php echo _("Enabled (Sphere zoom effect)"); ?></option>
                                            <option <?php echo ($vt_version['flyin']==2)?'selected':''; ?> id="2"><?php echo _("Enabled (Little Planet effect)"); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3 <?php echo $hide_external; ?>">
                                    <div class="form-group">
                                        <label for="flyin_duration_v<?php echo $vt_version['id']; ?>"><?php echo _("Fly-In Duration"); ?> <i title="<?php echo _("duration in milliseconds of the fly-in animation."); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                        <div class="input-group">
                                            <input type="number" min="0" class="form-control" id="flyin_duration_v<?php echo $vt_version['id']; ?>" value="<?php echo $vt_version['flyin_duration']; ?>" />
                                            <div class="input-group-append">
                                                <span class="input-group-text">ms</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="loading_background_color_v<?php echo $vt_version['id']; ?>"><?php echo _("Loading Background Color"); ?></label><br>
                                        <input type="text" class="form-control loading_background_color_v" id="loading_background_color_v<?php echo $vt_version['id']; ?>" value="<?php echo $vt_version['loading_background_color']; ?>" />
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="loading_text_color_v<?php echo $vt_version['id']; ?>"><?php echo _("Loading Text Color"); ?></label><br>
                                        <input type="text" class="form-control loading_text_color_v" id="loading_text_color_v<?php echo $vt_version['id']; ?>" value="<?php echo $vt_version['loading_text_color']; ?>" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <div class="<?php echo ($virtual_tour['external']==1) ? 'float-left' : ''; ?> <?php echo ($loading_iv==0) ? 'd-none' : ''; ?> col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="far fa-image"></i> <?php echo _("Background Image (Desktop)"); ?> <i title="<?php echo _("image displayed as background during initial loading and used as preview image for share"); ?>" class="help_t fas fa-question-circle"></i></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div id="div_exist_bg" class="col-md-12">
                                <div class="form-group">
                                    <select onchange="change_exist_bg();" class="form-control" id="exist_bg">
                                        <option selected id="0"><?php echo _("Upload new Background"); ?></option>
                                        <?php echo get_option_exist_background_logo($_SESSION['id_user'],$s3_enabled,$s3_url); ?>
                                    </select>
                                </div>
                            </div>
                            <div style="display: none" id="div_image_bg" class="col-md-12">
                                <img src="<?php echo $url_background_image; ?>" />
                            </div>
                            <div style="display: none" id="div_delete_bg" class="col-md-12 mt-2">
                                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_bg();" class="btn btn-block btn-danger"><?php echo _("REMOVE IMAGE"); ?></button>
                            </div>
                            <div style="display: none" class="col-md-12" id="div_upload_bg">
                                <?php if($upload_content) : ?>
                                    <form id="frm_b" action="ajax/upload_background_image.php" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="txtFile_b" name="txtFile_b" />
                                                        <label class="custom-file-label" for="txtFile_b"><?php echo _("Choose file"); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <input <?php echo ($demo || $disabled_upload) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload_b" value="<?php echo _("Upload Background Image"); ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="preview text-center">
                                                    <div class="progress progress_b mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                        <div class="progress-bar" id="progressBar_b" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                            0%
                                                        </div>
                                                    </div>
                                                    <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error_b"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="<?php echo ($virtual_tour['external']==1) ? 'float-left' : ''; ?> <?php echo ($loading_iv==0) ? 'd-none' : ''; ?> col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="far fa-image"></i> <?php echo _("Background Image (Mobile)"); ?> <i title="<?php echo _("image displayed as background during initial loading on a mobile"); ?>" class="help_t fas fa-question-circle"></i></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div id="div_exist_bg_m" class="col-md-12">
                                <div class="form-group">
                                    <select onchange="change_exist_bg_m();" class="form-control" id="exist_bg_m">
                                        <option selected id="0"><?php echo _("Upload new Background"); ?></option>
                                        <?php echo get_option_exist_background_m_logo($_SESSION['id_user'],$s3_enabled,$s3_url); ?>
                                    </select>
                                </div>
                            </div>
                            <div style="display: none;" id="div_image_bg_m" class="col-md-12">
                                <img src="<?php echo $url_background_image_mobile; ?>" />
                            </div>
                            <div style="display: none" id="div_delete_bg_m" class="col-md-12 mt-2">
                                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_bg_m();" class="btn btn-block btn-danger"><?php echo _("REMOVE IMAGE"); ?></button>
                            </div>
                            <div style="display: none" class="col-md-12" id="div_upload_bg_m">
                                <?php if($upload_content) : ?>
                                    <form id="frm_b_m" action="ajax/upload_background_image_m.php" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="txtFile_b_m" name="txtFile_b_m" />
                                                        <label class="custom-file-label" for="txtFile_b_m"><?php echo _("Choose file"); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <input <?php echo ($demo || $disabled_upload) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload_b_m" value="<?php echo _("Upload Background Image"); ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="preview text-center">
                                                    <div class="progress progress_b_m mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                        <div class="progress-bar" id="progressBar_b_m" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                            0%
                                                        </div>
                                                    </div>
                                                    <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error_b_m"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 <?php echo ($loading_iv==0) ? 'd-none' : ''; ?> <?php echo $hide_external; ?>">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-video"></i> <?php echo _("Background Video (Desktop)"); ?> <i title="<?php echo _("video displayed as background during initial loading"); ?>" class="help_t fas fa-question-circle"></i></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div id="div_exist_video_bg" class="col-md-12">
                                <div class="form-group">
                                    <select onchange="change_exist_video_bg();" class="form-control" id="exist_video_bg">
                                        <option selected id="0"><?php echo _("Upload new Background"); ?></option>
                                        <?php echo get_option_exist_background_video($_SESSION['id_user'],$s3_enabled,$s3_url); ?>
                                    </select>
                                </div>
                            </div>
                            <div style="display: none" id="div_video_bg" class="col-md-12">
                                <video muted><source src="<?php echo $url_background_video; ?>" type="video/mp4"></video>
                            </div>
                            <div style="display: none" id="div_video_params" class="col-md-12 mt-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="background_video_delay"><?php echo _("Video display time (seconds)"); ?> <i title="<?php echo _("set to 0 to wait for the end of the video, otherwise set the seconds for which the video should be displayed"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                            <input <?php echo ($virtual_tour['background_video']=='') ? 'disabled' : '' ; ?> class="form-control" type="number" min="0" id="background_video_delay" value="<?php echo $virtual_tour['background_video_delay'];?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="background_video_skip"><?php echo _("Video skippable"); ?></label><br>
                                            <input <?php echo ($virtual_tour['background_video_skip']==1) ? 'checked' : '' ; ?> type="checkbox" id="background_video_skip" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="display: none" id="div_delete_video_bg" class="col-md-12 mt-2">
                                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_video_bg();" class="btn btn-block btn-danger"><?php echo _("REMOVE VIDEO"); ?></button>
                            </div>
                            <div style="display: none" class="col-md-12" id="div_upload_video_bg">
                                <?php if($upload_content) : ?>
                                    <form id="frm_b_v" action="ajax/upload_background_video.php" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="txtFile_b_v" name="txtFile_b_v" />
                                                        <label class="custom-file-label" for="txtFile_b_v"><?php echo _("Choose file"); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <input <?php echo ($demo || $disabled_upload) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload_b_v" value="<?php echo _("Upload Background Video"); ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="preview text-center">
                                                    <div class="progress progress_b_v mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                        <div class="progress-bar" id="progressBar_b_v" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                            0%
                                                        </div>
                                                    </div>
                                                    <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error_b_v"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 <?php echo ($loading_iv==0) ? 'd-none' : ''; ?> <?php echo $hide_external; ?>">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-video"></i> <?php echo _("Background Video (Mobile)"); ?> <i title="<?php echo _("video displayed as background during initial loading on mobile"); ?>" class="help_t fas fa-question-circle"></i></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div id="div_exist_video_bg_m" class="col-md-12">
                                <div class="form-group">
                                    <select onchange="change_exist_video_bg_m();" class="form-control" id="exist_video_bg_m">
                                        <option selected id="0"><?php echo _("Upload new Background"); ?></option>
                                        <?php echo get_option_exist_background_m_video($_SESSION['id_user'],$s3_enabled,$s3_url); ?>
                                    </select>
                                </div>
                            </div>
                            <div style="display: none" id="div_video_bg_m" class="col-md-12">
                                <video muted><source src="<?php echo $url_background_video_mobile; ?>" type="video/mp4"></video>
                            </div>
                            <div style="display: none" id="div_video_m_params" class="col-md-12 mt-2">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="background_video_delay_m"><?php echo _("Video display time (seconds)"); ?> <i title="<?php echo _("set to 0 to wait for the end of the video, otherwise set the seconds for which the video should be displayed"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                            <input <?php echo ($virtual_tour['background_video_mobile']=='') ? 'disabled' : '' ; ?> class="form-control" type="number" min="0" id="background_video_delay_m" value="<?php echo $virtual_tour['background_video_delay_mobile'];?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="background_video_skip_m"><?php echo _("Video skippable"); ?></label><br>
                                            <input <?php echo ($virtual_tour['background_video_skip_mobile']==1) ? 'checked' : '' ; ?> type="checkbox" id="background_video_skip_m" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div style="display: none" id="div_delete_video_bg_m" class="col-md-12 mt-2">
                                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_video_bg_m();" class="btn btn-block btn-danger"><?php echo _("REMOVE VIDEO"); ?></button>
                            </div>
                            <div style="display: none" class="col-md-12" id="div_upload_video_bg_m">
                                <?php if($upload_content) : ?>
                                    <form id="frm_b_v_m" action="ajax/upload_background_video.php" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="txtFile_b_v_m" name="txtFile_b_v_m" />
                                                        <label class="custom-file-label" for="txtFile_b_v_m"><?php echo _("Choose file"); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <input <?php echo ($demo || $disabled_upload) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload_b_v_m" value="<?php echo _("Upload Background Video"); ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="preview text-center">
                                                    <div class="progress progress_b_v_m mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                        <div class="progress-bar" id="progressBar_b_v_m" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                            0%
                                                        </div>
                                                    </div>
                                                    <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error_b_v_m"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12 <?php echo ($intro_slider==0) ? 'd-none' : ''; ?> <?php echo $hide_external; ?>">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-images"></i> <?php echo _("Intro Slider"); ?> <i title="<?php echo _("image slider displayed as background during initial loading"); ?>" class="help_t fas fa-question-circle"></i></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 <?php echo $hide_external; ?>">
                                <div class="form-group">
                                    <label for="intro_slider_delay"><?php echo _("Image Duration"); ?> <i title="<?php echo _("duration in seconds of displaying each image"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <div class="input-group">
                                        <input type="number" min="1" class="form-control" id="intro_slider_delay" value="<?php echo $virtual_tour['intro_slider_delay']; ?>" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">s</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3 <?php echo $hide_external; ?>">
                                <div class="form-group">
                                    <label style="opacity:0">.</label>
                                    <button data-toggle="modal" data-target="#modal_gallery_images" class="btn btn-block btn-primary <?php echo (count($array_gallery_images)==0) ? 'disabled' : ''; ?>"><i class="fas fa-check-to-slot"></i>&nbsp;&nbsp;<?php echo _("Grab images from Gallery"); ?></button>
                                </div>
                            </div>
                        </div>
                        <?php if($create_content) : ?><form action="ajax/upload_gallery_image.php" class="dropzone mb-3 noselect <?php echo ($demo || $disabled_upload) ? 'disabled' : ''; ?>" id="gallery-dropzone"></form><?php endif; ?>
                        <div id="list_images" class="noselect">
                            <p><?php echo _("Loading images ..."); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane <?php echo $tab; ?>" id="vr_tab">
        <div class="row <?php echo $hide_external; ?>">
            <div class="col-md-12 mb-4">
                <div class="card shadow mb-12">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-icons"></i> <?php echo _("Icons"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-2 text-center">
                                <div class="form-group">
                                    <label><?php echo _("Marker"); ?></label><br>
                                    <?php echo print_vr_icon_block('marker'); ?>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2 text-center">
                                <div class="form-group">
                                    <label><?php echo _("POI Image"); ?></label><br>
                                    <?php echo print_vr_icon_block('poi_image'); ?>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2 text-center">
                                <div class="form-group">
                                    <label><?php echo _("POI Video"); ?></label><br>
                                    <?php echo print_vr_icon_block('poi_video'); ?>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2 text-center">
                                <div class="form-group">
                                    <label><?php echo _("POI Video 360"); ?></label><br>
                                    <?php echo print_vr_icon_block('poi_video360'); ?>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2 text-center">
                                <div class="form-group">
                                    <label><?php echo _("POI Text"); ?></label><br>
                                    <?php echo print_vr_icon_block('poi_html'); ?>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2 text-center">
                                <div class="form-group">
                                    <label><?php echo _("POI Audio"); ?></label><br>
                                    <?php echo print_vr_icon_block('poi_audio'); ?>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2 text-center">
                                <div class="form-group">
                                    <label><?php echo _("POI Object 3D"); ?></label><br>
                                    <?php echo print_vr_icon_block('poi_object3d'); ?>
                                </div>
                            </div>
                            <div class="col-md-3 mb-2 text-center">
                                <div class="form-group">
                                    <label><?php echo _("Close"); ?></label><br>
                                    <?php echo print_vr_icon_block('close'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane <?php echo $tab; ?>" id="content_tab">
        <div class="row <?php echo ($virtual_tour['external']==1) ? 'd-block' : ''; ?>">
            <div class="<?php echo ($virtual_tour['external']==1) ? 'float-left' : ''; ?> col-md-<?php echo $col2; ?>">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="far fa-image"></i> <?php echo _("Logo"); ?> <i title="<?php echo _("logo displayed on top of the tour"); ?>" class="help_t fas fa-question-circle"></i> <i style="font-size:12px;vertical-align:middle;color:<?php echo ($show_in_ui_logo>0)?'green':'orange'; ?>" <?php echo ($show_in_ui_logo==0)?'title="'._("Not visible in the tour, enable it in the Editor UI").'"':''; ?> class="<?php echo ($show_in_ui_logo==0)?'help_t':''; ?> show_in_ui fas fa-circle"></i></h6>
                    </div>
                    <div class="card-body <?php echo (!$plan_permissions['enable_logo']) ? 'disabled' : '' ; ?>">
                        <div class="row">
                            <div id="div_exist_logo" class="col-md-12">
                                <div class="form-group">
                                    <select onchange="change_exist_logo();" class="form-control" id="exist_logo">
                                        <option selected id="0"><?php echo _("Upload new Logo"); ?></option>
                                        <?php echo get_option_exist_logo($_SESSION['id_user'],$s3_enabled,$s3_url); ?>
                                    </select>
                                </div>
                            </div>
                            <div style="display: none" id="div_image_logo" class="col-md-12">
                                <img style="width: 100%" src="<?php echo $url_logo; ?>" />
                            </div>
                            <div style="display: none" id="div_delete_logo" class="col-md-12 mt-2">
                                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_logo();" class="btn btn-block btn-danger"><?php echo _("REMOVE LOGO"); ?></button>
                            </div>
                            <div style="display: none" class="col-md-12" id="div_upload_logo">
                                <?php if($upload_content) : ?>
                                    <form id="frm_l" action="ajax/upload_logo_image.php" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="txtFile_l" name="txtFile_l" />
                                                        <label class="custom-file-label" for="txtFile_l"><?php echo _("Choose file"); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <input <?php echo ($demo || $disabled_upload) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload_l" value="<?php echo _("Upload Logo Image"); ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="preview text-center">
                                                    <div class="progress progress_l mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                        <div class="progress-bar" id="progressBar_l" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                            0%
                                                        </div>
                                                    </div>
                                                    <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error_l"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div id="div_link_logo" class="col-md-12 mt-2 <?php echo $hide_external; ?>">
                                <div class="form-group">
                                    <label for="link_logo"><?php echo _("Hyperlink"); ?></label>
                                    <input id="link_logo" type="text" class="form-control" value="<?php echo $virtual_tour['link_logo']; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 <?php echo $hide_external; ?>">
                <div class="card shadow mb-4">
                    <div style="padding: 10px 20px" class="card-header">
                        <h6 style="vertical-align:bottom;" class="m-0 font-weight-bold text-primary d-inline-block"><i class="far fa-registered"></i> <?php echo _("Powered By"); ?> <i title="<?php echo _("powered by - logo / text displayed on bottom of the tour"); ?>" class="help_t fas fa-question-circle"></i> <i style="font-size:12px;vertical-align:middle;color:<?php echo ($show_in_ui_poweredby>0)?'green':'orange'; ?>" <?php echo ($show_in_ui_poweredby==0)?'title="'._("Not visible in the tour, enable it in the Editor UI").'"':''; ?> class="<?php echo ($show_in_ui_poweredby==0)?'help_t':''; ?> show_in_ui fas fa-circle"></i></h6>
                        <select onchange="change_poweredby_type();" id="poweredby_type" style="width:100px" class="form-control form-control-sm d-inline-block float-right">
                            <option <?php echo ($virtual_tour['poweredby_type']=='image') ? 'selected' : ''; ?> id="image"><?php echo _("Logo"); ?></option>
                            <option <?php echo ($virtual_tour['poweredby_type']=='text') ? 'selected' : ''; ?> id="text"><?php echo _("Text"); ?></option>
                        </select>
                    </div>
                    <div class="card-body <?php echo (!$plan_permissions['enable_poweredby']) ? 'disabled' : '' ; ?>">
                        <div style="display: <?php echo ($virtual_tour['poweredby_type']=='text') ? 'block' : 'none'; ?> " id="poweredby_type_text" class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="poweredby_text"><?php echo _("Text"); ?></label>
                                    <input id="poweredby_text" type="text" class="form-control" value="<?php echo $virtual_tour['poweredby_text']; ?>">
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="poweredby_link_text"><?php echo _("Hyperlink"); ?></label>
                                    <input id="poweredby_link_text" type="text" class="form-control" value="<?php echo $virtual_tour['poweredby_link']; ?>">
                                </div>
                            </div>
                        </div>
                        <div style="display: <?php echo ($virtual_tour['poweredby_type']=='image') ? 'block' : 'none'; ?> " id="poweredby_type_image" class="row">
                            <div id="div_exist_poweredby" class="col-md-12">
                                <div class="form-group">
                                    <select onchange="change_exist_poweredby();" class="form-control" id="exist_poweredby">
                                        <option selected id="0"><?php echo _("Upload new Logo"); ?></option>
                                        <?php echo get_option_exist_poweredby($_SESSION['id_user'],$s3_enabled,$s3_url); ?>
                                    </select>
                                </div>
                            </div>
                            <div style="display: none" id="div_image_poweredby" class="col-md-12">
                                <img style="width: 100%" src="<?php echo $url_poweredby; ?>" />
                            </div>
                            <div style="display: none" id="div_delete_poweredby" class="col-md-12 mt-2">
                                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_poweredby();" class="btn btn-block btn-danger"><?php echo _("REMOVE LOGO"); ?></button>
                            </div>
                            <div style="display: none" class="col-md-12" id="div_upload_poweredby">
                                <?php if($upload_content) : ?>
                                    <form id="frm_pw" action="ajax/upload_logo_image.php" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="txtFile_pw" name="txtFile_pw" />
                                                        <label class="custom-file-label" for="txtFile_pw"><?php echo _("Choose file"); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <input <?php echo ($demo || $disabled_upload) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload_pw" value="<?php echo _("Upload Logo Image"); ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="preview text-center">
                                                    <div class="progress progress_pw mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                        <div class="progress-bar" id="progressBar_pw" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                            0%
                                                        </div>
                                                    </div>
                                                    <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error_pw"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div id="div_link_poweredby" class="col-md-12 mt-2">
                                <div class="form-group">
                                    <label for="poweredby_link_image"><?php echo _("Hyperlink"); ?></label>
                                    <input id="poweredby_link_image" type="text" class="form-control" value="<?php echo $virtual_tour['poweredby_link']; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 <?php echo $hide_external; ?>">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary d-inline-block"><i class="far fa-image"></i> <?php echo _("Nadir Logo"); ?> <i title="<?php echo _("logo used to hide tripod on panorama image (size can be changed in the editor UI)"); ?>" class="help_t fas fa-question-circle"></i></h6>
                        <div class="form-group mb-0 d-inline-block float-right">
                            <label class="mb-0" for="nadir_round"><?php echo _("Round"); ?> <input onchange="change_nadir_round();" id="nadir_round" type="checkbox" <?php echo ($virtual_tour['nadir_round']) ? 'checked' : ''; ?> /></label>
                        </div>
                    </div>
                    <div class="card-body <?php echo (!$plan_permissions['enable_nadir_logo']) ? 'disabled' : '' ; ?>">
                        <div class="row">
                            <div id="div_exist_nadir_logo" class="col-md-12">
                                <div class="form-group">
                                    <select onchange="change_exist_nadir_logo();" class="form-control" id="exist_nadir_logo">
                                        <option selected id="0"><?php echo _("Upload new Nadir Logo"); ?></option>
                                        <?php echo get_option_exist_nadir_logo($_SESSION['id_user'],$s3_enabled,$s3_url); ?>
                                    </select>
                                </div>
                            </div>
                            <div style="display: none" id="div_image_nadir_logo" class="col-md-12 text-center">
                                <img class="<?php echo ($virtual_tour['nadir_round']) ? 'nadir_round' : ''; ?>" style="width: 100%;max-width: 150px" src="<?php echo $url_nadir_logo; ?>" />
                            </div>
                            <div style="display: none" id="div_delete_nadir_logo" class="col-md-12 mt-2">
                                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_nadir_logo();" class="btn btn-block btn-danger"><?php echo _("REMOVE LOGO"); ?></button>
                            </div>
                            <div style="display: none" class="col-md-12" id="div_upload_nadir_logo">
                                <?php if($upload_content) : ?>
                                    <form id="frm_n" action="ajax/upload_logo_nadir_image.php" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="txtFile_n" name="txtFile_n" />
                                                        <label class="custom-file-label" for="txtFile_n"><?php echo _("Choose file"); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <input <?php echo ($demo || $disabled_upload) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload_n" value="<?php echo _("Upload Logo Image"); ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="preview text-center">
                                                    <div class="progress progress_n mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                        <div class="progress-bar" id="progressBar_n" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                            0%
                                                        </div>
                                                    </div>
                                                    <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error_n"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 <?php echo $hide_external; ?>">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-music"></i> <?php echo _("Song"); ?> <i title="<?php echo _("background song during navigation of virtual tour"); ?>" class="help_t fas fa-question-circle"></i> <i style="font-size:12px;vertical-align:middle;color:<?php echo ($show_in_ui_audio>0)?'green':'orange'; ?>" <?php echo ($show_in_ui_audio==0)?'title="'._("Not visible in the tour, enable it in the Editor UI").'"':''; ?> class="<?php echo ($show_in_ui_audio==0)?'help_t':''; ?> show_in_ui fas fa-circle"></i></h6>
                    </div>
                    <div class="card-body <?php echo (!$plan_permissions['enable_song']) ? 'disabled' : '' ; ?>">
                        <div class="row">
                            <div id="div_exist_song" class="col-md-12">
                                <div class="form-group">
                                    <select onchange="change_exist_song();" class="form-control" id="exist_song">
                                        <option selected id="0"><?php echo _("Upload new Song"); ?></option>
                                        <?php echo get_option_exist_song($_SESSION['id_user'],$id_virtual_tour,null); ?>
                                    </select>
                                </div>
                            </div>
                            <div style="display: none" id="div_player_song" class="col-md-12 text-center">
                                <audio controls>
                                    <source src="<?php echo $url_song; ?>" type="audio/mpeg">
                                    Your browser does not support the audio element.
                                </audio>
                            </div>
                            <div style="display: none" id="div_delete_song" class="mt-2">
                                <div class="col-md-12">
                                    <button onclick="delete_song();return false;" id="btn_delete_song" class="btn btn-block btn-danger"><?php echo _("REMOVE SONG"); ?></button>
                                </div>
                            </div>
                            <div style="display: none" id="div_upload_song" class="col-md-12">
                                <?php if($upload_content) : ?>
                                    <form id="frm" action="ajax/upload_song.php" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="txtFile" name="txtFile" />
                                                        <label class="custom-file-label" for="txtFile"><?php echo _("Choose file"); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <input <?php echo ($demo || $disabled_upload) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload" value="<?php echo _("Upload Song (MP3)"); ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="preview text-center">
                                                    <div class="progress progress_s mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                        <div class="progress-bar" id="progressBar" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                            0%
                                                        </div>
                                                    </div>
                                                    <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div id="div_song_bg_volume" class="col-md-12 mt-2">
                                <div class="form-group">
                                    <label for="song_bg_volume"><?php echo _("Volume"); ?> (<span id="song_bg_volume_value"><?php echo $virtual_tour['song_bg_volume']*100; ?>%</span>)</label>
                                    <input oninput="change_song_bg_volume();" min="0" max="1" step="0.1" id="song_bg_volume" type="range" class="form-control-range" value="<?php echo $virtual_tour['song_bg_volume']; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 <?php echo $hide_external; ?>">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="far fa-image"></i> <?php echo _("Intro (Desktop)"); ?> <i title="<?php echo _("image displayed on desktop at first load"); ?>" class="help_t fas fa-question-circle"></i><span style="vertical-align:top;height:14px;" class="float-right"><?php echo print_language_input_selector($array_languages,$default_language,'intro_desktop'); ?></span></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div id="div_exist_introd" class="col-md-12">
                                <div class="form-group">
                                    <select onchange="change_exist_introd('');" class="form-control" id="exist_introd">
                                        <option selected id="0"><?php echo _("Upload new Image"); ?></option>
                                        <?php echo get_option_exist_introd($_SESSION['id_user'],$s3_enabled,$s3_url); ?>
                                    </select>
                                </div>
                            </div>
                            <div style="display: none" id="div_image_introd" class="col-md-12">
                                <img style="width: 100%" src="<?php echo $url_intro_desktop; ?>" />
                            </div>
                            <?php foreach ($array_languages as $lang) {
                                if($lang!=$default_language) : ?>
                                    <div style="display:none;" id="div_exist_introd_<?php echo $lang; ?>" class="col-md-12 input_lang" data-target-id="div_exist_introd" data-lang="<?php echo $lang; ?>">
                                        <div class="form-group">
                                            <select onchange="change_exist_introd('<?php echo $lang; ?>');" class="form-control" id="exist_introd_<?php echo $lang; ?>">
                                                <option selected id="0"><?php echo _("Upload new Image"); ?></option>
                                                <?php echo get_option_exist_introd($_SESSION['id_user'],$s3_enabled,$s3_url); ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div style="display: none" id="div_image_introd_<?php echo $lang; ?>" class="col-md-12 input_lang" data-target-id="div_image_introd" data-lang="<?php echo $lang; ?>">
                                        <input id="introd_file_<?php echo $lang; ?>" type="hidden" value="<?php echo $array_input_lang[$lang]['intro_desktop']; ?>" />
                                        <img style="width: 100%" src="<?php echo (!empty($array_input_lang[$lang]['intro_desktop'])) ? $path_base_url."content/".$array_input_lang[$lang]['intro_desktop'] : ''; ?>" />
                                    </div>
                                <?php endif;
                            } ?>
                            <div style="display: none" id="div_delete_introd" class="col-md-12 mt-2">
                                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_introd();" class="btn btn-block btn-danger"><?php echo _("REMOVE IMAGE"); ?></button>
                            </div>
                            <div style="display: none" class="col-md-12" id="div_upload_introd">
                                <?php if($upload_content) : ?>
                                    <form id="frm_id" action="ajax/upload_intro_image.php" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="txtFile_id" name="txtFile_id" />
                                                        <label class="custom-file-label" for="txtFile_id"><?php echo _("Choose file"); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <input <?php echo ($demo || $disabled_upload) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload_id" value="<?php echo _("Upload Image"); ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="preview text-center">
                                                    <div class="progress progress_id mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                        <div class="progress-bar" id="progressBar_id" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                            0%
                                                        </div>
                                                    </div>
                                                    <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error_id"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div id="div_hide_introd" class="col-md-12 mt-2">
                                <div class="form-group">
                                    <label for="intro_desktop_hide"><?php echo _("Auto hide after"); ?> <i title="<?php echo _("set to 0 to not hide it"); ?>" class="help_t fas fa-question-circle"></i></label>
                                    <div class="input-group">
                                        <input type="number" min="0" class="form-control" id="intro_desktop_hide" value="<?php echo $virtual_tour['intro_desktop_hide']; ?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text">s</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 <?php echo $hide_external; ?>">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="far fa-image"></i> <?php echo _("Intro (Mobile)"); ?> <i title="<?php echo _("image displayed on mobile at first load"); ?>" class="help_t fas fa-question-circle"></i><span style="vertical-align:top;height:14px;" class="float-right"><?php echo print_language_input_selector($array_languages,$default_language,'intro_mobile'); ?></span></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div id="div_exist_introm" class="col-md-12">
                                <div class="form-group">
                                    <select onchange="change_exist_introm();" class="form-control" id="exist_introm">
                                        <option selected id="0"><?php echo _("Upload new Image"); ?></option>
                                        <?php echo get_option_exist_introm($_SESSION['id_user'],$s3_enabled,$s3_url); ?>
                                    </select>
                                </div>
                            </div>
                            <div style="display: none" id="div_image_introm" class="col-md-12">
                                <img style="width: 100%" src="<?php echo $url_intro_mobile; ?>" />
                            </div>
                            <?php foreach ($array_languages as $lang) {
                                if($lang!=$default_language) : ?>
                                    <div style="display:none;" id="div_exist_introm_<?php echo $lang; ?>" class="col-md-12 input_lang" data-target-id="div_exist_introm" data-lang="<?php echo $lang; ?>">
                                        <div class="form-group">
                                            <select onchange="change_exist_introm('<?php echo $lang; ?>');" class="form-control" id="exist_introm_<?php echo $lang; ?>">
                                                <option selected id="0"><?php echo _("Upload new Image"); ?></option>
                                                <?php echo get_option_exist_introm($_SESSION['id_user'],$s3_enabled,$s3_url); ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div style="display: none" id="div_image_introm_<?php echo $lang; ?>" class="col-md-12 input_lang" data-target-id="div_image_introm" data-lang="<?php echo $lang; ?>">
                                        <input id="introm_file_<?php echo $lang; ?>" type="hidden" value="<?php echo $array_input_lang[$lang]['intro_mobile']; ?>" />
                                        <img style="width: 100%" src="<?php echo (!empty($array_input_lang[$lang]['intro_mobile'])) ? $path_base_url."content/".$array_input_lang[$lang]['intro_mobile'] : ''; ?>" />
                                    </div>
                                <?php endif;
                            } ?>
                            <div style="display: none" id="div_delete_introm" class="col-md-12 mt-2">
                                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_introm();" class="btn btn-block btn-danger"><?php echo _("REMOVE IMAGE"); ?></button>
                            </div>
                            <div style="display: none" class="col-md-12" id="div_upload_introm">
                                <?php if($upload_content) : ?>
                                    <form id="frm_im" action="ajax/upload_intro_image.php" method="POST" enctype="multipart/form-data">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="input-group">
                                                    <div class="custom-file">
                                                        <input type="file" class="custom-file-input" id="txtFile_im" name="txtFile_im" />
                                                        <label class="custom-file-label" for="txtFile_im"><?php echo _("Choose file"); ?></label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="form-group">
                                                    <input <?php echo ($demo || $disabled_upload) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload_im" value="<?php echo _("Upload Image"); ?>" />
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <div class="preview text-center">
                                                    <div class="progress progress_im mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                        <div class="progress-bar" id="progressBar_im" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                            0%
                                                        </div>
                                                    </div>
                                                    <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error_im"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <div id="div_hide_introm" class="col-md-12 mt-2">
                                <div class="form-group">
                                    <label for="intro_mobile_hide"><?php echo _("Auto hide after"); ?> <i title="<?php echo _("set to 0 to not hide it"); ?>" class="help_t fas fa-question-circle"></i></label>
                                    <div class="input-group">
                                        <input type="number" min="0" class="form-control" id="intro_mobile_hide" value="<?php echo $virtual_tour['intro_mobile_hide']; ?>">
                                        <div class="input-group-append">
                                            <span class="input-group-text">s</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 <?php echo $hide_external; ?>">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card shadow mb-4">
                            <div class="card-header py-3">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-video"></i> <?php echo _("Avatar Video"); ?> <i title="<?php echo _("video of an avatar displayed over the tour"); ?>" class="help_t fas fa-question-circle"></i> <i style="font-size:12px;vertical-align:middle;color:<?php echo ($show_in_ui_avatar_video>0)?'green':'orange'; ?>" <?php echo ($show_in_ui_avatar_video==0)?'title="'._("Not visible in the tour, enable it in the Editor UI").'"':''; ?> class="<?php echo ($show_in_ui_logo==0)?'help_t':''; ?> show_in_ui fas fa-circle"></i><span style="vertical-align:top;height:14px;" class="float-right"><?php echo print_language_input_selector($array_languages,$default_language,'avatar_video'); ?></span></h6>
                            </div>
                            <div class="card-body <?php echo (!$plan_permissions['enable_avatar_video']) ? 'disabled' : '' ; ?>">
                                <div class="row">
                                    <div style="display: block" class="col-md-12" id="div_upload_avatar_video">
                                        <?php if($upload_content) : ?>
                                            <form id="frm_av" action="ajax/upload_content_video.php?e=webm_mov" method="POST" enctype="multipart/form-data">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="input-group">
                                                            <div class="custom-file">
                                                                <input type="file" class="custom-file-input" id="txtFile_av" name="txtFile_av" />
                                                                <label class="custom-file-label" for="txtFile_av"><?php echo _("Choose file"); ?></label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <input <?php echo ($demo || $disabled_upload) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload_av" value="<?php echo _("Upload Video (MOV + WEBM)"); ?>" />
                                                        </div>
                                                    </div>
                                                    <div class="col-md-12">
                                                        <div class="preview text-center">
                                                            <div class="progress progress_av mb-3 mb-sm-3" style="height: 2.35rem;display: none">
                                                                <div class="progress-bar" id="progressBar_av" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                                    0%
                                                                </div>
                                                            </div>
                                                            <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error_av"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        <?php endif; ?>
                                        <div id="div_avatar_video_extensions" class="row">
                                            <div class="col-md-6 text-center">
                                                MOV <i id="mov_uploaded" style="color:<?php echo (empty($mov_video)) ? 'orange' : 'green'; ?>" class="fas fa-circle"></i>
                                            </div>
                                            <div class="col-md-6 text-center">
                                                WEBM <i id="webm_uploaded" style="color:<?php echo (empty($webm_video)) ? 'orange' : 'green'; ?>" class="fas fa-circle"></i>
                                            </div>
                                        </div>
                                        <?php foreach ($array_languages as $lang) {
                                            if($lang!=$default_language) : ?>
                                                <div style="display: none" id="div_avatar_video_extensions_<?php echo $lang; ?>" class="row input_lang" data-target-id="div_avatar_video_extensions" data-lang="<?php echo $lang; ?>">
                                                    <div class="col-md-6 text-center">
                                                        MOV <i id="mov_uploaded_<?php echo $lang; ?>" style="color:<?php echo (empty($array_input_lang[$lang]['mov_video'])) ? 'orange' : 'green'; ?>" class="fas fa-circle"></i>
                                                    </div>
                                                    <div class="col-md-6 text-center">
                                                        WEBM <i id="webm_uploaded_<?php echo $lang; ?>" style="color:<?php echo (empty($array_input_lang[$lang]['webm_video'])) ? 'orange' : 'green'; ?>" class="fas fa-circle"></i>
                                                    </div>
                                                </div>
                                            <?php endif;
                                        } ?>
                                    </div>
                                    <div class="col-md-12 mt-2 text-center">
                                        <label><input id="avatar_video_autoplay" <?php echo ($virtual_tour['avatar_video_autoplay']==1) ? 'checked' : ''; ?> type="checkbox" />&nbsp;&nbsp;<?php echo _("autoplay"); ?></label>&nbsp;&nbsp;
                                        <label><input id="avatar_video_pause" <?php echo ($virtual_tour['avatar_video_pause']==1) ? 'checked' : ''; ?> type="checkbox" />&nbsp;&nbsp;<?php echo _("pause"); ?></label>&nbsp;&nbsp;
                                        <label><input id="avatar_video_hide_end" <?php echo ($virtual_tour['avatar_video_hide_end']==1) ? 'checked' : ''; ?> type="checkbox" />&nbsp;&nbsp;<?php echo _("hide when ends"); ?></label>
                                    </div>
                                    <input id="avatar_video_content" type="hidden" value="<?php echo $virtual_tour['avatar_video']; ?>" />
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <input id="avatar_video_content_<?php echo $lang; ?>" class="input_lang" data-target-id="avatar_video_content" data-lang="<?php echo $lang; ?>" type="hidden" value="<?php echo $array_input_lang[$lang]['avatar_video']; ?>" />
                                        <?php endif;
                                    } ?>
                                    <div style="display: none" id="div_avatar_video_preview" class="col-md-12 mt-2">
                                        <video playsinline webkit-playsinline controls preload="auto" src="<?php echo $url_avatar_video; ?>"></video>
                                    </div>
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <div style="display: none" id="div_avatar_video_preview_<?php echo $lang; ?>" data-target-id="div_avatar_video_preview" data-lang="<?php echo $lang; ?>" class="col-md-12 mt-2 input_lang div_avatar_video_preview">
                                                <video playsinline webkit-playsinline controls preload="auto" src="<?php echo $array_input_lang[$lang]['url_avatar_video']; ?>"></video>
                                            </div>
                                        <?php endif;
                                    } ?>
                                    <div style="display: none" id="div_delete_avatar_video" class="col-md-12 mt-2">
                                        <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_avatar_video();" class="btn btn-block btn-danger"><?php echo _("REMOVE VIDEO"); ?></button>
                                    </div>
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <div style="display: none" id="div_delete_avatar_video_<?php echo $lang; ?>" data-target-id="div_delete_avatar_video" data-lang="<?php echo $lang; ?>" class="col-md-12 mt-2 input_lang">
                                                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_avatar_video();" class="btn btn-block btn-danger"><?php echo _("REMOVE VIDEO"); ?></button>
                                            </div>
                                        <?php endif;
                                    } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-8 <?php echo $hide_external; ?> <?php echo ($custom_html==0) ? 'd-none' : ''; ?>">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fab fa-html5"></i> <?php echo _("Custom HTML"); ?> <i title="<?php echo _("html code that will be displayed within the tour"); ?>" class="help_t fas fa-question-circle"></i></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div style="width:100%;" class="form-group">
                                <div id="custom_vt_html"><?php echo htmlspecialchars(str_replace('\"','"',$virtual_tour['custom_html'])); ?></div>
                                <div class="mt-1 text-right">
                                    <button onclick="open_modal_media_library('all','html_vt');return false;" class="btn btn-sm btn-primary"><?php echo _("Media Library"); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="form_tab">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-file-signature"></i> <?php echo _("Main Form"); ?> <i style="font-size:12px;vertical-align:middle;color:<?php echo ($show_in_ui_form>0)?'green':'orange'; ?>" <?php echo ($show_in_ui_form==0)?'title="'._("Not visible in the tour, enable it in the Editor UI").'"':''; ?> class="<?php echo ($show_in_ui_form==0)?'help_t':''; ?> show_in_ui fas fa-circle"></i></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div style="margin-bottom: 3px" class="form-group">
                                    <label style="margin-bottom: 1px" for="form_title"><?php echo _("Title"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'form_title'); ?>
                                    <input id="form_title" type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($form_content[0]['title']); ?>">
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <input style="display:none;" type="text" class="form-control form-control-sm input_lang" data-target-id="form_title" data-lang="<?php echo $lang; ?>" value="<?php echo htmlspecialchars($array_input_lang[$lang]['form_content'][0]['title']); ?>" />
                                        <?php endif;
                                    } ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div style="margin-bottom: 3px" class="form-group">
                                    <label style="margin-bottom: 1px" for="form_button"><?php echo _("Button send"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'form_button'); ?>
                                    <input id="form_button" type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($form_content[0]['button']); ?>">
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <input style="display:none;" type="text" class="form-control form-control-sm input_lang" data-target-id="form_button" data-lang="<?php echo $lang; ?>" value="<?php echo htmlspecialchars($array_input_lang[$lang]['form_content'][0]['button']); ?>" />
                                        <?php endif;
                                    } ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div style="margin-bottom: 3px" class="form-group">
                                    <label style="margin-bottom: 1px" for="form_response"><?php echo _("Reply message"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'form_response'); ?>
                                    <input id="form_response" type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($form_content[0]['response']); ?>">
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <input style="display:none;" type="text" class="form-control form-control-sm input_lang" data-target-id="form_response" data-lang="<?php echo $lang; ?>" value="<?php echo htmlspecialchars($array_input_lang[$lang]['form_content'][0]['response']); ?>" />
                                        <?php endif;
                                    } ?>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div style="margin-bottom: 3px" class="form-group">
                                    <label style="margin-bottom: 1px" for="form_description"><?php echo _("Description"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'form_description'); ?>
                                    <input id="form_description" type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($form_content[0]['description']); ?>">
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <input style="display:none;" type="text" class="form-control form-control-sm input_lang" data-target-id="form_description" data-lang="<?php echo $lang; ?>" value="<?php echo htmlspecialchars($array_input_lang[$lang]['form_content'][0]['description']); ?>" />
                                        <?php endif;
                                    } ?>
                                </div>
                            </div>
                            <div class="col-md-4 <?php echo (empty($settings['privacy_policy'])) ? 'd-none':''; ?>">
                                <div class="form-group">
                                    <label style="margin-bottom: 1px"><?php echo _("Show Privacy Policy"); ?></label><br>
                                    <input <?php echo ($form_content[0]['privacy_policy'])?'checked':''; ?> id="form_privacy_policy" type="checkbox">
                                </div>
                            </div>
                            <div class="col-md-4 <?php echo (!$settings['smtp_valid']) ? 'd-none':''; ?>">
                                <div class="form-group">
                                    <label style="margin-bottom: 1px"><?php echo _("Send Notification"); ?></label><br>
                                    <input <?php echo ($form_content[0]['send_email'])?'checked':''; ?> id="form_send_email" type="checkbox">
                                </div>
                            </div>
                            <div class="col-md-4 <?php echo (!$settings['smtp_valid']) ? 'd-none':''; ?>">
                                <div style="margin-bottom: 3px" class="form-group">
                                    <label style="margin-bottom: 1px" for="form_email"><?php echo _("E-Mail"); ?></label>
                                    <input id="form_email" type="email" class="form-control form-control-sm" value="<?php echo $form_content[0]['email']; ?>">
                                </div>
                            </div>
                        </div>
                        <hr style="margin: 3px">
                        <div class="row">
                            <div class="col-md-3">
                                <div style="margin-bottom: 3px" class="form-group">
                                    <label style="margin-bottom: 1px"><?php echo _("Background Color"); ?></label>
                                    <input id="form_background_m" type="text" class="form-control form-control-sm" value="<?php echo (isset($form_content[0]['background'])) ? $form_content[0]['background'] : 'rgba(255,255,255,1.0)'; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div style="margin-bottom: 3px" class="form-group">
                                    <label style="margin-bottom: 1px"><?php echo _("Text Color"); ?></label>
                                    <input id="form_color_m" type="text" class="form-control form-control-sm" value="<?php echo (isset($form_content[0]['color'])) ? $form_content[0]['color'] : '#000000'; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div style="margin-bottom: 3px" class="form-group">
                                    <label style="margin-bottom: 1px"><?php echo _("Button - Background Color"); ?></label>
                                    <input id="form_background_button" type="text" class="form-control form-control-sm" value="<?php echo (isset($form_content[0]['background_button'])) ? $form_content[0]['background_button'] : '#000000'; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div style="margin-bottom: 3px" class="form-group">
                                    <label style="margin-bottom: 1px"><?php echo _("Button - Text Color"); ?></label>
                                    <input id="form_color_button" type="text" class="form-control form-control-sm" value="<?php echo (isset($form_content[0]['color_button'])) ? $form_content[0]['color_button'] : '#ffffff'; ?>">
                                </div>
                            </div>
                        </div>
                        <hr style="margin: 3px">
                        <?php for($i=1;$i<=10;$i++) { ?>
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label style="margin-bottom: 1px">F.<?php echo $i; ?> <?php echo _("Enable"); ?></label><br>
                                        <input <?php echo ($form_content[$i]['enabled'])?'checked':''; ?> id="form_field_<?php echo $i; ?>" type="checkbox">
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label style="margin-bottom: 1px">F.<?php echo $i; ?> <?php echo _("Required"); ?></label><br>
                                        <input <?php echo ($form_content[$i]['required'])?'checked':''; ?> id="form_field_required_<?php echo $i; ?>" type="checkbox">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div style="margin-bottom: 3px" class="form-group">
                                        <label style="margin-bottom: 1px">F.<?php echo $i; ?> <?php echo _("Type"); ?></label><br>
                                        <select onchange="change_form_field_type(<?php echo $i; ?>);" id="form_field_type_<?php echo $i; ?>" class="form-control form-control-sm">
                                            <option <?php echo ($form_content[$i]['type']=='text')?'selected':''; ?> id="text" value="text"><?php echo _("Text"); ?></option>
                                            <option <?php echo ($form_content[$i]['type']=='textarea')?'selected':''; ?> id="textarea" value="textarea"><?php echo _("Text (multiple lines)"); ?></option>
                                            <option <?php echo ($form_content[$i]['type']=='number')?'selected':''; ?> id="number" value="number"><?php echo _("Number"); ?></option>
                                            <option <?php echo ($form_content[$i]['type']=='tel')?'selected':''; ?> id="tel" value="tel"><?php echo _("Phone"); ?></option>
                                            <option <?php echo ($form_content[$i]['type']=='email')?'selected':''; ?> id="email" value="email"><?php echo _("E-Mail"); ?></option>
                                            <option <?php echo ($form_content[$i]['type']=='select')?'selected':''; ?> id="select" value="select"><?php echo _("Select"); ?></option>
                                            <option <?php echo ($form_content[$i]['type']=='checkbox')?'selected':''; ?> id="checkbox" value="checkbox"><?php echo _("Checkbox"); ?></option>
                                            <option <?php echo ($form_content[$i]['type']=='date')?'selected':''; ?> id="date" value="date"><?php echo _("Date"); ?></option>
                                            <option <?php echo ($form_content[$i]['type']=='time')?'selected':''; ?> id="time" value="time"><?php echo _("Time"); ?></option>
                                            <option <?php echo ($form_content[$i]['type']=='file')?'selected':''; ?> id="file" value="time"><?php echo _("File Upload"); ?></option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div style="margin-bottom: 3px" class="form-group">
                                        <label style="margin-bottom: 1px">F.<?php echo $i; ?> <?php echo _("Label"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'form_field_label_'.$i); ?><br>
                                        <input id="form_field_label_<?php echo $i; ?>" type="text" class="form-control form-control-sm" value="<?php echo htmlspecialchars($form_content[$i]['label']); ?>">
                                        <?php foreach ($array_languages as $lang) {
                                            if($lang!=$default_language) : ?>
                                                <input style="display:none;" type="text" class="form-control form-control-sm input_lang" data-target-id="form_field_label_<?php echo $i; ?>" data-lang="<?php echo $lang; ?>" value="<?php echo htmlspecialchars($array_input_lang[$lang]['form_content'][$i]['label']); ?>" />
                                            <?php endif;
                                        } ?>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="hfov_tab">
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-ruler-horizontal"></i> <?php echo _("Field of View"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name"><?php echo _("Default"); ?> <i title="<?php echo _("sets the panorama’s starting horizontal field of view in degrees."); ?>" class="help_t fas fa-question-circle"></i></label>
                                    <input disabled type="number" min="20" max="140" class="form-control" id="hfov" value="<?php echo $virtual_tour['hfov']; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name"><?php echo _("Min"); ?> <i title="<?php echo _("sets the minimum pitch the viewer edge can be at, in degrees."); ?>" class="help_t fas fa-question-circle"></i></label>
                                    <input disabled type="number" min="20" max="140" class="form-control" id="min_hfov" value="<?php echo $virtual_tour['min_hfov']; ?>">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="name"><?php echo _("Max"); ?> <i title="<?php echo _("sets the maximum pitch the viewer edge can be at, in degrees."); ?>" class="help_t fas fa-question-circle"></i></label>
                                    <input disabled type="number" min="20" max="140" class="form-control" id="max_hfov" value="<?php echo $virtual_tour['max_hfov']; ?>">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="hfov_mobile_ratio"><?php echo _("HFOV Mobile Ratio"); ?> (<span id="hfov_mobile_ratio_val"><?php echo $virtual_tour['hfov_mobile_ratio']; ?></span>) <i title="<?php echo _("a lower ratio indicates a wider view on the mobile, while a higher value indicates a narrower view"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input oninput="change_hfov_mobile_ratio();" type="range" min="0.5" max="1.5" step="0.1" class="form-control-range" id="hfov_mobile_ratio" value="<?php echo $virtual_tour['hfov_mobile_ratio']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="zoom_friction"><?php echo _("Zoom Speed"); ?> (<span id="zoom_friction_val"><?php echo $virtual_tour['zoom_friction']; ?></span>) <i title="<?php echo _("controls the zoom speed. higher values mean faster zoom."); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input oninput="change_zoom_friction();" type="range" min="0.01" max="0.2" step="0.01" class="form-control-range" id="zoom_friction" value="<?php echo $virtual_tour['zoom_friction']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="zoom_friction_mobile"><?php echo _("Zoom Speed Mobile"); ?> (<span id="zoom_friction_mobile_val"><?php echo $virtual_tour['zoom_friction_mobile']; ?></span>) <i title="<?php echo _("controls the zoom speed. higher values mean faster zoom."); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input oninput="change_zoom_friction_mobile();" type="range" min="0.01" max="0.2" step="0.01" class="form-control-range" id="zoom_friction_mobile" value="<?php echo $virtual_tour['zoom_friction_mobile']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mouse_zoom"><?php echo _("Zoom"); ?></label><br>
                                    <select id="mouse_zoom" class="form-control form-control-sm">
                                        <option <?php echo ($virtual_tour['mouse_zoom']==0) ? 'selected':''; ?> id="0"><?php echo _("Disabled"); ?></option>
                                        <option <?php echo ($virtual_tour['mouse_zoom']==1) ? 'selected':''; ?> id="1"><?php echo _("Enabled"); ?></option>
                                        <option <?php echo ($virtual_tour['mouse_zoom']==2) ? 'selected':''; ?> id="2"><?php echo _("Enabled (Disabled when embedded)"); ?></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="far fa-hand-point-up"></i> <?php echo _("Interaction"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pan_speed"><?php echo _("Pan Speed"); ?> (<span id="pan_speed_val"><?php echo $virtual_tour['pan_speed']; ?></span>) <i title="<?php echo _("adjusts panning speed from touch inputs: a lower value indicates a slower pan speed, while a higher value indicates a faster pan speed"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input oninput="change_pan_speed();" type="range" min="0.1" max="3" step="0.1" class="form-control-range" id="pan_speed" value="<?php echo $virtual_tour['pan_speed']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="pan_speed_mobile"><?php echo _("Pan Speed Mobile"); ?> (<span id="pan_speed_mobile_val"><?php echo $virtual_tour['pan_speed_mobile']; ?></span>) <i title="<?php echo _("adjusts panning speed from touch inputs: a lower value indicates a slower pan speed, while a higher value indicates a faster pan speed"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input oninput="change_pan_speed_mobile();" type="range" min="0.1" max="3" step="0.1" class="form-control-range" id="pan_speed_mobile" value="<?php echo $virtual_tour['pan_speed_mobile']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="friction"><?php echo _("Friction"); ?> (<span id="friction_val"><?php echo $virtual_tour['friction']; ?></span>) <i title="<?php echo _("controls the friction that slows down the viewer motion after it is dragged and released. higher values mean the motion stops faster."); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input oninput="change_friction();" type="range" min="0.1" max="1" step="0.1" class="form-control-range" id="friction" value="<?php echo $virtual_tour['friction']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="friction_mobile"><?php echo _("Friction Mobile"); ?> (<span id="friction_mobile_val"><?php echo $virtual_tour['friction_mobile']; ?></span>) <i title="<?php echo _("controls the friction that slows down the viewer motion after it is dragged and released. higher values mean the motion stops faster."); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input oninput="change_friction_mobile();" type="range" min="0.1" max="1" step="0.1" class="form-control-range" id="friction_mobile" value="<?php echo $virtual_tour['friction_mobile']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="mouse_follow_feedback"><?php echo _("Feedback Mouse Movements"); ?> (<span id="mouse_follow_feedback_val"><?php echo $virtual_tour['mouse_follow_feedback']; ?></span>) <i title="<?php echo _("sensitivity of the movement of the panorama following the mouse (0 to disable)"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input oninput="change_mouse_follow_feedback();" type="range" min="0.0" max="3.0" step="0.1" class="form-control-range" id="mouse_follow_feedback" value="<?php echo $virtual_tour['mouse_follow_feedback']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="zoom_to_pointer"><?php echo _("Zoom to Pointer"); ?> <i title="<?php echo _("sets the zoom center on the mouse pointer when using the scroll wheel"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input onclick="change_zoom_to_pointer();" type="checkbox" id="zoom_to_pointer" <?php echo ($virtual_tour['zoom_to_pointer']==1) ? 'checked':''; ?>>
                                </div>
                            </div>
                            <div class="col-md-6 <?php echo $hide_external; ?>">
                                <div class="form-group">
                                    <label for="initial_feedback"><?php echo _("Initial Feedback Animation"); ?> <i title="<?php echo _("an animation at the start of the tour for feedback on the dragging of the panorama (0 = disabled)."); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <div class="input-group input-group-sm">
                                        <input type="number" min="0" class="form-control form-control-sm" id="initial_feedback" value="<?php echo $virtual_tour['initial_feedback']; ?>" />
                                        <div class="input-group-append">
                                            <span class="input-group-text">ms</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-binoculars"></i> <?php echo _("Preview"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8 text-center">
                                <label><?php echo _("Desktop"); ?></label>
                                <div style="width:100%;max-width:622px;height:350px;margin:0 auto;" id="panorama"></div>
                                <div class="mt-2" style="width: 100%;">
                                    <?php echo _("Current HFOV"); ?> <b><span id="hvof_debug"><?php echo $virtual_tour['hfov']; ?></span></b><br>
                                    <i><?php echo _("use the mouse wheel or the controls to zoom"); ?></i>
                                </div>
                            </div>
                            <div class="col-md-4 text-center">
                                <label><?php echo _("Mobile"); ?></label>
                                <div style="width:100%;max-width:200px;height:350px;margin:0 auto;" id="panorama_mobile"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade <?php echo ($user_info['role']!='administrator') ? 'd-none' : ''; ?>" id="note_tab">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="far fa-sticky-note"></i> <?php echo _("Note (only visible to administrators)"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <textarea class="form-control" id="note" rows="10"><?php echo $virtual_tour['note']; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade <?php echo ($user_info['role']!='administrator') ? 'd-none' : ''; ?>" id="editors_tab">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="far fa-users-cog"></i> <?php echo _("Assigned Editors (only visible to administrators)"); ?>
                            <span id="btn_unassign_all" onclick="unassign_all_editor_to_tour();" class="badge badge-danger float-right ml-2 <?php echo ($demo) ? 'disabled_d':''; ?> disabled"><?php echo _("Unassign all editors"); ?></span>
                            <span id="btn_assign_all" onclick="assign_all_editor_to_tour();" class="badge badge-primary float-right <?php echo ($demo) ? 'disabled_d':''; ?> disabled"><?php echo _("Assign all editors / permissions"); ?></span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-bordered table-hover" id="assign_editors_table" width="100%" cellspacing="0">
                                    <thead>
                                    <tr>
                                        <th><?php echo _("Assign"); ?></th>
                                        <th style="min-width: 350px"><?php echo _("User"); ?></th>
                                        <th><?php echo _("Edit Tour"); ?></th>
                                        <th><?php echo _("Editor UI"); ?></th>
                                        <th><?php echo _("Create Rooms"); ?></th>
                                        <th><?php echo _("Edit Rooms"); ?></th>
                                        <th><?php echo _("Delete Rooms"); ?></th>
                                        <th><?php echo _("Create Markers"); ?></th>
                                        <th><?php echo _("Edit Markers"); ?></th>
                                        <th><?php echo _("Delete Markers"); ?></th>
                                        <th><?php echo _("Create POIs"); ?></th>
                                        <th><?php echo _("Edit POIs"); ?></th>
                                        <th><?php echo _("Delete POIs"); ?></th>
                                        <th><?php echo _("Create Maps"); ?></th>
                                        <th><?php echo _("Edit Maps"); ?></th>
                                        <th><?php echo _("Delete Maps"); ?></th>
                                        <th><?php echo _("Info Box"); ?></th>
                                        <th><?php echo _("Presentation"); ?></th>
                                        <th><?php echo _("Gallery"); ?></th>
                                        <th><?php echo _("Icons Library"); ?></th>
                                        <th><?php echo _("Media Library"); ?></th>
                                        <th><?php echo _("Music Library"); ?></th>
                                        <th><?php echo _("Sound Library"); ?></th>
                                        <th><?php echo _("Publish"); ?></th>
                                        <th><?php echo _("Landing"); ?></th>
                                        <th><?php echo _("Forms"); ?></th>
                                        <th><?php echo _("Leads"); ?></th>
                                        <th><?php echo _("Shop"); ?></th>
                                        <th><?php echo _("3D View"); ?></th>
                                        <th><?php echo _("360 Video"); ?></th>
                                        <th><?php echo _("Measurements"); ?></th>
                                        <th><?php echo _("Video Projects"); ?></th>
                                        <th><?php echo _("Translate"); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody <?php echo ($demo) ? 'style="pointer-events:none"' : ''; ?>>

                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade <?php echo ($learning==0) ? 'd-none' : ''; ?>" id="learning_tab">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-graduation-cap"></i> <?php echo _("Settings"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_mode"><?php echo _("Enable"); ?></label><br>
                                    <input type="checkbox" id="learning_mode" <?php echo ($virtual_tour['learning_mode'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_unlock_marker"><?php echo _("Unlock Marker"); ?> <i title="<?php echo _("markers in rooms are unlocked only when all POIs in them are visited"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="checkbox" id="learning_unlock_marker" <?php echo ($virtual_tour['learning_unlock_marker'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_poi_progressive"><?php echo _("Progressive POIs"); ?> <i title="<?php echo _("POIs are unlocked only when the previous one is visited. You can define the priority in the POI settings"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="checkbox" id="learning_poi_progressive" <?php echo ($virtual_tour['learning_poi_progressive'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_restore_session"><?php echo _("Use sessions"); ?> <i title="<?php echo _("allows to recover the learning session in subsequent visits to the tour"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input type="checkbox" id="learning_restore_session" <?php echo ($virtual_tour['learning_restore_session'])?'checked':''; ?> />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-palette"></i> <?php echo _("Style"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="learning_summary_style"><?php echo _("Size"); ?></label>
                                    <select id="learning_summary_style" class="form-control">
                                        <option <?php echo ($virtual_tour['learning_summary_style']=='default') ? 'selected' : ''; ?> id="default"><?php echo _("Default"); ?></option>
                                        <option <?php echo ($virtual_tour['learning_summary_style']=='minimal') ? 'selected' : ''; ?> id="minimal"><?php echo _("Compact"); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_summary_title"><?php echo _("Title"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'learning_summary_title'); ?>
                                    <input type="text" class="form-control" id="learning_summary_title" value="<?php echo $virtual_tour['learning_summary_title']; ?>" placeholder="<?php echo _("Learning Score"); ?>" />
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <input style="display:none;" type="text" class="form-control input_lang" data-target-id="learning_summary_title" data-lang="<?php echo $lang; ?>" value="<?php echo $array_input_lang[$lang]['learning_summary_title']; ?>" />
                                        <?php endif;
                                    } ?>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="learning_summary_background"><?php echo _("Background"); ?></label>
                                    <input type="text" class="form-control" id="learning_summary_background" value="<?php echo $virtual_tour['learning_summary_background']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="learning_summary_color"><?php echo _("Color"); ?></label>
                                    <input type="text" class="form-control" id="learning_summary_color" value="<?php echo $virtual_tour['learning_summary_color']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_modal_icon"><?php echo _("Icon"); ?></label><br>
                                    <div style="margin-bottom: 5px;" class="form-group">
                                        <button class="btn btn-sm btn-primary" type="button" id="GetIconPicker" data-iconpicker-input="input#learning_modal_icon" data-iconpicker-preview="i#learning_modal_icon_preview"><?php echo _("Select Icon"); ?></button>
                                        <input readonly type="hidden" id="learning_modal_icon" name="Icon" value="<?php echo $virtual_tour['learning_modal_icon']; ?>" required="" placeholder="" autocomplete="off" spellcheck="false">
                                        <div style="vertical-align: middle;width: 40px;" class="icon-preview d-inline-block ml-1" data-toggle="tooltip" title="">
                                            <i style="font-size: 24px;" id="learning_modal_icon_preview" class="<?php echo $virtual_tour['learning_modal_icon']; ?>"></i>
                                        </div>
                                        <button onclick='remove_learning_modal_icon();' class='btn btn-sm btn-danger <?php echo ((empty($virtual_tour['learning_modal_icon'])) ? 'disabled' : ''); ?> btn_delete_learning_modal_icon'><i class='fas fa-remove'></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_summary_partial_title"><?php echo _("Label - Partial score"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'learning_summary_partial_title'); ?>
                                    <input type="text" class="form-control" id="learning_summary_partial_title" value="<?php echo $virtual_tour['learning_summary_partial_title']; ?>" placeholder="<?php echo _("Partial"); ?>" />
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <input style="display:none;" type="text" class="form-control input_lang" data-target-id="learning_summary_partial_title" data-lang="<?php echo $lang; ?>" value="<?php echo $array_input_lang[$lang]['learning_summary_partial_title']; ?>" />
                                        <?php endif;
                                    } ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_summary_partial_color"><?php echo _("Color - Partial score"); ?></label>
                                    <input type="text" class="form-control" id="learning_summary_partial_color" value="<?php echo $virtual_tour['learning_summary_partial_color']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_summary_global_title"><?php echo _("Label - Global score"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'learning_summary_global_title'); ?>
                                    <input type="text" class="form-control" id="learning_summary_global_title" value="<?php echo $virtual_tour['learning_summary_global_title']; ?>" placeholder="<?php echo _("Global"); ?>" />
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <input style="display:none;" type="text" class="form-control input_lang" data-target-id="learning_summary_global_title" data-lang="<?php echo $lang; ?>" value="<?php echo $array_input_lang[$lang]['learning_summary_global_title']; ?>" />
                                        <?php endif;
                                    } ?>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_summary_global_color"><?php echo _("Color - Global score"); ?></label>
                                    <input type="text" class="form-control" id="learning_summary_global_color" value="<?php echo $virtual_tour['learning_summary_global_color']; ?>" />
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_check_background"><?php echo _("Background - Check Icon"); ?></label>
                                    <input type="text" class="form-control" id="learning_check_background" value="<?php echo $virtual_tour['learning_check_background']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_check_color"><?php echo _("Color - Check Icon"); ?></label>
                                    <input type="text" class="form-control" id="learning_check_color" value="<?php echo $virtual_tour['learning_check_color']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_check_icon"><?php echo _("Check Icon"); ?></label><br>
                                    <div style="margin-bottom: 5px;" class="form-group">
                                        <button class="btn btn-sm btn-primary" type="button" id="GetIconPicker_c" data-iconpicker-input="input#learning_check_icon" data-iconpicker-preview="i#learning_check_icon_preview"><?php echo _("Select Icon"); ?></button>
                                        <input readonly type="hidden" id="learning_check_icon" name="Icon" value="<?php echo $virtual_tour['learning_check_icon']; ?>" required="" placeholder="" autocomplete="off" spellcheck="false">
                                        <div style="vertical-align: middle;width: 40px;" class="icon-preview d-inline-block ml-1" data-toggle="tooltip" title="">
                                            <i style="font-size: 24px;" id="learning_check_icon_preview" class="<?php echo $virtual_tour['learning_check_icon']; ?>"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-window-maximize"></i> <?php echo _("Intro"); ?>&nbsp;&nbsp;&nbsp;<input onchange="change_learning_show_modal();" type="checkbox" id="learning_show_modal" <?php echo ($virtual_tour['learning_show_modal'])?'checked':''; ?> /></h6>
                    </div>
                    <div class="card-body learning_modal_settings <?php echo ($virtual_tour['learning_show_modal'])?'':'disabled'; ?>">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_modal_background"><?php echo _("Background"); ?></label>
                                    <input type="text" class="form-control" id="learning_modal_background" value="<?php echo $virtual_tour['learning_modal_background']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_modal_color_text"><?php echo _("Color"); ?></label>
                                    <input type="text" class="form-control" id="learning_modal_color_text" value="<?php echo $virtual_tour['learning_modal_color_text']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="learning_modal_title"><?php echo _("Title"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'learning_modal_title'); ?>
                                    <input type="text" class="form-control" id="learning_modal_title" value="<?php echo $virtual_tour['learning_modal_title']; ?>" />
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <input style="display:none;" type="text" class="form-control input_lang" data-target-id="learning_modal_title" data-lang="<?php echo $lang; ?>" value="<?php echo $array_input_lang[$lang]['learning_modal_title']; ?>" />
                                        <?php endif;
                                    } ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_modal_color"><?php echo _("Subtitle - Color"); ?></label>
                                    <input type="text" class="form-control" id="learning_modal_color" value="<?php echo $virtual_tour['learning_modal_color']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="learning_modal_subtitle"><?php echo _("Subtitle"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'learning_modal_subtitle'); ?>
                                    <input type="text" class="form-control" id="learning_modal_subtitle" value="<?php echo $virtual_tour['learning_modal_subtitle']; ?>" />
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <input style="display:none;" type="text" class="form-control input_lang" data-target-id="learning_modal_subtitle" data-lang="<?php echo $lang; ?>" value="<?php echo $array_input_lang[$lang]['learning_modal_subtitle']; ?>" />
                                        <?php endif;
                                    } ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_modal_button_background"><?php echo _("Button - Background"); ?></label>
                                    <input type="text" class="form-control" id="learning_modal_button_background" value="<?php echo $virtual_tour['learning_modal_button_background']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_modal_button_color"><?php echo _("Button - Color"); ?></label>
                                    <input type="text" class="form-control" id="learning_modal_button_color" value="<?php echo $virtual_tour['learning_modal_button_color']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="learning_modal_button"><?php echo _("Button"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'learning_modal_button'); ?>
                                    <input type="text" class="form-control" id="learning_modal_button" placeholder="<?php echo _("Start"); ?>" value="<?php echo $virtual_tour['learning_modal_button']; ?>" />
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <input style="display:none;" type="text" class="form-control input_lang" data-target-id="learning_modal_button" data-lang="<?php echo $lang; ?>" value="<?php echo $array_input_lang[$lang]['learning_modal_button']; ?>" />
                                        <?php endif;
                                    } ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="learning_modal_description"><?php echo _("Description"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'learning_modal_description'); ?>
                                    <div><div id="learning_modal_description"><?php echo $virtual_tour['learning_modal_description']; ?></div></div>
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <div style="display:none;"><div id="learning_modal_description_<?php echo $lang; ?>" class="input_lang" data-target-id="learning_modal_description" data-lang="<?php echo $lang; ?>"><?php echo $array_input_lang[$lang]['learning_modal_description']; ?></div></div>
                                        <?php endif;
                                    } ?>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_show_email"><?php echo _("Request email"); ?></label><br>
                                    <input type="checkbox" id="learning_show_email" <?php echo ($virtual_tour['learning_show_email'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_mandatory_email"><?php echo _("Mandatory email"); ?></label><br>
                                    <input type="checkbox" id="learning_mandatory_email" <?php echo ($virtual_tour['learning_mandatory_email'])?'checked':''; ?> />
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="learning_placeholder_email"><?php echo _("Placeholder email"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'learning_placeholder_email'); ?>
                                    <input type="text" class="form-control" id="learning_placeholder_email" placeholder="<?php echo _("Your email");?>" value="<?php echo $virtual_tour['learning_placeholder_email']; ?>" />
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <input style="display:none;" type="text" class="form-control input_lang" data-target-id="learning_placeholder_email" data-lang="<?php echo $lang; ?>" value="<?php echo $array_input_lang[$lang]['learning_placeholder_email']; ?>" />
                                        <?php endif;
                                    } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane fade <?php echo ($shop==0) ? 'd-none' : ''; ?>" id="shop_tab">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="far fa-shopping-cart"></i> <?php echo _("Settings"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="shop_type"><?php echo _("Shop Type"); ?></label>
                                    <select onchange="change_shop_type();" class="form-control" id="shop_type">
                                        <option <?php echo ($virtual_tour['shop_type']=='snipcart') ? 'selected' : ''; ?> id="snipcart">Snipcart</option>
                                        <option <?php echo ($virtual_tour['shop_type']=='woocommerce') ? 'selected' : ''; ?> id="woocommerce">Woocommerce</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4 woocommerce_setting <?php echo ($virtual_tour['shop_type']=='woocommerce') ? '' : 'd-none'; ?>">
                                <div class="form-group">
                                    <label for="woocommerce_store_url"><?php echo _("Store Url"); ?></label>
                                    <input id="woocommerce_store_url" type="text" class="form-control" value="<?php echo $virtual_tour['woocommerce_store_url']; ?>">
                                </div>
                            </div>
                            <div class="col-md-2 woocommerce_setting <?php echo ($virtual_tour['shop_type']=='woocommerce') ? '' : 'd-none'; ?>">
                                <div class="form-group">
                                    <label for="woocommerce_store_cart"><?php echo _("Cart Page"); ?></label>
                                    <input id="woocommerce_store_cart" type="text" class="form-control" value="<?php echo $virtual_tour['woocommerce_store_cart']; ?>">
                                </div>
                            </div>
                            <div class="col-md-2 woocommerce_setting <?php echo ($virtual_tour['shop_type']=='woocommerce') ? '' : 'd-none'; ?>">
                                <div class="form-group">
                                    <label for="woocommerce_store_checkout"><?php echo _("Checkout Page"); ?></label>
                                    <input id="woocommerce_store_checkout" type="text" class="form-control" value="<?php echo $virtual_tour['woocommerce_store_checkout']; ?>">
                                </div>
                            </div>
                            <div class="col-md-1 woocommerce_setting <?php echo ($virtual_tour['shop_type']=='woocommerce') ? '' : 'd-none'; ?>">
                                <div class="form-group">
                                    <label for="woocommerce_show_stock_quantity"><?php echo _("Stock"); ?> <i title="<?php echo _("show remaining stock quantity"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input <?php echo ($virtual_tour['woocommerce_show_stock_quantity']) ? 'checked' : ''; ?> id="woocommerce_show_stock_quantity" type="checkbox">
                                </div>
                            </div>
                            <div class="col-md-1 woocommerce_setting <?php echo ($virtual_tour['shop_type']=='woocommerce') ? '' : 'd-none'; ?>">
                                <div class="form-group">
                                    <label for="woocommerce_modal"><?php echo _("Pop-up"); ?> <i title="<?php echo _("show cart and checkout page in a popup window. Attention: might not works for security reasons!"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                                    <input <?php echo ($virtual_tour['woocommerce_modal']) ? 'checked' : ''; ?> id="woocommerce_modal" type="checkbox">
                                </div>
                            </div>
                            <div class="col-md-6 woocommerce_setting <?php echo ($virtual_tour['shop_type']=='woocommerce') ? '' : 'd-none'; ?>">
                                <div class="form-group">
                                    <label for="woocommerce_customer_key"><?php echo _("Customer Key"); ?></label>
                                    <input autocomplete="new-password" id="woocommerce_customer_key" type="password" class="form-control" value="<?php echo ($virtual_tour['woocommerce_customer_key']!='') ? 'keep_woocommerce_customer_key' : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6 woocommerce_setting <?php echo ($virtual_tour['shop_type']=='woocommerce') ? '' : 'd-none'; ?>">
                                <div class="form-group">
                                    <label for="woocommerce_customer_secret"><?php echo _("Customer Secret"); ?></label>
                                    <input autocomplete="new-password" id="woocommerce_customer_secret" type="password" class="form-control" value="<?php echo ($virtual_tour['woocommerce_customer_secret']!='') ? 'keep_woocommerce_customer_secret' : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-6 snipcart_setting <?php echo ($virtual_tour['shop_type']=='woocommerce') ? 'd-none' : ''; ?>">
                                <div class="form-group">
                                    <label for="snipcart_api_key"><?php echo _("Public Key"); ?></label>
                                    <input autocomplete="new-password" id="snipcart_api_key" type="password" class="form-control" value="<?php echo ($virtual_tour['snipcart_api_key']!='') ? 'keep_snipcart_public_key' : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-4 snipcart_setting <?php echo ($virtual_tour['shop_type']=='woocommerce') ? 'd-none' : ''; ?>">
                                <div class="form-group">
                                    <label for="snipcart_currency"><?php echo _("Currency"); ?></label>
                                    <select class="form-control" id="snipcart_currency">
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='AED') ? 'selected' : ''; ?> id="AED">AED</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='ARS') ? 'selected' : ''; ?> id="ARS">ARS</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='AUD') ? 'selected' : ''; ?> id="AUD">AUD</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='BRL') ? 'selected' : ''; ?> id="BRL">BRL</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='CAD') ? 'selected' : ''; ?> id="CAD">CAD</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='CAD') ? 'selected' : ''; ?> id="CLP">CLP</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='CHF') ? 'selected' : ''; ?> id="CHF">CHF</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='CNY') ? 'selected' : ''; ?> id="CNY">CNY</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='CZK') ? 'selected' : ''; ?> id="CZK">CZK</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='EUR') ? 'selected' : ''; ?> id="EUR">EUR</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='GBP') ? 'selected' : ''; ?> id="GBP">GBP</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='HKD') ? 'selected' : ''; ?> id="HKD">HKD</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='IDR') ? 'selected' : ''; ?> id="IDR">IDR</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='ILS') ? 'selected' : ''; ?> id="ILS">ILS</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='INR') ? 'selected' : ''; ?> id="INR">INR</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='JPY') ? 'selected' : ''; ?> id="JPY">JPY</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='MXN') ? 'selected' : ''; ?> id="MXN">MXN</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='MYR') ? 'selected' : ''; ?> id="MYR">MYR</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='NGN') ? 'selected' : ''; ?> id="NGN">NGN</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='PHP') ? 'selected' : ''; ?> id="PHP">PHP</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='PYG') ? 'selected' : ''; ?> id="PYG">PYG</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='PLN') ? 'selected' : ''; ?> id="PLN">PLN</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='RUB') ? 'selected' : ''; ?> id="RUB">RUB</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='RWF') ? 'selected' : ''; ?> id="RWF">RWF</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='SEK') ? 'selected' : ''; ?> id="SEK">SEK</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='SGD') ? 'selected' : ''; ?> id="SGD">SGD</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='TJS') ? 'selected' : ''; ?> id="TJS">TJS</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='THB') ? 'selected' : ''; ?> id="THB">THB</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='TRY') ? 'selected' : ''; ?> id="TRY">TRY</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='USD') ? 'selected' : ''; ?> id="USD">USD</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='VND') ? 'selected' : ''; ?> id="VND">VND</option>
                                        <option <?php echo ($virtual_tour['snipcart_currency']=='ZAR') ? 'selected' : ''; ?> id="ZAR">ZAR</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12 snipcart_setting <?php echo ($virtual_tour['shop_type']=='woocommerce') ? 'd-none' : ''; ?>">
                                <span class="text-primary">1) <?php echo _("Create an account on"); ?></span> <a target="_blank" href="https://app.snipcart.com/register">Snipcart <i class="fas fa-external-link-square-alt"></i></a><br>
                                <i><?php echo sprintf(_("Pay attention that you can make 2 configurations, one for <b>test</b> and one for <b>live</b>, by changing the selector on the %s dashboard at the top"),'snipcart'); ?></i><br>
                                <span class="text-primary">2) <?php echo _("Fill your business information"); ?></span> <a target="_blank" href="https://app.snipcart.com/dashboard/account/settings"><i class="fas fa-external-link-square-alt"></i></a><br>
                                <span class="text-primary">3) <?php echo _("Configure your domain"); ?></span> <a target="_blank" href="https://app.snipcart.com/dashboard/account/domains"><i class="fas fa-external-link-square-alt"></i></a><br>
                                - <?php echo sprintf(_("Add your domain <b>%s</b> in the <b>Domain</b> field of the section <b>DEFAULT WEBSITE DOMAIN</b>"),$_SERVER['SERVER_NAME']); ?><br>
                                <span class="text-primary">4) <?php echo _("Configure Regional Settings"); ?></span> <a target="_blank" href="https://app.snipcart.com/dashboard/settings/regional"><i class="fas fa-external-link-square-alt"></i></a><br>
                                - <?php echo _("Add all the currencies in the section <b>SUPPORTED CURRENCIES</b>"); ?><br>
                                - <?php echo _("Enable the countries they can buy on your site in the section <b>ENABLED COUNTRIES</b>"); ?><br>
                                <span class="text-primary">5) <?php echo _("Configure Taxes"); ?></span> <a target="_blank" href="https://app.snipcart.com/dashboard/taxes"><i class="fas fa-external-link-square-alt"></i></a><br>
                                - <?php echo _("Click on <b>Create New Tax</b> a make sure to check <b>Included in price</b>"); ?><br>
                                <span class="text-primary">6) <?php echo _("Configure Checkout & Cart"); ?></span> <a target="_blank" href="https://app.snipcart.com/dashboard/settings/cart-and-checkout"><i class="fas fa-external-link-square-alt"></i></a><br>
                                - <?php echo _("You can decide whether to register your customers or not by changing the option <b>Allow Guests Only</b>"); ?><br>
                                <span class="text-primary">7) <?php echo _("Connect a payment gateway"); ?></span> <a target="_blank" href="https://app.snipcart.com/dashboard/account/gateway"><i class="fas fa-external-link-square-alt"></i></a><br>
                                <span class="text-primary">8) <?php echo _("Get Api Key"); ?></span> <a target="_blank" href="https://app.snipcart.com/dashboard/account/credentials"><i class="fas fa-external-link-square-alt"></i></a><br>
                                - <?php echo _("Retrieve your <b>public test or live API key</b> and enter it above"); ?>
                            </div>
                            <div class="col-md-12 woocommerce_setting <?php echo ($virtual_tour['shop_type']=='woocommerce') ? '' : 'd-none'; ?>">
                                1) <?php echo sprintf(_("Install %s plugin on your woocommerce site"),'<a target="_blank" href="https://wordpress.org/plugins/cart-rest-api-for-woocommerce/">CoCart <i class="fas fa-external-link-square-alt"></i></a>'); ?><br>
                                2) <?php echo _("Login into your woordpress administrator panel"); ?><br>
                                3) <?php echo _("Create a new API Key under <b>WooCommerce</b> - <b>Settings</b> - <b>Advanced</b> - <b>REST API</b>"); ?><br>
                                4) <?php echo _("Retrieve your <b>Consumer key and secret</b> and enter them above"); ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_regenerate_multires" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Multi resolution regenerate"); ?></h5>
            </div>
            <div class="modal-body">
                <span style="color: green;" class="ok_msg"><?php echo _("Success. Multi resolution panoramas will be regenerated in background."); ?></span>
                <span style="color: red" class="error_msg"><?php echo _("An error has occured."); ?></span>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_regenerate_panoramas" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Regenerate panoramas"); ?></h5>
            </div>
            <div class="modal-body">
                <span><i class="fas fa-spin fa-circle-notch" aria-hidden="true"></i> <?php echo _("Regeneration in progress, please wait ... Do not close this window!"); ?></span>
            </div>
        </div>
    </div>
</div>

<div id="modal_add_category" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Add Category"); ?></h5>
            </div>
            <div class="modal-body">
                <input type="text" class="form-control" id="category_name" />
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="add_category();" type="button" class="btn btn-success"><i class="fas fa-plus"></i> <?php echo _("Add"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_media_library" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" style="width: 90% !important; max-width: 90% !important; margin: 0 auto !important;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Media Library"); ?></h5>
            </div>
            <div class="modal-body">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_gallery_images" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" style="width: 90% !important; max-width: 90% !important; margin: 0 auto !important;" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Gallery Images"); ?></h5>
            </div>
            <div class="modal-body">
                <?php foreach ($array_gallery_images as $gallery_image) { ?>
                    <img class="float-left mb-2 ml-1 mr-1 image_gallery_slider" data-image="<?php echo $gallery_image; ?>" draggable="false" style="object-fit:cover;width:120px;height:120px;border-radius:5px;" src="<?php echo $path_base_url; ?>gallery/thumb/<?php echo $gallery_image; ?>">
                <?php } ?>
            </div>
            <div class="modal-footer">
                <button id="btn_add_image_to_slider" onclick="add_images_to_intro_slider(<?php echo $id_virtual_tour; ?>);" type="button" class="btn btn-success <?php echo ($demo) ? 'disabled_d' : ''; ?> disabled"><i class="fas fa-plus"></i> <?php echo _("Add"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_change_languages" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Languages"); ?></h5>
            </div>
            <div class="modal-body">
                <p><?php echo _("Enabled languages have changed. To apply them, you need to reload the page."); ?>
                </p>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="save_virtualtour(true);" type="button" class="btn btn-success"><i class="fas fa-check"></i> <?php echo _("Yes, Reload"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("No, i will do later"); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        Dropzone.autoDiscover = false;
        window.id_virtualtour = <?php echo $id_virtual_tour; ?>;
        var hfov = '<?php echo $virtual_tour['hfov']; ?>';
        var min_hfov = '<?php echo $virtual_tour['min_hfov']; ?>';
        var max_hfov = '<?php echo $virtual_tour['max_hfov']; ?>';
        window.s3_enabled = <?php echo ($s3_enabled) ? 1 : 0; ?>;
        window.s3_url = '<?php echo $s3_url; ?>';
        window.song = '<?php echo $virtual_tour['song']; ?>';
        window.logo = '<?php echo $virtual_tour['logo']; ?>';
        window.poweredby_image = '<?php echo $virtual_tour['poweredby_image']; ?>';
        window.nadir_logo = '<?php echo $virtual_tour['nadir_logo']; ?>';
        window.background_image = '<?php echo $virtual_tour['background_image']; ?>';
        window.background_video = '<?php echo $virtual_tour['background_video']; ?>';
        window.background_image_mobile = '<?php echo $virtual_tour['background_image_mobile']; ?>';
        window.background_video_mobile = '<?php echo $virtual_tour['background_video_mobile']; ?>';
        window.intro_desktop = '<?php echo $virtual_tour['intro_desktop']; ?>';
        window.intro_mobile = '<?php echo $virtual_tour['intro_mobile']; ?>';
        window.avatar_video = '<?php echo $virtual_tour['avatar_video']; ?>';
        window.hfov_mobile_ratio = <?php echo $virtual_tour['hfov_mobile_ratio']; ?>;
        window.pan_speed = <?php echo $virtual_tour['pan_speed']; ?>;
        window.pan_speed_mobile = <?php echo $virtual_tour['pan_speed_mobile']; ?>;
        window.friction = <?php echo $virtual_tour['friction']; ?>;
        window.friction_mobile = <?php echo $virtual_tour['friction_mobile']; ?>;
        window.zoom_friction = <?php echo $virtual_tour['zoom_friction']; ?>;
        window.zoom_friction_mobile = <?php echo $virtual_tour['zoom_friction_mobile']; ?>;
        window.zoom_to_pointer = <?php echo $virtual_tour['zoom_to_pointer']; ?>;
        window.first_panorama_image = '<?php echo $first_panorama_image; ?>';
        window.first_panorama_multires = <?php echo $first_panorama['multires']; ?>;
        window.first_panorama_multires_config = '<?php echo $first_panorama['multires_config']; ?>';
        window.custom_vt_html = null;
        window.intro_slider_images = [];
        var viewer = null;
        var viewer_mobile = null;
        var ratio_hfov = 1;
        var viewer_initialized = false, viewer_mobile_initialized = false;
        window.vt_need_save = false;
        window.external = <?php echo $virtual_tour['external']; ?>;
        window.multires = '<?php echo $settings['multires']; ?>';
        window.context_info_editor = null;
        window.mouse_follow_feedback = <?php echo $virtual_tour['mouse_follow_feedback']; ?>;
        var current_viewer;
        var current_viewer_pitch=0, current_viewer_yaw=0, x_mouseup=0, y_mouseup=0;
        var viewer_mov_follow_mouse = true, viewer_mov_pos_change = false, timeout_mov_follow_mouse, timeout_clear_mov_pos_change;
        var table_editors = null;
        window.loading_background_color_spectrum = null;
        window.loading_text_color_spectrum = null;
        window.form_background_spectrum = null;
        window.form_color_spectrum = null;
        window.form_background_button_spectrum = null;
        window.form_color_button_spectrum = null;
        window.learning_modal_description_editor = null;
        window.learning_modal_description_editor_lang = [];
        Quill.register("modules/htmlEditButton", htmlEditButton);
        var DirectionAttribute = Quill.import('attributors/attribute/direction');
        Quill.register(DirectionAttribute,true);
        var AlignClass = Quill.import('attributors/class/align');
        Quill.register(AlignClass,true);
        var BackgroundClass = Quill.import('attributors/class/background');
        Quill.register(BackgroundClass,true);
        var ColorClass = Quill.import('attributors/class/color');
        Quill.register(ColorClass,true);
        var DirectionClass = Quill.import('attributors/class/direction');
        Quill.register(DirectionClass,true);
        var FontClass = Quill.import('attributors/class/font');
        Quill.register(FontClass,true);
        var SizeClass = Quill.import('attributors/class/size');
        Quill.register(SizeClass,true);
        var AlignStyle = Quill.import('attributors/style/align');
        Quill.register(AlignStyle,true);
        var BackgroundStyle = Quill.import('attributors/style/background');
        Quill.register(BackgroundStyle,true);
        var ColorStyle = Quill.import('attributors/style/color');
        Quill.register(ColorStyle,true);
        var DirectionStyle = Quill.import('attributors/style/direction');
        Quill.register(DirectionStyle,true);
        var FontStyle = Quill.import('attributors/style/font');
        Quill.register(FontStyle,true);
        var SizeStyle = Quill.import('attributors/style/size');
        Quill.register(SizeStyle,true);
        var LinkFormats = Quill.import("formats/link");
        Quill.register(LinkFormats,true);
        var BlockEmbed = Quill.import('blots/block/embed');
        class keepHTML extends BlockEmbed {
            static create(node) {
                return node;
            }
            static value(node) {
                return node;
            }
        };
        keepHTML.blotName = 'keepHTML';
        keepHTML.className = 'keepHTML';
        keepHTML.tagName = 'div';
        Quill.register(keepHTML);
        var vt_name = `<?php echo $virtual_tour['name']; ?>`;
        window.introd_file_langs = {};
        window.introm_file_langs = {};
        $('#subtitle_header').html(vt_name);
        $(document).ready(function () {
            window.loading_background_color_spectrum = $('#loading_background_color').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false
            });
            $('.loading_background_color_v').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false
            });
            window.loading_text_color_spectrum = $('#loading_text_color').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false
            });
            $('.loading_text_color_v').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false
            });
            window.form_background_spectrum = $('#form_background_m').spectrum({
                type: "text",
                preferredFormat: "rgb",
                showAlpha: true,
                showButtons: false,
                allowEmpty: false,
                appendTo: '#modal_main_form'
            });
            window.form_color_spectrum = $('#form_color_m').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false,
                appendTo: '#modal_main_form'
            });
            window.form_background_button_spectrum = $('#form_background_button').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false,
                appendTo: '#modal_main_form'
            });
            window.form_color_button_spectrum = $('#form_color_button').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false,
                appendTo: '#modal_main_form'
            });
            $('#learning_modal_color').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false,
            });
            $('#learning_modal_color_text').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false,
            });
            $('#learning_modal_button_background').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false,
            });
            $('#learning_modal_button_color').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false,
            });
            $('#learning_modal_background').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false,
            });
            $('#learning_summary_background').spectrum({
                type: "text",
                preferredFormat: "rgb",
                showAlpha: true,
                showButtons: false,
                allowEmpty: false,
            });
            $('#learning_summary_color').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false,
            });
            $('#learning_summary_partial_color').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false,
            });
            $('#learning_summary_global_color').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false,
            });
            $('#learning_check_background').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false,
            });
            $('#learning_check_color').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false,
            });
            window.custom_vt_html = ace.edit('custom_vt_html');
            window.custom_vt_html.session.setMode("ace/mode/html");
            window.custom_vt_html.setOption('enableLiveAutocompletion',true);
            window.custom_vt_html.setShowPrintMargin(false);
            if($('body').hasClass('dark_mode')) {
                window.custom_vt_html.setTheme("ace/theme/one_dark");
            }
            if(window.rtl==1) {
                window.custom_vt_html.setOption("rtl", true);
            }
            bsCustomFileInput.init();
            $('.help_t').tooltip();
            if(logo=='') {
                $('#div_delete_logo').hide();
                $('#div_image_logo').hide();
                $('#div_upload_logo').show();
                $('#div_exist_logo').show();
            } else {
                $('#div_delete_logo').show();
                $('#div_image_logo').show();
                $('#div_upload_logo').hide();
                $('#div_exist_logo').hide();
            }
            if(poweredby_image=='') {
                $('#div_delete_poweredby').hide();
                $('#div_image_poweredby').hide();
                $('#div_upload_poweredby').show();
                $('#div_exist_poweredby').show();
            } else {
                $('#div_delete_poweredby').show();
                $('#div_image_poweredby').show();
                $('#div_upload_poweredby').hide();
                $('#div_exist_poweredby').hide();
            }
            if(nadir_logo=='') {
                $('#div_delete_nadir_logo').hide();
                $('#div_image_nadir_logo').hide();
                $('#div_upload_nadir_logo').show();
                $('#div_exist_nadir_logo').show();
            } else {
                $('#div_delete_nadir_logo').show();
                $('#div_image_nadir_logo').show();
                $('#div_upload_nadir_logo').hide();
                $('#div_exist_nadir_logo').hide();
            }
            if(background_image=='') {
                $('#div_delete_bg').hide();
                $('#div_image_bg').hide();
                $('#div_upload_bg').show();
                $('#div_exist_bg').show();
            } else {
                $('#div_delete_bg').show();
                $('#div_image_bg').show();
                $('#div_upload_bg').hide();
                $('#div_exist_bg').hide();
            }
            if(background_image_mobile=='') {
                $('#div_delete_bg_m').hide();
                $('#div_image_bg_m').hide();
                $('#div_upload_bg_m').show();
                $('#div_exist_bg_m').show();
            } else {
                $('#div_delete_bg_m').show();
                $('#div_image_bg_m').show();
                $('#div_upload_bg_m').hide();
                $('#div_exist_bg_m').hide();
            }
            if(background_video=='') {
                $('#div_delete_video_bg').hide();
                $('#div_video_bg').hide();
                $('#div_video_params').hide();
                $('#div_upload_video_bg').show();
                $('#div_exist_video_bg').show();
            } else {
                $('#div_delete_video_bg').show();
                $('#div_video_bg').show();
                $('#div_video_params').show();
                $('#div_upload_video_bg').hide();
                $('#div_exist_video_bg').hide();
            }
            if(background_video_mobile=='') {
                $('#div_delete_video_bg_m').hide();
                $('#div_video_bg_m').hide();
                $('#div_video_m_params').hide();
                $('#div_upload_video_bg_m').show();
                $('#div_exist_video_bg_m').show();
            } else {
                $('#div_delete_video_bg_m').show();
                $('#div_video_bg_m').show();
                $('#div_video_m_params').show();
                $('#div_upload_video_bg_m').hide();
                $('#div_exist_video_bg_m').hide();
            }
            if(song=='') {
                $('#div_delete_song').hide();
                $('#div_player_song').hide();
                $('#div_upload_song').show();
                $('#div_exist_song').show();
            } else {
                $('#div_delete_song').show();
                $('#div_player_song').show();
                $('#div_upload_song').hide();
                $('#div_exist_song').hide();
            }
            if(intro_desktop=='') {
                $('#div_delete_introd').hide();
                $('#div_image_introd').hide();
                $('#div_upload_introd').show();
                $('#div_exist_introd').show();
            } else {
                $('#div_delete_introd').show();
                $('#div_image_introd').show();
                $('#div_upload_introd').hide();
                $('#div_exist_introd').hide();
            }
            if(intro_mobile=='') {
                $('#div_delete_introm').hide();
                $('#div_image_introm').hide();
                $('#div_upload_introm').show();
                $('#div_exist_introm').show();
            } else {
                $('#div_delete_introm').show();
                $('#div_image_introm').show();
                $('#div_upload_introm').hide();
                $('#div_exist_introm').hide();
            }
            if(avatar_video=='') {
                $('#div_delete_avatar_video').hide();
                $('#div_avatar_video_preview').hide();
                $('#div_upload_avatar_video').show();
            } else {
                if($('.lang_input_switcher').length==0) {
                    var exists_videos = $('#avatar_video_content').val();
                    preview_avatar_video(exists_videos,'');
                } else if(window.selected_language==null) {
                    var exists_videos = $('#avatar_video_content').val();
                    preview_avatar_video(exists_videos,'');
                }
            }
            $('#exist_bg').selectator({
                useSearch: false
            });
            $('#exist_video_bg').selectator({
                useSearch: false
            });
            $('#exist_bg_m').selectator({
                useSearch: false
            });
            $('#exist_video_bg_m').selectator({
                useSearch: false
            });
            $('#exist_logo').selectator({
                useSearch: false
            });
            $('#exist_nadir_logo').selectator({
                useSearch: false
            });
            $('#exist_introd').selectator({
                useSearch: false
            });
            $('#exist_introm').selectator({
                useSearch: false
            });
            $('#exist_song').selectator({
                useSearch: false
            });
            var toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],['link'],
                ['clean']
            ];
            var toolbarOptions_l = [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],['image'],
                ['clean']
            ];
            var toolbarHtml = {
                debug: false,
                msg: `<?php echo _("Edit the content in HTML format"); ?>`,
                okText: `<?php echo _("Ok"); ?>`,
                cancelText: `<?php echo _("Cancel"); ?>`,
                buttonHTML: '<i class="fas fa-code"></i>',
                buttonTitle: `<?php echo _("Show HTML Source"); ?>`,
                syntax: true,
                prependSelector: null,
                editorModules: {}
            };
            window.context_info_editor = new Quill('#context_info', {
                modules: {
                    toolbar: toolbarOptions
                },
                theme: 'snow',
                bounds: document.getElementsByClassName('container-fluid')[0]
            });
            window.learning_modal_description_editor = new Quill('#learning_modal_description', {
                modules: {
                    toolbar: toolbarOptions_l,
                    htmlEditButton: toolbarHtml
                },
                theme: 'snow',
                bounds: document.getElementsByClassName('container-fluid')[0]
            });
            $('.input_lang[data-target-id="learning_modal_description"]').each(function() {
                var lang = $(this).attr('data-lang');
                var id = $(this).attr('id');
                setTimeout(function() {
                    window.learning_modal_description_editor_lang[lang] = new Quill('#'+id, {
                        modules: {
                            toolbar: toolbarOptions_l,
                            htmlEditButton: toolbarHtml
                        },
                        theme: 'snow',
                        bounds: document.getElementsByClassName('container-fluid')[0]
                    });
                },0);
            });
            const input1 = document.getElementById('poweredby_link_image');
            const input2 = document.getElementById('poweredby_link_text');
            input1.addEventListener('input', function() {
                input2.value = input1.value;
            });
            input2.addEventListener('input', function() {
                input1.value = input2.value;
            });
            var gallery_dropzone = new Dropzone("#gallery-dropzone", {
                url: "ajax/upload_intro_slider_image.php",
                parallelUploads: 1,
                maxFilesize: 20,
                timeout: 120000,
                dictDefaultMessage: "<?php echo _("Drop files or click here to upload"); ?>",
                dictFallbackMessage: "<?php echo _("Your browser does not support drag'n'drop file uploads."); ?>",
                dictFallbackText: "<?php echo _("Please use the fallback form below to upload your files like in the olden days."); ?>",
                dictFileTooBig: "<?php echo sprintf(_("File is too big (%sMiB). Max filesize: %sMiB."),'{{filesize}}','{{maxFilesize}}'); ?>",
                dictInvalidFileType: "<?php echo _("You can't upload files of this type."); ?>",
                dictResponseError: "<?php echo sprintf(_("Server responded with %s code."),'{{statusCode}}'); ?>",
                dictCancelUpload: "<?php echo _("Cancel upload"); ?>",
                dictCancelUploadConfirmation: "<?php echo _("Are you sure you want to cancel this upload?"); ?>",
                dictRemoveFile: "<?php echo _("Remove file"); ?>",
                dictMaxFilesExceeded: "<?php echo _("You can not upload any more files."); ?>",
                acceptedFiles: 'image/*'
            });
            gallery_dropzone.on("addedfile", function(file) {
                $('#list_images').addClass('disabled');
            });
            gallery_dropzone.on("success", function(file,rsp) {
                if(rsp !== "" && !rsp.startsWith("ERROR")) {
                    add_image_to_intro_slider(id_virtualtour, rsp);
                }
            });
            gallery_dropzone.on("queuecomplete", function() {
                $('#list_images').removeClass('disabled');
                gallery_dropzone.removeAllFiles();
            });
            $('.input_lang[data-target-id="div_exist_introd"]').each(function() {
                var lang = $(this).attr('data-lang');
                var indrod_file = $('#introd_file_'+lang).val();
                window.introd_file_langs[lang] = indrod_file;
            });
            $('.input_lang[data-target-id="div_exist_introm"]').each(function() {
                var lang = $(this).attr('data-lang');
                var indrom_file = $('#introm_file_'+lang).val();
                window.introm_file_langs[lang] = indrom_file;
            });
            if($('.lang_input_switcher').length) {
                $('.lang_input_switcher').each(function() {
                    var elem_o = $(this).attr('data-elem');
                    var lang_o = $(this).attr('data-default-lang');
                    switch_input_language(lang_o,lang_o,elem_o);
                });
            }
            IconPicker.Init({
                jsonUrl: 'vendor/iconpicker/iconpicker-1.6.0.json',
                searchPlaceholder: '<?php echo _("Search Icon"); ?>',
                showAllButton: '<?php echo _("Show All"); ?>',
                cancelButton: '<?php echo _("Cancel"); ?>',
                noResultsFound: '<?php echo _("No results found."); ?>',
                borderRadius: '20px'
            });
            IconPicker.Run('#GetIconPicker', function(){
                $('.btn_delete_learning_modal_icon').removeClass('disabled');
            });
            IconPicker.Run('#GetIconPicker_c', function(){});
            get_intro_slider_images(id_virtualtour);
        });

        window.click_editors = function() {
            if(table_editors==null) {
                table_editors = $('#assign_editors_table').DataTable({
                    "order": [[ 1, "asc" ]],
                    "responsive": true,
                    "scrollX": true,
                    "processing": true,
                    "searching": true,
                    "serverSide": true,
                    "ajax": {
                        url: "ajax/get_assigned_editors.php",
                        type: "POST",
                        data: {
                            id_virtualtour: window.id_virtualtour
                        }
                    },
                    "drawCallback": function() {
                        $('#assign_editors_table').DataTable().columns.adjust();
                        $('.assigned_user').change(function() {
                            var checked = this.checked;
                            if(checked) checked=1; else checked=0;
                            var id_user = $(this).attr('id');
                            assign_user_editor(id_user,checked);
                            $('.assigned_user').each(function () {
                                var checked = this.checked;
                                var id_user = $(this).attr('id');
                                if(checked) {
                                    $('.editor_permissions[id='+id_user+']').prop('disabled',false);
                                } else {
                                    $('.editor_permissions[id='+id_user+']').prop('disabled',true);
                                }
                            });
                        });
                        $('.editor_permissions').change(function() {
                            var checked = this.checked;
                            if(checked) checked=1; else checked=0;
                            var id_user = $(this).attr('id');
                            var field = $(this).attr('class');
                            field = field.replace('editor_permissions ','');
                            set_permission_user_editor(id_user,field,checked);
                        });
                        $('#assign_editors_table tr').on('click',function () {
                            $('#assign_editors_table tr').removeClass('highlight');
                            $(this).addClass('highlight');
                        });
                        $('.assigned_user').each(function () {
                            var checked = this.checked;
                            var id_user = $(this).attr('id');
                            if(checked) {
                                $('.editor_permissions[id='+id_user+']').prop('disabled',false);
                            } else {
                                $('.editor_permissions[id='+id_user+']').prop('disabled',true);
                            }
                        });
                        $('#btn_assign_all').removeClass('disabled');
                        $('#btn_unassign_all').removeClass('disabled');
                        setTimeout(function() {
                            $(window).trigger('resize');
                        },250);
                    },
                    "language": {
                        "decimal":        "",
                        "emptyTable":     "<?php echo _("No data available in table"); ?>",
                        "info":           "<?php echo sprintf(_("Showing %s to %s of %s entries"),'_START_','_END_','_TOTAL_'); ?>",
                        "infoEmpty":      "<?php echo _("Showing 0 to 0 of 0 entries"); ?>",
                        "infoFiltered":   "<?php echo sprintf(_("(filtered from %s total entries)"),'_MAX_'); ?>",
                        "infoPostFix":    "",
                        "thousands":      ",",
                        "lengthMenu":     "<?php echo sprintf(_("Show %s entries"),'_MENU_'); ?>",
                        "loadingRecords": "<?php echo _("Loading"); ?>...",
                        "processing":     "<?php echo _("Processing"); ?>...",
                        "search":         "<?php echo _("Search"); ?>:",
                        "zeroRecords":    "<?php echo _("No matching records found"); ?>",
                        "paginate": {
                            "first":      "<?php echo _("First"); ?>",
                            "last":       "<?php echo _("Last"); ?>",
                            "next":       "<?php echo _("Next"); ?>",
                            "previous":   "<?php echo _("Previous"); ?>"
                        },
                        "aria": {
                            "sortAscending":  ": <?php echo _("activate to sort column ascending"); ?>",
                            "sortDescending": ": <?php echo _("activate to sort column descending"); ?>"
                        }
                    }
                });
            } else {
                setTimeout(function() {
                    table_editors.ajax.reload();
                    $(window).trigger('resize');
                },250);
            }
        }

        window.initialize_hfov = function() {
            if(viewer==null) {
                $('#hfov_tab').css('opacity',0);
                $('#hfov_tab').show();
                if(window.first_panorama_image=='') {
                    var panorama_image = "img/test.jpg";
                } else {
                    if(window.s3_enabled==1) {
                        var panorama_image = window.s3_url+"viewer/panoramas/"+window.first_panorama_image+'?s3=1';
                    } else {
                        var panorama_image = "../viewer/panoramas/"+window.first_panorama_image;
                    }
                }
                var multires = parseInt(window.first_panorama_multires);
                if(multires) {
                    var multires_config = JSON.parse(window.first_panorama_multires_config);
                    viewer = pannellum.viewer('panorama', {
                        "type": "multires",
                        "multiRes": multires_config,
                        "multiResMinHfov": true,
                        "backgroundColor": [1,1,1],
                        "autoLoad": true,
                        "showFullscreenCtrl": false,
                        "showControls": true,
                        "hfov": parseInt(hfov),
                        "minHfov": parseInt(min_hfov),
                        "maxHfov": parseInt(max_hfov),
                        "friction": window.friction,
                        "zoom_friction": window.zoom_friction,
                        "touchPanSpeedCoeffFactor": window.pan_speed,
                        "strings": {
                            "loadingLabel": "<?php echo _("Loading"); ?>...",
                        },
                    });
                    setTimeout(function () {
                        viewer_initialized = true;
                        $('#hfov').prop("disabled",false);
                        $('#min_hfov').prop("disabled",false);
                        $('#max_hfov').prop("disabled",false);
                        var hfov = parseInt($('#hfov').val());
                        viewer.setHfov(hfov,false);
                        adjust_ratio_hfov_vt();
                        $('#hfov_tab').css('opacity',1);
                        $('#hfov_tab').hide();
                        var hfov = viewer.getHfov();
                        var hfov_t = hfov * ratio_hfov;
                        hfov_t = Math.round(hfov_t);
                        $('#hvof_debug').html(hfov_t);
                        register_viewer_listeners(viewer);
                    },200);
                } else {
                    viewer = pannellum.viewer('panorama', {
                        "type": "equirectangular",
                        "panorama": panorama_image,
                        "autoLoad": true,
                        "showFullscreenCtrl": false,
                        "showControls": true,
                        "hfov": parseInt(hfov),
                        "minHfov": parseInt(min_hfov),
                        "maxHfov": parseInt(max_hfov),
                        "friction": window.friction,
                        "zoom_friction": window.zoom_friction,
                        "touchPanSpeedCoeffFactor": window.pan_speed,
                        "strings": {
                            "loadingLabel": "<?php echo _("Loading"); ?>...",
                        },
                    });
                    viewer.on('load', function () {
                        viewer_initialized = true;
                        $('#hfov').prop("disabled",false);
                        $('#min_hfov').prop("disabled",false);
                        $('#max_hfov').prop("disabled",false);
                        var hfov = parseInt($('#hfov').val());
                        viewer.setHfov(hfov,false);
                        adjust_ratio_hfov_vt();
                        $('#hfov_tab').css('opacity',1);
                        $('#hfov_tab').hide();
                        var hfov = viewer.getHfov();
                        var hfov_t = hfov * ratio_hfov;
                        hfov_t = Math.round(hfov_t);
                        $('#hvof_debug').html(hfov_t);
                        register_viewer_listeners(viewer);
                    });
                }
                viewer.on('zoomchange', function () {
                    var hfov = viewer.getHfov();
                    var hfov_t = hfov;
                    hfov_t = Math.round(hfov_t);
                    $('#hvof_debug').html(hfov_t);
                    var c_hfov = parseInt($('#hfov').val());
                    var c_min_hfov = parseInt($('#min_hfov').val());
                    var c_max_hfov = parseInt($('#max_hfov').val());
                    if(c_hfov==hfov_t) {
                        $('#hfov').addClass("input-highlight");
                    } else {
                        $('#hfov').removeClass("input-highlight");
                    }
                    if(c_min_hfov==hfov_t) {
                        $('#min_hfov').addClass("input-highlight");
                        $("#min_hfov").blur();
                    } else {
                        $('#min_hfov').removeClass("input-highlight");
                    }
                    if(c_max_hfov==hfov_t) {
                        $('#max_hfov').addClass("input-highlight");
                        $("#max_hfov").blur();
                    } else {
                        $('#max_hfov').removeClass("input-highlight");
                    }
                });
                if(multires) {
                    var multires_config = JSON.parse(window.first_panorama_multires_config);
                    viewer_mobile = pannellum.viewer('panorama_mobile', {
                        "type": "multires",
                        "multiRes": multires_config,
                        "multiResMinHfov": true,
                        "backgroundColor": [1, 1, 1],
                        "autoLoad": true,
                        "showFullscreenCtrl": false,
                        "showControls": true,
                        "hfov": parseInt(hfov),
                        "minHfov": parseInt(min_hfov),
                        "maxHfov": parseInt(max_hfov),
                        "friction": window.friction,
                        "zoom_friction": window.zoom_friction,
                        "touchPanSpeedCoeffFactor": window.pan_speed,
                        "strings": {
                            "loadingLabel": "<?php echo _("Loading"); ?>...",
                        },
                    });
                    setTimeout(function () {
                        viewer_mobile_initialized = true;
                        adjust_ratio_hfov_vt_mobile();
                    },200);
                } else {
                    viewer_mobile = pannellum.viewer('panorama_mobile', {
                        "type": "equirectangular",
                        "panorama": panorama_image,
                        "autoLoad": true,
                        "showFullscreenCtrl": false,
                        "showControls": true,
                        "hfov": parseInt(hfov),
                        "minHfov": parseInt(min_hfov),
                        "maxHfov": parseInt(max_hfov),
                        "friction": window.friction_mobile,
                        "zoom_friction": window.zoom_friction_mobile,
                        "touchPanSpeedCoeffFactor": window.pan_speed_mobile,
                        "strings": {
                            "loadingLabel": "<?php echo _("Loading"); ?>...",
                        },
                    });
                    viewer_mobile.on('load', function () {
                        viewer_mobile_initialized = true;
                        adjust_ratio_hfov_vt_mobile();
                    });
                }
            }
        }

        function register_viewer_listeners(viewer) {
            current_viewer=viewer;
            setTimeout(function() {
                document.getElementById('panorama').removeEventListener('mousemove', mouse_move_el);
                document.getElementById('panorama').addEventListener('mousemove', mouse_move_el);
                current_viewer_pitch = parseFloat(viewer.getPitch());
                current_viewer_yaw = parseFloat(viewer.getYaw());
                viewer.on('mousedown',function() {
                    viewer_mov_follow_mouse = false;
                    viewer_mov_pos_change = false;
                });
                viewer.on('animatefinished', function(event) {
                    if(!viewer_mov_pos_change) {
                        clearTimeout(timeout_mov_follow_mouse);
                        current_viewer_pitch = parseFloat(event.pitch);
                        current_viewer_yaw = parseFloat(event.yaw);
                        timeout_mov_follow_mouse = setTimeout(function() {
                            if(mouse_follow_feedback!=0) {
                                viewer_mov_follow_mouse = true;
                            }
                        },20);
                    }
                });
                viewer.on('mouseup', viewer_click_listener);
            },250);
        }

        function viewer_click_listener(event) {
            x_mouseup = event.x;
            y_mouseup = event.y;
            current_viewer_pitch = parseFloat(current_viewer.getPitch());
            current_viewer_yaw = parseFloat(current_viewer.getYaw());
        }

        function mouse_move_el(event) {
            if(viewer_mov_follow_mouse) viewer_move_pos_listener(event);
        }

        function viewer_move_pos_listener(event) {
            var hfov = parseFloat(current_viewer.getHfov());
            var w = document.getElementById('panorama').offsetWidth;
            var h = document.getElementById('panorama').offsetHeight;
            var x = event.x;
            var y = event.y;
            if(x_mouseup==0) x_mouseup = x;
            if(y_mouseup==0) y_mouseup = y;
            var x_c = ((x-x_mouseup)/(w/2))*mouse_follow_feedback*(hfov/100);
            var y_c = ((y-y_mouseup)/(h/2))*mouse_follow_feedback*(hfov/100);
            var look_yaw = current_viewer_yaw+(x_c);
            var look_pitch = current_viewer_pitch-(y_c);
            viewer_mov_pos_change = true;
            clearTimeout(timeout_clear_mov_pos_change);
            current_viewer.lookAt(look_pitch,look_yaw,hfov,false,function() {
                timeout_clear_mov_pos_change = setTimeout(function() {
                    viewer_mov_pos_change = false;
                },100);
            });
        }

        $('#hfov,#min_hfov,#max_hfov').on('input',function (event) {
            window.vt_need_save = true;
            var hfov = parseInt($('#hfov').val());
            var min_hfov = parseInt($('#min_hfov').val());
            var max_hfov = parseInt($('#max_hfov').val());
            if(hfov<min_hfov) {
                hfov = min_hfov;
                $('#hfov').val(hfov);
            }
            if(hfov>max_hfov) {
                hfov = max_hfov;
                $('#hfov').val(hfov);
            }
            if(min_hfov<20) {
                min_hfov=20;
                $('#min_hfov').val(min_hfov);
            }
            if(max_hfov>140) {
                max_hfov=140;
                $('#max_hfov').val(max_hfov);
            }
            viewer.setHfovBounds([min_hfov,max_hfov]);
            viewer_mobile.setHfovBounds([min_hfov,max_hfov]);
            switch(event.currentTarget.id) {
                case 'hfov':
                    viewer.setHfov(hfov,false);
                    viewer_mobile.setHfov(hfov,false);
                    break;
                case 'min_hfov':
                    viewer.setHfov(min_hfov,false);
                    viewer_mobile.setHfov(min_hfov,false);
                    break;
                case 'max_hfov':
                    viewer.setHfov(max_hfov,false);
                    viewer_mobile.setHfov(max_hfov,false);
                    break;
            }
            adjust_ratio_hfov_vt();
            adjust_ratio_hfov_vt_mobile();
        });

        function adjust_ratio_hfov_vt() {
            var c_w = parseFloat($('#panorama').css('width').replace('px',''));
            var c_h = parseFloat($('#panorama').css('height').replace('px',''));
            var ratio_panorama = c_w / c_h;
            ratio_hfov = 1.7771428571428571 / ratio_panorama;
            var hfov = parseInt($('#hfov').val());
            var min_hfov = parseInt($('#min_hfov').val());
            var max_hfov = parseInt($('#max_hfov').val());
            min_hfov = min_hfov / ratio_hfov;
            max_hfov = max_hfov / ratio_hfov;
            hfov = hfov / ratio_hfov;
            viewer.setHfovBounds([min_hfov,max_hfov]);
            viewer.setHfov(hfov,false);
        }

        function adjust_ratio_hfov_vt_mobile() {
            var c_w = parseFloat($('#panorama_mobile').css('width').replace('px',''));
            var c_h = parseFloat($('#panorama_mobile').css('height').replace('px',''));
            var ratio_panorama = c_w / c_h;
            ratio_hfov = window.hfov_mobile_ratio / ratio_panorama;
            var hfov = parseInt($('#hfov').val());
            var min_hfov = parseInt($('#min_hfov').val());
            var max_hfov = parseInt($('#max_hfov').val());
            min_hfov = min_hfov / ratio_hfov;
            max_hfov = max_hfov / ratio_hfov;
            hfov = hfov / ratio_hfov;
            viewer_mobile.setHfovBounds([min_hfov,max_hfov]);
            viewer_mobile.setHfov(hfov,false);
        }

        $('body').on('submit','#frm',function(e){
            e.preventDefault();
            $('#error').hide();
            var url = $(this).attr('action');
            var frm = $(this);
            var data = new FormData();
            if(frm.find('#txtFile[type="file"]').length === 1 ){
                data.append('file', frm.find( '#txtFile' )[0].files[0]);
            }
            var ajax  = new XMLHttpRequest();
            ajax.upload.addEventListener('progress',function(evt){
                var percentage = (evt.loaded/evt.total)*100;
                update_progressbar(Math.round(percentage));
            },false);
            ajax.addEventListener('load',function(evt){
                if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                    show_error(evt.target.responseText);
                } else {
                    if(evt.target.responseText!='') {
                        window.vt_need_save = true;
                        window.song = evt.target.responseText;
                        if(window.s3_enabled==1) {
                            $('#div_player_song audio').attr('src',window.s3_url+'viewer/content/'+window.song);
                        } else {
                            $('#div_player_song audio').attr('src','../viewer/content/'+window.song);
                        }
                        $('#div_delete_song').show();
                        $('#div_player_song').show();
                        $('#div_upload_song').hide();
                        $('#div_exist_song').hide();
                        $('#div_song_bg_volume').show();
                    }
                }
                update_progressbar(0);
                frm[0].reset();
            },false);
            ajax.addEventListener('error',function(evt){
                show_error('upload failed');
                update_progressbar(0);
            },false);
            ajax.addEventListener('abort',function(evt){
                show_error('upload aborted');
                update_progressbar(0);
            },false);
            ajax.open('POST',url);
            ajax.send(data);
            return false;
        });

        function update_progressbar(value){
            $('#progressBar').css('width',value+'%').html(value+'%');
            if(value==0){
                $('.progress_s').hide();
            }else{
                $('.progress_s').show();
            }
        }

        function show_error(error){
            $('.progress_s').hide();
            $('#error').show();
            $('#error').html(error);
        }

        $('body').on('submit','#frm_l',function(e){
            e.preventDefault();
            $('#error_l').hide();
            var url = $(this).attr('action');
            var frm = $(this);
            var data = new FormData();
            if(frm.find('#txtFile_l[type="file"]').length === 1 ){
                data.append('file', frm.find( '#txtFile_l' )[0].files[0]);
            }
            var ajax  = new XMLHttpRequest();
            ajax.upload.addEventListener('progress',function(evt){
                var percentage = (evt.loaded/evt.total)*100;
                update_progressbar_l(Math.round(percentage));
            },false);
            ajax.addEventListener('load',function(evt){
                if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                    show_error_l(evt.target.responseText);
                } else {
                    if(evt.target.responseText!='') {
                        window.vt_need_save = true;
                        window.logo = evt.target.responseText;
                        if(window.s3_enabled==1) {
                            $('#div_image_logo img').attr('src',window.s3_url+'viewer/content/'+window.logo);
                        } else {
                            $('#div_image_logo img').attr('src','../viewer/content/'+window.logo);
                        }
                        $('#div_delete_logo').show();
                        $('#div_image_logo').show();
                        $('#div_link_logo').show();
                        $('#div_upload_logo').hide();
                        $('#div_exist_logo').hide();
                    }
                }
                update_progressbar_l(0);
                frm[0].reset();
            },false);
            ajax.addEventListener('error',function(evt){
                show_error_l('upload failed');
                update_progressbar_l(0);
            },false);
            ajax.addEventListener('abort',function(evt){
                show_error_l('upload aborted');
                update_progressbar_l(0);
            },false);
            ajax.open('POST',url);
            ajax.send(data);
            return false;
        });

        function update_progressbar_l(value){
            $('#progressBar_l').css('width',value+'%').html(value+'%');
            if(value==0){
                $('.progress_l').hide();
            }else{
                $('.progress_l').show();
            }
        }

        function show_error_l(error){
            $('.progress_l').hide();
            $('#error_l').show();
            $('#error_l').html(error);
        }

        $('body').on('submit','#frm_pw',function(e){
            e.preventDefault();
            $('#error_pw').hide();
            var url = $(this).attr('action');
            var frm = $(this);
            var data = new FormData();
            if(frm.find('#txtFile_pw[type="file"]').length === 1 ){
                data.append('file', frm.find( '#txtFile_pw' )[0].files[0]);
            }
            var ajax  = new XMLHttpRequest();
            ajax.upload.addEventListener('progress',function(evt){
                var percentage = (evt.loaded/evt.total)*100;
                update_progressbar_pw(Math.round(percentage));
            },false);
            ajax.addEventListener('load',function(evt){
                if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                    show_error_pw(evt.target.responseText);
                } else {
                    if(evt.target.responseText!='') {
                        window.vt_need_save = true;
                        window.poweredby_image = evt.target.responseText;
                        if(window.s3_enabled==1) {
                            $('#div_image_poweredby img').attr('src',window.s3_url+'viewer/content/'+window.poweredby_image);
                        } else {
                            $('#div_image_poweredby img').attr('src','../viewer/content/'+window.poweredby_image);
                        }
                        $('#div_delete_poweredby').show();
                        $('#div_image_poweredby').show();
                        $('#div_link_poweredby').show();
                        $('#div_upload_poweredby').hide();
                        $('#div_exist_poweredby').hide();
                    }
                }
                update_progressbar_pw(0);
                frm[0].reset();
            },false);
            ajax.addEventListener('error',function(evt){
                show_error_pw('upload failed');
                update_progressbar_pw(0);
            },false);
            ajax.addEventListener('abort',function(evt){
                show_error_pw('upload aborted');
                update_progressbar_pw(0);
            },false);
            ajax.open('POST',url);
            ajax.send(data);
            return false;
        });

        function update_progressbar_pw(value){
            $('#progressBar_pw').css('width',value+'%').html(value+'%');
            if(value==0){
                $('.progress_pw').hide();
            }else{
                $('.progress_pw').show();
            }
        }

        function show_error_pw(error){
            $('.progress_pw').hide();
            $('#error_pw').show();
            $('#error_pw').html(error);
        }

        $('body').on('submit','#frm_n',function(e){
            e.preventDefault();
            $('#error_n').hide();
            var url = $(this).attr('action');
            var frm = $(this);
            var data = new FormData();
            if(frm.find('#txtFile_n[type="file"]').length === 1 ){
                data.append('file', frm.find( '#txtFile_n' )[0].files[0]);
            }
            var ajax  = new XMLHttpRequest();
            ajax.upload.addEventListener('progress',function(evt){
                var percentage = (evt.loaded/evt.total)*100;
                update_progressbar_n(Math.round(percentage));
            },false);
            ajax.addEventListener('load',function(evt){
                if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                    show_error_n(evt.target.responseText);
                } else {
                    if(evt.target.responseText!='') {
                        window.vt_need_save = true;
                        window.nadir_logo = evt.target.responseText;
                        if(window.s3_enabled==1) {
                            $('#div_image_nadir_logo img').attr('src',window.s3_url+'viewer/content/'+window.nadir_logo);
                        } else {
                            $('#div_image_nadir_logo img').attr('src','../viewer/content/'+window.nadir_logo);
                        }
                        $('#div_delete_nadir_logo').show();
                        $('#div_image_nadir_logo').show();
                        $('#div_upload_nadir_logo').hide();
                        $('#div_exist_nadir_logo').hide();
                    }
                }
                update_progressbar_n(0);
                frm[0].reset();
            },false);
            ajax.addEventListener('error',function(evt){
                show_error_n('upload failed');
                update_progressbar_n(0);
            },false);
            ajax.addEventListener('abort',function(evt){
                show_error_n('upload aborted');
                update_progressbar_n(0);
            },false);
            ajax.open('POST',url);
            ajax.send(data);
            return false;
        });

        function update_progressbar_n(value){
            $('#progressBar_n').css('width',value+'%').html(value+'%');
            if(value==0){
                $('.progress_n').hide();
            }else{
                $('.progress_n').show();
            }
        }

        function show_error_n(error){
            $('.progress_n').hide();
            $('#error_n').show();
            $('#error_n').html(error);
        }

        $('body').on('submit','#frm_b',function(e){
            e.preventDefault();
            $('#error_b').hide();
            var url = $(this).attr('action');
            var frm = $(this);
            var data = new FormData();
            if(frm.find('#txtFile_b[type="file"]').length === 1 ){
                data.append('file', frm.find( '#txtFile_b' )[0].files[0]);
            }
            var ajax  = new XMLHttpRequest();
            ajax.upload.addEventListener('progress',function(evt){
                var percentage = (evt.loaded/evt.total)*100;
                update_progressbar_b(Math.round(percentage));
            },false);
            ajax.addEventListener('load',function(evt){
                if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                    show_error_b(evt.target.responseText);
                } else {
                    if(evt.target.responseText!='') {
                        window.vt_need_save = true;
                        window.background_image = evt.target.responseText;
                        if(window.s3_enabled==1) {
                            $('#div_image_bg img').attr('src',window.s3_url+'viewer/content/'+window.background_image);
                        } else {
                            $('#div_image_bg img').attr('src','../viewer/content/'+window.background_image);
                        }
                        $('#div_delete_bg').show();
                        $('#div_image_bg').show();
                        $('#div_upload_bg').hide();
                        $('#div_exist_bg').hide();
                    }
                }
                update_progressbar_b(0);
                frm[0].reset();
            },false);
            ajax.addEventListener('error',function(evt){
                show_error_b('upload failed');
                update_progressbar_b(0);
            },false);
            ajax.addEventListener('abort',function(evt){
                show_error_b('upload aborted');
                update_progressbar_b(0);
            },false);
            ajax.open('POST',url);
            ajax.send(data);
            return false;
        });

        function update_progressbar_b(value){
            $('#progressBar_b').css('width',value+'%').html(value+'%');
            if(value==0){
                $('.progress_b').hide();
            }else{
                $('.progress_b').show();
            }
        }

        function show_error_b(error){
            $('.progress_b').hide();
            $('#error_b').show();
            $('#error_b').html(error);
        }

        $('body').on('submit','#frm_b_m',function(e){
            e.preventDefault();
            $('#error_b_m').hide();
            var url = $(this).attr('action');
            var frm = $(this);
            var data = new FormData();
            if(frm.find('#txtFile_b_m[type="file"]').length === 1 ){
                data.append('file', frm.find( '#txtFile_b_m' )[0].files[0]);
            }
            var ajax  = new XMLHttpRequest();
            ajax.upload.addEventListener('progress',function(evt){
                var percentage = (evt.loaded/evt.total)*100;
                update_progressbar_b_m(Math.round(percentage));
            },false);
            ajax.addEventListener('load',function(evt){
                if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                    show_error_b_m(evt.target.responseText);
                } else {
                    if(evt.target.responseText!='') {
                        window.vt_need_save = true;
                        window.background_image_mobile = evt.target.responseText;
                        if(window.s3_enabled==1) {
                            $('#div_image_bg_m img').attr('src',window.s3_url+'viewer/content/'+window.background_image_mobile);
                        } else {
                            $('#div_image_bg_m img').attr('src','../viewer/content/'+window.background_image_mobile);
                        }
                        $('#div_delete_bg_m').show();
                        $('#div_image_bg_m').show();
                        $('#div_upload_bg_m').hide();
                        $('#div_exist_bg_m').hide();
                    }
                }
                update_progressbar_b_m(0);
                frm[0].reset();
            },false);
            ajax.addEventListener('error',function(evt){
                show_error_b_m('upload failed');
                update_progressbar_b_m(0);
            },false);
            ajax.addEventListener('abort',function(evt){
                show_error_b_m('upload aborted');
                update_progressbar_b_m(0);
            },false);
            ajax.open('POST',url);
            ajax.send(data);
            return false;
        });

        function update_progressbar_b_m(value){
            $('#progressBar_b_m').css('width',value+'%').html(value+'%');
            if(value==0){
                $('.progress_b_m').hide();
            }else{
                $('.progress_b_m').show();
            }
        }

        function show_error_b_m(error){
            $('.progress_b_m').hide();
            $('#error_b_m').show();
            $('#error_b_m').html(error);
        }

        $('body').on('submit','#frm_b_v',function(e){
            e.preventDefault();
            $('#error_b_v').hide();
            var url = $(this).attr('action');
            var frm = $(this);
            var data = new FormData();
            if(frm.find('#txtFile_b_v[type="file"]').length === 1 ){
                data.append('file', frm.find( '#txtFile_b_v' )[0].files[0]);
            }
            var ajax  = new XMLHttpRequest();
            ajax.upload.addEventListener('progress',function(evt){
                var percentage = (evt.loaded/evt.total)*100;
                update_progressbar_b_v(Math.round(percentage));
            },false);
            ajax.addEventListener('load',function(evt){
                if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                    show_error_b_v(evt.target.responseText);
                } else {
                    if(evt.target.responseText!='') {
                        window.vt_need_save = true;
                        window.background_video = evt.target.responseText;
                        if(window.s3_enabled==1) {
                            $('#div_video_bg video source').attr('src',window.s3_url+'viewer/content/'+window.background_video+'#t=2');
                        } else {
                            $('#div_video_bg video source').attr('src','../viewer/content/'+window.background_video+'#t=2');
                        }
                        $('#div_video_bg video').get(0).load();
                        $('#div_delete_video_bg').show();
                        $('#div_video_bg').show();
                        $('#div_video_params').show();
                        $('#div_upload_video_bg').hide();
                        $('#div_exist_video_bg').hide();
                        $('#background_video_delay').prop('disabled',false);
                    }
                }
                update_progressbar_b_v(0);
                frm[0].reset();
            },false);
            ajax.addEventListener('error',function(evt){
                show_error_b_v('upload failed');
                update_progressbar_b_v(0);
            },false);
            ajax.addEventListener('abort',function(evt){
                show_error_b_v('upload aborted');
                update_progressbar_b_v(0);
            },false);
            ajax.open('POST',url);
            ajax.send(data);
            return false;
        });

        function update_progressbar_b_v(value){
            $('#progressBar_b_v').css('width',value+'%').html(value+'%');
            if(value==0){
                $('.progress_b_v').hide();
            }else{
                $('.progress_b_v').show();
            }
        }

        function show_error_b_v(error){
            $('.progress_b_v').hide();
            $('#error_b_v').show();
            $('#error_b_v').html(error);
        }

        $('body').on('submit','#frm_b_v_m',function(e){
            e.preventDefault();
            $('#error_b_v_m').hide();
            var url = $(this).attr('action');
            var frm = $(this);
            var data = new FormData();
            if(frm.find('#txtFile_b_v_m[type="file"]').length === 1 ){
                data.append('file', frm.find( '#txtFile_b_v_m' )[0].files[0]);
            }
            var ajax  = new XMLHttpRequest();
            ajax.upload.addEventListener('progress',function(evt){
                var percentage = (evt.loaded/evt.total)*100;
                update_progressbar_b_v_m(Math.round(percentage));
            },false);
            ajax.addEventListener('load',function(evt){
                if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                    show_error_b_v_m(evt.target.responseText);
                } else {
                    if(evt.target.responseText!='') {
                        window.vt_need_save = true;
                        window.background_video_mobile = evt.target.responseText;
                        if(window.s3_enabled==1) {
                            $('#div_video_bg_m video source').attr('src',window.s3_url+'viewer/content/'+window.background_video_mobile+'#t=2');
                        } else {
                            $('#div_video_bg_m video source').attr('src','../viewer/content/'+window.background_video_mobile+'#t=2');
                        }
                        $('#div_video_bg_m video').get(0).load();
                        $('#div_delete_video_bg_m').show();
                        $('#div_video_bg_m').show();
                        $('#div_video_m_params').show();
                        $('#div_upload_video_bg_m').hide();
                        $('#div_exist_video_bg_m').hide();
                        $('#background_video_delay_m').prop('disabled',false);
                    }
                }
                update_progressbar_b_v_m(0);
                frm[0].reset();
            },false);
            ajax.addEventListener('error',function(evt){
                show_error_b_v_m('upload failed');
                update_progressbar_b_v_m(0);
            },false);
            ajax.addEventListener('abort',function(evt){
                show_error_b_v_m('upload aborted');
                update_progressbar_b_v_m(0);
            },false);
            ajax.open('POST',url);
            ajax.send(data);
            return false;
        });

        function update_progressbar_b_v_m(value){
            $('#progressBar_b_v_m').css('width',value+'%').html(value+'%');
            if(value==0){
                $('.progress_b_v_m').hide();
            }else{
                $('.progress_b_v_m').show();
            }
        }

        function show_error_b_v_m(error){
            $('.progress_b_v_m').hide();
            $('#error_b_v_m').show();
            $('#error_b_v_m').html(error);
        }

        $('body').on('submit','#frm_id',function(e){
            e.preventDefault();
            $('#error_id').hide();
            var url = $(this).attr('action');
            var frm = $(this);
            var data = new FormData();
            if(frm.find('#txtFile_id[type="file"]').length === 1 ){
                data.append('file', frm.find( '#txtFile_id' )[0].files[0]);
            }
            var ajax  = new XMLHttpRequest();
            ajax.upload.addEventListener('progress',function(evt){
                var percentage = (evt.loaded/evt.total)*100;
                update_progressbar_id(Math.round(percentage));
            },false);
            ajax.addEventListener('load',function(evt){
                if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                    show_error_id(evt.target.responseText);
                } else {
                    if(evt.target.responseText!='') {
                        window.vt_need_save = true;
                        if($('#div_exist_introd').css('display')=='block') {
                            window.intro_desktop = evt.target.responseText;
                            if(window.s3_enabled==1) {
                                $('#div_image_introd img').attr('src',window.s3_url+'viewer/content/'+window.intro_desktop);
                            } else {
                                $('#div_image_introd img').attr('src','../viewer/content/'+window.intro_desktop);
                            }
                            $('#div_delete_introd').show();
                            $('#div_image_introd').show();
                            $('#div_upload_introd').hide();
                            $('#div_exist_introd').hide();
                        } else {
                            $('.input_lang[data-target-id="div_exist_introd"]').each(function() {
                                if($(this).css('display')=='block') {
                                    var lang = $(this).attr('data-lang');
                                    window.introd_file_langs[lang] = evt.target.responseText;
                                    if(window.s3_enabled==1) {
                                        $('#div_image_introd_'+lang+' img').attr('src',window.s3_url+'viewer/content/'+window.introd_file_langs[lang]);
                                    } else {
                                        $('#div_image_introd_'+lang+' img').attr('src','../viewer/content/'+window.introd_file_langs[lang]);
                                    }
                                    $('#div_delete_introd').show();
                                    $('#div_image_introd_'+lang).show();
                                    $('#div_upload_introd').hide();
                                    $('#div_exist_introd_'+lang).hide();
                                }
                            });
                        }
                    }
                }
                update_progressbar_id(0);
                frm[0].reset();
            },false);
            ajax.addEventListener('error',function(evt){
                show_error_id('upload failed');
                update_progressbar_id(0);
            },false);
            ajax.addEventListener('abort',function(evt){
                show_error_id('upload aborted');
                update_progressbar_id(0);
            },false);
            ajax.open('POST',url);
            ajax.send(data);
            return false;
        });

        function update_progressbar_id(value){
            $('#progressBar_id').css('width',value+'%').html(value+'%');
            if(value==0){
                $('.progress_id').hide();
            }else{
                $('.progress_id').show();
            }
        }

        function show_error_id(error){
            $('.progress_id').hide();
            $('#error_id').show();
            $('#error_id').html(error);
        }

        $('body').on('submit','#frm_im',function(e){
            e.preventDefault();
            $('#error_im').hide();
            var url = $(this).attr('action');
            var frm = $(this);
            var data = new FormData();
            if(frm.find('#txtFile_im[type="file"]').length === 1 ){
                data.append('file', frm.find( '#txtFile_im' )[0].files[0]);
            }
            var ajax  = new XMLHttpRequest();
            ajax.upload.addEventListener('progress',function(evt){
                var percentage = (evt.loaded/evt.total)*100;
                update_progressbar_im(Math.round(percentage));
            },false);
            ajax.addEventListener('load',function(evt){
                if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                    show_error_im(evt.target.responseText);
                } else {
                    if(evt.target.responseText!='') {
                        window.vt_need_save = true;
                        if($('#div_exist_introm').css('display')=='block') {
                            window.intro_mobile = evt.target.responseText;
                            if(window.s3_enabled==1) {
                                $('#div_image_introm img').attr('src',window.s3_url+'viewer/content/'+window.intro_mobile);
                            } else {
                                $('#div_image_introm img').attr('src','../viewer/content/'+window.intro_mobile);
                            }
                            $('#div_delete_introm').show();
                            $('#div_image_introm').show();
                            $('#div_upload_introm').hide();
                            $('#div_exist_introm').hide();
                        } else {
                            $('.input_lang[data-target-id="div_exist_introm"]').each(function() {
                                if($(this).css('display')=='block') {
                                    var lang = $(this).attr('data-lang');
                                    window.introm_file_langs[lang] = evt.target.responseText;
                                    if(window.s3_enabled==1) {
                                        $('#div_image_introm_'+lang+' img').attr('src',window.s3_url+'viewer/content/'+window.introm_file_langs[lang]);
                                    } else {
                                        $('#div_image_introm_'+lang+' img').attr('src','../viewer/content/'+window.introm_file_langs[lang]);
                                    }
                                    $('#div_delete_introm').show();
                                    $('#div_image_introm_'+lang).show();
                                    $('#div_upload_introm').hide();
                                    $('#div_exist_introm_'+lang).hide();
                                }
                            });
                        }
                    }
                }
                update_progressbar_im(0);
                frm[0].reset();
            },false);
            ajax.addEventListener('error',function(evt){
                show_error_im('upload failed');
                update_progressbar_im(0);
            },false);
            ajax.addEventListener('abort',function(evt){
                show_error_im('upload aborted');
                update_progressbar_im(0);
            },false);
            ajax.open('POST',url);
            ajax.send(data);
            return false;
        });

        function update_progressbar_im(value){
            $('#progressBar_im').css('width',value+'%').html(value+'%');
            if(value==0){
                $('.progress_im').hide();
            }else{
                $('.progress_im').show();
            }
        }

        function show_error_im(error){
            $('.progress_im').hide();
            $('#error_im').show();
            $('#error_im').html(error);
        }

        $('body').on('submit','#frm_av',function(e){
            e.preventDefault();
            $('#error_av').hide();
            var url = $(this).attr('action');
            var frm = $(this);
            var data = new FormData();
            if(frm.find('#txtFile_av[type="file"]').length === 1 ){
                data.append('file', frm.find( '#txtFile_av' )[0].files[0]);
            }
            var ajax  = new XMLHttpRequest();
            ajax.upload.addEventListener('progress',function(evt){
                var percentage = (evt.loaded/evt.total)*100;
                update_progressbar_av(Math.round(percentage));
            },false);
            ajax.addEventListener('load',function(evt){
                if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                    show_error_av(evt.target.responseText);
                } else {
                    if(evt.target.responseText!='') {
                        window.vt_need_save = true;
                        var file_uploaded = evt.target.responseText;
                        if($('#div_avatar_video_extensions').css('display')=='flex') {
                            var exists_videos = $('#avatar_video_content').val();
                            var avatar_video_content = preview_avatar_video(exists_videos,file_uploaded);
                            $('#avatar_video_content').val(avatar_video_content);
                        } else {
                            $('.input_lang[data-target-id="div_avatar_video_extensions"]').each(function() {
                                if($(this).css('display')=='flex') {
                                    var lang = $(this).attr('data-lang');
                                    var exists_videos = $('#avatar_video_content_'+lang).val();
                                    var avatar_video_content = preview_avatar_video(exists_videos,file_uploaded);
                                    $('#avatar_video_content_'+lang).val(avatar_video_content);
                                }
                            });
                        }
                    }
                }
                update_progressbar_av(0);
                frm[0].reset();
            },false);
            ajax.addEventListener('error',function(evt){
                show_error_av('upload failed');
                update_progressbar_av(0);
            },false);
            ajax.addEventListener('abort',function(evt){
                show_error_av('upload aborted');
                update_progressbar_av(0);
            },false);
            ajax.open('POST',url);
            ajax.send(data);
            return false;
        });

        function update_progressbar_av(value){
            $('#progressBar_av').css('width',value+'%').html(value+'%');
            if(value==0){
                $('.progress_av').hide();
            }else{
                $('.progress_av').show();
            }
        }

        function show_error_av(error){
            $('.progress_av').hide();
            $('#error_av').show();
            $('#error_av').html(error);
        }

        $(window).resize(function() {
            if(viewer_initialized) {
                adjust_ratio_hfov_vt();
            }
            if(viewer_mobile_initialized) {
                adjust_ratio_hfov_vt_mobile();
            }
        });

        $("input").change(function(){
            window.vt_need_save = true;
        });

        $("select").change(function(){
            window.vt_need_save = true;
        });

        $(window).on('beforeunload', function(){
            if(window.vt_need_save) {
                var c=confirm();
                if(c) return true; else return false;
            }
        });

        window.change_hfov_mobile_ratio = function() {
            var hfov_mobile_ratio = $('#hfov_mobile_ratio').val();
            $('#hfov_mobile_ratio_val').html(hfov_mobile_ratio);
            window.hfov_mobile_ratio = parseFloat(hfov_mobile_ratio);
            adjust_ratio_hfov_vt_mobile();
        }

        window.change_pan_speed = function () {
            var pan_speed = $('#pan_speed').val();
            $('#pan_speed_val').html(pan_speed);
            viewer.setTouchPanSpeedCoeffFactor(parseFloat(pan_speed));
        }

        window.change_pan_speed_mobile = function () {
            var pan_speed_mobile = $('#pan_speed_mobile').val();
            $('#pan_speed_mobile_val').html(pan_speed_mobile);
            viewer_mobile.setTouchPanSpeedCoeffFactor(parseFloat(pan_speed_mobile));
        }

        window.change_friction = function () {
            var friction = $('#friction').val();
            $('#friction_val').html(friction);
            viewer.setFriction(parseFloat(friction));
        }

        window.change_friction_mobile = function () {
            var friction_mobile = $('#friction_mobile').val();
            $('#friction_mobile_val').html(friction_mobile);
            viewer_mobile.setFriction(parseFloat(friction_mobile));
        }

        window.change_zoom_friction = function () {
            var friction = $('#zoom_friction').val();
            $('#zoom_friction_val').html(friction);
            viewer.setZoomFriction(parseFloat(friction));
        }

        window.change_zoom_friction_mobile = function () {
            var friction_mobile = $('#zoom_friction_mobile').val();
            $('#zoom_friction_mobile_val').html(friction_mobile);
            viewer_mobile.setZoomFriction(parseFloat(friction_mobile));
        }

        window.change_mouse_follow_feedback = function () {
            var mouse_follow_feedback = $('#mouse_follow_feedback').val();
            $('#mouse_follow_feedback_val').html(mouse_follow_feedback);
            window.mouse_follow_feedback = mouse_follow_feedback;
        }

        window.change_zoom_to_pointer = function () {
            window.zoom_to_pointer = $('#zoom_to_pointer').is(':checked');
        }

        window.delete_image_vr_tour = function(type) {
            $('#div_upload_vr_'+type).show();
            $('#div_delete_vr_'+type).hide();
            $.ajax({
                url: "ajax/delete_vr_icon_tour.php",
                type: "POST",
                data: {
                    type: type,
                    id_vt: window.id_virtualtour
                },
                async: false,
                success: function (json) {
                    var image = new Image();
                    var url_image = '../vr/img/custom/'+type+'.png';
                    image.src = url_image;
                    if (image.width == 0) {
                        $('#image_vr_'+type).attr('src','../vr/img/'+type+'.png');
                    } else {
                        $('#image_vr_'+type).attr('src','../vr/img/custom/'+type+'.png');
                    }
                }
            });
        }

        window.upadte_progressbar_vr_icon = function(value,id){
            $('#progressBar_vr_'+id).css('width',value+'%').html(value+'%');
            if(value==0){
                $('#progress_vr_'+id).hide();
            }else{
                $('#progress_vr_'+id).show();
            }
        }

        window.show_error_vr_icon = function(error,id){
            $('#progress_vr_'+id).hide();
            $('#error_vr_'+id).show();
            $('#error_vr_'+id).html(error);
        }

        $('.image_gallery_slider').on('click', function() {
            $(this).toggleClass('selected');
            var count = $('.image_gallery_slider').filter('.selected').length;
            if(count==0) {
                $('#btn_add_image_to_slider').addClass('disabled');
            } else {
                $('#btn_add_image_to_slider').removeClass('disabled');
            }
        });

        window.change_vt_version_loading = function() {
            var id_version = parseInt($('#vt_version option:selected').attr('id'));
            $('.loading_settings_v').hide();
            $('#loading_settings').hide();
            if(id_version==0) {
                $('#loading_settings').show();
            } else {
                $('#loading_settings_v'+id_version).show();
            }
            try {
                window.loading_background_color_spectrum.spectrum('hide');
                window.loading_text_color_spectrum.spectrum('hide');
                $('.loading_background_color_v').spectrum("hide");
                $('.loading_text_color_v').spectrum("hide");
            } catch (e) {}
        }
    })(jQuery); // End of use strict

    function change_click_anywhere() {
        if($('#click_anywhere').is(':checked')) {
            $('#hide_markers').prop('disabled',false);
            if($('#hide_markers').is(':checked')) {
                $('#hover_markers').prop('disabled',false);
            } else {
                $('#hover_markers').prop('disabled',true);
            }
        } else {
            $('#hide_markers').prop('disabled',true);
            $('#hover_markers').prop('disabled',true);
        }
    }

    function change_transition_zoom() {
        var transition_zoom = $('#transition_zoom').val();
        $('#transition_zoom_val').html(transition_zoom);
    }

    function change_transition_hfov() {
        var transition_hfov = $('#transition_hfov').val();
        $('#transition_hfov_val').html(transition_hfov);
    }
</script>
<?php
function print_vr_icon_block($type) {
    global $demo,$id_virtual_tour,$vr_icons_size;
    if (file_exists(dirname(__FILE__).'/../vr/img/'.$id_virtual_tour.'/'.$type.'.png')) {
        $custom = true;
        $image_url = "../vr/img/$id_virtual_tour/$type.png?v=".time();
    } else {
        $custom = false;
        if (file_exists(dirname(__FILE__).'/../vr/img/custom/'.$type.'.png')) {
            $image_url = "../vr/img/custom/$type.png?v=".time();
        } else {
            $image_url = "../vr/img/$type.png";
        }
    }
    if(isset($vr_icons_size[str_replace('poi_','',$type)])) {
        $size = $vr_icons_size[str_replace('poi_','',$type)];
    } else {
        $size = "medium";
    }
    $script = <<<SCRIPT
<script>
  $('body').on('submit','#frm_{$type}',function(e){
        e.preventDefault();
        $('#error_{$type}').hide();
        var url = $(this).attr('action');
        var frm = $(this);
        var data = new FormData();
        if(frm.find('#txtFile_vr_{$type}[type="file"]').length === 1 ){
            data.append('file', frm.find( '#txtFile_vr_{$type}' )[0].files[0]);
        }
        var ajax  = new XMLHttpRequest();
        ajax.upload.addEventListener('progress',function(evt){
            var percentage = (evt.loaded/evt.total)*100;
            upadte_progressbar_vr_icon(Math.round(percentage),'{$type}');
        },false);
        ajax.addEventListener('load',function(evt){
            if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                show_error_vr_icon(evt.target.responseText,'{$type}');
            } else {
                if(evt.target.responseText!='') {
                    $('#div_upload_vr_{$type}').hide();
                    $('#div_delete_vr_{$type}').show();
                    $('#image_vr_{$type}').attr('src','../vr/img/{$id_virtual_tour}/'+evt.target.responseText+'?v='+Date.now());
                }
            }
            upadte_progressbar_vr_icon(0,'{$type}');
            frm[0].reset();
        },false);
        ajax.addEventListener('error',function(evt){
            show_error_vr_icon('upload failed','{$type}');
            upadte_progressbar_vr_icon(0,'{$type}');
        },false);
        ajax.addEventListener('abort',function(evt){
            show_error_vr_icon('upload aborted','{$type}');
            upadte_progressbar_vr_icon(0,'{$type}');
        },false);
        ajax.open('POST',url);
        ajax.send(data);
        return false;
    });
</script>
SCRIPT;
    return '<img id="image_vr_'.$type.'" style="width:100%;margin:0 auto;max-width:100px;" src="'.$image_url.'" />
            <div class="row">
                <div style="display: '.(($custom) ? 'block':'none').'" id="div_delete_vr_'.$type.'" class="col-md-12 mt-4 mb-3">
                    <button '.(($demo) ? 'disabled':'').' onclick="delete_image_vr_tour(\''.$type.'\');" class="btn btn-block btn-danger">'._("Remove Custom Icon").'</button>
                </div>
            </div>
            <div style="display: '.(($custom) ? 'none':'block').'" id="div_upload_vr_'.$type.'" class="mt-3">
                <form id="frm_'.$type.'" action="ajax/upload_vr_icon_tour.php?type='.$type.'&id_vt='.$id_virtual_tour.'" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="input-group">
                                <div class="custom-file">
                                    <input type="file" class="custom-file-input" id="txtFile_vr_'.$type.'" name="txtFile_vr_'.$type.'" />
                                    <label class="custom-file-label text-left" for="txtFile_vr_'.$type.'">'._("Choose file").'</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <input '.(($demo) ? 'disabled':'').' type="submit" class="btn btn-block btn-success" id="btnUpload_vr_'.$type.'" value="'._("Upload Icon").'" />
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="preview text-center">
                                <div id="progress_vr_'.$type.'" class="progress mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                    <div class="progress-bar" id="progressBar_vr_'.$type.'" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                        0%
                                    </div>
                                </div>
                                <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error_vr_'.$type.'"></div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="row '.(($type=='close') ? 'd-none' : '').'">
                <div class="col-md-12">
                    <div class="form-group">
                        <select id="vr_icon_size_'.$type.'" data-type="'.str_replace('poi_','',$type).'" class="form-control vr_icon_size_select">
                            <option '.(($size=='hidden') ? 'selected' : '').' '.(($type=='marker') ? 'disabled' : '').' id="hidden">'._("Hidden").'</option>
                            <option '.(($size=='extra_small') ? 'selected' : '').' id="extra_small">'._("Extra Small Size").'</option>
                            <option '.(($size=='small') ? 'selected' : '').' id="small">'._("Small Size").'</option>
                            <option '.(($size=='medium') ? 'selected' : '').' id="medium">'._("Medium Size").'</option>
                            <option '.(($size=='large') ? 'selected' : '').' id="large">'._("Large Size").'</option>
                            <option '.(($size=='extra_large') ? 'selected' : '').' id="extra_large">'._("Extra Large Size").'</option>
                        </select>
                    </div>
                </div>
            </div>
            '.$script;
}