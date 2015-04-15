<?php
//comments.class.db.php
class tcomments extends titems {
  public $rawtable;
  private $pid;
  
  public static function i($pid = 0) {
    $result = getinstance(__class__);
    if ($pid > 0) $result->pid = $pid;
    return $result;
  }
  
  protected function create() {
    $this->dbversion = true;
    parent::create();
    $this->table = 'comments';
    $this->rawtable = 'rawcomments';
    $this->basename = 'comments';
    $this->addevents('edited', 'onstatus', 'changed', 'onapproved');
    $this->pid = 0;
  }
  
  public function add($idpost, $idauthor, $content, $status, $ip) {
    if ($idauthor == 0) $this->error('Author id = 0');
    $filter = tcontentfilter::i();
    $filtered = $filter->filtercomment($content);
    
    $item = array(
    'post' => $idpost,
    'parent' => 0,
    'author' => (int) $idauthor,
    'posted' => sqldate(),
    'content' =>$filtered,
    'status' => $status
    );
    
    $id = (int) $this->db->add($item);
    $item['id'] = $id;
    $item['rawcontent'] = $content;
    $this->items[$id] = $item;
    
    $this->getdb($this->rawtable)->add(array(
    'id' => $id,
    'created' => sqldate(),
    'modified' => sqldate(),
    'ip' => $ip,
    'rawcontent' => $content,
    'hash' => basemd5($content)
    ));
    
    $this->added($id);
    $this->changed($id);
    return $id;
  }
  
  public function edit($id, $content) {
    if (!$this->itemexists($id)) return false;
    $filtered = tcontentfilter::i()->filtercomment($content);
    $this->db->setvalue($id, 'content', $filtered);
    $this->getdb($this->rawtable)->updateassoc(array(
    'id' => $id,
    'modified' => sqldate(),
    'rawcontent' => $content,
    'hash' => basemd5($content)
    ));
    
    if (isset($this->items[$id])) {
      $this->items[$id]['content'] = $filtered;
      $this->items[$id]['rawcontent'] = $content;
    }
    
    $this->edited($id);
    $this->changed($id);
    return true;
  }
  
  public function delete($id) {
    if (!$this->itemexists($id)) return false;
    $this->db->setvalue($id, 'status', 'deleted');
    $this->deleted($id);
    $this->changed($id);
    return true;
  }
  
  public function setstatus($id, $status) {
    if (!in_array($status, array('approved', 'hold', 'spam')))  return false;
    if (!$this->itemexists($id)) return false;
    $old = $this->getvalue($id, 'status');
    if ($old != $status) {
      $this->setvalue($id, 'status', $status);
      $this->onstatus($id, $old, $status);
      $this->changed($id);
      if (($old == 'hold') && ($status == 'approved')) $this->onapproved($id);
      return true;
    }
    return false;
  }
  
  public function postdeleted($idpost) {
    $this->db->update("status = 'deleted'", "post = $idpost");
  }
  
  public function getcomment($id) {
    return new tcomment($id);
  }
  
  public function getcount($where = '') {
    return $this->db->getcount($where);
  }
  
