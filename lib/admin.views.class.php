<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminviews extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public static function getviewform($url) {
    $html = tadminhtml ::i();
    $lang = tlocal::admin();
    $args = new targs();
    $args->idview = self::getcombo(tadminhtml::getparam('idview', 1));
    $form = new adminform($args);
    $form->action = litepublisher::$site->url . $url;
    $form->inline = true;
    $form->method = 'get';
    $form->items = '[combo=idview]';
    $form->submit = 'select';
    return $form->get();
  }
  
  public static function getcomboview($idview, $name = 'idview') {
    $lang = tlocal::i();
    $lang->addsearch('views');
    $theme = ttheme::i();
    return strtr($theme->templates['content.admin.combo'], array(
    '$lang.$name' => $lang->view,
    '$name' => $name,
    '$value' => self::getcombo($idview)
    ));
  }
  
  public static function getcombo($idview) {
    $result = '';
    $views = tviews::i();
    foreach ($views->items as $id => $item) {
      $result .= sprintf('<option value="%d" %s>%s</option>', $id,
      $idview == $id ? 'selected="selected"' : '', $item['name']);
    }
    return $result;
  }
  
  public static function replacemenu($src, $dst) {
    $views = tviews::i();
    foreach ($views->items as &$viewitem) {
      if ($viewitem['menuclass'] == $src) $viewitem['menuclass'] = $dst;
    }
    $views->save();
  }
  
  private function get_custom(tview $view) {
    $result = '';
    $html = $this->html;
    $customadmin = $view->theme->templates['customadmin'];
    foreach ($view->data['custom'] as $name => $value) {
      if (!isset($customadmin[$name])) continue;
      switch ($customadmin[$name]['type']) {
        case 'text':
        case 'editor':
        $value = tadminhtml::specchars($value);
        break;
        
        case 'checkbox':
        $value = $value ? 'checked="checked"' : '';
        break;
        
        case 'combo':
        $value = tadminhtml  ::array2combo($customadmin[$name]['values'], $value);
        break;
        
        case 'radio':
        $value = $html->getradioitems(    "custom-$name", $customadmin[$name]['values'], $value);
        break;
      }
      
      $result .= $html->getinput(
      $customadmin[$name]['type'],
      "custom-$name",
      $value,
      tadminhtml::specchars($customadmin[$name]['title'])
      );
    }
    return $result;
  }
  
  private function set_custom($idview) {
    $view = tview::i($idview);
    if (count($view->custom) == 0) return;
    $customadmin = $view->theme->templates['customadmin'];
    foreach ($view->data['custom'] as $name => $value) {
      if (!isset($customadmin[$name])) continue;
      switch ($customadmin[$name]['type']) {
        case 'checkbox':
        $view->data['custom'][$name] = isset($_POST["custom-$name"]);
        break;
        
        case 'radio':
        $view->data['custom'][$name] = $customadmin[$name]['values'][(int) $_POST["custom-$name"]];
        break;
        
        default:
        $view->data['custom'][$name] = $_POST["custom-$name"];
        break;
      }
    }
  }
  
  public static function getspecclasses() {
    return array('thomepage', 'tarchives', 'tnotfound404', 'tsitemap');
  }
  
  private function get_view_sidebars($idview, $html, $lang, $args) {
    $view = tview::i($idview);
    $widgets = twidgets::i();
    $args->idview = $idview;
    $args->adminurl = tadminhtml::getadminlink('/admin/views/widgets/', 'idwidget');
    $count = count($view->sidebars);
    $sidebarnames = range(1, 3);
    $about = tthemeparser::i()->getabout($view->theme->name);
    foreach ($sidebarnames as $k => $v) {
      if (isset($about["sidebar$k"])) $sidebarnames[$k] = $about["sidebar$k"];
    }
    
    $sidebars = '';
    if (($idview > 1) && !$view->customsidebar) $view = tview::i(1);
    $sitems = array();
    foreach ($view->sidebars as $index => $sidebar) {
      $args->index = $index;
      $widgetlist = '';
      $idwidgets = array();
      foreach ($sidebar as $_item) {
        $id = $_item['id'];
        $idwidgets[] = $id;
        $sitems[$id] = $_item;
        $args->id = $id;
        $args->add($widgets->items[$id]);
        $widgetlist .= $html->widgetitem($args);
      }
      
      $args->sidebarname = $sidebarnames[$index];
      $args->widgetlist = $widgetlist;
      $args->idwidgets = implode(',', $idwidgets);
      $sidebars .= $html->sidebar($args);
    }
    
    $woptions = '';
    $allwidgets = '';
    foreach ($widgets->items as $id => $item) {
      $args->id = $id;
      $args->add($item);
      $enabled = ($item['cache'] == 'cache') || ($item['cache'] == 'nocache');
      $args->enabled = $enabled ? 'enabled' : 'disabled';
      
      if (isset($sitems[$id])) {
        $sitem = $sitems[$id];
      } else {
        $allwidgets .= $html->widgetitem($args);
        $sitem = array(
        'ajax' => $enabled ? 'inline' : false
        );
      }
      
      $args->add($sitem);
      $args->controls =
      $html->getinput('checkbox', "ajax$id", $sitem['ajax'] ? 'checked="checked"' : '', $lang->ajax) .
      $html->getinput('checkbox', "inline$id", ($enabled ? '' : 'disabled="disabled" ') . ($sitem['ajax'] === 'inline' ? 'checked="checked"' : ''), $lang->inline) .
      $html->getinput('submit', "delete$id", '', $lang->widget_delete);
      
      $woptions  .= $html->woptions ($args);
    }
    
    $args->sidebars = $sidebars;
    $args->woptions = $woptions;
    $args->allwidgets = $allwidgets;
    return $html->sidebars($args);
  }
  
  public function getcontent() {
    $result = '';
    $views = tviews::i();
    $html = $this->html;
    $lang = tlocal::i('views');
    $args = new targs();
    switch ($this->name) {
      case 'views':
      $html->addsearch('views');
      $lang->addsearch('views');
      $id = tadminhtml::getparam('idview', 0);
      if (!$id || !$views->itemexists($id)) {
        $adminurl = $this->adminurl . 'view';
        return $html->h4($html->getlink($this->url . '/addview/', $lang->add)) .
        $html->buildtable($views->items, array(
        array('left', $lang->name, "<a href=\"$adminurl=\$id\">\$name</a>"),
        array('center', $lang->delete, "<a href=\"$adminurl=\$id&action=delete\" class=\"confirm-delete-link\">$lang->delete</a>"),
        ));
      }
      
      $result = self::getviewform($this->url);
      $tabs = new tuitabs();
      $menuitems = array();
      foreach ($views->items as $itemview) {
        $class = $itemview['menuclass'];
        $menuitems[$class] = $class == 'tmenus' ? $lang->stdmenu : ($class == 'tadminmenus' ? $lang->adminmenu : $class);
      }
      
      $itemview = $views->items[$id];
      $args->add($itemview);
      $tabs->add($lang->widgets, $this->get_view_sidebars($id, $html, $lang, $args));
      $args->menu = tadminhtml  ::array2combo($menuitems, $itemview['menuclass']);
      $tabs->add($lang->name,'[text=name]' .
      ($id == 1 ? '' : ('[checkbox=customsidebar] [checkbox=disableajax]')) .
      '[checkbox=hovermenu] [combo=menu]');
      
      $view = tview::i($id);
      $lang->firstsearch('themes');
      $tabs->add($lang->theme, tadminthemes::getlist($html->radiotheme, $view->theme->name));
      if (count($view->custom)) $tabs->add($lang->custom, $this->get_custom($view));
      
      $result .= $html->h4->help;
      $form = new adminform($args);
      $form->id = 'admin-view-form';
      $form->title = $lang->edit;
      $form->items = $tabs->get();
      $result .= $form->get();
      $result .=       ttemplate::i()->getjavascript(ttemplate::i()->jsmerger_adminviews);
      break;
      
      case 'addview':
      $args->formtitle = $lang->addview;
      $result .= $html->adminform('[text=name]', $args);
      break;
      
      case 'spec':
      $tabs = new tuitabs();
      $inputs = '';
      foreach (self::getspecclasses() as $classname) {
        $obj = getinstance($classname);
        $args->classname = $classname;
        $name = substr($classname, 1);
      $args->title = $lang->{$name};
        $inputs = self::getcomboview($obj->idview, "idview-$classname");
        if (isset($obj->data['keywords'])) $inputs .= $html->getedit("keywords-$classname", $obj->data['keywords'], $lang->keywords);
        if (isset($obj->data['description'])) $inputs .= $html->getedit("description-$classname", $obj->data['description'], $lang->description);
        if (isset($obj->data['head'])) $inputs .= $html->getinput('editor', "head-$classname", tadminhtml::specchars($obj->data['head']), $lang->head);
        
      $tabs->add($lang->{$name}, $inputs);
      }
      
      $args->formtitle = $lang->defaults;
      $result .= tuitabs::gethead() . $html->adminform($tabs->get(), $args);
      break;
      
      case 'group':
      $args->formtitle = $lang->viewposts;
      $result .= $html->adminform(
      self::getcomboview($views->defaults['post'], 'postview') .
      '<input type="hidden" name="action" value="posts" />', $args);
      
      $args->formtitle = $lang->viewmenus;
      $result .= $html->adminform(
      self::getcomboview($views->defaults['menu'], 'menuview') .
      '<input type="hidden" name="action" value="menus" />', $args);
      
      $args->formtitle = $lang->themeviews;
      $view = tview::i();
      $list =    tfiler::getdir(litepublisher::$paths->themes);
      sort($list);
      $themes = array_combine($list, $list);
      $result .= $html->adminform(
      $html->getcombo('themeview', tadminhtml::array2combo($themes, $view->themename), $lang->themename) .
      '<input type="hidden" name="action" value="themes" />', $args);
      break;
      
      case 'defaults':
      $items = '';
      $theme = ttheme::i();
      $tml = $theme->templates['content.admin.combo'];
      foreach ($views->defaults as $name => $id) {
        $args->name = $name;
        $args->value = self::getcombo($id);
        $args->data['$lang.$name'] = $lang->$name;
        $items .= $theme->parsearg($tml, $args);
      }
      $args->items = $items;
      $args->formtitle = $lang->defaultsform;
      $result .= $theme->parsearg($theme->content->admin->form, $args);
      break;
      
      case 'headers':
      $tabs = new tuitabs();
      $args->heads = ttemplate::i()->heads;
      $tabs->add($lang->headstitle, '[editor=heads]');
      
      $args->adminheads = tadminmenus::i()->heads;
      $tabs->add($lang->admin, '[editor=adminheads]');
      
      $ajax = tajaxposteditor ::i();
      $args->ajaxvisual=  $ajax->ajaxvisual;
      $args->visual= $ajax->visual;
      $args->show_file_perm = litepublisher::$options->show_file_perm;
      $tabs->add($lang->posteditor, '[checkbox=show_file_perm] [checkbox=ajaxvisual] [text=visual]');
      
      $args->formtitle = $lang->headstitle;
      $result = $html->adminform($tabs->get(), $args);
      $result .= tuitabs      ::gethead();
      break;
      
      case 'admin':
      return $this->adminoptionsform->getform();
    }
    
    return $html->fixquote($result);
  }
  
  public function processform() {
    //dumpvar($_POST);
    $result = '';
    switch ($this->name) {
      case 'views':
      $views = tviews::i();
      $idview = (int) tadminhtml::getparam('idview', 0);
      if (!$idview || !$views->itemexists($idview)) return '';
      if ($this->action == 'delete') {
        if ($idview > 1) $views->delete($idview);
        return '';
      }
      
      $view = tview::i($idview);
      if ($idview > 1) {
        $view->customsidebar = isset($_POST['customsidebar']);
        $view->disableajax = isset($_POST['disableajax']);
      }
      
      $view->name = trim($_POST['name']);
      $view->themename = trim($_POST['theme_idview']);
      $view->menuclass = $_POST['menu'];
      $view->hovermenu = isset($_POST['hovermenu']);
      
      $this->set_custom($idview);
      
      if (($idview == 1) || $view->customsidebar) {
        $widgets = twidgets::i();
        foreach (range(0, 2) as $index) {
          $view->sidebars[$index] = array();
          $idwidgets = explode(',', trim($_POST["sidebar$index"]));
          foreach($idwidgets as $idwidget) {
            $idwidget = (int) trim($idwidget);
            if (!$widgets->itemexists($idwidget)) continue;
            $view->sidebars[$index][] = array(
            'id' => $idwidget,
            'ajax' =>isset($_POST["inline$idwidget"]) ? 'inline' : isset($_POST["ajax$idwidget"])
            );
          }
        }
      }
      
      $view->save();
      break;
      
      case 'addview':
      $name = trim($_POST['name']);
      if ($name != '') {
        $views = tviews::i();
        $id = $views->add($name);
      }
      break;
      
      case 'spec':
      foreach (self::getspecclasses() as $classname) {
        $obj = getinstance($classname);
        $obj->lock();
        $obj->setidview($_POST["idview-$classname"]);
        if (isset($obj->data['keywords'])) $obj->keywords = $_POST["keywords-$classname"];
        if (isset($obj->data['description '])) $obj->description = $_POST["description-$classname"];
        if (isset($obj->data['head'])) $obj->head = $_POST["head-$classname"];
        $obj->unlock();
      }
      break;
      
      case 'group':
      switch ($_POST['action']) {
        case 'posts':
        $posts = tposts::i();
        $idview = (int) $_POST['postview'];
        if (dbversion) {
          $posts->db->update("idview = '$idview'", 'id > 0');
        } else {
          foreach ($posts->items as $id => $item) {
            $post = tpost::i($id);
            $post->idview = $idview;
            $post->save();
            $post->free();
          }
        }
        break;
        
        case 'menus':
        $idview = (int) $_POST['menuview'];
        $menus = tmenus::i();
        foreach ($menus->items as $id => $item) {
          $menu = tmenu::i($id);
          $menu->idview = $idview;
          $menu->save();
        }
        break;
        
        case 'themes':
        $themename = $_POST['themeview'];
        $views = tviews::i();
        $views->lock();
        foreach ($views->items as $id => $item) {
          $view = tview::i($id);
          $view->themename = $themename;
          $view->save();
        }
        $views->unlock();
        break;
      }
      break;
      
      case 'defaults':
      $views = tviews::i();
      foreach ($views->defaults as $name => $id) {
        $views->defaults[$name] = (int) $_POST[$name];
      }
      $views->save();
      break;
      
      case 'headers':
      $template = ttemplate::i();
      $template->heads = $_POST['heads'];
      $template->save();
      
      $adminmenus = tadminmenus::i();
      $adminmenus->heads = $_POST['adminheads'];
      $adminmenus->save();
      
      $ajax = tajaxposteditor ::i();
      $ajax->lock();
      $ajax->ajaxvisual = isset($_POST['ajaxvisual']);
      $ajax->visual = trim($_POST['visual']);
      $ajax->unlock();
      
      litepublisher::$options->show_file_perm = isset($_POST['show_file_perm']);
      break;
      
      case 'admin':
      return $this->adminoptionsform->processform();
    }
    
    ttheme::clearcache();
  }
  
}//class