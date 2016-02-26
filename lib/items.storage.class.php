<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class titems_storage extends titems {

  public function load() {
    return tstorage::load($this);
  }

  public function save() {
    return tstorage::save($this);
  }

} //class