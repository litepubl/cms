<?php

namespace litepubl\core;
use litepubl\Config;

class Request
{
use AppTrait;

    public $host;
    public $isAdminPanel;
    public $page;
    public $url;
    public $uripath;

public function __construct($host, $url)
 {
        $this->host = $this->getHost($host);
        $this->page = 1;
        $this->uripath = [];

if ($url) {
$app = $this->getApp();
        if ( $app->site->q == '?') {
            $this->url = substr($url, strlen( $app->site->subdir));
        } else {
            $this->url = $_GET['url'];
        }
} else {
$this->url = '';
}

        $this->isAdminPanel = Str::begin($this->url, '/admin/') || ($this->url == '/admin');
}

    public  function getHost() {
        if (Config::$host) {
            return config::$host;
        }

$host = \strtolower(\trim($host));
        if ($host && \preg_match('/(www\.)?([\w\.\-]+)(:\d*)?/', $host, $m)) {
            return $m[2];
        }

        if (config::$dieOnInvalidHost) {
            die('cant resolve domain name');
        }

return false;
    }

public function getInput()
 {
return file_get_contents('php://input');
}

public function getGet()
{
return $_GET;
}

public function getPost()
{
return $_POST;
}

    public function getNextPage() {
        $url = $this->itemRoute['url'];
        return  $this->getApp()->site->url . rtrim($url, '/') . '/page/' . ($this->page + 1) . '/';
    }

    public function getPrevpage() {
        $url = $this->itemRoute['url'];
        if ($this->page <= 2) {
            return url;
        }

        return  $this->getApp()->site->url . rtrim($url, '/') . '/page/' . ($this->page - 1) . '/';
    }

    public function signedRef() {
        if (isset($_GET['ref'])) {
            $ref = $_GET['ref'];
            $url = $this->url;
            $url = substr($url, 0, strpos($url, '&ref='));
$app = $this->getApp();
            if ($ref == md5(Config::$secret .  $app->site->url . $url .  $app->options->solt)) {
 return true;
}
        }
}

    public function isXXX() {
if ($this->signedRef()) {
return false;
}

        $host = '';
        if (!empty($_SERVER['HTTP_REFERER'])) {
            $p = parse_url($_SERVER['HTTP_REFERER']);
            $host = $p['host'];
        }

        return $host != $this->host;
    }

    public function checkAttack() {
return $this->getApp()->options->xxxcheck && $this->isXXX();
    }

} 