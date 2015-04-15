<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tini2array {
  
  public static function extractvalue(&$lines, &$name, &$value) {
    $s = '';
    while (($s == '') && (count($lines) > 0)) {
      $s = ltrim(array_shift($lines));
      if (isset($s[0]) && ($s[0] == ';')) $s = '';
    }
    if (($s == '') && (count($lines) == 0)) return false;
    if (!($i = strpos($s, '='))) {
      array_unshift($lines, $s);
      return false;
    }
    
    $name = trim(substr($s, 0, $i));
    $value = ltrim(substr($s, $i + 1));
    if (!$value) return '';
    $q = $value[0];
    if (($q == '"') || ($q == "'")) {
      $s = substr($value, 1);
      $value = '';
      $r = sprintf('/(?<!\\\\)%s(\s*?)?$/', $q);
      while (!preg_match($r, $s) && (count($lines) > 0)) {
        $value .= $s . "\n";
        $s= array_shift($lines);
      }
      
      $s = rtrim($s);
      if (preg_match($r, $s)) $s = substr($s, 0, strlen($s) - 1);
      $value .= $s;
      $value = str_replace("\\$q", $q, $value);
      return true;
    } else {
      $value  = trim($value);
      return true;
    }
  }
  
  public static function parse($s) {
    $result  =array();
    $s = str_replace(array("\r\n", "\r"), "\n", trim($s));
    $lines = explode("\n", $s);
    while (count($lines) > 0) {
      $line = array_shift($lines);
      $sectionname = trim(trim($line), '[] ');
      $section = array();
      $name = '';
      $value = '';
      while (self::extractvalue($lines, $name, $value)) {
        $section[$name] = $value;
      }
      
      if (isset($result[$sectionname])) {
        if (!isset($result[$sectionname][0]))  $result[$sectionname] = array(0 => $result[$sectionname]);
        $result[$sectionname][] = $section;
      } else {
        $result[$sectionname] = $section;
      }
    }
    
    return $result;
  }
  
  public static function parsesection($s) {
    $result  =array();
    $s = str_replace(array("\r\n", "\r"), "\n", trim($s));
    $lines = explode("\n", $s);
    $name = '';
    $value = '';
    while (self::extractvalue($lines, $name, $value)) {
      $result[$name] = $value;
    }
    return $result;
  }
  
}//class