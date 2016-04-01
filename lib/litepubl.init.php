<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl {
  if (\version_compare(\PHP_VERSION, '5.4', '<')) {
    die('Lite Publisher requires PHP 5.4 or later. You are using PHP ' . \PHP_VERSION);
  }

  if (isset(config::$classes['root']) && class_exists(config::$classes['root'])) {
    \call_user_func_array(config::$classes['root'], 'run', []);
  } else {
    litepubl::run();
  }

}//namespace