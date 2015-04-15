<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminform extends tevents implements itemplate {
  protected $formresult;
  protected $title;
  protected $section;
  
  public function gettitle() {
    return tlocal::get($this->section, 'title');
  }
  
public function gethead() {}
public function getkeywords() {}
public function getdescription() {}
  
  public function getidview() {
    return tviews::i()->defaults['admin'];
  }
  
public function setidview($id) {}
  
  public function request($arg) {
    $this->cache = false;
    tlocal::usefile('admin');
    $this->formresult = '';
    if (tguard::post()) {
      $this->formresult = $this->processform();
    }
  }
  
  public function processform() {
    return '';
  }
  
  public function getcont() {
    $result = $this->formresult;
    $result .= $this->getcontent();
    $theme = ttheme::i();
    return $theme->simple($result);
  }
  
  public function gethtml() {
    $result = tadminhtml ::i();
    $result->section = $this->section;
    $lang = tlocal::admin($this->section);
    return $result;
  }
  
}//class
?>