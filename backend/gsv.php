<?php
if (version_compare(PHP_VERSION, '8.0.0', '>=')) {
    if (file_exists('../gsv/index.php')) {
        include('../gsv/index.php');
    }
} else {
    die('This plugin requires PHP version 8.0 or higher. Your current PHP version is ' . PHP_VERSION . '. Please upgrade your PHP version to use this plugin.');
}