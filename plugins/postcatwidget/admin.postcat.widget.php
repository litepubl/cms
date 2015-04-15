<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminpostcatwidget extends tadmincustomwidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $widget = tpostcatwidget::i();
    $about = tplugins::getabout(tplugins::getname(__file__));
    $args = targs::i();
    $id = (int) tadminhtml::getparam('idwidget', 0);
    if (isset($widget->items[$id])) {
      $item = $widget->items[$id];
      $args->mode = 'edit';
      $args->idwidget = $id;
    } else {
      $item = array(
      'title' => '',
      'content' => '',
      'template' => 'widget',
      'cats' => array()
      );
      $args->mode = 'add';
      $args->idwidget = 0;
    }
    
    $cats = tposteditor::getcategories($item['cats']);
    $html= $this->html;
    $html->section = 'widgets';
    $args->add($item);
    $args->widgettitle  = $item['title'];
    $args->template =tadminhtml::array2combo(self::gettemplates(), $item['template']);
    $args->formtitle = $item['title'] == '' ? $this->lang->widget : $item['title'];
    $result = $html->adminform('
    [text=widgettitle]
    [editor=content]
    [combo=template]
    [hidden=idwidget]
    [hidden=mode]' .
    sprintf('<h4>%s</h4>', $about['cats']) .
    $cats,
    $args);
    $result .= $this->getlist($widget);
    return $result;
  }
  
  public function processform()  {
    $widget = tpostcatwidget ::i();
    if (isset($_POST['mode'])) {
      extract($_POST, EXTR_SKIP);
      switch ($mode) {
        case 'add':
        $_GET['idwidget'] = $widget->add($widgettitle , $content, $template, tposteditor::processcategories());
        break;
        
        case 'edit':
        $id = isset($_GET['idwidget']) ? (int) $_GET['idwidget'] : 0;
        if ($id == 0) $id = isset($_POST['idwidget']) ? (int) $_POST['idwidget'] : 0;
        $item = $widget->items[$id];
        $item['title'] = $widgettitle ;
        $item['content'] = $content;
        $item['template'] = $template;
        $item['cats'] = tposteditor::processcategories();
        $widget->items[$id]  = $item;
        $widget->save();
        
        $widgets = twidgets::i();
        $widgets->items[$id]['title'] = $widgettitle;
        $widgets->save();
        break;
      }
    } else {
      $this->deletewidgets($widget);
    }
  }
  
}//class