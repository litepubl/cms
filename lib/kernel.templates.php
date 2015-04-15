<?php
//local.class.php
class tlocal {
  public static $self;
  public $loaded;
  public $ini;
  public $section;
  public $searchsect;
  
  public static function i($section = '') {
    if (!isset(self::$self)) {
      self::$self= getinstance(__class__);
      self::$self->loadfile('default');
    }
    if ($section != '') self::$self->section = $section;
    return self::$self;
  }
  
  public static function admin($section = '') {
    $result = self::i($section);
    $result->check('admin');
    return $result;
  }
  
  public function __construct() {
    $this->ini = array();
    $this->loaded = array();
    $this->searchsect = array('common', 'default');
  }
  
  public static function get($section, $key) {
    //if (!isset(self::i()->ini[$section][$key])) litepublisher::$options->error("$section:$key");
    return self::i()->ini[$section][$key];
  }
  
  public function __get($name) {
    if (isset($this->ini[$this->section][$name])) return $this->ini[$this->section][$name];
    foreach ($this->searchsect as $section) {
      if (isset($this->ini[$section][$name])) return $this->ini[$section][$name];
    }
    return '';
  }
  
  public function __isset($name) {
    if (isset($this->ini[$this->section][$name])) return true;
    foreach ($this->searchsect as $section) {
      if (isset($this->ini[$section][$name])) return true;
    }
    
    return false;
  }
  
  public function __call($name, $args) {
    return strtr ($this->__get($name), $args->data);
  }
  
  public function addsearch() {
    $this->joinsearch(func_get_args());
  }
  
  public function joinsearch(array $a) {
    foreach ($a as $sect) {
      $sect = trim(trim($sect), "\"',;:.");
      if (!in_array($sect, $this->searchsect)) $this->searchsect[] = $sect;
    }
  }
  
  public function firstsearch() {
    $a = array_reverse(func_get_args());
    foreach ($a as $sect) {
      $i = array_search($sect, $this->searchsect);
      if ($i !== false)         array_splice($this->searchsect, $i, 1);
      array_unshift($this->searchsect, $sect);
    }
  }
  
  public static function date($date, $format = '') {
    if (empty($format)) $format = self::i()->getdateformat();
    return self::i()->translate(date($format, $date), 'datetime');
  }
  
  public function getdateformat() {
    $format = litepublisher::$options->dateformat;
    return $format != ''? $format : $this->ini['datetime']['dateformat'];
  }
  
  public function translate($s, $section = 'default') {
    return strtr($s, $this->ini[$section]);
  }
  
  public function check($name) {
    if ($name == '') $name = 'default';
    if (!in_array($name, $this->loaded)) $this->loadfile($name);
  }
  
  public function loadfile($name) {
    $this->loaded[] = $name;
    $filename = self::getcachedir() . $name;
    if (tfilestorage::loadvar($filename, $v) && is_array($v)) {
      $this->ini = $v + $this->ini ;
      if (isset($v['searchsect'])) $this->joinsearch($v['searchsect']);
    } else {
      $merger = tlocalmerger::i();
      $merger->parse($name);
    }
  }
  
  public static function usefile($name) {
    self::i()->check($name);
    return self::$self;
  }
  
  public static function inifile($class, $filename) {
    return self::inicache(litepublisher::$classes->getresourcedir($class) . litepublisher::$options->language . $filename);
  }
  
  public static function inicache($filename) {
    $self = self::i();
    if (!isset(ttheme::$inifiles[$filename])) {
      $ini = ttheme::cacheini($filename);
      if (is_array($ini)) {
        $self->ini = $ini + $self->ini ;
        if (isset($ini['searchsect'])) $self->joinsearch($ini['searchsect']);
        $keys = array_keys($ini);
        $self->section = array_shift($keys);
        $self->addsearch($self->section);
      }
    }
    return $self;
  }
  
  //backward
  public static function loadlang($name) {
    self::usefile($name);
  }
  
  public static function getcachedir() {
    return litepublisher::$paths->data . 'languages' . DIRECTORY_SEPARATOR;
  }
  
  public static function clearcache() {
    tfiler::delete(self::getcachedir(), false, false);
    self::i()->loaded = array();
  }
  
}//class

class tdateformater {
  public  $date;
public function __construct($date) { $this->date = $date; }
public function __get($name) { return tlocal::translate(date($name, $this->date), 'datetime'); }
}

//views.class.php
class tview extends titem_storage {
  public $sidebars;
  protected $themeinstance;
  
  public static function i($id = 1) {
    if ($id == 1) {
      $class = __class__;
    } else {
      $views = tviews::i();
      $class = $views->itemexists($id) ? $views->items[$id]['class'] : __class__;
    }
    return parent::iteminstance($class, $id);
  }
  
  public static function getinstancename() {
    return 'view';
  }
  
  public static function getview($instance) {
    $id = $instance->getidview();
    if (isset(self::$instances['view'][$id]))     return self::$instances['view'][$id];
    $views = tviews::i();
    if (!$views->itemexists($id)) {
      $id = 1; //default, wich always exists
      $instance->setidview($id);
    }
    return self::i($id);
  }
  
  protected function create() {
    parent::create();
    $this->data = array(
    'id' => 0,
    'class' => get_class($this),
    'name' => 'default',
    'themename' => 'default',
    'menuclass' => 'tmenus',
    'hovermenu' => true,
    'customsidebar' => false,
    'disableajax' => false,
    'custom' => array(),
    'sidebars' => array()
    );
    $this->sidebars = &$this->data['sidebars'];
    $this->themeinstance = null;
  }
  
