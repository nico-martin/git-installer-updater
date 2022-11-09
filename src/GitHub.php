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
        $this->release = $release;
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
        $url = 'https://raw.githubusercontent.com/SayHelloGmbH/git-installer/' . $this->release . '/git-installer.php';
        $content = Helpers::httpGetPlain($url);
        return Helpers::parseHeader($content);
    }
}

