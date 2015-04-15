<?php
/**
* Litepublisher shop script
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Comercial license. IMPORTANT: THE SOFTWARE IS LICENSED, NOT SOLD. Please read the following License Agreement (plugins/shop/license.txt)
* You can use one license on one website
**/

function rsschromeInstall($self) {
  $name = basename(dirname(__file__));
  $js = tjsmerger::i();
  $js->lock();
  $section = 'default';
  $js->add($section, "/plugins/$name/resource/" . litepublisher::$options->language . ".rss-chrome.min.js");
  $js->add($section, "/plugins/$name/resource/rss-chrome.min.js");
  $js->unlock();
}

function rsschromeUninstall($self) {
  $name = basename(dirname(__file__));
  $js = tjsmerger::i();
  $js->lock();
  $section = 'default';
  $js->deletefile($section, "/plugins/$name/resource/" . litepublisher::$options->language . ".rss-chrome.min.js");
  $js->deletefile($section, "/plugins/$name/resource/rss-chrome.min.js");
  $js->unlock();
}