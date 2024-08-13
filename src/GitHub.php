<?php
if (!defined('ABSPATH')) {
    die();
}

class GitHub
{
    public $release = '';
    private static $restBase = 'https://api.github.com/repos/nico-martin/git-installer';

    public function __construct($release)
    {
        $this->release = ($release === 'latest') ? $this->getLatestReleaseTagName() : $release;
    }

    public function getLatestReleaseTagName()
    {
        $cacheId = 'latest-release-name';
        $cached = Helpers::getCache($cacheId);
        if ($cached) return $cached;
        $content = Helpers::httpGetRest('https://api.github.com/repos/nico-martin/git-installer/releases/latest');
        $name = $content['tag_name'];
        Helpers::setCache($cacheId, $name);
        return $name;
    }

    public function getReleaseInfo($value = '')
    {
        $infos = $this->getRest('releases/tags/' . $this->release);
        if ($value === '') {
            return $infos;
        } elseif (array_key_exists($value, $infos)) {
            return $infos[$value];
        }
        return null;
    }

    public function getRest($path = '')
    {
        $append = $path !== '' ? '/' . Helpers::untrailingslashit($path) : '';
        return Helpers::httpGetRest(Helpers::untrailingslashit(self::$restBase) . $append);
    }

    public function getHeaders()
    {
        $cached = Helpers::getCache($this->release);
        if ($cached) return $cached;
        $url = 'https://raw.githubusercontent.com/nico-martin/git-installer/' . $this->release . '/git-installer.php';
        $content = Helpers::httpGetPlain($url);
        $headers = Helpers::parseHeader($content);

        Helpers::setCache($this->release, $headers);
        return $headers;
    }

    public function getUpdateInfos()
    {
        $headers = $this->getHeaders();
        $host = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";

        return [
            'name' => $headers['plugin'],
            'version' => $headers['version'],
            'download_url' => $host . "/zip.php?release={$this->release}",
            'homepage' => $headers['plugin-uri'],
            'requires' => $headers['requires-at-least'],
            'tested' => $headers['tested-up-to'],
            'author' => 'Nico Martin',
            'author_homepage' => 'https://nico.dev',
            'sections' => [
                'description' => $headers['description'],
                //'installation' => '(Recommended) Installation instructions.',
                //'changelog' => '(Recommended) Changelog. <p>This section will be displayed by default when the user clicks "View version x.y.z details".</p>',
                //'custom_section' => 'This is a custom section labeled "Custom Section".'
            ],
            'icons' => [
                '1x' => $host . '/assets/icon-128.jpg',
                '2x' => $host . '/assets/icon-256.jpg',
            ],
            'banners' => [
                'low' => $host . '/assets/banner-772x250.jpg',
                'high' => $host . '/assets/banner-1544x500.jpg',
            ]
        ];
    }
}

