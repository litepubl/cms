<?php
//menus.admin.class.php
class tadminmenus extends tmenus {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'adminmenu';
    $this->addevents('onexclude');
    $this->data['heads'] = '';
  }
  
  public function settitle($id, $title) {
    if ($id && isset($this->items[$id])) {
      $this->items[$id]['title'] = $title;
      $this->save();
      litepublisher::$urlmap->clearcache();
    }
  }
  
  public function getdir() {
    return litepublisher::$paths->data . 'adminmenus' . DIRECTORY_SEPARATOR;
  }
  
  public function getadmintitle($name) {
    $lang = tlocal::i();
    $ini = &$lang->ini;
    if (isset($ini[$name]['title'])) return $ini[$name]['title'];
    tlocal::usefile('install');
    if (!in_array('adminmenus', $lang->searchsect)) array_unshift($lang->searchsect, 'adminmenus');
    if ($result = $lang->__get($name)) return $result;
    return $name;
  }
  
  public function createurl($parent, $name) {
    return $parent == 0 ? "/admin/$name/" : $this->items[$parent]['url'] . "$name/";
  }
  
  public function createitem($parent, $name, $group, $class) {
    $title = $this->getadmintitle($name);
    $url = $this->createurl($parent, $name);
    return $this->additem(array(
    'parent' => $parent,
    'url' => $url,
    'title' => $title,
    'name' => $name,
    'class' => $class,
    'group' => $group
    ));
  }
  
  public function additem(array $item) {
    if (empty($item['group'])) {
      $groups = tusergroups::i();
      $item['group'] = $groups->items[$groups->defaults[0]]['name'];
    }
    return parent::additem($item);
  }
  
  public function addfakemenu(tmenu $menu) {
    $this->lock();
    $id = parent::addfakemenu($menu);
    if (empty($this->items[$id]['group'])) {
      $groups = tusergroups::i();
      $group = count($groups->defaults)  ? $groups->items[$groups->defaults[0]]['name'] : 'commentator';
      $this->items[$id]['group'] = $group;
    }
    
    $this->unlock();
    return $id;
  }
  
  public function getchilds($id) {
    if ($id == 0) {
      $result = array();
      $options = litepublisher::$options;
      foreach ($this->tree as $iditem => $items) {
        if ($options->hasgroup($this->items[$iditem]['group']))
        $result[] = $iditem;
      }
      return $result;
    }
    
    $parents = array($id);
    $parent = $this->items[$id]['parent'];
    while ($parent != 0) {
      array_unshift ($parents, $parent);
      $parent = $this->items[$parent]['parent'];
    }
    
    $tree = $this->tree;
    foreach ($parents as $parent) {
      foreach ($tree as $iditem => $items) {
        if ($iditem == $parent) {
          $tree = $items;
          break;
        }
      }
    }
    return array_keys($tree);
  }
  
  public function exclude($id) {
    if (!litepublisher::$options->hasgroup($this->items[$id]['group'])) return  true;
    return $this->onexclude($id);
  }
  
}//class

//menu.admin.class.php
class tadminmenu  extends tmenu {
  public static $adminownerprops = array('title', 'url', 'idurl', 'parent', 'order', 'status', 'name', 'group');
  public $arg;
  
  public static function getinstancename() {
    return 'adminmenu';
  }
  
  public static function getowner() {
    return tadminmenus::i();
  }
  
  protected function create() {
    parent::create();
    $this->cache = false;
  }
  
  public function get_owner_props() {
    return self::$adminownerprops;
  }
  
public function load() { return true; }
public function save() { return true; }
  
  public function gethead() {
    return tadminmenus::i()->heads;
  }
  
  public function getidview() {
    return tviews::i()->defaults['admin'];
  }
  
  public static function auth($group) {
    if ($s = tguard::checkattack()) return $s;
    if (!litepublisher::$options->user) {
      turlmap::nocache();
      return litepublisher::$urlmap->redir('/admin/login/' . litepublisher::$site->q . 'backurl=' . urlencode(litepublisher::$urlmap->url));
    }
    
    if (!litepublisher::$options->hasgroup($group)) {
      $url = tusergroups::i()->gethome(litepublisher::$options->group);
      turlmap::nocache();
      return litepublisher::$urlmap->redir($url);
    }
  }
  
  public function request($id) {
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);
    
    if (is_null($id)) $id = $this->owner->class2id(get_class($this));
    $this->data['id'] = (int)$id;
    if ($id > 0) {
      $this->basename =  $this->parent == 0 ? $this->name : $this->owner->items[$this->parent]['name'];
    }
    
    if ($s = self::auth($this->group)) return $s;
    tlocal::usefile('admin');
    $this->arg = litepublisher::$urlmap->argtree;
    if ($s = $this->canrequest()) return $s;
    $this->doprocessform();
  }
  
public function canrequest() { }
  
  protected function doprocessform() {
    if (tguard::post()) {
      litepublisher::$urlmap->clearcache();
    }
    return parent::doprocessform();
  }
  
  public function getcont() {
    if (litepublisher::$options->admincache) {
      $id = isset($_POST['id']) ? (int) $_POST['id'] : 0;
      $filename = 'adminmenu.' . litepublisher::$options->user . '.' .md5($_SERVER['REQUEST_URI'] . '&id=' . $id) . '.php';
      if ($result = litepublisher::$urlmap->cache->get($filename)) return $result;
      $result = parent::getcont();
      litepublisher::$urlmap->cache->set($filename, $result);
      return $result;
    } else {
      return parent::getcont();
    }
  }
  
  public static function idget() {
    return (int) tadminhtml::getparam('id', 0);
  }
  
  public function getaction() {
    return isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
  }
  
  public function gethtml($name = '') {
    $result = tadminhtml::i();
    if ($name == '') $name = $this->basename;
    if (!isset($result->ini[$name]) && $this->parent) {
      $name = $this->owner->items[$this->parent]['name'];
    }
    
    $result->section = $name;
    $lang = tlocal::i($name);
    return $result;
  }
  
  public function getlang() {
    return tlocal::i($this->name);
  }
  
  public function getadminlang() {
    return tlocal::inifile($this, '.admin.ini');
  }
  
  public function inihtml($name = '') {
    $html = $this->gethtml($name);
    $html->iniplugin(get_class($this));
    return $html;
  }
  
  public function getconfirmed() {
    return isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1);
  }
  
  public function getnotfound() {
    return $this->html->h4red->notfound;
  }
  
  public function getadminurl() {
    return litepublisher::$site->url .$this->url . litepublisher::$site->q . 'id';
  }
  
  public function getfrom($perpage, $count) {
    if (litepublisher::$urlmap->page <= 1) return 0;
    return min($count, (litepublisher::$urlmap->page - 1) * $perpage);
  }
  
}//class

