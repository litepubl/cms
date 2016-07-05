<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */

namespace litepubl\core;

use litepubl\config;

class litepubl
{
    public static $app;

    public static function init()
    {
        if (\version_compare(\PHP_VERSION, '7.0', '<')) {
            die('Lite Publisher requires PHP 7.0 or later. You are using PHP ' . \PHP_VERSION);
        }

        if (isset(config::$classes['app']) && class_exists(config::$classes['app'])) {
            $className = config::$classes['app'];
            static ::$app = new $className();
        } else {
            static ::$app = new App();
        }

        static ::$app->run();
    }
}

litepubl::init();
