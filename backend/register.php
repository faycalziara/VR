<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
if(!file_exists("../config/config.inc.php")) {
    header("Location: ../install/start.php");
}
if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'header_php'.DIRECTORY_SEPARATOR.'custom_b.php')) {
    include(__DIR__.DIRECTORY_SEPARATOR.'header_php'.DIRECTORY_SEPARATOR.'custom_b.php');
}
session_start();
include_once("../config/languages.inc.php");
require_once("functions.php");
if(check_maintenance_mode('backend')) {
    if(file_exists("../error_pages/custom/maintenance_backend.html")) {
        include("../error_pages/custom/maintenance_backend.html");
    } else {
        include("../error_pages/default/maintenance_backend.html");
    }
    exit;
}
$session_id = session_id();
$_SESSION['svt_si_l']=$session_id;
$settings = get_settings();
set_language($settings['language'],$settings['language_domain']);
$default_language = $settings['language'];
if($settings['enable_registration']==0) {
    die(_("Registration closed"));
}
$v = time();
$username_reg = "";
$email_reg = "";
$password_reg = "";
$auto_register = 0;
if(isset($_SESSION['username_reg']) && isset($_SESSION['email_reg']) && isset($_SESSION['password_reg'])) {
    $username_reg = $_SESSION['username_reg'];
    unset($_SESSION['username_reg']);
    $email_reg = $_SESSION['email_reg'];
    unset($_SESSION['email_reg']);
    $password_reg = $_SESSION['password_reg'];
    unset($_SESSION['password_reg']);
    $auto_register = 1;
}
if(isset($_SESSION['social_identifier']) && isset($_SESSION['social_provider'])) {
    $social_identifier = $_SESSION['social_identifier'];
    unset($_SESSION['social_identifier']);
    $social_provider = $_SESSION['social_provider'];
    unset($_SESSION['social_provider']);
}
if(empty($_SESSION['lang'])) {
    $browser_lang = detectUserLanguage();
    if(!empty($browser_lang)) {
        $lang = $browser_lang;
    } else {
        $lang = $settings['language'];
    }
} else {
    $lang = $_SESSION['lang'];
}
set_language($lang,$settings['language_domain']);
$_SESSION['theme_color']=$settings['theme_color'];
if(empty($settings['logo']) && !empty($settings['small_logo'])) {
    $settings['logo'] = $settings['small_logo'];
}
$style_register = $settings['style_register'];
switch($_SESSION['lang']) {
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
        $lang_code = substr($_SESSION['lang'], 0, 2);
        break;
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
?>

<!DOCTYPE html>
<html dir="<?php echo $dir; ?>" lang="<?php echo $lang_code; ?>">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta http-equiv="content-type" content="text/html;charset=UTF-8" />
    <meta charset="UTF-8">
    <meta name="description" content="">
    <meta name="author" content="">
    <title><?php echo $settings['name']; ?></title>
    <?php echo print_favicons_backend($settings['logo'],$settings['theme_color']); ?>
    <link rel="stylesheet" type="text/css" href="../viewer/vendor/fontawesome-free/css/fontawesome.min.css?v=6.5.1">
    <link rel="stylesheet" type="text/css" href="../viewer/vendor/fontawesome-free/css/solid.min.css?v=6.5.1">
    <link rel="stylesheet" type="text/css" href="../viewer/vendor/fontawesome-free/css/regular.min.css?v=6.5.1">
    <link rel="stylesheet" type="text/css" href="../viewer/vendor/fontawesome-free/css/brands.min.css?v=6.5.1">
    <?php switch ($settings['font_provider']) {
        case 'google': ?>
            <?php if($settings['cookie_consent']) { ?>
                <link rel="preconnect" href="https://fonts.googleapis.com">
                <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
                    <script type="text/plain" data-category="functionality" data-service="Google Fonts">
                        (function(d, l, s) {
                            const fontName = '<?php echo $settings['font_backend']; ?>';
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
                <link rel='stylesheet' type="text/css" crossorigin="anonymous" id="font_backend_link" href="https://fonts.googleapis.com/css2?family=<?php echo $settings['font_backend']; ?>">
                <?php } ?>
            <?php break;
        case 'collabs': ?>
            <link rel="preconnect" href="https://api.fonts.coollabs.io" crossorigin>
            <link rel="stylesheet" type="text/css" href="https://api.fonts.coollabs.io/css2?family=<?php echo $settings['font_backend']; ?>&display=swap">
            <?php break;
    } ?>
    <?php if($dir=='rtl') : ?>
        <link rel="stylesheet" type="text/css" href="css/sb-admin-2.rtl.min.css?v=2">
    <?php else: ?>
        <link rel="stylesheet" type="text/css" href="css/sb-admin-2.min.css?v=5">
    <?php endif; ?>
    <?php if($settings['cookie_consent']) : ?>
    <link rel="stylesheet" type="text/css" href="vendor/cookieconsent/cookieconsent.min.css?v=3.0.1">
    <?php endif; ?>
    <link rel="stylesheet" type="text/css" href="css/theme.php?v=<?php echo $v; ?>">
    <link rel="stylesheet" type="text/css" href="css/theme_dark.php?v=<?php echo $v; ?>">
    <link rel="stylesheet" type="text/css" href="css/custom.css?v=<?php echo $v; ?>">
    <?php if($dir=='rtl') : ?>
        <link rel="stylesheet" type="text/css" href="css/custom.rtl.css?v=<?php echo $v; ?>">
    <?php endif; ?>
    <link rel="stylesheet" type="text/css" href="css/dark_mode.css?v=<?php echo $v; ?>">
    <?php if(file_exists(__DIR__.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'custom_b.css')) : ?>
        <link rel="stylesheet" type="text/css" href="css/custom_b.css?v=<?php echo $v; ?>">
    <?php endif; ?>
</head>

<body class="bg_register <?php echo ($settings['background_reg']!='' && $style_register==2) ? 'bg_image' : 'bg-gradient-primary' ; ?>" style="<?php echo ($settings['background_reg']!='' && $style_register==2) ? 'background-image: url(assets/'.$settings['background_reg'].') !important;'  : '' ; ?>">
<script>
    var dark_mode_setting = <?php echo $settings['dark_mode']; ?>;
    var dark_mode = '0';
    if(dark_mode_setting==1) {
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            dark_mode = '1';
        }
        if (localStorage.getItem("dark_mode") === null) {
            localStorage.setItem("dark_mode",dark_mode);
        } else {
            dark_mode = localStorage.getItem('dark_mode');
        }
        if(dark_mode=='1') document.body.classList.add("dark_mode");
        window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
            dark_mode = e.matches ? '1' : '0';
            if(dark_mode=='1') {
                document.body.classList.add("dark_mode");
                localStorage.setItem("dark_mode",'1');
            } else {
                document.body.classList.remove("dark_mode");
                localStorage.setItem("dark_mode",'0');
            }
        });
    }
