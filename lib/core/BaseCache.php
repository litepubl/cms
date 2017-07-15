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

abstract class BaseCache
{
    use Callbacks;

    protected $items = [];
    protected $lifetime = 3600;

    public function onClear(callable $callback)
    {
        $this->addCallback('onclear', $callback);
    }

    abstract public function getString(string $filename): string;
    abstract public function setString(string $filename, string $str);

    public function set(string $filename, $data)
    {
        $this->setString($filename, $this->serialize($data));
    }

    public function get(string $filename)
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

    public function savePhp(string $filename, string $str)
    {
        $this->setString($filename, $str);
    }

    public function includePhp(string $filename)
    {
        if ($str = $this->getString($filename)) {
            eval('?>' . $str);
            return true;
        }

        return false;
    }

    public function exists(string $filename)
    {
        return array_key_exists($this->items);
    }

    public function setLifetime(int $value)
    {
        $this->lifetime = $value;
    }

    public function clear()
    {
        $this->items = [];
        $this->triggerCallback('onClear');
    }

    public function clearUrl(string $url)
    {
        $this->delete(md5($url) . '.php');
    }
}
