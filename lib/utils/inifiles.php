<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class inifiles {
    public static $files = array();

    public static function cache($filename) {
        if (isset(static ::$files[$filename])) {
            return static ::$inifiles[$filename];
        }

        $datafile = tlocal::getcachedir() . 'cacheini.' . md5($filename);
        $ini = litepubl::$storage->loaddata($datafile);
        if (!is_array($ini)) {
            if (file_exists($filename)) {
                $ini = parse_ini_file($filename, true);
                litepubl::$storage->savedata($datafile, $ini);
            } else {
                $ini = array();
            }
        }

        if (!isset(static ::$files)) static ::$files = array();
        static ::$files[$filename] = $ini;
        return $ini;
    }

    public static function getresource($class, $filename) {
        $dir = litepubl::$classes->getresourcedir($class);
        return static ::cache($dir . $filename);
    }

}