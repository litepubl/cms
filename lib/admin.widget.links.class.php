<?php

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
    tablebuilder::checkbox('checklink'),
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
