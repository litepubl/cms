<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class admintheme extends basetheme {
  
  public static function i() {
    $result = getinstance(__class__);
    if (!$result->name && ($context = litepublisher::$urlmap->context)) {
      $result->name = tview::getview($context)->adminname;
      $result->load();
    }
    
    return $result;
  }
  
  public static function getinstance($name) {
    return self::getbyname(__class__, $name);
  }
  
  public function getparser() {
    return adminparser::i();
  }
  
  public function gettable($head, $body) {
    return strtr($this->templates['table'], array(
    '$class' => ttheme::i()->templates['content.admin.tableclass'],
    '$head' => $head,
    '$body' => $body
    ));
  }
  
  public function getsection($title, $content) {
    return strtr($this->templates['section'], array(
    '$title' => $title,
    '$content' => $content
    ));
  }
  
}//class