</script>
<style>
    *{ font-family: '<?php echo $settings['font_backend']; ?>', sans-serif; }
</style>
<div class="container">
    <div class="row">
        <?php if(!empty($settings['logo'])) { ?>
            <div class="col-md-12 text-white mt-4 text-center">
                <img style="max-height:100px;max-width:200px;width:auto;height:auto" src="assets/<?php echo $settings['logo']; ?>" />
            </div>
        <?php } else { ?>
            <div class="col-md-12 text-white mt-4 text-center title_name_register">
                <h3><?php echo strtoupper($settings['name']); ?></h3>
            </div>
        <?php } ?>
    </div>
    <div class="row justify-content-center mt-3">
        <div class="<?php echo ($style_register==1 && $settings['background_reg']!='') ? 'col-xl-10 col-lg-12 col-md-9' : 'col-xl-6 col-lg-8 col-md-9'; ?>" style="<?php echo ($style_register==2) ? 'max-width:540px' : ''; ?>">
            <div class="card o-hidden border-0 shadow-lg my-2 <?php echo ($style_register==2) ? 'glass_effect' : ''; ?>">
                <div class="card-body p-0">
                    <div class="row" style="min-height: 530px;">
                        <div style="<?php echo ($settings['background_reg']!='' && $style_register==1) ? 'background-image: url(assets/'.$settings['background_reg'].');'  : '' ; ?>" class="d-none bg-login-image <?php echo ($style_register==1 && $settings['background_reg']!='') ? 'col-lg-6 d-lg-block' : ''; ?>"></div>
                        <div class="<?php echo ($style_register==1 && $settings['background_reg']!='') ? 'col-lg-6' : 'col-md-12'; ?> pl-0">
                            <div class="p-5">
                                <li class="nav-item dropdown no-arrow lang_switcher_login <?php echo ($style_register==2 || $settings['background']=='') ? 'fix_left_pos_lang_switcher' : ''; ?>">
                                    <a class="nav-link dropdown-toggle" href="#" id="langDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        <img style="height: 14px;" src="img/flags_lang/<?php echo $lang; ?>.png?v=2" />
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-left shadow" aria-labelledby="langDropdown">
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
                                <div class="text-center">
                                    <h1 id="title_register" class="h4 text-gray-900 mb-4"><?php echo _("Create an Account!"); ?></h1>
                                </div>
                                <form class="user user_register" method="post" action="#">
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <input tabindex="1" autocomplete="new-password" type="text" required class="form-control form-control-user" id="username_r" placeholder="<?php echo _("User name"); ?>" value="<?php echo $username_reg; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-sm-12">
                                            <input tabindex="2" autocomplete="new-password" type="email" required class="form-control form-control-user" id="email_r" placeholder="<?php echo _("Email Address"); ?>" value="<?php echo $email_reg; ?>">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-0">
                                        <div class="col-md-12">
                                            <div class="form-group position-relative">
                                                <input tabindex="3" autocomplete="new-password" type="password" minlength="6" required class="form-control form-control-user" id="password_r" placeholder="<?php echo _("Password"); ?>" value="<?php echo $password_reg; ?>">
                                                <i onclick="show_hide_password('password_r');" class="btn_show_hide_passw fa fa-eye-slash" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                        <div class="col-sm-12">
                                            <div class="form-group position-relative">
                                                <input tabindex="4" autocomplete="new-password" type="password" minlength="6" pattern=".{2,}" required class="form-control form-control-user" id="password2_r" placeholder="<?php echo _("Repeat Password"); ?>" value="<?php echo $password_reg; ?>">
                                                <i onclick="show_hide_password('password2_r');" class="btn_show_hide_passw fa fa-eye-slash" aria-hidden="true"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <?php if($settings['captcha_register']) : ?>
                                    <div class="form-group row">
                                        <div class="col-sm-12 text-center">
                                            <canvas id="captcha_canvas"></canvas>
                                            <input tabindex="5" autofill="off" autocomplete="off" id="captcha_code" class="form-control form-control-user" type="text" placeholder="<?php echo _("Type the code above"); ?>" required />
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                    <?php if(!empty($settings['terms_and_conditions'])) : ?>
                                        <div class="form-group row">
                                            <div class="col-sm-12 text-center mt-2">
                                                <div class="form-check">
                                                    <input required class="form-check-input" type="checkbox" value="" id="terms_and_conditions">
                                                    <label class="form-check-label" for="terms_and_conditions">
                                                        <?php echo _("I agree to <a data-toggle='modal' data-backdrop='true' data-target='#modal_terms_and_conditions'>Terms and Conditions</a"); ?>
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    <button id="btn_register" type="submit" class="btn btn-primary btn-user btn-block">
                                        <?php echo _("Register"); ?>
                                    </button>
                                    <div class="text-center">
                                        <?php if($settings['social_google_enable'] || $settings['social_facebook_enable'] || $settings['social_twitter_enable'] || $settings['social_wechat_enable'] || $settings['social_qq_enable']) { ?>
                                            <div style="font-size:14px;" class="strike mt-2 mb-2"><span><?php echo _("or"); ?></span></div>
                                        <?php } ?>
                                        <?php if($settings['social_google_enable']) : ?>
                                            <a onclick="go_to_social_reg('Google');return false;" href="#" class="btn btn-circle btn-google btn-user">
                                                <i class="fab fa-google fa-fw"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if($settings['social_facebook_enable']) : ?>
                                            <a onclick="go_to_social_reg('Facebook');return false;" href="#" class="btn btn-circle btn-facebook btn-user">
                                                <i class="fab fa-facebook-f fa-fw"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if($settings['social_twitter_enable']) : ?>
                                            <a onclick="go_to_social_reg('Twitter');return false;" href="#" class="btn btn-circle btn-dark btn-user">
                                                <i class="fab fa-x-twitter fa-fw"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if($settings['social_wechat_enable']) : ?>
                                            <a onclick="go_to_social_reg('WeChat');return false;" href="#" class="btn btn-circle btn-wechat btn-user">
                                                <i class="fab fa-weixin fa-fw"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if($settings['social_qq_enable']) : ?>
                                            <a onclick="go_to_social_reg('QQ');return false;" href="#" class="btn btn-circle btn-qq btn-user">
                                                <i class="fab fa-qq fa-fw"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                    <hr>
                                    <div class="text-center">
                                        <a class="small" href="login.php"><?php echo _("Already have an account? Login!"); ?></a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="modal_activate" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Validate Account"); ?></h5>
            </div>
            <div class="modal-body">
                <p><?php echo _("Your account has been made, please verify it by clicking the activation link that has been send to your email."); ?></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" onclick="close_modal_activation();return false;"><?php echo _("Ok"); ?></button>
            </div>
        </div>
    </div>
