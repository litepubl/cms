<?php
namespace litepubl;

define('litepubl_mode', 'config');
require (__DIR__ . '/index.php');
config::$debug = true;
//config::$classes['storage'] = 'litepubl\storageinc';
config::$beforeRequest  = function() {
include (__DIR__ . '/temp/zdebug.php');
};

require (__DIR__ . '/lib/kernel.debug.php');
//require (__DIR__ . '/lib/kernel.php');