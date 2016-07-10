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
    public static $abouts = [];
    public $deprecated;
public $paths;
private $dirNames;

    protected function create()
    {
        $this->dbversion = false;
        parent::create();
        $this->basename = 'plugins/index';
        $this->deprecated = [];
$this->addMap('paths', []);
    }

public function __get($name)
{
if (isset($this->items[$name])) {
$section = $this->items[$name]['path'] ?? '';
if (isset($this->paths[$section])) {
return trim($this->paths[$section], '\/') . '/' . $name;
} else {
return $name;
}
} elseif (isset($this->paths[$name])) {
return $this->paths[$name];
} else {
return parent::__get($name);
}
}

public function __set($name, $value)
{
if (isset($this->items[$name])) {
$this->items[$name]['path'] = $value;
} elseif (isset($this->paths[$name])) {
$this->paths[$name] = $value;
} else {
return parent::__set($name, $value);
}

$this->save();
return true;
}

    public static function getAbout(string $name): array
    {
        if (!isset(static ::$abouts[$name])) {
$pluginsDir = static ::getAppInstance()->paths->plugins;
if (is_dir($pluginsDir . $name)) {
$dir = $pluginsDir . $name;
} else {
$self = static::i();
$dir = $self->getPluginDir($name);
}

            static ::$abouts[$name] = static ::localAbout($dir);
        }

        return static ::$abouts[$name];
    }

    public static function localAbout(string $dir): array
    {
        $filename = rtrim($dir, '\/') . '/about.ini';
        $about = parse_ini_file($filename, true);
        if (isset($about[static ::getAppInstance()->options->language])) {
            $about['about'] = $about[static ::getAppInstance()->options->language] + $about['about'];
        }

        return $about['about'];
    }

    public static function getName(string $filename): string
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

    public function has(string $name): bool
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

    public function deletePlugins($list)
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

public function readPaths(): array
{
$paths = [];
$dir = $this->getapp()->paths->plugins;
$list = dir($dir);
while($filename = $list->read()) {
if ($filename == '.' || $filename == '..') {
continue;
}

if (is_dir($dir . $filename)) {
$this->dirNames[$filename] = $dir;
} elseif (substr($filename, -4) == '.ini') {
$ini = parse_ini_file($dir . $filename, false);
$paths = $ini + $paths;
}
}

$list->close();

if ($paths != $this->paths) {
$this->paths = $paths;
$this->save();
}

return $paths;
}

public function getDirNames(): array
{
if (!$this->dirNames) {
        $this->dirNames = [];
$paths = $this->readPaths();
        ksort($this->dirNames);

$pluginsDir = $this->getApp()->paths->plugins;
foreach ($paths as $path) {
$path = trim($path, '\/');
if (is_dir($pluginsDir . $path)) {
$dirNames = [];
$dir = $pluginsDir . $path;
$list = dir($dir);
while ($filename = $list->read()) {
if ($filename == '.' || $filename == '..') {
continue;
}

if (is_dir($dir . /' . $filename)) {
$dirNames[$filename] = $dir . '/' . $filename . '/';
}
}

$list->close();

ksort($dirNames);
$this->dirNames = $dirNames + $this->dirNames;
}
}
}

return $this->dirNames;
}

public function exists(string $name): bool
{
$list = $this->getDirList();
return isset($list[$name]);
}

public function getPluginDir(string $name): string
{
if ($this->dirNames && isset($this->dirNames[$name])) {
return $this->dirNames[$name];
} elseif (isset($this->items[$name])) {
return $this->getApp()->paths->plugins . $this->__get($name);
} else {
$list = $this->getDirNames();
return $list[$name];
}

}
