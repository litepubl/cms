<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\core;

Class Str
{
public static function sqlDate($date = 0) {
    if (!$date) {
        $date = time();
    }

    return date('Y-m-d H:i:s', $date);
}

public static function sqlTime($date = 0) {
    if ($date) {
        return date('Y-m-d H:i:s', $date);
    }

    return '0000-00-00 00:00:00';
}

public static function quote($s) {
    return litepubl::$db->quote($s);
}

public static function md5Rand() {
    return md5(mt_rand() . litepubl::$secret . microtime());
}

public static function md5Uniq() {
    return baseMd5(mt_rand() . litepubl::$secret . microtime());
}

public static function baseMd5($s) {
    return trim(base64_encode(md5($s, true)) , '=');
}

public static function begin($s, $begin) {
    return strncmp($s, $begin, strlen($begin)) == 0;
}

public static function begins() {
    $a = func_get_args();
    $s = array_shift($a);
    while ($begin = array_shift($a)) {
        if (strncmp($s, $begin, strlen($begin)) == 0) return true;
    }
    return false;
}

public static function end($s, $end) {
    return $end == substr($s, 0 - strlen($end));
}

public static function trimUtf($s) {
    $utf = "\xEF\xBB\xBF";
    return static::begin($s, $utf) ? substr($s, strlen($utf)) : $s;
}


public static function toArray($s) {
    $a = explode("\n", trim($s));
    foreach ($a as $k => $v) {
        $a[$k] = trim($v);
    }

    return $a;
}

    public static function toIntArray($s) {
        $result = array();
        foreach (explode(',', $s) as $value) {
            if ($v = (int)trim($value)) {
                $result[] = $v;
            }
        }

        return $result;
}

public static function toJson($a) {
    return json_encode($a, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
}

public static function jsonAttr($a) {
    return str_replace('"', '&quot;', tojson($a));
}

public static function getinstance($class) {
    return \litepubl\core\litepubl::$app->classes->getinstance($class);
}

public static function dumpstr($s) {
    echo "<pre>\n", htmlspecialchars($s) , "</pre>\n";
}

public static function dumpvar($v) {
    echo "<pre>\n";
    var_dump($v);
    echo "</pre>\n";
}

}