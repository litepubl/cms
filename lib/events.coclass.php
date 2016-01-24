<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class tcoevents extends tevents {
  private $owner;

  public function __construct() {
    parent::__construct();
    $a = func_get_args();
    $owner = array_shift($a);
    $this->owner = $owner;
    if (!isset($owner->data['events'])) {
$owner->data['events'] = array();
}

    $this->events = & $owner->data['events'];
    array_splice($this->eventnames, count($this->eventnames) , 0, $a);
  }

  public function __destruct() {
    parent::__destruct();
    unset($this->owner);
  }

  public function assignmap() {
  }

  protected function create() {
  }

  public function load() {
  }

  public function afterload() {
    $this->events = & $this->owner->data['events'];
  }

  public function save() {
    return $this->owner->save();
  }

} //class