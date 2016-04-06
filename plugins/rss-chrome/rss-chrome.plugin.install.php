<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function rsschromeInstall($self) {
    $name = basename(dirname(__file__));
    $js = tjsmerger::i();
    $js->lock();
    $section = 'default';
    $js->add($section, "/plugins/$name/resource/" . litepubl::$options->language . ".rss-chrome.min.js");
    $js->add($section, "/plugins/$name/resource/rss-chrome.min.js");
    $js->unlock();
}

function rsschromeUninstall($self) {
    $name = basename(dirname(__file__));
    $js = tjsmerger::i();
    $js->lock();
    $section = 'default';
    $js->deletefile($section, "/plugins/$name/resource/" . litepubl::$options->language . ".rss-chrome.min.js");
    $js->deletefile($section, "/plugins/$name/resource/rss-chrome.min.js");
    $js->unlock();
}