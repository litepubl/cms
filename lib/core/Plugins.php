<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\core;

use litepubl\view\Lang;

/**
*
 * Plugins manager
 *
 *
 * @property-write callable $onUpdate
 * @property-write callable $onSkip
 * @method         array onUpdate(array $params)
 * @method         array onSkip(array $params)
 */

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
        $this->addEvents('onUpdate', 'onSkip');
        $this->deprecated = [];
        $this->addMap('paths', []);
    }

    private function getNamedPath(string$name, string $section): string
    {
        if (isset($this->paths[$section])) {
            return trim($this->paths[$section], '\/') . '/' . $name;
        } else {
            return 'plugins/' . $name;
        }
    }

    public function __get($name)
    {
        if (isset($this->items[$name])) {
            return $this->getNamedPath($name, $this->items[$name]['path'] ?? '');
        } elseif (isset($this->paths[$name])) {
            return $this->paths[$name];
        } else {
            $list = $this->getDirNames();
            if (isset($list[$name])) {
                        return $this->getNamedPath($name, $list[$name]);
            }

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

    public static function getLangAbout($filename): Lang
    {
        $dir = dirname($filename);
        $name = basename($dir);
        if (!isset(static::$abouts[$name])) {
                $about = static ::localAbout($dir);
                static::$abouts[$name] = $about;
        }

        $about = static::$abouts[$name];
        $lang = Lang::admin();
        $lang->ini[$name] = $about;
        $lang->section = $name;
        return $lang;
    }

    public function add(string $name)
    {
        $dirNames = $this->getDirNames();
        if (!isset($dirNames[$name])) {
                return false;
        }

        $dir = $this->getPluginDir($name) . DIRECTORY_SEPARATOR;
        $about = static ::getAbout($name);

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

        $classname = trim($about['classname']);
        if (!strrpos($classname, '\\')) {
            $this->error('Plugin class must have namespace');
        }

        $classes = $this->getApp()->classes;
        $classes->lock();
        $this->lock();
            $this->items[$name] = [
        'path' => $dirNames[$name],
            ];

            $classes->installClass($classname);
            if ($about['adminclassname']) {
                $classes->installClass($about['adminclassname']);
            }

            $this->unlock();
            $classes->unlock();

            $this->added(['name' => $name]);
            $this->getApp()->log(sprintf('Plugin %s added', $name));
            $this->getApp()->cache->clear();
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

        unset($this->items[$name]);
        $this->save();

        $about = static ::getabout($name);
        $datafile = false;
        $app = $this->getApp();
        if (class_exists($about['classname'])) {
            $plugin = $app->classes->getInstance($about['classname']);
            if ($plugin instanceof Plugin) {
                $datafile = $app->paths->data . $plugin->getbasename();
            }
        }

        $app->classes->lock();
        if (strrpos($about['classname'], '\\')) {
            if ($about['adminclassname']) {
                $app->classes->uninstallClass($about['adminclassname']);
            }

            $app->classes->uninstallClass($about['classname']);
        } else {
            if ($about['adminclassname']) {
                $app->classes->delete($about['adminclassname']);
            }

            $app->classes->delete($about['classname']);
        }

        $app->classes->unlock();

        if ($datafile) {
            $app->storage->remove($datafile);
        }

        $this->deleted(['name' => $name]);
        $this->getApp()->log(sprintf('Plugin %s deleted', $name));
        $this->getApp()->cache->clear();
        return true;
    }

    public function deleteClass($class)
    {
        foreach ($this->items as $name => $item) {
            if ($item['class'] == $class) {
                $this->Delete($name);
            }
        }
    }

    public function addNamespace($instance)
    {
        $className = static::getClassName($instance);
        if ($i = strrpos($className, '\\')) {
            $ns = substr($className, 0, $i);
            $reflector = new \ReflectionClass($instance);
            $fileName = $reflector->getFileName();
            $path = $this->getRelativePath(dirname($fileName));
            $this->getApp()->classes->addNamespace($ns, $path);
        }
    }

    public function getRelativePath(string $to): string
    {
        $to = str_replace('\\', '/', $to);
        $to = rtrim($to, '/') . '/';
        $to       = explode('/', $to);
        $result = $to;

        $from     = explode('/', str_replace('\\', '/', $this->getApp()->paths->home));
        foreach ($from as $i => $dir) {
            if ($dir === $to[$i]) {
                array_shift($result);
            } else {
                $count = count($from) - $i;
                if ($count > 1) {
                    $padLength = (count($result) + $i - 1) * -1;
                    $result = array_pad($result, $padLength, '..');
                    break;
                } else {
                    $result[0] = './' . $result[0];
                }
            }
        }

        return implode('/', $result);
    }

    public function getPlugins()
    {
        return array_keys($this->items);
    }

    public function update(array $list)
    {
        $r = $this->onUpdate(['items' => $list]);
        $list = $r['items'];
        $add = array_diff($list, array_keys($this->items));
        $add = array_intersect($add, array_keys($this->getDirNames()));
        $delete = array_diff(array_keys($this->items), $list);
        $delete = array_intersect($delete, array_keys($this->getDirNames()));

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
        while ($filename = $list->read()) {
            if ($filename == '.' || $filename == '..' || $filename == '.git') {
                continue;
            }

            if (is_dir($dir . $filename)) {
                $this->dirNames[$filename] = '';
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

            $home = $this->getApp()->paths->home;
            foreach ($paths as $namePath => $path) {
                $path = trim($path, '\/');
                if (is_dir($home . $path)) {
                    $dirNames = [];
                    $dir = $home . $path;
                    $list = dir($dir);
                    while ($filename = $list->read()) {
                        if ($filename == '.' || $filename == '..' || $filename == '.git') {
                            continue;
                        }

                        if (is_dir($dir . '/' . $filename)) {
                            $dirNames[$filename] = $namePath;
                        }
                    }

                    $list->close();

                    ksort($dirNames);
                    $this->dirNames = $this->dirNames + $dirNames;
                }
            }
        }

        return $this->dirNames;
    }

    public function exists(string $name): bool
    {
        $list = $this->getDirNames();
        return isset($list[$name]);
    }

    public function getPluginDir(string $name): string
    {
        $home = $this->getApp()->paths->home;
        $plugins = $this->getApp()->paths->plugins;
        if (isset($this->items[$name])) {
            return $home . $this->__get($name);
        } elseif (is_dir($plugins . $name)) {
            return $plugins . $name;
        } else {
            $dirNames = $this->getDirNames();
            if (isset($this->paths[$dirNames[$name]])) {
                return $home . trim($this->paths[$dirNames[$name]], '\/') . '/' . $name;
            }
        }

        $this->error(sprintf('Plugin dir not found for %s', $name));
    }

    public function skipped(string $name): bool
    {
        if (in_array($name, $this->deprecated)) {
            return true;
        }

        $r = $this->onSkip(['name' => $name, 'result' => false]);
        return $r['result'];
    }
}
