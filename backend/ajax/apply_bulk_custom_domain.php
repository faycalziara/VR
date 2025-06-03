<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if((($_SERVER['SERVER_ADDR']==$_SESSION['demo_server_ip']) && ((!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR']))!=$_SESSION['demo_developer_ip']) && ($_SESSION['id_user']==$_SESSION['demo_user_id'])) || ($_SESSION['svt_si']!=session_id())) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
$id_user = (int) $_SESSION['id_user'];
session_write_close();
$id = (int)$_POST['id'];
$mode = strip_tags($_POST['mode']);
$default_tour = (int)$_POST['default_tour'];
$default_showcase = (int)$_POST['default_showcase'];
$default_globe = (int)$_POST['default_globe'];
switch($mode) {
    case 'remove':
        if($default_tour == 1) {
            $mysqli->query("DELETE FROM svt_custom_domains_tours_assoc WHERE id_custom_domain = (SELECT id FROM svt_custom_domains WHERE id=$id AND id_user=$id_user LIMIT 1);");
        }
        if($default_showcase == 1) {
            $mysqli->query("DELETE FROM svt_custom_domains_showcase_assoc WHERE id_custom_domain = (SELECT id FROM svt_custom_domains WHERE id=$id AND id_user=$id_user LIMIT 1);");
        }
        if($default_globe == 1) {
            $mysqli->query("DELETE FROM svt_custom_domains_globe_assoc WHERE id_custom_domain = (SELECT id FROM svt_custom_domains WHERE id=$id AND id_user=$id_user LIMIT 1);");
        }
        break;
    case 'apply':
        if($default_tour == 1) {
            $mysqli->query("DELETE FROM svt_custom_domains_tours_assoc WHERE id_custom_domain IN (SELECT id FROM svt_custom_domains WHERE id_user=$id_user);");
            $query = "SELECT id FROM svt_virtualtours WHERE id_user=$id_user;";
            $result = $mysqli->query($query);
            if($result->num_rows > 0) {
                $values = [];
                while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $id_item = (int)$row['id'];
                    $values[] = "($id, $id_item)";
                }
                if (!empty($values)) {
                    $mysqli->query("INSERT IGNORE INTO svt_custom_domains_tours_assoc(id_custom_domain, id_virtualtour) VALUES " . implode(',', $values));
                }
            }
        }
        if($default_showcase == 1) {
            $mysqli->query("DELETE FROM svt_custom_domains_showcase_assoc WHERE id_custom_domain IN (SELECT id FROM svt_custom_domains WHERE id_user=$id_user);");
            $query = "SELECT id FROM svt_showcases WHERE id_user=$id_user;";
            $result = $mysqli->query($query);
            if($result->num_rows > 0) {
                $values = [];
                while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $id_item = (int)$row['id'];
                    $values[] = "($id, $id_item)";
                }
                if (!empty($values)) {
                    $mysqli->query("INSERT IGNORE INTO svt_custom_domains_showcase_assoc(id_custom_domain, id_showcase) VALUES " . implode(',', $values));
                }
            }
        }
        if($default_globe == 1) {
            $mysqli->query("DELETE FROM svt_custom_domains_globe_assoc WHERE id_custom_domain IN (SELECT id FROM svt_custom_domains WHERE id_user=$id_user);");
            $query = "SELECT id FROM svt_globes WHERE id_user=$id_user;";
            $result = $mysqli->query($query);
            if($result->num_rows > 0) {
                $values = [];
                while($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    $id_item = (int)$row['id'];
                    $values[] = "($id, $id_item)";
                }
                if (!empty($values)) {
                    $mysqli->query("INSERT IGNORE INTO svt_custom_domains_globe_assoc(id_custom_domain, id_globe) VALUES " . implode(',', $values));
                }
            }
        }
        break;
}
ob_end_clean();
echo json_encode(array("status"=>"ok"));
exit;