<?php
session_start();
$id_user = $_SESSION['id_user'];
$features = true;
if($user_info['role']!='administrator' || !$user_info['super_admin']) {
    $features = false;
}
$array_content_features = array();
$array_name_features = array();
$query = "SELECT feature,name,content FROM svt_features;";
$result = $mysqli->query($query);
if($result) {
    if($result->num_rows>0) {
        while($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $feature = $row['feature'];
            if($feature == '3d_view') $feature = 'dollhouse';
            if($feature == '360_video_tour') $feature = 'video_360_tour';
            $content = $row['content'];
            $name = $row['name'];
            if(!empty($content)) {
                $array_content_features[$feature] = $content;
            }
            if(!empty($name)) {
                $array_name_features[$feature] = $name;
            }
        }
    }
}

$tmp_languages = get_languages_backend();
$array_languages = $tmp_languages[0];
$default_language = $tmp_languages[1];
$array_input_lang = array();
$query_lang = "SELECT fl.*,f.feature FROM svt_features_lang as fl JOIN svt_features as f ON f.id=fl.id_feature;";
$result_lang = $mysqli->query($query_lang);
if($result_lang) {
    if ($result_lang->num_rows > 0) {
        while($row_lang = $result_lang->fetch_array(MYSQLI_ASSOC)) {
            $language = $row_lang['language'];
            $feature = $row_lang['feature'];
            unset($row_lang['id_feature']);
            unset($row_lang['language']);
            unset($row_lang['feature']);
            if(!array_key_exists($feature, $array_input_lang)) {
                $array_input_lang[$feature]=array();
            }
            $array_input_lang[$feature][$language]=$row_lang;
        }
    }
}

function display_feature_block($feature) {
    global $array_content_features,$array_name_features,$array_languages,$default_language,$array_input_lang;
    $html = '<div class="col-md-12">
                <div class="form-group">
                    <label for="'.$feature.'_name">'._("Name").'</label>'.print_language_input_selector($array_languages,$default_language,"{$feature}_name").'
                    <input data-feature="'.$feature.'" type="text" class="form-control feature_name" id="'.$feature.'_name" value="'.$array_name_features[$feature].'" />';
    foreach ($array_languages as $lang) {
        if($lang!=$default_language) :
            $html .= '<input style="display:none;" data-feature="'.$feature.'" type="text" class="form-control input_lang feature_name_lang" id="'.$feature.'_name_'.$lang.'" data-target-id="'.$feature.'_name" data-lang="'.$lang.'" value="'.$array_input_lang[$feature][$lang]['name'].'" />';
        endif;
    }
    $html .= '  </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <label for="'.$feature.'_content">'._("Description").'</label>'.print_language_input_selector($array_languages,$default_language,"feature_{$feature}_content").'
                    <div><div data-feature="'.$feature.'" class="feature_content" id="'.$feature.'_content">'.$array_content_features[$feature].'</div></div>';
    foreach ($array_languages as $lang) {
        if($lang!=$default_language) :
            $html .= '<div style="display:none;"><div data-feature="'.$feature.'" class="input_lang feature_content_lang" id="'.$feature.'_content_'.$lang.'" data-target-id="'.$feature.'_content" data-lang="'.$lang.'">'.$array_input_lang[$feature][$lang]['content'].'</div></div>';
        endif;
    }
    $html .= '</div>
            </div>';
    return $html;
}
?>

<?php if(!$features): ?>
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

<div class="row">
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Info Box"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('info_box'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Gallery"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('gallery'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Download Slideshow"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('download_slideshow'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Maps"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('maps'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Presentation"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('presentation'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("360 Video Tour"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('video_360_tour'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Video Projects"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('video_projects'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("3D View"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('dollhouse'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Editor UI"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('editor_ui'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Measurements"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('measurements'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Icons Library"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('icons_library'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Media Library"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('media_library'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Music Library"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('music_library'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Sound Library"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('sound_library'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Voice Commands"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('voice_commands'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Statistics"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('statistics'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Multi Language"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('multilanguage'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Automatic Translation"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('auto_translation'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Shop"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('shop'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Learning"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('learning'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Landing"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('landing'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Showcase"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('showcase'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Globe"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('globe'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Your own Logo"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('logo'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Powered By - Logo / Text"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('poweredby'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Hide Tripod"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('nadir'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Loading Image/Video"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('loading_iv'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Loading Image Slider"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('intro_slider'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Custom HTML"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('custom_html'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Background Music"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('background_music'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Comments"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('comments'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Auto Rotation"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('auto_rotation'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Fly-In Animation"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('flyin'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Multi-Resolution"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('multires'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Live Session"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('live_session'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Meeting"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('meeting'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Annotations"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('annotations'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Avatar Video"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('avatar_video'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Video 360 Panorama"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('video_360_panorama'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("A.I. Panorama"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('ai_panorama'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("A.I. Enhancement"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('ai_panorama_autoenhance'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Multiple Room's Views"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('multiple_rooms_view'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Protect Rooms (Passcode, Leads)"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('protect_rooms'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Right Click Content"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('right_click_content'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Facebook / Whatsapp Chat"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('chat'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Share"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('share'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Snapshot"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('snapshot'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Forms"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('forms'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Device Orientation"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('device_orientation'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Virtual Reality"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('vr'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Expiring Dates"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('expiring_dates'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Meta Tags"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('meta_tags'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Protect tour (Password, Leads)"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('protect_tour'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Download Tour"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('download_tour'); ?>
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Import / Export Tour"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('import_export_tour'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php if (file_exists('../gsv/index.php')) : ?>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Google Street View Publish"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('gsv_publish'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="col-lg-4 col-md-6 mb-3">
        <div class="card shadow">
            <div class="card-header">
                <?php echo _("Custom Domain"); ?>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php echo display_feature_block('custom_domain'); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.features_need_save = false;
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
        window.feature_content_editor = {};
        window.feature_content_editor_lang = {};
        $(document).ready(function () {
            var toolbarOptions = [
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],['image'],
                ['clean']
            ];
            $('.feature_content').each(function() {
                var id = $(this).attr('id');
                var feature = $(this).attr('data-feature');
                setTimeout(function () {
                    window.feature_content_editor[feature] = new Quill('#'+id, {
                        modules: {
                            toolbar: toolbarOptions
                        },
                        theme: 'snow',
                        bounds: document.getElementsByClassName('container-fluid')[0]
                    });
                },0);
            });
            $('.feature_content_lang').each(function() {
                var id = $(this).attr('id');
                var lang = $(this).attr('data-lang');
                var feature = $(this).attr('data-feature');
                if(window.feature_content_editor_lang[feature]===undefined) {
                    window.feature_content_editor_lang[feature] = {};
                }
                setTimeout(function () {
                    window.feature_content_editor_lang[feature][lang] = new Quill('#'+id, {
                        modules: {
                            toolbar: toolbarOptions
                        },
                        theme: 'snow',
                        bounds: document.getElementsByClassName('container-fluid')[0]
                    });
                },0);
            });
        });

        $("input").change(function(){
            window.features_need_save = true;
        });

        $("textarea").change(function(){
            window.features_need_save = true;
        });

        $(window).on('beforeunload', function(){
            if(window.features_need_save) {
                var c=confirm();
                if(c) return true; else return false;
            }
        });
    })(jQuery); // End of use strict
</script>