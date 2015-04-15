<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tprivatefilesInstall($self) {
  $dir = litepublisher::$paths->files . 'private';
  @mkdir($dir, 0777);
  @chmod($dir, 0777);
  $dir .= DIRECTORY_SEPARATOR;
  $file = $dir . 'index.htm';
  file_put_contents($file, ' ');
  @chmod($file, 0666);
  
  $file = $dir . '.htaccess';
  file_put_contents($file, 'Deny from all');
  @chmod($file, 0666);
}