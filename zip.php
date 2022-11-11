<?php
require_once './src/MatomoTracker.php';

const ABSPATH = __DIR__;

$matomoTracker = new MatomoTracker((int)8, 'https://analytics.sayhello.agency/');
$matomoTracker->disableCookieSupport();
$matomoTracker->doTrackPageView('get plugin zip');

$release = array_key_exists('release', $_GET) ? $_GET['release'] : null;

if (!$release) {
    die();
}

require_once './src/Helpers.php';
require_once './src/ZipHelpers.php';
require_once './src/GitHub.php';

$github = new GitHub($release);
$release = $github->release;

$zipUrl = $github->getReleaseInfo('zipball_url');

$zipPluginFolder = 'git-installer';
$zipFileName = $zipPluginFolder . '.zip';
$zipFileDir = Helpers::trailingslashit(ABSPATH) . 'releases/' . $release . '/' . $zipFileName;

if (!file_exists($zipFileDir)) {

    if (!is_dir(Helpers::trailingslashit(ABSPATH) . 'releases/' . $release . '/')) mkdir(Helpers::trailingslashit(ABSPATH) . 'releases/' . $release . '/');

    /**
     * fetch Zip
     */

    $tempDirGet = ZipHelpers::getTempDir('tmp-updates-fetch-' . $release);
    Helpers::httpGetZip($zipUrl, $tempDirGet . $zipPluginFolder . '.zip');
    $unzip = ZipHelpers::unzip($tempDirGet . $zipPluginFolder . '.zip', $tempDirGet);
    $subDirs = glob($tempDirGet . '/*', GLOB_ONLYDIR);
    $packageDir = $subDirs[0];

    /**
     * move package files
     */

    $tempDirZip = ZipHelpers::getTempDir('tmp-updates-package-' . $release);
    $target = $tempDirZip . $zipPluginFolder . '/';
    if (!is_dir($target)) mkdir($target);

    array_map(function ($e) use ($packageDir, $target) {
        rename(
            Helpers::trailingslashit($packageDir) . $e,
            $target . $e
        );
    }, ['public/', 'src/', 'git-installer.php', 'LICENSE']);

    if (!is_dir($target . 'assets/')) mkdir($target . 'assets/');
    rename(
        Helpers::trailingslashit($packageDir) . 'assets/dist/',
        $target . 'assets/dist/'
    );

    /**
     * create new Zip
     */

    ZipHelpers::Zip($tempDirZip, $zipFileDir);
    ZipHelpers::cleanUpTmp();
}

$matomoTracker->doTrackEvent('Downloaded', $release);

header("Content-type: application/zip");
header("Content-Disposition: attachment; filename=$zipFileName");
header("Content-length: " . filesize($zipFileDir));
header("Pragma: no-cache");
header("Expires: 0");
header("Accept-Ranges: bytes");
readfile("$zipFileDir");

