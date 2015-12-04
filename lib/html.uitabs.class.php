<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tuitabs {
  public $head;
  public $body;
  public $tabs;
  public $customdata;
  private static $index = 0;
  private $tabindex;
  private $items;
  
  public function __construct() {
    $this->tabindex = ++self::$index;
    $this->items = array();
    $this->head = '<li><a href="%s" role="tab"><span>%s</span></a></li>';
    $this->body = '<div id="tab-' . self::$index . '-%d" role="tabpanel">%s</div>';
    $this->tabs = '<div id="tabs-' . self::$index . '" class="admintabs" %s>
    <ul role="tablist">%s</ul>
    %s
    </div>';
    $this->customdata = false;
  }
  
  public function get() {
    $head= '';
    $body = '';
    foreach ($this->items as $i => $item) {
      if (isset($item['url'])) {
        $head .= sprintf($this->head, $item['url'], $item['title']);
      } else {
        $head .= sprintf($this->head, "#tab-$this->tabindex-$i", $item['title']);
        $body .= sprintf($this->body, $i, $item['body']);
      }
    }
    
    $data = $this->customdata? sprintf('data-custom="%s"', str_replace('"', '&quot;', json_encode($this->customdata))) : '';
    return sprintf($this->tabs, $data, $head, $body);
  }
  
  public function add($title, $body) {
    $this->items[] = array(
    'title' => $title,
    'body' => $body
    );
  }
  
  public function ajax($title, $url) {
    $this->items[] = array(
    'url' => $url,
    'title' => $title,
    );
  }
  
  public static function gethead() {
    return '<script type="text/javascript">$.inittabs();</script>';
  }
  
}//class