<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tcoevents extends tevents {
  protected $owner;

  public function __construct() {
    $args = func_get_args();
    $first = array_shift($a);
if (is_callable($first)) {
$this->setcallback($first);
} else if (is_object($first) && ($first instanceof tdata)) {
$this->setowner($first);
}

if (is_array($this->eventnames)) {
    array_splice($this->eventnames, count($this->eventnames) , 0, $args);
} else {
$this->eventnames = $args;
}

    parent::__construct();
}

public function setowner(tdata $owner) {
    $this->owner = $owner;
    if (!isset($owner->data['events'])) {
$owner->data['events'] = array();
}

    $this->events = & $owner->data['events'];
  }

public function setcallback($callback) {
$callback($this);
}

  public function __destruct() {
    parent::__destruct();
    unset($this->owner);
  }

  public function assignmap() {
if (!$this->owner) {
parent::assignmap();
}
  }

  protected function create() {
if (!$this->owner) {
parent::create();
}
  }

  public function load() {
if (!$this->owner) {
return parent::load();
}
  }

  public function afterload() {
if ($this->owner) {
    $this->events = & $this->owner->data['events'];
} else {
parent::afterload();
}
  }

  public function save() {
if ($this->owner) {
    return $this->owner->save();
} else {
return parent::save();
}
  }

} //class