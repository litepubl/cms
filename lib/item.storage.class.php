<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */
class titem_storage extends titem {

  public function getowner() {
    $this->error(sprintf('The "%s" no have owner', get_class($this)));
  }

  public function load() {
    $owner = $this->owner;
    if ($owner->itemexists($this->id)) {
      $this->data = & $owner->items[$this->id];
      return true;
    }
    return false;
  }

  public function save() {
    return $this->owner->save();
  }

} //class