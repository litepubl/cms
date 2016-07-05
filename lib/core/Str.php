<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 */

namespace litepubl\core;

use litepubl\Config;

class Str
{
    public $value;

    public function __construct(string $s = '')
    {
        $this->value = (string)$s;
    }

    public function __tostring()
    {
        return $this->value;
    }

    //static methods
    public static function sqlDate($date = 0): string
    {
        if (!$date) {
            $date = time();
        }

        return date('Y-m-d H:i:s', $date);
    }

    public static function sqlTime($date = 0): string
    {
        if ($date) {
            return date('Y-m-d H:i:s', $date);
        }

        return '0000-00-00 00:00:00';
    }

    public static function quote(string $s): string
    {
        return litepubl::$app->db->quote($s);
    }

    public static function md5Rand(): string
    {
        return md5(mt_rand() . Config::$secret . microtime());
    }

    public static function md5Uniq(): string
    {
        return static ::baseMd5(mt_rand() . Config::$secret . microtime());
    }

    public static function baseMd5(string $s): string
    {
        return trim(base64_encode(md5($s, true)), '=');
    }

    public static function begin(string $s, string $begin): bool
    {
        return strncmp($s, $begin, strlen($begin)) == 0;
    }

    public static function begins()
    {
        $a = func_get_args();
        $s = array_shift($a);
        while ($begin = array_shift($a)) {
            if (strncmp($s, $begin, strlen($begin)) == 0) {
                return true;
            }
        }
        return false;
    }

    public static function end(string $s, string $end): bool
    {
        return $end == substr($s, 0 - strlen($end));
    }

    public static function trimUtf(string $s): string
    {
        $utf = "\xEF\xBB\xBF";
        return static ::begin($s, $utf) ? substr($s, strlen($utf)) : $s;
    }

    public static function toArray(string $s): array
    {
        $a = explode("\n", trim($s));
        foreach ($a as $k => $v) {
            $a[$k] = trim($v);
        }

        return $a;
    }

    public static function toIntArray(string $s): array
    {
        $result = array();
        foreach (explode(',', $s) as $value) {
            if ($v = (int)trim($value)) {
                $result[] = $v;
            }
        }

        return $result;
    }

    public static function toJson($a): string
    {
        return json_encode($a, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);
    }

    public static function jsonAttr($a): string
    {
        return str_replace('"', '&quot;', Str::toJson($a));
    }

    public static function log(string $s)
    {
        echo "<pre>\n", htmlspecialchars($s) , "</pre>\n";
    }

    public static function dump($v)
    {
        echo "<pre>\n";
        var_dump($v);
        echo "</pre>\n";
    }
}
