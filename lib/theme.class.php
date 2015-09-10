<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class ttheme extends tevents {
  public static $instances = array();
  public static $vars = array();
  public static $defaultargs;
  public static $inifiles;
  public $name;
  public $parsing;
  public $templates;
  public $extratml;

  public static function exists($name) {
    return file_exists(litepublisher::$paths->data . 'themes'. DIRECTORY_SEPARATOR . $name . '.php') ||
    file_exists(litepublisher::$paths->themes . $name . DIRECTORY_SEPARATOR  . 'about.ini');
  }
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public static function getinstance($name) {
    if (isset(self::$instances[$name])) return self::$instances[$name];
    $result = getinstance(__class__);
    if ($result->name != '') $result = litepublisher::$classes->newinstance(__class__);
    $result->name = $name;
    $result->load();
    return $result;
  }
  
  public static function getwidgetnames() {
    return array('categories', 'tags', 'archives', 'links', 'posts', 'comments', 'friends', 'meta') ;
  }
  
  protected function create() {
    parent::create();
    $this->name = '';
    $this->parsing = array();
    $this->data['type'] = 'litepublisher';
    $this->data['parent'] = '';
    $this->addmap('templates', array());
    $this->templates = array(
    'index' => '',
    'title' => '',
    'menu' => '',
    'content' => '',
    'sidebars' => array(),
    'custom' => array(),
    'customadmin' => array()
    );

    if (!isset(self::$defaultargs)) self::set_defaultargs();
    $this->extratml = '';
  }
  
  public static function set_defaultargs() {
    self::$defaultargs = array(
    '$site.url' => litepublisher::$site->url,
    '$site.files' => litepublisher::$site->files,
  '{$site.q}' => litepublisher::$site->q,
    '$site.q' => litepublisher::$site->q
    );
  }
  
  public function __destruct() {
    unset(self::$instances[$this->name], $this->templates);
    parent::__destruct();
  }
  
  public function getbasename() {
    return 'themes' . DIRECTORY_SEPARATOR . $this->name;
  }
  
  public function load() {
    if ($this->name == '') return false;
    if (parent::load()) {
      self::$instances[$this->name] = $this;
      return true;
    }
    return $this->parsetheme();
  }
  
  public function parsetheme() {
    if (!file_exists(litepublisher::$paths->themes . $this->name . DIRECTORY_SEPARATOR  . 'about.ini')) {
      $this->error(sprintf('The %s theme not exists', $this->name));
    }
    
    $parser = tthemeparser::i();
    if ($parser->parse($this)) {
      self::$instances[$this->name] = $this;
      $this->save();
    }else {
      $this->error(sprintf('Theme file %s not exists', $filename));
    }
  }
  
  public function __tostring() {
    return $this->templates['index'];
  }
  
  public function __set($name, $value) {
    if (array_key_exists($name, $this->templates)) {
      $this->templates[$name] = $value;
      return;
    }
    return parent::__set($name, $value);
  }
  public function reg($exp) {
    if (!strpos($exp, '\.')) $exp = str_replace('.', '\.', $exp);
    $result = array();
    foreach ($this->templates as $name => $val) {
      if (preg_match($exp, $name)) $result[$name] = $val;
    }
    return $result;
  }
  
  public function getsidebarscount() {
    return count($this->templates['sidebars']);
  }
  
  
  private function  get_author() {
    $context = isset(litepublisher::$urlmap->context) ? litepublisher::$urlmap->context : ttemplate::i()->context;
    if (!is_object($context)) {
      if (!isset(self::$vars['post'])) return new emptyclass();
      $context = self::$vars['post'];
    }
    
    if ($context instanceof     tuserpages) return $context;
    $iduser = 0;
    foreach (array('author', 'idauthor', 'user', 'iduser') as $propname) {
      if (isset($context->$propname)) {
        $iduser = $context->$propname;
        break;
      }
    }
    if (!$iduser) return new emptyclass();
    $pages = tuserpages::i();
    if (!$pages->itemexists($iduser)) return new emptyclass();
    $pages->request($iduser);
    return $pages;
  }
  
  private function getvar($name) {
    switch ($name) {
      case 'site':
      return litepublisher::$site;
      
      case 'lang':
      return tlocal::i();
      
      case 'post':
      $context = isset(litepublisher::$urlmap->context) ? litepublisher::$urlmap->context : ttemplate::i()->context;
      if ($context instanceof tpost) return $context;
      break;
      
      case 'author':
      return self::get_author();
      
      case 'metapost':
      return isset(self::$vars['post']) ? self::$vars['post']->meta : new emptyclass();
    } //switch
    
    if (isset($GLOBALS[$name])) {
      $var =  $GLOBALS[$name];
    } else {
      $classes = litepublisher::$classes;
      $var = $classes->gettemplatevar($name);
      if (!$var) {
        if (isset($classes->classes[$name])) {
          $var = $classes->getinstance($classes->classes[$name]);
        } elseif (isset($classes->items[$name])) {
          $var = $classes->getinstance($name);
        } else {
          $class = 't' . $name;
          if (isset($classes->items[$class])) $var = $classes->getinstance($class);
        }
      }
    }
    
    if (!is_object($var)) {
      litepublisher::$options->trace(sprintf('Object "%s" not found in %s', $name, $this->parsing[count($this->parsing) -1]));
      return false;
    }
    
    return $var;
  }
  
  public function parsecallback($names) {
    $name = $names[1];
    $prop = $names[2];
    if (isset(self::$vars[$name])) {
      $var =  self::$vars[$name];
    } elseif ($name == 'custom') {
      return $this->parse($this->templates['custom'][$prop]);
    } elseif ($name == 'label') {
      return "\$$name.$prop";
    } elseif ($var = $this->getvar($name)) {
      self::$vars[$name] = $var;
    } elseif (($name == 'metapost') && isset(self::$vars['post'])) {
      $var = self::$vars['post']->meta;
    } else {
      return '';
    }
    
    try {
    return $var->{$prop};
    } catch (Exception $e) {
      litepublisher::$options->handexception($e);
    }
    return '';
  }
  
  public function parse($s) {
    if (!$s) return '';
    $s = strtr((string) $s, self::$defaultargs);
    if (isset($this->templates['content.admin.tableclass'])) $s = str_replace('$tableclass', $this->templates['content.admin.tableclass'], $s);
    array_push($this->parsing, $s);
    try {
      $s = preg_replace('/%%([a-zA-Z0-9]*+)_(\w\w*+)%%/', '\$$1.$2', $s);
      $result = preg_replace_callback('/\$([a-zA-Z]\w*+)\.(\w\w*+)/', array($this, 'parsecallback'), $s);
    } catch (Exception $e) {
      $result = '';
      litepublisher::$options->handexception($e);
    }
    array_pop($this->parsing);
    return $result;
  }
  
  public function parsearg($s, targs $args) {
    $s = $this->parse($s);
    return strtr ($s, $args->data);
  }
  
  public function replacelang($s, $lang) {
    $s = preg_replace('/%%([a-zA-Z0-9]*+)_(\w\w*+)%%/', '\$$1.$2', (string) $s);
    self::$vars['lang'] = isset($lang) ? $lang : tlocal::i('default');
    $s = strtr($s, self::$defaultargs);
    if (preg_match_all('/\$lang\.(\w\w*+)/', $s, $m, PREG_SET_ORDER)) {
      foreach ($m as $item) {
        $name = $item[1];
      if ($v = $lang->{$name}) {
          $s = str_replace($item[0], $v, $s);
        }
      }
    }
    return $s;
  }
  
  public static function parsevar($name, $var, $s) {
    self::$vars[$name] = $var;
    return self::i()->parse($s);
  }
  
  public function gethtml($context) {
    self::$vars['context'] = $context;
    if (isset($context->index_tml) && ($tml = $context->index_tml)) {
      return $this->parse($tml);
    }
    
    return $this->parse($this->templates['index']);
  }
  
  public function getnotfount() {
    return $this->parse($this->templates['content.notfound']);
  }
  
  public function getpages($url, $page, $count, $params = '') {
    if (!(($count > 1) && ($page >=1) && ($page <= $count)))  {
      return '';
    }
    
    $args = new targs();
    $args->count = $count;
    $from = 1;
    $to = $count;
    $perpage = litepublisher::$options->perpage;
    $args->perpage = $perpage;
    $items = array();
    if ($count > $perpage * 2) {
      //$page is midle of the bar
      $from = (int) max(1, $page - ceil($perpage / 2));
      $to = (int) min($count, $from + $perpage);
    }
    
    if ($from == 1) {
      $items = range($from, $to);
    } else {
      $items[0] = 1;
      if ($from > $perpage) {
        if ($from - $perpage  - 1 < $perpage) {
          $items[] = $perpage;
        } else {
          array_splice($items, count($items), 0, range($perpage, $from - 1, $perpage));
        }
      }
      array_splice($items, count($items), 0, range($from, $to));
    }
    
    if ($to < $count) {
      $from2 = (int) ($perpage * ceil(($to+1) / $perpage));
      if ($from2 + $perpage >= $count) {
        if ($from2 < $count) $items[] = $from2;
      } else {
        array_splice($items, count($items), 0, range($from2, $count, $perpage));
      }
      if ($items[count($items) -1] != $count) $items[] = $count;
    }
    
    $currenttml=$this->templates['content.navi.current'];
    $tml =$this->templates['content.navi.link'];
    if (!strbegin($url, 'http')) $url = litepublisher::$site->url . $url;
    $pageurl = rtrim($url, '/') . '/page/';
    if ($params) $params = litepublisher::$site->q . $params;
    
    $a = array();
    if (($page > 1) && ($tml_prev = trim($this->templates['content.navi.prev']))) {
      $i = $page - 1;
      $args->page = $i;
      $link = $i == 1 ? $url : $pageurl .$i . '/';
      if ($params) $link .= $params;
      $args->link = $link;
      $a[] = $this->parsearg($tml_prev, $args);
    }
    
    foreach ($items as $i) {
      $args->page = $i;
      $link = $i == 1 ? $url : $pageurl .$i . '/';
      if ($params) $link .= $params;
      $args->link = $link;
      $a[] = $this->parsearg(($i == $page ? $currenttml : $tml), $args);
    }
    
    if (($page < $count) && ($tml_next = trim($this->templates['content.navi.next']))) {
      $i = $page + 1;
      $args->page = $i;
      $link = $pageurl .$i . '/';
      if ($params) $link .= $params;
      $args->link = $link;
      $a[] = $this->parsearg($tml_next, $args);
    }
    
    $args->link =$url;
    $args->pageurl = $pageurl;
    $args->page = $page;
    $args->items = implode($this->templates['content.navi.divider'], $a);
    return $this->parsearg($this->templates['content.navi'], $args);
  }
  
  public function getposts(array $items, $lite) {
    if (count($items) == 0) return '';
    if (dbversion) tposts::i()->loaditems($items);
    
    $result = '';
    self::$vars['lang'] = tlocal::i('default');
    //$tml = $lite ? $this->templates['content.excerpts.lite.excerpt'] : $this->templates['content.excerpts.excerpt'];
    foreach($items as $id) {
      $post = tpost::i($id);
      $result .= $post->getcontexcerpt($lite);
      // has $author.* tags in tml
      if (isset(self::$vars['author'])) unset(self::$vars['author']);
    }
    
    $tml = $lite ? $this->templates['content.excerpts.lite'] : $this->templates['content.excerpts'];
    if ($tml != '') $result = str_replace('$excerpt', $result, $this->parse($tml));
    unset(self::$vars['post']);
    return $result;
  }
  
  public function getpostsnavi(array $items, $lite, $url, $count, $liteperpage = 1000) {
    $result = $this->getposts($items, $lite);
    $perpage = $lite ? $liteperpage : litepublisher::$options->perpage;
    $result .= $this->getpages($url, litepublisher::$urlmap->page, ceil($count / $perpage));
    return $result;
  }
  
  public function getpostswidgetcontent(array $items, $sidebar, $tml) {
    if (count($items) == 0) return '';
    $result = '';
    if ($tml == '') $tml = $this->getwidgetitem('posts', $sidebar);
    foreach ($items as $id) {
      self::$vars['post'] = tpost::i($id);
      $result .= $this->parse($tml);
    }
    unset(self::$vars['post']);
    return str_replace('$item', $result, $this->getwidgetitems('posts', $sidebar));
  }
  
  public function getwidgetcontent($items, $name, $sidebar) {
    return str_replace('$item', $items, $this->getwidgetitems($name, $sidebar));
  }
  
  public function getwidget($title, $content, $template, $sidebar) {
    $args = new targs();
    $args->title = $title;
    $args->items = $content;
    $args->sidebar = $sidebar;
    return $this->parsearg($this->getwidgettml($sidebar, $template, ''), $args);
  }
  
  public function getidwidget($id, $title, $content, $template, $sidebar) {
    $args = new targs();
    $args->id = $id;
    $args->title = $title;
    $args->items = $content;
    $args->sidebar = $sidebar;
    return $this->parsearg($this->getwidgettml($sidebar, $template, ''), $args);
  }
  
  public function  getwidgetitem($name, $index) {
    return $this->getwidgettml($index, $name, 'item');
  }
  
  public function  getwidgetitems($name, $index) {
    return $this->getwidgettml($index, $name, 'items');
  }
  
  public function  getwidgettml($index, $name, $tml) {
    $count = count($this->templates['sidebars']);
    if ($index >= $count) $index = $count - 1;
    $widgets = &$this->templates['sidebars'][$index];
    if (($tml != '') && ($tml [0] != '.')) $tml = '.' . $tml;
    if (isset($widgets[$name . $tml])) return $widgets[$name . $tml];
    if (isset($widgets['widget' . $tml])) return $widgets['widget'  . $tml];
    $this->error("Unknown widget '$name' and template '$tml' in $index sidebar");
  }
  
  public function getajaxtitle($id, $title, $sidebar, $tml) {
    $args = new targs();
    $args->title = $title;
    $args->id = $id;
    $args->sidebar = $sidebar;
    return $this->parsearg($this->templates[$tml], $args);
  }
  
  public function simple($content) {
    return str_replace('$content', $content, $this->templates['content.simple']);
  }
  
  public function getbutton($title) {
    return strtr($this->templates['content.admin.button'], array(
    '$lang.$name' => $title,
    'name="$name"' => '',
    'id="submitbutton-$name"' => ''
    ));
  }
  
  public function getsubmit($title) {
    return strtr($this->templates['content.admin.submit'], array(
    '$lang.$name' => $title,
    'name="$name"' => '',
    'id="submitbutton-$name"' => ''
    ));
  }
  
  public static function quote($s) {
    return strtr ($s, array('"'=> '&quot;', "'" => '&#039;', '\\'=> '&#092;', '$' => '&#36;', '%' =>  '&#37;', '_' => '&#95;'));
  }
  
  public function getinput($type, $name, $value, $title) {
    return strtr($this->templates['content.admin.' . $type], array(
    '$lang.$name' => $title,
    '$name' => $name,
    '$value' => $value
    ));
  }
  
  public function getradio($name, $value, $title, $checked) {
    return strtr($this->templates['content.admin.radioitem'], array(
    '$lang.$name' => $title,
    '$name' => $name,
    '$value' => $title,
    '$index' => $value,
    '$checked' => $checked ? 'checked="checked"' : '',
    ));
  }
  
  public static function clearcache() {
    tfiler::delete(litepublisher::$paths->data . 'themes', false, false);
    litepublisher::$urlmap->clearcache();
  }
  
  public static function cacheini($filename) {
    if (isset(self::$inifiles[$filename])) return self::$inifiles[$filename];
    $datafile = tlocal::getcachedir() . sprintf('cacheini.%s.php', md5($filename));
    if (!tfilestorage::loadvar($datafile, $ini) || !is_array($ini)) {
      if (file_exists($filename)) {
        $ini = parse_ini_file($filename, true);
        tfilestorage::savevar($datafile, $ini);
      } else {
        $ini = array();
      }
    }
    
    if (!isset(self::$inifiles)) self::$inifiles = array();
    self::$inifiles[$filename] = $ini;
    return $ini;
  }
  
  public static function inifile($class, $filename) {
    $dir = litepublisher::$classes->getresourcedir($class);
    return self::cacheini($dir . $filename);
  }
  
  public static function getwidgetpath($path) {
    if ($path === '') return '';
    switch ($path) {
      case '.items':
      return '.items';
      
      case '.items.item':
      case '.item':
      return '.item';
      
      case '.items.item.subcount':
      case '.item.subcount':
      case '.subcount':
      return '.subcount';
      
      case '.items.item.subitems':
      case '.item.subitems':
      case '.subitems':
      return '.subitems';
      
      case '.classes':
      case '.items.classes':
      return  '.classes';
    }
    
    return false;
  }
  
}//class

