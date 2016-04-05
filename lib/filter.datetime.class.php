<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

// namespace litepubl\admin;
class datefilter {
    //only date without time
    public static $format = 'd.m.Y';
    public static $timeformat = 'H:i';

    public static function timestamp($date) {
        if (is_numeric($date)) {
            $date = (int)$date;
        } else if ($date == '0000-00-00 00:00:00') {
            $date = 0;
        } elseif ($date == '0000-00-00') {
            $date = 0;
        } elseif ($date = trim($date)) {
            $date = strtotime($date);
        } else {
            $date = 0;
        }

        return $date;
    }

    public static function getdate($name, $format = false) {
        if (empty($_POST[$name])) return 0;
        $date = trim($_POST[$name]);
        if (!$date) return 0;

        if (version_compare(PHP_VERSION, '5.3', '>=')) {
            if (!$format) $format = static ::$format;
            $d = DateTime::createFromFormat($format, $date);
            if ($d && $d->format($format) == $date) {
                $d->setTime(0, 0, 0);
                return $d->getTimestamp() + static ::gettime($name . '-time');
            }
        } else {
            if (@sscanf($date, '%d.%d.%d', $d, $m, $y)) {
                return mktime(0, 0, 0, $m, $d, $y) + static ::gettime($name . '-time');
            }
        }

        return 0;
    }

    public static function gettime($name) {
        $result = 0;
        if (!empty($_POST[$name]) && ($time = trim($_POST[$name]))) {
            if (preg_match('/^([01]?[0-9]|2[0-3]):([0-5][0-9])(:([0-5][0-9]))?$/', $time, $m)) {
                $result = intval($m[1]) * 3600 + intval($m[2]) * 60;
                if (isset($m[4])) {
                    $result+= (int)$m[4];
                }
            }
        }

        return $result;
    }

} //class