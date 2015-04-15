<?php
//menu.admin.class.php
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

class tauthor_rights extends tevents {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->addevents('gethead', 'getposteditor', 'editpost', 'changeposts', 'canupload', 'candeletefile');
    $this->basename = 'authorrights';
  }
  
}//class

//htmlresource.class.php
class thtmltag {
  public $tag;
  
public function __construct($tag) { $this->tag = $tag; }
  public function __get($name) {
    return sprintf('<%1$s>%2$s</%1$s>', $this->tag, tlocal::i()->$name);
  }
  
}//class

class redtag extends thtmltag {
  
  public function __get($name) {
    return sprintf('<%1$s class="red">%2$s</%1$s>', $this->tag, tlocal::i()->$name);
  }
  
}//class

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
          $tag = $this->getcalendar($name, $args->data[$varname]);
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
  
  public function inline($s) {
    return sprintf($this->ini['common']['inline'], $s);
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
      '$checked' => $value == $selected ? 'checked="checked"' : '',
      '$name' => $name,
      '$value' => self::specchars($value)
      ));
    }
    return $result;
  }
  
  public function getsubmit() {
    $result = '';
    $a = func_get_args();
    foreach ($a as $name) {
      $result .= strtr(ttheme::i()->templates['content.admin.button'], array(
      '$lang.$name' => tlocal::i()->__get($name),
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
  
  public function cleandate($date) {
    if (is_numeric($date)) {
      $date = intval($date);
    } else if ($date == '0000-00-00 00:00:00') {
      $date = 0;
    } elseif ($date == '0000-00-00') {
      $date = 0;
    } elseif (trim($date)) {
      $date = strtotime($date);
    } else {
      $date = 0;
    }
    
    return $date;
  }
  
  public function getcalendar($name, $date) {
    $date = $this->cleandate($date);
    $lang = tlocal::i();
    $controls = $this->getinput('text', $name, $date? date('d.m.Y', $date) : '', $lang->date);
    $controls .= $this->getinput('text', "$name-time", $date ?date('H:i', $date) : '', $lang->time);
    $controls .= str_replace('type="submit"', 'type="button"',
    $this->getinput('button', "calendar-$name", '', $lang->calendar));
    
    return sprintf($this->ini['common']['calendar'], $lang->__get($name), $this->inline($controls));
  }
  
  public function getdaterange($from, $to) {
    $from = $this->cleandate($from);
    $to = $this->cleandate($to);
    $lang = tlocal::i();
    $controls = $this->getinput('text', 'from', $from ? date('d.m.Y', $from) : '', $lang->from);
    $controls .= str_replace('type="submit"', 'type="button"',
    $this->getinput('button', "calendar-from", '', $lang->calendar));
    
    $controls .= $this->getinput('text', 'to', $to ? date('d.m.Y', $to) : '', $lang->to);
    $controls .= str_replace('type="submit"', 'type="button"',
    $this->getinput('button', "calendar-to", '', $lang->calendar));
    
    return sprintf($this->ini['common']['daterange'], $controls);
  }
  
  public static function getdatetime($name) {
    if (!empty($_POST[$name]) && @sscanf(trim($_POST[$name]), '%d.%d.%d', $d, $m, $y)) {
      $h = 0;
      $min  = 0;
      if (!empty($_POST[$name . '-time'])) @sscanf(trim($_POST[$name . '-time']), '%d:%d', $h, $min);
      return mktime($h,$min,0, $m, $d, $y);
    }
    
    return 0;
  }
  
  public static function datestr($date) {
    if ($date == '0000-00-00 00:00:00') return tlocal::i()->noword;
    return tlocal::date(strtotime($date),'d F Y');
  }
  
  public function gettable($head, $body) {
    return strtr($this->ini['common']['table'], array(
    '$tableclass' => ttheme::i()->templates['content.admin.tableclass'],
    '$tablehead' => $head,
    '$tablebody' => $body));
  }
  
  public function tablestruct(array $tablestruct) {
    $head = '';
    $body = '<tr>';
    foreach ($tablestruct as $item) {
      if (!$item || !count($item)) continue;
      $align = $item[0] ? $item[0] : 'left';
      $head .= sprintf('<th align="%s">%s</th>', $align, $item[1]);
      if (is_string($item[2])) {
        $body .= sprintf('<td align="%s">%s</td>', $align, $item[2]);
      } else {
        // special case for callback. Add new prop to template vars
        $tableprop = tableprop::i();
        $propname = $tableprop->addprop($item[2]);
        ttheme::$vars['tableprop'] = $tableprop;
        $body .= sprintf('<td align="%s">$tableprop.%s</td>', $item[0], $propname);
      }
    }
    
    $body .= '</tr>';
    return array($head, $body);
  }
  
  public function buildtable(array $items, array $tablestruct) {
    $body = '';
    list($head, $tml) = $this->tablestruct($tablestruct);
    $theme = ttheme::i();
    $args = new targs();
    foreach ($items as $id => $item) {
      ttheme::$vars['item'] = $item;
      $args->add($item);
      if (!isset($item['id'])) $args->id = $id;
      $body .= $theme->parsearg($tml, $args);
    }
    unset(ttheme::$vars['item']);
    
    $args->tablehead  = $head;
    $args->tablebody = $body;
    return $theme->parsearg($this->ini['common']['table'], $args);
  }
  
  public function items2table($owner, array $items, array $struct) {
    $head = '';
    $body = '';
    $tml = '<tr>';
    foreach ($struct as $elem) {
      $head .= sprintf('<th align="%s">%s</th>', $elem[0], $elem[1]);
      $tml .= sprintf('<td align="%s">%s</td>', $elem[0], $elem[2]);
    }
    $tml .= '</tr>';
    
    $theme = ttheme::i();
    $args = new targs();
    foreach ($items as $id) {
      $item = $owner->getitem($id);
      $args->add($item);
      $args->id = $id;
      $body .= $theme->parsearg($tml, $args);
    }
    $args->tablehead  = $head;
    $args->tablebody = $body;
    return $theme->parsearg($this->ini['common']['table'], $args);
  }
  
  public function tableposts(array $items, array $tablestruct) {
    $body = '';
    $head = $this->tableposts_head;
    $tml = $this->tableposts_item;
    foreach ($tablestruct as $item) {
      if (!$item || !count($item)) continue;
      $align = $item[0] ? $item[0] : 'left';
      $head .= sprintf('<th align="%s">%s</th>', $align, $item[1]);
      if (is_string($item[2])) {
        $tml .= sprintf('<td align="%s">%s</td>', $align, $item[2]);
      } else {
        // special case for callback. Add new prop to template vars
        $tableprop = tableprop::i();
        $propname = $tableprop->addprop($item[2]);
        ttheme::$vars['tableprop'] = $tableprop;
        $tml .= sprintf('<td align="%s">$tableprop.%s</td>', $item[0], $propname);
      }
    }
    
    $tml .= '</tr>';
    
    $theme = ttheme::i();
    $args = new targs();
    foreach ($items as $id) {
      $post = tpost::i($id);
      ttheme::$vars['post'] = $post;
      $args->id = $id;
      $body .= $theme->parsearg($tml, $args);
    }
    
    $args->tablehead  = $head;
    $args->tablebody = $body;
    return $theme->parsearg($this->ini['common']['table'], $args);
  }
  
  public function getitemscount($from, $to, $count) {
    return sprintf($this->h4->itemscount, $from, $to, $count);
  }
  
  public function get_table_checkbox($name) {
    return array('center', $this->invertcheckbox, str_replace('$checkboxname', $name, $this->checkbox));
  }
  
  public function get_table_item($name) {
    return array('left', tlocal::i()->$name, "\$$name");
  }
  
  public function get_table_link($action, $adminurl) {
    return array('left', tlocal::i()->$action, strtr($this->actionlink , array(
    '$action' => $action,
    '$lang.action' => tlocal::i()->$action,
    '$adminurl' => $adminurl
    )));
  }
  
  public function tableprops($item) {
    $body = '';
    $lang = tlocal::i();
    foreach ($item as $k => $v) {
      if (($k === false) || ($v === false)) continue;
      
      if (is_array($v)) {
        foreach ($v as $kv => $vv) {
          if ($k2 = $lang->__get($kv)) $kv = $k2;
          $body .= sprintf('<tr><td>%s</td><td>%s</td></tr>', $kv, $vv);
        }
      } else {
        if ($k2 = $lang->__get($k)) $k = $k2;
        $body .= sprintf('<tr><td>%s</td><td>%s</td></tr>', $k, $v);
      }
    }
    
    return $this->gettable("<th>$lang->name</th> <th>$lang->property</th>", $body);
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
  
  public function confirmdelete($id, $adminurl, $mesg) {
    $args = new targs();
    $args->id = $id;
    $args->action = 'delete';
    $args->adminurl = $adminurl;
    $args->confirm = $mesg;
    return $this->confirmform($args);
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
      return $this->confirmform($args);
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
  
  public function toggle($title, $target, $second = '') {
    return strtr($this->ini['common']['toggle'], array(
    '$title' => $title,
    '$target' => $target,
    '$second' => $second,
    "'" => '"',
    ));
  }
  
  public function inidir($dir) {
    $filename = $dir . 'html.ini';
    if (!isset(ttheme::$inifiles[$filename])) {
      $html_ini = ttheme::cacheini($filename);
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

class ttablecolumns {
  public $style;
  public $head;
  public $checkboxes;
  public $checkbox_tml;
  public $item;
  public $changed_hidden;
  public $index;
  
  public function __construct() {
    $this->index = 0;
    $this->style = '';
    $this->checkboxes = array();
    $this->checkbox_tml = '<input type="checkbox" name="checkbox-showcolumn-%1$d" value="%1$d" %2$s />
    <label for="checkbox-showcolumn-%1$d"><strong>%3$s</strong></label>';
    $this->head = '';
    $this->body = '';
    $this->changed_hidden = 'changed_hidden';
  }
  
  public function addcolumns(array $columns) {
    foreach ($columns as $column) {
      list($tml, $title, $align, $show) = $column;
      $this->add($tml, $title, $align, $show);
    }
  }
  
  public function add($tml, $title, $align, $show) {
    $class = 'col_' . ++$this->index;
    //if (isset($_POST[$this->changed_hidden])) $show  = isset($_POST["checkbox-showcolumn-$this->index"]);
    $display = $show ? 'block' : 'none';
  $this->style .= ".$class { text-align: $align; display: $display; }\n";
    $this->checkboxes[]=  sprintf($this->checkbox_tml, $this->index, $show ? 'checked="checked"' : '', $title);
    $this->head .= sprintf('<th class="%s">%s</th>', $class, $title);
    $this->body .= sprintf('<td class="%s">%s</td>', $class, $tml);
    return $this->index;
  }
  
  public function build($body, $buttons) {
    $args = new targs();
    $args->style = $this->style;
    $args->checkboxes = implode("\n", $this->checkboxes);
    $args->head = $this->head;
    $args->body = $body;
    $args->buttons = $buttons;
    $tml = tfilestorage::getfile(litepublisher::$paths->languages . 'tablecolumns.ini');
    $theme = ttheme::i();
    return $theme->parsearg($tml, $args);
  }
  
}//class

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
    return ttemplate::i()->getready('$($("div.admintabs").get().reverse()).tabs({
      hide: true,
      show: true,
      beforeLoad: litepubl.uibefore
    })');
  }
  
}//class

class adminform {
  public $args;
  public$title;
  public $items;
  public $action;
  public $method;
  public $enctype;
  public $id;
  public $class;
  public $target;
  public $submit;
  public $inlineclass;
  
  public function __construct($args = null) {
    $this->args = $args;
    $this->title = '';
    $this->items = '';
    $this->action = '';
    $this->method = 'post';
    $this->enctype = '';
    $this->id = '';
    $this->class = '';
    $this->target = '';
    $this->submit = 'update';
    $this->inlineclass = 'form-inline';
  }
  
  public function line($s) {
    return "<div class=\"$this->inlineclass\">$s</div>";
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
      
      case 'inline':
      $this->class = $v ? $this->inlineclass : '';
      break;
    }
  }
  
  public function __tostring() {
    return $this->get();
  }
  
  public function gettml() {
    $result = '<div class="form-holder">';
    if ($this->title) $result .= "<h4>$this->title</h4>\n";
    $attr = "action=\"$this->action\"";
    foreach (array('method', 'enctype', 'target', 'id', 'class') as $k) {
      if ($v = $this->$k) $attr .= sprintf(' %s="%s"', $k, $v);
    }
    
    $result .= "<form $attr role=\"form\">";
    $result .= $this->items;
    if ($this->submit) $result .= $this->class == $this->inlineclass ? "[button=$this->submit]" : "[submit=$this->submit]";
    $result .= "\n</form>\n</div>\n";
    return $result;
  }
  
  public function get() {
    return tadminhtml::i()->parsearg($this->gettml(), $this->args);
  }
  
}//class

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
    $id = intval(substr($name, strlen('prop')));
    return call_user_func_array($this->callbacks[$id], array(ttheme::$vars['item']));
  }
  
}

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
    //tfiler::log(var_export($_GET, true) . var_export($_POST, true) . var_export($_FILES, true));
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
    $html = tadminhtml ::i();
    $hsect = $html->section;
    $html->section = 'editor';
    $lang = tlocal::i();
    $lsect = $lang->section;
    $lang->section = 'editor';
    $title = $lang->$name;
    if ($visual && $this->ajaxvisual && $this->visual) $title .= $html->loadvisual();
    $result = $html->getinput('editor', $name, tadminhtml::specchars($value), $title);
    $lang->section = $lsect;
    $html->section = $hsect;
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
    $tml = str_replace('$checkbox', $html->getinput('checkbox', 'category-$id', 'value="$id" $checked', '$title'), $html->category);
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
  
  public function getfileperm() {
    return litepublisher::$options->show_file_perm ? tadminperms::getcombo(0, 'idperm_upload') : '';
  }
  
  // $posteditor.files in template editor
  public function getfilelist() {
    $html = tadminhtml::i();
    $html->push_section('editor');
    $args = new targs();
    $args->fileperm = $this->getfileperm();
    
    $post = ttheme::$vars['post'];
    $files = tfiles::i();
    $where = litepublisher::$options->ingroup('editor') ? '' : ' and author = ' . litepublisher::$options->user;
    $args->pages = (int) ceil($files->db->getcount(" parent = 0 $where") / 20);
  $args->jsitems = '{}';
    $args->files = '';
    if ($post->id) {
      $list = $files->itemsposts->getitems($post->id);
      if (count($list)) {
        $items = implode(',', $list);
        $args->files = $items;
        
        if (defined('JSON_NUMERIC_CHECK')) {
          $jsattr = JSON_NUMERIC_CHECK | (defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);
          $args->jsitems = json_encode($files->db->res2items($files->db->query("select * from $files->thistable where id in ($items) or parent in ($items)")), $jsattr);
        } else {
          $args->jsitems = json_encode($files->db->res2items($files->db->query("select * from $files->thistable where id in ($items) or parent in ($items)")));
        }
      }
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
      $post->posted = tadminhtml::getdatetime('date');
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

class poststatus {
  public function __get($name) {
    $post = ttheme::$vars['post'];
  $value = $post->{$name};
    $lang = tlocal::i();
  return $lang->{$value};
  }
}

