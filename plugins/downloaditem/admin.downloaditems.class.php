<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmindownloaditems extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $result = '';
    $html = $this->inihtml();
    $lang = tlocal::admin('downloaditems');
    $lang->ini['downloaditems'] = $lang->ini['downloaditem'] + $lang->ini['downloaditems'];
    
    $args = new targs();
    $args->adminurl = $this->adminurl;
    $editurl = tadminhtml::getadminlink('/admin/downloaditems/editor/', 'id');
    $args->editurl = $editurl;
    
    $downloaditems = tdownloaditems::i();
    $perpage = 20;
    $where = litepublisher::$options->group == 'downloaditem' ? ' and author = ' . litepublisher::$options->user : '';
    
    switch ($this->name) {
      case 'addurl':
      $args->formtitle = $lang->addurl;
      $args->url = tadminhtml::getparam('url', '');
      return $html->adminform('[text=url]', $args);
      
      case 'theme':
      $where .= " and type = 'theme' ";
      break;
      
      case 'plugin':
      $where .= " and type = 'plugin' ";
      break;
    }
    
    $count = $downloaditems->getchildscount($where);
    $from = $this->getfrom($perpage, $count);
    if ($count > 0) {
      $items = $downloaditems->select("status <> 'deleted' $where", " order by posted desc limit $from, $perpage");
      if (!$items) $items = array();
    }  else {
      $items = array();
    }
    
    $result .= $html->editlink();
    $form = new  adminform(new targs());
    $form->items =$html->getitemscount($from, $from + count($items), $count);
    $form->items .= $html->tableposts($items, array(
    array('right', $lang->downloads, '$post.downloads'),
    array('left', $lang->posttitle, '$post.bookmark'),
    array('left', $lang->status, '$ticket_status.status'),
    array('left', $lang->tags, '$post.tagnames'),
    array('center', $lang->edit, '<a href="' . $editurl . '=$post.id">' . $lang->edit . '</a>'),
    ));
    
    $form->items .= $html->div(
    '[button=publish]
    [button=setdraft]
    [button=delete]');
    
    $form->submit = false;
    $result .= $form->get();
    $result = $html->fixquote($result);
    
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    return $result;
  }
  
  public function processform() {
    $downloaditems = tdownloaditems::i();
    if ($this->name == 'addurl') {
      $url = trim($_POST['url']);
      if ($url == '') return '';
      if ($downloaditem = taboutparser::parse($url)) {
        $id = $downloaditems->add($downloaditem);
        litepublisher::$urlmap->redir(tadminhtml::getadminlink('/admin/downloaditems/editor/', "id=$id"));
      }
      return '';
    }
    
    $status = isset($_POST['publish']) ? 'published' :
    (isset($_POST['setdraft']) ? 'draft' :'delete');
    
    foreach ($_POST as $key => $id) {
      if (!is_numeric($id))  continue;
      $id = (int) $id;
      if ($status == 'delete') {
        $downloaditems->delete($id);
      } else {
        $downloaditem = tdownloaditem::i($id);
        $downloaditem->status = $status;
        $downloaditems->edit($downloaditem);
      }
    }
  }
  
}//class