  public function select($where, $limit) {
    if ($where != '') $where .= ' and ';
    $table = $this->thistable;
    $authors = litepublisher::$db->users;
    $res = litepublisher::$db->query("select $table.*, $authors.name, $authors.email, $authors.website, $authors.trust from $table, $authors
    where $where $authors.id = $table.author $limit");
    
    return $this->res2items($res);
  }
  
  public function getraw() {
    return $this->getdb($this->rawtable);
  }
  
  public function getapprovedcount() {
    return $this->db->getcount("post = $this->pid and status = 'approved'");
  }
  
  //uses in import functions
  public function insert($idauthor, $content, $ip, $posted, $status) {
    $filtered = tcontentfilter::i()->filtercomment($content);
    $item = array(
    'post' => $this->pid,
    'parent' => 0,
    'author' => $idauthor,
    'posted' => sqldate($posted),
    'content' =>$filtered,
    'status' => $status
    );
    
    $id =$this->db->add($item);
    $item['rawcontent'] = $content;
    $this->items[$id] = $item;
    
    $this->getdb($this->rawtable)->add(array(
    'id' => $id,
    'created' => sqldate($posted),
    'modified' => sqldate(),
    'ip' => $ip,
    'rawcontent' => $content,
    'hash' => basemd5($content)
    ));
    
    return $id;
  }
  
  public function getcontent() {
    return $this->getcontentwhere('approved', '');
  }
  
  public function getholdcontent($idauthor) {
    return $this->getcontentwhere('hold', "and $this->thistable.author = $idauthor");
  }
  
  public function getcontentwhere($status, $where ) {
    $result = '';
    $post = tpost::i($this->pid);
    $theme = $post->theme;
    if ($status == 'approved') {
      if (litepublisher::$options->commentpages ) {
        $page = litepublisher::$urlmap->page;
        if (litepublisher::$options->comments_invert_order) $page = max(0, $post->commentpages  - $page) + 1;
        $count = litepublisher::$options->commentsperpage;
        $from = ($page - 1) * $count;
      } else {
        $from = 0;
        $count = $post->commentscount;
      }
    } else {
      $from = 0;
      $count = litepublisher::$options->commentsperpage;
    }
    
    $table = $this->thistable;
    $items = $this->select("$table.post = $this->pid $where and $table.status = '$status'",
    "order by $table.posted asc limit $from, $count");
    
    $args = targs::i();
    $args->from = $from;
    $comment = new tcomment(0);
    ttheme::$vars['comment'] = $comment;
    $lang = tlocal::i('comment');
    
    $tml = strtr($theme->templates['content.post.templatecomments.comments.comment'], array(
    '$quotebuttons' => $post->comstatus != 'closed' ? $theme->templates['content.post.templatecomments.comments.comment.quotebuttons'] : ''
    ));
    
    $index = $from;
    $class1 = $theme->templates['content.post.templatecomments.comments.comment.class1'];
    $class2 = $theme->templates['content.post.templatecomments.comments.comment.class2'];
    foreach ($items as $id) {
      $comment->id = $id;
      $args->index = ++$index;
      $args->indexplus = $index + 1;
      $args->class = ($index % 2) == 0 ? $class1 : $class2;
      $result .= $theme->parsearg($tml, $args);
    }
    unset(ttheme::$vars['comment']);
    
    if ($result == '') return '';
    
    if ($status == 'hold') {
      $tml = $theme->templates['content.post.templatecomments.holdcomments'];
    } else {
      $tml = $theme->templates['content.post.templatecomments.comments'];
    }
    
    $args->from = $from + 1;
    $args->comment = $result;
    return $theme->parsearg($tml, $args);
  }
  
}//class

class tcomment extends tdata {
  private static $md5 = array();
  private $_posted;
  
  public function __construct($id = 0) {
    if (!isset($id)) return false;
    parent::__construct();
    $this->table = 'comments';
    $id = (int) $id;
    if ($id > 0) $this->setid($id);
  }
  
  public function setid($id) {
    $comments = tcomments::i();
    $this->data = $comments->getitem($id);
    if (!isset($this->data['name'])) $this->data = $this->data + tusers::i()->getitem($this->data['author']);
    $this->_posted = false;
  }
  
  public function save() {
    extract($this->data, EXTR_SKIP);
    $this->db->UpdateAssoc(compact('id', 'post', 'author', 'parent', 'posted', 'status', 'content'));
    
    $this->getdb($this->rawtable)->UpdateAssoc(array(
    'id' => $id,
    'modified' => sqldate(),
    'rawcontent' => $rawcontent,
    'hash' => basemd5($rawcontent)
    ));
  }
  
