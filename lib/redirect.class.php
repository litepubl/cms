<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tredirector extends titems {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'redirector';
    $this->addevents('onget');
  }
  
  public function add($from, $to) {
    $this->items[$from] = $to;
    $this->save();
    $this->added($from);
  }
  
  public function get($url) {
    if (isset($this->items[$url])) return $this->items[$url];
    if (strbegin($url, litepublisher::$site->url)) return substr($url, strlen(litepublisher::$site->url));
    
    //redir jquery scripts
    if (strbegin($url, '/js/jquery/ui-')) return '/js/jquery/ui-' . litepublisher::$site->jqueryui_version . substr($url, strpos($url, '/', 15));
    if (strbegin($url, '/js/jquery/jquery')) return '/js/jquery/jquery-' . litepublisher::$site->jquery_version . '.min.js';
    
    //fix for 2.xx versions
    if (preg_match('/^\/comments\/(\d*?)\/?$/', $url, $m)) return sprintf('/comments/%d.xml', $m[1]);
    if (preg_match('/^\/authors\/(\d*?)\/?$/', $url, $m)) return '/comusers.htm?id=' . $m[1];
    
    if (strpos($url, '%')) {
      $url = rawurldecode($url);
      if (strbegin($url, litepublisher::$site->url)) return substr($url, strlen(litepublisher::$site->url));
      if (litepublisher::$urlmap->urlexists($url)) return $url;
    }
    
    //fix php warnings e.g. function.preg-split
    if (($i = strrpos($url, '/')) && strbegin(substr($url, $i), '/function.')) {
      return substr($url, 0, $i + 1);
    }
    
    //redir version js files
    if (preg_match('/^\/files\/js\/(\w*+)\.(\d*+)\.js$/', $url, $m)) {
      $name = $m[1] == 'moderator' ? 'comments' : $m[1];
      $prop = 'jsmerger_' . $name;
      $template = ttemplate::i();
      if (isset($template->$prop)) return $template->$prop;
    }
    
    if (preg_match('/^\/files\/js\/(\w*+)\.(\d*+)\.css$/', $url, $m)) {
      $name = 'cssmerger_' . $m[1];
      $template = ttemplate::i();
      if (isset($template->$name)) return $template->$name;
    }
    
    if ($url = $this->onget($url)) return $url;
    return false;
  }
  
}//class