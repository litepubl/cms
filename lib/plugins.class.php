<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tplugins extends titems {
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
    if (!isset(static::$abouts[$name])) {
      if (!isset(static::$abouts)) static::$abouts = array();
      static::$abouts[$name] = static::localabout(litepubl::$paths->plugins . $name);
    }
    return static::$abouts[$name];
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
    return static::getnamelang(static::getname($filename));
  }

  public static function getnamelang($name) {
    $about = static::getabout($name);
    $lang = tlocal::admin();
    $lang->ini[$name] = $about;
    $lang->section = $name;
    return $lang;
  }

  public function add($name) {
    if (!@is_dir(litepubl::$paths->plugins . $name)) {
      return false;
    }

    $about = static::getabout($name);
    return $this->AddExt($name, $about['classname'], $about['filename'], $about['adminclassname'], $about['adminfilename']);
  }

  public function AddExt($name, $classname, $filename, $adminclassname, $adminfilename) {
if (!strpos($classname, '\\')) {
$classname = 'litepubl\\' . $classname;
}

if (!strpos($adminclassname, '\\')) {
$adminclassname = 'litepubl\\' . $adminclassname;
}

    $this->lock();
    $this->items[$name] = array(
      'id' => ++$this->autoid,
      'class' => $classname,
      'file' => $filename,
      'adminclass' => $adminclassname,
      'adminfile' => $adminfilename
    );

    litepubl::$classes->lock();
    litepubl::$classes->Add($classname, $filename, $name);
    if ($adminclassname) {
      litepubl::$classes->Add($adminclassname, $adminfilename, $name);
    }

    litepubl::$classes->unlock();
    $this->unlock();
    $this->added($name);
    return $this->autoid;
  }

  public function has($name) {
    return isset($this->items[$name]);
  }

  public function delete($name) {
    if (!isset($this->items[$name])) return false;
    $item = $this->items[$name];
    unset($this->items[$name]);
    $this->save();

    $datafile = false;
    if (class_exists($item['class'])) {
      $plugin = getinstance($item['class']);
      if ($plugin instanceof tplugin) {
        $datafile = litepubl::$paths->data . $plugin->getbasename();
      }
    }

    litepubl::$classes->lock();
    if (!empty($item['adminclass'])) litepubl::$classes->delete($item['adminclass']);
    litepubl::$classes->delete($item['class']);
    litepubl::$classes->unlock();

    if ($datafile) {
      litepubl::$storage->remove($datafile);
    }

    $this->deleted($name);
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
      if (!@mkdir(litepubl::$paths->plugins . $name, 0777)) return $this->Error("Cant create $name folder in plugins");
      @chmod(litepubl::$paths->plugins . $name, 0777);
    }
    $dir = litepubl::$paths->plugins . $name . DIRECTORY_SEPARATOR;
    foreach ($files as $filename => $content) {
      file_put_contents($dir . $filename, base64_decode($content));
    }
  }

} //class