  public function getauthorlink() {
    $name = $this->data['name'];
    $website = $this->data['website'];
    if ($website == '')  return $name;
    
    $manager = tcommentmanager::i();
    if ($manager->hidelink || ($this->trust <= $manager->trustlevel)) return $name;
    $rel = $manager->nofollow ? 'rel="nofollow"' : '';
    if ($manager->redir) {
      return sprintf('<a %s href="%s/comusers.htm%sid=%d">%s</a>',$rel,
      litepublisher::$site->url, litepublisher::$site->q, $this->author, $name);
    } else {
      if (!strbegin($website, 'http://')) $website = 'http://' . $website;
      return sprintf('<a class="url fn" %s href="%s" itemprop="url">%s</a>',
      $rel,$website, $name);
    }
  }
  
  public function getdate() {
    $theme = ttheme::i();
    return tlocal::date($this->posted, $theme->templates['content.post.templatecomments.comments.comment.date']);
  }
  
  public function Getlocalstatus() {
    return tlocal::get('commentstatus', $this->status);
  }
  
  public function getposted() {
    if ($this->_posted) return $this->_posted;
    return $this->_posted = strtotime($this->data['posted']);
  }
  
  public function setposted($date) {
    $this->data['posted'] = sqldate($date);
    $this->_posted = $date;
  }
  
  public function  gettime() {
    return date('H:i', $this->posted);
  }
  
  public function  getiso() {
    return date('c', $this->posted);
  }
  
  public function  getrfc() {
    return date('r', $this->posted);
  }
  
  public function geturl() {
    $post = tpost::i($this->post);
    return $post->link . "#comment-$this->id";
  }
  
  public function getposttitle() {
    $post = tpost::i($this->post);
    return $post->title;
  }
  
  public function getrawcontent() {
    if (isset($this->data['rawcontent'])) return $this->data['rawcontent'];
    $comments = tcomments::i($this->post);
    return $comments->raw->getvalue($this->id, 'rawcontent');
  }
  
  public function setrawcontent($s) {
    $this->data['rawcontent'] = $s;
    $filter = tcontentfilter::i();
    $this->data['content'] = $filter->filtercomment($s);
  }
  
  public function getip() {
    if (isset($this->data['ip'])) return $this->data['ip'];
    $comments = tcomments::i($this->post);
    return $comments->raw->getvalue($this->id, 'ip');
  }
  
  public function getmd5email() {
    $email = $this->data['email'];
    if ($email) {
      if (isset(self::$md5[$email])) return self::$md5[$email];
      $md5 = md5($email);
      self::$md5[$email] = $md5;
      return $md5;
    }
    return '';
  }
  
