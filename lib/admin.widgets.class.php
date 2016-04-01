<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
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
    $idview = (int)tadminhtml::getparam('idview', 1);
    $view = tview::i($idview);

    $widgets = twidgets::i();
    $theme = $this->view->theme;
    $admintheme = $this->view->admintheme;

    $lang = tlocal::i('widgets');
    $lang->addsearch('views');

    $html = tadminhtml::i();

    $args = new targs();
    $form = new adminform($args);
    $form->title = $lang->formhead;
    $form->items = $form->hidden('action', 'edit');
    $form->items.= $form->hidden('idview', $idview);

    if ($idview != 1) {

      $form->items.= $html->getcheckbox('customsidebar', true);
    }
    //all widgets
    $checkboxes = '';
    foreach ($widgets->items as $id => $item) {
      if (!tsidebars::getpos($view->sidebars, $id)) {
        $checkboxes.= $theme->getinput('checkbox', "addwidget-$id", "value=\"$id\"", $item['title']);
      }
    }

    $args->checkboxes = $checkboxes;
    $args->idview = $idview;
    $form->before = $admintheme->parsearg($admintheme->templates['addwidgets'], $args);
    $count = count($view->sidebars);
    $sidebarnames = self::getsidebarnames($view);

    //items for table builder
    $items = array();
    $tml_btn = $admintheme->templates['radiogroup.button'];
    $tml_active = $admintheme->templates['radiogroup.active'];

    foreach ($view->sidebars as $i => $sidebar) {
      $orders = range(1, count($sidebar));
      foreach ($sidebar as $j => $sb_item) {
        $id = $sb_item['id'];
        $w_item = $widgets->getitem($id);

        $items[] = array(
          'id' => $id,
          'title' => $w_item['title'],
          'sidebarcombo' => tadminhtml::getcombobox("sidebar-$id", $sidebarnames, $i) ,
          'ordercombo' => tadminhtml::getcombobox("order-$id", $orders, $j) ,
          'ajaxbuttons' => str_replace('$button',

          strtr($sb_item['ajax'] == false ? $tml_active : $tml_btn, array(
            '$name' => "ajax-$id",
            '$value' => 'noajax',
            '$title' => $lang->noajax
          )) .

          strtr(($sb_item['ajax'] && $sb_item['ajax'] != 'inline') ? $tml_active : $tml_btn, array(
            '$name' => "ajax-$id",
            '$value' => 'ajax',
            '$title' => $lang->ajax
          )) .

          (($w_item['cache'] == 'cache') || ($w_item['cache'] == 'nocache') ? strtr($sb_item['ajax'] == 'inline' ? $tml_active : $tml_btn, array(
            '$name' => "ajax-$id",
            '$value' => 'inline',
            '$title' => $lang->inline
          )) : '') , $admintheme->templates['radiogroup'])
        );
      }
    }

    $tb = new tablebuilder();
    $tb->args->adminurl = tadminhtml::getadminlink('/admin/views/widgets/', 'idwidget');
    $tb->setstruct(array(
      array(
        $lang->widget,
        '<a href="$adminurl=$id">$title</a>'
      ) ,

      array(
        $lang->sidebar,
        '$sidebarcombo'
      ) ,

      array(
        $lang->order,
        '$ordercombo'
      ) ,

      array(
        $lang->delete,
        '<a href="$adminurl=$id&action=delete" class="btn btn-default confirm-delete-link" role="button"><span class="fa fa-remove text-danger"></span> $lang.delete</a>',
      ) ,

      array(
        $lang->collapse,
        '$ajaxbuttons'
      )
    ));

    $form->items.= $tb->build($items);
    return $form->get();
  }

  public function getcontent() {
    if (!(isset($_GET['action']) && $_GET['action'] == 'delete')) {
      $idwidget = tadminhtml::getparam('idwidget', 0);
      $widgets = twidgets::i();
      if ($widgets->itemexists($idwidget)) {
        $widget = $widgets->getwidget($idwidget);
        return $widget->admin->getcontent();
      }
    }

    $idview = (int)tadminhtml::getparam('idview', 1);
    $view = tview::i($idview);
    $result = tadminviews::getviewform('/admin/views/widgets/');

    if (($idview == 1) || $view->customsidebar) {
      $result.= $this->get_table();
    } else {
      $lang = tlocal::i('widgets');
      $args = new targs();
      $args->customsidebar = $view->customsidebar;
      $args->disableajax = $view->disableajax;
      $args->idview = $idview;
      $args->action = 'options';
      $args->formtitle = $lang->viewsidebar;
      $result.= $this->html->adminform('
      [checkbox=customsidebar]
      [checkbox=disableajax]
      [hidden=idview]
      [hidden=action]', $args);
    }

    return $result;
  }

  public function processform() {
    litepublisher::$urlmap->clearcache();

    $idwidget = (int)tadminhtml::getparam('idwidget', 0);
    $widgets = twidgets::i();

    if ($widgets->itemexists($idwidget)) {
      if (isset($_GET['action']) && ($_GET['action'] == 'delete')) {
        $idview = (int)tadminhtml::getparam('idview', 1);
        $sidebars = tsidebars::i($idview);
        $sidebars->remove($idwidget);
        $result = $this->html->h4->deleted;
      } else {
        $widget = $widgets->getwidget($idwidget);
        $result = $widget->admin->processform();
      }

      return $result;
    }

    $idview = (int)tadminhtml::getparam('idview', 1);
    $view = tview::i($idview);

    switch ($_POST['action']) {
      case 'options':
        $view->disableajax = isset($_POST['disableajax']);
        $view->customsidebar = isset($_POST['customsidebar']);
        $view->save();
        break;


      case 'edit':
        if (($view->id > 1) && !isset($_POST['customsidebar'])) {
          $view->customsidebar = false;
        } else {
          $sidebars = tsidebars::i($idview);
          foreach ($sidebars->items as $i => $items) {
            foreach ($items as $j => $item) {
              $id = $item['id'];
              if (!isset($_POST["sidebar-$id"])) continue;

              $i2 = (int)$_POST["sidebar-$id"];
              if ($i2 >= count($sidebars->items)) {
                $i2 = count($sidebars->items) - 1;
              }

              $j2 = (int)$_POST["order-$id"];
              if ($j2 > count($sidebars->items[$i2])) {
                $j2 = count($sidebars[$i2]);
              }

              if ($i == $i2) {
                array_move($sidebars->items[$i2], $j, $j2);
              } else {
                array_delete($sidebars->items[$i], $j);
                array_insert($sidebars->items[$i2], $item, $j2);
              }

              $sidebars->items[$i2][$j2]['ajax'] = $_POST["ajax-$id"] == 'inline' ? 'inline' : ($_POST["ajax-$id"] == 'ajax');
            }
          }

          $sidebars->save();
        }
        break;


      case 'add':
        $idview = (int)tadminhtml::getparam('id_view', 1);
        $_GET['idview'] = $idview;
        $view = tview::i($idview);
        $widgets = twidgets::i();

        foreach ($_POST as $key => $value) {
          if (strbegin($key, 'addwidget-')) {
            $id = (int)$value;
            if (!$widgets->itemexists($id) || $widgets->subclass($id)) continue;

            $view->sidebars[0][] = array(
              'id' => $id,
              'ajax' => false
            );
          }
        }
        break;
      }

      $view->save();
    }

} //class