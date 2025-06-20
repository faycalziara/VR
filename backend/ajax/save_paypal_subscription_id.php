<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip']) && ($_SESSION['id_user']==$_SESSION['demo_user_id'])) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
if(file_exists("../../config/demo.inc.php")) {
    require_once("../../config/demo.inc.php");
    if($_SERVER['SERVER_ADDR']==DEMO_SERVER_IP && DEMO_DISABLE_CHANGE_PLAN==1) {
        //DEMO MODE
        die();
    }
}
require_once("../../db/connection.php");
require_once("../functions.php");
$settings = get_settings();
$user_info = get_user_info($_SESSION['id_user']);
$old_plan = $user_info['plan'];
if(!empty($user_info['language'])) {
    set_language($user_info['language'],$settings['language_domain']);
} else {
    set_language($settings['language'],$settings['language_domain']);
}
session_write_close();
$id_user = $_POST['id_user'];
$intent = $_POST['intent'];
if(isset($_POST['subscriptionID'])) {
    $subscriptionID = $_POST['subscriptionID'];
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("An error has occurred, please try again later"),"debug"=>serialize($_POST)));
}
$client_id = $settings['paypal_client_id'];
$client_secret = $settings['paypal_client_secret'];
if($settings['paypal_live']) {
    $url_paypal = "api-m.paypal.com";
} else {
    $url_paypal = "api-m.sandbox.paypal.com";
}
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://'.$url_paypal.'/v1/oauth2/token');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
curl_setopt($ch, CURLOPT_USERPWD, $client_id . ':' . $client_secret);
$headers = array();
$headers[] = 'Accept: application/json';
$headers[] = 'Accept-Language: en_US';
$headers[] = 'Content-Type: application/x-www-form-urlencoded';
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$result = curl_exec($ch);
if (curl_errno($ch)) {
    echo json_encode(array("status"=>"error","msg"=>curl_error($ch)));
    die();
} else {
    $response = json_decode($result,true);
    if(isset($response['error'])) {
        echo json_encode(array("status"=>"error","msg"=>$response['error_description']));
        die();
    } else {
        if(isset($response['access_token'])) {
            $access_token = $response['access_token'];
        } else {
            echo json_encode(array("status"=>"error","msg"=>"An error has occurred, please try again later","debug"=>serialize($response)));
            die();
        }
    }
}
curl_close($ch);
switch($intent) {
    case 'subscription':
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://'.$url_paypal.'/v1/billing/subscriptions/'.$subscriptionID);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer '.$access_token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo json_encode(array("status"=>"error","msg"=>curl_error($ch)));
            die();
        } else {
            $response = json_decode($result,true);
            if(isset($response['plan_id'])) {
                $id_paypal_plan = $response['plan_id'];
            } else {
                echo json_encode(array("status"=>"error","msg"=>"An error has occurred, please try again later","debug"=>serialize($response)));
                die();
            }
        }
        curl_close($ch);
        $query = "UPDATE svt_users SET id_subscription_paypal=?,id_plan=(SELECT id FROM svt_plans WHERE id_plan_paypal=? OR id_plan2_paypal=? LIMIT 1),expire_plan_date=NULL,status_subscription_paypal=1 WHERE id=?;";
        if($smt = $mysqli->prepare($query)) {
            $smt->bind_param('sssi',$subscriptionID,$id_paypal_plan,$id_paypal_plan,$id_user);
            $result = $smt->execute();
        }
        break;
    case 'order':
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://'.$url_paypal.'/v1/checkout/orders/'.$subscriptionID);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer '.$access_token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo json_encode(array("status"=>"error","msg"=>curl_error($ch)));
            die();
        } else {
            $response = json_decode($result,true);
            if(isset($response['purchase_units'][0]['payments']['captures'][0]['custom_id'])) {
                $id_paypal_plan = $response['purchase_units'][0]['payments']['captures'][0]['custom_id'];
            } else {
                echo json_encode(array("status"=>"error","msg"=>"An error has occurred, please try again later","debug"=>serialize($response)));
                die();
            }
        }
        curl_close($ch);
        $query = "UPDATE svt_users SET id_subscription_paypal=NULL,id_plan=?,expire_plan_date=NULL,status_subscription_paypal=1 WHERE id=?;";
        if($smt = $mysqli->prepare($query)) {
            $smt->bind_param('ii',$id_paypal_plan,$id_user);
            $result = $smt->execute();
        }
        break;
    case 'service':
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://'.$url_paypal.'/v1/checkout/orders/'.$subscriptionID);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        $headers = array();
        $headers[] = 'Content-Type: application/json';
        $headers[] = 'Authorization: Bearer '.$access_token;
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            echo json_encode(array("status"=>"error","msg"=>curl_error($ch)));
            die();
        } else {
            $response = json_decode($result,true);
            if(isset($response['purchase_units'][0]['payments']['captures'][0]['custom_id'])) {
                $params_service = $response['purchase_units'][0]['payments']['captures'][0]['custom_id'];
                $params_service = json_decode($params_service,true);
                $price = $response['purchase_units'][0]['payments']['captures'][0]['amount']['value'];
                $currency = $response['purchase_units'][0]['payments']['captures'][0]['amount']['currency_code'];
                ob_end_clean();
                if($params_service['id_vt']=="0") $params_service['id_vt'] = "NULL";
            } else {
                echo json_encode(array("status"=>"error","msg"=>"An error has occurred, please try again later","debug"=>serialize($response)));
                die();
            }
        }
        curl_close($ch);
        $note = strip_tags($_POST['note']);
        $note_q = str_replace("'","\'",$note);
        $result = $mysqli->query("INSERT INTO svt_services_log(uid,id_user,id_service,id_virtualtour,date_time,credits_used,price,currency,note,rooms_num) VALUES('{$params_service['uid']}',$id_user,{$params_service['id_service']},{$params_service['id_vt']},NOW(),0,$price,'$currency','$note_q',{$params_service['count']});");
        break;
}
if($result) {
    switch($intent) {
        case 'subscription':
        case 'order':
            $query = "SELECT u.id,u.username,u.email,p.name as plan,p.id as id_plan FROM svt_users as u LEFT JOIN svt_plans as p ON p.id=u.id_plan WHERE u.id=$id_user;";
            $result = $mysqli->query($query);
            if($result) {
                if($result->num_rows>0) {
                    while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                        $username = $row['username'];
                        $email_u = $row['email'];
                        $plan = $row['plan'];
                        $id_plan = $row['id_plan'];
                        set_user_log($id_user,'subscribe_plan',json_encode(array("id"=>$id_plan,"name"=>$plan)),date('Y-m-d H:i:s', time()));
                        if ($settings['notify_plan_changes']) {
                            $subject = $settings['mail_plan_changed_subject'];
                            $body = $settings['mail_plan_changed_body'];
                            $body = str_replace("%USER_NAME%", $username, $body);
                            $body = str_replace("%PLAN_NAME%", $plan, $body);
                            $body = str_replace('<p><br></p>', '<br>', $body);
                            $body = str_replace('<p>', '<p style="padding:0;margin:0;">', $body);
                            $subject_q = str_replace("'", "\'", $subject);
                            $body_q = str_replace("'", "\'", $body);
                            $mysqli->query("INSERT INTO svt_notifications(id_user,subject,body,notify_user,notified) VALUES($id_user,'$subject_q','$body_q',1,0);");
                        }
                    }
                }
            }
            break;
        case 'service':
            $service = get_service($params_service['id_service']);
            if($params_service['id_vt']!="NULL" && $service['block_tour']) {
                $mysqli->query("UPDATE svt_virtualtours SET block_tour=1 WHERE id={$params_service['id_vt']};");
            }
            $query = "SELECT id,username,email FROM svt_users WHERE id=$id_user LIMIT 1;";
            $result = $mysqli->query($query);
            if($result) {
                $row=$result->fetch_array(MYSQLI_ASSOC);
                set_user_log($id_user,'purchase_service',json_encode(array("id"=>$params_service['id_service'],"name"=>$service['name'],"id_vt"=>$params_service['id_vt'],"name_vt"=>$params_service['name_vt'])),date('Y-m-d H:i:s', time()));
                if($settings['notify_service_purchase']) {
                    $username = $row['username'];
                    $email_u = $row['email'];
                    $service_name = $service['name'];
                    $subject = $settings['mail_service_purchased_subject'];
                    $body = $settings['mail_service_purchased_body'];
                    $body = str_replace("%USER_NAME%",$username,$body);
                    if(!empty($params_service['name_vt'])) {
                        $service_name = $service_name." (".$params_service['name_vt'].")";
                    }
                    $body = str_replace("%SERVICE_NAME%",$service_name,$body);
                    $body = str_replace("%NOTE%", $note, $body);
                    $body = str_replace('<p><br></p>','<br>',$body);
                    $body = str_replace('<p>','<p style="padding:0;margin:0;">',$body);
                    $subject_q = str_replace("'","\'",$subject);
                    $body_q = str_replace("'","\'",$body);
                    $mysqli->query("INSERT INTO svt_notifications(id_user,subject,body,notify_user,notified) VALUES($id_user,'$subject_q','$body_q',1,0);");
                }
            }
            break;
    }
    ob_end_clean();
    if($intent=='service') {
        echo json_encode(array("status"=>"ok","uid"=>$params_service['uid']));
    } else {
        echo json_encode(array("status"=>"ok"));
    }
} else {
    ob_end_clean();
    echo json_encode(array("status"=>"error","msg"=>_("An error has occurred, please try again later"),"debug"=>$mysqli->error));
}