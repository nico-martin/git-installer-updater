<?php
require_once './src/MatomoTracker.php';

const ABSPATH = __DIR__;

$matomoTracker = new MatomoTracker((int)9, 'https://analytics.sayhello.agency/');
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
$releasesDir = Helpers::trailingslashit(ABSPATH) . 'releases_old/';
if (!is_dir($releasesDir)) mkdir($releasesDir);
$zipFileDir = $releasesDir . $release . '/';
if (!is_dir($zipFileDir)) mkdir($zipFileDir);
$zipFilePath = $zipFileDir . $zipFileName;

if (!file_exists($zipFilePath)) {
    $zip = new ZipArchive;
    $tempDirGet = ZipHelpers::getTempDir('tmp-updates-fetch-' . $release);
    $tempDir = ZipHelpers::getTempDir('tmp-updates-' . $release);
    Helpers::httpGetZip($zipUrl, $tempDirGet . $zipPluginFolder . '.zip');

    if ($zip->open($tempDirGet . $zipPluginFolder . '.zip') === true) {
        $zip->extractTo($tempDir);
        $zip->close();

        $folders = array_values(array_filter(scandir($tempDir), function ($e) {
            return $e !== '.' && $e !== '..';
        }));
        $rootFolder = $folders[0];
        rename("{$tempDir}/{$rootFolder}", "{$tempDir}/{$zipPluginFolder}");

        $zip->open($zipFilePath, ZipArchive::CREATE);
        ZipHelpers::addDirToZip($tempDir . $zipPluginFolder, $zip, "$zipPluginFolder/");
        $zip->close();
    }

    ZipHelpers::cleanUpTmp();

}
$matomoTracker->doTrackEvent('Downloaded', $release);

header("Content-type: application/zip");
header("Content-Disposition: attachment; filename=$zipFileName");
header("Content-length: " . filesize($zipFilePath));
header("Pragma: no-cache");
header("Expires: 0");
header("Accept-Ranges: bytes");
readfile("$zipFilePath");
