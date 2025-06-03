<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip']) && ($_SESSION['id_user']==$_SESSION['demo_user_id'])) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$insert_id = null;
$id_user = $_SESSION['id_user'];
$user_info = get_user_info($_SESSION['id_user']);
$name = strip_tags($_POST['name']);
$author = strip_tags($_POST['author']);
$language = strip_tags($_POST['language']);
$vt_type = strip_tags($_POST['vt_type']);
$external=0;
$ar_simulator=0;
switch ($vt_type) {
    case 0:
        $external=0;
        $ar_simulator=0;
        break;
    case 1:
        $external=1;
        $ar_simulator=0;
        break;
    case 2:
        $external=0;
        $ar_simulator=1;
        break;
}
$settings = get_settings();
$plan = get_plan($user_info['id_plan']);
$id_vt_template = $settings['id_vt_template'];
if(!empty($plan)) {
    if($plan['override_template']) {
        $id_vt_template = $plan['id_vt_template'];
    }
}
if(!$external && !$ar_simulator && !empty($id_vt_template)) {
    $add_language_translation = "";
    $swap_language_translation = "";
    $query_l = "SELECT language,languages_enabled FROM svt_virtualtours WHERE id = $id_vt_template LIMIT 1;";
    $result_l = $mysqli->query($query_l);
    if($result_l) {
        if($result_l->num_rows == 1) {
            $row_l = $result_l->fetch_array(MYSQLI_ASSOC);
            $language_vt = $row_l['language'];
            if(empty($language)) {
                $language = $settings['language'];
            }
            if(empty($language_vt)) {
                $language_vt = $settings['language'];
            }
            $languages_enabled_vt = $row_l['languages_enabled'];
            if(!empty($languages_enabled_vt)) {
                $languages_enabled_vt=json_decode($languages_enabled_vt,true);
                if($language!=$language_vt) {
                    if($languages_enabled_vt[$language]==1) {
                        $add_language_translation = $language_vt;
                        $swap_language_translation = $language;
                    } else {
                        $languages_enabled_vt[$language]=1;
                        $add_language_translation = $language_vt;

                    }
                }
                $languages_enabled_vt = json_encode($languages_enabled_vt);
            }
        }
    }
    $mysqli->query("CREATE TEMPORARY TABLE svt_virtualtour_tmp SELECT * FROM svt_virtualtours WHERE id = $id_vt_template;");
    $query="UPDATE svt_virtualtour_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_virtualtours),active=1,code=NULL,list_alt=NULL,start_date=NULL,end_date=NULL,snipcart_api_key=NULL,woocommerce_store_url=NULL,woocommerce_customer_key=NULL,woocommerce_customer_secret=NULL,password=NULL,note=NULL,html_landing=NULL,description=NULL,dollhouse=NULL,ga_tracking_id=NULL,fb_page_id=NULL,friendly_url=NULL,id_user=?,name=?,author=?,language=?,languages_enabled=?,date_created=NOW();";
    if($smt = $mysqli->prepare($query)) {
        $smt->bind_param('issss', $id_user,$name,$author,$language,$languages_enabled_vt);
        $smt->execute();
    }
    $result = $mysqli->query("INSERT INTO svt_virtualtours SELECT * FROM svt_virtualtour_tmp;");
    $insert_id = $mysqli->insert_id;
    $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_virtualtours_tmp;");
    $result = $mysqli->query("SELECT id FROM svt_virtualtours_versions WHERE id_virtualtour=$id_vt_template;");
    if($result) {
        if($result->num_rows>0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_version = $row['id'];
                $mysqli->query("CREATE TEMPORARY TABLE svt_virtualtours_versions_tmp SELECT * FROM svt_virtualtours_versions WHERE id=$id_version;");
                $mysqli->query("UPDATE svt_virtualtours_versions_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_virtualtours_versions),id_virtualtour=$insert_id;");
                $mysqli->query("INSERT INTO svt_virtualtours_versions SELECT * FROM svt_virtualtours_versions_tmp;");
                $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_virtualtours_versions_tmp;");
            }
        }
    }
    $result = $mysqli->query("SELECT * FROM svt_virtualtours_lang WHERE id_virtualtour=$id_vt_template;");
    if($result) {
        if($result->num_rows>0) {
            $mysqli->query("CREATE TEMPORARY TABLE svt_virtualtours_lang_tmp SELECT * FROM svt_virtualtours_lang WHERE id_virtualtour=$id_vt_template;");
            $mysqli->query("UPDATE svt_virtualtours_lang_tmp SET id_virtualtour=$insert_id,name=NULL,description=NULL;");
            $mysqli->query("INSERT INTO svt_virtualtours_lang SELECT * FROM svt_virtualtours_lang_tmp;");
            $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_virtualtours_lang_tmp;");
        }
    }
    if(!empty($add_language_translation)) {
        $query_d = "SELECT info_box, form_content, password_title, password_description, description, meta_title, meta_description, list_alt, avatar_video, media_file, location_content, custom_content, custom2_content, custom3_content, custom4_content, custom5_content, intro_desktop, intro_mobile, learning_modal_title, learning_modal_subtitle, learning_modal_description FROM svt_virtualtours WHERE id = ? LIMIT 1";
        $stmt_d = $mysqli->prepare($query_d);
        $stmt_d->bind_param("i", $insert_id);
        $stmt_d->execute();
        $result_d = $stmt_d->get_result();
        $row_d = ($result_d->num_rows === 1) ? $result_d->fetch_assoc() : [];
        $stmt_d->close();
        $query_l = "SELECT * FROM svt_virtualtours_lang WHERE id_virtualtour = ? AND language = ? LIMIT 1";
        $stmt_l = $mysqli->prepare($query_l);
        $stmt_l->bind_param("is", $insert_id, $add_language_translation);
        $stmt_l->execute();
        $result_l = $stmt_l->get_result();
        $row_l = ($result_l->num_rows === 1) ? $result_l->fetch_assoc() : [];
        $stmt_l->close();
        if ($row_l) {
            $update_fields = [];
            $update_values = [];
            foreach ($row_d as $field => $default_value) {
                if (isset($row_l[$field])) {
                    $translated_value = trim($row_l[$field]);
                    if (empty($translated_value) || $translated_value === '<p><br></p>') {
                        $update_fields[] = "$field = ?";
                        $update_values[] = $default_value;
                    }
                }
            }
            if (!empty($update_fields)) {
                $update_query = "UPDATE svt_virtualtours_lang SET " . implode(", ", $update_fields) . " WHERE id_virtualtour = ? AND language = ?";
                $update_stmt = $mysqli->prepare($update_query);
                if($update_stmt) {
                    $types = str_repeat("s", count($update_values)) . "is";
                    $update_values[] = $insert_id;
                    $update_values[] = $add_language_translation;
                    if($update_stmt->bind_param($types, ...$update_values)) {
                        $update_stmt->execute();
                    }
                }
                $update_stmt->close();
            }
        } else {
            $fields = array_keys($row_d);
            $placeholders = implode(", ", array_fill(0, count($fields), "?"));
            $columns = implode(", ", $fields);
            $insert_query = "INSERT INTO svt_virtualtours_lang (id_virtualtour, language, $columns) VALUES (?, ?, $placeholders)";
            $insert_stmt = $mysqli->prepare($insert_query);
            if($insert_stmt) {
                $insert_values = array_values($row_d);
                array_unshift($insert_values, $insert_id, $add_language_translation);
                $types = "is" . str_repeat("s", count($row_d));
                if($insert_stmt->bind_param($types, ...$insert_values)) {
                    $insert_stmt->execute();
                }
            }
            $insert_stmt->close();
        }
    }
    if(!empty($swap_language_translation)) {
        $query_l = "SELECT info_box, form_content, password_title, password_description, description, meta_title, meta_description, list_alt, avatar_video, media_file, location_content, custom_content, custom2_content, custom3_content, custom4_content, custom5_content, intro_desktop, intro_mobile, learning_modal_title, learning_modal_subtitle, learning_modal_description FROM svt_virtualtours_lang WHERE id_virtualtour = ? AND language = ? LIMIT 1";
        $stmt_l = $mysqli->prepare($query_l);
        $stmt_l->bind_param("is", $insert_id, $swap_language_translation);
        $stmt_l->execute();
        $result_l = $stmt_l->get_result();
        $row_l = ($result_l->num_rows === 1) ? $result_l->fetch_assoc() : [];
        $stmt_l->close();
        if ($row_l) {
            $update_fields = [];
            $update_values = [];
            foreach ($row_l as $field => $value) {
                if (!empty($value) && $value !== '<p><br></p>') {
                    $update_fields[] = "$field = ?";
                    $update_values[] = $value;
                }
            }
            if (!empty($update_fields)) {
                $update_query = "UPDATE svt_virtualtours SET " . implode(", ", $update_fields) . " WHERE id = ?";
                $update_stmt = $mysqli->prepare($update_query);
                if($update_stmt) {
                    $types = str_repeat("s", count($update_values)) . "i";
                    $update_values[] = $insert_id;
                    if($update_stmt->bind_param($types, ...$update_values)) {
                        $update_stmt->execute();
                    }
                }
                $update_stmt->close();
            }
        }
    }
    $array_icons = array();
    $result = $mysqli->query("SELECT id FROM svt_icons WHERE id_virtualtour=$id_vt_template;");
    if($result) {
        if($result->num_rows>0) {
            while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $id_icon = $row['id'];
                $mysqli->query("CREATE TEMPORARY TABLE svt_icon_tmp SELECT * FROM svt_icons WHERE id=$id_icon;");
                $mysqli->query("UPDATE svt_icon_tmp SET id=(SELECT MAX(id)+1 as id FROM svt_icons),id_virtualtour=$insert_id;");
                $mysqli->query("INSERT INTO svt_icons SELECT * FROM svt_icon_tmp;");
                $id_icon_new = $mysqli->insert_id;
                $array_icons[$id_icon] = $id_icon_new;
                $mysqli->query("DROP TEMPORARY TABLE IF EXISTS svt_icon_tmp;");
            }
        }
    }
    $query = "SELECT ui_style FROM svt_virtualtours WHERE id=$insert_id LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if ($result->num_rows == 1) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $ui_style = $row['ui_style'];
            if (!empty($ui_style)) {
                $ui_style_array = json_decode($ui_style, true);
                foreach ($ui_style_array['controls'] as $key => $item) {
                    if(!empty($item['icon_library']) && $item['icon_library']!=0) {
                        if(array_key_exists($item['icon_library'],$array_icons)) {
                            $ui_style_array['controls'][$key]['icon_library'] = $array_icons[$item['icon_library']];
                        }
                    }
                }
                $ui_style = str_replace("'", "\'", json_encode($ui_style_array, JSON_UNESCAPED_UNICODE));
                $mysqli->query("UPDATE svt_virtualtours SET ui_style='$ui_style' WHERE id=$insert_id;");
            }
        }
    }
} else {
    if($ar_simulator==1) {
        $query = "INSERT INTO svt_virtualtours(id_user,date_created,name,author,hfov,min_hfov,max_hfov,external,ar_simulator,show_device_orientation,show_webvr,auto_show_slider,arrows_nav,show_autorotation_toggle,show_presentation,keyboard_mode,language)
            VALUES(?,NOW(),?,?,70,70,70,?,?,0,0,2,0,0,0,0,?);";
    } else {
        $query = "INSERT INTO svt_virtualtours(id_user,date_created,name,author,hfov,min_hfov,max_hfov,external,ar_simulator,language)
            VALUES(?,NOW(),?,?,100,50,100,?,?,?);";
    }
    if($smt = $mysqli->prepare($query)) {
        $smt->bind_param('issiis',  $id_user,$name,$author,$external,$ar_simulator,$language);
        $result = $smt->execute();
        if($result) {
            $insert_id = $mysqli->insert_id;
        }
    } else {
        ob_end_clean();
        echo json_encode(array("status"=>"error"));
        exit;
    }
}
if($insert_id!=null) {
    $_SESSION['id_virtualtour_sel'] = $insert_id;
    $_SESSION['name_virtualtour_sel'] = $name;
    session_write_close();
    $code = md5($insert_id);
    $mysqli->query("UPDATE svt_virtualtours SET code='$code' WHERE id=$insert_id;");
    if($settings['aws_s3_enabled']==1 && $settings['aws_s3_vt_auto']==1) {
        $mysqli->query("UPDATE svt_virtualtours SET aws_s3=1 WHERE id=$insert_id;");
    }
    $query = "SELECT id FROM svt_advertisements WHERE auto_assign=1 LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $id_ads=$row['id'];
            $mysqli->query("INSERT INTO svt_assign_advertisements(id_advertisement,id_virtualtour) VALUES($id_ads,$insert_id);");
        }
    }
    $query = "SELECT id FROM svt_custom_domains WHERE id_user=$id_user AND default_tour=1 LIMIT 1;";
    $result = $mysqli->query($query);
    if($result) {
        if($result->num_rows==1) {
            $row=$result->fetch_array(MYSQLI_ASSOC);
            $id_custom_domain=$row['id'];
            $mysqli->query("INSERT INTO svt_custom_domains_tours_assoc(id_virtualtour,id_custom_domain) VALUES($insert_id,$id_custom_domain);");
        }
    }
    set_user_log($id_user,'add_virtual_tour',json_encode(array("id"=>$insert_id,"name"=>$name)),date('Y-m-d H:i:s', time()));
    ob_end_clean();
    echo json_encode(array("status"=>"ok","id"=>$insert_id));
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>$mysqli->error));
}