<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\rsschrome;

use litepubl\view\Js;

function PluginInstall($self)
{
    $name = basename(dirname(__file__));
    $js = Js::i();
    $js->lock();
    $section = 'default';
    $js->add($section, "/plugins/$name/resource/" . $self->getApp()->options->language . ".rss-chrome.min.js");
    $js->add($section, "/plugins/$name/resource/rss-chrome.min.js");
    $js->unlock();
}

function PluginUninstall($self)
{
    $name = basename(dirname(__file__));
    $js = Js::i();
    $js->lock();
    $section = 'default';
    $js->deletefile($section, "/plugins/$name/resource/" . $self->getApp()->options->language . ".rss-chrome.min.js");
    $js->deletefile($section, "/plugins/$name/resource/rss-chrome.min.js");
    $js->unlock();
}
