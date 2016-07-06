<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\debug;

class Proxy
{
    public static $trace;
    public static $total;
    public static $stat;
    public static $counts;
    public $obj;
    public $data;
    public $items;
    public $templates;

    public function __construct($obj)
    {
        $this->obj = $obj;
        if (isset($obj->data)) {
            $this->data = & $obj->data;
        }

        if ($obj instanceof \litepubl\core\Items) {
            $this->items = & $obj->items;
        }

        if ($obj instanceof \litepubl\view\Base) {
            $this->templates = & $obj->templates;
        }
    }

    public function __isset($name)
    {
        return $this->obj->__isset($name);
    }

    public function __get($name)
    {
        $m = microtime(true);
        $r = $this->obj->$name;
        $this->addstat(" get $name", microtime(true) - $m);
        return $r;
    }

    public function __set($name, $value)
    {
        $m = microtime(true);
        $this->obj->$name = $value;
        $this->addstat(" set $name", microtime(true) - $m);
    }

    public function __call($name, $args)
    {
        //echo get_class($this->obj), " call $name<br>";
        $m = microtime(true);
        $r = call_user_func_array(
            array(
            $this->obj,
            $name
            ), $args
        );
        $this->addstat(" call $name", microtime(true) - $m);
        return $r;
    }

    public function addstat($s, $time)
    {
        $name = get_class($this->obj) . $s;
        //echo "$name<br>";
        static ::$trace[] = array(
            $name,
            $time
        );
        if (isset(static ::$total[$name])) {
            static ::$total[$name]+= $time;
            ++static ::$counts[$name];
        } else {
            static ::$total[$name] = $time;
            static ::$counts[$name] = 1;
        }
    }

    public static function showperformance()
    {
        echo "<pre>\n";
        arsort(static ::$total);
        $total = 0;
        foreach (static ::$total as $k => $v) {
            $total+= $v;
            $v = round($v * 1000, 4);
            //$v = round($v * 100000);
            echo static ::$counts[$k];
            echo " $k $v\n";
        }
        $total = $total * 1000;
        echo "total $total\n";
    }
}
