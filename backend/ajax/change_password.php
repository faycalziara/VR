<?php
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE & ~E_DEPRECATED);
ob_start();
session_start();
if (
    ($_SERVER['SERVER_ADDR'] == $_SESSION['demo_server_ip'] &&
        (!empty($_SERVER['HTTP_CLIENT_IP']) ? $_SERVER['HTTP_CLIENT_IP'] : (!empty($_SERVER['HTTP_X_FORWARDED_FOR']) ? explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0] : $_SERVER['REMOTE_ADDR'])) != $_SESSION['demo_developer_ip'] &&
        $_SESSION['id_user'] == $_SESSION['demo_user_id']) ||
    $_SESSION['svt_si'] != session_id()
) {
    die();
}
require_once("../../db/connection.php");
require_once("../functions.php");
if (!isset($_SESSION['id_user'])) {
    ob_end_clean();
    echo json_encode(array("status" => "error", "message" => "Not authenticated"));
    exit;
}
$id_user = (int)$_SESSION['id_user'];
session_write_close();
if (!isset($_POST['id_svt']) || !isset($_POST['password_svt'])) {
    ob_end_clean();
    echo json_encode(array("status" => "error", "message" => "Invalid input"));
    exit;
}
$id = (int)$_POST['id_svt'];
$password = strip_tags($_POST['password_svt']);
if ($id !== $id_user) {
    if (get_user_role($id_user) !== 'administrator') {
        ob_end_clean();
        echo json_encode(array("status" => "error", "message" => "Unauthorized"));
        exit;
    }
}
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    ob_end_clean();
    echo json_encode(array("status" => "error", "message" => "CSRF token mismatch"));
    exit;
}
$query = "UPDATE svt_users SET password = MD5(?) WHERE id = ?;";
if ($stmt = $mysqli->prepare($query)) {
    $stmt->bind_param('si', $password, $id);
    $result = $stmt->execute();
    if ($result) {
        ob_end_clean();
        echo json_encode(array("status" => "ok"));
    } else {
        ob_end_clean();
        echo json_encode(array("status" => "error", "message" => "Failed to update password"));
    }
} else {
    ob_end_clean();
    echo json_encode(array("status" => "error", "message" => "Failed to prepare statement"));
}
?>