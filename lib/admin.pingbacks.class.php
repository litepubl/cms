<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminpingbacks extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $result = '';
    $pingbacks = tpingbacks::i();
    $lang = $this->lang;
    $html = $this->html;
    
    if ($action = $this->action) {
      $id = $this->idget();
      if (!$pingbacks->itemexists($id)) return $this->notfound;
      switch($action) {
        case 'delete':
        if(!$this->confirmed) return $this->html->confirmdelete($id, $this->adminurl, $lang->confirmdelete );
        $pingbacks->delete($id);
        $result .= $html->h4->successmoderated;
        break;
        
        case 'hold':
        $pingbacks->setstatus($id, false);
        $result .= $html->h2->successmoderated;
        break;
        
        case 'approve':
        $pingbacks->setstatus($id, true);
        $result .= $html->h2->successmoderated;
        break;
        
        case 'edit':
        $result .= $this->editpingback($id);
        break;
      }
    }
    $result .= $this->getpingbackslist();
    return $result;
  }
  
  private function getpingbackslist() {
    $result = '';
    $pingbacks = tpingbacks::i();
    $perpage = 20;
    $total = $pingbacks->getcount();
    $from = $this->getfrom($perpage, $total);
    $db = $pingbacks->db;
    $t = $pingbacks->thistable;
    $items = $db->res2assoc($db->query(
    "select $t.*, $db->posts.title as posttitle, $db->urlmap.url as posturl
    from $t, $db->posts, $db->urlmap
    where $t.status <> 'deleted' and $db->posts.id = $t.post and $db->urlmap.id = $db->posts.idurl
    order by $t.posted desc limit $from, $perpage"));
    
    $html = $this->html;
    $lang = tlocal::i();
    $args = new targs();
    $form = new adminform($args);
    $form->items =$html->getitemscount($from, $from + count($items), $total);
    ttheme::$vars['pingitem'] = new pingitem();
    $form->items .= $html->buildtable($items, array(
    $html->get_table_checkbox('id'),
    array('left', $lang->date , '$pingitem.date'),
    array('left', $lang->status, '$pingitem.status'),
    array('left', $lang->title, '$title'),
    array('left', $lang->url, '<a href="$url">$url</a>'),
    array('left', 'IP', '$ip'),
    array('left', $lang->post, '<a href="$posturl">$posttitle</a>'),
    array('center', $lang->edit, "<a href='$this->adminurl=\$id&action=edit'>$lang->edit</a>"),
    ));
    
    unset(ttheme::$vars['pingitem']);
    
    $form->items .= $html->div($html->getsubmit('approve', 'hold', 'delete'));
    $form->submit = false;
    $result = $form->get();
    
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($total/$perpage));
    return $result;
  }
  
  private function editpingback($id) {
    $pingbacks = tpingbacks::i();
    $args = targs::i();
    $args->add($pingbacks->getitem($id));
    $args->formtitle = tlocal::i()->edit;
    return $this->html->adminform('
    [text=title]
    [text=url]
    ', $args);
  }
  
  public function processform() {
    $pingbacks = tpingbacks::i();
    if (isset($_REQUEST['action']) && $_REQUEST['action'] == 'edit') {
      extract($_POST, EXTR_SKIP);
      $pingbacks->edit($this->idget(), $title, $url);
    } else {
      $status = isset($_POST['approve']) ? 'approve' : (isset($_POST['hold']) ? 'hold' : 'delete');
      foreach ($_POST as $k => $id) {
        if (!strbegin($k, 'id-') || !is_numeric($id))  continue;
        $id = (int) $id;
        if ($status == 'delete') {
          $pingbacks->delete($id);
        } else {
          $pingbacks->setstatus($id, $status == 'approve');
        }
      }
    }
    
    return $this->html->h4->successmoderated;
  }
  
}//class

class pingitem {
  
  public function __get($name) {
    $item = ttheme::$vars['item'];
    switch ($name) {
      case 'status':
      return tlocal::get('commentstatus', $item['status']);
      
      case 'date':
      return tlocal::date(strtotime($item['posted']));
    }
    
    return '';
  }
  
}