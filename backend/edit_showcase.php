<?php
session_start();
$id_user = $_SESSION['id_user'];
$id_showcase = $_GET['id'];
$showcase = get_showcase($id_showcase,$id_user);
if($showcase){
    $tmp_languages = get_languages_showcase();
    $array_languages = $tmp_languages[0];
    $default_language = $tmp_languages[1];
    $settings = get_settings();
    $plan_permissions = get_plan_permission($id_user);
    $array_input_lang = array();
    $query_lang = "SELECT * FROM svt_showcases_lang WHERE id_showcase=$id_showcase;";
    $result_lang = $mysqli->query($query_lang);
    if($result_lang) {
        if ($result_lang->num_rows > 0) {
            while($row_lang = $result_lang->fetch_array(MYSQLI_ASSOC)) {
                $language = $row_lang['language'];
                $array_input_lang[$language]=$row_lang;
            }
        }
    }
    $custom_domain_sel = "";
    if($settings['enable_custom_domain'] && $plan_permissions['enable_custom_domain'] && $user_info['role']!='editor') {
        $custom_domain_enable = true;
    } else {
        $custom_domain_enable = false;
    }
    if($custom_domain_enable) {
        $custom_domains = get_custom_domains($showcase['id_user']);
        $id_custom_domain_sel = get_custom_domain_connected('showcase',$id_showcase);
        foreach ($custom_domains as $id_custom_domain => $custom_domain) {
            if($id_custom_domain==$id_custom_domain_sel) {
                $custom_domain_sel = $custom_domain;
            }
        }
    } else {
        $custom_domains = array();
        $id_custom_domain_sel = 0;
    }
    if (is_ssl()) { $protocol = 'https'; } else { $protocol = 'http'; }
    $base_url = $protocol ."://". $_SERVER['SERVER_NAME'] . str_replace("backend/index.php","",$_SERVER['SCRIPT_NAME']);
    $link = $protocol ."://". (!empty($custom_domain_sel) ? $custom_domain_sel : $_SERVER['SERVER_NAME']) . str_replace("backend/index.php","showcase/index.php?code=",$_SERVER['SCRIPT_NAME']);
    $link_f = $protocol ."://". (!empty($custom_domain_sel) ? $custom_domain_sel : $_SERVER['SERVER_NAME']) . str_replace("backend/index.php","showcase/",$_SERVER['SCRIPT_NAME']);
}
?>

