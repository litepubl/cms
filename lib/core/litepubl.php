<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\core;
use litepubl\config;

class litepubl
 {
    public static $app;

    public static function init() {
        if (\version_compare(\PHP_VERSION, '5.4', '<')) {
            die('Lite Publisher requires PHP 5.4 or later. You are using PHP ' . \PHP_VERSION);
        }

        if (isset(config::$classes['app']) && class_exists(config::$classes['app'])) {
$className = config::$classes['app'];
static::$app = new className();
        } else {
            static ::$app = new App();
        }

static::$app->run();
    }

}

litepubl::init();