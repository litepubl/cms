<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class trobotstxt extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function create() {
    parent::create();
    $this->basename = 'robots.txt';
    $this->dbversion = false;
    $this->data['idurl'] = 0;
  }
  
  public function AddDisallow($url) {
    return $this->add("Disallow: $url");
  }
  
  public function add($value) {
    if (!in_array($value, $this->items)) {
      $this->items[] = $value;
      $this->save();
      $urlmap = turlmap::i();
      $urlmap->setexpired($this->idurl);
      $this->added($value);
    }
  }
  
  public function gettext() {
    return implode("\n", $this->items);
  }
  
  public function settext($value) {
    $this->items = explode("\n", $value);
    $this->save();
  }
  
  public function request($arg) {
    $s = "<?php
    @header('Content-Type: text/plain');
    ?>";
    $s .= $this->text;
    return  $s;
  }
  
}//class