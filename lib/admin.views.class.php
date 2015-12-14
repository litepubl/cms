<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tadminviews extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public static function getviewform($url) {
    $html = tadminhtml ::i();
    $lang = tlocal::admin();
    $args = new targs();
    $args->idview = self::getcombo(tadminhtml::getparam('idview', 1));
    $form = new adminform($args);
    $form->action = litepublisher::$site->url . $url;
    $form->inline = true;
    $form->method = 'get';
    $form->items = '[combo=idview]';
    $form->submit = 'select';
    return $form->get();
  }
  
  public static function getcomboview($idview, $name = 'idview') {
    $lang = tlocal::i();
    $lang->addsearch('views');
    $theme = ttheme::i();
    return strtr($theme->templates['content.admin.combo'], array(
    '$lang.$name' => $lang->view,
    '$name' => $name,
    '$value' => self::getcombo($idview)
    ));
  }
  
  public static function getcombo($idview) {
    $result = '';
    $views = tviews::i();
    foreach ($views->items as $id => $item) {
      $result .= sprintf('<option value="%d" %s>%s</option>', $id,
      $idview == $id ? 'selected="selected"' : '', $item['name']);
    }
    return $result;
  }
  
  public static function replacemenu($src, $dst) {
    $views = tviews::i();
    foreach ($views->items as &$viewitem) {
      if ($viewitem['menuclass'] == $src) $viewitem['menuclass'] = $dst;
    }
    $views->save();
  }
  
  private function get_custom(tview $view) {
    $result = '';
    $html = $this->html;
    $customadmin = $view->theme->templates['customadmin'];
    foreach ($view->data['custom'] as $name => $value) {
      if (!isset($customadmin[$name])) continue;
      switch ($customadmin[$name]['type']) {
        case 'text':
        case 'editor':
        $value = tadminhtml::specchars($value);
        break;
        
        case 'checkbox':
        $value = $value ? 'checked="checked"' : '';
        break;
        
        case 'combo':
        $value = tadminhtml  ::array2combo($customadmin[$name]['values'], $value);
        break;
        
        case 'radio':
        $value = $html->getradioitems(    "custom-$name", $customadmin[$name]['values'], $value);
        break;
      }
      
      $result .= $html->getinput(
      $customadmin[$name]['type'],
      "custom-$name",
      $value,
      tadminhtml::specchars($customadmin[$name]['title'])
      );
    }
    return $result;
  }
  
  private function set_custom($idview) {
    $view = tview::i($idview);
    if (count($view->custom) == 0) return;
    $customadmin = $view->theme->templates['customadmin'];
    foreach ($view->data['custom'] as $name => $value) {
      if (!isset($customadmin[$name])) continue;
      switch ($customadmin[$name]['type']) {
        case 'checkbox':
        $view->data['custom'][$name] = isset($_POST["custom-$name"]);
        break;
        
        case 'radio':
        $view->data['custom'][$name] = $customadmin[$name]['values'][(int) $_POST["custom-$name"]];
        break;
        
        default:
        $view->data['custom'][$name] = $_POST["custom-$name"];
        break;
      }
    }
  }
  
  public function getcontent() {
    $result = '';
    $views = tviews::i();
    $html = $this->html;
    $lang = tlocal::i('views');
    $args = new targs();
    
    switch ($this->name) {
      case 'views':
      $html->addsearch('views');
      $lang->addsearch('views');
      
      $id = tadminhtml::getparam('idview', 0);
      if (!$id || !$views->itemexists($id)) {
        $adminurl = $this->adminurl . 'view';
        return $html->h4($html->getlink($this->url . '/addview/', $lang->add)) .
        $html->buildtable($views->items, array(
        array('left', $lang->name, "<a href=\"$adminurl=\$id\">\$name</a>"),
      array('center', $lang->widgets, "<a href=\"{$this->link}widgets/?idview=\$id\">$lang->widgets</a>"),
        array('center', $lang->delete, "<a href=\"$adminurl=\$id&action=delete\" class=\"confirm-delete-link\">$lang->delete</a>"),
        ));
      }
      
      $result = self::getviewform($this->url);
      $tabs = new tuitabs();
      $menuitems = array();
      foreach ($views->items as $itemview) {
        $class = $itemview['menuclass'];
        $menuitems[$class] = $class == 'tmenus' ? $lang->stdmenu : ($class == 'tadminmenus' ? $lang->adminmenu : $class);
      }
      
      $itemview = $views->items[$id];
      $args->add($itemview);


    $dirlist =    tfiler::getdir(litepublisher::$paths->themes);
    sort($dirlist);
$list = array();
foreach ($dirlist as $dir) {
if (!strbegin($dir, 'admin')) $list[$dir] = $dir;
}

$args->themename =tadminhtml  ::array2combo($list, $itemview['themename']);

$list = array();
foreach ($dirlist as $dir) {
if (strbegin($dir, 'admin')) $list[$dir] = $dir;
}

$args->adminname =tadminhtml  ::array2combo($list, $itemview['adminname']);
      $args->menu = tadminhtml  ::array2combo($menuitems, $itemview['menuclass']);
      $args->postanounce = tadminhtml  ::array2combo(array(
      'excerpt' => $lang->postexcerpt,
      'card' => $lang->postcard,
      'lite' => $lang->postlite
      ), $itemview['postanounce']);
      
      $tabs->add($lang->name,'[text=name]
      [combo=themename]
      [combo=adminname]' .
      ($id == 1 ? '' : ('[checkbox=customsidebar] [checkbox=disableajax]')) .
            '[checkbox=hovermenu]
      [combo=menu]
      [combo=postanounce]
      [text=perpage]
      [checkbox=invertorder]
      ');

      $view = tview::i($id);      
      if (count($view->custom)) {
        $tabs->add($lang->custom, $this->get_custom($view));
      }
      
      $result .= $html->h4->help;
      
      $args->formtitle = $lang->edit;
      $result .= $html->adminform($tabs->get(), $args) .
      tuitabs::gethead();
      break;
      
      case 'addview':
      $args->formtitle = $lang->addview;
      $result .= $html->adminform('[text=name]', $args);
      break;
      
      case 'defaults':
      $items = '';
      $theme = ttheme::i();
      $tml = $theme->templates['content.admin.combo'];
      foreach ($views->defaults as $name => $id) {
        $args->name = $name;
        $args->value = self::getcombo($id);
        $args->data['$lang.$name'] = $lang->$name;
        $items .= $theme->parsearg($tml, $args);
      }
      $args->items = $items;
      $args->formtitle = $lang->defaultsform;
      $result .= $theme->parsearg($theme->content->admin->form, $args);
      break;
    }
    
    return $html->fixquote($result);
  }
  
  public function processform() {
    $result = '';
    switch ($this->name) {
      case 'views':
      $views = tviews::i();
      $idview = (int) tadminhtml::getparam('idview', 0);
      if (!$idview || !$views->itemexists($idview)) return '';
      
      if ($this->action == 'delete') {
        if ($idview > 1) $views->delete($idview);
        return '';
      }
      
      $view = tview::i($idview);
      if ($idview > 1) {
        $view->customsidebar = isset($_POST['customsidebar']);
        $view->disableajax = isset($_POST['disableajax']);
      }
      
      $view->name = trim($_POST['name']);
      $view->themename = trim($_POST['themename']);
      $view->adminname = trim($_POST['adminname']);
      $view->menuclass = $_POST['menu'];
      $view->hovermenu = isset($_POST['hovermenu']);
      $view->postanounce = $_POST['postanounce'];
      $view->perpage = (int) $_POST['perpage'];
      $view->invertorder = isset($_POST['invertorder']);
      
      $this->set_custom($idview);
      $view->save();
      break;
      
      case 'addview':
      $name = trim($_POST['name']);
      if ($name != '') {
        $views = tviews::i();
        $id = $views->add($name);
      }
      break;
      
      case 'defaults':
      $views = tviews::i();
      foreach ($views->defaults as $name => $id) {
        $views->defaults[$name] = (int) $_POST[$name];
      }
      $views->save();
      break;
    }
    
    ttheme::clearcache();
  }
  
}//class