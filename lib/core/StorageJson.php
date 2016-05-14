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

use litepubl\Config;

class StorageJson extends Storage
{

    public function getExt()
    {
        return '.json';
    }

    public function serialize(array $data)
    {
        return \json_encode($data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | (Config::$debug ? JSON_PRETTY_PRINT : 0));
    }

    public function unserialize($str)
    {
        return \json_decode($s, true);
    }

    public function before($str)
    {
        return $str;
    }

    public function after($str)
    {
        return $str;
    }

}

