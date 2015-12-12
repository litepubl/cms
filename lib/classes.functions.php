<?php

if (!function_exists( 'spl_autoload_register' ) ) {
  function __autoload($class) {
    litepublisher::$classes->_autoload($class);
  }
}

function getinstance($class) {
  return litepublisher::$classes->getinstance($class);
}