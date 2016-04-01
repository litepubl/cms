<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

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
    $id = (int)tadminhtml::getparam('idwidget', 0);
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
    $html = $this->html;
    $args->text = $item['content'];
    $args->template = tadminhtml::array2combo(self::gettemplates() , $item['template']);
    $result = $this->optionsform($item['title'], $viewcombo . $html->parsearg('[editor=text]
    [combo=template]
    [hidden=mode]
    [hidden=idwidget]', $args));

    $lang = tlocal::i();
    $tb = new tablebuilder();
    $tb->setstruct(array(
      $tb->checkbox('widgetcheck') ,
      array(
        $lang->widgettitle,
        "<a href=\"$this->adminurl\$id\" title=\"\$title\">\$title</a>"
      ) ,
    ));

    $form = new adminform($args);
    $form->title = $lang->widgets;
    $result.= $form->getdelete($tb->build($widget->items));
    return $result;
  }

  public function processform() {
    $widget = $this->widget;
    if (isset($_POST['mode'])) {
      extract($_POST, EXTR_SKIP);
      switch ($mode) {
        case 'add':
          $_GET['idwidget'] = $widget->add($idview, $title, $text, $template);
          break;


        case 'edit':
          $id = isset($_GET['idwidget']) ? (int)$_GET['idwidget'] : 0;
          if ($id == 0) $id = isset($_POST['idwidget']) ? (int)$_POST['idwidget'] : 0;
          $widget->edit($id, $title, $text, $template);
          break;
        }
    } elseif (isset($_POST['delete'])) {
      $this->deletewidgets($widget);
    }
  }

  public function deletewidgets(twidget $widget) {
    $widgets = twidgets::i();
    $widgets->lock();
    $widget->lock();
    foreach ($_POST as $key => $value) {
      if (strbegin($key, 'widgetcheck-')) $widget->delete((int)$value);
    }
    $widget->unlock();
    $widgets->unlock();
  }

} //class