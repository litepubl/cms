<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tpluginsInstall($self) {
  @mkdir(litepublisher::$paths->data . 'plugins', 0777);
  @chmod(litepublisher::$paths->data . 'plugins', 0777);
}