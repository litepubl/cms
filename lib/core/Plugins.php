<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\core;

class Plugins extends Items
 {
    public static $abouts;
    public $deprecated;

    protected function create() {
        $this->dbversion = false;
        parent::create();
        $this->basename = 'plugins' . DIRECTORY_SEPARATOR . 'index';
        $this->deprecated = array(
            'ajaxcommentform',
            'fileprops'
        );
    }

    public static function getabout($name) {
        if (!isset(static ::$abouts[$name])) {
            if (!isset(static ::$abouts)) static ::$abouts = array();
            static ::$abouts[$name] = static ::localabout(litepubl::$paths->plugins . $name);
        }
        return static ::$abouts[$name];
    }

    public static function localabout($dir) {
        $filename = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'about.ini';
        $about = parse_ini_file($filename, true);
        if (isset($about[litepubl::$options->language])) {
            $about['about'] = $about[litepubl::$options->language] + $about['about'];
        }

        return $about['about'];
    }

    public static function getname($filename) {
        return basename(dirname($filename));
    }

    public static function getlangabout($filename) {
        return static ::getnamelang(static ::getname($filename));
    }

    public static function getnamelang($name) {
        $about = static ::getabout($name);
        $lang = tlocal::admin();
        $lang->ini[$name] = $about;
        $lang->section = $name;
        return $lang;
    }

    public function add($name) {
        if (!@is_dir(litepubl::$paths->plugins . $name)) {
            return false;
        }

        $about = static ::getabout($name);

        $dir = litepubl::$paths->plugins . $name . DIRECTORY_SEPARATOR;
        if (file_exists($dir . $about['filename'])) {
            require_once ($dir . $about['filename']);
        } else {
            $this->error(sprintf('File plugins/%s/%s not found', $name, $about['filename']));
        }

        if ($about['adminfilename']) {
            if (file_exists($dir . $about['adminfilename'])) {
                require_once ($dir . $about['adminfilename']);
            } else {
                $this->error(sprintf('File plugins/%s/%s not found', $name, $about['adminfilename']));
            }
        }

        litepubl::$classes->lock();
        $this->lock();
        $classname = trim($about['classname']);
        if ($i = strrpos($classname, '\\')) {
            $this->items[$name] = array(
                'namespace' => substr($classname, 0, $i) ,
            );

            litepubl::$classes->installClass($classname);
            if ($about['adminclassname']) {
                litepubl::$classes->installClass($about['adminclassname']);
            }
        } else {
            $this->items[$name] = array(
                'namespace' => false,
            );

            if (!class_exists($classname, false)) {
                $classname = 'litepubl\\' . $classname;
            }

            litepubl::$classes->Add($classname, sprintf('plugins/%s/%s', $name, $about['filename']));

            if ($adminclass = $about['adminclassname']) {
                if (!class_exists($adminclass, false)) {
                    $adminclass = 'litepubl\\' . $adminclass;
                }

                litepubl::$classes->Add($adminclass, sprintf('plugins/%s/%s', $name, $about['adminfilename']));
            }
        }

        $this->unlock();
        litepubl::$classes->unlock();
        $this->added($name);
        return $name;
    }

    public function has($name) {
        return isset($this->items[$name]);
    }

    public function delete($name) {
        if (!isset($this->items[$name])) {
            return false;
        }

        $namespace = isset($this->items[$name]['namespace']) ? $this->items[$name]['namespace'] : false;
        unset($this->items[$name]);
        $this->save();

        $about = static ::getabout($name);
        $datafile = false;
        if (class_exists($about['classname'])) {
            $plugin = litepubl::$classes->getinstance($about['classname']);
            if ($plugin instanceof tplugin) {
                $datafile = litepubl::$paths->data . $plugin->getbasename();
            }
        }

        litepubl::$classes->lock();
        if ($namespace) {
            if ($about['adminclassname']) {
                litepubl::$classes->uninstallClass($about['adminclassname']);
            }

            litepubl::$classes->uninstallClass($about['classname']);
        } else {
            if (($about['adminclassname'])) {
                litepubl::$classes->delete($about['adminclassname']);
            }

            litepubl::$classes->delete($about['classname']);
        }

        litepubl::$classes->unlock();

        if ($datafile) {
            litepubl::$storage->remove($datafile);
        }

        $this->deleted($name);
        return true;
    }

    public function deleteclass($class) {
        foreach ($this->items as $name => $item) {
            if ($item['class'] == $class) $this->Delete($name);
        }
    }

    public function getplugins() {
        return array_keys($this->items);
    }

    public function update(array $list) {
        $add = array_diff($list, array_keys($this->items));
        $delete = array_diff(array_keys($this->items) , $list);
        $delete = array_intersect($delete, tfiler::getdir(litepubl::$paths->plugins));
        $this->lock();
        foreach ($delete as $name) {
            $this->Delete($name);
        }

        foreach ($add as $name) {
            $this->Add($name);
        }

        $this->unlock();
    }

    public function setplugins(array $list) {
        $names = array_diff($list, array_keys($this->items));
        foreach ($names as $name) {
            $this->Add($name);
        }
    }

    public function deleteplugins($list) {
        $names = array_intersect(array_keys($this->items) , $list);
        foreach ($names as $name) {
            $this->Delete($name);
        }
    }

    public function upload($name, $files) {
        if (!@file_exists(litepubl::$paths->plugins . $name)) {
            if (!@mkdir(litepubl::$paths->plugins . $name, 0777)) return $this->Error("Cantcreate $namefolderinplugins");
            @chmod(litepubl::$paths->plugins . $name, 0777);
        }
        $dir = litepubl::$paths->plugins . $name . DIRECTORY_SEPARATOR;
        foreach ($files as $filename => $content) {
            file_put_contents($dir . $filename, base64_decode($content));
        }
    }

} //class