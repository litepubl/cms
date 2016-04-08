<?php
namespace litepubl;

define('litepubl_mode', 'config');
require (__DIR__ . '/index.php');
config::$debug = true;
config::$ignoreRequest = true;
//config::$classes['storage'] = 'litepubl\storageinc';
config::$beforeRequest  = function() {
//
};

echo "<pre>\n";
require_once (__DIR__ . '/lib/kernel.debug.php');
require_once (__DIR__ . '/lib/updater.class.php');
require_once (__DIR__ . '/lib/local.class.php');
require_once (__DIR__ . '/lib/filer.class.php');
require_once (__DIR__ . '/lib/jsmerger.class.php');
require_once (__DIR__ . '/lib/localmerger.class.php');

var_dump(tupdater::i()->update());
