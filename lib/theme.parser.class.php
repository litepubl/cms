<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tthemeparser extends baseparser  {
  private $sidebar_index;

  public static function i() {
    return getinstance(__class__);
  }

  protected function create() {
    parent::create();
    $this->basename = 'themeparser';
        $this->sidebar_index = 0;
}

  public function doreplacelang(basetheme $theme) {
parent::doreplacelang($theme);

    foreach ($theme->templates['sidebars'] as &$sidebar) {
      unset($widget);
      foreach ($sidebar as &$widget) {
        $widget = $theme->replacelang($widget, $lang);
      }
    }
    
  }
  
  public function getfile($filename, $about) {
if ($s = parent::getfile($filename, $about)) {
    //fix some old tags
    $s = strtr($s, array(
    '$options.url$url' => '$link',
    '$post.categorieslinks' => '$post.catlinks',
    '$post.tagslinks' => '$post.taglinks',
    '$post.subscriberss' => '$post.rsslink',
    '$post.excerptcategories' => '$post.excerptcatlinks',
    '$post.excerpttags' => '$post.excerpttaglinks',
    '$options' => '$site',
    '$template.sitebar' => '$template.sidebar',
    '<!--sitebar-->' => '<!--sidebar-->',
    '<!--/sitebar-->' => '<!--/sidebar-->'
    ));
}

return $s;
}

