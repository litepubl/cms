<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
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
    $result = array();
    $where = litepublisher::$options->ingroup('editor') ? '' : ' and author = ' . litepublisher::$options->user;
    $files = tfiles::i();
    $result['count'] = (int) ceil($files->db->getcount(" parent = 0 $where") / 20);
    $result['files'] = array();
    
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
    $perpage = 20;
    $from = $page * $perpage;
    $where = litepublisher::$options->ingroup('editor') ? '' : ' where author = ' . litepublisher::$options->user;
    $files = tfiles::i();
    return array(
    'files' => $files->db->res2items($files->db->query("select * from $files->thistable $where order by id desc limit $from, $perpage"))
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
  
  public function files_upload(array $args) {
    if ( 'POST' != $_SERVER['REQUEST_METHOD']) return $this->forbidden();
    if (!isset($_FILES['Filedata']) || !is_uploaded_file($_FILES['Filedata']['tmp_name']) ||
    $_FILES['Filedata']['error'] != 0) return $this->forbidden();
    
    //psevdo logout
    litepublisher::$options->user = null;
    if (!litepublisher::$options->hasgroup('author')) return $this->forbidden();
    
    if (in_array(litepublisher::$options->groupnames['author'], litepublisher::$options->idgroups)
    && ($r = tauthor_rights::i()->canupload())) return $r;
    
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
    
    if ($item['preview'] > 0) $result['preview'] = $files->db->getitem($item['preview']);
    return $result;
  }
  
}//class