  public function getgravatar() {
    if ($md5email = $this->getmd5email()) {
      return sprintf('<img class="avatar photo" src="http://www.gravatar.com/avatar/%s?s=90&amp;r=g&amp;d=wavatar" title="%2$s" alt="%2$s"/>', $md5email, $this->name);
    } else {
      return '';
    }
  }
  
}//class

//comments.manager.class.php
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
      if ($this->trustlevel > intval($users->getvalue($idauthor, 'trust'))) {
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

//comments.form.class.php
class tcommentform extends tevents {
  public $helper;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename ='commentform';
    $this->cache = false;
    $this->addevents('oncomuser');
  }
  
  public function request($arg) {
    if (litepublisher::$options->commentsdisabled) return 404;
    if ( 'POST' != $_SERVER['REQUEST_METHOD'] ) {
      return "<?php
      header('HTTP/1.1 405 Method Not Allowed', true, 405);
      header('Allow: POST');
      header('Content-Type: text/plain');
      ?>";
    }
    
    tguard::post();
    return $this->dorequest($_POST);
  }
  
  public function dorequest(array $args) {
    if (isset($args['confirmid'])) return $this->confirm_recevied($args['confirmid']);
    return $this->processform($args, false);
  }
  
  public function getshortpost($id) {
    $id = (int) $id;
    if ($id == 0) return false;
    $db = litepublisher::$db;
    return $db->selectassoc("select id, idurl, idperm, status, comstatus, commentscount from $db->posts where id = $id");
  }
  
  public function invalidate(array $shortpost) {
    $lang = tlocal::i('comment');
    if(!$shortpost) {
      return $this->geterrorcontent($lang->postnotfound);
    }
    
    if ($shortpost['status'] != 'published')  {
      return $this->geterrorcontent($lang->commentondraft);
    }
    
    if ($shortpost['comstatus'] == 'closed') {
      return $this->geterrorcontent($lang->commentsdisabled);
    }
    
    return false;
  }
  
  public function processform(array $values, $confirmed) {
    $lang = tlocal::i('comment');
    if (trim($values['content']) == '') return $this->geterrorcontent($lang->emptycontent);
    if (!$this->checkspam(isset($values['antispam']) ? $values['antispam'] : ''))          {
      return $this->geterrorcontent($lang->spamdetected);
    }
    
    $shortpost= $this->getshortpost(isset($values['postid']) ? (int) $values['postid'] : 0);
    if ($err = $this->invalidate($shortpost)) return $err;
    if (intval($shortpost['idperm']) > 0) {
      $post = tpost::i((int) $shortpost['id']);
      $perm = tperm::i($post->idperm);
      if (!$perm->hasperm($post)) return 403;
    }
    
    $cm = tcommentmanager::i();
    if ($cm->checkduplicate && $cm->is_duplicate($shortpost['id'], $values['content']) ) {
      return $this->geterrorcontent($lang->duplicate);
    }
    
    unset($values['submitbutton']);
    
    if (!$confirmed) $values['ip'] = preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR']);
    if (litepublisher::$options->ingroups($cm->idgroups)) {
      if (!$confirmed && $cm->confirmlogged)  return $this->request_confirm($values, $shortpost);
      $iduser = litepublisher::$options->user;
    } else {
      switch ($shortpost['comstatus']) {
        case 'reg':
        return $this->geterrorcontent($lang->reg);
        
        case 'guest':
        if (!$confirmed && $cm->confirmguest)  return $this->request_confirm($values, $shortpost);
        $iduser = $cm->idguest;
        break;
        
        case 'comuser':
        //hook in regservices social plugin
        if ($r = $this->oncomuser($values, $confirmed)) return $r;
        if (!$confirmed && $cm->confirmcomuser)  return $this->request_confirm($values, $shortpost);
        if ($err = $this->processcomuser($values)) return $err;
        
        $users = tusers::i();
        if ($iduser =$users->emailexists($values['email'])) {
          if ('comuser' != $users->getvalue($iduser, 'status')) {
            return $this->geterrorcontent($lang->emailregistered);
          }
        } else {
          $iduser = $cm->addcomuser($values['name'], $values['email'], $values['url'], $values['ip']);
        }
        
        $cookies = array();
        foreach (array('name', 'email', 'url') as $field) {
          $cookies["comuser_$field"] = $values[$field];
        }
        break;
      }
    }
    
    $user = tusers::i()->getitem($iduser);
    if ('hold' == $user['status']) {
      return $this->geterrorcontent($lang->holduser);
    }
    
    if (!$cm->canadd( $iduser)) {
      return $this->geterrorcontent($lang->toomany);
    }
    
    if (!$cm->add($shortpost['id'], $iduser, $values['content'], $values['ip'])) {
      return $this->geterrorcontent($lang->spamdetected );
    }
    
    //subscribe by email
    switch ($user['status']) {
      case 'approved':
      if ($user['email'] != '') {
        // subscribe if its first comment
      if (1 == tcomments::i()->db->getcount("post = {$shortpost['id']} and author = $iduser")) {
          if ('enabled' == tuseroptions::i()->getvalue($iduser, 'subscribe')) {
            tsubscribers::i()->update($shortpost['id'], $iduser , true);
          }
        }
      }
      break;
      
      case 'comuser':
      if (('comuser' == $shortpost['comstatus']) && $cm->comuser_subscribe) {
        tsubscribers::i()->update($shortpost['id'], $iduser , $values['subscribe']);
      }
      break;
    }
    
    //$post->lastcommenturl;
    $shortpost['commentscount']++;
    if (!litepublisher::$options->commentpages || ($shortpost['commentscount'] <= litepublisher::$options->commentsperpage)) {
      $c = 1;
    } else {
      $c = ceil($shortpost['commentscount'] / litepublisher::$options->commentsperpage);
    }
    
    $url = litepublisher::$urlmap->getvalue($shortpost['idurl'], 'url');
    if (($c > 1) && !litepublisher::$options->comments_invert_order) $url = rtrim($url, '/') . "/page/$c/";
    
    litepublisher::$urlmap->setexpired($shortpost['idurl']);
    return $this->sendresult(litepublisher::$site->url . $url, isset($cookies) ? $cookies : array());
  }
  
  public function confirm_recevied($confirmid) {
    $lang = tlocal::i('comment');
    tsession::start(md5($confirmid));
    if (!isset($_SESSION['confirmid']) || ($confirmid != $_SESSION['confirmid'])) {
      session_destroy();
      return $this->geterrorcontent($lang->notfound);
    }
    
    $values = $_SESSION['values'];
    session_destroy();
    return $this->processform($values, true);
  }
  
  public function request_confirm(array $values, array $shortpost) {
    /*
    $kept = tkeptcomments::i();
    $kept->deleteold();
    */
    $values['date'] = time();
    $values['ip'] = preg_replace( '/[^0-9., ]/', '',$_SERVER['REMOTE_ADDR']);
    
    $confirmid = md5uniq();
    if ($sess = tsession::start(md5($confirmid))) $sess->lifetime = 900;
    $_SESSION['confirmid'] = $confirmid;
    $_SESSION['values'] = $values;
    session_write_close();
    
    if (intval($shortpost['idperm']) > 0) {
      $header = $this->getpermheader($shortpost);
      return $header . $this->confirm($confirmid);
    }
    
    return $this->confirm($confirmid);
  }
  
  public function getpermheader(array $shortpost) {
    $urlmap = litepublisher::$urlmap;
    $url = $urlmap->url;
    $saveitem = $urlmap->itemrequested;
    $urlmap->itemrequested = $urlmap->getitem($shortpost['idurl']);
    $urlmap->url = $urlmap->itemrequested['url'];
    $post = tpost::i((int) $shortpost['id']);
    $perm = tperm::i($post->idperm);
    // not restore values because perm will be used this values
    return $perm->getheader($post);
  }
  
  private function getconfirmform($confirmid) {
    ttheme::$vars['lang'] = tlocal::i('comment');
    $args = targs::i();
    $args->confirmid = $confirmid;
    $theme = tsimplecontent::gettheme();
    return $theme->parsearg(
    $theme->templates['content.post.templatecomments.confirmform'], $args);
  }
  
  //htmlhelper
  public function confirm($confirmid) {
    if (isset($this->helper) && ($this != $this->helper)) return $this->helper->confirm($confirmid);
    return tsimplecontent::html($this->getconfirmform($confirmid));
  }
  
  public function geterrorcontent($s) {
    if (isset($this->helper) && ($this != $this->helper)) return $this->helper->geterrorcontent($s);
    return tsimplecontent::content($s);
  }
  
  private function checkspam($s) {
    if  (!($s = @base64_decode($s))) return false;
    $sign = 'superspamer';
    if (!strbegin($s, $sign)) return false;
    $TimeKey = (int) substr($s, strlen($sign));
    return time() < $TimeKey;
  }
  
  public function processcomuser(array &$values) {
    $lang = tlocal::i('comment');
    if (empty($values['name']))       return $this->geterrorcontent($lang->emptyname);
    $values['name'] = tcontentfilter::escape($values['name']);
    $values['email'] = isset($values['email']) ? strtolower(trim($values['email'])) : '';
    if (!tcontentfilter::ValidateEmail($values['email'])) {
      return $this->geterrorcontent($lang->invalidemail);
    }
    
    $values['url'] = isset($values['url']) ? tcontentfilter::escape(tcontentfilter::clean_website($values['url'])) : '';
    $values['subscribe'] = isset($values['subscribe']);
  }
  
  public function sendresult($link, $cookies) {
    if (isset($this->helper) && ($this != $this->helper)) return $this->helper->sendresult($link, $cookies);
    foreach ($cookies as $name => $value) {
      setcookie($name, $value, time() + 30000000,  '/', false);
    }
    
    return litepublisher::$urlmap->redir($link);
  }
  
}//class

//comments.subscribers.class.php
class tsubscribers extends titemsposts {
  public $blacklist;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = true;
    parent::create();
    $this->table = 'subscribers';
    $this->basename = 'subscribers';
    $this->data['fromemail'] = '';
    $this->data['enabled'] = true;
    $this->addmap('blacklist', array());
  }
  
