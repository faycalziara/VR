<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
session_start();
if(file_exists("../config/demo.inc.php")) {
    require_once("../config/demo.inc.php");
    if($_SERVER['SERVER_ADDR']==DEMO_SERVER_IP && DEMO_DISABLE_CHANGE_PLAN==1) {
        //DEMO MODE
        die('demo mode');
    }
}
require_once("../backend/functions.php");
require_once("../db/connection.php");
require('../backend/vendor/stripe-php/init.php');
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

$debug = false;
if($debug) {
    register_shutdown_function( "fatal_handler" );
}

$settings = get_settings();
$key = $settings['stripe_secret_key'];
if(empty($key)) {
    exit;
}
$stripe = new \Stripe\StripeClient($key);

$input = @file_get_contents("php://input");
$event_json = json_decode($input);

$event = get_event($event_json->id);

if(isset($event) && $event->type == "checkout.session.completed") {
    $id_customer_stripe = $event->data->object->customer;
    $mode = $event->data->object->mode;
    switch ($mode) {
        case 'setup':
            $id_setup_intent = $event->data->object->setup_intent;
            $setup_intent = get_setup_intent($id_setup_intent);
            $id_subscription_stripe = $setup_intent->metadata->subscription_id;
            $payment_method = $setup_intent->payment_method;
            set_payment($id_subscription_stripe,$payment_method);
            $mysqli->query("UPDATE svt_users SET status_subscription_stripe=1 WHERE id_customer_stripe='$id_customer_stripe';");
            break;
        case 'subscription':
            $id_plan = $event->data->object->metadata->id_plan;
            $id_subscription_stripe = $event->data->object->subscription;
            $query = "SELECT id,id_plan FROM svt_users WHERE id_customer_stripe='$id_customer_stripe' LIMIT 1;";
            $result = $mysqli->query($query);
            $old_plan = "";
            if($result) {
                $row=$result->fetch_array(MYSQLI_ASSOC);
                $id_user = $row['id'];
                $id_plan_old = $row['id_plan'];
                $plan_old = get_plan($id_plan_old);
                $old_plan = $plan_old['name'];
                $plan_new = get_plan($id_plan);
                $name_new = $plan_new['name'];
                set_user_log($id_user,'subscribe_plan',json_encode(array("id"=>$id_plan,"name"=>$name_new)),date('Y-m-d H:i:s', time()));
            }
            $mysqli->query("UPDATE svt_users SET id_plan=$id_plan,id_subscription_stripe='$id_subscription_stripe',status_subscription_stripe=1,expire_plan_date=NULL WHERE id_customer_stripe='$id_customer_stripe';");
            if($settings['notify_plan_changes']) {
                $query = "SELECT u.id,u.username,u.email,p.name as plan FROM svt_users as u LEFT JOIN svt_plans as p ON p.id=u.id_plan WHERE u.id_customer_stripe='$id_customer_stripe';";
                $result = $mysqli->query($query);
                if($result) {
                    if($result->num_rows>0) {
                        while($row=$result->fetch_array(MYSQLI_ASSOC)) {
                            $id_user = $row['id'];
                            $username = $row['username'];
                            $email_u = $row['email'];
                            $plan = $row['plan'];
                            $subject = $settings['mail_plan_changed_subject'];
                            $body = $settings['mail_plan_changed_body'];
                            $body = str_replace("%USER_NAME%",$username,$body);
                            $body = str_replace("%PLAN_NAME%",$plan,$body);
                            $body = str_replace('<p><br></p>','<br>',$body);
                            $body = str_replace('<p>','<p style="padding:0;margin:0;">',$body);
                            $subject_q = str_replace("'","\'",$subject);
                            $body_q = str_replace("'","\'",$body);
                            $mysqli->query("INSERT INTO svt_notifications(id_user,subject,body,notify_user,notified) VALUES($id_user,'$subject_q','$body_q',1,0);");
                        }
                    }
                }
            }
            break;
        case 'payment':
            if(isset($event->data->object->metadata->id_plan)) {
                $id_plan = $event->data->object->metadata->id_plan;
                $query = "SELECT id,id_plan FROM svt_users WHERE id_customer_stripe='$id_customer_stripe' LIMIT 1;";
                $result = $mysqli->query($query);
                $old_plan = "";
                if($result) {
                    $row=$result->fetch_array(MYSQLI_ASSOC);
                    $id_user = $row['id'];
                    $id_plan_old = $row['id_plan'];
                    $plan_old = get_plan($id_plan_old);
                    $old_plan = $plan_old['name'];
                    $plan_new = get_plan($id_plan);
                    $name_new = $plan_new['name'];
                    set_user_log($id_user,'subscribe_plan',json_encode(array("id"=>$id_plan,"name"=>$name_new)),date('Y-m-d H:i:s', time()));
                    $mysqli->query("UPDATE svt_users SET id_plan=$id_plan,id_subscription_stripe=NULL,status_subscription_stripe=1,expire_plan_date=NULL WHERE id_customer_stripe='$id_customer_stripe';");
                    if($settings['notify_plan_changes']) {
                        $query = "SELECT u.id,u.username,u.email,p.name as plan FROM svt_users as u LEFT JOIN svt_plans as p ON p.id=u.id_plan WHERE u.id_customer_stripe='$id_customer_stripe' LIMIT 1;";
                        $result = $mysqli->query($query);
                        if($result) {
                            if($result->num_rows==1) {
                                $row=$result->fetch_array(MYSQLI_ASSOC);
                                $id_user = $row['id'];
                                $username = $row['username'];
                                $email_u = $row['email'];
                                $plan = $row['plan'];
                                $subject = $settings['mail_plan_changed_subject'];
                                $body = $settings['mail_plan_changed_body'];
                                $body = str_replace("%USER_NAME%",$username,$body);
                                $body = str_replace("%PLAN_NAME%",$plan,$body);
                                $body = str_replace('<p><br></p>','<br>',$body);
                                $body = str_replace('<p>','<p style="padding:0;margin:0;">',$body);
                                $subject_q = str_replace("'","\'",$subject);
                                $body_q = str_replace("'","\'",$body);
                                $mysqli->query("INSERT INTO svt_notifications(id_user,subject,body,notify_user,notified) VALUES($id_user,'$subject_q','$body_q',1,0);");
                            }
                        }
                    }
                }
            } else if(isset($event->data->object->metadata->id_service)) {
                $id_service = $event->data->object->metadata->id_service;
                $id_vt = $event->data->object->metadata->id_vt;
                if($id_vt=="0") $id_vt = "NULL";
                $name_vt = $event->data->object->metadata->name_vt;
                $price = $event->data->object->amount_total;
                $currency = $event->data->object->currency;
                $currency = strtolower($currency);
                switch($currency) {
                    case 'vnd':
                    case 'clp':
                    case 'jpy':
                    case 'rwf':
                    case 'pyg':
                        $price = (float)$price;
                        break;
                    default:
                        $price = (float)$price/100;
                        break;
                }
                $currency = strtoupper($currency);
                $uid = $event->data->object->metadata->uid;
                $count = $event->data->object->metadata->count;
                $note = $event->data->object->metadata->note;
                if(!empty($note)) $note = gzuncompress(base64_decode($note));
                $note_q = str_replace("'","\'",$note);
                $service = get_service($id_service);
                $query = "SELECT id,username,email FROM svt_users WHERE id_customer_stripe='$id_customer_stripe' LIMIT 1;";
                $result = $mysqli->query($query);
                if($result) {
                    $row=$result->fetch_array(MYSQLI_ASSOC);
                    $id_user = $row['id'];
                    set_user_log($id_user,'purchase_service',json_encode(array("id"=>$id_service,"name"=>$service['name'],"id_vt"=>$id_vt,"name_vt"=>$name_vt)),date('Y-m-d H:i:s', time()));
                    $mysqli->query("INSERT INTO svt_services_log(uid,id_user,id_service,id_virtualtour,date_time,credits_used,price,currency,note,rooms_num) VALUES('$uid',$id_user,$id_service,$id_vt,NOW(),0,$price,'$currency','$note_q',$count);");
                    if($id_vt!="NULL" && $service['block_tour']) {
                        $mysqli->query("UPDATE svt_virtualtours SET block_tour=1 WHERE id=$id_vt;");
                    }
                    if($settings['notify_service_purchase']) {
                        $username = $row['username'];
                        $email_u = $row['email'];
                        $service_name = $service['name'];
                        $subject = $settings['mail_service_purchased_subject'];
                        $body = $settings['mail_service_purchased_body'];
                        if(!empty($name_vt)) {
                            $service_name = $service_name." (".$name_vt.")";
                        }
                        $body = str_replace("%USER_NAME%",$username,$body);
                        $body = str_replace("%SERVICE_NAME%",$service_name,$body);
                        $body = str_replace("%NOTE%",$note,$body);
                        $body = str_replace('<p><br></p>','<br>',$body);
                        $body = str_replace('<p>','<p style="padding:0;margin:0;">',$body);
                        $subject_q = str_replace("'","\'",$subject);
                        $body_q = str_replace("'","\'",$body);
                        $mysqli->query("INSERT INTO svt_notifications(id_user,subject,body,notify_user,notified) VALUES($id_user,'$subject_q','$body_q',1,0);");
                    }
                }
            }
            break;
    }
}

