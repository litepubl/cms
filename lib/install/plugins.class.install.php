<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tpluginsInstall($self) {
  @mkdir(litepublisher::$paths->data . 'plugins', 0777);
  @chmod(litepublisher::$paths->data . 'plugins', 0777);
}