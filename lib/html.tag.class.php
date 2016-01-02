<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class thtmltag {
  public $tag;
  
public function __construct($tag = '') { $this->tag = $tag; }
  public function __get($name) {
    return sprintf('<%1$s>%2$s</%1$s>', $this->tag, tlocal::i()->$name);
  }
  
}//class

class redtag extends thtmltag {
  
  public function __get($name) {
    return sprintf('<%1$s class="red">%2$s</%1$s>', $this->tag, tlocal::i()->$name);
  }
  
}//class