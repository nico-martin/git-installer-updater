<?php
if (!defined('ABSPATH')) {
    die();
}

class GitHub
{
    public $release = '';
    private static $restBase = 'https://api.github.com/repos/SayHelloGmbH/git-installer';

    public function __construct($release)
    {
        $this->release = ($release === 'latest') ? $this->getLatestReleaseTagName() : $release;
    }

    public function getLatestReleaseTagName()
    {
        $cacheId = 'latest-release-name';
        $cached = Helpers::getCache($cacheId);
        if ($cached) return $cached;
        $content = Helpers::httpGetRest('https://api.github.com/repos/SayHelloGmbH/git-installer/releases/latest');
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
        $url = 'https://raw.githubusercontent.com/SayHelloGmbH/git-installer/' . $this->release . '/git-installer.php';
        $content = Helpers::httpGetPlain($url);
        $headers = Helpers::parseHeader($content);
        Helpers::setCache($this->release, $headers);
        return $headers;
    }
}

