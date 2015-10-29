<?php
class adminform {
  public $args;
  public$title;
  public $items;
  public $action;
  public $method;
  public $enctype;
  public $id;
  public $class;
  public $target;
  public $submit;
  public $inlineclass;
  
  public function __construct($args = null) {
    $this->args = $args;
    $this->title = '';
    $this->items = '';
    $this->action = '';
    $this->method = 'post';
    $this->enctype = '';
    $this->id = '';
    $this->class = '';
    $this->target = '';
    $this->submit = 'update';
    $this->inlineclass = 'form-inline';
  }
  
  public function line($s) {
    return "<div class=\"$this->inlineclass\">$s</div>";
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
      
      case 'inline':
      $this->class = $v ? $this->inlineclass : '';
      break;
    }
  }
  
  public function __tostring() {
    return $this->get();
  }
  
  public function gettml() {
    $result = '<div class="form-holder">';
    if ($this->title) $result .= "<h4>$this->title</h4>\n";
    $attr = "action=\"$this->action\"";
    foreach (array('method', 'enctype', 'target', 'id', 'class') as $k) {
      if ($v = $this->$k) $attr .= sprintf(' %s="%s"', $k, $v);
    }
    
    $result .= "<form $attr role=\"form\">";
    $result .= $this->items;
    if ($this->submit) $result .= $this->class == $this->inlineclass ? "[button=$this->submit]" : "[submit=$this->submit]";
    $result .= "\n</form>\n</div>\n";
    return $result;
  }
  
  public function get() {
    return tadminhtml::i()->parsearg($this->gettml(), $this->args);
  }
  
}//class