  public function load() {
    return tfilestorage::load($this);
  }
  
  public function save() {
    if ($this->lockcount > 0) return;
    tfilestorage::save($this);
  }
  
  public function update($pid, $uid, $subscribed) {
    if ($subscribed == $this->exists($pid, $uid)) return;
    $this->remove($pid, $uid);
    $user = tusers::i()->getitem($uid);
    if (in_array($user['email'], $this->blacklist)) return;
    if ($subscribed) $this->add($pid, $uid);
  }
  
  public function setenabled($value) {
    if ($this->enabled != $value) {
      $this->data['enabled'] = $value;
      $this->save();
      $comments = tcomments::i();
      if ($value) {
        tposts::i()->added = $this->postadded;
        
        $comments->lock();
        $comments->added = $this->sendmail;
        $comments->onapproved = $this->sendmail;
        $comments->unlock();
      } else {
        $comments->unbind($this);
        tposts::i()->delete_event_class('added', get_class($this));
      }
    }
  }
  
  public function postadded($idpost) {
    $post = tpost::i($idpost);
    if ($post->author <= 1) return;
    
    $useroptions = tuseroptions::i();
    if ('enabled' == $useroptions->getvalue($post->author, 'authorpost_subscribe')) {
      $this->add($idpost, $post->author);
    }
  }
  
