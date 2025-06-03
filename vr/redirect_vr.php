<?php
session_start();
$_SESSION['redirect_vr'] = true;
$url = $_GET['url'];
if(isset($_GET['n'])) {
    $_SESSION['new_window'] = true;
} else {
    $_SESSION['new_window'] = false;
}
session_write_close();
header("Location: $url");
die();