<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class adminform {
  public $args;
  public$title;
  public $before;
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
    $this->items = '';
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
    $this->items = $table;
    $this->items .= $this->hidden('delete', 'delete');
    $this->submit = 'delete';
    
    return $this->get();
  }
  
  public function __tostring() {
    return $this->get();
  }
  
  public function gettml() {
    $result = '<div class="form-holder">';
    if ($this->title) $result .= "<h4>$this->title</h4>\n";
    $result .= $this->before;
    
    $attr = "action=\"$this->action\"";
    foreach (array('method', 'enctype', 'target', 'id', 'class') as $k) {
      if ($v = $this->$k) $attr .= sprintf(' %s="%s"', $k, $v);
    }
    
    $result .= "<form $attr>";

if ($this->inline) {
$result .= $this->line($this->items . ($this->submit ? "[button=$this->submit]" : ''));
} else {
    $result .= $this->items;
    if ($this->submit) {
      $result .= "[submit=$this->submit]";
    }
}
    

    return strtr($this->getadmintheme()->templates['form'], array(
'$title' => $title,
'$before' => $this->before,
'attr' => $attr,
'$body' => $body,
));  }
  
  public function get() {
    return tadminhtml::i()->parsearg($this->gettml(), $this->args);
  }
  
}//class