  public function getlocklist() {
    return implode("\n", $this->blacklist);
  }
  
  public function setlocklist($s) {
    $this->setblacklist(explode("\n", strtolower(trim($s))));
  }
  
  public function setblacklist(array $a) {
    $a = array_unique($a);
    array_delete_value($a, '');
    $this->data['blacklist'] = $a;
    $this->save();
    
    $dblist = array();
    foreach ($a as $s) {
      if ($s == '') continue;
      $dblist[] = dbquote($s);
    }
    if (count($dblist) > 0) {
      $db = $this->db;
      $db->delete("item in (select id from $db->users where email in (" . implode(',', $dblist) . '))');
    }
  }
  
  public function sendmail($id) {
    if (!$this->enabled) return;
    $comments = tcomments::i();
    if (!$comments->itemexists($id)) return;
    $item = $comments->getitem($id);
    if (($item['status'] != 'approved')) return;
    
    if (litepublisher::$options->mailer == 'smtp') {
      tcron::i()->add('single', get_class($this),  'cronsendmail', (int) $id);
    } else {
      $this->cronsendmail($id);
    }
  }
  
  public function cronsendmail($id) {
    $comments = tcomments::i();
    try {
      $item = $comments->getitem($id);
    } catch (Exception $e) {
      return;
    }
    
    $subscribers  = $this->getitems($item['post']);
    if (!$subscribers  || (count($subscribers ) == 0)) return;
    $comment = $comments->getcomment($id);
    ttheme::$vars['comment'] = $comment;
    tlocal::usefile('mail');
    $lang = tlocal::i('mailcomments');
    $theme = ttheme::i();
    $args = new targs();
    
    $subject = $theme->parsearg($lang->subscribesubj, $args);
    $body = $theme->parsearg($lang->subscribebody, $args);
    
    $body .= "\n";
    $adminurl = litepublisher::$site->url . '/admin/subscribers/';
    
    $users = tusers::i();
    $users->loaditems($subscribers);
    $list = array();
    foreach ($subscribers as $uid) {
      $user = $users->getitem($uid);
      if ($user['status'] == 'hold') continue;
      $email = $user['email'];
      if (empty($email)) continue;
      if ($email == $comment->email) continue;
      if (in_array($email, $this->blacklist)) continue;
      
      $admin =  $adminurl;
      if ('comuser' == $user['status']) {
        $admin .= litepublisher::$site->q . 'auth=';
        if (empty($user['cookie'])) {
          $user['cookie'] = md5uniq();
          $users->setvalue($user['id'], 'cookie', $user['cookie']);
        }
        $admin .= rawurlencode($user['cookie']);
      }
      
      $list[] = array(
      'fromname' => litepublisher::$site->name,
      'fromemail' =>  $this->fromemail,
      'toname' => $user['name'],
      'toemail' =>  $email,
      'subject' => $subject,
      'body' => $body . $admin
      );
    }
    
    if (count($list)) tmailer::sendlist($list);
  }
  
}//class

