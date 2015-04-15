<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminthemes extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public static function getthemes() {
    $html = tadminhtml::i();
    $html->section = 'themes';
    return $html->ul(self::getlist($html->item, ''));
  }
  
  public static function getlist($tml, $selected) {
    $result = '';
    if (!is_array($selected)) $selected = array((string) $selected);
    $html = tadminhtml::i();
    $html->section = 'themes';
    $args = targs::i();
    $list =    tfiler::getdir(litepublisher::$paths->themes);
    sort($list);
    $args->filesurl = tadminhtml::getadminlink('/admin/views/themefiles/', 'theme');
    $parser = tthemeparser::i();
    foreach ($list as $name) {
      if ($about = $parser->getabout($name)) {
        $about['name'] = $name;
        if (!isset($about['screenshot'])) $about['screenshot'] = 'screenshot.png';
        $args->add($about);
        $args->checked = in_array($name,  $selected);
        $result .= $html->parsearg($tml, $args);
      }
    }
    return  $result;
  }
  
  public function getcontent() {
    $result = tadminviews::getviewform('/admin/views/themes/');
    $idview = tadminhtml::getparam('idview', 1);
    $view = tview::i($idview);
    $html = $this->gethtml('themes');
    $args = targs::i();
    $args->idview = $idview;
    $theme = $view->theme;
    
    $result .= $html->formheader($args);
    $result .= self::getlist($html->radioitem, $theme->name);
    $result .= $html->formfooter();
    return $html->fixquote($result);
  }
  
  public function processform() {
    $result = '';
    $idview = tadminhtml::getparam('idview', 1);
    $view = tview::i($idview);
    
    if  (isset($_POST['reparse'])) {
      $parser = tthemeparser::i();
      try {
        $parser->reparse($view->theme->name);
      } catch (Exception $e) {
        $result = $e->getMessage();
      }
    } else {
      if (empty($_POST['selection']))   return '';
      try {
        $view->themename = $_POST['selection'];
        $result = $this->html->h2->success;
      } catch (Exception $e) {
        $view->themename = 'default';
        $result = $e->getMessage();
      }
    }
    ttheme::clearcache();
    return $result;
  }
  
}//class