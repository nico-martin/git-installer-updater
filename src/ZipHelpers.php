<?php
if (!defined('ABSPATH')) {
    die();
}

class ZipHelpers
{
    public static function getTempDir($folder)
    {
        $dir = ABSPATH . '/zip-tmp/';
        if (!is_dir($dir)) mkdir($dir);

        $dir = $dir . $folder . '/';
        if (!is_dir($dir)) mkdir($dir);

        return $dir;
    }

    public static function cleanUpTmp()
    {
        Helpers::removeDir(ABSPATH . '/zip-tmp/');
    }

    public static function addDirToZip($path, $zip, $zip_path)
    {
        $handler = opendir($path);
        while (($filename = readdir($handler)) !== false) {
            if ($filename != "." && $filename != "..") {
                if (is_dir($path . "/" . $filename)) {
                    self::addDirToZip($path . "/" . $filename, $zip, $zip_path . $filename . "/");
                } else {
                    $zip->addFile($path . "/" . $filename, $zip_path . $filename);
                }
            }
        }
        closedir($handler);
    }
}
