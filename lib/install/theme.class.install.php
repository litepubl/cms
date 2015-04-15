<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tthemeInstall($self) {
  $dir = litepublisher::$paths->data . 'themes';
  if (!is_dir($dir)) {
    mkdir($dir, 0777);
    chmod($dir, 0777);
  }
  $self->name = 'default';
  $self->parsetheme();
}