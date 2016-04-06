<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class cachestorage_file {

    public function getdir() {
        return litepubl::$paths->cache;
    }

    public function set($filename, $data) {
        $this->setString($filename, serialize($data));
    }

    public function setString($filename, $str) {
        $fn = $this->getdir() . $filename;
        file_put_contents($fn, $str);
        @chmod($fn, 0666);
    }

    public function get($filename) {
        if ($s = $this->getString($filename)) {
            return unserialize($s);
        }

        return false;
    }

    public function getString($filename) {
        $fn = $this->getdir() . $filename;
        if (file_exists($fn)) {
            return file_get_contents($fn);
        }

        return false;
    }

    public function delete($filename) {
        $fn = $this->getdir() . $filename;
        if (file_exists($fn)) {
            unlink($fn);
        }
    }

    public function exists($filename) {
        return file_exists($this->getdir() . $filename);
    }

    public function clear() {
        $path = $this->getdir();
        if ($h = @opendir($path)) {
            while (FALSE !== ($filename = @readdir($h))) {
                if (($filename == '.') || ($filename == '..') || ($filename == '.svn')) continue;
                $file = $path . $filename;
                if (is_dir($file)) {
                    tfiler::delete($file . DIRECTORY_SEPARATOR, true, true);
                } else {
                    unlink($file);
                }
            }
            closedir($h);
        }
    }

} //class