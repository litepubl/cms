<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

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
      if ((int) $item['preview']) return $files->geturl($item['preview']);
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
    $items = $this->__get($name);
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