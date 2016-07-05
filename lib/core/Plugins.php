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

use litepubl\utils\Filer;
use litepubl\view\Lang;

class Plugins extends Items
{
    public static $abouts;
    public $deprecated;

    protected function create()
    {
        $this->dbversion = false;
        parent::create();
        $this->basename = 'plugins' . DIRECTORY_SEPARATOR . 'index';
        $this->deprecated = array(
            'ajaxcommentform',
            'fileprops'
        );
    }

    public static function getAbout($name)
    {
        if (!isset(static ::$abouts[$name])) {
            if (!isset(static ::$abouts)) {
                static ::$abouts = array();
            }

            static ::$abouts[$name] = static ::localabout(static ::getAppInstance()->paths->plugins . $name);
        }

        return static ::$abouts[$name];
    }

    public static function localAbout($dir)
    {
        $filename = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'about.ini';
        $about = parse_ini_file($filename, true);
        if (isset($about[static ::getAppInstance()->options->language])) {
            $about['about'] = $about[static ::getAppInstance()->options->language] + $about['about'];
        }

        return $about['about'];
    }

    public static function getName($filename)
    {
        return basename(dirname($filename));
    }

    public static function getLangAbout($filename)
    {
        return static ::getNameLang(static ::getName($filename));
    }

    public static function getNamelang($name)
    {
        $about = static ::getAbout($name);
        $lang = Lang::admin();
        $lang->ini[$name] = $about;
        $lang->section = $name;
        return $lang;
    }

    public function add($name)
    {
        if (!@is_dir($this->getApp()->paths->plugins . $name)) {
            return false;
        }

        $about = static ::getabout($name);
        $dir = $this->getApp()->paths->plugins . $name . DIRECTORY_SEPARATOR;
        if (file_exists($dir . $about['filename'])) {
            include_once $dir . $about['filename'];
        } else {
            $this->error(sprintf('File plugins/%s/%s not found', $name, $about['filename']));
        }

        if ($about['adminfilename']) {
            if (file_exists($dir . $about['adminfilename'])) {
                include_once $dir . $about['adminfilename'];
            } else {
                $this->error(sprintf('File plugins/%s/%s not found', $name, $about['adminfilename']));
            }
        }

        $classes = $this->getApp()->classes;
        $classes->lock();
        $this->lock();
        $classname = trim($about['classname']);
        if ($i = strrpos($classname, '\\')) {
            $this->items[$name] = array(
                'namespace' => substr($classname, 0, $i) ,
            );

            $classes->installClass($classname);
            if ($about['adminclassname']) {
                $classes->installClass($about['adminclassname']);
            }
        } else {
            $this->items[$name] = array(
                'namespace' => false,
            );

            if (!class_exists($classname, false)) {
                $classname = 'litepubl\\' . $classname;
            }

            $classes->Add($classname, sprintf('plugins/%s/%s', $name, $about['filename']));

            if ($adminclass = $about['adminclassname']) {
                if (!class_exists($adminclass, false)) {
                    $adminclass = 'litepubl\\' . $adminclass;
                }

                $classes->Add($adminclass, sprintf('plugins/%s/%s', $name, $about['adminfilename']));
            }
        }

        $this->unlock();
        $classes->unlock();
        $this->added($name);
        return $name;
    }

    public function has($name)
    {
        return isset($this->items[$name]);
    }

    public function delete($name)
    {
        if (!isset($this->items[$name])) {
            return false;
        }

        $namespace = isset($this->items[$name]['namespace']) ? $this->items[$name]['namespace'] : false;
        unset($this->items[$name]);
        $this->save();

        $about = static ::getabout($name);
        $datafile = false;
        if (class_exists($about['classname'])) {
            $plugin = $this->getApp()->classes->getinstance($about['classname']);
            if ($plugin instanceof tplugin) {
                $datafile = $this->getApp()->paths->data . $plugin->getbasename();
            }
        }

        $this->getApp()->classes->lock();
        if ($namespace) {
            if ($about['adminclassname']) {
                $this->getApp()->classes->uninstallClass($about['adminclassname']);
            }

            $this->getApp()->classes->uninstallClass($about['classname']);
        } else {
            if (($about['adminclassname'])) {
                $this->getApp()->classes->delete($about['adminclassname']);
            }

            $this->getApp()->classes->delete($about['classname']);
        }

        $this->getApp()->classes->unlock();

        if ($datafile) {
            $this->getApp()->storage->remove($datafile);
        }

        $this->deleted($name);
        return true;
    }

    public function deleteclass($class)
    {
        foreach ($this->items as $name => $item) {
            if ($item['class'] == $class) {
                $this->Delete($name);
            }
        }
    }

    public function getPlugins()
    {
        return array_keys($this->items);
    }

    public function update(array $list)
    {
        $add = array_diff($list, array_keys($this->items));
        $delete = array_diff(array_keys($this->items), $list);
        $delete = array_intersect($delete, Filer::getdir($this->getApp()->paths->plugins));

        $this->lock();
        foreach ($delete as $name) {
            $this->Delete($name);
        }

        foreach ($add as $name) {
            $this->Add($name);
        }

        $this->unlock();
    }

    public function setPlugins(array $list)
    {
        $names = array_diff($list, array_keys($this->items));
        foreach ($names as $name) {
            $this->Add($name);
        }
    }

    public function deleteplugins($list)
    {
        $names = array_intersect(array_keys($this->items), $list);
        foreach ($names as $name) {
            $this->Delete($name);
        }
    }

    public function upload($name, $files)
    {
        if (!@file_exists($this->getApp()->paths->plugins . $name)) {
            if (!@mkdir($this->getApp()->paths->plugins . $name, 0777)) {
                return $this->Error("Cantcreate $name folder inplugins");
            }

            @chmod($this->getApp()->paths->plugins . $name, 0777);
        }
        $dir = $this->getApp()->paths->plugins . $name . DIRECTORY_SEPARATOR;
        foreach ($files as $filename => $content) {
            file_put_contents($dir . $filename, base64_decode($content));
        }
    }
}
