<?php

class tevents_storage extends tevents {
  
  public function load() {
    return tstorage::load($this);
  }
  
  public function save() {
    return tstorage::save($this);
  }
  
}//class