//author-rights.class.php
class tauthor_rights extends tevents {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->addevents('gethead', 'getposteditor', 'editpost', 'changeposts', 'canupload', 'candeletefile');
    $this->basename = 'authorrights';
  }
  
}

//theme.admin.class.php
class admintheme extends basetheme {
  
  public static function i() {
    $result = getinstance(__class__);
    if (!$result->name && ($context = litepublisher::$urlmap->context)) {
      $result->name = tview::getview($context)->adminname;
      $result->load();
    }
    
    return $result;
  }
  
  public static function getinstance($name) {
    return self::getbyname(__class__, $name);
  }
  
  public function getparser() {
    return adminparser::i();
  }
  
  public function gettable($head, $body) {
    return strtr($this->templates['table'], array(
    '$class' => ttheme::i()->templates['content.admin.tableclass'],
    '$head' => $head,
    '$body' => $body
    ));
  }
  
  public function getsection($title, $content) {
    return strtr($this->templates['section'], array(
    '$title' => $title,
    '$content' => $content
    ));
  }
  
  public function getcalendar($name, $date) {
    $date = datefilter::timestamp($date);
    
    $args = new targs();
    $args->name = $name;
    $args->title = tlocal::i()->__get($name);
    $args->format = datefilter::$format;
    
    if ($date) {
      $args->date = date(datefilter::$format, $date);
      $args->time = date(datefilter::$timeformat, $date);
    } else {
      $args->date = '';
      $args->time = '';
    }
    
    return $this->parsearg($this->templates['calendar'], $args);
  }
  
  public function getdaterange($from, $to) {
    $from = datefilter::timestamp($from);
    $to = datefilter::timestamp($to);
    
    $args = new targs();
    $args->from = $from ? date(datefilter::$format, $from) : '';
    $args->to = $to ? date(datefilter::$format, $to) : '';
    $args->format = datefilter::$format;
    
    return $this->parsearg($this->templates['daterange'], $args);
  }
  
}//class

//htmlresource.class.php
class tadminhtml {
  public static $tags = array('h1', 'h2', 'h3', 'h4', 'p', 'li', 'ul', 'strong', 'div', 'span');
  public $section;
  public $searchsect;
  public $ini;
  private $map;
  private $section_stack;
  
  public static function i() {
    $self = getinstance(__class__);
    if (count($self->ini) == 0) $self->load();
    return $self;
  }
  
  public static function getinstance($section) {
    $self = self::i();
    $self->section = $section;
    tlocal::i($section);
    return $self;
  }
  
  public function __construct() {
    $this->ini = array();
    $this->searchsect = array('common');
    tlocal::usefile('admin');
  }
  
  public function __get($name) {
    if (isset($this->ini[$this->section][$name])) return $this->ini[$this->section][$name];
    foreach ($this->searchsect as $section) {
      if (isset($this->ini[$section][$name])) return $this->ini[$section][$name];
    }
    
    if (in_array($name, self::$tags)) return new thtmltag($name);
    if (strend($name, 'red') && in_array(substr($name, 0, -3), self::$tags)) return new redtag($name);
    
    throw new Exception("the requested $name item not found in $this->section section");
  }
  
  public function __call($name, $params) {
    if ($name == 'getinput') return call_user_func_array(array(ttheme::i(), 'getinput'), $params);
    $s = $this->__get($name);
    if (is_object($s) && ($s instanceof thtmltag))  return sprintf('<%1$s>%2$s</%1$s>', $name, $params[0]);
    
    if ($name == 'h4error') return sprintf('<h4 class="red">%s</h4>', $params[0]);
    
    $args = isset($params[0]) && $params[0] instanceof targs ? $params[0] : new targs();
    return $this->parsearg($s, $args);
  }
  
  public function parsearg($s, targs $args) {
    if (!is_string($s)) $s = (string) $s;
    $theme = ttheme::i();
    
    // parse tags [form] .. [/form]
    if (is_int($i = strpos($s, '[form]'))) {
      $form = $theme->templates['content.admin.form'];
      $replace = substr($form, 0, strpos($form, '$items'));
      $s = substr_replace($s, $replace, $i, strlen('[form]'));
    }
    
    if ($i = strpos($s, '[/form]')) {
      $replace = substr($form, strrpos($form, '$items') + strlen('$items'));
      $s = substr_replace($s, $replace, $i, strlen('[/form]'));
    }
    
    if (preg_match_all('/\[(editor|checkbox|text|password|combo|hidden|submit|button|calendar|upload)(:|=)(\w*+)\]/i', $s, $m, PREG_SET_ORDER)) {
      foreach ($m as $item) {
        $type = $item[1];
        $name = $item[3];
        $varname = '$' . $name;
        //convert spec charsfor editor
        if (!in_array($type, array('checkbox', 'combo', 'calendar', 'upload'))) {
          if (isset($args->data[$varname])) {
            $args->data[$varname] = self::specchars($args->data[$varname]);
          } else {
            $args->data[$varname] = '';
          }
        }
        
        if ($type == 'calendar') {
          $tag = admintheme::i()->getcalendar($name, $args->data[$varname]);
        } else {
          $tag = strtr($theme->templates["content.admin.$type"], array(
          '$name' => $name,
          '$value' => $varname
          ));
        }
        
        $s = str_replace($item[0], $tag, $s);
      }
    }
    
    $s = strtr($s, $args->data);
    return $theme->parse($s);
  }
  
  public function addsearch() {
    $a = func_get_args();
    foreach ($a as $sect) {
      if (!in_array($sect, $this->searchsect)) $this->searchsect[] = $sect;
    }
  }
  
  public function push_section($section) {
    if (!isset($this->section_stack)) $this->section_stack = array();
    $lang = tlocal::i();
    $this->section_stack[] = array(
    $this->section,
    $lang->section
    );
    
    $this->section = $section;
    $lang->section = $section;
  }
  
  public function pop_section() {
    $a = array_pop($this->section_stack);
    $this->section = $a[0];
    tlocal::i()->section = $a[1];
  }
  
  public static function specchars($s) {
    return strtr(            htmlspecialchars($s), array(
    '"' => '&quot;',
    "'" =>'&#39;',
    '$' => '&#36;',
    '%' => '&#37;',
    '_' => '&#95;'
    ));
  }
  
