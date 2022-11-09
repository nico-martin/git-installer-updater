<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

const ABSPATH = __DIR__;
$release = array_key_exists('release', $_GET) ? $_GET['release'] : null;

if (!$release) {
    die();
}

require_once './src/Helpers.php';
require_once './src/GitHub.php';

$github = new GitHub($release);

header("Content-Type: application/json; charset=utf-8");
echo json_encode($github->getHeaders());
