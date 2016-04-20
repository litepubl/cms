<?php
namespace litepubl;

define('litepubl_mode', 'config');
require (__DIR__ . '/index.php');
Config::$debug = true;
//Config::$classes['storage'] = 'litepubl\storageinc';
Config::$beforeRequest  = function() {
include (__DIR__ . '/temp/zdebug.php');
};

require (__DIR__ . '/lib/debug/kernel.php');
//require (__DIR__ . '/lib/kernel.php');