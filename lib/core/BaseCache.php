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

class BaseCache
{
    protected $items = [];
    protected $lifetime = 3600;

    public function getString($filename)
    {
    }

    public function setString($filename, $str)
    {
    }

    public function set($filename, $data)
    {
        $this->setString($filename, $this->serialize($data));
    }

    public function get($filename)
    {
        if ($s = $this->getString($filename)) {
            return $this->unserialize($s);
        }

        return false;
    }

    public function serialize($data)
    {
        return serialize($data);
    }

    public function unserialize(&$data)
    {
        return unserialize($data);
    }

    public function savePhp($filename, $str)
    {
        $this->setString($filename, $str);
    }

    public function includePhp($filename)
    {
        if ($str = $this->getString($filename)) {
            eval('?>' . $str);
            return true;
        }

        return false;
    }

    public function exists($filename)
    {
        return array_key_exists($this->items);
    }

    public function setLifetime($value)
    {
        $this->lifetime = $value;
    }

    public function clearUrl($url)
    {
        $this->delete(md5($url) . '.php');
    }

}

