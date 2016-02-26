<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */
class ticons extends titems {

  public static function i() {
    return getinstance(__class__);
  }

  public function getid($name) {
    return isset($this->items[$name]) ? $this->items[$name] : 0;
  }

  public function geturl($name) {
    if (isset($this->items[$name])) {
      $files = tfiles::i();
      return $files->geturl($this->items[$name]);
    }
    return '';
  }

  public function geticon($name) {
    if (isset($this->items[$name]) && ($this->items[$name] > 0)) {
      $files = tfiles::i();
      return $files->geticon($this->items[$name]);
    }
    return '';
  }

  public function filedeleted($idfile) {
    foreach ($this->items as $name => $id) {
      if ($id == $idfile) {
        $this->delete($name);
        return true;
      }
    }
  }

} //class