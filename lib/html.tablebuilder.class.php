<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tablebuilder {
  //current item in items
  public $item;
  //id or index of current item
  public $id;
  //template head and body table
  public $head;
  public $body;
  //targs
  public $args;
  public $callbacks;
  
  public function __construct() {
    $this->head = '';
    $this->body = '';
    $this->args = new targs();
    $this->callbacks = array();
  }
  
  public function setstruct(array $struct) {
    $this->body = '<tr>';
    foreach ($struct as $index => $item) {
      if (!$item || !count($item)) continue;
      
      if (count($item) == 2) {
        $colclass = 'text-left';
      } else {
        $colclass = self::getcolclass(array_shift($item));
      }
      
      $this->head .= sprintf('<th class="%s">%s</th>', $colclass, array_shift($item));
      
      $s = array_shift($item);
      if (is_string($s)) {
        $this->body .= sprintf('<td class="%s">%s</td>', $colclass, $s);
      } else if (is_callable($s)) {
        $name = '$callback' . $index;
        $this->body .= sprintf('<td class="%s">$%s</td>', $colclass, $name);
        
        array_unshift($item, $this);
        $this->callbacks[$name] = array(
        'callback'=> $s,
        'params' => $item,
        );
      } else {
        throw new Exception('Unknown column ' . var_export($s, true));
      }
    }
    
    $this->body .= '</tr>';
  }
  
  public function addcallback($varname, $callback, $param) {
    $this->callbacks[$varname] = array(
    'callback'=> $callback,
    'params' => array($this, $param),
    );
  }
  
  public function build(array $items) {
    $body = '';
    $args = $this->args;
    $admintheme = admintheme::i();
    
    foreach ($items as $id => $item) {
      if (is_array($item)) {
        $this->item = $item;
        $args->add($item);
        if (!isset($item['id'])) {
          $this->id = $id;
          $args->id = $id;
        }
      } else {
        $this->id = $item;
        $args->id = $item;
      }
      
      foreach ($this->callbacks as $name => $callback) {
        $args->data[$name] = call_user_func_array($callback['callback'], $callback['params']);
      }
      
      $body .= $admintheme->parsearg($this->body, $args);
    }
    
    return $admintheme->gettable($this->head, $body);
  }

//predefined callbacks
public function titems_callback(tablebuilder $self, titems $owner) {
$self->item = $owner->getitem($self->id);
$self->args->add($self->item);
}

public function setowner(titems $owner) {
$this->addcallback('$temp' . count($this->callbacks), array($this, 'titems_callback'), $owner);
}


  public function action($action, $adminurl) {
$title = tlocal::i()->__get($action);

    return array(
 $title,
"<a href=\"$adminurl=\$id&action=$action\">$title</a>"
);
  }
  
  public static function checkbox($name) {
    $admin = admintheme::i();
    
    return array(
    'text-center col-checkbox',
    $admin->templates['invertcheck'],
    str_replace('$name', $name, $admin->templates['checkbox'])
    );
  }
  
  public static function getcolclass($s) {
    //most case
    if (!$s || $s == 'left') {
      return 'text-left';
    }
    
    $map = array(
    'left' => 'text-left',
    'right' => 'text-right',
    'center' => 'text-center'
    );
    
    $list = explode(' ', $s);
    foreach ($list as $i => $v) {
      if (isset($map[$v])) {
        $list[$i] = $map[$v];
      }
    }
    
    return implode(' ', $list);
  }
  
}