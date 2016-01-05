<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class adminform {
  public $args;
  public$title;
  public $before;
  public $body;
  //items deprecated
  public $items;
  public $submit;
  public $inline;
  
  //attribs for <form>
  public $action;
  public $method;
  public $enctype;
  public $id;
  public $class;
  public $target;
  
  public function __construct($args = null) {
    $this->args = $args;
    $this->title = '';
    $this->before = '';
    $this->body = '';
    $this->items = &$this->body;
    $this->submit = 'update';
    $this->inline = false;
    
    $this->action = '';
    $this->method = 'post';
    $this->enctype = '';
    $this->id = '';
    $this->class = '';
    $this->target = '';
  }
  
  public function line($content) {
    return str_replace('$content', $content, $this->getadmintheme()->templates['inline']);
  }
  
  public function getadmintheme() {
    return admintheme::i();
  }
  
  public function __set($k, $v) {
    switch ($k) {
      case 'upload':
      if ($v) {
        $this->enctype = 'multipart/form-data';
        $this->submit = 'upload';
      } else {
        $this->enctype = '';
        $this->submit = 'update';
      }
      break;
    }
  }
  
  public function centergroup($buttons) {
    return str_replace('$buttons', $buttons, $this->getadmintheme()->templates['centergroup']);
  }
  
  public function hidden($name, $value) {
    return sprintf('<input type="hidden" name="%s" value="%s" />', $name, $value);
  }
  
  public function getdelete($table) {
    $this->body = $table;
    $this->body .= $this->hidden('delete', 'delete');
    $this->submit = 'delete';
    
    return $this->get();
  }
  
  public function __tostring() {
    return $this->get();
  }
  
  public function gettml() {
    $admin = $this->getadmintheme();
    $title = $this->title ? str_replace('$title', $this->title, $admin->templates['form.title']) : '';
    
    $attr = "action=\"$this->action\"";
    foreach (array('method', 'enctype', 'target', 'id', 'class') as $k) {
      if ($v = $this->$k) $attr .= sprintf(' %s="%s"', $k, $v);
    }
    
    $theme = ttheme::i();
    $lang = tlocal::i();
    $body = $this->body;
    
    if ($this->inline) {
      if ($this->submit) {
        $body .= $theme->getinput('button', $this->submit, '', $lang->__get($this->submit));
      }
      
      $body = $this->line($body);
    } else {
      if ($this->submit) {
        $body .= $theme->getinput('submit', $this->submit, '', $lang->__get($this->submit));
      }
    }
    
    return strtr($admin->templates['form'], array(
    '$title' => $title,
    '$before' => $this->before,
    '$attr' => $attr,
    '$body' => $body,
    ));
  }
  
  public function get() {
    return tadminhtml::i()->parsearg($this->gettml(), $this->args);
  }
  
}//class