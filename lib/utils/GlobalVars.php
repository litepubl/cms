<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\utils;

class GlobalVars
{
    private $items = [];

public function __construct()
{
$this->push($this->get());
}

public function __destruct()
{
$this->set($this->pop());
}

    public function push(array $item)
    {
        $this->items[] = $item;
}

    public function pop()
    {
return array_pop($this->items);
}

public function get(): array
{
return [
        '_POST' => $_POST,
        '_GET' => $_GET,
        '_COOKIE' => $_COOKIE,
        '_SERVER' => $_SERVER,
        '_FILES' => $_FILES,
        '_GLOBALS' => $_GLOBALS,
        ];
    }

    public function set(array $a)
    {
        $_GLOBALS = $a['_GLOBALS'];
        $_POST = $a['_POST'];
        $_GET = $a['_GET'];
        $_COOKIE = $a['_COOKIE'];
        $_SERVER = $a['_SERVER'];
        $_FILES = $A['_FILES'];
    }
}