//template.comments.class.php
class ttemplatecomments extends tevents {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'comments.templates';
  }
  
  public function getcomments($idpost) {
    $result = '';
    $idpost = (int) $idpost;
    $post = tpost::i($idpost);
    $comments = tcomments::i($idpost);
    $lang = tlocal::i('comment');
    $list = $comments->getcontent();
    
    $theme = $post->theme;
    $args = new targs();
    $args->count = $post->cmtcount;
    $result .= $theme->parsearg($theme->templates['content.post.templatecomments.comments.count'], $args);
    $result .= $list;
    
    if ((litepublisher::$urlmap->page == 1) && ($post->pingbackscount > 0))  {
      $pingbacks = tpingbacks::i($post->id);
      $result .= $pingbacks->getcontent();
    }
    
    if (!litepublisher::$options->commentsdisabled && ($post->comstatus != 'closed')) {
      $args->postid = $post->id;
      $args->antispam = base64_encode('superspamer' . strtotime ("+1 hour"));
      
      $cm = tcommentmanager::i();
      $result .=  sprintf('<?php if (litepublisher::$options->ingroups(array(%s))) {', implode(',', $cm->idgroups));
        //add hold list
        $result .= 'if ($ismoder = litepublisher::$options->ingroup(\'moderator\')) { ?>';
          $args->comment = '';
          $result .= $theme->parsearg($theme->templates['content.post.templatecomments.holdcomments'], $args);
          $result .= $this->loadhold;
        $result .= '<?php } ?>';
        
        $mesg = $this->logged;
        if ($cm->canedit || $cm->candelete) $mesg .= "\n" . $this->adminpanel;
        $args->mesg = $this->fixmesg($mesg, $theme);
        $result .= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
        $result .= $this->getjs(($post->idperm == 0) && $cm->confirmlogged, 'logged');
      $result .= '<?php } else { ?>';
        
        switch ($post->comstatus) {
          case 'reg':
          $mesg = $this->reqlogin;
          if (litepublisher::$options->reguser) $mesg .= "\n" . $this->regaccount;
          $args->mesg = $this->fixmesg($mesg, $theme);
          $result .= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
          break;
          
          case 'guest':
          $result .= $this->getjs(($post->idperm == 0) && $cm->confirmguest, 'guest');
          $mesg = $this->guest;
          if (litepublisher::$options->reguser) $mesg .= "\n" . $this->regaccount;
          $args->mesg = $this->fixmesg($mesg, $theme);
          $result .= $theme->parsearg($theme->templates['content.post.templatecomments.regform'], $args);
          break;
          
          case 'comuser':
          $result .= $this->getjs(($post->idperm == 0) && $cm->confirmcomuser, 'comuser');
          $mesg = $this->comuser;
          if (litepublisher::$options->reguser) $mesg .= "\n" . $this->regaccount;
          $args->mesg = $this->fixmesg($mesg, $theme);
          
          foreach (array('name', 'email', 'url') as $field) {
            $args->$field = "<?php echo (isset(\$_COOKIE['comuser_$field']) ? \$_COOKIE['comuser_$field'] : ''); ?>";
          }
          
          $args->subscribe = false;
          $args->content = '';
          
          $result .= $theme->parsearg($theme->templates['content.post.templatecomments.form'], $args);
          break;
        }
        
      $result .= '<?php } ?>';
    } else {
      $result .= $theme->parse($theme->templates['content.post.templatecomments.closed']);
    }
    return $result;
  }
  
  public function fixmesg($mesg, $theme) {
    return $theme->parse(str_replace('backurl=', 'backurl=' . urlencode(litepublisher::$urlmap->url),
    str_replace('&backurl=', '&amp;backurl=', $mesg)));
  }
  
  public function getjs($confirmcomment, $logstatus) {
    $cm = tcommentmanager::i();
    $result = sprintf('<script type="text/javascript">
    ltoptions.theme.comments = $.extend(true, ltoptions.theme.comments, %s%s);
    </script>',
    json_encode(array(
    'confirmcomment' => $confirmcomment,
    'comuser' => 'comuser' == $logstatus,
    'canedit' => $cm->canedit,
    'candelete' => $cm->candelete,
    )),
  $logstatus == 'logged' ? ', {ismoder: <?php echo ($ismoder ? \'true\' : \'false\'); ?>}' : '');
    
    $template = ttemplate::I();
    $result .= $template->getjavascript($template->jsmerger_comments);
    return  $result;
    
    /*
    $result .= $template->getjavascript('/js/litepublisher/confirmcomment.js');
    $result .= $template->getjavascript($template->jsmerger_moderate);
    $result .= $template->getjavascript('/js/litepublisher/moderate.js');
    
    return  $result;
    */
  }
  
} //class

