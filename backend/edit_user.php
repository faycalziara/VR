<?php
session_start();
$role = get_user_role($_SESSION['id_user']);
$id_user_edit = $_GET['id'];
$id_user_crypt = xor_obfuscator($id_user_edit);
$user_info_edit = get_user_info($id_user_edit);
$user_stats_edit = get_user_stats($id_user_edit);
$settings = get_settings();
$theme_color = $settings['theme_color'];
$user_info = get_user_info($_SESSION['id_user']);
if(!isset($_SESSION['lang'])) {
    if(!empty($user_info['language'])) {
        $language = $user_info['language'];
    } else {
        $language = $settings['language'];
    }
} else {
    $language = $_SESSION['lang'];
}
$users = get_users_delete($id_user_edit);
if(($user_info['role']=='administrator') && (!$user_info['super_admin']) && ($user_info_edit['role']=='administrator') && $user_info_edit['super_admin']) {
    $user_info_edit=array();
}
if(file_exists('../gsv/index.php')) {
    $gsv_installed = true;
} else {
    $gsv_installed = false;
}
if(empty($user_info_edit['id_plan'])) {
    $plan = array();
    $ai_generated = 0;
    $autoenhance_generated = 0;
    $gsv_published = 0;
} else {
    $plan = get_plan($user_info_edit['id_plan']);
    $ai_generated = get_user_ai_generated($id_user_edit,$plan['ai_generate_mode']);
    $autoenhance_generated = get_user_autoenhance_generated($id_user_edit,$plan['autoenhance_generate_mode']);
    $gsv_published = get_user_autoenhance_generated($id_user_edit,$plan['gsv_publish_mode']);
}
$services_used = get_user_service_used($id_user_edit);
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
$z0='';if(array_key_exists('SERVER_ADDR',$_SERVER)){$z0=$_SERVER['SERVER_ADDR'];if(!filter_var($z0,FILTER_VALIDATE_IP,FILTER_FLAG_IPV4)){$z0=gethostbyname($_SERVER['SERVER_NAME']);}}elseif(array_key_exists('LOCAL_ADDR',$_SERVER)){$z0=$_SERVER['LOCAL_ADDR'];}elseif(array_key_exists('SERVER_NAME',$_SERVER)){$z0=gethostbyname($_SERVER['SERVER_NAME']);}else{if(stristr(PHP_OS,'WIN')){$z0=gethostbyname(php_uname('n'));}else{$b1=shell_exec('/sbin/ifconfig eth0');preg_match('/addr:([\d\.]+)/',$b1,$e2);$z0=$e2[1];}}echo"<input type='hidden' id='vlfc' />";$v3=get_settings();$o5=$z0.'RR'.$v3['purchase_code'];$v6=password_verify($o5,$v3['license']);if(!$v6&&!empty($v3['license2'])){$o5=str_replace("www.","",$_SERVER['SERVER_NAME']).'RR'.$v3['purchase_code'];$v6=password_verify($o5,$v3['license2']);}$o5=$z0.'RE'.$v3['purchase_code'];$w7=password_verify($o5,$v3['license']);if(!$w7&&!empty($v3['license2'])){$o5=str_replace("www.","",$_SERVER['SERVER_NAME']).'RE'.$v3['purchase_code'];$w7=password_verify($o5,$v3['license2']);}$o5=$z0.'E'.$v3['purchase_code'];$r8=password_verify($o5,$v3['license']);if(!$r8&&!empty($v3['license2'])){$o5=str_replace("www.","",$_SERVER['SERVER_NAME']).'E'.$v3['purchase_code'];$r8=password_verify($o5,$v3['license2']);}if($v6){include('license.php');exit;}else if(($r8)||($w7)){}else{include('license.php');exit;}
?>

<?php if(($role!='administrator') || (count($user_info_edit)==0)): ?>
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

<ul class="nav bg-white nav-pills nav-fill mb-2">
    <li class="nav-item">
        <a class="nav-link active" data-toggle="pill" href="#user_info_tab"><i class="fas fa-user-edit"></i> <?php echo strtoupper(_("PROFILE")); ?></a>
    </li>
    <?php if ($user_info_edit['role']=='editor') { ?>
        <li class="nav-item">
            <a class="nav-link" onclick="click_tab_resize();" data-toggle="pill" href="#editor_assign_tab"><i class="fas fa-route"></i> <?php echo strtoupper(_("ASSIGNED TOURS")); ?></a>
        </li>
    <?php } else { ?>
        <li class="nav-item">
            <a class="nav-link" data-toggle="pill" href="#user_stats_tab"><i class="fas fa-chart-area"></i> <?php echo strtoupper(_("STATISTICS")); ?></a>
        </li>
        <li class="nav-item">
            <a class="nav-link disabled" data-toggle="pill" href="#vt_list_tab"><i class="fas fa-route"></i> <?php echo strtoupper(_("TOURS LIST")); ?>&nbsp;&nbsp;<span style="vertical-align:text-top;font-size:13px;" id="vt_num" class="badge badge-secondary">-</span></a>
        </li>
    <?php } ?>
    <?php if($settings['buy_services'] && $user_info_edit['role']!='editor') : ?>
    <li class="nav-item">
        <a class="nav-link disabled" onclick="click_tab_resize();" data-toggle="pill" href="#services_tab"><i class="fas fa-hand-holding-usd"></i> <?php echo strtoupper(_("SERVICES")); ?>&nbsp;&nbsp;<span style="vertical-align:text-top;font-size:13px;" id="services_num" class="badge badge-secondary">-</span></a>
    </li>
    <?php endif; ?>
    <li class="nav-item">
        <a class="nav-link" onclick="click_tab_resize();" data-toggle="pill" href="#log_activity_tab"><i class="fas fa-list-ol"></i> <?php echo strtoupper(_("ACTIVITY LOG")); ?></a>
    </li>
