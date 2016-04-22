<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\core;

class BaseCache
{
protected $items;

    abstract public function getString($filename);
    abstract public function setString($filename, $str);

    public function set($filename, $data) {
        $this->setString($filename, $this->serialize($data));
    }

    public function get($filename) {
        if ($s = $this->getString($filename)) {
            return $this->unserialize($s);
        }

        return false;
    }

    public function serialize($data) {
        return serialize($data);
    }

    public function unserialize(&$data) {
        return unserialize($data);
    }

public function savePhp($filename, $str) {
$this->setString($filename, $str);
}

public function includePhp($filename) {
if ($str = $this->getString($filename)) {
eval('?>'. $str);
}
}

    public function exists($filename) {
return array_key_exists($this->items);
}

}