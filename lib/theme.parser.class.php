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

protected function preparetag($name) {
      $name = parent::preparetag($name);
      if (strbegin($name, 'sidebar')) {
        if (preg_match('/^sidebar(\d)\.?/', $name, $m)) {
          $this->sidebar_index = (int) $m[1];
        } else {
          $this->sidebar_index = 0;
        }
        
        if (!isset($this->theme->templates['sidebars'][$this->sidebar_index])) {
          $this->theme->templates['sidebars'][$this->sidebar_index] = array();
        }
      }
      
return $name;
}

protected function setvalue($name, $value) {
        if (strbegin($name, 'sidebar')) {
          $this->setwidgetvalue($name, $s);
        }  elseif (isset($this->paths[$name])) {
          $this->set_value($name, $s);
        } elseif (($name == '') || ($name == '$template')) {
          $this->theme->templates['index'] = $s;
        } elseif (strbegin($name, '$custom') || strbegin($name, 'custom')) {
          $this->setcustom($name, $s);
        } else {
          $this->error("The '$name' tag not found. Content \n$s");
        }
}

      public function set_value($name, $value) {
        $this->parsedtags[] = $name;
        switch ($name) {
          case 'content.menu':
          //fix old ver
          $this->theme->templates['content.author'] = str_replace('menu', 'author', $value);
          break;
          
          case 'menu.item':
          $this->theme->templates['menu.single'] = $value;
          $this->theme->templates['menu.current'] = $value;
          break;
        }
        
        $this->theme->templates[$name] = $value;
      }
      
      public function getinfo($name, $child) {
        if (strbegin($child,  '$template.sidebar') && (substr_count($child, '.') == 1)) {
          return array(
          'path' => substr($child, strlen('$template.')),
          'tag' => $child,
          'replace' => $child
          );
        }
        
        if (($name == '') || ($child == '$template')) return 'index';
        if (strbegin($name, '$template.')) $name = substr($name, strlen('$template.'));
        if ($name == '$template') $name = '';
        
        foreach ($this->paths as $path => $info) {
          if (strbegin($path, $name)) {
            if ($child == $info['tag']) {
              $info['path'] = $path;+
              return $info;
            }
          }
        }
        
        $child = substr($child, 1);
        $path = $name . '.' . $child;
        if (strbegin($name, 'sidebar')) {
          return array(
          'path' => $path,
          'tag' => $child,
          'replace' => $child == '$classes' ? '' : $child
          );
        }
        
        if (strbegin($name, '$custom') || strbegin($name, 'custom')) {
          return array(
          'path' => $path,
          'tag' => $child,
          'replace' => ''
          );
        }
        
        $this->error("The '$child' not found in path '$name'");
      }
