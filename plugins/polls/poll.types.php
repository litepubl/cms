<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpolltypes extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'polls' . DIRECTORY_SEPARATOR . 'types';
  }
  
  public function add(array $item) {
    if (!isset($item['closed'])) $item['closed'] = $this->closed;
    if (!isset($item['itemclosed'])) $item['itemclosed'] = $this->itemclosed;
    
    $this->items[$item['type']] = $item;
    $this->save();
  }
  
  public function build($type, $name, $title, array $items) {
    if (!isset($this->items[$type])) $this->error(sprintf('The "%s" type not exists', $type));
    if (count($items) == 0) $this->error('Empty poll items');
    
    $item = $this->items[$type];
    $theme = ttheme::i();
    $args = new targs();
    $args->id = '$id';
    $args->type = $type;
    $args->title = $title;
    
    $open = '';
    $close = '';
    foreach ($items as $index => $text) {
      $args->checked = 0 == $index;
      $args->index = $index;
      $args->indexplus = $index + 1;
      $args->text = $text;
      $args->votes = '$votes' . $index;
      $open .= $theme->parsearg($item['item'], $args);
      $close .= $theme->parsearg($item['itemclosed'], $args);
    }
    
    $args->id = '$id';
    $args->type = $type;
    $args->title = $title;
    $args->rate ='$rate';
    $args->worst = 1;
    $args->best = count($items);
    
    $args->items = $open;
    $opened = $theme->parsearg($item['opened'], $args);
    $args->items = $close;
    $closed = $theme->parsearg($item['closed'], $args);
    
    return array(
    'type' => $type,
    'name' => $name,
    'title' => $title,
    'items' => $items,
    'opened' => $opened,
    'closed' => $closed
    );
  }
  
}//class