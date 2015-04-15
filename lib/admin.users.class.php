<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminusers extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function  gethead() {
    return parent::gethead() . tuitabs::gethead();
  }
  
  public function getcontent() {
    $result = '';
    $users = tusers::i();
    $groups = tusergroups::i();
    
    $html = $this->html;
    $lang = tlocal::i('users');
    $args = targs::i();
    
    $id = $this->idget();
    switch ($this->action) {
      case 'edit':
      if (!$users->itemexists($id)) {
        $result .= $this->notfound();
      } else {
        $statuses = array();
        foreach (array('approved', 'hold','comuser') as $name) {
          $statuses[$name] = $lang->$name;
        }
        
        $item = $users->getitem($id);
        $args->add($item);
        $args->registered = tuserpages::i()->getvalue($id, 'registered');
        $args->formtitle = $item['name'];
        $args->status = tadminhtml::array2combo($statuses, $item['status']);
        
        $tabs = new tuitabs();
        $tabs->add($lang->login, '[text=email] [password=password]');
        $tabs->add($lang->groups, '[combo=status]' .
        tadmingroups::getgroups($item['idgroups']));
        $tabs->add('Cookie', '[text=cookie] [text=expired] [text=registered] [text=trust]');
        
        $args->password = '';
        $result .= $html->adminform($tabs->get(), $args);
      }
      break;
      
      case 'delete':
      $result .= $html->confirm_delete($users, $this->adminurl);
      break;
      
      default:
      $args->formtitle = $lang->newuser;
      $args->email = '';
      $args->action = 'add';
      
      $tabs = new tuitabs();
      $tabs->add($lang->login, '[text=email] [password=password] [text=name] [hidden=action]');
      $tabs->add($lang->groups, tadmingroups::getgroups(array()));
      
      $result .= $html->adminform($tabs->get(), $args);
    }
    $args->search = '';
    
    //table
    $perpage = 20;
    $count = $users->count;
    $from = $this->getfrom($perpage, $count);
    $where = '';
    $params = '';
    if (!empty($_GET['idgroup'])) {
      $idgroup = (int) tadminhtml::getparam('idgroup', 0);
      if ($groups->itemexists($idgroup)) {
        $grouptable = litepublisher::$db->prefix . $users->grouptable;
        $where =  "$users->thistable.id in (select iduser from $grouptable where idgroup = $idgroup)";
        $params = "idgroup=$idgroup";
      }
    } elseif ($search = trim(tadminhtml::getparam('search', ''))) {
      $params = 'search=' . urlencode($search);
      $args->search = $search;
      $search = litepublisher::$db->escape($search);
      $search = strtr($search, array(
      '%' => '\%',
      '_' => '\_'
      ));
      
      $where = "email like '%$search%' or name like '%$search%' ";
      $count = $users->db->getcount($where);
      $from = $this->getfrom($perpage, $count);
    }
    
    $items = $users->select($where, " order by id desc limit $from, $perpage");
    if (!$items) $items = array();
    
    $args->adminurl = $this->adminurl;
    $args->formtitle = $lang->userstable;
    $args->table = $html->items2table($users, $items, array(
    $html->get_table_checkbox('user'),
    array('left', $lang->edit, sprintf('<a href="%s=$id&action=edit">$name</a>', $this->adminurl)),
    $html->get_table_item('status'),
    array('left', $lang->comments, sprintf('<a href="%s">%s</a>', tadminhtml::getadminlink('/admin/comments/', 'iduser=$id'), $lang->comments)),
    array('left', $lang->page, sprintf('<a href="%s">%s</a>', tadminhtml::getadminlink('/admin/users/pages/', 'id=$id'), $lang->page)),
    $html->get_table_link('delete', $this->adminurl)
    ));
    
    $result .= $html->deletetable($args);
    $result = $html->fixquote($result);
    
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage), $params);
    
    $form = new adminform($args);
    $form->method = 'get';
    $form->inline = true;
    $form->items = '[text=search]';
    $form->submit = 'find';
    $result .= $form->get();
    return $result;
  }
  
  public function processform() {
    $users = tusers::i();
    $groups = tusergroups::i();
    
    if (isset($_POST['delete'])) {
      foreach ($_POST as $key => $value) {
        if (!is_numeric($value)) continue;
        $id = (int) $value;
        $users->delete($id);
        //if (litepublisher::$classes->exists('tregservices')) $users->getdb('
      }
      return;
    }
    
    switch ($this->action) {
      case 'add':
      $_POST['idgroups'] = tadminhtml::check2array('idgroup-');
      if ($id = $users->add($_POST)) {
        litepublisher::$urlmap->redir("$this->adminurl=$id&action=edit");
      } else {
        return $this->html->h4red->invalidregdata;
      }
      break;
      
      case 'edit':
      $id = $this->idget();
      if (!$users->itemexists($id)) return;
      $_POST['idgroups'] = tadminhtml::check2array('idgroup-');
      if (!$users->edit($id, $_POST))return $this->notfound;
      if ($id == 1) {
        litepublisher::$site->author = $_POST['name'];
        //litepublisher::$site->email = $_POST['email'];
      }
      break;
    }
  }
  
}//class