  public function fixquote($s) {
    $s = str_replace("\\'", '\"', $s);
    $s = str_replace("'", '"', $s);
    return str_replace('\"', "'", $s);
  }
  
  public function load() {
    $filename = tlocal::getcachedir() . 'adminhtml';
    if (tfilestorage::loadvar($filename, $v) && is_array($v)) {
      $this->ini = $v + $this->ini;
    } else {
      $merger = tlocalmerger::i();
      $merger->parsehtml();
    }
  }
  
  public function loadinstall() {
    if (isset($this->ini['installation'])) return;
    tlocal::usefile('install');
    if( $v = parse_ini_file(litepublisher::$paths->languages . 'install.ini', true)) {
      $this->ini = $v + $this->ini;
    }
  }
  
  public static function getparam($name, $default) {
    return !empty($_GET[$name]) ? $_GET[$name] : (!empty($_POST[$name]) ? $_POST[$name] : $default);
  }
  
  public static function idparam() {
    return (int) self::getparam('id', 0);
  }
  
  public static function getadminlink($path, $params) {
    return litepublisher::$site->url . $path . litepublisher::$site->q . $params;
  }
  
  public static function getlink($url, $title) {
    return sprintf('<a href="%s%s">%s</a>', litepublisher::$site->url, $url, $title);
  }
  
  public static function array2combo(array $items, $selected) {
    $result = '';
    foreach ($items as $i => $title) {
      $result .= sprintf('<option value="%s" %s>%s</option>', $i, $i == $selected ? 'selected' : '', self::specchars($title));
    }
    return $result;
  }
  
  public static function getcombobox($name, array $items, $selected) {
    return sprintf('<select name="%1$s" id="%1$s">%2$s</select>', $name,
    self::array2combo($items, $selected));
  }
  
  public function adminform($tml, targs $args) {
    $args->items = $this->parsearg($tml, $args);
    return $this->parsearg(ttheme::i()->templates['content.admin.form'], $args);
  }
  
  public function getupload($name) {
    return $this->getinput('upload', $name, '', '');
  }
  
  public function getcheckbox($name, $value) {
    return $this->getinput('checkbox', $name, $value ? 'checked="checked"' : '', '$lang.' . $name);
  }
  
  public function getradioitems($name, array $items, $selected) {
    $result = '';
    $theme = ttheme::i();
    $tml = $theme->templates['content.admin.radioitem'];
    foreach ($items as $index => $value) {
      $result .= strtr($tml, array(
      '$index' => $index,
      '$checked' => $index == $selected ? 'checked="checked"' : '',
      '$name' => $name,
      '$value' => self::specchars($value)
      ));
    }
    return $result;
  }
  
  public function getsubmit() {
    $result = '';
    $theme = ttheme::i();
    $lang = tlocal::i();
    
    $a = func_get_args();
    foreach ($a as $name) {
      $result .= strtr($theme->templates['content.admin.button'], array(
      '$lang.$name' => $lang->__get($name),
      '$name' => $name,
      ));
    }
    
    return $result;
  }
  
  public function getedit($name, $value, $title) {
    return $this->getinput('text', $name, $value, $title);
  }
  
  public function getcombo($name, $value, $title) {
    return $this->getinput('combo', $name, $value, $title);
  }
  
  public static function datestr($date) {
    if ($date == '0000-00-00 00:00:00') return tlocal::i()->noword;
    return tlocal::date(strtotime($date),'d F Y');
  }
  
  public function gettable($head, $body) {
    return admintheme::i()->gettable($head, $body);
  }
  
  public function tablestruct(array $tablestruct, $args = false) {
    $head = '';
    $body = '<tr>';
    
    foreach ($tablestruct as $index => $item) {
      if (!$item || !count($item)) continue;
      
      if (count($item) == 2) {
        array_unshift($item, 'left');
      }
      
      $colclass = tablebuilder::getcolclass($item[0]);
      $head .= sprintf('<th class="%s">%s</th>', $colclass, $item[1]);
      
      if (is_string($item[2])) {
        $body .= sprintf('<td class="%s">%s</td>', $colclass, $item[2]);
      } else if ($args) {
        $callback_name = 'callback' . $index;
      $args->{$callback_name} = $item[2];
        $body .= sprintf('<td class="%s">$%s</td>', $colclass, $callback_name);
      } else {
        // special case for callback. Add new prop to template vars
        $tableprop =         ttheme::$vars['tableprop'] = tableprop::i();
        $body .= sprintf('<td class="%s">$tableprop.%s</td>', $colclass, $tableprop->addprop($item[2]));
      }
    }
    
    $body .= '</tr>';
    
    return array($head, $body, $args);
  }
  
  public function buildtable(array $items, array $tablestruct) {
    $tb = new tablebuilder();
    $tb->setstruct($tablestruct);
    return $db->build($items);
  }
  
  public function getitemscount($from, $to, $count) {
    return sprintf($this->h4->itemscount, $from, $to, $count);
  }
  
  public function tablevalues(array $a) {
    $body = '';
    foreach ($a as $k => $v) {
      if (is_array($v)) {
        foreach ($v as $kv => $vv) {
          $body .= sprintf('<tr><td>%s</td><td>%s</td></tr>', $kv, $vv);
        }
      } else {
        $body .= sprintf('<tr><td>%s</td><td>%s</td></tr>', $k, $v);
      }
    }
    
    $lang = tlocal::i();
    return $this->gettable("<th>$lang->name</th> <th>$lang->value</th>", $body);
  }
  
  public function singlerow(array $a) {
    $head = '';
    $body = '<tr>';
    foreach ($a as $k => $v) {
      $head .= sprintf('<th>%s</th>', $k);
      $body .= sprintf('<td>%s</td>', $v);
    }
    $body .= '</tr>';
    
    return $this->gettable($head, $body);
  }
  
  public function proplist($tml, array $props) {
    $result = '';
    if (!$tml) $tml = '<li>%s: %s</li>';
    // exclude props with int keys
    $tml_int = '<li>%s</li>';
    
    foreach ($props as $prop => $value) {
      if ($value === false) continue;
      if (is_array($value)) {
        $value = $this->proplist($tml, $value);
      }
      
      if (is_int($prop)) {
        $result .= sprintf($tml_int, $value);
      } else {
        $result .= sprintf($tml, $prop, $value);
      }
    }
    
    return $result ? sprintf('<ul>%s</ul>', $result) : '';
  }
  
