<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminmoderator extends tadmincommoncomments {
  private $moder;
  private $iduser;
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function canrequest() {
    $this->moder = litepublisher::$options->ingroup('moderator');
    $this->iduser = $this->moder ? (isset($_GET['iduser']) ? (int) $_GET['iduser'] : 0) : litepublisher::$options->user;
  }
  
  public function can($id, $action) {
    if ($this->moder) return true;
    if (litepublisher::$options->user != tcomments::i()->getvalue($id, 'author')) return false;
    $cm = tcommentmanager::i();
    switch ($action) {
      case 'edit':
      return $cm->canedit;
      
      case 'delete':
      return $cm->candelete;
    }
    return false;
  }
  
  public function getcontent() {
    $result = '';
    $comments = tcomments::i();
    $cm = tcommentmanager::i();
    $lang = $this->lang;
    $html = $this->html;
    if ($action = $this->action) {
      $id = $this->idget();
      if (!$comments->itemexists($id)) return $this->notfound;
      
      switch($action) {
        case 'delete':
        if (!$this->can($id, 'delete')) return $html->h4->forbidden;
        if(!$this->confirmed) return $this->confirmdelete($id);
        $comments->delete($id);
        $result .= $html->h4->successmoderated;
        break;
        
        case 'hold':
        if (!$this->moder) return $html->h4->forbidden;
        $comments->setstatus($id, 'hold');
        $result .= $this->moderated($id);
        break;
        
        case 'approve':
        if (!$this->moder) return $html->h4->forbidden;
        $comments->setstatus($id, 'approved');
        $result .= $this->moderated($id);
        break;
        
        case 'edit':
        if (!$this->can($id, 'edit')) return $html->h4->forbidden;
        $result .= $this->editcomment($id);
        break;
        
        case 'reply':
        if (!$this->can($id, 'edit')) return $html->h4->forbidden;
        $result .= $this->reply($id);
        break;
      }
    }
    
    $result .= $this->getlist($this->name);
    return $result;
  }
  
  public function getinfo($comment) {
    $html = $this->html;
    $lang = tlocal::admin();
    $result = $html->tableprops(array(
    'commentonpost' => "<a href=\"$comment->url\">$comment->posttitle</a>",
    'author' => $comment->name,
    'E-Mail' => $comment->email,
    'IP' => $comment->ip,
    'website' => $comment->website ? "<a href=\"$comment->website\">$comment->website</a>" : '',
    'status' => $comment->localstatus,
    ));
    
    $result .= $html->p->content . $html->p($comment->content);
    $adminurl =$this->adminurl . "=$comment->id&action";
    $result .= "<p>
    $lang->cando:
    <a href='$adminurl=reply'>$lang->reply</a>,
    <a href='$adminurl=approve'>$lang->approve</a>,
    <a class'confirm-delete-link' href='$adminurl=delete'>$lang->delete</a>,
    <a href='$adminurl=hold'>$lang->hold</a>.
    </p>";
    
    return $result;
  }
  
  private function editcomment($id) {
    $comment = new tcomment($id);
    $args = new targs();
    $args->content = $comment->rawcontent;
    $args->formtitle = tlocal::i()->editform;
    $result = $this->getinfo($comment);
    $result .= $this->html->adminform('[editor=content]', $args);
    return $result;
  }
  
  private function reply($id) {
    $comment = new tcomment($id);
    $args = new targs();
    $args->pid = $comment->post;
    $args->formtitle = tlocal::i()->replyform;
    $result = $this->getinfo($comment);
    $args->content = '';
    $result .= $this->html->adminform('
    [editor=content]
    [hidden=pid]
    ', $args);
    return $result;
  }
  
  private function getlist($kind) {
    $result = '';
    $comments = tcomments::i(0);
    $perpage = 20;
    // get total count
    $status = $kind == 'hold' ? 'hold' : 'approved';
    $where = "$comments->thistable.status = '$status'";
    if ($this->iduser) $where .= " and $comments->thistable.author = $this->iduser";
    $total = $comments->db->getcount($where);
    $from = $this->getfrom($perpage, $total);
    $list = $comments->select($where, "order by $comments->thistable.posted desc limit $from, $perpage");
    $html = $this->html;
    $result .= sprintf($html->h4->listhead, $from, $from + count($list), $total);
    $table = $this->createtable();
    $args = targs::i();
    $args->adminurl = $this->adminurl;
    $comment = new tcomment(0);
    ttheme::$vars['comment'] = $comment;
    $body = '';
    foreach ($list as $id) {
      $comment->id = $id;
      $args->id = $id;
      $args->excerpt = tadminhtml::specchars(tcontentfilter::getexcerpt($comment->content, 120));
      $args->onhold = $comment->status == 'hold';
      $args->email = $comment->email == '' ? '' : "<a href='mailto:$comment->email'>$comment->email</a>";
      $args->website =$comment->website == '' ? '' : "<a href='$comment->website'>$comment->website</a>";
      $body .=$html->parsearg($table->body, $args);
    }
    
    $result .= $table->build($body, $html->div(
    $html->getsubmit('approve') .
    $html->getsubmit('hold') .
    $html->getsubmit('delete')
    ));
    
    $theme = ttheme::i();
    
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($total/$perpage),
    ($this->iduser ? "iduser=$this->iduser" : ''));
    return $result;
  }
  
  private function moderated($id) {
    $result = $this->html->h4->successmoderated;
    $result .= $this->getinfo(new tcomment($id));
    return $result;
  }
  
  private function confirmdelete($id) {
    $result = $this->getconfirmform($id, $this->lang->confirmdelete);
    $result .= $this->getinfo(new tcomment($id));
    return $result;
  }
  
  private function getconfirmform($id, $confirm) {
    $args = targs::i();
    $args->id = $id;
    $args->action = 'delete';
    $args->adminurl = litepublisher::$site->url . $this->url . litepublisher::$site->q . 'id';
    $args->confirm = $confirm;
    return $this->html->confirmform($args);
  }
  public function processform() {
    $result = '';
    parent::processform();
    $comments = tcomments::i();
    if (isset($_REQUEST['action'])) {
      switch ($_REQUEST['action']) {
        case 'reply':
        if (!$this->moder) return $this->html->h4->forbidden;
        $item = $comments->getitem($this->idget() );
        $post = tpost::i( (int) $item['post']);
        $this->manager->reply($this->idget(), $_POST['content']);
        return litepublisher::$urlmap->redir($post->lastcommenturl);
        
        case 'edit':
        if (!$this->can($id, 'edit')) return $this->html->h4->forbidden;
        $comments->edit($this->idget(), $_POST['content']);
        return $this->html->h4->successmoderated;
      }
    }
    
    $status = isset($_POST['approve']) ? 'approved' : (isset($_POST['hold']) ? 'hold' : 'delete');
    foreach ($_POST as $key => $id) {
      if (!is_numeric($id))  continue;
      if (!strbegin($key, 'checkbox-item-')) continue;
      $id = (int) $id;
      if ($status == 'delete') {
        if ($this->can($id, 'delete')) $comments->delete($id);
      } else {
        if ($this->moder) $comments->setstatus($id, $status);
      }
    }
    
    return $this->html->h4->successmoderated;
  }
  
  public static function refilter() {
    $db = litepublisher::$db;
    $filter = tcontentfilter::i();
    $from = 0;
    while ($a = $db->res2assoc($db->query("select id, rawcontent from $db->rawcomments where id > $from limit 500"))) {
      $db->table = 'comments';
      foreach ($a as $item) {
        $s = $filter->filtercomment($item['rawcontent']);
        $db->setvalue($item['id'], 'content', $s);
        $from = max($from, $item['id']);
      }
      unset($a);
    }
  }
  
}//class