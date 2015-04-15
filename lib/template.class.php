<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

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