  public function linkproplist(array $props) {
    return $this->proplist('<li><a href="' . litepublisher::$site->url . '%s">%s</a></li>', $props);
  }
  
  public function confirmdelete($id, $adminurl, $mesg) {
    $args = new targs();
    $args->id = $id;
    $args->action = 'delete';
    $args->adminurl = $adminurl;
    $args->confirm = $mesg;
    
    $admin = admintheme::i();
    return $this->parsearg($admin->templates['confirmform'], $args);
  }
  
  public function confirm_delete($owner, $adminurl) {
    $id = (int) self::getparam('id', 0);
    if (!$owner->itemexists($id)) return $this->h4red->notfound;
    if  (isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1)) {
      $owner->delete($id);
      return $this->h4->successdeleted;
    } else {
      $args = new targs();
      $args->id = $id;
      $args->adminurl = $adminurl;
      $args->action = 'delete';
      $args->confirm = tlocal::i()->confirmdelete;
      
      $admin = admintheme::i();
      return $this->parsearg($admin->templates['confirmform'], $args);
    }
  }
  
  public static function check2array($prefix) {
    $result = array();
    foreach ($_POST as $key => $value) {
      if (strbegin($key, $prefix)) {
        $result[] = is_numeric($value) ? (int) $value : $value;
      }
    }
    return $result;
  }
  
  public function inidir($dir) {
    $filename = $dir . 'html.ini';
    if (!isset(inifiles::$files[$filename])) {
      $html_ini = inifiles::cache($filename);
      if (is_array($html_ini)) {
        $this->ini = $html_ini + $this->ini;
        $keys = array_keys($html_ini);
        $this->section = array_shift($keys);
        $this->searchsect[] = $this->section;
      }
    }
    
    tlocal::inicache($dir . litepublisher::$options->language . '.admin.ini');
    return $this;
  }
  
  public function iniplugin($class) {
    return $this->inidir(litepublisher::$classes->getresourcedir($class));
  }
  
}//class

//html.adminform.class.php
class adminform {
  public $args;
  public$title;
  public $before;
  public $body;
  //items deprecated
  public $items;
  public $submit;
  public $inline;
  
  //attribs for <form>
  public $action;
  public $method;
  public $enctype;
  public $id;
  public $class;
  public $target;
  
  public function __construct($args = null) {
    $this->args = $args;
    $this->title = '';
    $this->before = '';
    $this->body = '';
    $this->items = &$this->body;
    $this->submit = 'update';
    $this->inline = false;
    
    $this->action = '';
    $this->method = 'post';
    $this->enctype = '';
    $this->id = '';
    $this->class = '';
    $this->target = '';
  }
  
  public function line($content) {
    return str_replace('$content', $content, $this->getadmintheme()->templates['inline']);
  }
  
  public function getadmintheme() {
    return admintheme::i();
  }
  
  public function __set($k, $v) {
    switch ($k) {
      case 'upload':
      if ($v) {
        $this->enctype = 'multipart/form-data';
        $this->submit = 'upload';
      } else {
        $this->enctype = '';
        $this->submit = 'update';
      }
      break;
    }
  }
  
  public function centergroup($buttons) {
    return str_replace('$buttons', $buttons, $this->getadmintheme()->templates['centergroup']);
  }
  
  public function hidden($name, $value) {
    return sprintf('<input type="hidden" name="%s" value="%s" />', $name, $value);
  }
  
  public function getdelete($table) {
    $this->body = $table;
    $this->body .= $this->hidden('delete', 'delete');
    $this->submit = 'delete';
    
    return $this->get();
  }
  
  public function __tostring() {
    return $this->get();
  }
  
  public function gettml() {
    $title = $this->title ? str_replace('$title', $this->title, $this->getadmintheme()->templates['form.title']) : '';
    
    $attr = "action=\"$this->action\"";
    foreach (array('method', 'enctype', 'target', 'id', 'class') as $k) {
      if ($v = $this->$k) $attr .= sprintf(' %s="%s"', $k, $v);
    }
    
    if ($this->inline) {
      $body = $this->line($this->body . ($this->submit ? "[button=$this->submit]" : ''));
    } else {
      $body = $this->body;
      if ($this->submit) {
        $body .= "[submit=$this->submit]";
      }
    }
    
    return strtr($this->getadmintheme()->templates['form'], array(
    '$title' => $title,
    '$before' => $this->before,
    'attr' => $attr,
    '$body' => $body,
    ));
  }
  
  public function get() {
    return tadminhtml::i()->parsearg($this->gettml(), $this->args);
  }
  
}//class

//html.tableprop.class.php
class tableprop {
  public $callbacks;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function __construct() {
    $this->callbacks = array();
  }
  
  public function addprop($callback) {
    $this->callbacks[] = $callback;
    $id = count($this->callbacks) -  1;
    return 'prop' . $id;
  }
  
  public function __get($name) {
    $id = (int) substr($name, strlen('prop'));
    return call_user_func_array($this->callbacks[$id], array(ttheme::$vars['item']));
  }
  
}

//html.uitabs.class.php
class tuitabs {
  public $head;
  public $body;
  public $tabs;
  public $customdata;
  private static $index = 0;
  private $tabindex;
  private $items;
  
  public function __construct() {
    $this->tabindex = ++self::$index;
    $this->items = array();
    $this->head = '<li><a href="%s" role="tab"><span>%s</span></a></li>';
    $this->body = '<div id="tab-' . self::$index . '-%d" role="tabpanel">%s</div>';
    $this->tabs = '<div id="tabs-' . self::$index . '" class="admintabs" %s>
    <ul role="tablist">%s</ul>
    %s
    </div>';
    $this->customdata = false;
  }
  
  public function get() {
    $head= '';
    $body = '';
    foreach ($this->items as $i => $item) {
      if (isset($item['url'])) {
        $head .= sprintf($this->head, $item['url'], $item['title']);
      } else {
        $head .= sprintf($this->head, "#tab-$this->tabindex-$i", $item['title']);
        $body .= sprintf($this->body, $i, $item['body']);
      }
    }
    
    $data = $this->customdata? sprintf('data-custom="%s"', str_replace('"', '&quot;', json_encode($this->customdata))) : '';
    return sprintf($this->tabs, $data, $head, $body);
  }
  
  public function add($title, $body) {
    $this->items[] = array(
    'title' => $title,
    'body' => $body
    );
  }
  
  public function ajax($title, $url) {
    $this->items[] = array(
    'url' => $url,
    'title' => $title,
    );
  }
  
