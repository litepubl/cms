<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */
function tthemeInstall($self) {
  $dir = litepublisher::$paths->data . 'themes';
  if (!is_dir($dir)) {
    mkdir($dir, 0777);
    chmod($dir, 0777);
  }
  $self->name = 'default';
  $self->parsetheme();
}