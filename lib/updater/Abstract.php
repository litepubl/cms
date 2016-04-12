<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class tremotefiler {
    protected $host;
    protected $login;
    public $port;
    protected $password;
    protected $handle;
    protected $timeout;
    public $chmod_file;
    public $chmod_dir;
    public $connected;

    public static function i() {
        return litepubl::$classes->getinstance(get_called_class());
    }

    public function __construct() {
        $this->port = 21;
        $this->handle = null;
        $this->timeout = 30;
        $this->chmod_file = 0644;
        $this->chmod_dir = 0755;
        $this->connected = false;
    }

    public function close() {
    }

    public function connect($host, $login, $password) {
        if (empty($host) || empty($login) || empty($password)) return false;
        $this->host = $host;
        $this->login = $login;
        $this->password = $password;
        return true;
    }

    public function getmode($mode) {
        static $modes;
        if (!$mode) return $this->chmod_file;
        if (!isset($modes)) {
            foreach (array(
                0644,
                0666,
                0640,
                0660,
                0777,
                0755,
                0770,
                0750
            ) as $value) {
                $modes[$value] = $value;
                $modes[octdec($value) ] = $value;
                $d = (int)sprintf('%o', $value);
                $modes[$d] = $value;
                $o = (int)sprintf('%o', decoct($value));
                $modes[$o] = $value;
            }
        }
        $mode = (int)$mode;
        return isset($modes[$mode]) ? $modes[$mode] : $this->chmod_file;
    }

    public static function getownername($owner) {
        if ($owner && function_exists('posix_getpwuid')) {
            $a = posix_getpwuid($owner);
            return $a['name'];
        }
        return $owner;
    }

    protected function getgroupname($group) {
        if ($group && function_exists('posix_getgrgid')) {
            $a = posix_getgrgid($group);
            return $a['name'];
        }
        return $group;
    }

    public function copy($src, $dst, $overwrite = false) {
        if (!$overwrite && $this->exists($dst)) return false;
        if (false === ($s = $this->getfile($src))) return false;
        return $this->putfile($dst, $s);
    }

    public function move($source, $destination, $overwrite = false) {
        if ($this->copy($source, $destination, $overwrite) && $this->exists($destination)) {
            $this->delete($source);
            return true;
        }
        return false;
    }

    public function mkdir($path, $chmod) {
        if (!$chmod) $chmod = $this->chmod_dir;
        $chmod = $this->getmode($chmod);
        $this->chmod($path, $chmod);
        return true;
    }

    public function forcedir($dir) {
        $dir = rtrim($dir, '/');
        if (!$this->is_dir($dir)) {
            $this->forcedir(dirname($dir));
            $this->mkdir($dir, $this->chmod_dir);
        }
    }

    protected function getfileinfo($filename) {
        $result = array();
        $result['mode'] = $this->getchmod($filename);
        $result['owner'] = $this->owner($filename);
        $result['group'] = $this->group($filename);
        $result['size'] = $this->size($filename);
        $result['time'] = $this->mtime($filename);
        $result['isdir'] = $this->is_dir($filename);
        return $result;
    }

    public function each($dir, $func, $args) {
        $dir = rtrim($dir, '/');
        if ($list = $this->getdir($dir)) {
            $call = array(
                $this,
                $func
            );
            if (!is_array($args)) {
                $args = isset($args) ? array(
                    0 => $args
                ) : array();
            }
            array_unshift($args, 0);
            foreach ($list as $name => $item) {
                $args[0] = $dir . '/' . $name;
                call_user_func_array($call, $args);
            }
        }
    }

    public function getroot($root) {
        $temp = litepubl::$paths->data . md5rand();
        file_put_contents($temp, ' ');
        @chmod($temp, 0666);

        $filename = str_replace('\\\\', '/', $temp);
        $filename = str_replace('\\', '/', $filename);

        $this->chdir('/');
        if (!$root || !strbegin($filename, $root) || !$this->exists(substr($filename, strlen($root)))) {
            $root = $this->findroot($temp);
        }

        unlink($temp);
        return $root;
    }

    public function findroot($filename) {
        $root = '';
        $filename = str_replace('\\\\', '/', $filename);
        $filename = str_replace('\\', '/', $filename);

        if ($i = strpos($filename, ':')) {
            $root = substr($filename, 0, $i);
            $filename = substr($filename, $i);
        }

        $this->chdir('/');
        while ($filename && !$this->exists($filename)) {
            if ($i = strpos($filename, '/', 1)) {
                $root.= substr($filename, 0, $i);
                $filename = substr($filename, $i);
            } else {
                return false;
            }
        }

        return $root;
    }

} //class