</div>

<div id="modal_terms_and_conditions" class="modal" tabindex="-1" role="dialog" data-backdrop="static">
    <div style="max-width: 1280px;" class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _("Terms and Conditions"); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <?php echo $settings['terms_and_conditions']; ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo _("Close"); ?></button>
            </div>
        </div>
    </div>
</div>

<?php require_once("footer_login.php"); ?>

<script src="vendor/jquery/jquery.min.js?v=3.7.1"></script>
<script src="js/jquery-captcha.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/sb-admin-2.js?v=2"></script>
<?php if($settings['cookie_consent']) : ?>
<script type="text/javascript" src="vendor/cookieconsent/cookieconsent.min.js?v=3.0.1"></script>
<?php endif; ?>
<script>
    window.register_labels = {
        "error_msg":`<?php echo _("Error, retry later."); ?>`,
    };
</script>
<script src="js/function.js?v=<?php echo $v; ?>"></script>
<script>
    window.wizard_step = -1;
    window.captcha = null;
    var auto_register = <?php echo $auto_register; ?>;
    var social_provider = '<?php echo $social_provider; ?>';
    var social_identifier = '<?php echo $social_identifier; ?>';
    window.default_language = '<?php echo $default_language; ?>';
    window.language = '<?php echo $lang; ?>';
    (function($) {
        "use strict"; // Start of use strict
        if(auto_register==1) {
            register_account();
        }
        if($('#captcha_code').length) {
            window.captcha = new Captcha($('#captcha_canvas'));
        }
        $(".user_register").submit(function(e){
            register_account();
            e.preventDefault();
        });
        window.go_to_social_reg = function (provider) {
            var complete = true;
            if($('#captcha_code').length) {
                if($('#captcha_code').val()!='') {
                    $('#captcha_code').removeClass("error-highlight");
                    var valid_captcha = window.captcha.valid($('input[id="captcha_code"]').val());
                    if(!valid_captcha) {
                        complete = false;
                        $('#captcha_code').addClass("error-highlight");
                    }
                } else {
                    complete = false;
                    $('#captcha_code').addClass("error-highlight");
                }
            }
            if($('#terms_and_conditions').length) {
                if($('#terms_and_conditions').is(':checked')) {
                    $('#terms_and_conditions').removeClass("error-highlight");
                } else {
                    $('#terms_and_conditions').addClass("error-highlight");
                    complete = false;
                }
            }
             if(complete) {
                location.href = 'social_auth.php?provider='+provider+'&reg=1';
            }
        }
    })(jQuery); // End of use strict

    function show_hide_password(id) {
        if($('#'+id).attr("type") == "text"){
            $('#'+id).attr('type', 'password');
            $('#'+id).parent().find('i').addClass( "fa-eye-slash" );
            $('#'+id).parent().find('i').removeClass( "fa-eye" );
        }else if($('#'+id).attr("type") == "password"){
            $('#'+id).attr('type', 'text');
            $('#'+id).parent().find('i').removeClass( "fa-eye-slash" );
            $('#'+id).parent().find('i').addClass( "fa-eye" );
        }
    }
</script>
<?php if(!empty($settings['ga_tracking_id'])) : ?>
    <?php if($settings['cookie_consent']) { ?>
        <script type="text/plain" data-category="analytics" data-service="Google Analytics" async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $settings['ga_tracking_id']; ?>"></script>
        <script type="text/plain" data-category="analytics" data-service="Google Analytics">
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?php echo $settings['ga_tracking_id']; ?>', {
            'page_title': 'register'
        });
        </script>
    <?php } else { ?>
        <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo $settings['ga_tracking_id']; ?>"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '<?php echo $settings['ga_tracking_id']; ?>', {
                'page_title': 'register'
            });
        </script>
    <?php } ?>
<?php endif; ?>
<?php if($settings['cookie_consent']) : ?>
    <?php require_once('cookie_consent.php'); ?>
<?php endif; ?>
<script>
    window.addEventListener('load', () => {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('service-worker.js?v=<?php echo time(); ?>', {
                scope: '.'
            });
        }
    });
</script>
</body>
</html>
