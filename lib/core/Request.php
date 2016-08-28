<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

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
            if ($app->site->q == '?') {
                $this->url = substr($url, strlen($app->site->subdir));
            } else {
                $this->url = $this->getArg('url', '');
            }
        } else {
            $this->url = '';
        }

        $this->isAdminPanel = Str::begin($this->url, '/admin/') || ($this->url == '/admin');
    }

    public function getHost($host)
    {
        if (Config::$host) {
            return config::$host;
        }

        $host = \strtolower(\trim($host));
        if ($host && \preg_match('/(www\.)?([\w\.\-]+)(:\d*)?/', $host, $m)) {
            return $m[2];
        }

        return 'localhost';
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

    public function getArg(string $name, $default = false)
    {
        $get = $this->getGet();
        return $get[$name] ?? $default;
    }

    public function getNextPage()
    {
        $url = $this->itemRoute['url'];
        return $this->getApp()->site->url . rtrim($url, '/') . '/page/' . ($this->page + 1) . '/';
    }

    public function getPrevpage()
    {
        $url = $this->itemRoute['url'];
        if ($this->page <= 2) {
            return url;
        }

        return $this->getApp()->site->url . rtrim($url, '/') . '/page/' . ($this->page - 1) . '/';
    }

    public function signedRef(): bool
    {
        if ($ref = $this->getArg('ref')) {
            $url = $this->url;
            $url = substr($url, 0, strpos($url, '&ref='));
            $app = $this->getApp();
            if ($ref == md5(Config::$secret . $app->site->url . $url . $app->options->solt)) {
                return true;
            }
        }

        return false;
    }

    public function isXXX(): bool
    {
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

    public function checkAttack(): bool
    {
        return $this->getApp()->options->xxxcheck && $this->isXXX();
    }
}
