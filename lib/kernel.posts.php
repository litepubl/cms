<?php
//items.posts.class.php
class titemsposts extends titems {
  public $tablepost;
  public $postprop;
  public $itemprop;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'itemsposts';
    $this->table = 'itemsposts';
    $this->tablepost = 'posts';
    $this->postprop = 'post';
    $this->itemprop = 'item';
  }
  
  public function add($idpost, $iditem) {
    $this->db->insert(array(
    $this->postprop => $idpost,
    $this->itemprop => $iditem
    ));
    $this->added();
  }
  
  public function exists($idpost, $iditem) {
    return $this->db->exists("$this->postprop = $idpost and $this->itemprop = $iditem");
  }
  
  public function remove($idpost, $iditem) {
    return $this->db->delete("$this->postprop = $idpost and $this->itemprop = $iditem");
  }
  
  public function delete($idpost) {
    return $this->deletepost($idpost);
  }
  
  public function deletepost($idpost) {
    $db = $this->db;
    $result = $db->res2id($db->query("select $this->itemprop from $this->thistable where $this->postprop = $idpost"));
    $db->delete("$this->postprop = $idpost");
    return $result;
  }
  
  public function deleteitem($iditem) {
    $this->db->delete("$this->itemprop = $iditem");
    $this->deleted();
  }
  
  public function setitems($idpost, array $items) {
    array_clean($items);
    $db = $this->db;
    $old = $this->getitems($idpost);
    $add = array_diff($items, $old);
    $delete = array_diff($old, $items);
    
    if (count($delete)) $db->delete("$this->postprop = $idpost and $this->itemprop in (" . implode(', ', $delete) . ')');
    if (count($add)) {
      $vals = array();
      foreach ($add as $iditem) {
        $vals[]= "($idpost, $iditem)";
      }
      $db->exec("INSERT INTO $this->thistable ($this->postprop, $this->itemprop) values " . implode(',', $vals) );
    }
    
    return array_merge($old, $add);
  }
  
  public function getitems($idpost) {
    return litepublisher::$db->res2id(litepublisher::$db->query("select $this->itemprop from $this->thistable where $this->postprop = $idpost"));
  }
  
  public function getposts($iditem) {
    return litepublisher::$db->res2id(litepublisher::$db->query("select $this->postprop from $this->thistable where $this->itemprop = $iditem"));
  }
  
  public function getpostscount($ititem) {
    $db = $this->getdb($this->tablepost);
    return $db->getcount("$db->prefix$this->tablepost.status = 'published' and id in (select $this->postprop from $this->thistable where $this->itemprop = $ititem)");
  }
  
  public function updateposts(array $list, $propname) {
    $db = $this->db;
    foreach ($list as $idpost) {
      $items = $this->getitems($idpost);
      $db->table = $this->tablepost;
      $db->setvalue($idpost, $propname, implode(', ', $items));
    }
  }
  
}//class

class titemspostsowner extends titemsposts {
  private $owner;
  public function __construct($owner) {
    if (!isset($owner)) return;
    parent::__construct();
    $this->owner = $owner;
    $this->table = $owner->table . 'items';
  }
  
public function load() { }
public function save() { $this->owner->save(); }
public function lock() { $this->owner->lock(); }
public function unlock() { $this->owner->unlock(); }
  
}//class

//post.class.php
class tpost extends titem implements  itemplate {
  public $childdata;
  public $childtable;
  public $factory;
  public $syncdata;
  private $aprev;
  private $anext;
  private $_meta;
  private $_theme;
  private $_onid;
  
  public static function i($id = 0) {
    $id = (int) $id;
    if ($id > 0) {
      if (isset(self::$instances['post'][$id]))     return self::$instances['post'][$id];
      if ($result = self::loadpost($id)) {
        self::$instances['post'][$id] = $result;
        return $result;
      }
      return null;
    }
    return parent::iteminstance(__class__, $id);
  }
  
  public static function getinstancename() {
    return 'post';
  }
  
  public static function getchildtable() {
    return '';
  }
  
  public static function selectitems(array $items) {
    return array();
  }
  
