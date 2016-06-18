<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\core;

trait AppTrait
{

    public static function getAppInstance(): App
    {
        return litepubl::$app;
    }

    public function getApp(): App
    {
        return static ::getAppInstance();
    }
}
