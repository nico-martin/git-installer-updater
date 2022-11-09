<?php
if (!defined('ABSPATH')) {
    die();
}

class Helpers
{


    public static function getTempDir($folder)
    {
        $dir = ABSPATH . '/tmp/';
        if (!is_dir($dir)) mkdir($dir);

        $dir = $dir . $folder . '/';
        if (!is_dir($dir)) mkdir($dir);

        return $dir;
    }

    public static function cleanUpTmp()
    {
        self::removeDir(ABSPATH . '/tmp/');
    }

    public static function removeDir($dir)
    {
        if (is_dir($dir)) {
            $objects = scandir($dir);
            foreach ($objects as $object) {
                if ($object != "." && $object != "..") {
                    if (filetype($dir . "/" . $object) == "dir") {
                        self::removeDir($dir . "/" . $object);
                    } else {
                        unlink($dir . "/" . $object);
                    }
                }
            }
            reset($objects);
            rmdir($dir);
        }
    }

    public static function trailingslashit($string)
    {
        return self::untrailingslashit($string) . '/';
    }

    public static function untrailingslashit($string)
    {
        return rtrim($string, '/\\');
    }

    public static function httpGetZip($url, $target, $authorization = null)
    {
        $fp = fopen($target, 'w+');
        $ch = curl_init();
        if ($authorization) curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: {$authorization}"));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FILE, $fp);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'php-request');
        $resp = curl_exec($ch);
        curl_close($ch);
        return $resp;
    }

    public static function httpGetRest($url, $authorization = null)
    {
        $ch = curl_init();
        if ($authorization) curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: {$authorization}"));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'php-request');
        $json = curl_exec($ch);
        curl_close($ch);

        if (!$json) {
            echo curl_error($ch);
        }

        return json_decode($json, true);
    }

    public static function httpGetPlain($url, $authorization = null)
    {
        $ch = curl_init();
        if ($authorization) curl_setopt($ch, CURLOPT_HTTPHEADER, array("Authorization: {$authorization}"));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_USERAGENT, 'php-request');
        $resp = curl_exec($ch);
        curl_close($ch);
        return str_replace('<?', '', $resp);
    }

    public static function parseHeader($fileData)
    {
        $fileData = str_replace("\r", "\n", $fileData);
        $allHeaders = [];

        foreach (
            [
                'plugin' => 'Plugin Name',
                'plugin-uri' => 'Plugin URI',
                'description' => 'Description',
                'tags' => 'Tags',
                'version' => 'Version',
                'requires-at-least' => 'Requires at least',
                'tested-up-to' => 'Tested up to',
                'requires-php' => 'Requires PHP',
                'author' => 'Author',
                'author-uri' => 'Author URI',
                'license' => 'License',
                'license-uri' => 'License URI',
                'text-domain' => 'Text Domain',
                'domain-path' => 'Domain Path',
                'network' => 'Network',
                'update-uri' => 'Update URI',
            ] as $field => $regex
        ) {
            if (preg_match('/^(?:[ \t]*<\?php)?[ \t\/*#@]*' . preg_quote($regex, '/') . ':(.*)$/mi', $fileData, $match) && $match[1]) {
                $allHeaders[$field] = trim(preg_replace('/\s*(?:\*\/|\?>).*/', '', $match[1]));
            } else {
                $allHeaders[$field] = '';
            }
        }

        return $allHeaders;
    }
}
