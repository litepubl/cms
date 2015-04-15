<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminpolloptions extends tadminmenu {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function getcontent() {
    $html = tadminhtml::i();
    $lang = tlocal::admin('polls');
    $args = new targs();
    
    //note to open admin menus
    $result = $html->h3->noteoptions;
    
    $polls = tpolls::i();
    $man = tpollsman::i();
    $args->addtopost = $man->addtopost;
    
    $items = array();
    $polls->loadall_tml();
    foreach ($polls->tml_items as $id => $tml) {
      $items[$id] = $tml['name'];
    }
    
    $args->pollpost = tadminhtml::array2combo($items, $man->pollpost);
    $args->lifetime = $man->lifetime;
    $args->formtitle = $lang->Options;
    $result .= $html->adminform(
    '
    [checkbox=addtopost]
    [combo=pollpost]
    [text=lifetime]
    ', $args);
    
    return $result;
  }
  
  public function processform() {
    $man = tpollsman::i();
    $man->lock();
    $man->pollpost = (int) $_POST['pollpost'];
    $this->setadddtopost(isset($_POST['addtopost']));
    $man->lifetime = (int) $_POST['lifetime'];
    $man->unlock();
    return '';
  }
  
  public function setadddtopost($v) {
    $man = tpollsman::i();
    if ($v == $man->addtopost) return;
    $man->data['addtopost'] = $v;
    $man->save();
    
    $posts = tposts::i();
    if ($v) {
      $posts->added = $man->postadded;
      $posts->deleted = $man->postdeleted;
      $posts->aftercontent = $man->afterpost;
      $posts->syncmeta = true;
    } else {
      $posts->delete_event_class('added', get_class($man));
      $posts->delete_event_class('deleted', get_class($man));
      $posts->delete_event_class('aftercontent', get_class($man));
    }
  }
  
}//class