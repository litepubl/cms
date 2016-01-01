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

public function getcalendar($name, $date) {
    $date = datefilter::clean($date);

$args = new targs();
$args->name = $name;
$args->title = tlocal::i()->__get($name);
$args->format = dateutil::$format;

if ($date) {
$args->date = date(dateutil::$format, $date);
$args->time = date(dateutil::$timeformat, $date);
} else {
$args->date = '';
$args->time = '';
}

return $this->parsearg($this->templates['calendar'], $args);
}

}//class