  public function __destruct() {
    unset($this->themeinstance);
    parent::__destruct();
  }
  
  public function getowner() {
    return tviews::i() ;
  }
  
  public function load() {
    if (parent::load()) {
      $this->sidebars = &$this->data['sidebars'];
      return true;
    }
    return false;
  }
  
  protected function get_theme_instance($name) {
    return ttheme::getinstance($name);
  }
  
  public function setthemename($name) {
    if ($name != $this->themename) {
      if (!ttheme::exists($name)) return $this->error(sprintf('Theme %s not exists', $name));
      $this->data['themename'] = $name;
      $this->themeinstance = $this->get_theme_instance($name);
      $this->data['custom'] = $this->themeinstance->templates['custom'];
      $this->save();
      tviews::i()->themechanged($this);
    }
  }
  
  public function gettheme() {
    if (isset($this->themeinstance)) return $this->themeinstance;
    if (ttheme::exists($this->themename)) {
      $this->themeinstance = $this->get_theme_instance($this->themename);
      $viewcustom = &$this->data['custom'];
      $themecustom = &$this->themeinstance->templates['custom'];
      //aray_equal
      if ((count($viewcustom) == count($themecustom)) && !count(array_diff(array_keys($viewcustom), array_keys($themecustom)))) {
        $this->themeinstance->templates['custom'] = $viewcustom;
      } else {
        $this->data['custom'] = $themecustom;
        $this->save();
      }
    } else {
      $this->setthemename('default');
    }
    return $this->themeinstance;
  }
  
  public function setcustomsidebar($value) {
    if ($value != $this->customsidebar) {
      if ($this->id == 1) return false;
      if ($value) {
        $default = tview::i(1);
        $this->sidebars = $default->sidebars;
      } else {
        $this->sidebars = array();
      }
      $this->data['customsidebar'] = $value;
      $this->save();
    }
  }
  
}//class

class tviews extends titems_storage {
  public $defaults;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->basename = 'views';
    $this->addevents('themechanged');
    $this->addmap('defaults', array());
  }
  
  public function add($name) {
    $this->lock();
    $id = ++$this->autoid;
    $view = litepublisher::$classes->newitem(tview::getinstancename(), 'tview', $id);
    $view->id = $id;
    $view->name = $name;
    $view->data['class'] = get_class($view);
    $this->items[$id] = &$view->data;
    $this->unlock();
    return $id;
  }
  
  public function addview(tview $view) {
    $this->lock();
    $id = ++$this->autoid;
    $view->id = $id;
    if ($view->name == '') $view->name = 'view_' . $id;
    $view->data['class'] = get_class($view);
    $this->items[$id] = &$view->data;
    $this->unlock();
    return $id;
  }
  
  public function delete($id) {
    if ($id == 1) return $this->error('You cant delete default view');
    foreach ($this->defaults as $name => $iddefault) {
      if ($id == $iddefault) $this->defaults[$name] = 1;
    }
    return parent::delete($id);
  }
  
  public function get($name) {
    foreach ($this->items as $id => $item) {
      if ($name == $item['name']) return tview::i($id);
    }
    return false;
  }
  
  public function widgetdeleted($idwidget) {
    $deleted = false;
    foreach ($this->items as &$viewitem) {
      unset($sidebar);
      foreach ($viewitem['sidebars'] as &$sidebar) {
        for ($i = count($sidebar) - 1; $i >= 0; $i--) {
          if ($idwidget == $sidebar[$i]['id']) {
            array_delete($sidebar, $i);
            $deleted = true;
          }
        }
      }
    }
    if ($deleted) $this->save();
  }
  
}//class


class tevents_itemplate extends tevents {
  
  protected function create() {
    parent::create();
    $this->data['idview'] = 1;
  }
  
public function gethead() {}
public function getkeywords() {}
public function getdescription() {}
  
  public function getidview() {
    return $this->data['idview'];
  }
  
  public function setidview($id) {
    if ($id != $this->idview) {
      $this->data['idview'] = $id;
      $this->save();
    }
  }
  
  public function getview() {
    return tview::getview($this);
  }
  
}//class

class titems_itemplate extends titems {
  
  protected function create() {
    parent::create();
    $this->data['idview'] = 1;
    $this->data['keywords'] = '';
    $this->data['description'] = '';
    $this->data['head'] = '';
  }
  
  public function gethead() {
    return $this->data['head'];
  }
  
  public function getkeywords() {
    return $this->data['keywords'];
  }
  
  public function getdescription() {
    return $this->data['description'];
  }
  
  public function getidview() {
    return $this->data['idview'];
  }
  
  public function setidview($id) {
    if ($id != $this->data['idview']) {
      $this->data['idview'] = $id;
      $this->save();
    }
  }
  
  public function getview() {
    return tview::getview($this);
  }
  
}//class

