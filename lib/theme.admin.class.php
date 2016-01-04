<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
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

  public function geterr($content) {
    return strtr($this->templates['error'], array(
    '$title' => tlocal::i()->error,
    '$content' => $content
    ));
}
  
  public function getcalendar($name, $date) {
    $date = datefilter::timestamp($date);
    
    $args = new targs();
    $args->name = $name;
    $args->title = tlocal::i()->__get($name);
    $args->format = datefilter::$format;
    
    if ($date) {
      $args->date = date(datefilter::$format, $date);
      $args->time = date(datefilter::$timeformat, $date);
    } else {
      $args->date = '';
      $args->time = '';
    }
    
    return $this->parsearg($this->templates['calendar'], $args);
  }
  
  public function getdaterange($from, $to) {
    $from = datefilter::timestamp($from);
    $to = datefilter::timestamp($to);
    
    $args = new targs();
    $args->from = $from ? date(datefilter::$format, $from) : '';
    $args->to = $to ? date(datefilter::$format, $to) : '';
    $args->format = datefilter::$format;
    
    return $this->parsearg($this->templates['daterange'], $args);
  }

  public function proplist($tml, array $props) {
    $result = '';
    if (!$tml) $tml = '<li>%s: %s</li>';
    // exclude props with int keys
    $tml_int = '<li>%s</li>';
    
    foreach ($props as $prop => $value) {
      if ($value === false) continue;
      if (is_array($value)) {
        $value = $this->proplist($tml, $value);
      }
      
      if (is_int($prop)) {
        $result .= sprintf($tml_int, $value);
      } else {
        $result .= sprintf($tml, $prop, $value);
      }
    }
    
    return $result ? sprintf('<ul>%s</ul>', $result) : '';
  }
  
  public function linkproplist(array $props) {
    return $this->proplist('<li><a href="' . litepublisher::$site->url . '%s">%s</a></li>', $props);
  }
  
}//class