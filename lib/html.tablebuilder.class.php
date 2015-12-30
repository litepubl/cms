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
    $this->addcallback('$tempcallback' . count($this->callbacks), array($this, 'titems_callback'), $owner);
  }

public function posts_callback(tablebuilder $self) {
$post = tpost::i($self->id);
basetheme::$vars['post'] = $post;
$self->args->poststatus = tlocal::i()->__get($post->status);
}

  public function setposts(array $struct) {
array_unshift($struct, self::checkbox('checkbox'));
$this->setstruct($struct);
    $this->addcallback('$tempcallback' . count($this->callbacks), array($this, 'posts_callback'), false);
  }

  public function props(array $props) {
    $lang = tlocal::i();
$this->setstruct(array(
array(
$lang->name,
'$name'
),

array(
$lang->property,
'$value'
)
));

    $body = '';
    $args = $this->args;
    $admintheme = admintheme::i();

    foreach ($props as $k => $v) {
      if (($k === false) || ($v === false)) continue;
      
      if (is_array($v)) {
        foreach ($v as $kv => $vv) {
          if ($k2 = $lang->__get($kv)) $kv = $k2;
$args->name = $kv;
$args->value = $vv;
      $body .= $admintheme->parsearg($this->body, $args);
        }
      } else {
        if ($k2 = $lang->__get($k)) $k = $k2;
$args->name = $k;
$args->value = $v;
      $body .= $admintheme->parsearg($this->body, $args);
      }
    }
    
    return $admintheme->gettable($this->head, $body);
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