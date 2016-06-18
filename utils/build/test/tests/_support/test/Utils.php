<?php

namespace test;

class Utils
{

    public static function getSingleFile($dir)
    {
        $result = false;
        $list = dir($dir);
        while ($filename = $list->read()) {
            if ($filename != '.' && $filename != '..') {
                $result = file_get_contents($dir . $filename);
                break;
            }
        }

        $list->close();
        return $result;
    }

    public static function getLine($s, $sub)
    {
        if ($i = strpos($s, $sub)) {
            $s = substr($s, 0, strpos($s, "\n", $i));
            return trim(substr($s, strrpos($s, "\n")));
        }

        return false;
    }
}
