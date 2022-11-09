<?php
if (!defined('ABSPATH')) {
    die();
}

class ZipHelpers
{
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
