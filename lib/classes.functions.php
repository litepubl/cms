<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */
if (!function_exists('spl_autoload_register')) {
  function __autoload($class) {
    litepublisher::$classes->_autoload($class);
  }
}

function getinstance($class) {
  return litepublisher::$classes->getinstance($class);
}