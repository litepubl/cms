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
        if ( $this->getApp()->site->q == '?') {
            $this->url = substr($url, strlen( $this->getApp()->site->subdir));
        } else {
            $this->url = $_GET['url'];
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

//default by options
return $this->getApp()->site->domain;
    }

}