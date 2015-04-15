<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadmingroups extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public static function getgroups(array $idgroups) {
    $result = '';
    $groups = tusergroups::i();
    $tml = '<li><input type="checkbox" name="idgroup-$id" id="checkbox-idgroup-$id" value="$id" $checked />
    <label for="checkbox-idgroup-$id"><strong>$title</strong></label></li>';
    $theme = ttheme::i();
    $args = new targs();
    foreach ($groups->items as $id => $item) {
      $args->add($item);
      $args->id = $id;
      $args->checked = in_array($id, $idgroups);
      $result .= strtr ($tml, $args->data);
    }
    return sprintf('<ul>%s</ul>', $result);
  }
  
  public function getcontent() {
    $groups = tusergroups::i();
    $html = $this->html;
    $lang = tlocal::admin('users');
    $args = targs::i();
    $adminurl = $this->adminurl;
    $result = "<h4><a href='$adminurl=0&action=add'>$lang->addgroup</a></h4>";
    $id = $this->idget();
    
    switch ($this->action) {
      case 'add':
      $result .= $html->p->notegroup;
      $args->name = '';
      $args->title = '';
      $args->home = '';
      $args->action = 'add';
      $args->formtitle = $lang->editgroup;
      $result .= $html->adminform('
      [text=title]
      [text=name]
      [text=home]
      [hidden=action]'
      . $html->h4->parentgroups .
      self::getgroups(array())
      , $args);
      break;
      
      case 'edit':
      $result .= $html->p->notegroup;
      $args->add($groups->items[$id]);
      $args->id = $id;
      $args->action = 'edit';
      $args->formtitle = $lang->editgroup;
      $result .= $html->adminform('
      [text=title]
      [text=name]
      [text=home]
      [hidden=id]
      [hidden=action]'
      . $html->h4->parentgroups .
      self::getgroups($groups->items[$id]['parents'])
      , $args);
      break;
      
      case 'delete':
      $result .= $html->confirm_delete($groups, $this->adminurl);
      break;
    }
    
    $result .= $html->h4->grouptable;
    $result .= $html->buildtable($groups->items, array(
    array('left', $lang->name, '<a href="' . $adminurl . '=$id&action=edit" title="$title">$title</a>'),
    array('left', $lang->users, sprintf('<a href="%s">%s</a>', tadminhtml::getadminlink('/admin/users/', 'idgroup=$id'), $lang->users)),
    $html->get_table_link('delete', $adminurl)
    ));
    return $result;
  }
  
  public function processform() {
    $groups = tusergroups::i();
    switch ($this->action) {
      case 'add':
      $groups->lock();
      $id = $groups->add($_POST['name'], $_POST['title'], $_POST['home']);
      $groups->items[$id]['parents'] = tadminhtml::check2array('idgroup-');
      $groups->unlock();
      $_POST['id'] = $id;
      $_GET['id'] = $id;
      $_GET['action'] = 'edit';
      break;
      
      case 'edit':
      $id = $this->idget();
      if ($groups->itemexists($id)) {
        foreach (array('name', 'title', 'home') as $name) {
          $groups->items[$id][$name] = $_POST[$name];
        }
        $groups->items[$id]['parents'] = tadminhtml::check2array('idgroup-');
        $groups->save();
      }
      break;
    }
  }
  
}//class