<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace litepubl\admin;

class DateFilter
{
    //only date without time
    public static $format = 'd.m.Y';
    public static $timeformat = 'H:i';

    public static function timestamp($date)
    {
        if (is_numeric($date)) {
            $date = (int)$date;
        } elseif ($date == '0000-00-00 00:00:00') {
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

    public static function getDate($name, $format = false)
    {
        if (empty($_POST[$name])) {
            return 0;
        }

        $date = trim($_POST[$name]);
        if (!$date) {
            return 0;
        }
        if (!$format) {
            $format = static ::$format;
        }

        $d = \DateTime::createFromFormat($format, $date);
        if ($d && $d->format($format) == $date) {
            $d->setTime(0, 0, 0);
            return $d->getTimestamp() + static ::gettime($name . '-time');
        }

        return 0;
    }

    public static function getTime($name)
    {
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
}
