<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

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