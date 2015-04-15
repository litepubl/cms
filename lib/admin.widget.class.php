<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminwidget extends tdata {
  public $widget;
  protected $html;
  protected $lang;
  
  protected function create() {
    //parent::i();
    $this->html = tadminhtml ::i();
    $this->html->section = 'widgets';
    $this->lang = tlocal::i('widgets');
  }
  
  protected function getadminurl() {
    return litepublisher::$site->url . '/admin/views/widgets/' . litepublisher::$site->q . 'idwidget=';
  }
  
  protected function dogetcontent(twidget $widget, targs $args){
    $this->error('Not implemented');
  }
  
  protected function optionsform($widgettitle, $content) {
    $args = targs::i();
    $args->formtitle = $widgettitle . ' ' . $this->lang->widget;
    $args->title = $widgettitle;
    $args->items = $this->html->getedit('title', $widgettitle, $this->lang->widgettitle) . $content;
    return $this->html->parsearg(ttheme::i()->templates['content.admin.form'], $args);
  }
  
  public function getcontent(){
    return $this->optionsform(
    $this->widget->gettitle($this->widget->id),
    $this->dogetcontent($this->widget, targs::i()));
  }
  
  public function processform()  {
    $widget = $this->widget;
    $widget->lock();
    if (isset($_POST['title'])) $widget->settitle($widget->id, $_POST['title']);
    $this->doprocessform($widget);
    $widget->unlock();
    return $this->html->h2->updated;
  }
  
  protected function doprocessform(twidget $widget)  {
    $this->error('Not implemented');
  }
  
}//class

class tadmintagswidget extends tadminwidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function dogetcontent(twidget $widget, targs $args){
    $args->showcount = $widget->showcount;
    $args->showsubitems = $widget->showsubitems;
    $args->maxcount = $widget->maxcount;
    $args->sort = tadminhtml::array2combo(tlocal::i()->ini['sortnametags'], $widget->sortname);
    return $this->html->parsearg('[combo=sort] [checkbox=showsubitems] [checkbox=showcount] [text=maxcount]', $args);
  }
  
  protected function doprocessform(twidget $widget)  {
    extract($_POST, EXTR_SKIP);
    $widget->maxcount = (int) $maxcount;
    $widget->showcount = isset($showcount);
    $widget->showsubitems = isset($showsubitems);
    $widget->sortname = $sort;
  }
  
}//class

class tadminmaxcount extends tadminwidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function dogetcontent(twidget $widget, targs $args){
    $args->maxcount = $widget->maxcount;
    return $this->html->parsearg('[text=maxcount]', $args);
  }
  
  protected function doprocessform(twidget $widget)  {
    $widget->maxcount = (int) $_POST['maxcount'];
  }
  
}//class

class tadminshowcount extends tadminwidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function dogetcontent(twidget $widget, targs $args){
    $args->showcount = $widget->showcount;
    return $this->html->parsearg('[checkbox=showcount]', $args);
  }
  
  protected function doprocessform(twidget $widget)  {
    $widget->showcount = isset($_POST['showcount']);
  }
  
}//class

class tadminorderwidget extends tadminwidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function dogetcontent(twidget $widget, targs $args){
    $idview = tadminhtml::getparam('idview', 1);
    $view = tview::i($idview);
    $args->sidebar = tadminhtml::array2combo(tadminwidgets::getsidebarnames($view), $widget->sidebar);
    $args->order = tadminhtml::array2combo(range(-1, 10), $widget->order + 1);
    $args->ajax = $widget->ajax;
    return $this->html->parsearg('[combo=sidebar] [combo=order] [checkbox=ajax]', $args);
  }
  
  protected function doprocessform(twidget $widget)  {
    $widget->sidebar = (int) $_POST['sidebar'];
    $widget->order = ((int) $_POST['order'] - 1);
    $widget->ajax = isset($_POST['ajax']);
  }
  
}//class