//widget.comments.class.php
class tcommentswidget extends twidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.comments';
    $this->cache = 'include';
    $this->template = 'comments';
    $this->adminclass = 'tadminmaxcount';
    $this->data['maxcount'] =  7;
  }
  
  public function getdeftitle() {
    return tlocal::get('default', 'recentcomments');
  }
  
  public function getcontent($id, $sidebar) {
    $recent = $this->getrecent($this->maxcount);
    if (count($recent) == 0) return '';
    $result = '';
    $theme = ttheme::i();
    $tml = $theme->getwidgetitem('comments', $sidebar);
    $url = litepublisher::$site->url;
    $args = targs::i();
    $args->onrecent = tlocal::get('comment', 'onrecent');
    foreach ($recent as $item) {
      $args->add($item);
      $args->link = $url . $item['posturl'];
      $args->content = tcontentfilter::getexcerpt($item['content'], 120);
      $result .= $theme->parsearg($tml,$args);
    }
    return $theme->getwidgetcontent($result, 'comments', $sidebar);
  }
  
  public function changed() {
    $this->expire();
  }
  
  public function getrecent($count, $status = 'approved') {
    $db = litepublisher::$db;
    $result = $db->res2assoc($db->query("select $db->comments.*,
    $db->users.name as name, $db->users.email as email, $db->users.website as url,
    $db->posts.title as title, $db->posts.commentscount as commentscount,
    $db->urlmap.url as posturl
    from $db->comments, $db->users, $db->posts, $db->urlmap
    where $db->comments.status = '$status' and
    $db->users.id = $db->comments.author and
    $db->posts.id = $db->comments.post and
    $db->urlmap.id = $db->posts.idurl and
    $db->posts.status = 'published' and
    $db->posts.idperm = 0
    order by $db->comments.posted desc limit $count"));
    
    if (litepublisher::$options->commentpages && !litepublisher::$options->comments_invert_order) {
      foreach ($result as $i => $item) {
        $page = ceil($item['commentscount'] / litepublisher::$options->commentsperpage);
        if ($page > 1) $result[$i]['posturl']= rtrim($item['posturl'], '/') . "/page/$page/";
      }
    }
    return $result;
  }
  
}//class

