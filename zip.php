<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

const ABSPATH = __DIR__;
$release = array_key_exists('release', $_GET) ? $_GET['release'] : null;

if (!$release) {
    die();
}

require_once './src/Helpers.php';
require_once './src/ZipHelpers.php';
require_once './src/GitHub.php';

$github = new GitHub($release);
$zipUrl = $github->getReleaseInfo('zipball_url');

$zipPluginFolder = 'git-installer-' . $release;
$zipFileName = $zipPluginFolder . '.zip';
$zipFileDir = Helpers::trailingslashit(ABSPATH) . 'releases/' . $zipFileName;

if (!file_exists(Helpers::trailingslashit(ABSPATH) . 'releases/' . $zipFileName)) {

    if (!is_dir(Helpers::trailingslashit(ABSPATH) . 'releases/')) mkdir(Helpers::trailingslashit(ABSPATH) . 'releases/');

    /**
     * fetch Zip
     */

    $tempDirGet = Helpers::getTempDir('tmp-updates-fetch');
    Helpers::httpGetZip($zipUrl, $tempDirGet . $zipPluginFolder . '.zip');
    $unzip = ZipHelpers::unzip($tempDirGet . $zipPluginFolder . '.zip', $tempDirGet);
    $subDirs = glob($tempDirGet . '/*', GLOB_ONLYDIR);
    $packageDir = $subDirs[0];

    /**
     * move package files
     */

    $tempDirZip = Helpers::getTempDir('tmp-updates-package');
    $target = $tempDirZip . $zipPluginFolder . '/';
    if (!is_dir($target)) mkdir($target);

    //if (is_dir($target)) Helpers::removeDir($target);

    //if (!is_dir($target . 'public/')) mkdir($target . 'public/');
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
    Helpers::cleanUpTmp();
}

header("Content-type: application/zip");
header("Content-Disposition: attachment; filename=$zipFileName");
header("Content-length: " . filesize($zipFileDir));
header("Pragma: no-cache");
header("Expires: 0");
header("Accept-Ranges: bytes");
readfile("$zipFileDir");

