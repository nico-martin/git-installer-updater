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

    public static function unzip($zipFile, $dest)
    {
        $zip = new \ZipArchive;
        $res = $zip->open($zipFile);
        if ($res !== true) return false;
        $zip->extractTo($dest);
        $zip->close();
        unlink($zipFile);
        return true;
    }

    public static function Zip($source, $destination)
    {
        $zip = new \ZipArchive();

        if ($zip->open($destination, \ZIPARCHIVE::CREATE) !== TRUE) {
            exit("cannot open zip\n");
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($source),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($source));
                $zip->addFile($filePath, $relativePath);
            }
        }
        $zip->close();
    }
}