  public static function select_child_items($table, array $items) {
    if (($table == '') || (count($items) == 0)) return array();
    $db = litepublisher::$db;
    $childtable =  $db->prefix . $table;
    $list = implode(',', $items);
    return $db->res2items($db->query("select $childtable.*
    from $childtable where id in ($list)"));
  }
  
  public static function newpost($class) {
    if (empty($class)) $class = __class__;
    return new $class();
  }
  
  protected function create() {
    $this->table = 'posts';
    $this->syncdata = array();
    //last binding, like cache
    $this->childtable = call_user_func_array(array(get_class($this), 'getchildtable'), array());
    
    $this->data= array(
    'id' => 0,
    'idview' => 1,
    'idurl' => 0,
    'parent' => 0,
    'author' => 0,
    'revision' => 0,
    'icon' => 0,
    'idperm' => 0,
    'class' => __class__,
    'posted' => 0,
    'modified' => 0,
    'url' => '',
    'title' => '',
    'title2' => '',
    'filtered' => '',
    'excerpt' => '',
    'rss' => '',
    'rawcontent' => false,
    'keywords' => '',
    'description' => '',
    'rawhead' => '',
    'moretitle' => '',
    'categories' => array(),
    'tags' => array(),
    'files' => array(),
    'status' => 'published',
    'comstatus' => litepublisher::$options->comstatus,
    'pingenabled' => litepublisher::$options->pingenabled,
    'password' => '',
    'commentscount' => 0,
    'pingbackscount' => 0,
    'pagescount' => 0,
    'pages' => array()
    );
    
    $this->data['childdata'] = &$this->childdata;
    $this->factory = litepublisher::$classes->getfactory($this);
    $posts = $this->factory->posts;
    foreach ($posts->itemcoclasses as $class) {
      $coinstance = litepublisher::$classes->newinstance($class);
      $coinstance->post = $this;
      $this->coinstances[]  = $coinstance;
    }
  }
  
  public function __get($name) {
    if ($this->childtable) {
      if ($name == 'id') return $this->data['id'];
      if (method_exists($this, $get = 'get' . $name))   return $this->$get();
      if (array_key_exists($name, $this->childdata)) return $this->childdata[$name];
    }
    
    // tags and categories theme tag
    switch ($name) {
      case 'catlinks':
      return $this->get_taglinks('categories', false);
      
      case 'taglinks':
      return $this->get_taglinks('tags', false);
      
      case 'excerptcatlinks':
      return $this->get_taglinks('categories', true);
      
      case 'excerpttaglinks':
      return $this->get_taglinks('tags', true);
    }
    
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if ($this->childtable) {
      if ($name == 'id') return $this->setid($value);
      if (method_exists($this, $set = 'set'. $name)) return $this->$set($value);
      if (array_key_exists($name, $this->childdata)) {
        $this->childdata[$name] = $value;
        return true;
      }
    }
    return parent::__set($name, $value);
  }
  
  public function __isset($name) {
    return parent::__isset($name) || ($this->childtable && array_key_exists($name, $this->childdata) );
  }
  
  //db
  public function afterdb() {
    //$this->childdata['reproduced'] = $this->childdata['reproduced'] == '1';
  }
  
  public function beforedb() {
    //if ($this->childdata['closed'] == '') $this->childdata['closed'] = sqldate();
  }
  
  public function load() {
    if ($result = $this->LoadFromDB()) {
      foreach ($this->coinstances as $coinstance) $coinstance->load();
    }
    return $result;
  }
  
  protected function LoadFromDB() {
    if ($a = self::getassoc($this->id)) {
      $this->setassoc($a);
      return true;
    }
    return false;
  }
  
  public static function loadpost($id) {
    if ($a = self::getassoc($id)) {
      $self = self::newpost($a['class']);
      $self->setassoc($a);
      return $self;
    }
    return false;
  }
  
  public static function getassoc($id) {
    $db = litepublisher::$db;
    return $db->selectassoc("select $db->posts.*, $db->urlmap.url as url  from $db->posts, $db->urlmap
    where $db->posts.id = $id and  $db->urlmap.id  = $db->posts.idurl limit 1");
  }
  
  public function setassoc(array $a) {
    $trans = $this->factory->gettransform($this);
    $trans->setassoc($a);
    if ($this->childtable) {
      if ($a = $this->getdb($this->childtable)->getitem($this->id)) {
        $this->childdata = $a;
        $this->afterdb();
      }
    }
  }
  
  public function save() {
    if ($this->lockcount > 0) return;
    $this->SaveToDB();
    foreach ($this->coinstances as $coinstance) $coinstance->save();
  }
  
  protected function SaveToDB() {
    $this->factory->gettransform($this)->save($this);
    if ($this->childtable) {
      $this->beforedb();
      $this->childdata['id'] = $this->id;
      $this->getdb($this->childtable)->updateassoc($this->childdata);
    }
  }
  
  public function create_id() {
    $id = $this->factory->add($this);
    $this->setid($id);
    if ($this->childtable) {
      $this->beforedb();
      $this->childdata['id'] = $id;
      $this->getdb($this->childtable)->insert($this->childdata);
    }
    
    $this->idurl = $this->create_url();
    $this->db->setvalue($id, 'idurl', $this->idurl);
    $this->onid();
    
    return $id;
  }
  
  public function create_url() {
    return litepublisher::$urlmap->add($this->url, get_class($this), (int) $this->id);
  }
  
  public function onid() {
    if (isset($this->_onid) && count($this->_onid) > 0) {
      foreach ($this->_onid as  $call) {
        try {
          call_user_func ($call, $this);
        } catch (Exception $e) {
          litepublisher::$options->handexception($e);
        }
      }
      unset($this->_onid);
    }
    
    if (isset($this->_meta)) {
      $this->_meta->id = $this->id;
      $this->_meta->save();
    }
  }
  
  public function setonid($call) {
    if (!is_callable($call)) $this->error('Event onid not callable');
    if (isset($this->_onid)) {
      $this->_onid[] = $call;
    } else {
      $this->_onid = array($call);
    }
  }
  
  public function free() {
    foreach ($this->coinstances as $coinstance) $coinstance->free();
    if (isset($this->_meta)) $this->_meta->free();
    unset($this->aprev, $this->anext, $this->_meta, $this->_theme, $this->_onid);
    parent::free();
  }
  
  public function getcomments() {
    return $this->factory->getcomments($this->id);
  }
  
  public function getpingbacks() {
    return $this->factory->getpingbacks($this->id);
  }
  
  public function getprev() {
    if (!is_null($this->aprev)) return $this->aprev;
    $this->aprev = false;
    if ($id = $this->db->findid("status = 'published' and posted < '$this->sqldate' order by posted desc")) {
      $this->aprev = self::i($id);
    }
    return $this->aprev;
  }
  
  public function getnext() {
    if (!is_null($this->anext)) return $this->anext;
    $this->anext = false;
    if ($id = $this->db->findid("status = 'published' and posted > '$this->sqldate' order by posted asc")) {
      $this->anext = self::i($id);
    }
    return $this->anext;
  }
  
  public function getmeta() {
    if (!isset($this->_meta)) $this->_meta = $this->factory->getmeta($this->id);
    return $this->_meta;
  }
  
  public function Getlink() {
    return litepublisher::$site->url . $this->url;
  }
  
  public function Setlink($link) {
    if ($a = @parse_url($link)) {
      if (empty($a['query'])) {
        $this->url = $a['path'];
      } else {
        $this->url = $a['path'] . '?' . $a['query'];
      }
    }
  }
  
  public function settitle($title) {
    $this->data['title'] = tcontentfilter::escape(tcontentfilter::unescape($title));
  }
  
  public function gettheme() {
    ttheme::$vars['post'] = $this;
    if (isset($this->_theme)) return $this->_theme;
    $this->_theme = isset(ttemplate::i()->view) ? ttemplate::i()->view->theme : tview::getview($this)->theme;
    return $this->_theme;
  }
  
  public function parsetml($path) {
    $theme = $this->theme;
    return $theme->parse($theme->templates[$path]);
  }
  
  public function getextra() {
    $theme = $this->theme;
    return $theme->parse($theme->extratml);
  }
  
  public function getbookmark() {
    return $this->theme->parse('<a href="$post.link" rel="bookmark" title="$lang.permalink $post.title">$post.iconlink$post.title</a>');
  }
  
  public function getrsscomments() {
    return litepublisher::$site->url . "/comments/$this->id.xml";
  }
  
  public function Getisodate() {
    return date('c', $this->posted);
  }
  
  public function Getpubdate() {
    return date('r', $this->posted);
  }
  
  public function Setpubdate($date) {
    $this->data['posted'] = strtotime($date);
  }
  
  public function getsqldate() {
    return sqldate($this->posted);
  }
  
  public function getidimage() {
    if (count($this->files) == 0) return false;
    $files = $this->factory->files;
    foreach ($this->files as $id) {
      $item = $files->getitem($id);
      if ('image' == $item['media']) return $id;
    }
    return false;
  }
  
  public function getimage() {
    if ($id = $this->getidimage()) {
      return $this->factory->files->geturl($id);
    }
    return false;
  }
  
  public function getthumb() {
    if (count($this->files) == 0) return false;
    $files = $this->factory->files;
    foreach ($this->files as $id) {
      $item = $files->getitem($id);
      if (intval($item['preview'])) return $files->geturl($item['preview']);
    }
    return false;
  }
  
  public function getfirstimage() {
    if (count($this->files) == 0) return '';
    $files = $this->factory->files;
    foreach ($this->files as $id) {
      $item = $files->getitem($id);
      if ('image' == $item['media']) {
        $args = new targs();
        $args->add($item);
        $args->link = litepublisher::$site->files . '/files/' . $item['filename'];
        $preview = new tarray2prop();
        $preview->array = $files->getitem($item['preview']);
        $preview->link = litepublisher::$site->files . '/files/' . $preview->filename;
        ttheme::$vars['preview'] = $preview;
        $theme = $this->theme;
        $result = $theme->parsearg($theme->templates['content.excerpts.excerpt.firstimage'], $args);
        unset(ttheme::$vars['preview']);
        return $result;
      }
    }
    return '';
  }
  
  
  //template
  protected function get_taglinks($name, $excerpt) {
    $items = $this->$name;
    if (!count($items)) return '';
    
    $theme = $this->theme;
    $tmlpath= $excerpt ? 'content.excerpts.excerpt' : 'content.post';
    $tmlpath .= $name == 'tags' ? '.taglinks' : '.catlinks';
    $tmlitem = $theme->templates[$tmlpath . '.item'];
    
    $tags= strbegin($name, 'tag') ? $this->factory->tags : $this->factory->categories;
    $tags->loaditems($items);
    
    $args = new targs();
    $list = array();
    
    foreach ($items as $id) {
      $item = $tags->getitem($id);
      $args->add($item);
      if (($item['icon'] == 0) || litepublisher::$options->icondisabled) {
        $args->icon = '';
      } else {
        $files = $this->factory->files;
        if ($files->itemexists($item['icon'])) {
          $args->icon = $files->geticon($item['icon']);
        } else {
          $args->icon = '';
        }
      }
      $list[] = $theme->parsearg($tmlitem,  $args);
    }
    
    $args->items =     ' ' . implode($theme->templates[$tmlpath . '.divider'] , $list);
    $result = $theme->parsearg($theme->templates[$tmlpath], $args);
    $this->factory->posts->callevent('ontags', array($tags, $excerpt, &$result));
    return $result;
  }
  
  public function getdate() {
    return tlocal::date($this->posted, $this->theme->templates['content.post.date']);
  }
  
  public function getexcerptdate() {
    return tlocal::date($this->posted, $this->theme->templates['content.excerpts.excerpt.date']);
  }
  
  public function getday() {
    return date($this->posted, 'D');
  }
  
  public function getmonth() {
    return tlocal::date($this->posted, 'M');
  }
  
  public function getyear() {
    return date($this->posted, 'Y');
  }
  
  public function getmorelink() {
    if ($this->moretitle == '') return '';
    return $this->parsetml('content.excerpts.excerpt.morelink');
  }
  
  public function gettagnames() {
    if (count($this->tags) == 0) return '';
    $tags = $this->factory->tags;
    return implode(', ', $tags->getnames($this->tags));
  }
  
  public function settagnames($names) {
    $tags = $this->factory->tags;
    $this->tags=  $tags->createnames($names);
  }
  
  public function getcatnames() {
    if (count($this->categories) == 0)  return '';
    $categories = $this->factory->categories;
    return implode(', ', $categories->getnames($this->categories));
  }
  
  public function setcatnames($names) {
    $categories = $this->factory->categories;
    $this->categories = $categories->createnames($names);
    if (count($this->categories ) == 0) {
      $defaultid = $categories->defaultid;
      if ($defaultid > 0) $this->data['categories '][] =  $dfaultid;
    }
  }
  
  public function getcategory() {
    if ($idcat = $this->getidcat()) {
      return $this->factory->categories->getname($idcat);
    }
    
    return '';
  }
  
  public function getidcat() {
    if (($cats = $this->categories) && count($cats)) return $cats[0];
    return 0;
  }
  
  //ITemplate
  
  public function request($id) {
    parent::request((int) $id);
    if ($this->status != 'published') {
      if (!litepublisher::$options->show_draft_post) return 404;
      $groupname = litepublisher::$options->group;
      if (($groupname == 'admin') || ($groupname == 'editor')) return;
      if ($this->author == litepublisher::$options->user) return;
      return 404;
    }
  }
  
  public function gettitle() {
    //if ($this->data['title2'] != '') return $this->data['title2'];
    return $this->data['title'];
  }
  
  public function gethead() {
    $result = $this->rawhead;
    ttemplate::i()->ltoptions['idpost'] = $this->id;
    $theme = $this->theme;
    $result .= $theme->templates['head.post'];
    if ($prev = $this->prev) {
      ttheme::$vars['prev'] = $prev;
      $result .= $theme->templates['head.post.prev'];
    }
    
    if ($next = $this->next) {
      ttheme::$vars['next'] = $next;
      $result .= $theme->templates['head.post.next'];
    }
    
    if ($this->hascomm) {
      $lang = tlocal::i('comment');
      $result .= $theme->templates['head.post.rss'];
    }
    $result = $theme->parse($result);
    $this->factory->posts->callevent('onhead', array($this, &$result));
    return $result;
  }
  
  public function getanhead() {
    $result = '';
    $this->factory->posts->callevent('onanhead', array($this, &$result));
    return $result;
  }
  
  public function getkeywords() {
    return empty($this->data['keywords']) ? $this->Gettagnames() : $this->data['keywords'];
  }
  //fix for file version. For db must be deleted
  public function setkeywords($s) {
    $this->data['keywords'] = $s;
  }
  
  public function getdescription() {
    return $this->data['description'];
  }
  
  public function getidview() {
    return $this->data['idview'];
  }
  
  public function setidview($id) {
    if ($id != $this->idview) {
      $this->data['idview'] = $id;
      if ($this->id) $this->db->setvalue($this->id, 'idview', $id);
    }
  }
  
  public function setid_view($id_view) {
    $this->data['idview'] = $id_view;
  }
  
  public function geticonurl() {
    if ($this->icon == 0) return '';
    $files = $this->factory->files;
    if ($files->itemexists($this->icon)) return $files->geturl($this->icon);
    $this->icon = 0;
    $this->save();
    return '';
  }
  
  public function geticonlink() {
    if (($this->icon == 0) || litepublisher::$options->icondisabled) return '';
    $files = $this->factory->files;
    if ($files->itemexists($this->icon)) return $files->geticon($this->icon);
    $this->icon = 0;
    $this->save();
    return '';
  }
  
  public function setfiles(array $list) {
    array_clean($list);
    $this->data['files'] = $list;
  }
  
  public function getfilelist() {
    if ((count($this->files) == 0) || ((litepublisher::$urlmap->page > 1) &&   litepublisher::$options->hidefilesonpage)) return '';
    $files = $this->factory->files;
    return $files->getfilelist($this->files, false);
  }
  
  public function getexcerptfilelist() {
    if (count($this->files) == 0) return '';
    $files = $this->factory->files;
    return $files->getfilelist($this->files, true);
  }
  
  public function getindex_tml() {
    $theme = $this->theme;
    if (!empty($theme->templates['index.post'])) return $theme->templates['index.post'];
    return false;
  }
  
  public function getcont() {
    return $this->parsetml('content.post');
  }
  
  public function getcontexcerpt($lite) {
    //no use self theme because post in other context
    $theme = ttheme::i();
    $tml = $lite ? $theme->templates['content.excerpts.lite.excerpt'] : $theme->templates['content.excerpts.excerpt'];
    ttheme::$vars['post'] = $this;
    return $theme->parse($tml);
  }
  
  public function getrsslink() {
    if ($this->hascomm) {
      return $this->parsetml('content.post.rsslink');
    }
    return '';
  }
  
  public function onrssitem($item) {
  }
  
  public function getprevnext() {
    $prev = '';
    $next = '';
    $theme = $this->theme;
    if ($prevpost = $this->prev) {
      ttheme::$vars['prevpost'] = $prevpost;
      $prev = $theme->parse($theme->templates['content.post.prevnext.prev']);
    }
    if ($nextpost = $this->next) {
      ttheme::$vars['nextpost'] = $nextpost;
      $next = $theme->parse($theme->templates['content.post.prevnext.next']);
    }
    
    if (($prev == '') && ($next == '')) return '';
    $result = strtr(    $theme->parse($theme->templates['content.post.prevnext']), array(
    '$prev' => $prev,
    '$next' => $next
    ));
    unset(ttheme::$vars['prevpost'],ttheme::$vars['nextpost']);
    return $result;
  }
  
  public function getcommentslink() {
    if (($this->comstatus == 'closed') || !litepublisher::$options->commentspull) {
      if (($this->commentscount == 0) && (($this->comstatus == 'closed'))) return '';
      return sprintf('<a href="%s%s#comments">%s</a>', litepublisher::$site->url, $this->getlastcommenturl(), $this->getcmtcount());
    } else {
      //inject php code
      $l = tlocal::i()->ini['comment'];
      $result =sprintf('<?php
      echo \'<a href="%s%s#comments">\';
      $count =  tcommentspull::i()->get(%d);
      ',litepublisher::$site->url, $this->getlastcommenturl(), $this->id);
      
      $result .= 'if ($count == 0) {
        echo \'' . $l[0] . '\';
      } elseif ($count == 1) {
        echo \'' . $l[1] . '\';
      } else {
        echo sprintf(\'' . $l[2] . '\', $count);
      }
      
      echo \'</a>\';
      ?>';
    }
    
    return $result;
  }
  
  public function getcmtcount() {
    $l = tlocal::i()->ini['comment'];
    switch($this->commentscount) {
      case 0: return $l[0];
      case 1: return $l[1];
      default: return sprintf($l[2], $this->commentscount);
    }
  }
  
  public function  gettemplatecomments() {
    $result = '';
    $page = litepublisher::$urlmap->page;
    $countpages = $this->countpages;
    if ($countpages > 1) $result .= $this->theme->getpages($this->url, $page, $countpages);
    
    if (($this->commentscount > 0) || ($this->comstatus != 'closed') || ($this->pingbackscount > 0)) {
      if (($countpages > 1) && ($this->commentpages < $page)) {
        $result .= $this->getcommentslink();
      } else {
        $result .= $this->factory->templatecomments->getcomments($this->id);
      }
    }
    
    return $result;
  }
  
  public function gethascomm() {
    return ($this->data['comstatus'] != 'closed') && ((int) $this->data['commentscount'] > 0);
  }
  
  public function getexcerptcontent() {
    $posts = $this->factory->posts;
    if ($this->revision < $posts->revision) $this->update_revision($posts->revision);
    $result = $this->excerpt;
    $posts->beforeexcerpt($this, $result);
    $result = $this->replacemore($result, true);
    if (litepublisher::$options->parsepost) {
      $result = $this->theme->parse($result);
    }
    $posts->afterexcerpt($this, $result);
    return $result;
  }
  
  public function replacemore($content, $excerpt) {
    $more = $this->parsetml($excerpt ?
    'content.excerpts.excerpt.morelink' :
    'content.post.more');
    $tag = '<!--more-->';
    if ($i =strpos($content, $tag)) {
      return str_replace($tag, $more, $content);
    } else {
      return $excerpt ? $content  : $more . $content;
    }
  }
  
  protected function getteaser() {
    $content = $this->filtered;
    $tag = '<!--more-->';
    if ($i =strpos($content, $tag)) {
      $content = substr($content, $i + strlen($tag));
      if (!strbegin($content, '<p>')) $content = '<p>' . $content;
      return $content;
    }
    return '';
  }
  
  protected function getcontentpage($page) {
    $result = '';
    if ($page == 1) {
      $result .= $this->filtered;
      $result = $this->replacemore($result, false);
    } elseif ($s = $this->getpage($page - 2)) {
      $result .= $s;
    } elseif ($page <= $this->commentpages) {
    } else {
      $result .= tlocal::i()->notfound;
    }
    
    return $result;
  }
  
  public function getcontent() {
    $result = '';
    $posts = $this->factory->posts;
    $posts->beforecontent($this, $result);
    if ($this->revision < $posts->revision) $this->update_revision($posts->revision);
    $result .= $this->getcontentpage(litepublisher::$urlmap->page);
    if (litepublisher::$options->parsepost) {
      $result = $this->theme->parse($result);
    }
    $posts->aftercontent($this, $result);
    return $result;
  }
  
  public function setcontent($s) {
    if (!is_string($s)) $this->error('Error! Post content must be string');
    $this->rawcontent = $s;
    tcontentfilter::i()->filterpost($this,$s);
  }
  
  public function update_revision($value) {
    if ($value != $this->revision) {
      $this->updatefiltered();
      $posts = $this->factory->posts;
      $this->revision = (int) $posts->revision;
      if ($this->id > 0) $this->save();
    }
  }
  
  public function updatefiltered() {
    tcontentfilter::i()->filterpost($this,$this->rawcontent);
  }
  
  public function getrawcontent() {
    if (($this->id > 0) && ($this->data['rawcontent'] === false)) {
      $this->data['rawcontent'] = $this->rawdb->getvalue($this->id, 'rawcontent');
    }
    return $this->data['rawcontent'];
  }
  
  protected function getrawdb() {
    return $this->getdb('rawposts');
  }
  
  public function getpage($i) {
    if ( isset($this->data['pages'][$i]))   return $this->data['pages'][$i];
    if ($this->id > 0) {
      if ($r = $this->getdb('pages')->getassoc("(id = $this->id) and (page = $i) limit 1")) {
        $s = $r['content'];
      } else {
        $s = false;
      }
      $this->data['pages'][$i] = $s;
      return $s;
    }
    return false;
  }
  
  public function addpage($s) {
    $this->data['pages'][] = $s;
    $this->data['pagescount'] = count($this->data['pages']);
    if ($this->id > 0) {
      $this->getdb('pages')->insert(array(
      'id' => $this->id,
      'page' => $this->data['pagescount'] -1,
      'content' => $s
      ));
    }
  }
  
  public function deletepages() {
    $this->data['pages'] = array();
    $this->data['pagescount'] = 0;
    if ($this->id > 0) $this->getdb('pages')->iddelete($this->id);
  }
  
  public function gethaspages() {
    return ($this->pagescount > 1) || ($this->commentpages > 1);
  }
  
  public function getpagescount() {
    return $this->data['pagescount'] + 1;
  }
  
  public function getcountpages() {
    return max($this->pagescount, $this->commentpages);
  }
  
  public function getcommentpages() {
    if (!litepublisher::$options->commentpages || ($this->commentscount <= litepublisher::$options->commentsperpage)) return 1;
    return ceil($this->commentscount / litepublisher::$options->commentsperpage);
  }
  
  public function getlastcommenturl() {
    $c = $this->commentpages;
    $url = $this->url;
    if (($c > 1) && !litepublisher::$options->comments_invert_order) $url = rtrim($url, '/') . "/page/$c/";
    return $url;
  }
  
  public function clearcache() {
    litepublisher::$urlmap->setexpired($this->idurl);
  }
  
  public function getschemalink() {
    return 'post';
  }
  
  //author
  protected function getauthorname() {
    return $this->getusername($this->author, false);
  }
  
  protected function getauthorlink() {
    return $this->getusername($this->author, true);
  }
  
  protected function getusername($id, $link) {
    if ($id <= 1) {
      if ($link) {
        return sprintf('<a href="%s/" rel="author" title="%2$s">%2$s</a>', litepublisher::$site->url, litepublisher::$site->author);
      } else {
        return litepublisher::$site->author;
      }
    } else {
      $users = tusers::i();
      if (!$users->itemexists($id)) return '';
      $item = $users->getitem($id);
      if (!$link || ($item['website'] == '')) return $item['name'];
      return sprintf('<a href="%s/users.htm%sid=%s">%s</a>',litepublisher::$site->url, litepublisher::$site->q, $id, $item['name']);
    }
  }
  
  public function getauthorpage() {
    $id = $this->author;
    if ($id <= 1) {
      return sprintf('<a href="%s/" rel="author" title="%2$s">%2$s</a>', litepublisher::$site->url, litepublisher::$site->author);
    } else {
      $pages = tuserpages::i();
      if (!$pages->itemexists($id)) return '';
      $pages->id = $id;
      if ($pages->url == '') return '';
      return sprintf('<a href="%s%s" title="%3$s" rel="author"><%3$s</a>', litepublisher::$site->url, $pages->url, $pages->name);
    }
  }
  
}//class

class tpostfactory extends tdata {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getposts() {
    return tposts::i();
  }
  
  public function getfiles() {
    return tfiles::i();
  }
  
  public function gettags() {
    return ttags::i();
  }
  
  public function getcats () {
    return tcategories::i();
  }
  
  public function getcategories() {
    return tcategories::i();
  }
  
  public function gettemplatecomments() {
    return ttemplatecomments::i();
  }
  
  public function getcomments($id) {
    return tcomments::i($id);
  }
  
  public function getpingbacks($id) {
    return tpingbacks::i($id);
  }
  
  public function getmeta($id) {
    return tmetapost::i($id);
  }
  
  public function gettransform(tpost $post) {
    return tposttransform::i($post);
  }
  
  public function add(tpost $post) {
    return tposttransform ::add($post);
  }
  
}//class

//posts.class.php
class tposts extends titems {
  public $itemcoclasses;
  public $archives;
  public $rawtable;
  public $childtable;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public static function unsub($obj) {
    self::i()->unbind($obj);
  }
  
  protected function create() {
    $this->dbversion = true;
    parent::create();
    $this->table = 'posts';
    $this->childtable = '';
    $this->rawtable = 'rawposts';
    $this->basename = 'posts/index';
    $this->addevents('edited', 'changed', 'singlecron', 'beforecontent', 'aftercontent', 'beforeexcerpt', 'afterexcerpt',
    'onselect', 'onhead', 'onanhead', 'ontags');
    $this->data['archivescount'] = 0;
    $this->data['revision'] = 0;
    $this->data['syncmeta'] = false;
    $this->addmap('itemcoclasses', array());
  }
  
  public function getitem($id) {
    if ($result = tpost::i($id)) return $result;
    $this->error("Item $id not found in class ". get_class($this));
  }
  
  public function finditems($where, $limit) {
    if (isset(titem::$instances['post']) && (count(titem::$instances['post']) > 0)) {
      $result = $this->db->idselect($where . ' '. $limit);
      $this->loaditems($result);
      return $result;
    } else {
      return $this->select($where, $limit);
    }
  }
  
  public function loaditems(array $items) {
    //exclude already loaded items
    if (!isset(titem::$instances['post'])) titem::$instances['post'] = array();
    $loaded = array_keys(titem::$instances['post']);
    $newitems = array_diff($items, $loaded);
    if (!count($newitems)) return $items;
    $newitems = $this->select(sprintf('%s.id in (%s)', $this->thistable, implode(',', $newitems)), '');
    return array_merge($newitems, array_intersect($loaded, $items));
  }
  
  public function setassoc(array $items) {
    if (count($items) == 0) return array();
    $result = array();
    $t = new tposttransform();
    $fileitems = array();
    foreach ($items as $a) {
      $t->post = tpost::newpost($a['class']);
      $t->setassoc($a);
      $result[] = $t->post->id;
      $f = $t->post->files;
      if (count($f)) $fileitems = array_merge($fileitems, array_diff($f, $fileitems));
    }
    
    unset($t);
    if ($this->syncmeta)  tmetapost::loaditems($result);
    if (count($fileitems)) tfiles::i()->preload($fileitems);
    $this->onselect($result);
    return $result;
  }
  
  public function select($where, $limit) {
    $db = litepublisher::$db;
    if ($this->childtable) {
      $childtable = $db->prefix . $this->childtable;
      return $this->setassoc($db->res2items($db->query("select $db->posts.*, $db->urlmap.url as url, $childtable.*
      from $db->posts, $db->urlmap, $childtable
      where $where and  $db->posts.id = $childtable.id and $db->urlmap.id  = $db->posts.idurl $limit")));
    }
    
    $items = $db->res2items($db->query("select $db->posts.*, $db->urlmap.url as url  from $db->posts, $db->urlmap
    where $where and  $db->urlmap.id  = $db->posts.idurl $limit"));
    
    /*
    $items = $db->res2items($db->query(
    "select $db->posts.*, $db->urlmap.url as url  from $db->posts
    left join  $db->urlmap on $db->urlmap.id  = $db->posts.idurl
    where $where $limit"));
    */
    
    if (count($items) == 0) return array();
    $subclasses = array();
    foreach ($items as &$item) {
      if (empty($item['class'])) $item['class'] = 'tpost';
      if ($item['class'] != 'tpost') {
        $subclasses[$item['class']][] = $item['id'];
      }
    }
    unset($item);
    
    foreach ($subclasses as $class => $list) {
      /*
      $childtable =  $db->prefix .
      call_user_func_array(array($class, 'getchildtable'), array());
      $list = implode(',', $list);
      $subitems = $db->res2items($db->query("select $childtable.*
      from $childtable where id in ($list)"));
      */
      
      $subitems = call_user_func_array(array($class, 'selectitems'), array($list));
      foreach ($subitems as $id => $subitem) {
        $items[$id] = array_merge($items[$id], $subitem);
      }
    }
    
    return $this->setassoc($items);
  }
  
  public function getcount() {
    return $this->db->getcount("status<> 'deleted'");
  }
  
  public function getchildscount($where) {
    if ($this->childtable == '') return 0;
    $db = litepublisher::$db;
    $childtable = $db->prefix . $this->childtable;
    if ($res = $db->query("SELECT COUNT($db->posts.id) as count FROM $db->posts, $childtable
    where $db->posts.status <> 'deleted' and $childtable.id = $db->posts.id $where")) {
      if ($r = $db->fetchassoc($res)) return $r['count'];
    }
    return 0;
  }
  
  public function getlinks($where, $tml) {
    $db = $this->db;
    $t = $this->thistable;
    $items = $db->res2assoc($db->query(
    "select $t.id, $t.title, $db->urlmap.url as url  from $t, $db->urlmap
    where $t.status = 'published' and $where and $db->urlmap.id  = $t.idurl"));
    
    if (count($items) == 0) return '';
    
    $result = '';
    $args = new targs();
    $theme = ttheme::i();
    foreach ($items as $item) {
      $args->add($item);
      $result .=$theme->parsearg($tml, $args);
    }
    return $result;
  }
  
  private function beforechange($post) {
    $post->title = trim($post->title);
    $post->modified = time();
    $post->revision = $this->revision;
    $post->class = get_class($post);
    if (($post->status == 'published') && ($post->posted > time())) {
      $post->status = 'future';
    } elseif (($post->status == 'future') && ($post->posted <= time())) {
      $post->status = 'published';
    }
  }
  
  public function add(tpost $post) {    if ($post->posted == 0) $post->posted = time();
    $this->beforechange($post);
    if ($post->posted == 0) $post->posted = time();
    if ($post->posted <= time()) {
      if ($post->status == 'future') $post->status = 'published';
    } else {
      if ($post->status =='published') $post->status = 'future';
    }
    
    if (($post->icon == 0) && !litepublisher::$options->icondisabled) {
      $icons = ticons::i();
      $post->icon = $icons->getid('post');
    }
    
    if ($post->idview == 1) {
      $views = tviews::i();
      if (isset($views->defaults['post'])) $post->id_view = $views->defaults['post'];
    }
    
    $post->url = tlinkgenerator::i()->addurl($post, $post->schemalink);
    $id = $post->create_id();
    
    $this->updated($post);
    $this->cointerface('add', $post);
    $this->added($post->id);
    $this->changed();
    litepublisher::$urlmap->clearcache();
    return $post->id;
  }
  
  public function edit(tpost $post) {
    $this->beforechange($post);
    $linkgen = tlinkgenerator::i();
    $linkgen->editurl($post, $post->schemalink);
    if ($post->posted <= time()) {
      if ($post->status == 'future') $post->status = 'published';
    } else {
      if ($post->status =='published') $post->status = 'future';
    }
    $this->lock();
    $post->save();
    $this->updated($post);
    $this->cointerface('edit', $post);
    $this->unlock();
    $this->edited($post->id);
    $this->changed();
    
    litepublisher::$urlmap->clearcache();
  }
  
  public function delete($id) {
    if (!$this->itemexists($id)) return false;
    $urlmap = turlmap::i();
    $idurl = $this->db->getvalue($id, 'idurl');
    $this->db->setvalue($id, 'status', 'deleted');
    if ($this->childtable) {
      $db = $this->getdb($this->childtable);
      $db->delete("id = $id");
    }
    
    $this->lock();
    $this->PublishFuture();
    $this->UpdateArchives();
    $this->cointerface('delete', $id);
    $this->unlock();
    $this->deleted($id);
    $this->changed();
    $urlmap->clearcache();
    return true;
  }
  
  
  public function updated(tpost $post) {
    $this->PublishFuture();
    $this->UpdateArchives();
    tcron::i()->add('single', get_class($this), 'dosinglecron', $post->id);
  }
  
  public function UpdateArchives() {
    $this->archivescount = $this->db->getcount("status = 'published' and posted <= '" . sqldate() . "'");
  }
  
  public function dosinglecron($id) {
    $this->PublishFuture();
    ttheme::$vars['post'] = tpost::i($id);
    $this->singlecron($id);
    unset(ttheme::$vars['post']);
  }
  
  public function hourcron() {
    $this->PublishFuture();
  }
  
  private function publish($id) {
    $post = tpost::i($id);
    $post->status = 'published';
    $this->edit($post);
  }
  
  public function PublishFuture() {
    if ($list = $this->db->idselect(sprintf('status = \'future\' and posted <= \'%s\' order by posted asc', sqldate()))) {
      foreach( $list as $id) $this->publish($id);
    }
  }
  
  public function getrecent($author, $count) {
    $author = (int) $author;
    $where = "status != 'deleted'";
    if ($author > 1) $where .= " and author = $author";
    return $this->finditems($where, ' order by posted desc limit ' . (int) $count);
  }
  
  public function getpage($author, $page, $perpage, $invertorder) {
    $author = (int) $author;
    $from = ($page - 1) * $perpage;
    $t = $this->thistable;
    $where = "$t.status = 'published'";
    if ($author > 1) $where .= " and $t.author = $author";
    $order = $invertorder ? 'asc' : 'desc';
    return $this->finditems($where,  " order by $t.posted $order limit $from, $perpage");
  }
  
  public function stripdrafts(array $items) {
    if (count($items) == 0) return array();
    $list = implode(', ', $items);
    $t = $this->thistable;
    return $this->db->idselect("$t.status = 'published' and $t.id in ($list)");
  }
  
  //coclasses
  private function cointerface($method, $arg) {
    foreach ($this->coinstances as $coinstance) {
      if ($coinstance instanceof  ipost) $coinstance->$method($arg);
    }
  }
  
  public function addrevision() {
    $this->data['revision']++;
    $this->save();
    litepublisher::$urlmap->clearcache();
  }
  
  public function getanhead(array $items) {
    if (count($items) == 0) return '';
    $this->loaditems($items);
    
    $result = '';
    foreach($items as $id) {
      $result .= tpost::i($id)->anhead;
    }
    return $result;
  }
  
  //fix call reference
  public function beforecontent($post, &$result) {
    $this->callevent('beforecontent', array($post, &$result));
  }
  
  public function aftercontent($post, &$result) {
    $this->callevent('aftercontent', array($post, &$result));
  }
  
  public function beforeexcerpt($post, &$result) {
    $this->callevent('beforeexcerpt', array($post, &$result));
  }
  
  public function afterexcerpt($post, &$result) {
    $this->callevent('afterexcerpt', array($post, &$result));
  }
  
  public function getsitemap($from, $count) {
    return $this->externalfunc(__class__, 'Getsitemap', array($from, $count));
  }
  
}//class


class tpostswidget extends twidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.posts';
    $this->template = 'posts';
    $this->adminclass = 'tadminmaxcount';
    $this->data['maxcount'] = 10;
  }
  
  public function getdeftitle() {
    return tlocal::get('default', 'recentposts');
  }
  
  public function getcontent($id, $sidebar) {
    $posts = tposts::i();
    $list = $posts->getpage(0, 1, $this->maxcount, false);
    $theme = ttheme::i();
    return $theme->getpostswidgetcontent($list, $sidebar, '');
  }
  
}//class

//post.transform.class.php
class tposttransform  {
  public $post;
  public static $arrayprops= array('categories', 'tags', 'files');
  public static $intprops= array('id', 'idurl', 'parent', 'author', 'revision', 'icon', 'commentscount', 'pingbackscount', 'pagescount', 'idview', 'idperm');
  public static $boolprops= array('pingenabled');
  public static $props = array('id', 'idurl', 'parent', 'author', 'revision', 'class',
  //'created', 'modified',
  'posted',
  'title', 'title2', 'filtered', 'excerpt', 'rss', 'keywords', 'description', 'rawhead', 'moretitle',
  'categories', 'tags', 'files',
  'password', 'idview', 'idperm', 'icon',
  'status', 'comstatus', 'pingenabled',
  'commentscount', 'pingbackscount', 'pagescount',
  );
  
  public static function i(tpost $post) {
    $self = getinstance(__class__);
    $self->post = $post;
    return $self;
  }
  
  public static function add(tpost $post) {
    $self = self::i($post);
    $values = array();
    foreach (self::$props as $name) {
      $values[$name] = $self->__get($name);
    }
    $db = $post->db;
    $id = $db->add($values);
    $post->rawdb->insert(array(
    'id' => $id,
    'created' => sqldate(),
    'modified' => sqldate(),
    'rawcontent' => $post->data['rawcontent']
    ));
    
    $db->table = 'pages';
    foreach ($post->data['pages'] as $i => $content) {
      $db->insert(array('id' => $id, 'page' => $i,         'content' => $content));
    }
    
    return $id;
  }
  
  public function save() {
    $post = $this->post;
    $db = $post->db;
    $list = array();
    foreach (self::$props  As $name) {
      if ($name == 'id') continue;
      $list[] = "$name = " . $db->quote($this->__get($name));
    }
    
    $db->idupdate($post->id, implode(', ', $list));
    
    $raw = array(
    'id' => $post->id,
    'modified' => sqldate()
    );
    if (false !== $post->data['rawcontent']) $raw['rawcontent'] = $post->data['rawcontent'];
    $post->rawdb->updateassoc($raw);
    /*
    $db->table = 'pages';
    $db->iddelete($post->id);
    foreach ($post->data['pages'] as $i => $content) {
      $db->insert(array('id' => $post->id, 'page' => $i, 'content' => $content));
    }
    */
  }
  
  public function setassoc(array $a) {
    foreach ($a as $k => $v) $this->__set($k, $v);
  }
  
  public function __get($name) {
    if ('pagescount' == $name) return $this->post->data[$name];
    if (method_exists($this, $get = "get$name")) return $this->$get();
    if (in_array($name, self::$arrayprops))  return implode(',', $this->post->$name);
    if (in_array($name, self::$boolprops))  return $this->post->$name ? 1 : 0;
    return $this->post->$name;
  }
  
  public function __set($name, $value) {
    if (method_exists($this, $set = "set$name")) return $this->$set($value);
    if (in_array($name, self::$arrayprops)) {
      $this->post->data[$name] = tdatabase::str2array($value);
    } elseif (in_array($name, self::$intprops)) {
      $this->post->$name = (int) $value;
    } elseif (in_array($name, self::$boolprops)) {
      $this->post->data[$name] = $value == '1';
    } else {
      $this->post->$name = $value;
    }
  }
  
  private function getposted() {
    return sqldate($this->post->posted);
  }
  
  private function setposted($value) {
    $this->post->posted = strtotime($value);
  }
  
  private function setrevision($value) {
    $this->post->data['revision'] = $value;
  }
  
}//class

//post.meta.class.php
class tmetapost extends titem {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, (int) $id);
  }
  
  public static function getinstancename() {
    return 'postmeta';
  }
  
  protected function create() {
    $this->table = 'postsmeta';
  }
  
  public function getdbversion() {
    return true;
  }
  
  public function __set($name, $value) {
    if ($name == 'id') return $this->setid($value);
    $exists = isset($this->data[$name]);
    if ($exists && ($this->data[$name] == $value)) return true;
    $this->data[$name] = $value;
    $name = dbquote($name);
    $value = dbquote($value);
    if ($exists) {
      $this->db->update("value = $value", "id = $this->id and name = $name");
    } else {
      $this->db->insertrow("(id, name, value) values ($this->id, $name, $value)");
    }
  }
  
  public function __unset($name) {
    $this->remove($name);
  }
  
  //db
  public function load() {
    $this->LoadFromDB();
    return true;
  }
  
  protected function LoadFromDB() {
    $db = $this->db;
    $res = $db->select("id = $this->id");
    if (is_object($res)) {
      while ($r = $res->fetch_assoc()) {
        $this->data[$r['name']] = $r['value'];
      }
    }
    return true;
  }
  
  protected function SaveToDB() {
    $db = $this->db;
    $db->delete("id = $this->id");
    foreach ($this->data as $name => $value) {
      if ($name == 'id') continue;
      $name = dbquote($name);
      $value = dbquote($value);
      $this->db->insertrow("(id, name, value) values ($this->id, $name, $value)");
    }
  }
  
  public function remove($name) {
    if ($name == 'id') return;
    unset($this->data[$name]);
    $this->db->delete("id = $this->id and name = '$name'");
  }
  
  public static function loaditems(array $items) {
    if (!count($items)) return;
    //exclude already loaded items
    if (isset(self::$instances['postmeta'])) {
      $items = array_diff($items, array_keys(self::$instances['postmeta']));
      if (!count($items)) return;
    } else {
      self::$instances['postmeta'] = array();
    }
    
    $instances = &self::$instances['postmeta'];
    $db = litepublisher::$db;
    $db->table = 'postsmeta';
    $res = $db->select(sprintf('id in (%s)', implode(',', $items)));
    while ($row = $db->fetchassoc($res)) {
      $id = (int) $row['id'];
      if (!isset($instances[$id])) {
        $instances[$id] = new self();
        $instances[$id]->data['id'] = $id;
      }
      
      $instances[$id]->data[$row['name']] = $row['value'];
    }
    
    return $items;
  }
  
}//class

//tags.common.class.php
class tcommontags extends titems implements  itemplate {
  public $factory;
  public $contents;
  public $itemsposts;
  public $PermalinkIndex;
  public $postpropname;
  public $id;
  private $newtitle;
  private $all_loaded;
  private $_idposts;
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->addevents('changed', 'onbeforecontent', 'oncontent');
    $this->data['lite'] = false;
    $this->data['includechilds'] = false;
    $this->data['includeparents'] = false;
    $this->PermalinkIndex = 'category';
    $this->postpropname = 'categories';
    $this->all_loaded = false;
    $this->_idposts = array();
    $this->createfactory();
  }
  
  protected function createfactory() {
    $this->factory = litepublisher::$classes->getfactory($this);
    $this->contents = new ttagcontent($this);
    if (!$this->dbversion)  $this->data['itemsposts'] = array();
    $this->itemsposts = new titemspostsowner ($this);
  }
  
  public function loadall() {
    //prevent double request
    if ($this->all_loaded) return;
    $this->all_loaded = true;
    return parent::loadall();
  }
  
  public function select($where, $limit) {
    if ($where != '') $where .= ' and ';
    $db = litepublisher::$db;
    $t = $this->thistable;
    $u = $db->urlmap;
    $res = $db->query("select $t.*, $u.url from $t, $u
    where $where $u.id = $t.idurl $limit");
    return $this->res2items($res);
  }
  
  public function load() {
    if (parent::load() && !$this->dbversion) {
      $this->itemsposts->items = &$this->data['itemsposts'];
    }
  }
  
  public function getsortedcontent(array $tml, $parent,  $sortname, $count, $showcount) {
    $sorted = $this->getsorted($parent, $sortname, $count);
    if (count($sorted) == 0) return '';
    $result = '';
    $iconenabled = ! litepublisher::$options->icondisabled;
    $theme = ttheme::i();
    $args = new targs();
    $args->rel = $this->PermalinkIndex;
    $args->parent = $parent;
    foreach($sorted as $id) {
      $item = $this->getitem($id);
      $args->add($item);
      $args->icon = $iconenabled ? $this->geticonlink($id) : '';
      $args->subcount =$showcount ? $theme->parsearg($tml['subcount'],$args) : '';
      $args->subitems = $tml['subitems'] ? $this->getsortedcontent($tml, $id, $sortname, $count, $showcount) : '';
      $result .= $theme->parsearg($tml['item'],$args);
    }
    if ($parent == 0) return $result;
    $args->parent = $parent;
    $args->item = $result;
    return $theme->parsearg($tml['subitems'], $args);
  }
  
  public function geticonlink($id) {
    $item = $this->getitem($id);
    if ($item['icon'] == 0)  return '';
    $files = tfiles::i();
    if ($files->itemexists($item['icon'])) return $files->geticon($item['icon'], $item['title']);
    $this->setvalue($id, 'icon', 0);
    if (!$this->dbversion) $this->save();
    return '';
  }
  
  public function geticon() {
    $item = $this->getitem($this->id);
    return $item['icon'];
  }
  
  public function geturl($id) {
    $item = $this->getitem($id);
    return $item['url'];
  }
  
  public function postedited($idpost) {
    $post = $this->factory->getpost((int) $idpost);
  $items = $post->{$this->postpropname};
    array_clean($items);
    if (count($items)) $items = $this->db->idselect(sprintf('id in (%s)', implode(',', $items)));
    $changed = $this->itemsposts->setitems($idpost, $items);
    $this->updatecount($changed);
  }
  
  public function postdeleted($idpost) {
    $changed = $this->itemsposts->deletepost($idpost);
    $this->updatecount($changed);
  }
  
  protected function updatecount(array $items) {
    if (count($items) == 0) return;
    $db = litepublisher::$db;
    //next queries update values
    $items = implode(',', $items);
    $thistable = $this->thistable;
    $itemstable = $this->itemsposts->thistable;
    $itemprop = $this->itemsposts->itemprop;
    $postprop = $this->itemsposts->postprop;
    $poststable = $db->posts;
    $list = $db->res2assoc($db->query("select $itemstable.$itemprop as id, count($itemstable.$itemprop)as itemscount from $itemstable, $poststable
    where $itemstable.$itemprop in ($items)  and $itemstable.$postprop = $poststable.id and $poststable.status = 'published'
    group by $itemstable.$itemprop"));
    
    $db->table = $this->table;
    foreach ($list as $item) {
      $db->setvalue($item['id'], 'itemscount', $item['itemscount']);
    }
  }
  
  public function geturltype() {
    return 'normal';
  }
  
  public function add($parent, $title) {
    $title = trim($title);
    if (empty($title)) return false;
    if ($id  = $this->indexof('title', $title)) return $id;
    $parent = (int) $parent;
    if (($parent != 0) && !$this->itemexists($parent)) $parent = 0;
    
    $url = tlinkgenerator::i()->createurl($title, $this->PermalinkIndex, true);
    $views = tviews::i();
    $idview = isset($views->defaults[$this->PermalinkIndex]) ? $views->defaults[$this->PermalinkIndex] : 1;
    
    $item = array(
    'idurl' => 0,
    'customorder' => 0,
    'parent' => $parent,
    'title' => $title,
    'idview' => $idview,
    'idperm' => 0,
    'icon' => 0,
    'itemscount' => 0,
    'includechilds' => $this->includechilds,
    'includeparents' => $this->includeparents,
    'invertorder' => false,
    'lite' => $this->lite,
    'liteperpage' => 1000
    );
    
    $id = $this->db->add($item);
    $this->items[$id] = $item;
    $idurl =         litepublisher::$urlmap->add($url, get_class($this),  $id, $this->urltype);
    $this->setvalue($id, 'idurl', $idurl);
    $this->items[$id]['url'] = $url;
    $this->added($id);
    $this->changed();
    litepublisher::$urlmap->clearcache();
    return $id;
  }
  
  public function edit($id, $title, $url) {
    $item = $this->getitem($id);
    if (($item['title'] == $title) && ($item['url'] == $url)) return;
    $item['title'] = $title;
    if ($this->dbversion) {
      $this->db->updateassoc(array(
      'id' => $id,
      'title' => $title
      ));
    }
    
    $linkgen = tlinkgenerator::i();
    $url = trim($url);
    // try rebuild url
    if ($url == '') {
      $url = $linkgen->createurl($title, $this->PermalinkIndex, false);
    }
    
    if ($item['url'] != $url) {
      if (($urlitem = litepublisher::$urlmap->finditem($url)) && ($urlitem['id'] != $item['idurl'])) {
        $url = $linkgen->MakeUnique($url);
      }
      litepublisher::$urlmap->setidurl($item['idurl'], $url);
      litepublisher::$urlmap->addredir($item['url'], $url);
      $item['url'] = $url;
    }
    
    $this->items[$id] = $item;
    $this->save();
    $this->changed();
    litepublisher::$urlmap->clearcache();
  }
  
  public function delete($id) {
    $item = $this->getitem($id);
    litepublisher::$urlmap->deleteitem($item['idurl']);
    $this->contents->delete($id);
    $list = $this->itemsposts->getposts($id);
    $this->itemsposts->deleteitem($id);
    parent::delete($id);
    if ($this->postpropname) $this->itemsposts->updateposts($list, $this->postpropname);
    $this->changed();
    litepublisher::$urlmap->clearcache();
  }
  
  public function createnames($list) {
    if (is_string($list)) $list = explode(',', trim($list));
    $result = array();
    $this->lock();
    foreach ($list as $title) {
      $title = tcontentfilter::escape($title);
      if ($title == '') continue;
      $result[] = $this->add(0, $title);
    }
    $this->unlock();
    return $result;
  }
  
  public function getnames(array $list) {
    $this->loaditems($list);
    $result =array();
    foreach ($list as $id) {
      if (!isset($this->items[$id])) continue;
      $result[] = $this->items[$id]['title'];
    }
    return $result;
  }
  
  public function getlinks(array $list) {
    if (count($list) == 0) return array();
    $this->loaditems($list);
    $result =array();
    foreach ($list as $id) {
      if (!isset($this->items[$id])) continue;
      $item = $this->items[$id];
      $result[] = sprintf('<a href="%1$s" title="%2$s">%2$s</a>', litepublisher::$site->url . $item['url'], $item['title']);
    }
    return $result;
  }
  
  public function getsorted($parent, $sortname, $count) {
    $count = (int) $count;
    if ($sortname == 'count') $sortname = 'itemscount';
    if (!in_array($sortname, array('title', 'itemscount', 'customorder', 'id'))) $sortname = 'title';
    
    if ($this->dbversion) {
      $limit  = $sortname == 'itemscount' ?
      "order by $this->thistable.$sortname desc" :
      "order by $this->thistable.$sortname asc";
      if ($count > 0) $limit .= " limit $count";
      return $this->select($parent == -1 ? '' : "$this->thistable.parent = $parent", $limit);
    }
    
    $list = array();
    foreach($this->items as $id => $item) {
      if (($parent != -1) & ($parent != $item['parent'])) continue;
      $list[$id] = $item[$sortname];
    }
    if (($sortname == 'itemscount')) {
      arsort($list);
    } else {
      asort($list);
    }
    
    if (($count > 0) && ($count < count($list))) {
      $list = array_slice($list, 0, $count, true);
    }
    
    return array_keys($list);
  }
  
  //Itemplate
  public function request($id) {
    $this->id = (int) $id;
    try {
      $item = $this->getitem((int) $id);
    } catch (Exception $e) {
      return 404;
    }
    
    $perpage = (int) $item['lite'] ? (int) $item['liteperpage'] : litepublisher::$options->perpage;
    $list = $this->getidposts($id);
    $pages = (int) ceil(count ($list) / $perpage);
    if (($pages  > 1) && (litepublisher::$urlmap->page > $pages)) {
      return sprintf('<?php litepublisher::$urlmap->redir(\'%s\'); ?>',$item['url']);
    }
    
  }
  
  public function getname($id) {
    $item = $this->getitem($id);
    return $item['title'];
  }
  
  public function gettitle() {
    $item = $this->getitem($this->id);
    return $item['title'];
  }
  
  public function gethead() {
    $result = $this->contents->getvalue($this->id, 'head');
    $result .= tview::getview($this)->theme->templates['head.tags'];
    $list = $this->getidposts($this->id);
    $result .=     $this->factory->posts->getanhead($list);
    return ttheme::i()->parse($result);
  }
  
  public function getkeywords() {
    $result = $this->contents->getvalue($this->id, 'keywords');
    if ($result == '') $result = $this->title;
    return $result;
  }
  
  public function getdescription() {
    $result = $this->contents->getvalue($this->id, 'description');
    if ($result == '') $result = $this->title;
    return $result;
  }
  
  public function getidview() {
    $item = $this->getitem($this->id);
    return $item['idview'];
  }
  
  public function setidview($id) {
    if ($id != $this->idview) {
      $this->setvalue($this->id, 'idview', $id);
    }
  }
  
  public function getidperm() {
    $item = $this->getitem($this->id);
    return isset($item['idperm']) ? (int) $item['idperm'] : 0;
  }
  
  public function getindex_tml() {
    $theme = ttheme::i();
    if (!empty($theme->templates['index.tag'])) return $theme->templates['index.tag'];
    return false;
  }
  
  public function getcontent() {
    if ($s = $this->contents->getcontent($this->id)) {
      $pages = explode('<!--nextpage-->', $s);
      $page = litepublisher::$urlmap->page - 1;
      if (isset($pages[$page])) return $pages[$page];
    }
    
    return '';
  }
  
  public function getcont() {
    $result = '';
    $this->callevent('onbeforecontent', array(&$result));
    $theme = ttheme::i();
    if ($this->id == 0) {
      $items = $this->getsortedcontent(array(
      'item' =>'<li><a href="$link" title="$title">$icon$title</a>$subcount</li>',
      'subcount' => '<strong>($itemscount)</strong>',
      'subitems' =>       '<ul>$item</ul>'
      ),
      0, 'count', 0, 0, false);
      $result .= sprintf('<ul>%s</ul>', $items);
      $this->callevent('oncontent', array(&$result));
      return $result;
    }
    
    if ($this->getcontent()) {
      ttheme::$vars['menu'] = $this;
      $result .= $theme->parse($theme->templates['content.menu']);
    }
    
    $list = $this->getidposts($this->id);
    $item = $this->getitem($this->id);
    $result .= $theme->getpostsnavi($list, (int) $item['lite'], $item['url'], $item['itemscount'], $item['liteperpage']);
    $this->callevent('oncontent', array(&$result));
    return $result;
  }
  
  public function get_sorted_posts($id, $count, $invert) {
    $ti = $this->itemsposts->thistable;
    $posts = $this->factory->posts;
    $p = $posts->thistable;
    $order = $invert ? 'asc' : 'desc';
    $result = $this->db->res2id($this->db->query("select $p.id as id, $ti.post as post from $p, $ti
    where    $ti.item = $id and $p.id = $ti.post and $p.status = 'published'
    order by $p.posted $order limit 0, $count"));
    
    $posts->loaditems($result);
    return $result;
  }
  
  public function getidposts($id) {
    if (isset($this->_idposts[$id])) return $this->_idposts[$id];
    $item = $this->getitem($id);
    
    $includeparents = (int) $item['includeparents'];
    $includechilds = (int) $item['includechilds'];
    $perpage = (int) $item['lite'] ? $item['liteperpage'] : litepublisher::$options->perpage;
    $posts = $this->factory->posts;
    $p = $posts->thistable;
    $t = $this->thistable;
    $ti = $this->itemsposts->thistable;
    $postprop = $this->itemsposts->postprop;
    $itemprop = $this->itemsposts->itemprop;
    
    if ($includeparents || $includechilds) {
      $this->loadall();
      $all = array($id);
      if ($includeparents) $all = array_merge($all, $this->getparents($id));
      if ($includechilds) $all = array_merge($all, $this->getchilds($id));
      $tags = sprintf('in (%s)', implode(',', $all));
    } else {
      $tags = " = $id";
    }
    
    $from = (litepublisher::$urlmap->page - 1) * $perpage;
    $order = (int) $item['invertorder'] ? 'asc' : 'desc';
    /*
    $this->_idposts[$id] = $posts->select("$p.status = 'published' and $p.id in
    (select DISTINCT post from $ti where $ti.item $tags)",
    "order by $p.posted $order limit $from, $perpage");
    */
    
    $result = $this->db->res2id($this->db->query("select $ti.$postprop as $postprop, $p.id as id from $ti, $p
    where    $ti.$itemprop $tags and $p.id = $ti.$postprop and $p.status = 'published'
    order by $p.posted $order limit $from, $perpage"));
    
    $result = array_unique($result);
    $posts->loaditems($result);
    $this->_idposts[$id] = $result;
    return $result;
  }
  
  public function getparents($id) {
    $result = array();
    while ($id = (int) $this->items[$id]['parent']) $result[] = $id;
    return $result;
  }
  
  public function getchilds($parent) {
    $result = array();
    foreach ($this->items as $id => $item) {
      if ($parent == $item['parent']) {
        $result[] =$id;
        $result = array_merge($result, $this->getchilds($id));
      }
    }
    return $result;
  }
  
  public function getsitemap($from, $count) {
    return $this->externalfunc(__class__, 'Getsitemap', array($from, $count));
  }
  
}//class

class ttagcontent extends tdata {
  private $owner;
  private $items;
  
  public function __construct(TCommonTags $owner) {
    parent::__construct();
    $this->owner = $owner;
    $this->items = array();
  }
  
  private function getfilename($id) {
    return litepublisher::$paths->data . $this->owner->basename . DIRECTORY_SEPARATOR . $id;
  }
  
  public function getitem($id) {
    if (isset($this->items[$id]))  return $this->items[$id];
    $item = array(
    'description' => '',
    'keywords' => '',
    'head' => '',
    'content' => '',
    'rawcontent' => ''
    );
    
    if ($this->owner->dbversion) {
      if ($r = $this->db->getitem($id)) $item = $r;
    } else {
      tfilestorage::loadvar($this->getfilename($id), $item);
    }
    $this->items[$id] = $item;
    return $item;
  }
  
  public function setitem($id, $item) {
    if (isset($this->items[$id]) && ($this->items[$id] == $item)) return;
    $this->items[$id] = $item;
    if ($this->owner->dbversion) {
      $item['id'] = $id;
      $this->db->addupdate($item);
    } else {
      tfilestorage::savevar($this->getfilename($id), $item);
    }
  }
  
  public function edit($id, $content, $description, $keywords, $head) {
    $item = $this->getitem($id);
    $filter = tcontentfilter::i();
    $item =array(
    'content' => $filter->filter($content),
    'rawcontent' => $content,
    'description' => $description,
    'keywords' => $keywords,
    'head' => $head
    );
    $this->setitem($id, $item);
  }
  
  public function delete($id) {
    $this->db->iddelete($id);
  }
  
  public function getvalue($id, $name) {
    $item = $this->getitem($id);
    return $item[$name];
  }
  
  public function setvalue($id, $name, $value) {
    $item = $this->getitem($id);
    $item[$name] = $value;
    $this->setitem($id, $item);
  }
  
  public function getcontent($id) {
    return $this->getvalue($id, 'content');
  }
  
  public function setcontent($id, $content) {
    $item = $this->getitem($id);
    $filter = tcontentfilter::i();
    $item['rawcontent'] = $content;
    $item['content'] = $filter->filterpages($content);
    $item['description'] = tcontentfilter::getexcerpt($content, 80);
    $this->setitem($id, $item);
  }
  
  public function getdescription($id) {
    return $this->getvalue($id, 'description');
  }
  
  public function getkeywords($id) {
    return $this->getvalue($id, 'keywords');
  }
  
  public function gethead($id) {
    return $this->getvalue($id, 'head');
  }
  
}//class

class tcommontagswidget extends twidget {
  
  protected function create() {
    parent::create();
    $this->adminclass = 'tadmintagswidget';
    $this->data['sortname'] = 'count';
    $this->data['showcount'] = true;
    $this->data['showsubitems'] = true;
    $this->data['maxcount'] =0;
  }
  
  public function getowner() {
    return false;
  }
  
  public function getcontent($id, $sidebar) {
    $theme = ttheme::i();
    $items = $this->owner->getsortedcontent(array(
    'item' => $theme->getwidgetitem($this->template, $sidebar),
    'subcount' =>$theme->getwidgettml($sidebar, $this->template, 'subcount'),
    'subitems' => $this->showsubitems ? $theme->getwidgettml($sidebar, $this->template, 'subitems') : ''
    ),
    0, $this->sortname, $this->maxcount, $this->showcount);
    return str_replace('$parent', 0,
    $theme->getwidgetcontent($items, $this->template, $sidebar));
  }
  
}//class

class tcategories extends tcommontags {
  //public  $defaultid;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->table = 'categories';
    $this->contents->table = 'catscontent';
    $this->itemsposts->table = $this->table . 'items';
    $this->basename = 'categories' ;
    $this->data['defaultid'] = 0;
  }
  
  public function setdefaultid($id) {
    if (($id != $this->defaultid) && $this->itemexists($id)) {
      $this->data['defaultid'] = $id;
      $this->save();
    }
  }
  
  public function save() {
    parent::save();
    if (!$this->locked)  {
      tcategorieswidget::i()->expire();
    }
  }
  
}//class

class tcategorieswidget extends tcommontagswidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.categories';
    $this->template = 'categories';
  }
  
  public function getdeftitle() {
    return tlocal::get('default', 'categories');
  }
  
  public function getowner() {
    return tcategories::i();
  }
  
}//class

class ttags extends tcommontags {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->table = 'tags';
    $this->basename = 'tags';
    $this->PermalinkIndex = 'tag';
    $this->postpropname = 'tags';
    $this->contents->table = 'tagscontent';
    $this->itemsposts->table = $this->table . 'items';
  }
  
  public function save() {
    parent::save();
    if (!$this->locked)  {
      ttagswidget::i()->expire();
    }
  }
  
}//class

class ttagswidget extends tcommontagswidget {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'widget.tags';
    $this->template = 'tags';
    $this->sortname = 'title';
    $this->showcount = false;
  }
  
  public function getdeftitle() {
    return tlocal::get('default', 'tags');
  }
  
  public function getowner() {
    return ttags::i();
  }
  
}//class

class ttagfactory extends tdata {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getposts() {
    return tposts::i();
  }
  
  public function getpost($id) {
    return tpost::i($id);
  }
  
}//class

//files.class.php
class tfiles extends titems {
  public $itemsposts;
  public $cachetml;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = true;
    parent::create();
    $this->basename = 'files';
    $this->table = 'files';
    $this->addevents('changed', 'edited', 'ongetfilelist', 'onlist');
    $this->itemsposts = tfileitems ::i();
    $this->data['videoplayer'] = '/js/litepublisher/icons/videoplayer.jpg';
    $this->cachetml = array();
  }
  
  public function preload(array $items) {
    $items = array_diff($items, array_keys($this->items));
    if (count($items) > 0) {
      $this->select(sprintf('(id in (%1$s)) or (parent in (%1$s))',
      implode(',', $items)), '');
    }
  }
  
  public function geturl($id) {
    $item = $this->getitem($id);
    return litepublisher::$site->files . '/files/' . $item['filename'];
  }
  
  public function getlink($id) {
    $item = $this->getitem($id);
    $icon = '';
    if (($item['icon'] != 0) && ($item['media'] != 'icon')) {
      $icon = $this->geticon($item['icon']);
    }
    return sprintf('<a href="%1$s/files/%2$s" title="%3$s">%4$s</a>', litepublisher::$site->files,
    $item['filename'], $item['title'], $icon . $item['description']);
  }
  
  public function geticon($id) {
    return sprintf('<img src="%s" alt="icon" />', $this->geturl($id));
  }
  
  public function gethash($filename) {
    return trim(base64_encode(md5_file($filename, true)), '=');
  }
  
  public function additem(array $item) {
    $realfile = litepublisher::$paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
    $item['author'] = litepublisher::$options->user;
    $item['posted'] = sqldate();
    $item['hash'] = $this->gethash($realfile);
    $item['size'] = filesize($realfile);
    
    //fix empty props
    foreach (array('mime', 'title', 'description', 'keywords') as $prop) {
      if (!isset($item[$prop])) $item[$prop] = '';
    }
    return $this->insert($item);
  }
  
  public function insert(array $item) {
    $item = $this->escape($item);
    $id = $this->db->add($item);
    $this->items[$id] = $item;
    $this->changed();
    $this->added($id);
    return $id;
  }
  
  public function escape(array $item) {
    foreach (array('title', 'description', 'keywords') as $name) {
      $item[$name] = tcontentfilter::escape(tcontentfilter::unescape($item[$name]));
    }
    return $item;
  }
  
  public function edit($id, $title, $description, $keywords) {
    $item = $this->getitem($id);
    if (($item['title'] == $title) && ($item['description'] == $description) && ($item['keywords'] == $keywords)) return false;
    
    $item['title'] = $title;
    $item['description'] = $description;
    $item['keywords'] = $keywords;
    $item = $this->escape($item);
    $this->items[$id] = $item;
    $this->db->updateassoc($item);
    $this->changed();
    $this->edited($id);
    return true;
  }
  
  public function delete($id) {
    if (!$this->itemexists($id)) return false;
    $list = $this->itemsposts->getposts($id);
    $this->itemsposts->deleteitem($id);
    $this->itemsposts->updateposts($list, 'files');
    $item = $this->getitem($id);
    if ($item['idperm'] == 0) {
      @unlink(litepublisher::$paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']));
    } else {
      @unlink(litepublisher::$paths->files . 'private' . DIRECTORY_SEPARATOR . basename($item['filename']));
      litepublisher::$urlmap->delete('/files/' . $item['filename']);
    }
    
    parent::delete($id);
    if ($item['preview'] > 0) $this->delete($item['preview']);
    
    $this->getdb('imghashes')->delete("id = $id");
    $this->changed();
    return true;
  }
  
  public function setcontent($id, $content) {
    if (!$this->itemexists($id)) return false;
    $item = $this->getitem($id);
    $realfile = litepublisher::$paths->files . str_replace('/', DIRECTORY_SEPARATOR, $item['filename']);
    if (file_put_contents($realfile, $content)) {
      $item['hash'] = $this->gethash($realfile);
      $item['size'] = filesize($realfile);
      $this->items[$id] = $item;
      if ($this->dbversion) {
        $item['id'] = $id;
        $this->db->updateassoc($item);
      } else {
        $this->save();
      }
    }
  }
  
  public function exists($filename) {
    return $this->indexof('filename', $filename);
  }
  
  public function getfilelist(array $list, $excerpt) {
    if ($result = $this->ongetfilelist($list, $excerpt)) return $result;
    if (count($list) == 0) return '';
    
    return $this->getlist($list, $excerpt ?
    $this->gettml('content.excerpts.excerpt.filelist') :
    $this->gettml('content.post.filelist'));
  }
  
  public function gettml($basekey) {
    if (isset($this->cachetml[$basekey])) return $this->cachetml[$basekey];
    $theme = ttheme::i();
    $result = array(
    'all' => $theme->templates[$basekey],
    );
    
    $key = $basekey . '.';
    foreach  ($theme->templates as $k => $v) {
      if (strbegin($k, $key)) $result[substr($k, strlen($key))] = $v;
    }
    
    $this->cachetml[$basekey] = $result;
    return $result;
  }
  
  public function getlist(array $list,  array $tml) {
    if (count($list) == 0) return '';
    $this->onlist($list);
    $result = '';
    $this->preload($list);
    //sort by media type
    $items = array();
    foreach ($list as $id) {
      if (!isset($this->items[$id])) continue;
      $item = $this->items[$id];
      $type = $item['media'];
      if (isset($tml[$type])) {
        $items[$type][] = $id;
      } else {
        $items['file'][] = $id;
      }
    }
    
    $theme = ttheme::i();
    $args = new targs();
    $args->count = count($list);
    
    $url = litepublisher::$site->files . '/files/';
    $preview = new tarray2prop();
    ttheme::$vars['preview'] = $preview;
    $index = 0;
    foreach ($items as $type => $subitems) {
      $args->subcount = count($subitems);
      $sublist = '';
      foreach ($subitems as $typeindex => $id) {
        $item = $this->items[$id];
        $args->add($item);
        $args->link = $url . $item['filename'];
        $args->id = $id;
        $args->typeindex = $typeindex;
        $args->index = $index++;
        $args->preview  = '';
        $preview->array = array();
        
        if ($item['preview'] > 0) {
          $preview->array = $this->getitem($item['preview']);
        } elseif($type == 'image') {
          $preview->array = $item;
          $preview->id = $id;
        } elseif($type == 'video') {
          $preview->link = litepublisher::$site->url . $this->videoplayer;
          $args->preview = $theme->parsearg($types['preview'], $args);
          $preview->array = array();
        }
        
        if (count($preview->array)) {
          $preview->link = $url . $preview->filename;
          $args->preview = $theme->parsearg($tml['preview'], $args);
        }
        
        unset($item['title'], $item['keywords'], $item['description']);
        $args->json = jsonattr($item);
        
        $sublist .= $theme->parsearg($tml[$type], $args);
      }
      
      $args->__set($type, $sublist);
      $result .=  $theme->parsearg($tml[$type . 's'], $args);
    }
    
    unset(ttheme::$vars['preview'], $preview);
    $args->files =  $result;
    return $theme->parsearg($tml['all'], $args);
  }
  
  public function postedited($idpost) {
    $post = tpost::i($idpost);
    $this->itemsposts->setitems($idpost, $post->files);
  }
  
}//class

class tfileitems extends titemsposts {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->basename = 'fileitems';
    $this->table = 'filesitemsposts';
  }
  
}

