<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tcommentmanager extends tevents_storage {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'commentmanager';
    $this->addevents('onchanged', 'approved', 'comuseradded',     'is_spamer', 'oncreatestatus');
  }
  
  public function getcount() {
    litepublisher::$db->table = 'comments';
    return litepublisher::$db->getcount();
  }
  
  public function addcomuser($name, $email, $website, $ip) {
    $users = tusers::i();
    $id = $users->add(array(
    'email' => strtolower(trim($email)),
    'name' => $name,
    'website' => tcontentfilter::clean_website($website),
    'status' => 'comuser',
    'idgroups' => 'commentator'
    ));
    
    if ($id) {
      $this->comuseradded($id);
    }
    return $id;
  }
  
  public function add($idpost, $idauthor, $content, $ip) {
    $status = $this->createstatus($idpost, $idauthor, $content, $ip);
    if (!$status) return false;
    $comments = tcomments::i();
    return $comments->add($idpost, $idauthor,  $content, $status, $ip);
  }
  
  public function reply($idparent, $content) {
    $idauthor = 1; //admin
    $comments = tcomments::i();
    $idpost = $comments->getvalue($idparent, 'post');
    $id = $comments->add($idpost, $idauthor,  $content, 'approved', '');
    $comments->setvalue($id, 'parent', $idparent);
    return $id;
  }
  
  public function changed($id) {
    $comments = tcomments::i();
    $idpost = $comments->getvalue($id, 'post');
    $count = $comments->db->getcount("post = $idpost and status = 'approved'");
    $comments->getdb('posts')->setvalue($idpost, 'commentscount', $count);
    if (litepublisher::$options->commentspull) tcommentspull::i()->set($idpost, $count);
    //update trust
    try {
      $idauthor = $comments->getvalue($id, 'author');
      $users = tusers::i();
      if ($this->trustlevel > (int) $users->getvalue($idauthor, 'trust')) {
        $trust = $comments->db->getcount("author = $idauthor and status = 'approved' limit " . ($this->trustlevel + 1));
        $users->setvalue($idauthor, 'trust', $trust);
      }
    } catch (Exception $e) {
    }
    
    $this->onchanged($id);
  }
  
  public function sendmail($id) {
    if ($this->sendnotification) {
      litepublisher::$urlmap->onclose($this, 'send_mail', $id);
    }
  }
  
  public function send_mail($id) {
    $comments = tcomments::i();
    $comment = $comments->getcomment($id);
    //ignore admin comments
    if ($comment->author == 1) return;
    ttheme::$vars['comment'] = $comment;
    $args = new targs();
    $adminurl = litepublisher::$site->url . '/admin/comments/'. litepublisher::$site->q . "id=$id";
    $ref = md5(litepublisher::$secret . $adminurl . litepublisher::$options->solt);
    $adminurl .= "&ref=$ref&action";
    $args->adminurl = $adminurl;
    
    tlocal::usefile('mail');
    $lang = tlocal::i('mailcomments');
    $theme = ttheme::i();
    
    $subject = $theme->parsearg($lang->subject, $args);
    $body = $theme->parsearg($lang->body, $args);
    return tmailer::sendtoadmin($subject, $body, false);
  }
  
  public function createstatus($idpost, $idauthor, $content, $ip) {
    $status = $this->oncreatestatus ($idpost, $idauthor, $content, $ip);
    if (false ===  $status) return false;
    if ($status == 'spam') return false;
    if (($status == 'hold') || ($status == 'approved')) return $status;
    if (!$this->filterstatus) return $this->defstatus;
    if ($this->defstatus == 'approved') return 'approved';
    
    return 'hold';
  }
  
  public function canadd($idauthor) {
    return !$this->is_spamer($idauthor);
  }
  
  public function is_duplicate($idpost, $content) {
    $comments = tcomments::i($idpost);
    $content = trim($content);
    $hash = basemd5($content);
    return $comments->raw->findid("hash = '$hash'");
  }
  
  public function request($arg) {
    $id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
    $users = tusers::i();
    if (!$users->itemexists($id)) return "<?php litepublisher::$urlmap->redir('/');";
    $item = $users->getitem($id);
    $url = $item['website'];
    if (!strpos($url, '.')) $url = litepublisher::$site->url . '/';
    if (!strbegin($url, 'http://')) $url = 'http://' . $url;
    return "<?php litepublisher::$urlmap->redir('$url');";
  }
  
}//class