class targs {
  public $data;
  
  public static function i() {
    return litepublisher::$classes->newinstance(__class__);
  }
  
  public function __construct($thisthis = null) {
    if (!isset(ttheme::$defaultargs)) ttheme::set_defaultargs();
    $this->data = ttheme::$defaultargs;
    if (isset($thisthis)) $this->data['$this'] = $thisthis;
  }
  
  public function __get($name) {
    if (($name == 'link') && !isset($this->data['$link'])  && isset($this->data['$url'])) {
      return litepublisher::$site->url . $this->data['$url'];
    }
    return $this->data['$' . $name];
  }
  
  public function __set($name, $value) {
    if (!$name || !is_string($name)) return;
    if (is_array($value)) return;
    
    if (is_bool($value)) {
      $value = $value ? 'checked="checked"' : '';
    }
    
    $this->data['$'.$name] = $value;
    $this->data["%%$name%%"] = $value;
    
    if (($name == 'url') && !isset($this->data['$link'])) {
      $this->data['$link'] = litepublisher::$site->url . $value;
      $this->data['%%link%%'] = litepublisher::$site->url . $value;
    }
  }
  
  public function add(array $a) {
    foreach ($a as $k => $v) {
      $this->__set($k, $v);
      if ($k == 'url') {
        $this->data['$link'] = litepublisher::$site->url . $v;
        $this->data['%%link%%'] = litepublisher::$site->url . $v;
      }
    }
    
    if (isset($a['title']) && !isset($a['text'])) $this->__set('text', $a['title']);
    if (isset($a['text']) && !isset($a['title']))  $this->__set('title', $a['text']);
  }
  
  public function parse($s) {
    return ttheme::i()->parsearg($s, $this);
  }
  
}//class

class emptyclass{
public function __get($name) { return ''; }
}