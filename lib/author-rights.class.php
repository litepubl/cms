<?php

class tauthor_rights extends tevents {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->addevents('gethead', 'getposteditor', 'editpost', 'changeposts', 'canupload', 'candeletefile');
    $this->basename = 'authorrights';
  }