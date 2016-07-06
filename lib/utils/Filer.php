<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\utils;

use litepubl\core\litepubl;

class Filer
{

    public static function delete($path, $subdirs, $rmdir = false)
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if ($h = @opendir($path)) {
            while (false !== ($filename = readdir($h))) {
                if (($filename == '.') || ($filename == '..') || ($filename == '.svn')) {
                    continue;
                }

                $file = $path . $filename;
                if (is_dir($file)) {
                    if ($subdirs) {
                        static ::delete($file . DIRECTORY_SEPARATOR, $subdirs, $rmdir);
                    }
                } else {
                    static ::_delete($file);
                }
            }
            closedir($h);
        }
        if ($rmdir && is_dir($path)) {
            rmdir($path);
        }
    }

    public static function deleteMask($mask)
    {
        if ($list = glob($mask)) {
            foreach ($list as $filename) {
                static ::_delete($filename);
            }
        }
    }

    public static function getFiles($path)
    {
        $result = array();
        if ($h = opendir($path)) {
            while (false !== ($filename = readdir($h))) {
                if (($filename == '.') || ($filename == '..') || ($filename == '.svn')) {
                    continue;
                }

                if (!is_dir($path . $filename)) {
                    $result[] = $filename;
                }
            }
            closedir($h);
        }
        return $result;
    }

    public static function getDir($dir)
    {
        $result = array();
        if ($fp = opendir($dir)) {
            while (false !== ($file = readdir($fp))) {
                if (is_dir($dir . $file) && ($file != '.') && ($file != '..') && ($file != '.svn')) {
                    $result[] = $file;
                }
            }
        }
        return $result;
    }

    public static function forceDir($dir)
    {
        $dir = rtrim(str_replace('\\', '/', $dir), '/');
        if (is_dir($dir)) {
            return true;
        }

        $up = rtrim(dirname($dir), '/');
        if (($up != '') || ($up != '.')) {
            static ::forcedir($up);
        }
        if (!is_dir($dir)) {
            mkdir($dir, 0777);
        }
        chmod($dir, 0777);
        return is_dir($dir);
    }

    public static function append($filename, $s)
    {
        $dir = dirname($filename);
        if (!is_dir($dir)) {
            mkdir($dir, 0777);
            @chmod($dir, 0777);
        }

        if ($fp = fopen($filename, 'a+')) {
            fwrite($fp, $s);
            fclose($fp);
            @chmod($filename, 0666);
        }
    }

    public static function getFiletimeOffset()
    {
        $filename = litepubl::$app->paths->data . md5(microtime()) . '.tmp';
        $t = time();
        touch($filename, $t, $t);
        clearstatcache();
        $t2 = filemtime($filename);
        unlink($filename);
        return $t2 - $t;
    }

    public static function _delete($filename)
    {
        if (\file_exists($filename) && !\unlink($filename)) {
            \chmod($filename, 0666);
            \unlink($filename);
        }
    }
}
