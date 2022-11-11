<?php
require_once './src/MatomoTracker.php';

const ABSPATH = __DIR__;

$matomoTracker = new MatomoTracker((int)8, 'https://analytics.sayhello.agency/');
$matomoTracker->disableCookieSupport();
$matomoTracker->doTrackPageView('get plugin infos');

$release = array_key_exists('release', $_GET) ? $_GET['release'] : null;

if (!$release) {
    die();
}

require_once './src/Helpers.php';
require_once './src/GitHub.php';

$github = new GitHub($release);

header("Content-Type: application/json; charset=utf-8");
echo json_encode($github->getUpdateInfos());