  public static function gethead() {
    return '<script type="text/javascript">$.inittabs();</script>';
  }
  
}//class

//html.tag.class.php
class thtmltag {
  public $tag;
  
public function __construct($tag = '') { $this->tag = $tag; }
  public function __get($name) {
    return sprintf('<%1$s>%2$s</%1$s>', $this->tag, tlocal::i()->$name);
  }
  
}//class

class redtag extends thtmltag {
  
  public function __get($name) {
    return sprintf('<%1$s class="red">%2$s</%1$s>', $this->tag, tlocal::i()->$name);
  }
  
}//class

//html.autoform.class.php
class tautoform {
  const editor = 'editor';
  const text = 'text';
  const checkbox = 'checkbox';
  const hidden = 'hidden';
  
  public $obj;
  public $props;
  public $section;
  public $_title;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function __construct(tdata $obj, $section, $titleindex) {
    $this->obj = $obj;
    $this->section = $section;
    $this->props = array();
    $lang = tlocal::i($section);
    $this->_title = $lang->$titleindex;
  }
  
  public function __set($name, $value) {
    $this->props[] = array(
    'obj' => $this->obj,
    'propname' => $name,
    'type' => $value
    );
  }
  
  public function __get($name) {
    if (isset($this->obj->$name)) {
      return array(
      'obj' => $this->obj,
      'propname' => $name
      );
    }
    //tlogsubsystem::error(sprintf('The property %s not found in class %s', $name, get_class($this->obj));
  }
  
  public function __call($name, $args) {
    if (isset($this->obj->$name)) {
      $result = array(
      'obj' => $this->obj,
      'propname' => $name,
      'type' => $args[0]
      );
      if (($result['type'] == 'combo') && isset($args[1]))  $result['items'] = $args[1];
      return $result;
    }
  }
  
  public function add() {
    $a = func_get_args();
    foreach ($a as $prop) {
      $this->addprop($prop);
    }
  }
  
  public function addsingle($obj, $propname, $type) {
    return $this->addprop(array(
    'obj' => $obj,
    'propname' => $propname,
    'type' => $type
    ));
  }
  
  public function addeditor($obj, $propname) {
    return $this->addsingle($obj, $propname, 'editor');
  }
  
  public function addprop(array $prop) {
    if (isset($prop['type'])) {
      $type = $prop['type'];
    } else {
      $type = 'text';
    $value = $prop['obj']->{$prop['propname']};
      if (is_bool($value)) {
        $type = 'checkbox';
      } elseif(strpos($value, "\n")) {
        $type = 'editor';
      }
    }
    
    $item = array(
    'obj' => $prop['obj'],
    'propname' => $prop['propname'],
    'type' => $type,
    'title' => isset($prop['title']) ? $prop['title'] : ''
    );
    if (($type == 'combo') && isset($prop['items'])) $item['items'] = $prop['items'];
    $this->props[] = $item;
    return count($this->props) - 1;
  }
  
  public function getcontent() {
    $result = '';
    $lang = tlocal::i();
    $theme = ttheme::i();
    
    foreach ($this->props as $prop) {
    $value = $prop['obj']->{$prop['propname']};
      switch ($prop['type']) {
        case 'text':
        case 'editor':
        $value = tadminhtml::specchars($value);
        break;
        
        case 'checkbox':
        $value = $value ? 'checked="checked"' : '';
        break;
        
        case 'combo':
        $value = tadminhtml  ::array2combo($prop['items'], $value);
        break;
      }
      
      $result .= strtr($theme->templates['content.admin.' . $prop['type']], array(
    '$lang.$name' => empty($prop['title']) ? $lang->{$prop['propname']} : $prop['title'],
      '$name' => $prop['propname'],
      '$value' => $value
      ));
    }
    return $result;
  }
  
  public function getform() {
    $args = new targs();
    $args->formtitle = $this->_title;
    $args->items = $this->getcontent();
    $theme = ttheme::i();
    $tml = str_replace('[submit=update]', str_replace('$name', 'update', $theme->templates['content.admin.submit']), $theme->templates['content.admin.form']);
    return $theme->parsearg($tml, $args);
  }
  
  public function processform() {
    foreach ($this->props as $prop) {
      if (method_exists($prop['obj'], 'lock')) $prop['obj']->lock();
    }
    
    foreach ($this->props as $prop) {
      $name = $prop['propname'];
      if (isset($_POST[$name])) {
        $value = trim($_POST[$name]);
        if ($prop['type'] == 'checkbox') $value = true;
      } else {
        $value = false;
      }
      $prop['obj']->$name = $value;
    }
    
    foreach ($this->props as $prop) {
      if (method_exists($prop['obj'], 'unlock')) $prop['obj']->unlock();
    }
  }
  
}//class

//html.tablebuilder.class.php
class tablebuilder {
  //current item in items
  public $item;
  //id or index of current item
  public $id;
  //template head and body table
  public $head;
  public $body;
  //targs
  public $args;
  public $callbacks;
  
  public function __construct() {
    $this->head = '';
    $this->body = '';
    $this->args = new targs();
    $this->callbacks = array();
  }
  
  public function setstruct(array $struct) {
    $this->body = '<tr>';
    foreach ($struct as $index => $item) {
      if (!$item || !count($item)) continue;
      
      if (count($item) == 2) {
        $colclass = 'text-left';
      } else {
        $colclass = self::getcolclass(array_shift($item));
      }
      
      $this->head .= sprintf('<th class="%s">%s</th>', $colclass, array_shift($item));
      
      $s = array_shift($item);
      if (is_string($s)) {
        $this->body .= sprintf('<td class="%s">%s</td>', $colclass, $s);
      } else if (is_callable($s)) {
        $name = '$callback' . $index;
        $this->body .= sprintf('<td class="%s">$%s</td>', $colclass, $name);
        
        array_unshift($item, $this);
        $this->callbacks[$name] = array(
        'callback'=> $s,
        'params' => $item,
        );
      } else {
        throw new Exception('Unknown column ' . var_export($s, true));
      }
    }
    
    $this->body .= '</tr>';
  }
  
  public function addcallback($varname, $callback, $param) {
    $this->callbacks[$varname] = array(
    'callback'=> $callback,
    'params' => array($this, $param),
    );
  }
  
