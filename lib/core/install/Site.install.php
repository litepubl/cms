<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */

namespace litepubl\core;

function SiteInstall($self)
{
    $site = $self;
    $site->lock();
    $site->subdir = getrequestdir();
    $site->fixedurl = true;
    $site->url = 'http://' . strtolower($_SERVER['HTTP_HOST']) . $site->subdir;
    $site->files = $site->data['url'];
    $site->q = '?';

    $site->home = '/';
    $site->keywords = "blog";
    $site->jquery_version = '1.12.4';
    $site->author = 'Admin';

    $site->mapoptions = array(
        'version' => 'version',
        'language' => 'language',
    );
    $site->unlock();
}

function getrequestdir()
{
    if (isset($_GET) && (count($_GET) > 0) && ($i = strpos($_SERVER['REQUEST_URI'], '?'))) {
        $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, $i);
    }

    if (preg_match('/index\.php$/', $_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, strlen($_SERVER['REQUEST_URI']) - strlen('index.php'));
    }

    if (preg_match('/install\.php$/', $_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = substr($_SERVER['REQUEST_URI'], 0, strlen($_SERVER['REQUEST_URI']) - strlen('install.php'));
    }

    return rtrim($_SERVER['REQUEST_URI'], '/');
}
