<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tadmintickets extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $result = '';
    $tickets = ttickets::i();
    $perpage = 20;
    $where = litepublisher::$options->group == 'ticket' ? ' and author = ' . litepublisher::$options->user : '';
    
    switch ($this->name) {
      case 'opened':
      $where .= " and state = 'opened' ";
      break;
      
      case 'fixed':
      $where .= " and state = 'fixed' ";
      break;
    }
    
    $count = $tickets->getchildscount($where);
    $from = $this->getfrom($perpage, $count);
    
    if ($count > 0) {
      $items = $tickets->select("status <> 'deleted' $where", " order by posted desc limit $from, $perpage");
      if (!$items) $items = array();
    }  else {
      $items = array();
    }
    
    $html = $this->inihtml();
    $lang = tlocal::admin('tickets');
    $lang->addsearch('ticket');
    $result .=$html->editlink();
    $result .=$html->getitemscount($from, $from + count($items), $count);
    
$tb = new tablebuilder();
$tb->setposts(array(
    array('center', $lang->date, '$post.date'),
    array('left', $lang->posttitle, '$post.bookmark'),
    array('left', $lang->author, '$post.authorlink'),
    array($lang->status, '$poststatus'),
    array('left', $lang->category, '$post.category'),
    array($lang->state, function(tablebuilder $t) {
return tlocal::i()->__get(basetheme::$vars['post']->state);
}),

    array('center', $lang->edit, '<a href="' . tadminhtml::getadminlink('/admin/tickets/editor/', 'id') . '=$post.id">' . $lang->edit . '</a>'),
    ));

$table = $tb->build($items);    

    //wrap form
    if (litepublisher::$options->group != 'ticket') {
      $args = new targs();
      $args->table = $table;
      $result .= $html->tableform ($args);
    } else {
      $result .= $table;
    }
    
    $result = $html->fixquote($result);
    
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  public function processform() {
    if (litepublisher::$options->group == 'ticket') return '';
    $tickets = ttickets::i();
    $status = isset($_POST['publish']) ? 'published' :
    (isset($_POST['setdraft']) ? 'draft' :
    (isset($_POST['setfixed']) ? 'fixed' :'delete'));
    foreach ($_POST as $key => $id) {
      if (!is_numeric($id))  continue;
      $id = (int) $id;
      if ($status == 'delete') {
        $tickets->delete($id);
      } else {
        $ticket = tticket::i($id);
        if ($status == 'fixed') {
          $ticket->set_state($status);
        } else {
          $ticket->status = $status;
        }
        $tickets->edit($ticket);
      }
    }
  }
  
}//class