//template.class.php
class ttemplate extends tevents_storage {
  public $path;
  public $url;
  public $context;
  public $itemplate;
  public $view;
  public $ltoptions;
  public $custom;
  public $hover;
  public $extrahead;
  public $extrabody;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    //prevent recursion
    litepublisher::$classes->instances[get_class($this)] = $this;
    parent::create();
    $this->basename = 'template' ;
    $this->addevents('beforecontent', 'aftercontent', 'onhead', 'onbody', 'onrequest', 'ontitle', 'ongetmenu');
    $this->path = litepublisher::$paths->themes . 'default' . DIRECTORY_SEPARATOR ;
    $this->url = litepublisher::$site->files . '/themes/default';
    $this->itemplate = false;
    $this->ltoptions = array(
    'url' =>    litepublisher::$site->url,
    'files' =>litepublisher::$site->files,
    'idurl' => litepublisher::$urlmap->itemrequested['id'],
    'lang' => litepublisher::$site->language,
    'video_width' => litepublisher::$site->video_width,
    'video_height' => litepublisher::$site->video_height,
    'theme' => array(),
    'custom' => array(),
    );
    $this->hover = true;
    $this->data['heads'] = '';
    $this->data['js'] = '<script type="text/javascript" src="%s"></script>';
  $this->data['jsready'] = '<script type="text/javascript">$(document).ready(function() {%s});</script>';
    $this->data['jsload'] = '<script type="text/javascript">$.load_script(%s);</script>';
    $this->data['footer']=   '<a href="http://litepublisher.com/">Powered by Lite Publisher</a>';
    $this->data['tags'] = array();
    $this->addmap('custom', array());
    $this->extrahead = '';
    $this->extrabody = '';
  }
  
  public function assignmap() {
    parent::assignmap();
    $this->ltoptions['custom'] = &$this->custom;
    $this->ltoptions['jsmerger'] = &$this->data['jsmerger'];
    $this->ltoptions['cssmerger'] = &$this->data['cssmerger'];
  }
  
  public function __get($name) {
    if (method_exists($this, $get = 'get' . $name)) return $this->$get();
    if (array_key_exists($name, $this->data)) return $this->data[$name];
    if (preg_match('/^sidebar(\d)$/', $name, $m)) {
      $widgets = twidgets::i();
      return $widgets->getsidebarindex($this->context, $this->view, (int) $m[1]);
    }
    
    if (array_key_exists($name, $this->data['tags'])) {
      $tags = ttemplatetags::i();
      return $tags->$name;
    }
    if (isset($this->context) && isset($this->context->$name)) return $this->context->$name;
    return parent::__get($name);
  }
  
  protected function get_view($context) {
    return $this->itemplate ? tview::getview($context) : tview::i();
  }
  
  public function request($context) {
    $this->context = $context;
    ttheme::$vars['context'] = $context;
    ttheme::$vars['template'] = $this;
    $this->itemplate = $context instanceof itemplate;
    $this->view = $this->get_view($context);
    $theme = $this->view->theme;
    $this->ltoptions['theme']['name'] = $theme->name;
    litepublisher::$classes->instances[get_class($theme)] = $theme;
    $this->path = litepublisher::$paths->themes . $theme->name . DIRECTORY_SEPARATOR ;
    $this->url = litepublisher::$site->files . '/themes/' . $theme->name;
    if ($this->view->hovermenu) {
      $this->hover = $theme->templates['menu.hover'];
      if ($this->hover != 'bootstrap')     $this->hover  =     ($this->hover  == 'true');
    } else {
      $this->hover = false;
    }
    
    $result = $this->httpheader();
    $result  .= $theme->gethtml($context);
    $this->callevent('onbody', array(&$this->extrabody));
    if ($this->extrabody) $result = str_replace('</body>', $this->extrabody . '</body>', $result);
    $this->callevent('onrequest', array(&$result));
    unset(ttheme::$vars['context'], ttheme::$vars['template']);
    return $result;
  }
  
  protected function  httpheader() {
    $ctx = $this->context;
    if (method_exists($ctx, 'httpheader')) {
      $result= $ctx->httpheader();
      if (!empty($result)) return $result;
    }
    
    if (isset($ctx->idperm) && ($idperm = $ctx->idperm)) {
      $perm =tperm::i($idperm);
      if ($result = $perm->getheader($ctx)) {
        return $result . turlmap::htmlheader($ctx->cache);
      }
    }
    
    return turlmap::htmlheader($ctx->cache);
  }
  
  //html tags
  public function getsidebar() {
    return twidgets::i()->getsidebar($this->context, $this->view);
  }
  
  public function gettitle() {
    $title = $this->itemplate ? $this->context->gettitle() : '';
    if ($this->callevent('ontitle', array(&$title))) return $title;
    return $this->parsetitle($this->view->theme->title, $title);
  }
  
  public function parsetitle($tml, $title) {
    $args = targs::i();
    $args->title = $title;
    $result = $this->view->theme->parsearg($tml, $args);
    //$result = trim($result, sprintf(' |.:%c%c', 187, 150));
    $result = trim($result, " |.:\n\r\t");
    if ($result == '') return litepublisher::$site->name;
    return $result;
  }
  
  public function geticon() {
    $result = '';
    if (isset($this->context) && isset($this->context->icon)) {
      $icon = $this->context->icon;
      if ($icon > 0) {
        $files = tfiles::i();
        if ($files->itemexists($icon)) $result = $files->geturl($icon);
      }
    }
    if ($result == '')  return litepublisher::$site->files . '/favicon.ico';
    return $result;
  }
  
  public function getkeywords() {
    $result = $this->itemplate ? $this->context->getkeywords() : '';
    if ($result == '')  return litepublisher::$site->keywords;
    return $result;
  }
  
  public function getdescription() {
    $result = $this->itemplate ? $this->context->getdescription() : '';
    if ($result =='') return litepublisher::$site->description;
    return $result;
  }
  
  public function getmenu() {
    if ($r = $this->ongetmenu()) return $r;
    //$current = $this->context instanceof tmenu ? $this->context->id : 0;
    $view = $this->view;
    $menuclass = $view->menuclass;
    $filename = $view->theme->name . sprintf('.%s.%s.php',
    $menuclass, litepublisher::$options->group ? litepublisher::$options->group : 'nobody');
    
    if ($result = litepublisher::$urlmap->cache->get($filename)) return $result;
    
    $menus = getinstance($menuclass);
    $result = $menus->getmenu($this->hover, 0);
    litepublisher::$urlmap->cache->set($filename, $result);
    return $result;
  }
  
  private function getltoptions() {
    return sprintf('<script type="text/javascript">window.ltoptions = %s;</script>', tojson($this->ltoptions));
  }
  
  public function getjavascript($filename) {
    return sprintf($this->js, litepublisher::$site->files . $filename);
  }
  
  public function getready($s) {
    return sprintf($this->jsready, $s);
  }
  
  public function getloadjavascript($s) {
    return sprintf($this->jsload, $s);
  }
  
  public function addtohead($s) {
    $s = trim($s);
    if (false === strpos($this->heads, $s)) {
      $this->heads = trim($this->heads) . "\n" . $s;
      $this->save();
    }
  }
  
  public function deletefromhead($s) {
    $s = trim($s);
    $i = strpos($this->heads, $s);
    if (false !== $i) {
      $this->heads = substr_replace($this->heads, '', $i, strlen($s));
      $this->heads = trim(str_replace("\n\n", "\n", $this->heads));
      $this->save();
    }
  }
  
  public function gethead() {
    $result = $this->heads;
    if ($this->itemplate) $result .= $this->context->gethead();
    $result = $this->getltoptions() . $result;
    $result .= $this->extrahead;
    $result = $this->view->theme->parse($result);
    $this->callevent('onhead', array(&$result));
    return $result;
  }
  
  public function getcontent() {
    $result = '';
    $this->callevent('beforecontent', array(&$result));
    $result .= $this->itemplate ? $this->context->getcont() : '';
    $this->callevent('aftercontent', array(&$result));
    return $result;
  }
  
  protected function setfooter($s) {
    if ($s != $this->data['footer']) {
      $this->data['footer'] = $s;
      $this->Save();
    }
  }
  
  public function getpage() {
    $page = litepublisher::$urlmap->page;
    if ($page <= 1) return '';
    return sprintf(tlocal::get('default', 'pagetitle'), $page);
  }
  
  public function trimwords($s, array $words) {
    if ($s == '') return '';
    foreach ($words as $word) {
      if (strbegin($s, $word)) $s = substr($s, strlen($word));
      if (strend($s, $word)) $s = substr($s, 0, strlen($s) - strlen*($word));
    }
    return $s;
  }
  
}//class