if(isset($event) && $event->type == "invoice.payment_failed") {
    $id_customer_stripe = $event->data->object->customer;
    $id_subscription_stripe = $event->data->object->subscription;
    $mysqli->query("UPDATE svt_users SET status_subscription_stripe=0 WHERE id_customer_stripe='$id_customer_stripe';");
}

if(isset($event) && $event->type == "customer.subscription.deleted") {
    $id_customer_stripe = $event->data->object->customer;
    $id_subscription_stripe = $event->data->object->subscription;
    $end_date = $event->data->object->current_period_end;
    $end_date = date('Y-m-d H:i:s',$end_date);
    $mysqli->query("UPDATE svt_users SET id_subscription_stripe=NULL,status_subscription_stripe=0,expire_plan_date=NULL,id_plan=0 WHERE id_customer_stripe='$id_customer_stripe' AND id_subscription_stripe='$id_subscription_stripe';");
}

if(isset($event) && $event->type == "customer.subscription.updated") {
    $id_customer_stripe = $event->data->object->customer;
    $id_product_stripe = $event->data->object->items->data[0]->plan->product;
    $cancel_at_end = $event->data->object->cancel_at_period_end;
    if($cancel_at_end) {
        $end_date = $event->data->object->current_period_end;
        $end_date = date('Y-m-d H:i:s',$end_date);
        $mysqli->query("UPDATE svt_users SET expire_plan_date='$end_date' WHERE id_customer_stripe='$id_customer_stripe';");
    } else {
        $id_plan = get_id_plan_stripe($id_product_stripe);
        $mysqli->query("UPDATE svt_users SET id_plan=$id_plan,expire_plan_date=NULL WHERE id_customer_stripe='$id_customer_stripe';");
    }
}

