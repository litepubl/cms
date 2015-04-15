<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

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