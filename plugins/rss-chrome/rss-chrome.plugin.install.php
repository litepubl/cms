<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\Js;

function rsschromeInstall($self) {
    $name = basename(dirname(__file__));
    $js = Js::i();
    $js->lock();
    $section = 'default';
    $js->add($section, "/plugins/$name/resource/" .  $self->getApp()->options->language . ".rss-chrome.min.js");
    $js->add($section, "/plugins/$name/resource/rss-chrome.min.js");
    $js->unlock();
}

function rsschromeUninstall($self) {
    $name = basename(dirname(__file__));
    $js = Js::i();
    $js->lock();
    $section = 'default';
    $js->deletefile($section, "/plugins/$name/resource/" .  $self->getApp()->options->language . ".rss-chrome.min.js");
    $js->deletefile($section, "/plugins/$name/resource/rss-chrome.min.js");
    $js->unlock();
}