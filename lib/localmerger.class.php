<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tlocalmerger extends tfilemerger {

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'localmerger';
  }

  public function addtext($name, $section, $s) {
    $s = trim($s);
    if ($s != '') $this->addsection($name, $section, tini2array::parsesection($s));
  }

  public function addsection($name, $section, array $items) {
    if (!isset($this->items[$name])) {
      $this->items[$name] = array(
        'files' => array() ,
        'texts' => array(
          $key => $items
        )
      );
    } elseif (!isset($this->items[$name]['texts'][$section])) {
      $this->items[$name]['texts'][$section] = $items;
    } else {
      $this->items[$name]['texts'][$section] = $items + $this->items[$name]['texts'][$section];
    }
    $this->save();
    return count($this->items[$name]['texts']) - 1;
  }

  public function getrealfilename($filename) {
    $filename = ltrim($filename, '/');
    $name = substr($filename, 0, strpos($filename, '/'));
    if (isset(litepublisher::$paths->$name)) {
      return litepublisher::$paths->$name . str_replace('/', DIRECTORY_SEPARATOR, substr($filename, strlen($name) + 1));
    }
    return litepublisher::$paths->home . str_replace('/', DIRECTORY_SEPARATOR, $filename);
  }

  public function merge() {
    $lang = getinstance('tlocal');
    $lang->ini = array();
    inifiles::$files = array();
    foreach ($this->items as $name => $items) {
      $this->parse($name);
    }
  }

  public function parse($name) {
    $lang = getinstance('tlocal');
    if (!isset($this->items[$name])) $this->error(sprintf('The "%s" partition not found', $name));
    $ini = array();
    foreach ($this->items[$name]['files'] as $filename) {
      $realfilename = $this->getrealfilename($filename);
      if (!file_exists($realfilename)) continue;
      if (!file_exists($realfilename)) $this->error(sprintf('The file "%s" not found', $filename));
      if (!($parsed = parse_ini_file($realfilename, true))) $this->error(sprintf('Error parse "%s" ini file', $realfilename));
      if (count($ini) == 0) {
        $ini = $parsed;
      } else {
        foreach ($parsed as $section => $itemsini) {
          $ini[$section] = isset($ini[$section]) ? $itemsini + $ini[$section] : $itemsini;
        }
      }
    }

    foreach ($this->items[$name]['texts'] as $section => $itemsini) {
      $ini[$section] = isset($ini[$section]) ? $itemsini + $ini[$section] : $itemsini;
    }

    litepubl::$storage->savedata(tlocal::getcachedir() . $name, $ini);
    $lang->ini = $ini + $lang->ini;
    $lang->loaded[] = $name;
    if (isset($ini['searchsect'])) $lang->joinsearch($ini['searchsect']);
  }

  public function addplugin($name) {
    $language = litepublisher::$options->language;
    $dir = litepublisher::$paths->plugins . $name . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
    $this->lock();
    if (file_exists($dir . $language . '.ini')) $this->add('default', "plugins/$name/resource/$language.ini");
    if (file_exists($dir . $language . '.admin.ini')) $this->add('admin', "plugins/$name/resource/$language.admin.ini");
    if (file_exists($dir . $language . '.mail.ini')) $this->add('mail', "plugins/$name/resource/$language.mail.ini");
    if (file_exists($dir . $language . '.install.ini')) $this->add('install', "plugins/$name/resource/$language.install.ini");
    $this->unlock();
  }

  public function deleteplugin($name) {
    $language = litepublisher::$options->language;
    $this->lock();
    $this->deletefile('default', "plugins/$name/resource/$language.ini");
    $this->deletefile('admin', "plugins/$name/resource/$language.admin.ini");
    $this->deletefile('mail', "plugins/$name/resource/$language.mail.ini");
    $this->deletefile('install', "plugins/$name/resource/$language.install.ini");
    $this->unlock();
  }

} //class