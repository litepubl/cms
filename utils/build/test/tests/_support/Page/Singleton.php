<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace Page;

trait Singleton
{
    private static $instance;

    public static function i(\AcceptanceTester $I)
    {
        if (!static::$instance) {
            static::$instance = new static($I);
        }

        return static::$instance;
    }
}
