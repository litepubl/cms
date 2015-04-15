<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminpolls extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcombostatus($status) {
    $lang = tlocal::admin('polls');
    return tadminhtml::array2combo(array(
    'opened' => $lang->opened,
    'closed' => $lang->closed
    ), $status);
  }
  
  public function getcombotml($id_tml) {
    $polls = tpolls::i();
    $polls->loadall_tml();
    $tml_items = array();
    foreach ($polls->tml_items as $id => $tml) {
      $tml_items[$id] = $tml['name'];
    }
    
    return tadminhtml::array2combo($tml_items, $id_tml);
  }
  
  public function getcontent() {
    $result = '';
    $polls = tpolls::i();
    $html = tadminhtml::i();
    $lang = tlocal::admin('polls');
    $args = new targs();
    $adminurl = $this->adminurl;
    
    if ($action = $this->action) {
      $id = $this->idget();
      switch ($action) {
        case 'delete':
        if ($this->confirmed) {
          $polls->delete($id);
          $result .= $html->h4->deleted;
        } else {
          $result .= $html->confirmdelete($id, $adminurl, $lang->confirmdelete);
        }
        break;
        
        case 'edit':
        if (!$polls->itemexists($id)) {
          $result .= $this->notfound();
        } else {
          $result .= sprintf($html->h4->shorttag, $id);
          $item = $polls->getitem($id);
          $args->status = $this->getcombostatus($item['status']);
          $args->id_tml = $this->getcombotml($item['id_tml']);
          $args->id = $id;
          $args->formtitle = $lang->editpoll;
          $result .= $html->adminform(
          '[combo=status]
          [combo=id_tml]
          ', $args);
        }
        break;
        
        case 'add':
        $args->status = $this->getcombostatus('opened');
        $args->id_tml = $this->getcombotml(tpollsman::i()->pollpost);
        $args->formtitle = $lang->addpoll;
        $result .= $html->adminform(
        '[combo=status]
        [combo=id_tml]
        ', $args);
        break;
        
        case 'create':
        $args->status = $this->getcombostatus('opened');
        $args->status = '';
        $args->title = '';
        $types = array_keys(tpolltypes::i()->items);
        $args->type = tadminhtml::array2combo(array_combine($types, $types), $types[0]);
        $args->newitems = '';
        $args->formtitle = $lang->createpoll;
        $result .= $html->adminform(
        '[combo=status]
        [text=name]
        [text=title]
        [combo=type]
        [editor=newitems]
        ', $args);
        break;
      }
    }
    
    $result .= "<ul>
    <li><a href='$adminurl=0&amp;action=add'>$lang->addpoll</a></li>
    <li><a href='$adminurl=0&amp;action=create'>$lang->createpoll</a></li>
    </ul>";
    
    $args->adminurl = $adminurl;
    $table = '';
    $tr = '<tr>
    <td><a href="$adminurl=$id&amp;action=edit">$name</a></td>
    <td><a href=$adminurl=$id&amp;action=delete">$lang.delete</a></td>
    </tr>';
    
    $perpage = 20;
    $count = $polls->db->getcount();
    $from = $this->getfrom($perpage, $count);
    $items = $polls->select('', " order by id desc limit $from, $perpage");
    $result .=sprintf($html->h4->count, $from, $from + count($items), $count);
    foreach ($items as $id) {
      $item = $polls->getitem($id);
      $args->id = $id;
      $args->add($item);
      $tml = $polls->get_tml($item['id_tml']);
      $args->name = $tml['name'];
      $args->title = $tml['title'];
      $table .= $html->parsearg($tr, $args);
    }
    
    $head = "<tr>
    <th>$lang->edit</th>
    <th>$lang->delete</th>
    </tr>";
    
    $result .= $html->gettable($head, $table);
    
    $theme = ttheme::i();
    $result .= $theme->getpages($this->url, litepublisher::$urlmap->page, ceil($count/$perpage));
    
    return $result;
  }
  
  public function processform() {
    $polls = tpolls::i();
    if ($action = $this->action) {
      switch ($action) {
        case 'edit':
        $polls->edit($this->idget(), $_POST['id_tml'], $_POST['status']);
        break;
        
        case 'add':
        $id = $polls->add($_POST['id_tml'], $_POST['status']);
        return litepublisher::$urlmap->redir($this->adminurl . '=' . $id . '&action=edit');
        
        case 'create':
        if ($id_tml = tadminpolltemplates::i()->addtml()) {
          $id = $polls->add($id_tml, $_POST['status']);
          return litepublisher::$urlmap->redir($this->adminurl . '=' . $id . '&action=edit');
        } else {
          return $this->html->empty;
        }
        break;
      }
    }
  }
  
}//class