<?php if(!$showcase): ?>
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
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-info-circle"></i> <?php echo _("Details"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="name"><?php echo _("Name"); ?><?php echo print_language_input_selector($array_languages,$default_language,'name'); ?></label>
                            <input type="text" class="form-control" id="name" value="<?php echo $showcase['name']; ?>" />
                            <?php foreach ($array_languages as $lang) {
                                if($lang!=$default_language) : ?>
                                    <input style="display:none;" id="name_<?php echo $lang; ?>" type="text" class="form-control input_lang" data-target-id="name" data-lang="<?php echo $lang; ?>" value="<?php echo $array_input_lang[$lang]['name']; ?>">
                                <?php endif;
                            } ?>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="bg_color"><?php echo _("Background Color"); ?></label>
                            <input type="text" class="form-control" id="bg_color" value="<?php echo $showcase['bg_color']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="open_target"><?php echo _("Open target"); ?></label>
                            <select id="open_target" class="form-control">
                                <option id="self" <?php echo ($showcase['open_target']=='self') ? 'selected' : ''; ?>><?php echo _("Same window"); ?></option>
                                <option id="new" <?php echo ($showcase['open_target']=='new') ? 'selected' : ''; ?>><?php echo _("New window"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="sort_settings"><?php echo _("Sorting Mode"); ?></label>
                            <?php
                            $sort_settings = $showcase['sort_settings'];
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
                            ?>
                            <select id="sort_settings" data-iconBase="fa" data-tickIcon="fa-check" data-actions-box="true" data-selected-text-format="count > 4" data-count-selected-text="{0} <?php echo _("items selected"); ?>" data-deselect-all-text="<?php echo _("Deselect All"); ?>" data-select-all-text="<?php echo _("Select All"); ?>" data-none-selected-text="<?php echo _("Nothing selected"); ?>" data-none-results-text="<?php echo _("No results matched"); ?> {0}" class="form-control selectpicker" multiple>
                                <option id="date" <?php echo ($sort_settings['date']==1) ? 'selected' : ''; ?>><?php echo _("Date"); ?></option>
                                <option id="relevance" <?php echo ($sort_settings['relevance']==1) ? 'selected' : ''; ?>><?php echo _("Relevance (Custom Sort)"); ?></option>
                                <option id="name" <?php echo ($sort_settings['name']==1) ? 'selected' : ''; ?>><?php echo _("Name"); ?></option>
                                <option id="category" <?php echo ($sort_settings['category']==1) ? 'selected' : ''; ?>><?php echo _("Category"); ?></option>
                                <option id="author" <?php echo ($sort_settings['author']==1) ? 'selected' : ''; ?>><?php echo _("Author"); ?></option>
                                <option id="views" <?php echo ($sort_settings['views']==1) ? 'selected' : ''; ?>><?php echo _("Views"); ?></option>
                            </select>
                            <script type="text/javascript">$('#sort_settings').selectpicker('render');</script>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="sort_default"><?php echo _("Default Sorting"); ?></label>
                            <select id="sort_default" class="form-control">
                                <option id="date|asc" <?php echo ($sort_settings['default']=='date|asc') ? 'selected' : ''; ?>>↓ <?php echo _("Date"); ?></option>
                                <option id="date|desc" <?php echo ($sort_settings['default']=='date|desc') ? 'selected' : ''; ?>>↑ <?php echo _("Date"); ?></option>
                                <option id="relevance|asc" <?php echo ($sort_settings['default']=='relevance|asc') ? 'selected' : ''; ?>>↓ <?php echo _("Relevance (Custom Sort)"); ?></option>
                                <option id="relevance|desc" <?php echo ($sort_settings['default']=='relevance|desc') ? 'selected' : ''; ?>>↑ <?php echo _("Relevance (Custom Sort)"); ?></option>
                                <option id="name|asc" <?php echo ($sort_settings['default']=='name|asc') ? 'selected' : ''; ?>>↓ <?php echo _("Name"); ?></option>
                                <option id="name|desc" <?php echo ($sort_settings['default']=='name|desc') ? 'selected' : ''; ?>>↑ <?php echo _("Name"); ?></option>
                                <option id="category|asc" <?php echo ($sort_settings['default']=='category|asc') ? 'selected' : ''; ?>>↓ <?php echo _("Category"); ?></option>
                                <option id="category|desc" <?php echo ($sort_settings['default']=='category|desc') ? 'selected' : ''; ?>>↑ <?php echo _("Category"); ?></option>
                                <option id="author|asc" <?php echo ($sort_settings['default']=='author|asc') ? 'selected' : ''; ?>>↓ <?php echo _("Author"); ?></option>
                                <option id="author|desc" <?php echo ($sort_settings['default']=='author|desc') ? 'selected' : ''; ?>>↑ <?php echo _("Author"); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="language"><?php echo _("Default Language"); ?></label>
                            <select class="form-control" id="language">
                                <?php if((!check_language_enabled_viewer('en_US',$settings['languages_enabled']) && check_language_enabled_viewer('en_GB',$settings['languages_enabled'])) || (check_language_enabled_viewer('en_US',$settings['languages_enabled']) && !check_language_enabled_viewer('en_GB',$settings['languages_enabled']))) {
                                    $languages_list['en_GB']['name'] = "English";
                                    $languages_list['en_US']['name'] = "English";
                                } ?>
                                <option <?php echo ($showcase['language']=='') ? 'selected':''; ?> id=""><?php echo _("Default")." ({$settings['language']})"; ?></option>
                                <?php foreach ($languages_list as $lang_code => $lang_data): ?>
                                    <?php if (check_language_enabled_viewer($lang_code, $settings['languages_viewer_enabled'])): ?>
                                        <option <?php echo ($showcase['language']==$lang_code) ? 'selected':''; ?> id="<?php echo $lang_code; ?>"><?php echo $lang_data['name']." - ".$lang_data['native_name']." ($lang_code)"; ?></option>
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="languages_enabled"><?php echo _("Languages Enabled"); ?></label>
                            <select <?php echo (!$plan_permissions['enable_multilanguage']) ? 'disabled' : '' ; ?> style="height: 125px" multiple class="form-control selectpicker" id="languages_enabled" data-container="body" data-actions-box="true" data-selected-text-format="count > 2" data-count-selected-text="{0} <?php echo _("items selected"); ?>" data-deselect-all-text="<?php echo _("Deselect All"); ?>" data-select-all-text="<?php echo _("Select All"); ?>" data-none-selected-text="<?php echo _("Only default"); ?>" data-none-results-text="<?php echo _("No results matched"); ?> {0}">
                                <?php if((!check_language_enabled_viewer('en_US',$settings['languages_enabled']) && check_language_enabled_viewer('en_GB',$settings['languages_enabled'])) || (check_language_enabled_viewer('en_US',$settings['languages_enabled']) && !check_language_enabled_viewer('en_GB',$settings['languages_enabled']))) {
                                    $languages_list['en_GB']['name'] = "English";
                                    $languages_list['en_US']['name'] = "English";
                                }
                                foreach ($languages_list as $lang_code => $lang_data): ?>
                                    <?php if (check_language_enabled_viewer($lang_code, $settings['languages_viewer_enabled'])): ?>
                                        <option <?php echo (check_language_enabled_vt($lang_code,$settings['languages_viewer_enabled'],$showcase['languages_enabled'])) ? 'selected':''; ?> id="ls_<?php echo $lang_code; ?>"><?php echo $lang_data['name']." - ".$lang_data['native_name']." ($lang_code)"; ?></option>
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
                    <div id="ga_tracking_id_div" class="col-md-3">
                        <div class="form-group">
                            <label for="ga_tracking_id"><?php echo _("Google Analytics Tracking ID"); ?> <i title="<?php echo _("Google Analytics Tracking ID (G-XXXXXXXXX)."); ?>" class="help_t fas fa-question-circle"></i></label><br>
                            <input type="text" class="form-control" id="ga_tracking_id" value="<?php echo $showcase['ga_tracking_id']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cookie_consent"><?php echo _("Enable Cookie Consent"); ?></label><br>
                            <input type="checkbox" id="cookie_consent" <?php echo ($showcase['cookie_consent'])?'checked':''; ?> />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['pwa_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="pwa_enable"><?php echo _("Enable")." PWA"; ?></label><br>
                            <input type="checkbox" id="pwa_enable" <?php echo ($showcase['pwa_enable'])?'checked':''; ?> />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="header_html"><?php echo _("Custom HTML Header"); ?><?php echo print_language_input_selector($array_languages,$default_language,'header_html'); ?></label><br>
                            <div id="header_html"><?php echo htmlspecialchars(str_replace('\"','"',$showcase['header_html'])); ?></div>
                            <?php foreach ($array_languages as $lang) {
                                if($lang!=$default_language) : ?>
                                    <div style="display:none;" id="header_html_<?php echo $lang; ?>" class="input_lang" data-target-id="header_html" data-lang="<?php echo $lang; ?>"><?php echo htmlspecialchars(str_replace('\"','"',$array_input_lang[$lang]['header_html'])); ?></div>
                                <?php endif;
                            } ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="footer_html"><?php echo _("Custom HTML Footer"); ?><?php echo print_language_input_selector($array_languages,$default_language,'footer_html'); ?></label><br>
                            <div id="footer_html"><?php echo htmlspecialchars(str_replace('\"','"',$showcase['footer_html'])); ?></div>
                            <?php foreach ($array_languages as $lang) {
                                if($lang!=$default_language) : ?>
                                    <div style="display:none;" id="footer_html_<?php echo $lang; ?>" class="input_lang" data-target-id="footer_html" data-lang="<?php echo $lang; ?>"><?php echo htmlspecialchars(str_replace('\"','"',$array_input_lang[$lang]['footer_html'])); ?></div>
                                <?php endif;
                            } ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label><?php echo _("Logo"); ?></label>
                        <div style="background-color:#4e73df;display: none" id="div_image_logo" class="col-md-12">
                            <img style="width: 100%" src="../viewer/content/<?php echo $showcase['logo']; ?>" />
                        </div>
                        <div style="display: none" id="div_delete_logo" class="col-md-12 mt-4">
                            <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_s_logo();" class="btn btn-block btn-danger"><?php echo _("DELETE IMAGE"); ?></button>
                        </div>
                        <div style="display: none" id="div_upload_logo">
                            <form id="frm" action="ajax/upload_s_logo_image.php" method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input type="file" class="form-control" id="txtFile" name="txtFile" />
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input <?php echo ($demo) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload" value="<?php echo _("Upload Logo Image"); ?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="preview text-center">
                                            <div class="progress mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                <div class="progress-bar" id="progressBar" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                    0%
                                                </div>
                                            </div>
                                            <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error"></div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label><?php echo _("Banner image"); ?></label>
                        <div style="display: none" id="div_image_banner" class="col-md-12">
                            <img style="width: 100%" src="../viewer/content/<?php echo $showcase['banner']; ?>" />
                        </div>
                        <div style="display: none" id="div_delete_banner" class="col-md-12 mt-4">
                            <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_s_banner();" class="btn btn-block btn-danger"><?php echo _("DELETE IMAGE"); ?></button>
                        </div>
                        <div style="display: none" id="div_upload_banner">
                            <form id="frm_b" action="ajax/upload_s_banner_image.php" method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input type="file" class="form-control" id="txtFile_b" name="txtFile_b" />
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <input <?php echo ($demo) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload_b" value="<?php echo _("Upload Banner Image"); ?>" />
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="preview text-center">
                                            <div class="progress mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                <div class="progress-bar" id="progressBar_b" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                    0%
                                                </div>
                                            </div>
                                            <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error_b"></div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header pt-3 pb-2">
                <h6 class="m-0 font-weight-bold text-primary float-left d-inline-block"><i class="fas fa-route"></i> <?php echo _("Assigned Virtual Tours"); ?><br><span style="font-size:12px;font-weight:normal;vertical-align:text-top;" class="ml-3">* V=<?php echo _("Viewer"); ?>, L=<?php echo _("Landing"); ?></span></h6>
                <span class="float-right d-inline-block">
                    <input class="form-control form-control-sm" id="search_vt" type="search" placeholder="<?php echo _("Search"); ?>" />
                </span>
                <span class="float-right d-inline-block">
                    <button id="btn_start_sort" onclick="sort_vt_showcase();" class="btn btn-sm btn-primary mr-3"><i class="fas fa-sort-amount-down-alt"></i> <?php echo _("Sort List"); ?></button>
                    <button id="btn_stop_sort" style="display:none;" onclick="exit_sort_vt_showcase();" class="btn btn-sm btn-success mr-3"><i class="fas fa-sort-amount-down-alt"></i> <?php echo _("Confirm"); ?></button>
                </span>
            </div>
            <div class="card-body">
                <div class="row list_s_vt ui-sortable">
                    <?php echo get_showcase_virtualtours($id_user,$id_showcase); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
if($custom_domain_enable) : ?>
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow mb-4">
                <div class="card-header py-2">
                    <div class="row">
                        <div class="col-lg-8 col-md-6 col-sm-6 mb-2 mb-sm-0 mb-md-0 mb-lg-0">
                            <h6 style="vertical-align: bottom" class="m-0 d-inline-block font-weight-bold text-primary"><i class="fas fa-sitemap"></i> <?php echo _("Custom Domain"); ?></h6>
                        </div>
                        <div class="col-lg-4 col-md-6 col-sm-6">
                            <div class="input-group input-group-sm mb-0">
                                <select <?php echo (count($custom_domains)==0 ? 'disabled' : ''); ?> onchange="change_custom_domain();" class="form-control form-control-sm" id="custom_domain">
                                    <option id="0"><?php echo (count($custom_domains)==0 ? _("No Custom Domain added") : _("None")); ?></option>
                                    <?php
                                    foreach ($custom_domains as $id_custom_domain => $custom_domain) { ?>
                                        <option <?php echo ($id_custom_domain==$id_custom_domain_sel) ? 'selected' : ''; ?> id="<?php echo $id_custom_domain; ?>"><?php echo $custom_domain ?></option>
                                    <?php } ?>
                                </select>
                                <div class="input-group-append">
                                    <button onclick="apply_custom_domain('showcase',<?php echo $id_showcase; ?>);" disabled id="btn_apply_custom_domain" class="btn btn-sm btn-primary <?php echo ($demo) ? 'disabled_d' : ''; ?>" type="button"><?php echo _("Apply"); ?>&nbsp;&nbsp;<i class="fas fa-arrow-right"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-share-alt"></i> <?php echo _("Share & Embed"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group <?php echo ($user_info['role']!='administrator') ? 'd-none' : 'd-inline-block' ?>">
                            <label for="show_in_first_page"><?php echo _("Show as first page"); ?> (<?php echo $base_url; ?>) <i title="<?php echo _("only visible to administrators"); ?>" class="help_t fas fa-question-circle"></i></label><br>
                            <input <?php echo ($demo) ? 'disabled' : ''; ?> id="show_in_first_page" <?php echo ($showcase['show_in_first_page']) ? 'checked' : ''; ?> type="checkbox" data-toggle="toggle" data-onstyle="success" data-offstyle="light" data-size="normal" data-on="<?php echo _("Yes"); ?>" data-off="<?php echo _("No"); ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group mb-0">
                            <label for="link"><i class="fas fa-link"></i> <?php echo _("Link"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'link'); ?>
                            <div class="input-group mb-0">
                                <input readonly type="text" class="form-control bg-white mb-0 pb-0" id="link" value="<?php echo $link . $showcase['code']; ?>" />
                                <?php foreach ($array_languages as $lang) {
                                    if($lang!=$default_language) : ?>
                                        <input id="link_<?php echo $lang; ?>" style="display:none;" readonly type="text" class="form-control input_lang bg-white mb-0 pb-0" data-target-id="link" data-lang="<?php echo $lang; ?>" value="<?php echo $link . $showcase['code'] . "&lang=$lang"; ?>" />
                                    <?php endif;
                                } ?>
                                <div class="input-group-append">
                                    <a id="open_link" title="<?php echo _("OPEN LINK"); ?>" class="btn btn-success help_t" href="<?php echo $link . $showcase['code']; ?>" target="_blank">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <a style="display:none;" title="<?php echo _("OPEN LINK"); ?>" class="btn btn-success input_lang help_t" data-target-id="open_link" data-lang="<?php echo $lang; ?>" href="<?php echo $link . $showcase['code'] . "&lang=$lang"; ?>" target="_blank">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php endif;
                                    } ?>
                                    <button id="copy_link" title="<?php echo _("COPY TO CLIPBOARD"); ?>" class="btn btn-primary cpy_btn help_t" data-clipboard-target="#link">
                                        <i class="far fa-clipboard"></i>
                                    </button>
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <button style="display:none;" title="<?php echo _("COPY TO CLIPBOARD"); ?>" class="btn btn-primary cpy_btn input_lang help_t" data-target-id="copy_link" data-lang="<?php echo $lang; ?>" data-clipboard-target="#link_<?php echo $lang; ?>">
                                                <i class="far fa-clipboard"></i>
                                            </button>
                                        <?php endif;
                                    } ?>
                                    <button id="qrcode_link" title="<?php echo _("QR CODE"); ?>" onclick="open_qr_code_modal('<?php echo $link . $showcase['code']; ?>');" class="btn btn-secondary help_t">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <button style="display:none;" title="<?php echo _("QR CODE"); ?>" onclick="open_qr_code_modal('<?php echo $link . $showcase['code'] . "&lang=$lang"; ?>');" class="btn btn-secondary input_lang help_t" data-target-id="qrcode_link" data-lang="<?php echo $lang; ?>">
                                                <i class="fas fa-qrcode"></i>
                                            </button>
                                        <?php endif;
                                    } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <?php $array_share_providers = explode(",",$settings['share_providers']); ?>
                        <div id="share_link" style="margin-top: 10px" class="a2a_kit a2a_kit_size_32 a2a_default_style" data-a2a-url="<?php echo $link . $showcase['code']; ?>">
                            <a class="a2a_button_email <?php echo (in_array('email',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                            <a class="a2a_button_whatsapp <?php echo (in_array('whatsapp',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                            <a class="a2a_button_facebook <?php echo (in_array('facebook',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                            <a class="a2a_button_x <?php echo (in_array('twitter',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                            <a class="a2a_button_linkedin <?php echo (in_array('linkedin',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                            <a class="a2a_button_telegram <?php echo (in_array('telegram',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                            <a class="a2a_button_facebook_messenger <?php echo (in_array('facebook_messenger',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                            <a class="a2a_button_pinterest <?php echo (in_array('pinterest',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                            <a class="a2a_button_reddit <?php echo (in_array('reddit',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                            <a class="a2a_button_line <?php echo (in_array('line',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                            <a class="a2a_button_viber <?php echo (in_array('viber',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                            <a class="a2a_button_vk <?php echo (in_array('vk',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                            <a class="a2a_button_qzone <?php echo (in_array('qzone',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                            <a class="a2a_button_wechat <?php echo (in_array('wechat',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                        </div>
                        <?php foreach ($array_languages as $lang) {
                            if($lang!=$default_language) : ?>
                                <div style="display:none;margin-top: 10px" class="a2a_kit a2a_kit_size_32 a2a_default_style input_lang" data-a2a-url="<?php echo $link . $showcase['code'] . "&lang=$lang"; ?>" data-target-id="share_link" data-lang="<?php echo $lang; ?>">
                                    <a class="a2a_button_email <?php echo (in_array('email',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                                    <a class="a2a_button_whatsapp <?php echo (in_array('whatsapp',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                                    <a class="a2a_button_facebook <?php echo (in_array('facebook',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                                    <a class="a2a_button_x <?php echo (in_array('twitter',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                                    <a class="a2a_button_linkedin <?php echo (in_array('linkedin',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                                    <a class="a2a_button_telegram <?php echo (in_array('telegram',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                                    <a class="a2a_button_facebook_messenger <?php echo (in_array('facebook_messenger',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                                    <a class="a2a_button_pinterest <?php echo (in_array('pinterest',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                                    <a class="a2a_button_reddit <?php echo (in_array('reddit',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                                    <a class="a2a_button_line <?php echo (in_array('line',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                                    <a class="a2a_button_viber <?php echo (in_array('viber',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                                    <a class="a2a_button_vk <?php echo (in_array('vk',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                                    <a class="a2a_button_qzone <?php echo (in_array('qzone',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                                    <a class="a2a_button_wechat <?php echo (in_array('wechat',$array_share_providers) ? '' : 'hidden'); ?>"></a>
                                </div>
                            <?php endif;
                        } ?>
                        <?php if($settings['cookie_consent']) { ?>
                            <script type="text/plain" data-category="functionality" data-service="Social Share (AddToAny)" async src="https://static.addtoany.com/menu/page.js"></script>
                            <div style="display:none" id="cookie_denied_msg"><?php echo _("To use tour sharing via social networks, enable \"Social Share\" cookies in the <a data-cc='show-consentModal' href='#'>cookie preferences</a>."); ?></div>
                        <?php } else { ?>
                            <script async src="https://static.addtoany.com/menu/page.js"></script>
                        <?php } ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="link_f"><i class="fas fa-link"></i> <?php echo _("Friendly Link"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'link_f'); ?>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text noselect" id="basic-addon3"><?php echo $link_f; ?></span>
                                </div>
                                <input <?php echo ($demo) ? 'disabled' : ''; ?> type="text" class="form-control bg-white" id="link_f" value="<?php echo $showcase['friendly_url']; ?>" />
                                <?php foreach ($array_languages as $lang) {
                                    if($lang!=$default_language) : ?>
                                        <input style="display:none" readonly type="text" class="form-control input_lang bg-white" data-target-id="link_f" data-lang="<?php echo $lang; ?>" value="<?php echo (!empty($showcase['friendly_url'])) ? $showcase['friendly_url']."@".$lang : ''; ?>" />
                                    <?php endif;
                                } ?>
                                <div class="input-group-append <?php echo (empty($showcase['friendly_url'])) ? 'disabled' : '' ; ?>">
                                    <a id="link_open" title="<?php echo _("OPEN LINK"); ?>" class="btn btn-success help_t" href="<?php echo $link_f . $showcase['friendly_url']; ?>" target="_blank">
                                        <i class="fas fa-external-link-alt"></i>
                                    </a>
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <a style="display:none;" title="<?php echo _("OPEN LINK"); ?>" class="btn btn-success input_lang help_t" data-target-id="open_link_f" data-lang="<?php echo $lang; ?>" href="<?php echo $link_f.$showcase['friendly_url']."@".$lang; ?>" target="_blank">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php endif;
                                    } ?>
                                    <button id="link_copy" title="<?php echo _("COPY TO CLIPBOARD"); ?>" class="btn btn-primary cpy_btn help_t" data-clipboard-text="<?php echo $link_f . $showcase['friendly_url']; ?>">
                                        <i class="far fa-clipboard"></i>
                                    </button>
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <button style="display:none;" title="<?php echo _("COPY TO CLIPBOARD"); ?>" class="btn btn-primary cpy_btn input_lang help_t" data-target-id="copy_link_f" data-lang="<?php echo $lang; ?>" data-clipboard-text="<?php echo $link_f.$showcase['friendly_url']."@".$lang; ?>">
                                                <i class="far fa-clipboard"></i>
                                            </button>
                                        <?php endif;
                                    } ?>
                                    <button id="link_qr" title="<?php echo _("QR CODE"); ?>" onclick="open_qr_code_modal('<?php echo $link_f.$showcase['friendly_url']; ?>');" class="btn btn-secondary help_t">
                                        <i class="fas fa-qrcode"></i>
                                    </button>
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <button style="display:none;" title="<?php echo _("QR CODE"); ?>" onclick="open_qr_code_modal('<?php echo $link_f.$showcase['friendly_url']."@".$lang; ?>');" class="btn btn-secondary input_lang help_t" data-target-id="qrcode_link_f" data-lang="<?php echo $lang; ?>">
                                                <i class="fas fa-qrcode"></i>
                                            </button>
                                        <?php endif;
                                    } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="code"><i class="fas fa-code"></i> <?php echo _("Embed Code"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'code'); ?>
                            <div class="input-group">
                                <textarea id="code" class="form-control" rows="3"><iframe allow="accelerometer; camera; display-capture; fullscreen; geolocation; gyroscope; magnetometer; microphone; midi; xr-spatial-tracking;" width="100%" height="600px" frameborder="0" scrolling="yes" marginheight="0" marginwidth="0" src="<?php echo $link . $showcase['code']; ?>"></iframe></textarea>
                                <?php foreach ($array_languages as $lang) {
                                    if($lang!=$default_language) : ?>
                                        <textarea style="display:none;" id="code_<?php echo $lang; ?>" class="form-control input_lang" data-target-id="code" data-lang="<?php echo $lang; ?>" rows="3"><iframe allow="accelerometer; camera; display-capture; fullscreen; geolocation; gyroscope; magnetometer; microphone; midi; xr-spatial-tracking;" width="100%" height="600px" frameborder="0" scrolling="yes" marginheight="0" marginwidth="0" src="<?php echo $link.$showcase['code']."&lang=".$lang; ?>"></iframe></textarea>
                                    <?php endif;
                                } ?>
                                <div class="input-group-append">
                                    <button id="copy_code" title="<?php echo _("COPY TO CLIPBOARD"); ?>" class="btn btn-primary cpy_btn help_t" data-clipboard-target="#code">
                                        <i class="far fa-clipboard"></i>
                                    </button>
                                    <?php foreach ($array_languages as $lang) {
                                        if($lang!=$default_language) : ?>
                                            <button style="display:none;" title="<?php echo _("COPY TO CLIPBOARD"); ?>" class="btn btn-primary cpy_btn input_lang help_t" data-target-id="copy_code" data-lang="<?php echo $lang; ?>" data-clipboard-target="#code_<?php echo $lang; ?>">
                                                <i class="far fa-clipboard"></i>
                                            </button>
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
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <a href="#collapsePI" class="d-block card-header py-3 collapsed <?php echo (!$plan_permissions['enable_metatag']) ? 'disabled' : '' ; ?>" data-toggle="collapse" role="button" aria-expanded="false" aria-controls="collapsePI">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-hashtag"></i> <?php echo _("Meta Tag"); ?></h6>
            </a>
            <div class="collapse" id="collapsePI">
                <div class="card-body <?php echo (!$plan_permissions['enable_metatag']) ? 'disabled' : '' ; ?>">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group position-relative">
                                <label for="meta_title"><?php echo _("Title"); ?></label><?php echo print_language_input_selector($array_languages,$default_language,'meta_title'); ?>
                                <input oninput="change_meta_title();" onchange="change_meta_title();" type="text" class="form-control" id="meta_title" value="<?php echo $showcase['meta_title']; ?>" />
                                <?php foreach ($array_languages as $lang) {
                                    if($lang!=$default_language) : ?>
                                        <input style="display:none;" oninput="change_meta_title();" onchange="change_meta_title();" type="text" class="form-control input_lang" data-target-id="meta_title" data-lang="<?php echo $lang; ?>" value="<?php echo $array_input_lang[$lang]['meta_title']; ?>" />
                                    <?php endif;
                                } ?>
                            </div>
                            <div class="form-group position-relative">
                                <label for="meta_description"><?php echo _("Description"); ?></label></label><?php echo print_language_input_selector($array_languages,$default_language,'meta_description'); ?>
                                <textarea oninput="change_meta_description();" onchange="change_meta_description();" rows="3" class="form-control" id="meta_description"><?php echo $showcase['meta_description']; ?></textarea>
                                <?php foreach ($array_languages as $lang) {
                                    if($lang!=$default_language) : ?>
                                        <textarea style="display:none;" oninput="change_meta_description();" onchange="change_meta_description();" rows="3" class="form-control input_lang" data-target-id="meta_description" data-lang="<?php echo $lang; ?>"><?php echo $array_input_lang[$lang]['meta_description']; ?></textarea>
                                    <?php endif;
                                } ?>
                            </div>
                            <div class="form-group">
                                <label><?php echo _("Image"); ?></label>
                                <div style="display: none" id="div_delete_image_meta" class="form-group mt-2">
                                    <button <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_image_meta('showcase',<?php echo $id_showcase; ?>);" class="btn btn-block btn-danger"><?php echo _("DELETE IMAGE"); ?></button>
                                </div>
                                <div style="display: none" id="div_upload_image_meta">
                                    <form id="frm_im" action="ajax/upload_meta_image.php" method="POST" enctype="multipart/form-data">
                                        <div class="form-group">
                                            <input type="file" class="form-control" id="txtFile_im" name="txtFile_im" />
                                        </div>
                                        <div class="form-group">
                                            <input <?php echo ($demo) ? 'disabled':''; ?> type="submit" class="btn btn-block btn-success" id="btnUpload_im" value="<?php echo _("Upload Image"); ?>" />
                                        </div>
                                        <div class="preview text-center">
                                            <div class="progress mb-3 mb-sm-3 mb-lg-0 mb-xl-0" style="height: 2.35rem;display: none">
                                                <div class="progress-bar" id="progressBar_im" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width:0%;">
                                                    0%
                                                </div>
                                            </div>
                                            <div style="display: none;padding: .38rem;" class="alert alert-danger" id="error_im"></div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label><?php echo _("Preview"); ?></label><br>
                            <div class="facebook-preview preview">
                                <div class="facebook-preview__link">
                                    <?php if(empty($showcase['meta_image'])) {
                                        if(empty($showcase['background_image'])) {
                                            $meta_image = '';
                                        } else {
                                            $meta_image = $showcase['background_image'];
                                        }
                                    } else {
                                        $meta_image = $showcase['meta_image'];
                                    } ?>
                                    <img class="facebook-preview__image <?php echo (empty($meta_image)) ? 'd-none' : ''; ?>" src="../viewer/content/<?php echo $meta_image; ?>" alt="">
                                    <div class="facebook-preview__content">
                                        <div class="facebook-preview__url">
                                            <?php echo $_SERVER['SERVER_NAME']; ?>
                                        </div>
                                        <h2 class="facebook-preview__title">
                                            <?php if(empty($showcase['meta_title'])) {
                                                echo $showcase['name'];
                                            } else {
                                                echo $showcase['meta_title'];
                                            } ?>
                                        </h2>
                                        <div class="facebook-preview__description">
                                            <?php if(!empty($showcase['meta_description'])) {
                                                echo $showcase['meta_description'];
                                            } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12 mb-4">
        <div class="card shadow mb-12">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fab fa-css3-alt"></i> <?php echo _("Custom CSS"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12">
                        <div style="position: relative;width: 100%;height: 400px;" class="editors_css" id="custom_s"><?php echo get_editor_css_content_s('custom_'.$showcase['code']); ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_delete_showcase" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Delete Showcase"); ?></h5>
            </div>
            <div class="modal-body">
                <p><?php echo _("Are you sure you want to delete the showcase?"); ?>
                </p>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_delete_showcase" onclick="" type="button" class="btn btn-danger"><i class="fas fa-trash"></i> <?php echo _("Yes, Delete"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_qrcode" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("QR Code"); ?></h5>
            </div>
            <div class="modal-body text-center">
                <i class="fas fa-spin fa-spinner"></i>
                <img style="width: 100%;" src="" />
            </div>
            <div class="modal-footer">
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
                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="save_showcase(<?php echo $id_showcase; ?>,true);" type="button" class="btn btn-success"><i class="fas fa-check"></i> <?php echo _("Yes, Reload"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("No, i will do later"); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.showcase_need_save = false;
        window.id_showcase = <?php echo $id_showcase; ?>;
        window.bg_color_spectrum = null;
        window.link_f = '<?php echo $link_f; ?>';
        window.s_logo_image = '<?php echo $showcase['logo']; ?>';
        window.s_banner_image = '<?php echo $showcase['banner']; ?>';
        window.editor_css = null;
        window.editor_html_h = null;
        window.editor_html_f = null;
        window.editor_html_h_langs = [];
        window.editor_html_f_langs = [];
        window.image_meta = '<?php echo $showcase['meta_image']; ?>';
        window.image_meta_default = '<?php echo $showcase['banner']; ?>';
        window.title_meta_default = `<?php echo $showcase['name']; ?>`;
        window.description_meta_default = ``;
        $(document).ready(function () {
            $('.help_t').tooltip();
            $('.cpy_btn').tooltip();
            var clipboard = new ClipboardJS('.cpy_btn');
            clipboard.on('success', function(e) {
                setTooltip(e.trigger, window.backend_labels.copied+"!");
            });
            $(".list_s_vt").sortable({
                itemSelector: ".vt_block",
                handle: '.move_vt',
                cursor: "grabbing",
                update: function(event, ui) {
                    fix_vt_priority();
                }
            });
            $(".list_s_vt").sortable('disable');
            window.editor_css = ace.edit('custom_s');
            window.editor_css.session.setMode("ace/mode/css");
            window.editor_css.setOption('enableLiveAutocompletion',true);
            window.editor_css.setShowPrintMargin(false);
            if($('body').hasClass('dark_mode')) {
                window.editor_css.setTheme("ace/theme/one_dark");
            }
            if(window.rtl==1) {
                window.editor_css.setOption("rtl", true);
            }
            window.editor_html_h = ace.edit('header_html');
            window.editor_html_h.session.setMode("ace/mode/html");
            window.editor_html_h.session.setUseWrapMode(true);
            window.editor_html_h.setOption('enableLiveAutocompletion',true);
            window.editor_html_h.setShowPrintMargin(false);
            if($('body').hasClass('dark_mode')) {
                window.editor_html_h.setTheme("ace/theme/one_dark");
            }
            if(window.rtl==1) {
                window.editor_html_h.setOption("rtl", true);
            }
            $('.input_lang[data-target-id="header_html"]').each(function() {
                var lang = $(this).attr('data-lang');
                window.editor_html_h_langs[lang] = ace.edit('header_html_'+lang);
                window.editor_html_h_langs[lang].session.setMode("ace/mode/html");
                window.editor_html_h_langs[lang].session.setUseWrapMode(true);
                window.editor_html_h_langs[lang].setOption('enableLiveAutocompletion',true);
                window.editor_html_h_langs[lang].setShowPrintMargin(false);
                if($('body').hasClass('dark_mode')) {
                    window.editor_html_h_langs[lang].setTheme("ace/theme/one_dark");
                }
                if(window.rtl==1) {
                    window.editor_html_h_langs[lang].setOption("rtl", true);
                }
            });
            window.editor_html_f = ace.edit('footer_html');
            window.editor_html_f.session.setMode("ace/mode/html");
            window.editor_html_f.session.setUseWrapMode(true);
            window.editor_html_f.setOption('enableLiveAutocompletion',true);
            window.editor_html_f.setShowPrintMargin(false);
            if($('body').hasClass('dark_mode')) {
                window.editor_html_f.setTheme("ace/theme/one_dark");
            }
            if(window.rtl==1) {
                window.editor_html_f.setOption("rtl", true);
            }
            $('.input_lang[data-target-id="footer_html"]').each(function() {
                var lang = $(this).attr('data-lang');
                window.editor_html_f_langs[lang] = ace.edit('footer_html_'+lang);
                window.editor_html_f_langs[lang].session.setMode("ace/mode/html");
                window.editor_html_f_langs[lang].session.setUseWrapMode(true);
                window.editor_html_f_langs[lang].setOption('enableLiveAutocompletion',true);
                window.editor_html_f_langs[lang].setShowPrintMargin(false);
                if($('body').hasClass('dark_mode')) {
                    window.editor_html_f_langs[lang].setTheme("ace/theme/one_dark");
                }
                if(window.rtl==1) {
                    window.editor_html_f_langs[lang].setOption("rtl", true);
                }
            });
            window.bg_color_spectrum = $('#bg_color').spectrum({
                type: "text",
                preferredFormat: "hex",
                showAlpha: false,
                showButtons: false,
                allowEmpty: false
            });
            $('#show_in_first_page').change(function() {
                if($(this).prop('checked')) {
                    var show_in_first_page = 1;
                } else {
                    var show_in_first_page = 0;
                }
                set_show_in_first_page(show_in_first_page,'showcase');
            });
            if(window.s_logo_image=='') {
                $('#div_delete_logo').hide();
                $('#div_image_logo').hide();
                $('#div_upload_logo').show();
            } else {
                $('#div_delete_logo').show();
                $('#div_image_logo').show();
                $('#div_upload_logo').hide();
            }
            if(window.s_banner_image=='') {
                $('#div_delete_banner').hide();
                $('#div_image_banner').hide();
                $('#div_upload_banner').show();
            } else {
                $('#div_delete_banner').show();
                $('#div_image_banner').show();
                $('#div_upload_banner').hide();
            }
            if(window.image_meta=='') {
                $('#div_delete_image_meta').hide();
                $('#div_upload_image_meta').show();
            } else {
                $('#div_delete_image_meta').show();
                $('#div_upload_image_meta').hide();
            }
            var timer_furl;
            $('#link_f').on('input',function(){
                if(timer_furl) {
                    clearTimeout(timer_furl);
                }
                timer_furl = setTimeout(function() {
                    change_friendly_url('showcase','link_f',window.id_showcase);
                },300);
            });

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
                    upadte_progressbar_im(Math.round(percentage));
                },false);
                ajax.addEventListener('load',function(evt){
                    if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                        show_error_im(evt.target.responseText);
                    } else {
                        if(evt.target.responseText!='') {
                            window.image_meta = evt.target.responseText;
                            $('.facebook-preview__image').attr('src','../viewer/content/'+window.image_meta);
                            $('.facebook-preview__image').removeClass('d-none');
                            $('#div_delete_image_meta').show();
                            $('#div_upload_image_meta').hide();
                            save_metadata('showcase',window.id_showcase);
                        }
                    }
                    upadte_progressbar_im(0);
                    frm[0].reset();
                },false);
                ajax.addEventListener('error',function(evt){
                    show_error_im('upload failed');
                    upadte_progressbar_im(0);
                },false);
                ajax.addEventListener('abort',function(evt){
                    show_error_im('upload aborted');
                    upadte_progressbar_im(0);
                },false);
                ajax.open('POST',url);
                ajax.send(data);
                return false;
            });

            function upadte_progressbar_im(value){
                $('#progressBar_im').css('width',value+'%').html(value+'%');
                if(value==0){
                    $('.progress').hide();
                }else{
                    $('.progress').show();
                }
            }

            function show_error_im(error){
                $('.progress').hide();
                $('#error_im').show();
                $('#error_im').html(error);
            }

            var timer_meta;
            $('#meta_title,#meta_description,.input_lang[data-target-id="meta_title"],.input_lang[data-target-id="meta_description"]').on('input',function(){
                if(timer_meta) {
                    clearTimeout(timer_meta);
                }
                timer_meta = setTimeout(function() {
                    save_metadata('showcase',window.id_showcase);
                },400);
            });
        });
        $("input[type='text']").change(function(){
            if($(this).attr('id')!='link_f' && $(this).attr('id')!='meta_title' && $(this).attr('id')!='meta_description') {
                window.showcase_need_save = true;
            }
        });
        $("input[type='checkbox']").change(function(){
            window.showcase_need_save = true;
        });
        $("select").change(function(){
            window.showcase_need_save = true;
        });
        $(window).on('beforeunload', function(){
            if(window.showcase_need_save) {
                var c=confirm();
                if(c) return true; else return false;
            }
        });
    })(jQuery); // End of use strict

    $("#search_vt").on("keyup input", function() {
        var value = $(this).val().toLowerCase();
        $(".list_s_vt div").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

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
            upadte_progressbar(Math.round(percentage));
        },false);
        ajax.addEventListener('load',function(evt){
            if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                show_error(evt.target.responseText);
            } else {
                if(evt.target.responseText!='') {
                    window.showcase_need_save = true;
                    window.s_logo_image = evt.target.responseText;
                    $('#div_image_logo img').attr('src','../viewer/content/'+window.s_logo_image);
                    $('#div_delete_logo').show();
                    $('#div_image_logo').show();
                    $('#div_upload_logo').hide();
                }
            }
            upadte_progressbar(0);
            frm[0].reset();
        },false);
        ajax.addEventListener('error',function(evt){
            show_error('upload failed');
            upadte_progressbar(0);
        },false);
        ajax.addEventListener('abort',function(evt){
            show_error('upload aborted');
            upadte_progressbar(0);
        },false);
        ajax.open('POST',url);
        ajax.send(data);
        return false;
    });

    function upadte_progressbar(value){
        $('#progressBar').css('width',value+'%').html(value+'%');
        if(value==0){
            $('.progress').hide();
        }else{
            $('.progress').show();
        }
    }

    function show_error(error){
        $('.progress').hide();
        $('#error').show();
        $('#error').html(error);
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
            upadte_progressbar_b(Math.round(percentage));
        },false);
        ajax.addEventListener('load',function(evt){
            if(evt.target.responseText.toLowerCase().indexOf('error')>=0){
                show_error_b(evt.target.responseText);
            } else {
                if(evt.target.responseText!='') {
                    window.showcase_need_save = true;
                    window.s_banner_image = evt.target.responseText;
                    $('#div_image_banner img').attr('src','../viewer/content/'+window.s_banner_image);
                    $('#div_delete_banner').show();
                    $('#div_image_banner').show();
                    $('#div_upload_banner').hide();
                }
            }
            upadte_progressbar_b(0);
            frm[0].reset();
        },false);
        ajax.addEventListener('error',function(evt){
            show_error_b('upload failed');
            upadte_progressbar_b(0);
        },false);
        ajax.addEventListener('abort',function(evt){
            show_error_b('upload aborted');
            upadte_progressbar_b(0);
        },false);
        ajax.open('POST',url);
        ajax.send(data);
        return false;
    });

    function upadte_progressbar_b(value){
        $('#progressBar_b').css('width',value+'%').html(value+'%');
        if(value==0){
            $('.progress').hide();
        }else{
            $('.progress').show();
        }
    }

    function show_error_b(error){
        $('.progress').hide();
        $('#error_b').show();
        $('#error_b').html(error);
    }

    function setTooltip(btn, message) {
        var title = $(btn).attr('data-original-title');
        $(btn).tooltip('hide')
            .attr('data-original-title', message)
            .tooltip('show');
        setTimeout(function() {
            $(btn).tooltip('dispose');
            $(btn).attr('title',title);
            $(btn).tooltip();
        }, 1000);
    }

    function fix_vt_priority() {
        var priority = 1;
        $('.vt_block').each(function() {
            if($(this).is(':visible')) {
                $(this).find('.vt_priority').val(priority);
                priority++;
            }
        });
    }

    function sort_vt_showcase() {
        $("#search_vt").val('');
        $('#search_vt').prop('disabled',true);
        $("#search_vt").trigger('input');
        $('.vt_block').each(function() {
            if(!$(this).find('input[type="checkbox"]').is(':checked')) {
                $(this).hide();
            }
        });
        $('.vt_block select').hide();
        $('.vt_block input[type="checkbox"]').hide();
        $('.vt_block input[type="checkbox"]').prop('disabled',true);
        $('.vt_block .vt_priority').show();
        $('.vt_block .move_vt').show();
        $(".list_s_vt").sortable('enable');
        fix_vt_priority();
        $('#btn_start_sort').hide();
        $('#btn_stop_sort').show();
    }

    function exit_sort_vt_showcase() {
        $('#search_vt').prop('disabled',false);
        $('.vt_block').show();
        $('.vt_block select').show();
        $('.vt_block input[type="checkbox"]').show();
        $('.vt_block input[type="checkbox"]').prop('disabled',false);
        $('.vt_block .vt_priority').hide();
        $('.vt_block .move_vt').hide();
        $(".list_s_vt").sortable('disable');
        $('#btn_start_sort').show();
        $('#btn_stop_sort').hide();
    }
</script>