  public function build(array $items) {
    $body = '';
    $args = $this->args;
    $admintheme = admintheme::i();
    
    foreach ($items as $id => $item) {
      if (is_array($item)) {
        $this->item = $item;
        $args->add($item);
        if (!isset($item['id'])) {
          $this->id = $id;
          $args->id = $id;
        }
      } else {
        $this->id = $item;
        $args->id = $item;
      }
      
      foreach ($this->callbacks as $name => $callback) {
        $args->data[$name] = call_user_func_array($callback['callback'], $callback['params']);
      }
      
      $body .= $admintheme->parsearg($this->body, $args);
    }
    
    return $admintheme->gettable($this->head, $body);
  }
  
  //predefined callbacks
  public function titems_callback(tablebuilder $self, titems $owner) {
    $self->item = $owner->getitem($self->id);
    $self->args->add($self->item);
  }
  
  public function setowner(titems $owner) {
    $this->addcallback('$tempcallback' . count($this->callbacks), array($this, 'titems_callback'), $owner);
  }
  
  public function posts_callback(tablebuilder $self) {
    $post = tpost::i($self->id);
    basetheme::$vars['post'] = $post;
    $self->args->poststatus = tlocal::i()->__get($post->status);
  }
  
  public function setposts(array $struct) {
    array_unshift($struct, self::checkbox('checkbox'));
    $this->setstruct($struct);
    $this->addcallback('$tempcallback' . count($this->callbacks), array($this, 'posts_callback'), false);
  }
  
  public function props(array $props) {
    $lang = tlocal::i();
    $this->setstruct(array(
    array(
    $lang->name,
    '$name'
    ),
    
    array(
    $lang->property,
    '$value'
    )
    ));
    
    $body = '';
    $args = $this->args;
    $admintheme = admintheme::i();
    
    foreach ($props as $k => $v) {
      if (($k === false) || ($v === false)) continue;
      
      if (is_array($v)) {
        foreach ($v as $kv => $vv) {
          if ($k2 = $lang->__get($kv)) $kv = $k2;
          $args->name = $kv;
          $args->value = $vv;
          $body .= $admintheme->parsearg($this->body, $args);
        }
      } else {
        if ($k2 = $lang->__get($k)) $k = $k2;
        $args->name = $k;
        $args->value = $v;
        $body .= $admintheme->parsearg($this->body, $args);
      }
    }
    
    return $admintheme->gettable($this->head, $body);
  }
  
  public function action($action, $adminurl) {
    $title = tlocal::i()->__get($action);
    
    return array(
    $title,
    "<a href=\"$adminurl=\$id&action=$action\">$title</a>"
    );
  }
  
  public static function checkbox($name) {
    $admin = admintheme::i();
    
    return array(
    'text-center col-checkbox',
    $admin->templates['invertcheck'],
    str_replace('$name', $name, $admin->templates['checkbox'])
    );
  }
  
  public static function getcolclass($s) {
    //most case
    if (!$s || $s == 'left') {
      return 'text-left';
    }
    
    $map = array(
    'left' => 'text-left',
    'right' => 'text-right',
    'center' => 'text-center'
    );
    
    $list = explode(' ', $s);
    foreach ($list as $i => $v) {
      if (isset($map[$v])) {
        $list[$i] = $map[$v];
      }
    }
    
    return implode(' ', $list);
  }
  
}

//filter.datetime.class.php
// namespace litepubl\admin;

class datefilter {
  //only date without time
  public static $format = 'd.m.Y';
  public static $timeformat = 'H:i';
  
  public static function timestamp($date) {
    if (is_numeric($date)) {
      $date = (int) $date;
    } else if ($date == '0000-00-00 00:00:00') {
      $date = 0;
    } elseif ($date == '0000-00-00') {
      $date = 0;
    } elseif ($date = trim($date)) {
      $date = strtotime($date);
    } else {
      $date = 0;
    }
    
    return $date;
  }
  
  public static function getdate($name, $format = false) {
    if (empty($_POST[$name])) return 0;
    $date = trim($_POST[$name]);
    if (!$date) return 0;
    
    if (version_compare(PHP_VERSION, '5.3', '>=')) {
      if (!$format) $format = self::$format;
      $d = DateTime::createFromFormat($format, $date);
      if ($d && $d->format($format) == $date) {
        $d->setTime(0, 0, 0);
        return $d->getTimestamp() + self::gettime($name . '-time');
      }
    } else {
      if (@sscanf($date, '%d.%d.%d', $d, $m, $y)) {
        return mktime(0, 0, 0, $m, $d, $y) + self::gettime($name . '-time');
      }
    }
    
    return 0;
  }
  
  public static function gettime($name) {
    $result = 0;
    if (!empty($_POST[$name] && ($time = trim($_POST[$name])))) {
      if (preg_match('/^([01]?[0-9]|2[0-3]):([0-5][0-9])(:([0-5][0-9]))?$/', $time, $m)) {
        $result = intval($m[1]) * 3600 + intval($m[2]) * 60;
        if (isset($m[4])) {
          $result += (int) $m[4];
        }
      }
    }
    
    return $result;
  }
  
}//class