//theme.class.php
class ttheme extends tevents {
  public static $instances = array();
  public static $vars = array();
  public static $defaultargs;
  public static $inifiles;
  public $name;
  public $parsing;
  public $templates;
  public $extratml;
  private $themeprops;
  
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
    return array('submenu', 'categories', 'tags', 'archives', 'links', 'posts', 'comments', 'friends', 'meta') ;
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
    $this->themeprops = new tthemeprops($this);
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
    unset($this->themeprops, self::$instances[$this->name], $this->templates);
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
  
  public function __get($name) {
    if (array_key_exists($name, $this->templates)) return $this->themeprops->setpath($name);
    if ($name == 'comment') return $this->themeprops->setpath('content.post.templatecomments.comments.comment');
    if ($name == 'sidebar') return $this->themeprops->setroot($this->templates['sidebars'][0]);
    if (preg_match('/^sidebar(\d)$/', $name, $m)) return $this->themeprops->setroot($this->templates['sidebars'][$m[1]]);
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if (array_key_exists($name, $this->templates)) {
      $this->templates[$name] = $value;
      return;
    }
    return parent::__set($name, $value);
  }
  
  public function gettag($path) {
    if (!array_key_exists($path, $this->templates)) $this->error(sprintf('Path "%s" not found', $path));
    $this->themeprops->setpath($path);
    $this->themeprops->tostring = true;
    return $this->themeprops;
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
    if (isset($context->index_tml) && ($tml = $context->index_tml)) return $this->parse($tml);
    return $this->parse($this->templates['index']);
  }
  
  public function getnotfount() {
    return $this->parse($this->templates['content.notfound']);
  }
  
