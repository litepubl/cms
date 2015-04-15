<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tuserpages extends titems implements itemplate {
  public static $userprops = array('email', 'name', 'website');
  public static $pageprops = array('url', 'content', 'rawcontent');
  public $id;
  protected $useritem;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = dbversion;
    parent::create();
    $this->basename = 'userpage';
    $this->table = 'userpage';
    $this->data['lite'] = false;
    $this->data['createpage'] = true;
  }
  
  public function __get($name) {
    if (in_array($name, self::$userprops)) {
      return tusers::i()->getvalue($this->id, $name);
    }
    
    if (in_array($name, self::$pageprops)) {
      return $this->getvalue($this->id, $name);
    }
    
    return parent::__get($name);
  }
  
  public function getmd5email() {
    if ($email = tusers::i()->getvalue($this->id, 'email')) {
      return md5($email);
    } else {
      return '';
    }
  }
  
  public function getgravatar() {
    if ($md5 = $this->md5email) {
      return sprintf('<img class="avatar photo" src="http://www.gravatar.com/avatar/%s?s=120&amp;r=g&amp;d=wavatar" title="%2$s" alt="%2$s"/>', $md5, $this->name);
    } else {
      return '';
    }
  }
  
  public function getwebsitelink() {
    if ($website = $this->website) {
      return sprintf('<a href="%1$s">%1$s</a>', $website);
    }
    return '';
  }
  
  public function select($where, $limit) {
    if (!$this->dbversion) $this->error('Select method must be called ffrom database version');
    if ($where) $where .= ' and ';
    $db = litepublisher::$db;
    $table = $this->thistable;
    $res = $db->query(
    "select $table.*, $db->urlmap.url as url from $table, $db->urlmap
    where $where $db->urlmap.id  = $table.idurl $limit");
    return $this->res2items($res);
  }
  
  public function getitem($id) {
    $item = parent::getitem($id);
    if (!isset($item['url'])) {
      $item['url'] = $item['idurl'] == 0 ? '' : litepublisher::$urlmap->getidurl($item['idurl']);
      $this->items[$id]['url'] = $item['url'];
    }
    return $item;
  }
  
  public function request($id) {
    if ($id == 'url') {
      $id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
      $users = tusers::i();
      if (!$users->itemexists($id)) return 404;
      $item = $users->getitem($id);
      $website = $item['website'];
      if (!strpos($website, '.')) $website = litepublisher::$site->url . litepublisher::$site->home;
      if (!strbegin($website, 'http://')) $website = 'http://' . $website;
      return "<?php litepublisher::$urlmap->redir('$website');";
    }
    
    $this->id = (int) $id;
    if (!$this->itemexists($id)) return 404;
    $item =$this->getitem($id);
    if ($this->lite && (litepublisher::$urlmap->page > 1)) {
      return sprintf("<?php litepublisher::$urlmap->redir('%s');",$item['url']);
    }
  }
  
  public function gettitle() {
    return $this->name;
  }
  
  public function getkeywords() {
    return $this->getvalue($this->id, 'keywords');
  }
  
  public function getdescription() {
    return $this->getvalue($this->id, 'description');
  }
  
  public function getidview() {
    return $this->getvalue($this->id, 'idview');
  }
  
  public function setidview($id) {
    $this->setvalue($this->id, 'idveiw');
  }
  
  public function gethead() {
    return $this->getvalue($this->id, 'head');
  }
  
  public function getcont() {
    $item = $this->getitem($this->id);
    ttheme::$vars['author'] = $this;
    $theme = tview::getview($this)->theme;
    $result = $theme->parse($theme->templates['content.author']);
    
    $perpage = $this->lite ? 1000 : litepublisher::$options->perpage;
    $posts = litepublisher::$classes->posts;
    $from = (litepublisher::$urlmap->page - 1) * $perpage;
    
    $poststable = $posts->thistable;
    $count = $posts->db->getcount("$poststable.status = 'published' and $poststable.author = $this->id");
    $items = $posts->select("$poststable.status = 'published' and $poststable.author = $this->id",
    "order by $poststable.posted desc limit $from, $perpage");
    $result .= $theme->getposts($items, $this->lite);
    $result .=$theme->getpages($item['url'], litepublisher::$urlmap->page, ceil($count / $perpage));
    return $result;
  }
  
  public function addpage($id) {
    $item = $this->getitem($id);
    if ($item['idurl'] > 0) return $item['idurl'];
    $item = $this->addurl($item);
    $this->items = $item;
    unset($item['url']);
    $item['id'] = $id;
    $this->db->updateassoc($item);
  }
  
  private function addurl(array $item) {
    if ($item['id'] == 1) return $item;
    $item['url'] = '';
    $linkitem = tusers::i()->getitem($item['id']) + $item;
    $linkgen = tlinkgenerator::i();
    $item['url'] = $linkgen->addurl(new tarray2prop ($linkitem), 'user');
    $item['idurl'] = litepublisher::$urlmap->add($item['url'], get_class($this), $item['id']);
    return $item;
  }
  
  public function add($id) {
    $item = array(
    'id' => $id,
    'idurl' => 0,
    'idview' => 1,
    'registered' => sqldate(),
    'ip' => '',
    'avatar' => 0,
    'content' => '',
    'rawcontent' => '',
    'keywords' => '',
    'description' => '',
    'head' => ''
    );
    
    if ($this->createpage) {
      $users = tusers::i();
      if ('approved' == $users->getvalue($id, 'status'))  $item = $this->addurl($item);
    }
    $this->items[$id] = $item;
    unset($item['url']);
    $this->db->insert($item);
  }
  
  public function delete($id) {
    if ($id <= 1) return false;
    if (!$this->itemexists($id)) return false;
    $idurl = $this->getvalue($id, 'idurl');
    if ($idurl > 0) litepublisher::$urlmap->deleteitem($idurl);
    return parent::delete($id);
  }
  
  public function edit($id, array $values) {
    if (!$this->itemexists($id)) return false;
    $item = $this->getitem($id);
    $url = isset($values['url']) ? $values['url'] : '';
    unset($values['url'], $values['idurl'], $values['id']);
    foreach ($item as $k => $v) {
      if (isset($values[$k])) $item[$k] = $values[$k];
    }
    $item['id'] = $id;
    $item['content'] = tcontentfilter::i()->filter($item['rawcontent']);
    if ($url && ($url != $item['url'])) {
      if ($item['idurl'] == 0) {
        $item['idurl'] = litepublisher::$urlmap->add($url, get_class($this), $id);
      } else {
        litepublisher::$urlmap->addredir($item['url'], $url);
        litepublisher::$urlmap->setidurl($item['idurl'], $url);
      }
      $item['url'] = $url;
    }
    
    $this->items[$id] = $item;
    unset($item['url']);
    $this->db->updateassoc($item);
  }
  
}//class