//admin.posteditor.ajax.class.php
class tajaxposteditor  extends tevents {
  public $idpost;
  private $isauthor;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'ajaxposteditor';
    $this->addevents('onhead', 'oneditor');
    $this->data['head'] = '';
    $this->data['visual'] = '';
    //'/plugins/tiny_mce/init.js';
    //'/plugins/ckeditor/init.js';
    $this->data['ajaxvisual'] = true;
  }
  
  public function setvisual($url) {
    if ($url == $this->visual) return;
    $js = tjsmerger::i();
    $js->lock();
    $js->deletefile('posteditor', $this->visual);
    $js->deletetext('posteditor', 'visual');
    
    if ($url) {
      if ($this->ajaxvisual) {
        $js->addtext('posteditor', 'visual', sprintf(
        '$(document).ready(function() {
          litepubl.posteditor.init_visual_link("%s", %s);
        });', litepublisher::$site->files . $url, json_encode(tlocal::get('editor', 'loadvisual')))
        );
      }else {
        $js->add('posteditor', $url);
      }
    }
    
    $js->unlock();
    
    $this->data['visual'] = $url;
    $this->save();
  }
  
  public function gethead() {
    $result = $this->data['head'];
    if ($this->visual) {
      $template = ttemplate::i();
      if ($this->ajaxvisual) {
        $result .= $template->getready('$("a[rel~=\'loadvisual\']").one("click", function() {
          $("#loadvisual").remove();
          $.load_script("' . litepublisher::$site->files . $this->visual . '");
          return false;
        });');
      } else {
        $result .= $template->getjavascript($this->visual);
      }
    }
    
    $this->callevent('onhead', array(&$result));
    return $result;
  }
  
  protected static function error403() {
    return '<?php header(\'HTTP/1.1 403 Forbidden\', true, 403); ?>' . turlmap::htmlheader(false) . 'Forbidden';
  }
  
  public function getviewicon($idview, $icon) {
    $result = tadminviews::getcomboview($idview);
    if ($icons = tadminicons::getradio($icon)) {
      $html = tadminhtml ::i();
      if ($html->section == '') $html->section = 'editor';
      $result .= $html->h2->icons;
      $result .= $icons;
    }
    return $result;
  }
  
  public static function auth() {
    $options = litepublisher::$options;
    if (!$options->user) return self::error403();
    if (!$options->hasgroup('editor')) {
      if (!$options->hasgroup('author')) return self::error403();
    }
  }
  
  public function request($arg) {
    $this->cache = false;
    turlmap::sendheader(false);
    
    if ($err = self::auth()) return $err;
    $this->idpost = tadminhtml::idparam();
    $this->isauthor = litepublisher::$options->ingroup('author');
    if ($this->idpost > 0) {
      $posts = tposts::i();
      if (!$posts->itemexists($this->idpost)) return self::error403();
      if (!litepublisher::$options->hasgroup('editor')) {
        if (litepublisher::$options->hasgroup('author')) {
          $this->isauthor = true;
          $post = tpost::i($this->idpost);
          if (litepublisher::$options->user != $post->author) return self::error403();
        }
      }
    }
    
    return $this->getcontent();
  }
  
  public function getcontent() {
    $theme = tview::i(tviews::i()->defaults['admin'])->theme;
    $html = tadminhtml ::i();
    $html->section = 'editor';
    $lang = tlocal::i('editor');
    $post = tpost::i($this->idpost);
    ttheme::$vars['post'] = $post;
    
    switch ($_GET['get']) {
      case 'tags':
      $result = $html->getedit('tags', $post->tagnames, $lang->tags);
      $lang->section = 'editor';
      $result .= $html->h4->addtags;
      $items = array();
      $tags = $post->factory->tags;
      $list = $tags->getsorted(-1, 'name', 0);
      foreach ($list as $id ) {
        $items[] = '<a href="" class="posteditor-tag">' . $tags->items[$id]['title'] . "</a>";
      }
      $result .= sprintf('<p>%s</p>', implode(', ', $items));
      break;
      
      case 'status':
      $args = new targs();
      $args->comstatus= tadminhtml::array2combo(array(
      'closed' => $lang->closed,
      'reg' => $lang->reg,
      'guest' => $lang->guest,
      'comuser' => $lang->comuser
      ), $post->comstatus);
      
      
      $args->pingenabled = $post->pingenabled;
      $args->status= tadminhtml::array2combo(array(
      'published' => $lang->published,
      'draft' => $lang->draft
      ), $post->status);
      
      $args->perms = tadminperms::getcombo($post->idperm);
      $args->password = $post->password;
      $result = $html->parsearg(
      '[combo=comstatus]
      [checkbox=pingenabled]
      [combo=status]
      $perms
      [password=password]
      <p>$lang.notepassword</p>',
      $args);
      
      break;
      
      case 'view':
      $result = $this->getviewicon($post->idview, $post->icon);
      break;
      
      default:
      $result = var_export($_GET, true);
    }
    //tfiler::log($result);
    return turlmap::htmlheader(false) . $result;
  }
  
  public function geteditor($name, $value, $visual) {
    $theme = tview::i(tviews::i()->defaults['admin'])->theme;
    $html = tadminhtml ::i();
    $html->push_section('editor');
    $lang = tlocal::i();
    $title = $lang->$name;
    if ($visual && $this->ajaxvisual && $this->visual) {
      $title .= $html->loadvisual();
    }
    
    $result = $theme->getinput('editor', $name, tadminhtml::specchars($value), $title);
    $html->pop_section();
    return $result;
  }
  
}//class

//admin.posteditor.class.php
class tposteditor extends tadminmenu {
  public $idpost;
  protected $isauthor;
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethtml($name = '') {
    if (!$name) $name = 'editor';
    return parent::gethtml($name);
  }
  
  public function gethead() {
    $result = parent::gethead();
    
    $template = ttemplate::i();
    $template->ltoptions['idpost'] = $this->idget();
    $result .= $template->getjavascript($template->jsmerger_posteditor);
    
    if ($this->isauthor &&($h = tauthor_rights::i()->gethead()))  $result .= $h;
    return $result;
  }
  
  protected static function getsubcategories($parent, array $postitems, $exclude = false) {
    $result = '';
    $categories = tcategories::i();
    $html = tadminhtml::getinstance('editor');
    $theme = ttheme::i();
    $checkbox = $theme->getinput('checkbox', 'category-$id', 'value="$id" $checked', '$title');
    $tml = str_replace('$checkbox', $checkbox, $html->category);
    
    $args = new targs();
    foreach ($categories->items  as $id => $item) {
      if ($parent != $item['parent']) continue;
      if ($exclude && in_array($id, $exclude)) continue;
      $args->add($item);
      $args->checked = in_array($item['id'], $postitems);
      $args->subcount = '';
      $args->subitems = self::getsubcategories($id, $postitems);
      $result .= $html->parsearg($tml, $args);
    }
    
    if ($result == '') return '';
    return sprintf($html->categories(), $result);
  }
  
  public static function getcategories(array $items) {
    $categories = tcategories::i();
    $categories->loadall();
    $html = tadminhtml::i();
    $html->push_section('editor');
    $result = $html->categorieshead();
    $result .= self::getsubcategories(0, $items);
    $html->pop_section();
    return str_replace("'", '"', $result);
  }
  
  public static function getcombocategories(array $items, $idselected) {
    $result = '';
    $categories = tcategories::i();
    $categories->loadall();
    if (count($items) == 0) $items = array_keys($categories->items);
    foreach ($items as $id) {
      $result .= sprintf('<option value="%s" %s>%s</option>', $id, $id == $idselected ? 'selected' : '', tadminhtml::specchars($categories->getvalue($id, 'title')));
    }
    return $result;
  }
  
  protected function getpostcategories(tpost $post) {
    $postitems = $post->categories;
    $categories = tcategories::i();
    if (count($postitems) == 0) $postitems = array($categories->defaultid);
    return self::getcategories($postitems);
  }
  
  public static function getfileperm() {
    return litepublisher::$options->show_file_perm ? tadminperms::getcombo(0, 'idperm_upload') : '';
  }
  
