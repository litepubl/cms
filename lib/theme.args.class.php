<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

class targs {
  public $data;
  public $callbacks;
  public $callback_params;

  public static function i() {
    return litepublisher::$classes->newinstance(__class__);
  }

  public function __construct($thisthis = null) {
    $this->callbacks = array();
    $this->callback_params = array();

    if (!isset(basetheme::$defaultargs)) {
      basetheme::set_defaultargs();
    }

    $this->data = basetheme::$defaultargs;
    if (isset($thisthis)) $this->data['$this'] = $thisthis;
  }

  public function __get($name) {
    if (($name == 'link') && !isset($this->data['$link']) && isset($this->data['$url'])) {
      return litepublisher::$site->url . $this->data['$url'];
    }

    return $this->data['$' . $name];
  }

  public function __set($name, $value) {
    if (!$name || !is_string($name)) return;
    if (is_array($value)) return;

    if (!is_string($value) && is_callable($value)) {
      $this->callbacks['$' . $name] = $value;
      return;
    }

    if (is_bool($value)) {
      $value = $value ? 'checked="checked"' : '';
    }

    $this->data['$' . $name] = $value;
    $this->data["%%$name%%"] = $value;

    if (($name == 'url') && !isset($this->data['$link'])) {
      $this->data['$link'] = litepublisher::$site->url . $value;
      $this->data['%%link%%'] = litepublisher::$site->url . $value;
    }
  }

  public function add(array $a) {
    foreach ($a as $k => $v) {
      $this->__set($k, $v);
      if ($k == 'url') {
        $this->data['$link'] = litepublisher::$site->url . $v;
        $this->data['%%link%%'] = litepublisher::$site->url . $v;
      }
    }

    if (isset($a['title']) && !isset($a['text'])) $this->__set('text', $a['title']);
    if (isset($a['text']) && !isset($a['title'])) $this->__set('title', $a['text']);
  }

  public function parse($s) {
    return basetheme::i()->parsearg($s, $this);
  }

  public function callback($s) {
    if (!count($this->callbacks)) return $s;

    $params = $this->callback_params;
    array_unshift($params, $this);

    foreach ($this->callbacks as $tag => $callback) {
      $s = str_replace($tag, call_user_func_array($callback, $params) , $s);
    }

    return $s;
  }

} //class