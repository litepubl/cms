<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminredirector extends tadminmenu {
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $redir = tredirector::i();
    $html = $this->html;
    $lang = $this->lang;
    $args = targs::i();
    $from = tadminhtml::getparam('from', '');
    if (isset($redir->items[$from])) {
      $args->from = $from;
      $args->to = $redir->items[$from];
    } else {
      $args->from = '';
      $args->to = '';
    }
    $args->action = 'edit';
    $args->formtitle= $lang->edit;
    $result = $html->adminform('[text=from] [text=to] [hidden=action]', $args);
    
    $id = 1;
    $items = array();
    foreach ($redir->items as $from => $to) {
      $items[] = array(
      'id' => $id++,
      'from'  => $from,
      'to' =>  $to
      );
    }
    
    $adminurl = tadminhtml::getadminlink($this->url, 'from');
    $args->table = $html->buildtable($items, array(
    array('center', '+', '<input type="checkbox" name="checkbox_$id" id="checkbox_$id" value="$from" />'),
    array('left', $lang->from, '<a href="$site.url$from" title="$from">$from</a>'),
    array('left', $lang->to,'<a href="$site.url$to" title="$to">$to</a>'),
    array('center', $lang->edit, "<a href=\"$adminurl=\$from\">$lang->edit</a>")
    ));
    
    $args->action = 'delete';
    $result .= $html->parsearg('<form name="deleteform" action="" method="post">
    [hidden=action]
    $table
    <p><input type="submit" name="delete" value="$lang.delete" /></p>
    </form>', $args);
    $result = $html->fixquote($result);
    return $result;
  }
  
  public function processform() {
    $redir = tredirector::i();
    switch ($_POST['action']) {
      case 'edit':
      $redir->items[$_POST['from']] = $_POST['to'];
      break;
      
      case 'delete':
      foreach ($_POST as $id => $value) {
        if (strbegin($id, 'checkbox_')) {
          if (isset($redir->items[$value])) unset($redir->items[$value]);
        }
      }
      break;
    }
    
    $redir->save();
    return '';
  }
  
}//class