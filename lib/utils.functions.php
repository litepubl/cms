<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

//namespace litepubl;

function sqldate($date = 0) {
    if (!$date) {
$date = time();
}

    return date('Y-m-d H:i:s', $date);
}

function sqltime($date = 0) {
    if ($date) {
    return date('Y-m-d H:i:s', $date);
}

return '0000-00-00 00:00:00';
}

function dbquote($s) {
    return litepubl::$db->quote($s);
}

function md5rand() {
    return md5(mt_rand() . litepubl::$secret . microtime());
}

function md5uniq() {
    return basemd5(mt_rand() . litepubl::$secret . microtime());
}

function basemd5($s) {
    return trim(base64_encode(md5($s, true)) , '=');
}

function strbegin($s, $begin) {
    return strncmp($s, $begin, strlen($begin)) == 0;
}

function strbegins() {
    $a = func_get_args();
    $s = array_shift($a);
    while ($begin = array_shift($a)) {
        if (strncmp($s, $begin, strlen($begin)) == 0) return true;
    }
    return false;
}

function strend($s, $end) {
    return $end == substr($s, 0 - strlen($end));
}

function strip_utf($s) {
    $utf = "\xEF\xBB\xBF";
    return strbegin($s, $utf) ? substr($s, strlen($utf)) : $s;
}

function array_delete(array & $a, $i) {
    array_splice($a, $i, 1);
}

function array_delete_value(array & $a, $value) {
    $i = array_search($value, $a);
    if ($i !== false) {
        array_splice($a, $i, 1);
        return true;
    }

    return false;
}

function array_clean(array & $items) {
    $items = array_unique($items);
    foreach (array(
        0,
        false,
        null,
        ''
    ) as $v) {
        $i = array_search($v, $items);
        if (($i !== false) && ($items[$i] === $v)) {
            array_splice($items, $i, 1);
        }
    }
}

function array_insert(array & $a, $item, $index) {
    array_splice($a, $index, 0, array(
        $item
    ));
}

function array_move(array & $a, $oldindex, $newindex) {
    //delete and insert
    if (($oldindex == $newindex) || !isset($a[$oldindex])) return false;
    $item = $a[$oldindex];
    array_splice($a, $oldindex, 1);
    array_splice($a, $newindex, 0, array(
        $item
    ));
}

function strtoarray($s) {
    $a = explode("\n", trim($s));
    foreach ($a as $k => $v) {
$a[$k] = trim($v);
}

    return $a;
}

function tojson($a) {
        return json_encode($a, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
}

function jsonattr($a) {
    return str_replace('"', '&quot;', tojson($a));
}

function toenum($v, array $a) {
    $v = trim($v);
    return in_array($v, $a) ? $v : $a[0];
}

function getinstance($class) {
    return litepubl\litepubl::$classes->getinstance($class);
}

function dumpstr($s) {
    echo "<pre>\n", htmlspecialchars($s) , "</pre>\n";
}

function dumpvar($v) {
    echo "<pre>\n";
    var_dump($v);
    echo "</pre>\n";
}