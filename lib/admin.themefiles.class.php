<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminthemefiles extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public static function isfilename($filename) {
    return preg_match('/^\w[\w\.\-_]*+$/', $filename);
  }
  
  public static function file_exists($themename, $filename) {
    return self::theme_exists($themename) && self::isfilename($filename) &&
    file_exists(litepublisher::$paths->themes .$themename . DIRECTORY_SEPARATOR  . $filename);
  }
  
  public static function theme_exists($name) {
    return preg_match('/^\w[\w\.\-_]*+$/', $name) &&
    is_dir(litepublisher::$paths->themes .$name);
  }
  
  public function getcontent() {
    $themename = tadminhtml::getparam('theme', '');
    if (($themename == '') || !self::theme_exists($themename)) return tadminthemes::getthemes();
    
    $html = $this->gethtml('themefiles');
    $lang = tlocal::i('themefiles');
    $args = new targs();
    $result = sprintf($html->h4->filelist, $themename);
    $list = tfiler::getfiles(litepublisher::$paths->themes . $themename . DIRECTORY_SEPARATOR  );
    sort($list);
    $editurl = tadminhtml::getadminlink('/admin/views/themefiles/', sprintf('theme=%s&file', $themename));
    $filelist = '';
    foreach ($list as $file) {
      $filelist .= $html->li("<a href='$editurl=$file'>$file</a>");
    }
    $result .= $html->ul($filelist);
    
    if (!empty($_GET['file'])) {
      $file = $_GET['file'];
      if (!self::file_exists($themename, $file)) return $this->notfound;
      $filename = litepublisher::$paths->themes .$themename . DIRECTORY_SEPARATOR  . $file;
      $args->text = file_get_contents($filename);
      $args->formtitle = sprintf($lang->filename, $file);
      $result .= $html->adminform('[editor=text]', $args);
    }
    
    return $html->fixquote($result);
  }
  
  public function processform() {
    $result = '';
    if (empty($_GET['file']) || empty($_GET['theme'])) return '';
    if (!self::file_exists($_GET['theme'], $_GET['file'])) return '';
    if (!file_put_contents(litepublisher::$paths->themes . $_GET['theme'] . DIRECTORY_SEPARATOR . $_GET['file'], $_POST['text'])) {
      $result = $this->html->h2->errorsave;
    }
    
    ttheme::clearcache();
    return $result;
  }
  
}//class
?>