function get_event($id) {
    global $stripe;
    try {
        $response = $stripe->events->retrieve($id, []);
        return $response;
    } catch(\Stripe\Exception\CardException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\RateLimitException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\AuthenticationException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (Exception $e) {
        echo json_encode(array("status"=>"error","msg"=>"Error, retry later."));
        exit;
    }
}

function get_setup_intent($id) {
    global $stripe;
    try {
        $response = $stripe->setupIntents->retrieve($id, []);
        return $response;
    } catch(\Stripe\Exception\CardException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\RateLimitException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\AuthenticationException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (Exception $e) {
        echo json_encode(array("status"=>"error","msg"=>"Error, retry later."));
        exit;
    }
}

function set_payment($id_subscription_stripe,$payment_method) {
    global $stripe;
    try {
        $response = $stripe->subscriptions->update($id_subscription_stripe, [
            'default_payment_method' => $payment_method,
        ]);
        return $response;
    } catch(\Stripe\Exception\CardException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\RateLimitException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\InvalidRequestException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\AuthenticationException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiConnectionException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (\Stripe\Exception\ApiErrorException $e) {
        echo json_encode(array("status"=>"error","msg"=>$e->getError()->message));
        exit;
    } catch (Exception $e) {
        echo json_encode(array("status"=>"error","msg"=>"Error, retry later."));
        exit;
    }
}

function fatal_handler() {
    global $debug;
    $errfile = "unknown file";
    $errstr  = "shutdown";
    $errno   = E_CORE_ERROR;
    $errline = 0;
    $error = error_get_last();
    if($error !== NULL) {
        $errno   = $error["type"];
        $errfile = $error["file"];
        $errline = $error["line"];
        $errstr  = $error["message"];
        if($debug) {
            print_r($error);
        }
    }
}

function format_error( $errno, $errstr, $errfile, $errline ) {
    $trace = print_r( debug_backtrace( false ), true );
    $content = "File: $errfile, Error: $errstr, Line:$errline";
    return $content;
}