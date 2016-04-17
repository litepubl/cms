<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

//namespace litepubl;

function sqlDate($date = 0) {
    if (!$date) {
        $date = time();
    }

    return date('Y-m-d H:i:s', $date);
}

function sqlTime($date = 0) {
    if ($date) {
        return date('Y-m-d H:i:s', $date);
    }

    return '0000-00-00 00:00:00';
}

function dbQuote($s) {
    return litepubl::$db->quote($s);
}

function md5Rand() {
    return md5(mt_rand() . litepubl::$secret . microtime());
}

function md5Uniq() {
    return baseMd5(mt_rand() . litepubl::$secret . microtime());
}

function baseMd5($s) {
    return trim(base64_encode(md5($s, true)) , '=');
}

function strBegin($s, $begin) {
    return strncmp($s, $begin, strlen($begin)) == 0;
}

function strBegins() {
    $a = func_get_args();
    $s = array_shift($a);
    while ($begin = array_shift($a)) {
        if (strncmp($s, $begin, strlen($begin)) == 0) return true;
    }
    return false;
}

function strEnd($s, $end) {
    return $end == substr($s, 0 - strlen($end));
}

function trimUtf($s) {
    $utf = "\xEF\xBB\xBF";
    return strBegin($s, $utf) ? substr($s, strlen($utf)) : $s;
}

function arrayDelete(array & $a, $i) {
    array_splice($a, $i, 1);
}

function arrayDeleteValue(array & $a, $value) {
    $i = array_search($value, $a);
    if ($i !== false) {
        array_splice($a, $i, 1);
        return true;
    }

    return false;
}

function arrayClean(array & $items) {
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

function arrayInsert(array & $a, $item, $index) {
    array_splice($a, $index, 0, array(
        $item
    ));
}

function arrayMove(array & $a, $oldindex, $newindex) {
    //delete and insert
    if (($oldindex == $newindex) || !isset($a[$oldindex])) return false;
    $item = $a[$oldindex];
    array_splice($a, $oldindex, 1);
    array_splice($a, $newindex, 0, array(
        $item
    ));
}

function strToArray($s) {
    $a = explode("\n", trim($s));
    foreach ($a as $k => $v) {
        $a[$k] = trim($v);
    }

    return $a;
}

function toJson($a) {
    return json_encode($a, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
}

function jsonAttr($a) {
    return str_replace('"', '&quot;', tojson($a));
}

function toEnum($v, array $a) {
    $v = trim($v);
    return in_array($v, $a) ? $v : $a[0];
}

function getinstance($class) {
    return \litepubl\core\litepubl::$app->classes->getinstance($class);
}

function dumpstr($s) {
    echo "<pre>\n", htmlspecialchars($s) , "</pre>\n";
}

function dumpvar($v) {
    echo "<pre>\n";
    var_dump($v);
    echo "</pre>\n";
}