  // $posteditor.files in template editor
  public function getfilelist() {
    $post = ttheme::$vars['post'];
    if (version_compare(PHP_VERSION, '5.3', '>=')) {
      return static::getuploader($post->id ? tfiles::i()->itemsposts->getitems($post->id) : array());
    } else {
      return self::getuploader($post->id ? tfiles::i()->itemsposts->getitems($post->id) : array());
    }
  }
  
  public static function getuploader(array $list) {
    $html = tadminhtml::i();
    $html->push_section('editor');
    $args = new targs();
    if (version_compare(PHP_VERSION, '5.3', '>=')) {
      $args->fileperm = static::getfileperm();
    } else {
      $args->fileperm = self::getfileperm();
    }
    
    $files = tfiles::i();
    $where = litepublisher::$options->ingroup('editor') ? '' : ' and author = ' . litepublisher::$options->user;
    
    $db = $files->db;
    //total count files
    $args->count = (int) $db->getcount(" parent = 0 $where");
    //already loaded files
    $args->items= '{' .
    '}';
    // attrib for hidden input
    $args->files = '';
    
    if (count($list)) {
      $items = implode(',', $list);
      $args->files = $items;
      $args->items = tojson($db->res2items($db->query("select * from $files->thistable where id in ($items) or parent in ($items)")));
    }
    
    $result = $html->filelist($args);
    $html->pop_section();
    return $result;
  }
  
  public function canrequest() {
    tlocal::admin()->searchsect[] = 'editor';
    $this->isauthor = false;
    $this->basename = 'editor';
    $this->idpost = $this->idget();
    if ($this->idpost > 0) {
      $posts = tposts::i();
      if (!$posts->itemexists($this->idpost)) return 404;
    }
    $post = tpost::i($this->idpost);
    if (!litepublisher::$options->hasgroup('editor')) {
      if (litepublisher::$options->hasgroup('author')) {
        $this->isauthor = true;
        if (($post->id != 0) && (litepublisher::$options->user != $post->author)) return 403;
      }
    }
  }
  
  public function gettitle() {
    if ($this->idpost == 0){
      return parent::gettitle();
    } else {
      if (isset(tlocal::admin()->ini[$this->name]['editor'])) return tlocal::get($this->name, 'editor');
      return tlocal::get('editor', 'editor');
    }
  }
  
  public function getexternal() {
    $this->basename = 'editor';
    $this->idpost = 0;
    return $this->getcontent();
  }
  
  public function getpostargs(tpost $post, targs $args) {
    $args->id = $post->id;
    $args->ajax = tadminhtml::getadminlink('/admin/ajaxposteditor.htm', "id=$post->id&get");
    $args->title = tcontentfilter::unescape($post->title);
    $args->categories = $this->getpostcategories($post);
    $args->date = $post->posted;
    $args->url = $post->url;
    $args->title2 = $post->title2;
    $args->keywords = $post->keywords;
    $args->description = $post->description;
    $args->head = $post->rawhead;
    
    $args->raw = $post->rawcontent;
    $args->filtered = $post->filtered;
    $args->excerpt = $post->excerpt;
    $args->rss = $post->rss;
    $args->more = $post->moretitle;
    $args->upd = '';
  }
  
  public function getcontent() {
    $html = $this->html;
    $post = tpost::i($this->idpost);
    ttheme::$vars['post'] = $post;
    ttheme::$vars['posteditor'] = $this;
    $args = new targs();
    $this->getpostargs($post, $args);
    
    $result = $post->id == 0 ? '' : $html->h4($this->lang->formhead . ' ' . $post->bookmark);
    if ($this->isauthor &&($r = tauthor_rights::i()->getposteditor($post, $args)))  return $r;
    
    $result .= $html->form($args);
    unset(ttheme::$vars['post'], ttheme::$vars['posteditor']);
    return $html->fixquote($result);
  }
  
  public static function processcategories() {
    return tadminhtml::check2array('category-');
  }
  
  protected function set_post(tpost $post) {
    extract($_POST, EXTR_SKIP);
    $post->title = $title;
    
    $cats = self::processcategories();
    $cats = array_unique($cats);
    array_delete_value($cats, 0);
    array_delete_value($cats, '');
    array_delete_value($cats, false);
    array_delete_value($cats, null);
    $post->categories= $cats;
    
    if (($post->id == 0) && (litepublisher::$options->user >1)) $post->author = litepublisher::$options->user;
    if (isset($tags)) $post->tagnames = $tags;
    if (isset($icon)) $post->icon = (int) $icon;
    if (isset($idview)) $post->idview = $idview;
    if (isset($files))  {
      $files = trim($files, ', ');
      $post->files = tdatabase::str2array($files);
    }
    if (isset($date) && $date) {
      $post->posted = datefilter::getdate('date');
    }
    
    if (isset($status)) {
      $post->status = $status == 'draft' ? 'draft' : 'published';
      $post->comstatus = $comstatus;
      $post->pingenabled = isset($pingenabled);
      $post->idperm = (int) $idperm;
      if ($password != '') $post->password = $password;
    }
    
    if (isset($url)) {
      $post->url = $url;
      $post->title2 = $title2;
      $post->keywords = $keywords;
      $post->description = $description;
      $post->rawhead = $head;
    }
    
    $post->content = $raw;
    if (isset($excerpt)) $post->excerpt = $excerpt;
    if (isset($rss)) $post->rss = $rss;
    if (isset($more)) $post->moretitle = $more;
    if (isset($filtered)) $post->filtered = $filtered;
    if (isset($upd)) {
      $update = sprintf($this->lang->updateformat, tlocal::date(time()), $upd);
      $post->content = $post->rawcontent . "\n\n" . $update;
    }
    
  }
  
  public function processform() {
    //dumpvar($_POST);
    $this->basename = 'editor';
    $html = $this->html;
    if (empty($_POST['title'])) return $html->h2->emptytitle;
    $id = (int)$_POST['id'];
    $post = tpost::i($id);
    
    if ($this->isauthor &&($r = tauthor_rights::i()->editpost($post)))  {
      $this->idpost = $post->id;
      return $r;
    }
    
    $this->set_post($post);
    $posts = tposts::i();
    if ($id == 0) {
      $this->idpost = $posts->add($post);
      $_POST['id'] = $this->idpost;
    } else {
      $posts->edit($post);
    }
    $_GET['id'] = $this->idpost;
    return sprintf($html->p->success,$post->bookmark);
  }
  
}//class

