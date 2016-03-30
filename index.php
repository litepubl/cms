<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;
class config {
public static $debug = false;
}

if (config::$debug) {
require (__DIR__ . '/lib/kernel.debug.php');
} else {
require (__DIR__ . '/lib/kernel.php');
}