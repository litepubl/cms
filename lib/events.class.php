<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class ECancelEvent extends Exception {
  public $result;
  
  public function __construct($message, $code = 0) {
    $this->result = $message;
    parent::__construct('', 0);
  }
}

class tevents extends tdata {
  protected $events;
  protected $eventnames;
  protected $map;
  
  public function __construct() {
    $this->eventnames = array();
    $this->map = array();
    parent::__construct();
    $this->assignmap();
    $this->load();
  }
  
  public function __destruct() {
    unset($this->data, $this->events, $this->eventnames, $this->map);
  }
  
  protected function create() {
    $this->addmap('events', array());
    $this->addmap('coclasses', array());
  }
  
  public function assignmap() {
    foreach ($this->map as $propname => $key) {
      $this->$propname = &$this->data[$key];
    }
  }
  
  public function afterload() {
    $this->assignmap();
    foreach ($this->coclasses as $coclass) {
      $this->coinstances[] = getinstance($coclass);
    }
    parent::afterload();
  }
  
  protected function addmap($name, $value) {
    $this->map[$name] = $name;
    $this->data[$name] = $value;
    $this->$name = &$this->data[$name];
  }
  
  public function free() {
    unset(litepublisher::$classes->instances[get_class($this)]);
    foreach ($this->coinstances as $coinstance) $coinstance->free();
  }
  
  public function eventexists($name) {
    return in_array($name, $this->eventnames);
  }
  
  public function __get($name) {
    if (method_exists($this, $name)) return array('class' =>get_class($this), 'func' => $name);
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if (parent::__set($name, $value)) return true;
    if (in_array($name, $this->eventnames)) {
      $this->addevent($name, $value['class'], $value['func']);
      return true;
    }
    $this->error(sprintf('Unknown property %s in class %s', $name, get_class($this)));
  }
  
  public function method_exists($name) {
    return in_array($name, $this->eventnames);
  }
  
  public  function __call($name, $params) {
    if (in_array($name, $this->eventnames)) return $this->callevent($name, $params);
    parent::__call($name, $params);
  }
  
  public function __isset($name) {
    if (parent::__isset($name)) return true;
    return in_array($name, $this->eventnames);
  }
  
  protected function addevents() {
    $a = func_get_args();
    array_splice($this->eventnames, count($this->eventnames), 0, $a);
  }
  
  private function get_events($name) {
    return isset($this->events[$name]) ? $this->events[$name] : false;
  }
  
  public function callevent($name, $params) {
    $result = '';
    if (    $list = $this->get_events($name)) {
      
      foreach ($list as $i => $item) {
        if (empty($item['class'])) {
          if (function_exists($item['func'])) {
            $call = $item['func'];
          } else {
            $this->delete_event_item($name, $i);
            continue;
          }
        } elseif (!class_exists($item['class'])) {
          $this->delete_event_item($name, $i);
          continue;
        } else {
          $obj = getinstance($item['class']);
          $call = array($obj, $item['func']);
        }
        try {
          $result = call_user_func_array($call, $params);
        } catch (ECancelEvent $e) {
          return $e->result;
        }
      }
    }
    
    return $result;
  }
  
  public static function cancelevent($result) {
    throw new ECancelEvent($result);
  }
  
  private function delete_event_item($name, $i) {
    array_splice($this->events[$name], $i, 1);
    if (count($this->events[$name]) == 0) unset($this->events[$name]);
    $this->save();
  }
  
  public function setevent($name, $params) {
    return $this->addevent($name, $params['class'], $params['func']);
  }
  
  public function addevent($name, $class, $func) {
    if (!in_array($name, $this->eventnames)) return $this->error(sprintf('No such %s event', $name ));
    if (empty($func)) return false;
    if (isset($this->events[$name])) {
      if ($list = $this->get_events($name)) {
        foreach ($list  as $event) {
          if (($event['class'] == $class) && ($event['func'] == $func)) return false;
        }
      }
    } else {
      $this->events[$name] =array();
    }
    
    $this->events[$name][] = array(
    'class' => $class,
    'func' => $func
    );
    $this->save();
  }
  
  public function delete_event_class($name, $class) {
    if (isset($this->events[$name])) {
      $list = &$this->events[$name];
      $deleted = false;
      for ($i = count($list) - 1; $i >= 0; $i--) {
        if ($list[$i]['class'] == $class) {
          array_splice($list, $i, 1);
          $deleted = true;
        }
      }
      if ($deleted) {
        if (count($list) == 0) unset($this->events[$name]);
        $this->save();
      }
      return $deleted;
    }
    return false;
  }
  
  public function unsubscribeclass($obj) {
    $this->unbind($obj);
  }
  
  public function unsubscribeclassname($class) {
    $this->unbind($class);
  }
  
  public function unbind($c) {
    $class = self::get_class_name($c);
    foreach ($this->events as $name => $events) {
      foreach ($events as $i => $item) {
        if ($item['class'] == $class) array_splice($this->events[$name], $i, 1);
      }
    }
    
    $this->save();
  }
  
  public function seteventorder($eventname, $c, $order) {
    if (!isset($this->events[$eventname])) return false;
    $events = &$this->events[$eventname];
    $class = self::get_class_name($c);
    $count = count($events);
    if (($order < 0) || ($order >= $count)) $order = $count - 1;
    foreach ($events as $i => $event) {
      if ($class == $event['class']) {
        if ($i == $order) return true;
        array_splice($events, $i, 1);
        array_splice($events, $order, 0, array(0 => $event));
        $this->save();
        return true;
      }
    }
  }
  
  private function indexofcoclass($class) {
    return array_search($class, $this->coclasses);
  }
  
  public function addcoclass($class) {
    if ($this->indexofcoclass($class) === false) {
      $this->coclasses[] = $class;
      $this->save();
      $this->coinstances = getinstance($class);
    }
  }
  
  public function deletecoclass($class) {
    $i = $this->indexofcoclass($class);
    if (is_int($i)) {
      array_splice($this->coclasses, $i, 1);
      $this->save();
    }
  }
  
}//class

class tevents_storage extends tevents {
  
  public function load() {
    return tstorage::load($this);
  }
  
  public function save() {
    return tstorage::save($this);
  }
  
}//class

class tcoevents extends tevents {
  private $owner;
  
  public function __construct() {
    parent::__construct();
    $a = func_get_args();
    $owner = array_shift ($a);
    $this->owner = $owner;
    if (!isset($owner->data['events'])) $owner->data['events'] = array();
    $this->events = &$owner->data['events'];
    array_splice($this->eventnames, count($this->eventnames), 0, $a);
  }
  
  public function __destruct() {
    parent::__destruct();
    unset($this->owner);
  }
  
public function assignmap() {}
protected function create() { }
public function load() {}
  public function afterload() {
    $this->events = &$this->owner->data['events'];
  }
  
  public function save() {
    return $this->owner->save();
  }
  
}//class