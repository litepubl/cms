<?php

class ttagfactory extends tdata {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getposts() {
    return tposts::i();
  }
  
  public function getpost($id) {
    return tpost::i($id);
  }
  
}//class