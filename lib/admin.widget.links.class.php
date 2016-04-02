<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tadminlinkswidget extends tadminwidget {

  public static function i() {
    return getinstance(__class__);
  }

  protected function dogetcontent(twidget $widget, targs $args) {
    $args->redir = $widget->redir;
    return $this->html->parsearg('[checkbox=redir]', $args);
  }

  public function getcontent() {
    $result = parent::getcontent();
    $widget = $this->widget;
    $html = $this->html;
    $args = new targs();
    $id = isset($_GET['idlink']) ? (int)$_GET['idlink'] : 0;
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
    $result.= $html->adminform('
    [text=url]
    [text=text]
    [text=linktitle]
    [hidden=mode]', $args);

    $adminurl = $this->adminurl . intval($_GET['idwidget']) . '&idlink';
    $tb = new tablebuilder();
    $tb->setstruct(array(
      $tb->checkbox('checklink') ,
      array(
        $lang->url,
        '<a href=\'$url\'>$url</a>'
      ) ,
      array(
        $lang->anchor,
        '$text'
      ) ,
      array(
        $lang->description,
        '$title'
      ) ,
      array(
        $lang->edit,
        "<a href='$adminurl=\$id'>$lang->edit</a>"
      ) ,
    ));

    $form = new adminform($args);
    $form->title = $lang->widgets;
    $result.= $form->getdelete($tb->build($widget->items));
    return $result;
  }

  public function processform() {
    $widget = $this->widget;
    $widget->lock();
    if (isset($_POST['delete'])) {
      foreach ($_POST as $key => $value) {
        $id = (int)$value;
        if (isset($widget->items[$id])) $widget->delete($id);
      }
    } elseif (isset($_POST['mode'])) {
      extract($_POST, EXTR_SKIP);
      switch ($mode) {
        case 'add':
          $_GET['idlink'] = $widget->add($url, $linktitle, $text);
          break;


        case 'edit':
          $widget->edit((int)$_GET['idlink'], $url, $linktitle, $text);
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

} //class