</ul>

<div class="tab-content mb-4">
    <div class="tab-pane active" id="user_info_tab">
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-cog"></i> <?php echo _("Account"); ?>&nbsp;&nbsp;
                            <?php
                            if(!empty($user_info_edit['hash'])) {
                                echo " <span style='color:darkorange'><b>" . _("Waiting for email activation") . "</b></span>";
                            }
                            ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="username"><?php echo _("Username"); ?></label>
                                    <input type="text" class="form-control" id="username" value="<?php echo $user_info_edit['username']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="email"><?php echo _("E-mail"); ?></label>
                                    <input type="email" class="form-control" id="email" value="<?php echo ($demo) ? obfuscateEmail($user_info_edit['email']) : $user_info_edit['email']; ?>" />
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
                                        <option <?php echo ($user_info_edit['language']=='') ? 'selected':''; ?> id=""><?php echo _("Default")." ({$settings['language']})"; ?></option>
                                        <?php foreach ($languages_list as $lang_code => $lang_data): ?>
                                            <?php if (check_language_enabled($lang_code, $settings['languages_enabled'])): ?>
                                                <option <?php echo ($user_info_edit['language']==$lang_code) ? 'selected':''; ?> id="<?php echo $lang_code; ?>"><?php echo $lang_data['name']." ($lang_code)"; ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="role"><?php echo _("Role"); ?></label>
                                    <select onchange="change_user_role();" class="form-control" id="role">
                                        <?php if($user_info['super_admin']) : ?>
                                        <option <?php echo (($user_info_edit['role']=='administrator') && $user_info_edit['super_admin']) ? 'selected' : '' ; ?> id="super_admin"><?php echo _("Super Administrator"); ?></option>
                                        <?php endif; ?>
                                        <option <?php echo (($user_info_edit['role']=='administrator') && !$user_info_edit['super_admin']) ? 'selected' : '' ; ?> id="administrator"><?php echo _("Administrator"); ?></option>
                                        <option <?php echo ($user_info_edit['role']=='customer') ? 'selected' : '' ; ?> id="customer"><?php echo _("Customer"); ?></option>
                                        <option <?php echo ($user_info_edit['role']=='editor') ? 'selected' : '' ; ?> id="editor"><?php echo _("Editor"); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="active"><?php echo _("Active"); ?></label><br>
                                    <input <?php echo ($user_info_edit['active']) ? 'checked' : '' ; ?> type="checkbox" id="active" />
                                    <?php if(!empty($user_info_edit['hash'])) { ?>
                                        &nbsp;&nbsp;&nbsp;<button <?php echo ($demo) ? 'disabled_d' : ''; ?> onclick="send_activation_email();" id="btn_resend_activation_email" class="btn btn-sm btn-primary"><i class="fas fa-envelope"></i>&nbsp;&nbsp;<?php echo _("resend activation email"); ?></button>
                                    <?php } ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <br>
                                    <button data-toggle="modal" data-target="#modal_change_password" class="btn btn-block btn-primary"><?php echo _("CHANGE PASSWORD"); ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row plan_div" style="display: <?php echo ($user_info_edit['role']=='editor') ? 'none' : 'block'; ?>">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-crown"></i> <?php echo _("Plan"); ?>&nbsp;&nbsp;
                            <?php
                            if(!empty($user_info_edit['id_plan'])) {
                                switch ($user_info_edit['plan_status']) {
                                    case 'active':
                                        echo " <span style='color:green'><b>" . _("Active") . "</b></span>";
                                        break;
                                    case 'expiring':
                                        echo " <span style='color:darkorange'><b>" . _("Active (expiring)") . "</b></span>";
                                        break;
                                    case 'expired':
                                        echo " <span style='color:red'><b>" . _("Expired") . "</b></span>";
                                        break;
                                    case 'invalid_payment':
                                        echo " <span style='color:red'><b>" . _("Invalid payment") . "</b></span>";
                                        break;
                                }
                            }
                            ?>
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="plan"><?php echo _("Current Plan"); ?></label>
                                    <select class="form-control" id="plan">
                                        <?php echo get_plans_options($user_info_edit['id_plan']); ?>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo _("Manual Expiration Date"); ?> <i title="<?php echo _("set expiration date manually (leave empty for automatic)"); ?>" class="help_t fas fa-question-circle"></i></label>
                                    <input class="form-control" type="date" id="expire_plan_date_manual_date" value="<?php echo (!empty($user_info_edit['expire_plan_date_manual'])) ? date('Y-m-d',strtotime($user_info_edit['expire_plan_date_manual'])) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo _("Manual Expiration Time"); ?> <i title="<?php echo _("set expiration time manually (leave empty for automatic)"); ?>" class="help_t fas fa-question-circle"></i></label>
                                    <input class="form-control" type="time" id="expire_plan_date_manual_time" value="<?php echo (!empty($user_info_edit['expire_plan_date_manual'])) ? date('H:i',strtotime($user_info_edit['expire_plan_date_manual'])) : ''; ?>">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label><?php echo _("Expires on"); ?></label><br>
                                    <b><?php echo (empty($user_info_edit['expire_plan_date'])) ? _("Never") : formatTime("dd MMM y - HH:mm",$language,strtotime($user_info_edit['expire_plan_date'])); ?></b>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label><?php echo _("Subscription info"); ?></label><br>
                                    <b><?php
                                        $id_subscription_stripe = $user_info_edit['id_subscription_stripe'];
                                        $status_subscription_stripe = $user_info_edit['status_subscription_stripe'];
                                        $id_subscription_paypal = $user_info_edit['id_subscription_paypal'];
                                        $status_subscription_paypal = $user_info_edit['status_subscription_paypal'];
                                        if($demo && !empty($id_subscription_stripe)) {
                                            $id_subscription_stripe = "**********";
                                        }
                                        if($demo && !empty($id_subscription_paypal)) {
                                            $id_subscription_paypal = "**********";
                                        }
                                        if($status_subscription_stripe) {
                                            echo "Stripe (".$id_subscription_stripe.") <i class='fas fa-check'></i>";
                                        } else if($status_subscription_paypal) {
                                            echo "Paypal (".$id_subscription_paypal.") <i class='fas fa-check'></i>";
                                        } else if(!empty($id_subscription_stripe)) {
                                            echo "Stripe (".$id_subscription_stripe.") <i class='fas fa-times'></i>";
                                        } else if(!empty($id_subscription_paypal)) {
                                            echo "Paypal (".$id_subscription_paypal.") <i class='fas fa-times'></i>";
                                        } else {
                                            echo "--";
                                        }
                                        ?></b>
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
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="far fa-sticky-note"></i> <?php echo _("Note (only visible to administrators)"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <textarea class="form-control" id="note" rows="4"><?php echo $user_info_edit['note']; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row credits_div" style="display: <?php echo ($user_info_edit['role']=='editor' || empty($user_info_edit['id_plan'])) ? 'none' : 'block'; ?>">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-coins"></i> <?php echo _("Credits"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="card-group mb-3">
                                    <div class="card">
                                        <div class="card-header">
                                            <?php echo _("A.I. Panorama"); ?>
                                        </div>
                                        <div class="card-body pb-0 pt-2">
                                            <div class="form-group">
                                                <?php if($plan['ai_generate_mode']=='credit') { ?>
                                                    <label class="text-primary"><b><?php echo $ai_generated; ?></b> <?php echo _("used in total"); ?></label> / <label class="text-primary"><b><?php echo $user_info_edit['ai_credits'] - $ai_generated; ?></b> <?php echo _("to use"); ?></label>
                                                <?php } else { ?>
                                                    <label class="text-primary"><b><?php echo $ai_generated; ?></b> <?php echo _("used this month"); ?></label> / <label class="text-primary"><b><?php echo ($plan['n_ai_generate_month']==-1) ? '<i class="fas fa-infinity"></i>' : $plan['n_ai_generate_month'] - $user_info_edit['ai_credits']; ?></b> <?php echo _("to use"); ?></label>
                                                <?php } ?>
                                                <input <?php echo ($plan['ai_generate_mode']=='month') ? 'readonly' : ''; ?> id="ai_credits" min="0" step="1" type="<?php echo ($plan['ai_generate_mode']=='month') ? 'text' : 'number'; ?>" class="form-control" aria-label="Default" value="<?php echo ($plan['ai_generate_mode']=='month') ? $plan['n_ai_generate_month'] : $user_info_edit['ai_credits'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <?php echo _("A.I. Enhancement"); ?>
                                        </div>
                                        <div class="card-body pb-0 pt-2">
                                            <div class="form-group">
                                                <?php if($plan['autoenhance_generate_mode']=='credit') { ?>
                                                    <label class="text-primary"><b><?php echo $autoenhance_generated; ?></b> <?php echo _("used in total"); ?></label> / <label class="text-primary"><b><?php echo $user_info_edit['autoenhance_credits'] - $autoenhance_generated; ?></b> <?php echo _("to use"); ?></label>
                                                <?php } else { ?>
                                                    <label class="text-primary"><b><?php echo $autoenhance_generated; ?></b> <?php echo _("used this month"); ?></label> / <label class="text-primary"><b><?php echo ($plan['n_autoenhance_generate_month']==-1) ? '<i class="fas fa-infinity"></i>' : $plan['n_autoenhance_generate_month'] - $user_info_edit['autoenhance_credits']; ?></b> <?php echo _("to use"); ?></label>
                                                <?php } ?>
                                                <input <?php echo ($plan['autoenhance_generate_mode']=='month') ? 'readonly' : ''; ?> id="autoenhance_credits" min="0" step="1" type="<?php echo ($plan['autoenhance_generate_mode']=='month') ? 'text' : 'number'; ?>" class="form-control" aria-label="Default" value="<?php echo ($plan['autoenhance_generate_mode']=='month') ? $plan['n_autoenhance_generate_month'] : $user_info_edit['autoenhance_credits'] ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card">
                                        <div class="card-header">
                                            <?php echo _("Services"); ?>
                                        </div>
                                        <div class="card-body pb-0 pt-2">
                                            <div class="form-group">
                                                <label class="text-primary"><b><?php echo $services_used; ?></b> <?php echo _("used in total"); ?></label> / <label class="text-primary"><b><?php echo $user_info_edit['services_credits'] - $services_used; ?></b> <?php echo _("to use"); ?></label>
                                                <input id="services_credits" min="0" step="1" type="number" class="form-control" aria-label="Default" value="<?php echo $user_info_edit['services_credits']; ?>">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card <?php echo (!$gsv_installed) ? 'd-none' : ''; ?>">
                                        <div class="card-header">
                                            <?php echo _("Google Street View Publish"); ?>
                                        </div>
                                        <div class="card-body pb-0 pt-2">
                                            <div class="form-group">
                                                <?php if($plan['gsv_publish_mode']=='credit') { ?>
                                                    <label class="text-primary"><b><?php echo $gsv_published; ?></b> <?php echo _("used in total"); ?></label> / <label class="text-primary"><b><?php echo $user_info_edit['gsv_publish_credits'] - $gsv_published; ?></b> <?php echo _("to use"); ?></label>
                                                <?php } else { ?>
                                                    <label class="text-primary"><b><?php echo $gsv_published; ?></b> <?php echo _("used this month"); ?></label> / <label class="text-primary"><b><?php echo ($plan['n_gsv_publish_month']==-1) ? '<i class="fas fa-infinity"></i>' : $plan['n_gsv_publish_month'] - $user_info_edit['gsv_publish_credits']; ?></b> <?php echo _("to use"); ?></label>
                                                <?php } ?>
                                                <input <?php echo ($plan['gsv_publish_mode']=='month') ? 'readonly' : ''; ?> id="gsv_publish_credits" min="0" step="1" type="<?php echo ($plan['gsv_publish_mode']=='month') ? 'text' : 'number'; ?>" class="form-control" aria-label="Default" value="<?php echo ($plan['gsv_publish_mode']=='month') ? $plan['n_gsv_publish_month'] : $user_info_edit['gsv_publish_credits'] ?>">
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
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-user-circle"></i> <?php echo _("Personal Informations"); ?></h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 <?php echo (!$settings['first_name_enable']) ? 'd-none' : ''; ?>">
                                <div class="form-group">
                                    <label for="first_name"><?php echo _("First Name"); ?></label>
                                    <input type="text" class="form-control" id="first_name" value="<?php echo $user_info_edit['first_name']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3 <?php echo (!$settings['last_name_enable']) ? 'd-none' : ''; ?>">
                                <div class="form-group">
                                    <label for="last_name"><?php echo _("Last Name"); ?></label>
                                    <input type="text" class="form-control" id="last_name" value="<?php echo $user_info_edit['last_name']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3 <?php echo (!$settings['company_enable']) ? 'd-none' : ''; ?>">
                                <div class="form-group">
                                    <label for="company"><?php echo _("Company"); ?></label>
                                    <input type="text" class="form-control" id="company" value="<?php echo $user_info_edit['company']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3 <?php echo (!$settings['tax_id_enable']) ? 'd-none' : ''; ?>">
                                <div class="form-group">
                                    <label for="tax_id"><?php echo _("Tax Id"); ?></label>
                                    <input type="text" class="form-control" id="tax_id" value="<?php echo $user_info_edit['tax_id']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-6 <?php echo (!$settings['street_enable']) ? 'd-none' : ''; ?>">
                                <div class="form-group">
                                    <label for="street"><?php echo _("Address"); ?></label>
                                    <input type="text" class="form-control" id="street" value="<?php echo $user_info_edit['street']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3 <?php echo (!$settings['city_enable']) ? 'd-none' : ''; ?>">
                                <div class="form-group">
                                    <label for="city"><?php echo _("City"); ?></label>
                                    <input type="text" class="form-control" id="city" value="<?php echo $user_info_edit['city']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3 <?php echo (!$settings['province_enable']) ? 'd-none' : ''; ?>">
                                <div class="form-group">
                                    <label for="province"><?php echo _("State / Province / Region"); ?></label>
                                    <input type="text" class="form-control" id="province" value="<?php echo $user_info_edit['province']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3 <?php echo (!$settings['postal_code_enable']) ? 'd-none' : ''; ?>">
                                <div class="form-group">
                                    <label for="postal_code"><?php echo _("Zip / Postal Code"); ?></label>
                                    <input type="text" class="form-control" id="postal_code" value="<?php echo $user_info_edit['postal_code']; ?>" />
                                </div>
                            </div>
                            <div class="col-md-3 <?php echo (!$settings['country_enable']) ? 'd-none' : ''; ?>">
                                <div class="form-group">
                                    <label for="country"><?php echo _("Country"); ?> <?php echo ($settings['country_mandatory']) ? '*' : ''; ?></label>
                                    <select id="country" class="form-control selectpicker countrypicker" <?php echo (!empty($user_info_edit['country'])) ? 'data-default="'.$user_info_edit['country'].'"' : '' ; ?> data-flag="true" data-live-search="true" title="<?php echo _("Select country"); ?>"></select>
                                </div>
                                <script>
                                    $('.countrypicker').countrypicker();
                                </script>
                            </div>
                            <div class="col-md-3 <?php echo (!$settings['tel_enable']) ? 'd-none' : ''; ?>">
                                <div class="form-group">
                                    <label for="tel"><?php echo _("Telephone"); ?></label>
                                    <input type="text" class="form-control" id="tel" value="<?php echo $user_info_edit['tel']; ?>" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="editor_assign_tab">
        <div class="row assign_vt_div">
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-route"></i> <?php echo _("Assigned Virtual Tours"); ?>
                            <span id="btn_unassign_all" onclick="unassign_all_tour_to_editor();" class="badge badge-danger float-right ml-2 <?php echo ($demo) ? 'disabled_d':''; ?> disabled"><?php echo _("Unassign all tours"); ?></span>
                            <span id="btn_assign_all" onclick="assign_all_tour_to_editor();" class="badge badge-primary float-right <?php echo ($demo) ? 'disabled_d':''; ?> disabled"><?php echo _("Assign all tours / permissions"); ?></span>
                        </h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover" id="assign_vt_table" width="100%" cellspacing="0">
                            <thead>
                            <tr>
                                <th><?php echo _("Assign"); ?></th>
                                <th style="min-width: 350px"><?php echo _("Tour"); ?></th>
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
                            <tbody>

                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php if($user_info_edit['role']!='editor') : ?>
    <div class="tab-pane" id="user_stats_tab">
        <div class="row stats_div">
            <div class="col-xl-4 col-md-4 mb-3">
                <div class="card border-left-dark shadow h-100 p-1">
                    <div class="card-body p-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1"><?php echo _("Disk Space Used"); ?></div>
                                <div id="disk_space_used" class="h5 mb-0 font-weight-bold text-gray-800">
                                    <button style="line-height:1;opacity:1" onclick="get_disk_space_stats(null,<?php echo $id_user_edit; ?>);" class="btn btn-sm btn-primary p-1"><i class="fab fa-digital-ocean"></i> <?php echo _("analyze"); ?></button>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-hdd fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-4 mb-3">
                <div class="card border-left-dark shadow h-100 p-1">
                    <div class="card-body p-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1"><?php echo _("Uploaded Files Size"); ?></div>
                                <div id="disk_space_used_uploaded" class="h5 mb-0 font-weight-bold text-gray-800">
                                    <button style="line-height:1;opacity:1" onclick="get_uploaded_file_size_stats(<?php echo $id_user_edit; ?>);" class="btn btn-sm btn-primary p-1"><i class="fab fa-digital-ocean"></i> <?php echo _("analyze"); ?></button>
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-hdd fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-4 col-md-4 mb-3">
                <div class="card border-left-primary shadow h-100 p-1">
                    <div class="card-body p-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1"><?php echo _("Virtual Tours"); ?></div>
                                <div id="num_virtual_tours" class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $user_stats_edit['count_virtual_tours']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-route fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-success shadow h-100 p-1">
                    <div class="card-body p-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1"><?php echo _("Rooms"); ?></div>
                                <div id="num_rooms" class="h5 mb-0 d-inline-block font-weight-bold text-gray-800"><?php echo $user_stats_edit['count_rooms']; ?></div>
                                <div id="num_vt_rooms" style="font-size:12px;" class="mb-0 d-inline-block text-gray-800"> / <?php echo $user_stats_edit['count_vt_rooms']." "._("tours"); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-vector-square fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-info shadow h-100 p-1">
                    <div class="card-body p-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1"><?php echo _("Markers"); ?></div>
                                <div id="num_markers" class="h5 mb-0 d-inline-block font-weight-bold text-gray-800"><?php echo $user_stats_edit['count_markers']; ?></div>
                                <div id="num_vt_markers" style="font-size:12px;" class="mb-0 d-inline-block text-gray-800"> / <?php echo $user_stats_edit['count_vt_markers']." "._("tours"); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-caret-square-up fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-info shadow h-100 p-1">
                    <div class="card-body p-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1"><?php echo _("POIs"); ?></div>
                                <div id="num_pois" class="h5 mb-0 d-inline-block font-weight-bold text-gray-800"><?php echo $user_stats_edit['count_pois']; ?></div>
                                <div id="num_vt_pois" style="font-size:12px;" class="mb-0 d-inline-block text-gray-800"> / <?php echo $user_stats_edit['count_vt_pois']." "._("tours"); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-bullseye fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-info shadow h-100 p-1">
                    <div class="card-body p-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1"><?php echo _("Measurements"); ?></div>
                                <div id="num_measures" class="h5 mb-0 d-inline-block font-weight-bold text-gray-800"><?php echo $user_stats_edit['count_measures']; ?></div>
                                <div id="num_vt_measures" style="font-size:12px;" class="mb-0 d-inline-block text-gray-800"> / <?php echo $user_stats_edit['count_vt_measures']." "._("tours"); ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-ruler-combined fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-warning shadow h-100 p-1">
                    <a style="text-decoration:none;" target="_self" href="index.php?p=video360">
                        <div class="card-body p-2">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1"><?php echo _("360 Video Tour"); ?></div>
                                    <div id="num_video360" class="h5 mb-0 d-inline-block font-weight-bold text-gray-800"><?php echo $user_stats_edit['count_video360']; ?></div>
                                    <div id="num_vt_video360" style="font-size:12px;" class="mb-0 d-inline-block text-gray-800"> / <?php echo $user_stats_edit['count_vt_video360']." "._("tours"); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-video fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-warning shadow h-100 p-1" >
                    <a style="text-decoration:none;" target="_self" href="index.php?p=video">
                        <div class="card-body p-2">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1"><?php echo _("Video Projects"); ?></div>
                                    <div id="num_video_projects" class="h5 mb-0 d-inline-block font-weight-bold text-gray-800"><?php echo $user_stats_edit['count_video_projects']; ?></div>
                                    <div id="num_vt_video_projects" style="font-size:12px;" class="mb-0 d-inline-block text-gray-800"> / <?php echo $user_stats_edit['count_vt_video_projects']." "._("tours"); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-film fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-warning shadow h-100 p-1">
                    <a style="text-decoration:none;" target="_self" href="#">
                        <div class="card-body p-2">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1"><?php echo _("Slideshows"); ?></div>
                                    <div id="num_slideshows" class="h5 mb-0 d-inline-block font-weight-bold text-gray-800"><?php echo $user_stats_edit['count_slideshows']; ?></div>
                                    <div id="num_vt_slideshows" style="font-size:12px;" class="mb-0 d-inline-block text-gray-800"> / <?php echo $user_stats_edit['count_vt_slideshows']." "._("tours"); ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-file-video fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-secondary shadow h-100 p-1 noselect" style="cursor: default">
                    <div class="card-body p-2">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1"><?php echo _("Total Visitors"); ?></div>
                                <div id="total_visitors" class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $user_stats_edit['total_visitors']; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-line"></i> <?php echo _("Virtual Tour Accesses"); ?></h6>
                    </div>
                    <div class="card-body p-2">
                        <div id="chart_visitor_vt"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="vt_list_tab">
        <div id="virtual_tours_list">
            <div class="card mb-4 py-3 border-left-primary">
                <div class="card-body" style="padding-top: 0;padding-bottom: 0;">
                    <div class="row">
                        <div class="col-md-8 text-center text-sm-center text-md-left text-lg-left">
                            <?php echo _("LOADING VIRTUAL TOURS ..."); ?>
                        </div>
                        <div class="col-md-4 text-center text-sm-center text-md-right text-lg-right">
                            <a href="#" class="btn btn-primary btn-circle">
                                <i class="fas fa-spin fa-spinner"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="tab-pane" id="services_tab">
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-add"></i> <?php echo _("Manually add service to user"); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="form-group">
                            <select onchange="change_service_add_list();" class="form-control" id="service_add_list">
                                <option id="0"><?php echo _("Select service"); ?></option>
                                <?php echo get_services_options(); ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <select onchange="change_service_add_list();" disabled class="form-control" id="service_add_tour">
                                <option id="0"><?php echo _("Select tour"); ?></option>
                                <?php
                                $vt_list = get_virtual_tours($id_user_edit,'rooms');
                                foreach ($vt_list as $vt) {
                                    if($vt['external']==0) {
                                        echo "<option data-count='".$vt['count_check']."' id='".$vt['id']."'>".$vt['name']." (".$vt['count_check']." "._("rooms").")</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="form-group">
                            <label class="mr-3"><input oninput="change_rooms_num_add_list()" onchange="change_rooms_num_add_list()" style="width:80px;" type="number" id="rooms_num_val" value="" disabled /> <?php echo _("No. of rooms"); ?></label>
                            <label class="mr-3"><input style="vertical-align:middle" checked id='use_credits' type="checkbox"> <?php echo sprintf(_("Use %s credits"),'<input style="width:80px;" type="number" id="use_credits_val" value="0" />'); ?></label>
                            <button onclick="manual_add_service();" id="service_add_btn" <?php echo ($demo) ? 'disabled':''; ?> class="btn btn-primary disabled"><i class="fas fa-plus"></i>&nbsp;&nbsp;<?php echo _("ADD"); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-hover" id="purchased_services_table" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th><?php echo _("Service"); ?></th>
                        <th><?php echo _("Type"); ?></th>
                        <th><?php echo _("Tour"); ?></th>
                        <th><?php echo _("N. Rooms"); ?></th>
                        <th><?php echo _("Price"); ?></th>
                        <th><?php echo _("Credits"); ?></th>
                        <th><?php echo _("Note"); ?></th>
                        <th><?php echo _("Date"); ?></th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="tab-pane" id="log_activity_tab">
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-hover" id="activity_log_table" width="100%" cellspacing="0">
                    <thead>
                    <tr>
                        <th><?php echo _("Activity"); ?></th>
                        <th><?php echo _("Details"); ?></th>
                        <th><?php echo _("Date"); ?></th>
                    </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="modal_delete_user" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Delete User"); ?></h5>
            </div>
            <div class="modal-body">
                <p><?php echo _("Are you sure you want to delete the user?"); ?><br>
                <div class="<?php echo ($user_info_edit['role']=='editor') ? 'd-none' : ''; ?>">
                    <div class="form-group">
                        <label for="user_assign"><?php echo _("Assign contents to"); ?></label>
                        <select onchange="change_delete_user_assign();" id="user_assign" class="form-control">
                            <option id="0"><?php echo _("Nobody"); ?></option>
                            <?php echo $users['options']; ?>
                        </select>
                    </div>
                    <b style="color:red;" id="warning_delete_msg"><?php echo _("Attention: all the virtual tours assigned to this user will be deleted!!!"); ?></b>
                </div>
                </p>
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> id="btn_delete_user" onclick="" type="button" class="btn btn-danger"><i class="fas fa-save"></i> <?php echo _("Yes, Delete"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
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
                            <input autocomplete="new-password" type="password" class="form-control" id="password" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="repeat_password"><?php echo _("Repeat password"); ?></label>
                            <input autocomplete="new-password" type="password" class="form-control" id="repeat_password" />
                        </div>
                    </div>
                </div>
                <input type="hidden" id="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
            </div>
            <div class="modal-footer">
                <button <?php echo ($demo) ? 'disabled':''; ?> onclick="change_password('user');" type="button" class="btn btn-success"><i class="fas fa-key"></i> <?php echo _("Change"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_edit_service_log" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Edit Service"); ?>: <span id="service_title">--</span></h5>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="rooms_num_edit"><?php echo _("N. Rooms"); ?></label>
                            <input type="number" class="form-control" id="rooms_num_edit" />
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="credits_used_edit"><?php echo _("Credits"); ?></label>
                            <input type="number" class="form-control" id="credits_used_edit" />
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="note_edit"><?php echo _("Note"); ?></label>
                            <textarea rows="3" class="form-control" id="note_edit"></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button id="save_service_log_btn" <?php echo ($demo) ? 'disabled':''; ?> onclick="save_service_log();" type="button" class="btn btn-success"><i class="fas fa-save"></i> <?php echo _("Save"); ?></button>
                <button id="delete_service_log_btn" <?php echo ($demo) ? 'disabled':''; ?> onclick="delete_service_log();" type="button" class="btn btn-danger"><i class="fas fa-trash"></i> <?php echo _("Delete"); ?></button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> <?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<style>
    .search_vt_div, .btn_delete_vt, .btn_export, .btn_duplicate, .author_vt_list, .btn_view_rename { display: none !important; }
    .btn_lock, .button_storage_vt { pointer-events: none !important; }
</style>

<script>
    (function($) {
        "use strict"; // Start of use strict
        window.user_need_save = false;
        window.id_user_edit = '<?php echo $id_user_edit; ?>';
        window.user_role = '<?php echo $user_info['role']; ?>';
        window.theme_color = '<?php echo $theme_color; ?>';
        window.service_log_id_sel = null;
        $(document).ready(function () {
            $('.help_t').tooltip();
            get_virtual_tours(0,window.id_user_edit);
            get_purchased_services(window.id_user_edit);
            get_statistics('chart_visitor_vt');
            $('#assign_vt_table').DataTable({
                "order": [[ 1, "asc" ]],
                "responsive": true,
                "scrollX": true,
                "processing": true,
                "searching": true,
                "serverSide": true,
                "ajax": {
                    url: "ajax/get_assigned_vt.php",
                    type: "POST",
                    data: {
                        id_user_edit: window.id_user_edit
                    }
                },
                "drawCallback": function() {
                    $('#assign_vt_table').DataTable().columns.adjust();
                    $('.assigned_vt').change(function() {
                        var checked = this.checked;
                        if(checked) checked=1; else checked=0;
                        var id_vt = $(this).attr('id');
                        assign_vt_editor(id_vt,checked);
                        $('.assigned_vt').each(function () {
                            var checked = this.checked;
                            var id_vt = $(this).attr('id');
                            if(checked) {
                                $('.editor_permissions[id='+id_vt+']').prop('disabled',false);
                            } else {
                                $('.editor_permissions[id='+id_vt+']').prop('disabled',true);
                            }
                        });
                    });
                    $('.editor_permissions').change(function() {
                        var checked = this.checked;
                        if(checked) checked=1; else checked=0;
                        var id_vt = $(this).attr('id');
                        var field = $(this).attr('class');
                        field = field.replace('editor_permissions ','');
                        set_permission_vt_editor(id_vt,field,checked);
                    });
                    $('#assign_vt_table tr').on('click',function () {
                        $('#assign_vt_table tr').removeClass('highlight');
                        $(this).addClass('highlight');
                    });
                    $('.assigned_vt').each(function () {
                        var checked = this.checked;
                        var id_vt = $(this).attr('id');
                        if(checked) {
                            $('.editor_permissions[id='+id_vt+']').prop('disabled',false);
                        } else {
                            $('.editor_permissions[id='+id_vt+']').prop('disabled',true);
                        }
                    });
                    $('#btn_assign_all').removeClass('disabled');
                    $('#btn_unassign_all').removeClass('disabled');
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
            $('#activity_log_table').DataTable({
                "order": [[ 2, "desc" ]],
                "responsive": true,
                "scrollX": true,
                "processing": true,
                "searching": false,
                "serverSide": true,
                "ajax": {
                    url: "ajax/get_user_activity_log.php",
                    type: "POST",
                    data: {
                        id_user_edit: window.id_user_edit
                    }
                },
                "drawCallback": function() {
                    $('#activity_log_table').DataTable().columns.adjust();
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
            $('#purchased_services_table').DataTable({
                "order": [[ 7, "desc" ]],
                "columnDefs": [
                    {
                        targets: [0, 1, 7],
                        className: 'no-wrap'
                    }
                ],
                "responsive": true,
                "scrollX": true,
                "processing": true,
                "searching": false,
                "serverSide": true,
                "ajax": {
                    url: "ajax/get_purchased_services_table.php",
                    type: "POST",
                    data: {
                        id_user_edit: window.id_user_edit
                    }
                },
                "drawCallback": function() {
                    $('#purchased_services_table').DataTable().columns.adjust();
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
            $('#purchased_services_table tbody').on('click', 'td', function () {
                var service_log_id = $(this).parent().attr("id");
                window.service_log_id_sel = service_log_id;
                open_modal_service_log_edit(service_log_id);
            });
        });

        window.change_rooms_num_add_list = function() {
            var count = parseInt($('#rooms_num_val').val());
            var credits = parseInt($('#service_add_list option:selected').attr('data-credits'));
            credits = credits * count;
            $('#use_credits_val').val(credits);
        }

        window.change_service_add_list = function() {
            var id_service = $('#service_add_list option:selected').attr('id');
            var id_tour = $('#service_add_tour option:selected').attr('id');
            var type = $('#service_add_list option:selected').attr('data-type');
            var credits = parseInt($('#service_add_list option:selected').attr('data-credits'));
            if(id_service==0) {
                $('#service_add_tour').prop('disabled',true);
                $('#rooms_num_val').prop('disabled',true);
                $('#rooms_num_val').val('');
                $('#use_credits_val').val(0);
            } else {
                switch(type) {
                    case 'tour_service':
                        $('#service_add_tour').prop('disabled',false);
                        if(id_tour==0) {
                            $('#service_add_btn').addClass('disabled');
                            $('#use_credits_val').val(0);
                            $('#rooms_num_val').prop('disabled',true);
                        } else {
                            $('#service_add_btn').removeClass('disabled');
                            $('#rooms_num_val').prop('disabled',false);
                            var count = parseInt($('#service_add_tour option:selected').attr('data-count'));
                            if(count==0) {
                                $('#service_add_btn').addClass('disabled');
                                $('#use_credits_val').val(0);
                                $('#rooms_num_val').val('');
                            } else {
                                credits = credits * count;
                                $('#use_credits_val').val(credits);
                                $('#rooms_num_val').val(count);
                            }
                        }
                        break;
                    case 'tour_generic':
                        $('#rooms_num_val').prop('disabled',true);
                        $('#rooms_num_val').val('');
                        $('#service_add_tour').prop('disabled',false);
                        if(id_tour==0) {
                            $('#service_add_btn').addClass('disabled');
                            $('#use_credits_val').val(0);
                        } else {
                            $('#service_add_btn').removeClass('disabled');
                            $('#use_credits_val').val(credits);
                        }
                        break;
                    case 'generic':
                        $('#rooms_num_val').prop('disabled',true);
                        $('#rooms_num_val').val('');
                        $('#service_add_tour').prop('disabled',true);
                        $('#service_add_btn').removeClass('disabled');
                        $('#use_credits_val').val(credits);
                        break;
                }
            }
        }

        window.manual_add_service = function() {
            var id_service = $('#service_add_list option:selected').attr('id');
            var id_tour = $('#service_add_tour option:selected').attr('id');
            var use_credits = ($('#use_credits').is(':checked')) ? 1 : 0;
            var use_credits_val = $('#use_credits_val').val();
            var rooms_num = $('#rooms_num_val').val();
            if(id_service!=0) {
                $('#service_add_btn').addClass('disabled');
                $.ajax({
                    url: "ajax/manual_add_service.php",
                    type: "POST",
                    data: {
                        id_user: window.id_user_edit,
                        id_service: id_service,
                        id_tour: id_tour,
                        use_credits: use_credits,
                        use_credits_val: use_credits_val,
                        rooms_num: rooms_num
                    },
                    async: true,
                    success: function (json) {
                        var rsp = JSON.parse(json);
                        if(rsp.status=='ok') {
                            $('#service_add_list option[id="0"]').prop('selected',true);
                            $('#service_add_tour option[id="0"]').prop('selected',true);
                            $('#service_add_tour').prop('disabled',true);
                            $('#use_credits_val').val(0);
                            $('#rooms_num_val').val('');
                            $('#rooms_num_val').prop('disabled',true);
                            $('#service_add_btn').addClass('disabled');
                            $('#purchased_services_table').DataTable().ajax.reload();
                        } else {
                            $('#service_add_btn').removeClass('disabled');
                        }
                    },
                    error: function () {
                        $('#service_add_btn').removeClass('disabled');
                    }
                });
            }
        }

        window.open_modal_service_log_edit = function(id) {
            $.ajax({
                url: "ajax/get_service_log.php",
                type: "POST",
                data: {
                    id: id
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    var service_name = rsp.service_name;
                    var tour_name = rsp.tour_name;
                    $('#rooms_num_edit').val(rsp.rooms_num);
                    $('#credits_used_edit').val(rsp.credits_used);
                    $('#note_edit').val(rsp.note);
                    if(rsp.service_type=='tour_service') {
                        $('#rooms_num_edit').prop('disabled',false);
                    } else {
                        $('#rooms_num_edit').prop('disabled',true);
                    }
                    if(tour_name==null || tour_name==undefined || tour_name=='') {
                        $('#modal_edit_service_log #service_title').html(service_name);
                    } else {
                        $('#modal_edit_service_log #service_title').html(service_name+' ('+tour_name+')');
                    }
                    $('#modal_edit_service_log').modal("show");
                }
            });
        };

        window.save_service_log = function() {
            $('#modal_edit_service_log button').addClass("disabled");
            var rooms_num = $('#rooms_num_edit').val();
            var credits_used = $('#credits_used_edit').val();
            var note = $('#note_edit').val();
            $.ajax({
                url: "ajax/save_service_log.php",
                type: "POST",
                data: {
                    id: window.service_log_id_sel,
                    rooms_num: rooms_num,
                    credits_used: credits_used,
                    note: note
                },
                async: true,
                success: function (json) {
                    var rsp = JSON.parse(json);
                    if(rsp.status=='ok') {
                        $('#modal_edit_service_log').modal("hide");
                        $('#modal_edit_service_log button').removeClass("disabled");
                        $('#purchased_services_table').DataTable().ajax.reload();
                    } else {
                        $('#modal_edit_service_log button').removeClass("disabled");
                    }
                },
                error: function() {
                    $('#modal_edit_service_log button').removeClass("disabled");
                }
            });
        }

        window.delete_service_log = function() {
            var retVal = confirm(window.backend_labels.delete_sure_msg);
            if( retVal == true ) {
                $('#modal_edit_service_log button').addClass("disabled");
                $.ajax({
                    url: "ajax/delete_service_log.php",
                    type: "POST",
                    data: {
                        id: window.service_log_id_sel
                    },
                    async: true,
                    success: function (json) {
                        var rsp = JSON.parse(json);
                        if(rsp.status=='ok') {
                            $('#modal_edit_service_log').modal("hide");
                            $('#modal_edit_service_log button').removeClass("disabled");
                            $('#purchased_services_table').DataTable().ajax.reload();
                        } else {
                            $('#modal_edit_service_log button').removeClass("disabled");
                        }
                    },
                    error: function() {
                        $('#modal_edit_service_log button').removeClass("disabled");
                    }
                });
            }

        }

        $("input[type='text']").change(function(){
            window.user_need_save = true;
        });
        $("input[type='checkbox']").change(function(){
            window.user_need_save = true;
        });
        $("select").change(function(){
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