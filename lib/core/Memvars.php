<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\core;

class Memvars
{
    use AppTrait;

    public static $vars;

    public static function i()
    {
        if (!static ::$vars) {
            if (static ::getAppInstance()->memcache) {
                static ::$vars = new MemvarMemcache();
            } else {
                static ::$vars = new MemvarMysql();
            }
        }

        return static ::$vars;
    }
}