class tadmincustomwidget extends tadminwidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public static function gettemplates() {
    $result = array();
    $lang = tlocal::i('widgets');
    $result['widget'] = $lang->defaulttemplate;
    foreach (ttheme::getwidgetnames() as $name) {
      $result[$name] = $lang->$name;
    }
    return $result;
  }
  
  public function getcontent() {
    $widget = $this->widget;
    $args = new targs();
    $id = (int) tadminhtml::getparam('idwidget', 0);
    if (isset($widget->items[$id])) {
      $item = $widget->items[$id];
      $args->mode = 'edit';
      $viewcombo = '';
    } else {
      $id = 0;
      $viewcombo = tadminviews::getcomboview(1);
      $args->mode = 'add';
      $item = array(
      'title' => '',
      'content' => '',
      'template' => 'widget'
      );
    }
    
    $args->idwidget = $id;
    $html= $this->html;
    $args->text = $item['content'];
    $args->template =tadminhtml::array2combo(self::gettemplates(), $item['template']);
    $result = $this->optionsform($item['title'], $viewcombo . $html->parsearg(
    '[editor=text]
    [combo=template]
    [hidden=mode]
    [hidden=idwidget]',
    $args));
    
    $lang = tlocal::i();
    $args->formtitle = $lang->widgets;
    $args->table = $html->buildtable($widget->items, array(
    $html->get_table_checkbox('widgetcheck'),
    array('left', $lang->widgettitle, "<a href=\"$this->adminurl\$id\" title=\"\$title\">\$title</a>"),
    ));
    
    $result .= $html->deletetable($args);
    return $result;
  }
  
  public function processform()  {
    $widget = $this->widget;
    if (isset($_POST['mode'])) {
      extract($_POST, EXTR_SKIP);
      switch ($mode) {
        case 'add':
        $_GET['idwidget'] = $widget->add($idview, $title, $text, $template);
        break;
        
        case 'edit':
        $id = isset($_GET['idwidget']) ? (int) $_GET['idwidget'] : 0;
        if ($id == 0) $id = isset($_POST['idwidget']) ? (int) $_POST['idwidget'] : 0;
        $widget->edit($id, $title, $text, $template);
        break;
      }
    } elseif (isset($_POST['delete']))  {
      $this->deletewidgets($widget);
    }
  }
  
  public function deletewidgets(twidget $widget) {
    $widgets = twidgets::i();
    $widgets->lock();
    $widget->lock();
    foreach ($_POST as $key => $value) {
      if (strbegin($key, 'widgetcheck-')) $widget->delete((int) $value);
    }
    $widget->unlock();
    $widgets->unlock();
  }
  
}//class
class tadminlinkswidget extends tadminwidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function dogetcontent(twidget $widget, targs $args){
    $args->redir = $widget->redir;
    return $this->html->parsearg('[checkbox=redir]', $args);
  }
  
  public function getcontent() {
    $result = parent::getcontent();
    $widget = $this->widget;
    $html= $this->html;
    $args = new targs();
    $id = isset($_GET['idlink']) ? (int) $_GET['idlink'] : 0;
    if (isset($widget->items[$id])) {
      $item = $widget->items[$id];
      $args->mode = 'edit';
    } else {
      $args->mode = 'add';
      $item = array(
      'url' => '',
      'linktitle' => '',
      'text' => ''
      );
    }
    
    $args->add($item);
    $args->linktitle = isset($item['title']) ? $item['title'] : (isset($item['linktitle']) ? $item['linktitle'] : '');
    $lang = tlocal::i();
    $args->formtitle = $lang->editlink;
    $result .= $html->adminform('
    [text=url]
    [text=text]
    [text=linktitle]
    [hidden=mode]', $args);
    
    $adminurl = $this->adminurl . $_GET['idwidget'] . '&idlink';
    $args->table = $html->buildtable($widget->items, array(
    $html->get_table_checkbox('checklink'),
    array('left', $lang->url, '<a href=\'$url\'>$url</a>'),
    array('left', $lang->anchor, '$text'),
    array('left', $lang->description, '$title'),
    array('center', $lang->edit, "<a href='$adminurl=\$id'>$lang->edit</a>"),
    ));
    
    $result .= $html->deletetable($args);
    return $result;
  }
  
  public function processform()  {
    $widget = $this->widget;
    $widget->lock();
    if (isset($_POST['delete'])) {
      foreach ($_POST as $key => $value) {
        $id = (int) $value;
        if (isset($widget->items[$id]))  $widget->delete($id);
      }
    } elseif (isset($_POST['mode'])) {
      extract($_POST, EXTR_SKIP);
      switch ($mode) {
        case 'add':
        $_GET['idlink'] = $widget->add($url, $linktitle, $text);
        break;
        
        case 'edit':
        $widget->edit((int) $_GET['idlink'], $url, $linktitle, $text);
        break;
      }
    } else {
      extract($_POST, EXTR_SKIP);
      $widget->settitle($widget->id, $title);
      $widget->redir = isset($redir);
    }
    $widget->unlock();
    return $this->html->h2->updated;
  }
  
}//class

class tadminmetawidget extends tadminwidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function dogetcontent(twidget $widget, targs $args){
    $html = $this->html;
    $result = '';
    foreach ($widget->items as $name => $item) {
      $result .= $html->getinput('checkbox', $name, $item['enabled'] ? 'checked="checked"' : '', $item['title']);
    }
    return $result;
  }
  
  protected function doprocessform(twidget $widget)  {
    foreach ($widget->items as $name => $item) {
      $widget->items[$name]['enabled'] = isset($_POST[$name]);
    }
  }
  
}//class

?>