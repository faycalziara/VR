<?php
session_start();
$role = get_user_role($_SESSION['id_user']);
$id_user_edit = $_SESSION['id_user'];
$settings = get_settings();
$user_info = get_user_info($id_user_edit);
if(!isset($_SESSION['lang'])) {
    if(!empty($user_info['language'])) {
        $language = $user_info['language'];
    } else {
        $language = $settings['language'];
    }
} else {
    $language = $_SESSION['lang'];
}
$_SESSION['2fa_secretkey'] = $user_info['2fa_secretkey'];
$hide_personal_info = '';
if((!$settings['first_name_enable']) && (!$settings['last_name_enable']) && (!$settings['company_enable']) && (!$settings['tax_id_enable']) && (!$settings['street_enable']) && (!$settings['city_enable']) && (!$settings['province_enable']) && (!$settings['postal_code_enable']) && (!$settings['country_enable']) && (!$settings['tel_enable'])) {
    $hide_personal_info = 'd-none';
}
$to_complete = check_profile_to_complete($id_user_edit);
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
$enable_ai_room = $settings['enable_ai_room'];
$enable_autoenhance_room = $settings['enable_autoenhance_room'];
if(file_exists('../gsv/index.php')) {
    $gsv_installed = true;
} else {
    $gsv_installed = false;
}
$credits_disabled = true;
if($enable_ai_room && $plan_info['enable_ai_room']) {
    $credits_disabled = false;
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
    $credits_disabled = false;
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
    $credits_disabled = false;
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
    $credits_disabled = false;
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

<?php if($to_complete) : ?>
    <div class="card bg-warning text-white shadow mb-3">
        <div class="card-body">
            <?php echo _("Please complete your profile with the required fields (*) before continuing to use the application."); ?>
        </div>
    </div>
<?php endif; ?>

<div class="row <?php echo $hide_personal_info; ?>">
    <div class="col-md-12">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-circle"></i> <?php echo _("Personal Informations"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3 <?php echo (!$settings['first_name_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="first_name"><?php echo _("First Name"); ?> <?php echo ($settings['first_name_mandatory']) ? '*' : ''; ?></label>
                            <input data-mandatory="<?php echo ($settings['first_name_enable'] && $settings['first_name_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="first_name" value="<?php echo $user_info['first_name']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['last_name_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="last_name"><?php echo _("Last Name"); ?> <?php echo ($settings['last_name_mandatory']) ? '*' : ''; ?></label>
                            <input data-mandatory="<?php echo ($settings['last_name_enable'] && $settings['last_name_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="last_name" value="<?php echo $user_info['last_name']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['company_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="company"><?php echo _("Company"); ?> <?php echo ($settings['company_mandatory']) ? '*' : ''; ?></label>
                            <input data-mandatory="<?php echo ($settings['company_enable'] && $settings['company_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="company" value="<?php echo $user_info['company']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['tax_id_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="tax_id"><?php echo _("Tax Id"); ?> <?php echo ($settings['tax_id_mandatory']) ? '*' : ''; ?> <i title="<?php echo _("Tax identification number for issuing the invoice."); ?>" class="help_t fas fa-question-circle"></i></label>
                            <input data-mandatory="<?php echo ($settings['tax_id_enable'] && $settings['tax_id_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="tax_id" value="<?php echo $user_info['tax_id']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-6 <?php echo (!$settings['street_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="street"><?php echo _("Address"); ?> <?php echo ($settings['street_mandatory']) ? '*' : ''; ?></label>
                            <input data-mandatory="<?php echo ($settings['street_enable'] && $settings['street_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="street" value="<?php echo $user_info['street']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['city_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="city"><?php echo _("City"); ?> <?php echo ($settings['city_mandatory']) ? '*' : ''; ?></label>
                            <input data-mandatory="<?php echo ($settings['city_enable'] && $settings['city_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="city" value="<?php echo $user_info['city']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['province_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="province"><?php echo _("State / Province / Region"); ?> <?php echo ($settings['province_mandatory']) ? '*' : ''; ?></label>
                            <input data-mandatory="<?php echo ($settings['province_enable'] && $settings['province_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="province" value="<?php echo $user_info['province']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['postal_code_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="postal_code"><?php echo _("Zip / Postal Code"); ?> <?php echo ($settings['postal_code_mandatory']) ? '*' : ''; ?></label>
                            <input data-mandatory="<?php echo ($settings['postal_code_enable'] && $settings['postal_code_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="postal_code" value="<?php echo $user_info['postal_code']; ?>" />
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['country_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="country"><?php echo _("Country"); ?> <?php echo ($settings['country_mandatory']) ? '*' : ''; ?></label>
                            <select data-mandatory="<?php echo ($settings['country_enable'] && $settings['country_mandatory']) ? 'true' : 'false'; ?>" id="country" class="form-control selectpicker countrypicker" <?php echo (!empty($user_info['country'])) ? 'data-default="'.$user_info['country'].'"' : '' ; ?> data-flag="true" data-live-search="true" title="<?php echo _("Select country"); ?>"></select>
                            <script>
                                $('.countrypicker').countrypicker();
                            </script>
                        </div>
                    </div>
                    <div class="col-md-3 <?php echo (!$settings['tel_enable']) ? 'd-none' : ''; ?>">
                        <div class="form-group">
                            <label for="tel"><?php echo _("Telephone"); ?> <?php echo ($settings['tel_mandatory']) ? '*' : ''; ?></label>
                            <input data-mandatory="<?php echo ($settings['tel_enable'] && $settings['tel_mandatory']) ? 'true' : 'false'; ?>" type="text" class="form-control" id="tel" value="<?php echo $user_info['tel']; ?>" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if($to_complete) : ?>
<div class="row">
    <div class="col-md-12">
        <button id="btn_save_continue_profile" onclick="save_profile(true);" class="btn btn-block btn-success"><?php echo _("SAVE AND CONTINUE"); ?></button>
    </div>
</div>
<?php endif; ?>

<div class="row <?php echo ($to_complete) ? 'd-none' : ''; ?>">
    <div class="col-lg-4 col-md-6">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-camera"></i> <?php echo _("Avatar"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 text-center">
                        <img id="avatar_edit" src="<?php echo $user_info['avatar']; ?>" />
                    </div>
                    <div class="col-md-12 text-center">
                        <input class="d-none" type="file" id="input_avatar" accept="image/*">
                        <button id="btn_upload_avatar" onclick="upload_avatar_file();" class="btn btn-primary mt-3"><?php echo _("UPLOAD"); ?></button>
                        <button id="btn_create_avatar" onclick="create_avatar_file();" class="btn btn-success d-none"><?php echo _("CREATE"); ?></button>
                    </div>
                </div>
            </div>
        </div>
        <?php if($settings['social_google_enable'] || $settings['social_facebook_enable'] || $settings['social_twitter_enable'] || $settings['social_wechat_enable'] || $settings['social_qq_enable']) : ?>
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-comments"></i> <?php echo _("Connected Accounts"); ?></h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php if($settings['social_google_enable']) : ?>
                            <div class="col-md-12">
                                <a onclick="connect_social('Google',<?php echo (empty($user_info['google_identifier'])) ? 0 : 1; ?>);return false;" href="#" class="btn btn-block btn-google btn-user <?php echo (empty($user_info['google_identifier'])) ? 'provider_disconnected' : ''; ?>">
                                    <i class="fab fa-google fa-fw"></i> <?php echo (empty($user_info['google_identifier'])) ? _("Connect Google") : _("Disconnect Google"); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if($settings['social_facebook_enable']) : ?>
                            <div class="col-md-12">
                                <a onclick="connect_social('Facebook',<?php echo (empty($user_info['facebook_identifier'])) ? 0 : 1; ?>);return false;" href="#" class="btn btn-block btn-facebook btn-user <?php echo (empty($user_info['facebook_identifier'])) ? 'provider_disconnected' : ''; ?>">
                                    <i class="fab fa-facebook-f fa-fw"></i> <?php echo (empty($user_info['facebook_identifier'])) ? _("Connect Facebook") : _("Disconnect Facebook"); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if($settings['social_twitter_enable']) : ?>
                            <div class="col-md-12">
                                <a onclick="connect_social('Twitter',<?php echo (empty($user_info['twitter_identifier'])) ? 0 : 1; ?>);return false;" href="#" class="btn btn-block btn-dark btn-user <?php echo (empty($user_info['twitter_identifier'])) ? 'provider_disconnected' : ''; ?>">
                                    <i class="fab fa-x-twitter fa-fw"></i> <?php echo (empty($user_info['twitter_identifier'])) ? _("Connect Twitter") : _("Disconnect Twitter"); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if($settings['social_wechat_enable']) : ?>
                            <div class="col-md-12">
                                <a onclick="connect_social('WeChat',<?php echo (empty($user_info['wechat_identifier'])) ? 0 : 1; ?>);return false;" href="#" class="btn btn-block btn-wechat btn-user <?php echo (empty($user_info['wechat_identifier'])) ? 'provider_disconnected' : ''; ?>">
                                    <i class="fab fa-weixin fa-fw"></i> <?php echo (empty($user_info['wechat_identifier'])) ? _("Connect Wechat") : _("Disconnect Wechat"); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                        <?php if($settings['social_qq_enable']) : ?>
                            <div class="col-md-12">
                                <a onclick="connect_social('QQ',<?php echo (empty($user_info['qq_identifier'])) ? 0 : 1; ?>);return false;" href="#" class="btn btn-block btn-qq btn-user <?php echo (empty($user_info['qq_identifier'])) ? 'provider_disconnected' : ''; ?>">
                                    <i class="fab fa-qq fa-fw"></i> <?php echo (empty($user_info['qq_identifier'])) ? _("Connect QQ") : _("Disconnect QQ"); ?>
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if(!$credits_disabled) : ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-coins"></i> <?php echo _("Credits"); ?></h6>
            </div>
            <div class="card-body p-0">
                <div class="row">
                    <div class="col-md-12">
                        <ul class="list-group">
                            <?php if($enable_ai_room && $plan_info['enable_ai_room']) : ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo _("A.I. Panorama"); ?>
                                <span class="badge text-bg-primary rounded-pill">
                                    <?php switch($plan_info['ai_generate_mode']) {
                                        case 'month': ?>
                                            <?php echo $ai_generated." "._("of")."&nbsp;".(($n_ai_generate_month<0) ? '<i style="vertical-align: middle;margin-right: 0;-webkit-text-stroke-width: 0.6px;" class="fas fa-infinity"></i>' : $n_ai_generate_month).'&nbsp;'."("._("monthly").")"; ?>
                                            <?php break;
                                        case 'credit': ?>
                                            <?php echo $ai_generated." "._("of")."&nbsp;".(($ai_credits<0) ? 0 : $ai_credits); ?>
                                            <?php break;
                                    } ?>
                                </span>
                            </li>
                            <?php endif; ?>
                            <?php if($enable_autoenhance_room && $plan_info['enable_autoenhance_room']) : ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo _("A.I. Enhancement"); ?>
                                <span class="badge text-bg-primary rounded-pill">
                                    <?php switch($plan_info['autoenhance_generate_mode']) {
                                        case 'month': ?>
                                            <?php echo $autoenhance_generated." "._("of")."&nbsp;".(($n_autoenhance_generate_month<0) ? '<i style="vertical-align: middle;margin-right: 0;-webkit-text-stroke-width: 0.6px;" class="fas fa-infinity"></i>' : $n_autoenhance_generate_month).'&nbsp;'."("._("monthly").")"; ?>
                                            <?php break;
                                        case 'credit': ?>
                                            <?php echo $autoenhance_generated." "._("of")."&nbsp;".(($autoenhance_credits<0) ? 0 : $autoenhance_credits); ?>
                                            <?php break;
                                    } ?>
                                </span>
                            </li>
                            <?php endif; ?>
                            <?php if($settings['buy_services']) : ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo _("Services"); ?>
                                <span class="badge text-bg-primary rounded-pill">
                                    <?php echo $services_used." "._("of")."&nbsp;".(($services_credits<0) ? 0 : $services_credits); ?>
                                </span>
                            </li>
                            <?php endif; ?>
                            <?php if($gsv_installed && $plan_info['enable_gsv_publish']) : ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?php echo _("GSV Publish"); ?>
                                    <span class="badge text-bg-primary rounded-pill">
                                        <?php switch($plan_info['gsv_publish_mode']) {
                                            case 'month': ?>
                                                <?php echo $gsv_published." "._("of")."&nbsp;".(($n_gsv_publish_month<0) ? '<i style="vertical-align: middle;margin-right: 0;-webkit-text-stroke-width: 0.6px;" class="fas fa-infinity"></i>' : $n_gsv_publish_month).'&nbsp;'."("._("monthly").")"; ?>
                                                <?php break;
                                            case 'credit': ?>
                                                <?php echo $gsv_published." "._("of")."&nbsp;".(($gsv_publish_credits<0) ? 0 : $gsv_publish_credits); ?>
                                                <?php break;
                                        } ?>
                                    </span>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <div class="col-lg-8 col-md-6">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-cog"></i> <?php echo _("Account"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="username"><?php echo _("Username"); ?></label>
                                    <input type="text" class="form-control" id="username" value="<?php echo $user_info['username']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="email"><?php echo _("E-mail"); ?></label>
                                    <input type="email" class="form-control" id="email" value="<?php echo $user_info['email']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="language"><?php echo _("Language"); ?></label>
                                    <select class="form-control" id="language">
                                        <?php if((!check_language_enabled('en_US',$settings['languages_enabled']) && check_language_enabled('en_GB',$settings['languages_enabled'])) || (check_language_enabled('en_US',$settings['languages_enabled']) && !check_language_enabled('en_GB',$settings['languages_enabled']))) {
                                            $languages_list['en_GB']['name'] = "English";
                                            $languages_list['en_US']['name'] = "English";
                                        } ?>
                                        <option <?php echo ($user_info['language']=='') ? 'selected':''; ?> id=""><?php echo _("Default")." ({$settings['language']})"; ?></option>
                                        <?php foreach ($languages_list as $lang_code => $lang_data): ?>
                                            <?php if (check_language_enabled($lang_code, $settings['languages_enabled'])): ?>
                                                <option <?php echo ($user_info['language']==$lang_code) ? 'selected':''; ?> id="<?php echo $lang_code; ?>"><?php echo $lang_data['name']." ($lang_code)"; ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label><?php echo _("Password"); ?></label>
                                    <button data-toggle="modal" data-target="#modal_change_password" class="btn btn-block btn-primary"><?php echo _("CHANGE"); ?></button>
                                </div>
                            </div>
                            <?php if($settings['2fa_enable']) : ?>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><?php echo _("Two-Factor Authentication"); ?>&nbsp;&nbsp;<i id="circle_2fa" style="font-size:14px;color:<?php echo (empty($user_info['2fa_secretkey'])) ? 'red' : 'green' ; ?>" class="fas fa-circle"></i></label>
                                        <button id="btn_modal_enable_2fa" onclick="open_modal_enable_2fa();" class="btn btn-block btn-success <?php echo (empty($user_info['2fa_secretkey'])) ? '' : 'd-none' ; ?>"><?php echo _("ENABLE"); ?></button><button id="btn_modal_disable_2fa" data-toggle="modal" data-target="#modal_disable_2fa" class="btn btn-block mt-0 btn-danger <?php echo (empty($user_info['2fa_secretkey'])) ? 'd-none' : '' ; ?>"><?php echo _("DISABLE"); ?></button>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php if($settings['enable_registration']) : ?>
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-credit-card"></i> <?php echo _("Current Subscription"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><?php echo _("Plan"); ?></label><br>
                                    <span><?php echo ((empty($user_info['id_plan'])) ? '--' : (get_plan($user_info['id_plan'])['name'])); ?></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><?php echo _("Status"); ?></label><br>
                                    <?php
                                    if(!empty($user_info['id_plan'])) {
                                        switch ($user_info['plan_status']) {
                                            case 'active':
                                                echo " <span style='color:green'>" . _("Active") . "</span>";
                                                break;
                                            case 'expiring':
                                                echo " <span style='color:darkorange'>" . _("Active (expiring)") . "</span>";
                                                break;
                                            case 'expired':
                                                echo " <span style='color:red'>" . _("Expired") . "</span>";
                                                break;
                                            case 'invalid_payment':
                                                echo " <span style='color:red'>" . _("Invalid payment") . "</span>";
                                                break;
                                        }
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label><?php echo _("Expires on"); ?></label><br>
                                    <span><?php echo (empty($user_info['expire_plan_date'])) ? _("Never") : formatTime("dd MMM y - HH:mm",$language,strtotime($user_info['expire_plan_date'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if($settings['buy_services']) : ?>
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-hand-holding-usd"></i> <?php echo _("Purchased Services"); ?></h6>
                        </div>
                        <div class="card-body">
                            <?php
                            $services = get_purchased_services($id_user_edit,$language);
                            if(count($services) == 0) { ?>
                                <span><?php echo _("No purchased services yet."); ?></span>
                           <?php } else { ?>
                                <table class="table table-sm">
                                    <thead>
                                    <tr>
                                        <th scope="col"><?php echo _("Date"); ?></th>
                                        <th scope="col"><?php echo _("Service"); ?></th>
                                        <th scope="col"><?php echo _("Tour"); ?></th>
                                        <th scope="col"><?php echo _("N. Rooms"); ?></th>
                                        <th scope="col"><?php echo _("Price"); ?></th>
                                        <th scope="col"><?php echo _("Credits"); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($services as $service) {
                                        echo "<tr>";
                                        echo "<td>".formatTime("dd MMM y",$language,strtotime($service['date_time']))."</td>";
                                        echo "<td>".$service['name']."</td>";
                                        echo "<td>".$service['vt_name']."</td>";
                                        echo "<td>".$service['rooms_num']."</td>";
                                        echo "<td>".$service['price']."</td>";
                                        echo "<td>".$service['credits_used']."</td>";
                                        echo "</tr>";
                                    }
                                    ?>
                                    </tbody>
                                </table>
                            <?php } ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div id="modal_change_password" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Change Password"); ?></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="password"><?php echo _("Password"); ?></label>
                            <input autocomplete="new-password" type="password" minlength="6" required class="form-control" id="password" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="repeat_password"><?php echo _("Repeat Password"); ?></label>
                            <input autocomplete="new-password" type="password" minlength="6" required class="form-control" id="repeat_password" />
                        </div>
                    </div>
                </div>
                <input type="hidden" id="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="change_password();" type="button" class="btn btn-success"><i class="fas fa-key"></i> <?php echo _("Change"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_enable_2fa" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Enable Two-Factor Authentication"); ?></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <span><?php echo _("Please use your authentication app (such as Google Authenticator) to scan this QR code or enter the code above."); ?></span>
                    </div>
                    <div class="col-md-12">
                        <div id="qr_code_2fa" class="text-center">
                            <i class='fas fa-spin fa-circle-notch' aria-hidden='true'></i>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <input readonly class="form-control text-center bg-white" id="code_2fa" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button onclick="continue_enable_2fa();" type="button" class="btn btn-success"><?php echo _("Continue"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_check_enable_2fa" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Enable Two-Factor Authentication"); ?></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <span><?php echo _("Please enter the confirmation code that you see on your authenticator app."); ?></span>
                    </div>
                    <div class="col-md-12 mt-2">
                        <div class="form-group">
                            <input type="number" class="form-control text-center" id="code_check_2fa" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="btn_enable_2fa" onclick="enable_2fa();" type="button" class="btn btn-success"><i class="fas fa-lock"></i> <?php echo _("Enable"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_disable_2fa" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Disable Two-Factor Authentication"); ?></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <span><?php echo _("Please enter the confirmation code that you see on your authenticator app."); ?></span>
                    </div>
                    <div class="col-md-12 mt-2">
                        <div class="form-group">
                            <input type="number" class="form-control text-center" id="code_disable_2fa" />
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="btn_disable_2fa" onclick="disable_2fa();" type="button" class="btn btn-danger"><i class="fas fa-unlock"></i> <?php echo _("Disable"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    (function($) {
        "use strict"; // Start of use strict
        var avatar_crop = null;
        window.user_need_save = false;
        window.id_user_edit = '<?php echo $id_user_edit; ?>';
        $(document).ready(function () {
            $('.help_t').tooltip();
            $('#input_avatar').on('change', function () { readFile(this); });
        });
        function readFile(input) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function (e) {
                    avatar_crop = $('#avatar_edit').croppie({
                        url: e.target.result,
                        enableExif: true,
                        viewport: {
                            width: 160,
                            height: 160,
                            type: 'circle'
                        },
                        boundary: {
                            width: 160,
                            height: 160
                        }
                    });
                    $('#btn_upload_avatar').hide();
                    $('#btn_create_avatar').removeClass('d-none');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        window.upload_avatar_file = function() {
            $('#input_avatar').click();
        }
        window.create_avatar_file = function() {
            avatar_crop.croppie('result','base64','viewport','jpeg',1,true).then(function(base64) {
                $('#input_avatar').off('change');
                $('#input_avatar').val('');
                $('#avatar_edit').attr('src',base64);
                $('#btn_create_avatar').addClass('d-none');
                save_profile(window.id_user_edit);
            });
        }
        window.connect_social = function (provider,disconnect) {
            location.href = 'social_auth.php?provider='+provider+'&reg=0&edit_p=1&signout_p='+disconnect;
        }
        $("input[type='text']").change(function(){
            window.user_need_save = true;
        });
        $(window).on('beforeunload', function(){
            if(window.user_need_save) {
                var c=confirm();
                if(c) return true; else return false;
            }
        });
    })(jQuery); // End of use strict
</script>