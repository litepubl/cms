<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tsingleitems extends titems {
  public $copyprops;
  public static $instances;

  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->copyprops = array();
  }

  public function addinstance($instance) {
    $classname = get_class($instance);
    $item = array(
      'classname' => $classname,
    );

    foreach ($this->copyprops as $prop) {
      $item[$prop] = $instance->{$prop};
    }

    $id = $this->additem($item);
    $instance->id = $id;
    $instance->save();

    if (isset(self::$instances[$classname])) {
      self::$instances[$classname][$id] = $instance;
    } else {
      self::$instances[$classname] = array(
        $id => $instance
      );
    }

    return $id;
  }

  public function get($id) {
    $id = (int)$id;
    $classname = $this->items[$id]['classname'];
    $result = getinstance($classname);
    if ($id != $result->id) {
      if (!isset(self::$instances[$classname])) {
        self::$instances[$classname] = array();
      }

      if (isset(self::$instances[$classname][$id])) {
        $result = self::$instances[$classname][$id];
      } else {
        if ($result->id) {
          $result = new $classname();
        }

        $result->id = $id;
        $result->load();
        self::$instances[$classname][$id] = $result;
      }
    }

    return $result;
  }

} //class