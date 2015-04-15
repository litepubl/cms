<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminmenumanager extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gethead() {
    $result = parent::gethead();
    
    $template = ttemplate::i();
    $template->ltoptions['idpost'] = $this->idget();
    $template->ltoptions['lang'] = litepublisher::$options->language ;
  $result .= $template->getready('$("#tabs").tabs({ beforeLoad: litepubl.uibefore});');
    return $result . tajaxmenueditor ::i()->gethead();
  }
  
  public function gettitle() {
    if (($this->name == 'edit') && ($this->idget() != 0)) {
      return $this->lang->edit;
    }
    return parent::gettitle();
  }
  
  public function getcontent() {
    $result = '';
    switch ($this->name) {
      case 'menu':
      if (isset($_GET['action']) && in_array($_GET['action'], array('delete', 'setdraft', 'publish'))) {
        $result .= $this->doaction($this->idget(), $_GET['action']);
      }
      $result .= $this->getmenulist();
      return $result;
      
      case 'edit':
      case 'editfake':
      $id = tadminhtml::idparam();
      $menus = tmenus::i();
      $parents = array(0 => '-----');
      foreach ($menus->items as $item) {
        $parents[$item['id']] = $item['title'];
      }
      
      $html = $this->html;
      $lang = tlocal::i('menu');
      $args = new targs();
      $args->adminurl = $this->adminurl;
      $args->ajax = tadminhtml::getadminlink('/admin/ajaxmenueditor.htm', "id=$id&get");
      $args->editurl = tadminhtml::getadminlink('/admin/menu/edit', 'id');
      if ($id == 0) {
        $args->id = 0;
        $args->title = '';
        $args->parent = tadminhtml::array2combo($parents, 0);
        $args->order = tadminhtml::array2combo(range(0, 10), 0);
        $status = 'published';
      } else {
        if (!$menus->itemexists($id)) return $this->notfound;
        $menuitem = tmenu::i($id);
        $args->id = $id;
        $args->title = $menuitem->getownerprop('title');
        $args->parent = tadminhtml::array2combo($parents, $menuitem->parent);
        $args->order = tadminhtml::array2combo(range(0, 10), $menuitem->order);
        $status = $menuitem->status;
      }
      $args->status = tadminhtml::array2combo(array(
      'draft' => $lang->draft,
      'published' => $lang->published
      ), $status);
      
      if (($this->name == 'editfake') || (($id > 0) && ($menuitem instanceof tfakemenu))) {
        $args->url = $id == 0 ? '' : $menuitem->url;
        $args->type = 'fake';
        $args->formtitle = $lang->faketitle;
        return $html->adminform(
        '[text=title]
        [text=url]
        [combo=parent]
        [combo=order]
        [combo=status]
        [hidden=type]
        [hidden=id]', $args);
      }
      
      $tabs = new tuitabs();
      $tabs->add($lang->title, '
      [text=title]
      [combo=parent]
      [combo=order]
      [combo=status]
      [hidden=id]
      ');
      
      $ajaxurl = tadminhtml::getadminlink('/admin/ajaxmenueditor.htm', "id=$id&get");
      $tabs->ajax($lang->view,"$ajaxurl=view");
      $tabs->ajax('SEO', "$ajaxurl=seo");
      
      $ajaxeditor = tajaxmenueditor::i();
      $args->formtitle = $lang->edit;
      return tuitabs::gethead() . $html->adminform($tabs->get() .
      sprintf('<div>%s</div>', $ajaxeditor->geteditor('raw', $id == 0 ? '' : $menuitem->rawcontent, true)), $args);
    }
  }
  
  public function processform() {
    if (!(($this->name == 'edit') || ($this->name == 'editfake'))) return '';
    extract($_POST, EXTR_SKIP);
    if (empty($title)) return '';
    $id = $this->idget();
    $menus = tmenus::i();
    if (($id != 0) && !$menus->itemexists($id)) return $this->notfound;
    if (isset($type) && ($type == 'fake')) {
      $menuitem = tfakemenu::i($id);
    } else  {
      $menuitem = tmenu::i($id);
    }
    
    $menuitem->title = $title;
    $menuitem->order = (int) $order;
    $menuitem->parent = (int) $parent;
    $menuitem->status = $status == 'draft' ? 'draft' : 'published';
    if (isset($raw)) $menuitem->content = $raw;
    if (isset($idview)) $menuitem->idview = $idview;
    if (isset($url)) {
      $menuitem->url = $url;
      if (!isset($type) || ($type != 'fake')) {
        $menuitem->keywords = $keywords;
        $menuitem->description = $description;
        $menuitem->head = $head;
      }
    }
    if ($id == 0) {
      $_POST['id'] = $menus->add($menuitem);
    } else  {
      $menus->edit($menuitem);
    }
    return sprintf($this->html->p->success,"<a href=\"$menuitem->link\" title=\"$menuitem->title\">$menuitem->title</a>");
  }
  
  private function getmenulist() {
    $menus = tmenus::i();
    $lang = tlocal::admin();
    $editurl = litepublisher::$site->url .$this->url . 'edit/' . litepublisher::$site->q . 'id';
    $html = $this->html;
    ttheme::$vars['menuitem'] = new menu_item();
    $result = $html->buildtable($menus->items, array(
    array('left', $lang->menutitle, '$menuitem.link'),
    array('center', $lang->order, '$order'),
    array('center', $lang->parent, '$menuitem.parent'),
    array('center', $lang->edit, "<a href='$editurl=\$id'>$lang->edit</a>"),
    array('center', $lang->delete, "<a class=\"confirm-delete-link\" href=\"$this->adminurl=\$id&action=delete\">$lang->delete</a>"),
    ));
    
    unset(ttheme::$vars['menuitem']);
    return str_replace("'", '"', $result);
  }
  
  private function doaction($id, $action) {
    $menus = tmenus::i();
    if (!$menus->itemexists($id))  return $this->notfound;
    $args = targs::i();
    $html = $this->html;
    $h2 = $html->h2;
    $menuitem = tmenu::i($id);
    switch ($action) {
      case 'delete' :
      if  (!$this->confirmed) {
        $args->adminurl = $this->adminurl;
        $args->id = $id;
        $args->action = 'delete';
        $args->confirm = sprintf($this->lang->confirm, tlocal::get('common', $action), $menus->getlink($id));
        return $this->html->confirmform($args);
      } else {
        $menus->delete($id);
        return $h2->confirmeddelete;
      }
      
      case 'setdraft':
      $menuitem->status = 'draft';
      $menus->edit($menuitem);
      return $h2->confirmedsetdraft;
      
      case 'publish':
      $menuitem->status = 'published';
      $menus->edit($menuitem);
      return $h2->confirmedpublish;
    }
    return '';
  }
  
}//class

class menu_item {
  public $menus;
  
  public function __construct() {
    $this->menus = tmenus::i();
  }
  
  public function __get($name) {
    $item = ttheme::$vars['item'];
    switch ($name) {
      case 'parent':
      return $item['parent'] == 0 ? '---' : $this->menus->getlink($item['parent']);
      
      case 'status':
      return tlocal::get('common', $item['status']);
      
      case 'link':
      return $this->menus->getlink($item['id']);
    }
    
    return '';
  }
  
}