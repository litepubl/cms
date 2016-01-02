<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class tjsonfiles extends tevents {
  
  public static function i() {
    return getinstance(__class__);
  }
  protected function create() {
    parent::create();
    $this->addevents('uploaded', 'onprops');
  }
  
  public function auth($idpost) {
    if (!litepublisher::$options->user) return false;
    if (litepublisher::$options->ingroup('editor')) return true;
    if ($idpost == 0) return true;
    if ($idauthor = $this->getdb('posts')->getvalue($idpost, 'author')) {
      return litepublisher::$options->user == (int) $idauthor;
    }
    return false;
  }
  
  public function forbidden() {
    $this->error('Forbidden', 403);
  }
  
  public function files_getpost(array $args) {
    $idpost = (int) $args['idpost'];
    if (!$this->auth($idpost)) return $this->forbidden();
    
    $where = litepublisher::$options->ingroup('editor') ? '' : ' and author = ' . litepublisher::$options->user;
    
    $files = tfiles::i();
    $result = array(
    'count' => (int) $files->db->getcount(" parent = 0 $where"),
    'files' => array()
    );
    
    if ($idpost) {
      $list = $files->itemsposts->getitems($idpost);
      if (count($list)) {
        $items = implode(',', $list);
        $result['files'] = $files->db->res2items($files->db->query("select * from $files->thistable where id in ($items) or parent in ($items)"));
      }
    }
    
    if (litepublisher::$options->show_file_perm) {
      $theme = ttheme::getinstance('default');
      $result['fileperm'] = tadminperms::getcombo(0, 'idperm_upload');
    }
    
    return $result;
  }
  
  public function files_getpage(array $args) {
    if (!litepublisher::$options->hasgroup('author')) return $this->forbidden();
    $page = (int) $args['page'];
    $perpage = isset($args['perpage']) ? (int) $args['perpage'] : 10;
    
    $from = $page * $perpage;
    $where = litepublisher::$options->ingroup('editor') ? '' : ' and author = ' . litepublisher::$options->user;
    
    $files = tfiles::i();
    $db = $files->db;
    
    $result = $db->res2items($db->query("select * from $files->thistable where parent = 0 $where order by id desc limit $from, $perpage"));
    
    if (count($result)) {
      $idlist = implode(',', array_keys($result));
      $thumbs = $db->res2items($db->query("select * from $files->thistable where parent in ($idlist)"));
      $result = array_merge($result, $thumbs);
    }
    
    return array(
    'files' => $result
    );
  }
  
  public function files_setprops(array $args) {
    if (!litepublisher::$options->hasgroup('author')) return $this->forbidden();
    $id = (int) $args['idfile'];
    $files = tfiles::i();
    if (!$files->itemexists($id)) return $this->forbidden();
    $item= $files->getitem($id);
    $item['title'] = tcontentfilter::escape(tcontentfilter::unescape($args['title']));
    $item['description'] = tcontentfilter::escape(tcontentfilter::unescape($args['description']));
    $item['keywords'] = tcontentfilter::escape(tcontentfilter::unescape($args['keywords']));
    
    $this->callevent('onprops', array(&$item));
    
    $item = $files->escape($item);
    $files->db->updateassoc($item);
    return array(
    'item' => $item
    );
  }
  
  public function canupload() {
    if (!litepublisher::$options->hasgroup('author')) return false;
    
    if (in_array(litepublisher::$options->groupnames['author'], litepublisher::$options->idgroups)
    && ($err = tauthor_rights::i()->canupload())) {
      return false;
    }
    
    return true;
  }
  
  public function files_upload(array $args) {
    if ( 'POST' != $_SERVER['REQUEST_METHOD']) return $this->forbidden();
    if (!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name']) ||
    $_FILES['Filedata']['error'] != 0) return $this->forbidden();
    
    //psevdo logout
    litepublisher::$options->user = null;
    if (!$this->canupload()) return $this->forbidden();
    
    $parser = tmediaparser::i();
    $id = $parser->uploadfile($_FILES['Filedata']['name'], $_FILES['Filedata']['tmp_name'], '', '', '', false);
    if (isset($_POST['idperm'])) {
      $idperm = (int) $_POST['idperm'];
      if ($idperm > 0) tprivatefiles::i()->setperm($id, (int) $_POST['idperm']);
    }
    
    $this->uploaded($id);
    
    $files = tfiles::i();
    $item = $files->db->getitem($id);
    $files->items[$id] = $item;
    
    $result = array(
    'id' => $id,
    'item' => $item
    );
    
    if ((int) $item['preview']) {
      $result['preview'] = $files->db->getitem($item['preview']);
    }
    
    if ((int) $item['midle']) {
      $result['midle'] = $files->db->getitem($item['midle']);
    }
    
    return $result;
  }
  
}//class