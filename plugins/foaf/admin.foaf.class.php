<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminfoaf extends tadminmenu {
  
  private $user;
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  private function getcombo($id, $status) {
    $lang = tlocal::i('foaf');
    $names = array('approved', 'hold', 'invated', 'rejected', 'spam', 'error');
    $result = "<select name='status-$id' >\n";
    
    foreach ($names as $name) {
      $title = $lang->$name;
      $selected = $status == $name ? 'selected' : '';
      $result .= "<option value='$name' $selected>$title</option>\n";
    }
    $result .= "</select>";
    return $result;
  }
  
  private function getlist() {
    $foaf = tfoaf::i();
    $perpage = 20;
    $total = $foaf->getcount();
    $from = $this->getfrom($perpage, $total);
    if ($foaf->dbversion) {
      $items = $foaf->select('', " order by status asc, added desc limit $from, $perpage");
      if (!$items) $items = array();
    } else {
      $items = array_slice(array_keys($foaf->items), $from, $perpage);
    }
    $html = $this->html;
    $result = $html->tableheader();
    $args = targs::i();
    $args->adminurl = $this->adminurl;
    foreach ($items as $id )  {
      $item = $foaf->getitem($id);
      $args->add($item);
      $args->id = $id;
      $args->status = tlocal::get('foaf', $item['status']);
      $result .= $html->itemlist($args);
    }
    $result .= $html->tablefooter();
    
    $theme = ttheme::i();
    $result .= $theme->getpages('/admin/foaf/', litepublisher::$urlmap->page, ceil($total/$perpage));
    return $result;
  }
  
  
  public function getcontent() {
    $lang = tlocal::i('foaf');
    $result = '';
    $foaf = tfoaf::i();
    $html = $this->html;
    
    switch ($this->name) {
      case 'foaf':
      switch ($this->action) {
        case false:
        $result = $html->addform();
        break;
        
        case 'edit':
        $id = $this->idget();
        if (!$foaf->itemexists($id)) return $this->notfound;
        $item = $foaf->getitem($id);
        $args = targs::i();
        $args->add($item);
        $args->status = $this->getcombo($id, $item['status']);
        $result .= $html->editform($args);
        break;
        
        case 'delete':
        $id = $this->idget();
        if (!$foaf->itemexists($id)) return $this->notfound;
        if ($this->confirmed) {
          $foaf->delete($id);
          $result .= $html->h2->deleted;
        } else {
          $item = $foaf->getitem($id);
          $args = targs::i();
          $args->add($item);
          $args->adminurl = $this->adminurl;
          $args->action = 'delete';
          $args->confirm = $html->confirmdelete($args);
          $result .= $html->confirmform($args);
        }
        break;
      }
      $result .= $this->getlist();
      break;
      
      case 'profile':
      $profile = tprofile::i();
      ttheme::$vars['profile '] = $profile;
      $args = targs::i();
      $form = '';
      foreach (array(
      'nick',
      'img',
      'dateOfBirth',
      'googleprofile',
      'skype',
      'icqChatID',
      'aimChatID',
      'jabberID',
      'msnChatID',
      'yahooChatID',
      'mbox',
      'country',
      'region',
      'city',
      'geourl',
      'interests',
      'interesturl'
      ) as $name) {
        $args->$name = $profile->$name;
        $form .= is_bool($profile->$name) ? "[checkbox=$name]" : "[text=$name]";
        if (!isset($lang->$name)) $args->data["\$lang.$name"] = $name;
      }
      $args->gender = $profile->gender != 'female';
      $args->data['$lang.gender'] = $lang->ismale;
      $args->bio = $profile->bio;
      $args->formtitle = $lang->profileform;
      $result .= $html->adminform($form .
      '[checkbox=gender]
      [editor=bio]
      ',$args);
      break;
      
      case 'profiletemplate':
      $profile = tprofile::i();
      $args = targs::i();
      $args->template = $profile->template;
      $result .= $html->profiletemplate($args);
      break;
    }
    
    return $html->fixquote($result);
  }
  
  public function processform() {
    $foaf = tfoaf::i();
    $html = $this->html;
    
    switch ($this->name) {
      case 'foaf':
      if (!isset($_POST['foaftable'])) {
        extract($_POST, EXTR_SKIP);
        if ($this->action == 'edit') {
          $id = $this->idget();
          if (!$foaf->itemexists($id)) return '';
          $status = $_POST["status-$id"];
          $foaf->edit($id, $nick, $url, $foafurl, $status);
          return $html->h2->successedit;
        } else {
          if (empty($url))  return '';
          if ($foaf->hasfriend($url)) return $html->h2->erroradd;
          $foaf->addurl($url);
          return $html->h2->successadd;
        }
      } else {
        $status = isset($_POST['approve']) ? 'approved' : (isset($_POST['hold']) ? 'hold' : 'delete');
        $foaf->lock();
        foreach ($_POST as $key => $id) {
          if (!is_numeric($id))  continue;
          $id = (int) $id;
          if ($status == 'delete') {
            $foaf->delete($id);
          } else {
            $foaf->changestatus($id, $status);
          }
        }
        $foaf->unlock();
        return $html->h2->successmoderate;
      }
      
      case 'profile':
      $profile = tprofile::i();
      foreach ($_POST as $key => $value) {
        if (isset($profile->data[$key])) $profile->data[$key] = $value;
      }
      $profile->gender = isset($_POST['gender']) ? 'male' : 'female';
      $profile->save();
      return $html->h2->successprofile;
      
      case 'profiletemplate':
      $profile = tprofile::i();
      $profile->template = $_POST['template'];
      $profile->save();
      return $html->h2->successprofile;
    }
    
    return '';
  }
  
}//class