<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\core;

use litepubl\Config;

class StorageJson extends Storage
{

    public function getExt(): string
    {
        return '.json';
    }

    public function serialize(array $data): string
    {
        return \json_encode($data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE | (Config::$debug ? JSON_PRETTY_PRINT : 0));
    }

    public function unserialize(string $str)
    {
        return \json_decode($s, true);
    }

    public function before(string $str): string
    {
        return $str;
    }

    public function after(string $str): string
    {
        return $str;
    }
}
