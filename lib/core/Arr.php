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

class Arr
{
    public static function delete(array & $a, $i)
    {
        array_splice($a, $i, 1);
    }

    public static function deleteValue(array & $a, $value)
    {
        $i = array_search($value, $a);
        if ($i !== false) {
            array_splice($a, $i, 1);
            return true;
        }

        return false;
    }

    public static function clean(array & $items)
    {
        $items = array_unique($items);
        foreach ([
            0,
            false,
            null,
            ''
        ] as $v) {
            $i = array_search($v, $items);
            if (($i !== false) && ($items[$i] === $v)) {
                array_splice($items, $i, 1);
            }
        }
    }

    public static function insert(array & $a, $item, $index)
    {
        array_splice(
            $a,
            $index,
            0,
            [
            $item
            ]
        );
    }

    public static function move(array & $a, $oldindex, $newindex)
    {
        //delete and insert
        if (($oldindex == $newindex) || !isset($a[$oldindex])) {
            return false;
        }

        $item = $a[$oldindex];
        array_splice($a, $oldindex, 1);
        array_splice(
            $a,
            $newindex,
            0,
            [
            $item
            ]
        );
    }

    public static function toEnum($v, array $a)
    {
        $v = trim($v);
        return in_array($v, $a) ? $v : $a[0];
    }

    public static function reIndex(array & $a)
    {
        array_splice($a, count($a), 0, []);
    }

    public static function append(array & $a, int $index, $value): int
    {
        while (array_key_exists($index, $a)) {
            $index++;
        }

        $a[$index] = $value;
        ksort($a);
        return $index;
    }
}
