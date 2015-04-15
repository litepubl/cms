<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcssmerger extends tfilemerger {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'cssmerger';
  }
  
  public function replaceurl($m) {
    $url = $m[1];
    if (strbegin($url, 'data:')) return " url(\"$url\")";
    $args = '';
    if ($i = strpos($url, '?')) {
      $args = substr($url, $i);
      $url = substr($url, 0, $i);
    }
    
    if($realfile = realpath($url)) {
      $url = substr($realfile, strlen(litepublisher::$paths->home));
    } // else must be absolute url
    
    $url = str_replace(DIRECTORY_SEPARATOR, '/', $url);
    $url = litepublisher::$site->files. '/' .  ltrim($url, '/');
    $url = substr($url, strpos($url, '/', 9));
    return " url('$url$args')";
  }
  
  public function readfile($filename) {
    if ($result = parent::readfile($filename)) {
      chdir(dirname($filename));
      $result = preg_replace_callback('/\s*url\s*\(\s*[\'"]?(.*?)[\'"]?\s*\)/i',
      array($this, 'replaceurl'), $result);
      
      //delete comments
      $result = preg_replace('/\/\*.*?\*\//ims', '', $result);
      return $result;
    }
  }
  
  public function getfilename($section, $revision) {
    return sprintf('/files/js/%s.%s.css', $section, $revision);
  }
  
  public function addstyle($filename) {
    if (!($filename = $this->normfilename($filename))) return false;
    $template = ttemplate::i();
    if (strpos($template->heads, $this->basename . '_default')) {
      $this->add('default', $filename);
    } else {
      $template->addtohead(sprintf('<link type="text/css" href="$site.files%s" rel="stylesheet" />', $filename));
    }
  }
  
  public function deletestyle($filename) {
    if (!($filename = $this->normfilename($filename))) return false;
    $template = ttemplate::i();
    if (strpos($template->heads, $this->basename . '_default')) {
      $this->deletefile('default', $filename);
    } else {
      $template->deletefromhead(sprintf('<link type="text/css" href="$site.files%s" rel="stylesheet" />', $filename));
    }
  }
  
}//class