  public function getpages($url, $page, $count, $params = '') {
    if (!(($count > 1) && ($page >=1) && ($page <= $count)))  return '';
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
    foreach ($items as $i) {
      $args->page = $i;
      $link = $i == 1 ? $url : $pageurl .$i . '/';
      if ($params) $link .= $params;
      $args->link = $link;
      $a[] = $this->parsearg(($i == $page ? $currenttml : $tml), $args);
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
    //if (($type == 'text') || ($type == 'editor')) $value =  self::quote(htmlspecialchars($value));
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

class tthemeprops {
  
  public $path;
  public $tostring;
  private $root;
  private $theme;
  
  public function __construct(ttheme $theme) {
    $this->theme = $theme;
    $this->root = &$theme->templates;
    $this->path = '';
    $this->tostring = false;
  }
  
  public function __destruct() {
    unset($this->theme, $this->root);
  }
  
  public function error($path) {
    litepublisher::$options->trace(sprintf('Path "%s" not found', $path));
    litepublisher::$options->showerrors();
  }
  
  public function getpath($name) {
    return $this->path == '' ? $name : $this->path . '.' . $name;
  }
  
  public function setpath($path) {
    $this->root = &$this->theme->templates;
    $this->path = $path;
    $this->tostring = false;
    return $this;
  }
  
  public function setroot(array &$root) {
    $this->setpath('');
    $this->root = &$root;
    return $this;
  }
  
  public function __get($name) {
    //echo "$name get tml<br>";
    $path = $this->getpath($name);
    if (!array_key_exists($path, $this->root)) $this->error($path);
    if ($this->tostring) return $this->root[$path];
    $this->path = $path;
    return $this;
  }
  
  public function __set($name, $value) {
    $this->root[$this->getpath($name)] = $value;
  }
  
  public function __call($name, $params) {
    if (isset($params[0]) && is_object($params[0]) && ($params[0] instanceof targs)) {
      return $this->theme->parsearg( (string) $this->$name, $params[0]);
    } else {
      return $this->theme->parse((string) $this->$name);
    }
  }
  
  public function __tostring() {
    if (array_key_exists($this->path, $this->root)) {
      return $this->root[$this->path];
    } else {
      $this->error($this->path);
    }
  }
  
  public function __isset($name) {
    return array_key_exists($this->getpath($name), $this->root);
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

//widgets.class.php
class twidget extends tevents {
  public $id;
  public $template;
  protected $adminclass;
  
  protected function create() {
    parent::create();
    $this->basename = 'widget';
    $this->cache = 'cache';
    $this->id = 0;
    $this->template = 'widget';
    $this->adminclass = 'tadminwidget';
  }
  
  public function addtosidebar($sidebar) {
    $widgets = twidgets::i();
    $id = $widgets->add($this);
    $sidebars = tsidebars::i();
    $sidebars->insert($id, false, $sidebar, -1);
    
    litepublisher::$urlmap->clearcache();
    return $id;
  }
  
  protected function getadmin() {
    if (($this->adminclass != '') && class_exists($this->adminclass)) {
      $admin = getinstance($this->adminclass);
      $admin->widget = $this;
      return $admin;
    }
    $this->error(sprintf('The "%s" admin class not found', $this->adminclass));
  }
  
  public function getwidget($id, $sidebar) {
    ttheme::$vars['widget'] = $this;
    try {
      $title = $this->gettitle($id);
      $content = $this->getcontent($id, $sidebar);
    } catch (Exception $e) {
      litepublisher::$options->handexception($e);
      return '';
    }
    
    $theme = ttheme::i();
    $result = $theme->getidwidget($id, $title, $content, $this->template, $sidebar);
    unset(ttheme::$vars['widget']);
    return $result;
  }
  
  public function getdeftitle() {
    return '';
  }
  
  public function gettitle($id) {
    if (!isset($id)) $this->error('no id');
    $widgets = twidgets::i();
    if (isset($widgets->items[$id])) {
      return $widgets->items[$id]['title'];
    }
    return $this->getdeftitle();
  }
  
  public function settitle($id, $title) {
    $widgets = twidgets::i();
    if (isset($widgets->items[$id]) && ($widgets->items[$id]['title'] != $title)) {
      $widgets->items[$id]['title'] = $title;
      $widgets->save();
    }
  }
  
  public function getcontent($id, $sidebar) {
    return '';
  }
  
  public static function getcachefilename($id) {
    $theme = ttheme::i();
    if ($theme->name == '') {
      $theme = tview::i()->theme;
    }
    return sprintf('widget.%s.%d.php', $theme->name, $id);
  }
  
  public function expired($id) {
    switch ($this->cache) {
      case 'cache':
      $cache = twidgetscache::i();
      $cache->expired($id);
      break;
      
      case 'include':
      $sidebar = self::findsidebar($id);
      $filename = self::getcachefilename($id, $sidebar);
      litepublisher::$urlmap->cache->set($filename, $this->getcontent($id, $sidebar));
      break;
    }
  }
  
  public static function findsidebar($id) {
    $view = tview::i();
    foreach ($view->sidebars as $i=> $sidebar) {
      foreach ($sidebar as $item) {
        if ($id == $item['id']) return $i;
      }
    }
    return 0;
  }
  
  public function expire() {
    $widgets = twidgets::i();
    foreach ($widgets->items as $id => $item) {
      if ($this instanceof $item['class']) $this->expired($id);
    }
  }
  
  public function getcontext($class) {
    if (litepublisher::$urlmap->context instanceof $class) return litepublisher::$urlmap->context;
    //ajax
    $widgets = twidgets::i();
    return litepublisher::$urlmap->getidcontext($widgets->idurlcontext);
  }
  
}//class

class torderwidget extends twidget {
  
  protected function create() {
    parent::create();
    unset($this->id);
    $this->data['id'] = 0;
    $this->data['ajax'] = false;
    $this->data['order'] = 0;
    $this->data['sidebar'] = 0;
  }
  
  public function onsidebar(array &$items, $sidebar) {
    if ($sidebar != $this->sidebar) return;
    $order = $this->order;
    if (($order < 0) || ($order >= count($items))) $order = count($items);
    array_insert($items, array('id' => $this->id, 'ajax' => $this->ajax), $order);
  }
  
}//class

class tclasswidget extends twidget {
  private $item;
  
  private function isvalue($name) {
    return in_array($name, array('ajax', 'order', 'sidebar'));
  }
  
  public function __get($name) {
    if ($this->isvalue($name)) {
      if (!$this->item) {
        $widgets = twidgets::i();
        $this->item = &$widgets->finditem($widgets->find($this));
      }
      return $this->item[$name];
    }
    return parent::__get($name);
  }
  
  public function __set($name, $value) {
    if ($this->isvalue($name)) {
      if (!$this->item) {
        $widgets = twidgets::i();
        $this->item = &$widgets->finditem($widgets->find($this));
      }
      $this->item[$name] = $value;
    } else {
      parent::__set($name, $value);
    }
  }
  
  public function save() {
    parent::save();
    $widgets = twidgets::i();
    $widgets->save();
  }
  
}//class

class twidgets extends titems_storage {
  public $classes;
  public $currentsidebar;
  public $idwidget;
  public $idurlcontext;
  
  public static function i($id = null) {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->addevents('onwidget', 'onadminlogged', 'onadminpanel', 'ongetwidgets', 'onsidebar');
    $this->basename = 'widgets';
    $this->currentsidebar = 0;
    $this->idurlcontext = 0;
    $this->addmap('classes', array());
  }
  
  public function add(twidget $widget) {
    return $this->additem( array(
    'class' => get_class($widget),
    'cache' => $widget->cache,
    'title' => $widget->gettitle(0),
    'template' => $widget->template
    ));
  }
  
  public function addext(twidget $widget, $title, $template) {
    return $this->additem( array(
    'class' => get_class($widget),
    'cache' => $widget->cache,
    'title' => $title,
    'template' => $template
    ));
  }
  
  public function addclass(twidget $widget, $class) {
    $this->lock();
    $id = $this->add($widget);
    if (!isset($this->classes[$class])) $this->classes[$class] = array();
    $this->classes[$class][] = array(
    'id' => $id,
    'order' => 0,
    'sidebar' => 0,
    'ajax' => false
    );
    $this->unlock();
    return $id;
  }
  
  public function subclass($id) {
    foreach ($this->classes as $class => $items) {
      foreach ($items as $item) {
        if ($id == $item['id']) return $class;
      }
    }
    return false;
  }
  
  public function delete($id) {
    if (!isset($this->items[$id])) return false;
    
    foreach ($this->classes as $class => $items) {
      foreach ($items as $i => $item) {
        if ($id == $item['id']) array_delete($this->classes[$class], $i);
      }
    }
    
    unset($this->items[$id]);
    $this->deleted($id);
    $this->save();
    return true;
  }
  
  public function deleteclass($class) {
    $this->unbind($class);
    $deleted = array();
    foreach ($this->items as $id => $item) {
      if($class == $item['class']) {
        unset($this->items[$id]);
        $deleted[] = $id;
      }
    }
    
    if (count($deleted) > 0) {
      foreach ($this->classes as $name => $items) {
        foreach ($items as $i => $item) {
          if (in_array($item['id'], $deleted)) array_delete($this->classes[$name], $i);
        }
        if (count($this->classes[$name]) == 0) unset($this->classes[$name]);
      }
    }
    
    if (isset($this->classes[$class])) unset($this->classes[$class]);
    $this->save();
    foreach ($deleted as $id)     $this->deleted($id);
  }
  
  public function class2id($class) {
    foreach ($this->items as $id => $item) {
      if($class == $item['class']) return $id;
    }
    
    return false;
  }
  
  public function getwidget($id) {
    if (!isset($this->items[$id])) return $this->error("The requested $id widget not found");
    $class = $this->items[$id]['class'];
    if (!class_exists($class)) {
      $this->delete($id);
      return $this->error("The $class class not found");
    }
    $result = getinstance($class);
    $result->id = $id;
    return $result;
  }
  
  public function getsidebar($context, tview $view) {
    return $this->getsidebarindex($context, $view, $this->currentsidebar++);
  }
  
  public function getsidebarindex($context, tview $view, $sidebar) {
    $items = $this->getwidgets($context, $view, $sidebar);
    if ($context instanceof iwidgets) $context->getwidgets($items, $sidebar);
    if (litepublisher::$options->admincookie) $this->callevent('onadminlogged', array(&$items, $sidebar));
    if (litepublisher::$urlmap->adminpanel) $this->callevent('onadminpanel', array(&$items, $sidebar));
    $this->callevent('ongetwidgets', array(&$items, $sidebar));
    $result = $this->getsidebarcontent($items, $sidebar, !$view->customsidebar && $view->disableajax);
    if ($context instanceof iwidgets) $context->getsidebar($result, $sidebar);
    $this->callevent('onsidebar', array(&$result, $sidebar));
    return $result;
  }
  
  private function getwidgets($context, tview $view, $sidebar) {
    $theme = $view->theme;
    if (($view->id >  1) && !$view->customsidebar) {
      $view = tview::i(1);
    }
    
    $items =  isset($view->sidebars[$sidebar]) ? $view->sidebars[$sidebar] : array();
    
    $subitems =  $this->getsubitems($context, $sidebar);
    $items = $this->joinitems($items, $subitems);
    if ($sidebar + 1 == $theme->sidebarscount) {
      for ($i = $sidebar + 1; $i < count($view->sidebars); $i++) {
        $subitems =  $this->joinitems($view->sidebars[$i], $this->getsubitems($context, $i));
        
        //delete copies
        foreach ($subitems as $index => $subitem) {
          $id = $subitem['id'];
          foreach ($items as $item) {
            if ($id == $item['id']) array_delete($subitems, $index);
          }
        }
        
        foreach ($subitems as $item) $items[] = $item;
      }
    }
    
    return $items;
  }
  
  private function getsubitems($context, $sidebar) {
    $result = array();
    foreach ($this->classes as $class => $items) {
      if ($context instanceof $class) {
        foreach ($items as  $item) {
          if ($sidebar == $item['sidebar']) $result[] = $item;
        }
      }
    }
    return $result;
  }
  
  private function joinitems(array $items, array $subitems) {
    if (count($subitems) == 0) return $items;
    if (count($items) > 0) {
      //delete copies
      for ($i = count($items) -1; $i >= 0; $i--) {
        $id = $items[$i]['id'];
        foreach ($subitems as $subitem) {
          if ($id == $subitem['id']) array_delete($items, $i);
        }
      }
    }
    //join
    foreach ($subitems as $item) {
      $count = count($items);
      $order = $item['order'];
      if (($order < 0) || ($order >= $count)) {
        $items[] = $item;
      } else {
        array_insert($items, $item, $order);
      }
    }
    
    return $items;
  }
  
  private function getsidebarcontent(array $items, $sidebar, $disableajax) {
    $result = '';
    foreach ($items as $item) {
      $id = $item['id'];
      if (!isset($this->items[$id])) continue;
      $cachetype = $this->items[$id]['cache'];
      if ($disableajax)  $item['ajax'] = false;
      if ($item['ajax'] === 'inline') {
        switch ($cachetype) {
          case 'cache':
          case 'nocache':
          case false:
          $content = $this->getinline($id, $sidebar);
          break;
          
          default:
          $content = $this->getajax($id, $sidebar);
          break;
        }
      } elseif ($item['ajax']) {
        $content = $this->getajax($id, $sidebar);
      } else {
        switch ($cachetype) {
          case 'cache':
          $content = $this->getwidgetcache($id, $sidebar);
          break;
          
          case 'include':
          $content = $this->includewidget($id, $sidebar);
          break;
          
          case 'nocache':
          case false:
          $widget = $this->getwidget($id);
          $content = $widget->getwidget($id, $sidebar);
          break;
          
          case 'code':
          $content = $this->getcode($id, $sidebar);
          break;
        }
      }
      $this->callevent('onwidget', array($id, &$content));
      $result .= $content;
    }
    return $result;
  }
  
  public function getajax($id, $sidebar) {
    $theme = ttheme::i();
    $title = $theme->getajaxtitle($id, $this->items[$id]['title'], $sidebar, 'ajaxwidget');
    $content = "<!--widgetcontent-$id-->";
    return $theme->getidwidget($id, $title, $content, $this->items[$id]['template'], $sidebar);
  }
  
  public function getinline($id, $sidebar) {
    $theme = ttheme::i();
    $title = $theme->getajaxtitle($id, $this->items[$id]['title'], $sidebar, 'inlinewidget');
    if ('cache' == $this->items[$id]['cache']) {
      $cache = twidgetscache::i();
      $content = $cache->getcontent($id, $sidebar);
    } else {
      $widget = $this->getwidget($id);
      $content = $widget->getcontent($id, $sidebar);
    }
    $content = sprintf('<!--%s-->', $content);
    return $theme->getidwidget($id, $title, $content, $this->items[$id]['template'], $sidebar);
  }
  
  public function getwidgetcache($id, $sidebar) {
    $title = $this->items[$id]['title'];
    $cache = twidgetscache::i();
    $content = $cache->getcontent($id, $sidebar);
    $theme = ttheme::i();
    return $theme->getidwidget($id, $title, $content, $this->items[$id]['template'], $sidebar);
  }
  
  private function includewidget($id, $sidebar) {
    $filename = twidget::getcachefilename($id, $sidebar);
    if (!litepublisher::$urlmap->cache->exists($filename)) {
      $widget = $this->getwidget($id);
      $content = $widget->getcontent($id, $sidebar);
      litepublisher::$urlmap->cache->set($filename, $content);
    }
    
    $theme = ttheme::i();
    return $theme->getidwidget($id, $this->items[$id]['title'], "\n<?php echo litepublisher::\$urlmap->cache->get('$filename'); ?>\n", $this->items[$id]['template'], $sidebar);
  }
  
  private function getcode($id, $sidebar) {
    $class = $this->items[$id]['class'];
    return "\n<?php
    \$widget = $class::i();
    \$widget->id = \$id;
    echo \$widget->getwidget($id, $sidebar);
    ?>\n";
  }
  
  public function find(twidget $widget) {
    $class = get_class($widget);
    foreach ($this->items as $id => $item) {
      if ($class == $item['class']) return $id;
    }
    return false;
  }
  
  public function xmlrpcgetwidget($id, $sidebar, $idurl) {
    if (!isset($this->items[$id])) return $this->error("Widget $id not found");
    $this->idurlcontext = $idurl;
    $result = $this->getwidgetcontent($id, $sidebar);
    //fix bug for javascript client library
    if ($result == '') return 'false';
  }
  
  private static function getget($name) {
    return isset($_GET[$name]) ? (int) $_GET[$name] : false;
  }
  
  private static function error_request($s) {
    return '<?php header(\'HTTP/1.1 400 Bad Request\', true, 400); ?>' . turlmap::htmlheader(false) . $s;
  }
  
  public function request($arg) {
    $this->cache = false;
    $id = self::getget('id');
    $sidebar = self::getget('sidebar');
    $this->idurlcontext = self::getget('idurl');
    if (($id === false) || ($sidebar === false) || !$this->itemexists($id)) return $this->error_request('Invalid params');
    $themename = isset($_GET['themename']) ? trim($_GET['themename']) : tview::i(1)->themename;
    if (!preg_match('/^\w[\w\.\-_]*+$/', $themename) || !ttheme::exists($themename)) $themename = tviews::i(1)->themename;
    $theme = ttheme::getinstance($themename);
    
    try {
      $result = $this->getwidgetcontent($id, $sidebar);
      return turlmap::htmlheader(false) . $result;
    } catch (Exception $e) {
      return $this->error_request('Cant get widget content');
    }
  }
  
  public function getwidgetcontent($id, $sidebar) {
    if (!isset($this->items[$id])) return false;
    switch ($this->items[$id]['cache']) {
      case 'cache':
      $cache = twidgetscache::i();
      $result = $cache->getcontent($id, $sidebar);
      break;
      
      case 'include':
      $filename = twidget::getcachefilename($id, $sidebar);
      $result = litepublisher::$urlmap->cache->get($filename);
      if (!$result) {
        $widget = $this->getwidget($id);
        $result = $widget->getcontent($id, $sidebar);
        litepublisher::$urlmap->cache->set($filename, $result);
      }
      break;
      
      case 'nocache':
      case 'code':
      case false:
      $widget = $this->getwidget($id);
      $result = $widget->getcontent($id, $sidebar);
      break;
    }
    
    return $result;
  }
  
  public function getpos($id) {
    return tsidebars::getpos($this->sidebars, $id);
  }
  
  public function &finditem($id) {
    foreach ($this->classes as $class => $items) {
      foreach ($items as $i => $item) {
        if ($id == $item['id']) return $this->classes[$class][$i];
      }
    }
    $item = null;
    return $item;
  }
  
}//class

class twidgetscache extends titems {
  private $modified;
  
  public static function i($id = null) {
    return getinstance(__class__);
  }
  
  protected function create() {
    $this->dbversion = false;
    parent::create();
    $this->modified = false;
  }
  
  public function getbasename() {
    $theme = ttheme::i();
    return 'widgetscache.' . $theme->name;
  }
  
  public function load() {
    if ($s = litepublisher::$urlmap->cache->get($this->getbasename() .'.php')) {
      return $this->loadfromstring($s);
    }
    return false;
  }
  
  public function savemodified() {
    if ($this->modified) {
      litepublisher::$urlmap->cache->set($this->getbasename(), $this->savetostring());
    }
    $this->modified = false;
  }
  
  public function save() {
    if (!$this->modified) {
      litepublisher::$urlmap->onclose = array($this, 'savemodified');
      $this->modified = true;
    }
  }
  
  public function getcontent($id, $sidebar) {
    if (isset($this->items[$id][$sidebar])) return $this->items[$id][$sidebar];
    return $this->setcontent($id, $sidebar);
  }
  
  public function setcontent($id, $sidebar) {
    $widgets = twidgets::i();
    $widget = $widgets->getwidget($id);
    $result = $widget->getcontent($id, $sidebar);
    $this->items[$id][$sidebar] = $result;
    $this->save();
    return $result;
  }
  
  public function expired($id) {
    if (isset($this->items[$id])) {
      unset($this->items[$id]);
      $this->save();
    }
  }
  
  public function onclearcache() {
    $this->items = array();
    $this->modified = false;
  }
  
}//class

//guard.class.php
class tguard {
  //prevent double call post()
  private static $posted;
  
  public static function post() {
    if (is_bool(self::$posted)) return self::$posted;
    self::$posted = false;
    if (!isset($_POST) || !count($_POST)) return false;
    if (get_magic_quotes_gpc()) {
      foreach ($_POST as $name => $value) {
        $_POST[$name] = stripslashes($_POST[$name]);
      }
    }
    self::$posted = true;
    return true;
  }
  
  public static function is_xxx() {
    if (isset($_GET['ref'])) {
      $ref = $_GET['ref'];
      $url = $_SERVER['REQUEST_URI'];
      $url = substr($url, 0, strpos($url, '&ref='));
      if ($ref == md5(litepublisher::$secret . litepublisher::$site->url . $url . litepublisher::$options->solt)) return false;
    }
    
    $host = '';
    if (!empty($_SERVER['HTTP_REFERER'])) {
      $p = parse_url($_SERVER['HTTP_REFERER']);
      $host = $p['host'];
    }
    return $host != $_SERVER['HTTP_HOST'];
  }
  
  public static function checkattack() {
    if (litepublisher::$options->xxxcheck  && self::is_xxx()) {
      tlocal::usefile('admin');
      if ($_POST) {
        die(tlocal::get('login', 'xxxattack'));
      }
      if ($_GET) {
        die(tlocal::get('login', 'confirmxxxattack') .
        sprintf(' <a href="%1$s">%1$s</a>', $_SERVER['REQUEST_URI']));
      }
    }
    return false;
  }
  
}//class

