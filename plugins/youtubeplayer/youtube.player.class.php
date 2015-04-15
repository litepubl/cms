<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tyoutubeplayer extends tplugin {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->data['template'] ='<object width="425" height="350">' .
    '<param name="movie" value="http://www.youtube.com/v/$id?fs=1&amp;rel=0"></param>' .
    //'<param name="wmode" value="transparent"></param>' .
    '<param name="allowFullScreen" value="true"></param>' .
    '<param name="allowscriptaccess" value="always"></param>' .
    '<embed src="http://www.youtube.com/v/$id?fs=1&amp;rel=0" ' .
    'type="application/x-shockwave-flash" ' .
    //'wmode="transparent" ' .
    'allowscriptaccess="always" ' .
    'allowfullscreen="true" ' .
    'width="425" height="350">' .
    '</embed></object>';
  }
  
  public function filter(&$content) {
    if (preg_match_all(
  "/\[youtube\=http:\/\/([a-zA-Z0-9\-\_]+\.|)youtube\.com\/watch(\?v\=|\/v\/|#!v=)([a-zA-Z0-9\-\_]{11})([^<\s]*)\]/",
  //"/\[youtube\=http:\/\/([a-zA-Z0-9\-\_]+\.|)youtube\.com\/watch(\?v\=|\/v\/)([a-zA-Z0-9\-\_]{11})([^<\s]*)\]/",
    $content, $m, PREG_SET_ORDER)) {
      foreach ($m as $item) {
        $id = $item[3];
        $content = str_replace($item[0],
        str_replace('$id', $id, $this->template), $content);
      }
    }
    
    if (preg_match_all('/http:\/\/youtu\.be\/(\w*+)/',
    $content, $m, PREG_SET_ORDER)) {
      foreach ($m as $item) {
        $id = $item[1];
        $content = str_replace($item[0],
        str_replace('$id', $id, $this->template), $content);
      }
    }
  }
  
  public function install() {
    tcontentfilter::i()->afterfilter = $this->filter;
  }
  
  public function uninstall() {
    tcontentfilter::i()->unbind($this);
  }
  
}//class