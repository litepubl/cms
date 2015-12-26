<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tadminwidgets extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public static function getsidebarnames(tview $view) {
    $count = $view->theme->sidebarscount;
    $result = range(1, $count);
    $parser = tthemeparser::i();
    $about = $parser->getabout($view->theme->name);
    foreach ($result as $key => $value) {
      if (isset($about["sidebar$key"])) $result[$key] = $about["sidebar$key"];
    }

    return $result;
  }
  
  public function get_table() {
    $idview = (int) tadminhtml::getparam('idview', 1);
    $view = tview::i($idview);

    $widgets = twidgets::i();
$theme = $this->view->theme;
$admintheme = $this->view->admintheme;

    $lang = tlocal::i('widgets');
$lang->addsearch('views');

    $html = tadminhtml ::i();
    $html->section = 'widgets';

    $args = new targs();
    $args->idview = $idview;
    $args->customsidebar = $idview == 1 ? '' : 
$theme->getinput('checkbox', $name, $value ? 'checked="checked"' : '', '$lang.' . $name);
$theme->parse($html->getcheckbox('customsidebar', true));

    $args->adminurl = tadminhtml::getadminlink('/admin/views/widgets/', 'idwidget');

    $result = $html->formhead($args);
    $count = count($view->sidebars);
    $sidebarnames = self::getsidebarnames($view);

//items for table builder
$items = array();
    foreach ($view->sidebars as $i => $sidebar) {
      $orders = range(1, count($sidebar));
      foreach ($sidebar as $j => $sb_item) {
        $id = $sb_item['id'];
        $w_item = $widgets->getitem($id);

$items[] = array(
'id' => $id,
'title' => $w_item['title'],
'sidebarcombo' => tadminhtml::getcombobox("sidebar-$id", $sidebarnames, $i),
'ordercombo' => tadminhtml::getcombobox("order-$id", $orders, $j),
'ajaxbuttons' => str_replace('$button',
$admintheme->templates['']
        $args->disabled = ($item['cache'] == 'cache') || ($item['cache'] == 'nocache') ? '' : 'disabled="disabled"';
        $args->ajax = $sb_item['ajax'];
        $args->inline = $sb_item['ajax'] === 'inline';
$admintheme->templates['radiogroup'])
);
      }
    }

$tb = new tablebuilder();
$tb->args->adminurl = tadminhtml::getadminlink('/admin/views/widgets/', 'idwidget');
$tb->setstruct(array(
array(
$lang->widget,
'<a href="$adminurl=$id">$title</a>'
),

array(
$lang->sidebar,
'$sidebarcombo'
),

array(
$lang->order,
'$ordercombo'
),

array(
$lang->collapse,
'$ajaxbuttons'
)
));

    $result .= $tb->build($items);
    
    //all widgets
    $args->id_view = $idview;
    $result .= $html->addhead($args);
    foreach ($widgets->items as $id => $item) {
      $args->id = $id;
      $args->add($item);
      $args->checked = tsidebars::getpos($view->sidebars, $id) ? false : true;
      $result .= $html->additem($args);
    }
    $result .= $html->addfooter();

    return  $result;
  }
  
  // parse POST into sidebars array
  public static function editsidebars(array &$sidebars) {
    // collect all id from checkboxes
    $items = array();
    foreach ($_POST as $key => $value) {
      if (strbegin($key, 'widgetcheck-'))$items[] = (int) $value;
    }
    
    foreach ($items as $id) {
      if ($pos = tsidebars::getpos($sidebars, $id)) {
        list($i, $j) = $pos;
        if (isset($_POST['deletewidgets']))  {
          array_delete($sidebars[$i], $j);
        } else {
          $i2 = (int)$_POST["sidebar-$id"];
          $j2 = (int) $_POST["order-$id"];
          if ($j2 > count($sidebars[$i2])) $j2 = count($sidebars[$i2]);
          if (($i != $i2) || ($j != $j2)) {
            $item = $sidebars[$i][$j];
            array_delete($sidebars[$i], $j);
            array_insert($sidebars[$i2], $item, $j2);
          }
          $sidebars[$i2][$j2]['ajax'] =  isset($_POST["inlinecheck-$id"]) ? 'inline' : isset($_POST["ajaxcheck-$id"]);
        }
      }
    }
    //    return $this->html->h2->success;
  }
  
  public function getcontent() {
    switch ($this->name) {
      case 'widgets':
      $idwidget = tadminhtml::getparam('idwidget', 0);
      $widgets = twidgets::i();
      if ($widgets->itemexists($idwidget)) {
        $widget = $widgets->getwidget($idwidget);
        $result = $widget->admin->getcontent();
      } else {
        $idview = (int) tadminhtml::getparam('idview', 1);
        $view = tview::i($idview);
        $result = tadminviews::getviewform('/admin/views/widgets/');
        if (($idview == 1) || $view->customsidebar) {
          $result .= $this->get_table();
        } else {
          $args = targs::i();
          $args->idview = $idview;
          $args->customsidebar = $view->customsidebar;
          $args->disableajax = $view->disableajax;
          $args->action = 'options';
          $result .= $this->html->adminform('[checkbox=customsidebar] [checkbox=disableajax] [hidden=idview] [hidden=action]', $args);
        }
      }
break;
      
      case 'addcustom':
      $widget = tcustomwidget::i();
      $result = $widget->admin->getcontent();
break;
    }

        return $result;
  }
  
  public function processform() {
$result = '';
    litepublisher::$urlmap->clearcache();

    switch ($this->name) {
      case 'widgets':
      $idwidget = (int) tadminhtml::getparam('idwidget', 0);
      $widgets = twidgets::i();
      if ($widgets->itemexists($idwidget)) {
        $widget = $widgets->getwidget($idwidget);
        $result = $widget->admin->processform();
      } else {
        if (isset($_POST['action'])) self::setsidebars();
        $result = $this->html->h2->success;
      }
break;
      
      case 'addcustom':
      $widget = tcustomwidget::i();
      $result = $widget->admin->processform();
break;
    }

return $result;
  }
  
  public static function setsidebars() {
    $idview = (int) tadminhtml::getparam('idview', 1);
    $view = tview::i($idview);
    
    switch ($_POST['action']) {
      case 'options':
      $view->disableajax = isset($_POST['disableajax']);
      $view->customsidebar = isset($_POST['customsidebar']);
      break;
      
      case 'edit':
      if (($view->id > 1) && !isset($_POST['customsidebar'])) {
        $view->customsidebar = false;
      } else {
        self::editsidebars($view->sidebars);
      }
      break;
      
      case 'add':
      $idview = (int) tadminhtml::getparam('id_view', 1);
      $_GET['idview'] = $idview;
      $view = tview::i($idview);
      $widgets = twidgets::i();
      foreach ($_POST as $key => $value) {
        if (strbegin($key, 'addwidget-')){
          $id = (int) $value;
          if (!$widgets->itemexists($id) || $widgets->subclass($id)) continue;
          $view->sidebars[0][] = array(
          'id' => $id,
          'ajax' => false
          );
        }
      }
    }
    $view->save();
  }
  
}//class