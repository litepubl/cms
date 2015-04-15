<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tplugins extends TItems {
  public static $abouts;
  public $deprecated;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'plugins' . DIRECTORY_SEPARATOR  . 'index';
    $this->deprecated = array('ajaxcommentform', 'fileprops');
  }
  
  public static function getabout($name) {
    if (!isset(self::$abouts[$name])) {
      if (!isset(self::$abouts)) self::$abouts = array();
      self::$abouts[$name] = self::localabout(litepublisher::$paths->plugins .  $name );
    }
    return self::$abouts[$name];
  }
  
  public static function localabout($dir) {
    $filename = rtrim($dir,DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR  . 'about.ini';
    $about = parse_ini_file($filename, true);
    if (isset($about[litepublisher::$options->language])) {
      $about['about'] = $about[litepublisher::$options->language] + $about['about'];
    }
    
    return $about['about'];
  }
  
  public static function getname($filename) {
    return basename(dirname($filename));
  }
  
  public static function getlangabout($filename) {
    return self::getnamelang(self::getname($filename));
  }
  
  public static function getnamelang($name) {
    $about = self::getabout($name);
    $lang = tlocal::admin();
    $lang->ini[$name] = $about;
    $lang->section = $name;
    return $lang;
  }
  
  public function add($name) {
    if (!@is_dir(litepublisher::$paths->plugins . $name)) return false;
    $about = self::getabout($name);
    return $this->AddExt($name, $about['classname'], $about['filename'], $about['adminclassname'], $about['adminfilename']);
  }
  
  public function AddExt($name, $classname, $filename, $adminclassname, $adminfilename) {
    $this->lock();
    $this->items[$name] = array(
    'id' => ++$this->autoid,
    'class' => $classname,
    'file' => $filename,
    'adminclass' => $adminclassname,
    'adminfile' => $adminfilename
    );
    
    litepublisher::$classes->lock();
    litepublisher::$classes->Add($classname, $filename, $name);
    if ($adminclassname != '') litepublisher::$classes->Add($adminclassname, $adminfilename, $name);
    litepublisher::$classes->unlock();
    $this->unlock();
    $this->added($name);return $this->autoid;
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
        $datafile = litepublisher::$paths->data. $plugin->getbasename();
      }
    }
    
    litepublisher::$classes->lock();
    if (!empty($item['adminclass'])) litepublisher::$classes->delete($item['adminclass']);
    litepublisher::$classes->delete($item['class']);
    litepublisher::$classes->unlock();
    
    if ($datafile) {
      tfilestorage::delete($datafile . '.php');
      tfilestorage::delete($datafile . '.bak.php');
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
    $delete  = array_diff(array_keys($this->items), $list);
    $delete  = array_intersect($delete, tfiler::getdir(litepublisher::$paths->plugins));
    $this->lock();
    foreach ($delete as $name) {
      $this->Delete($name);
    }
    
    foreach ($add as $name) {
      $this->Add($name);
    }
    
    $this->unlock();
  }
  
  public function setplugins($list) {
    $names = array_diff($list, array_keys($this->items));
    foreach ($names as $name) {
      $this->Add($name);
    }
  }
  
  public function deleteplugins($list) {
    $names = array_intersect(array_keys($this->items), $list);
    foreach ($names as $name) {
      $this->Delete($name);
    }
  }
  
  public function upload($name, $files) {
    if (!@file_exists(litepublisher::$paths->plugins . $name)) {
      if (!@mkdir(litepublisher::$paths->plugins . $name, 0777)) return $this->Error("Cant create $name folder in plugins");
      @chmod(litepublisher::$paths->plugins . $name, 0777);
    }
    $dir = litepublisher::$paths->plugins . $name . DIRECTORY_SEPARATOR  ;
    foreach ($files as $filename => $content) {
      file_put_contents($dir . $filename, base64_decode($content));
    }
  }
  
} //class