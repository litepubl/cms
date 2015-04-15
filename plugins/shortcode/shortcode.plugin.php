<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tshortcode extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'shortcodes';
  }
  
  public function filter(&$content) {
    foreach ($this->items as $code => $tml) {
      $content = str_replace("[$code]", $tml, $content);
      if (preg_match_all("/\[$code\=(.*?)\]/", $content, $m, PREG_SET_ORDER)) {
        foreach ($m as $item) {
          $value =         str_replace('$value', $item[1], $tml);
          $content = str_replace($item[0], $value, $content);
        }
      }
    }
  }
  
}