<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdboptimizer extends tevents {
  public $childtables;
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->basename = 'db.optimizer';
    $this->addmap('childtables', array());
    $this->addevents('postsdeleted');
  }
  
  public function garbageposts($table) {
    $db = litepublisher::$db;
    $deleted = $db->res2id($db->query("select id from $db->prefix$table where id not in
    (select $db->posts.id from $db->posts)"));
    if (count($deleted) > 0) {
      $db->table = $table;
      $db->deleteitems($deleted);
    }
  }
  
  public function deletedeleted() {
    //posts
    $db = litepublisher::$db;
    $db->table = 'posts';
    $items = $db->idselect("status = 'deleted'");
    if (count($items) > 0) {
      $this->postsdeleted($items);
      $deleted = sprintf('id in (%s)', implode(',', $items));
      $db->exec("delete from $db->urlmap where id in
      (select idurl from $db->posts where $deleted)");
      
      foreach (array('posts', 'rawposts', 'pages', 'postsmeta')  as $table) {
        $db->table = $table;
        $db->delete($deleted);
      }
      
      foreach ($this->childtables as $table) {
        $db->table = $table;
        $db->delete($deleted);
      }
    }
    
    //comments
    $db->table = 'comments';
    $items = $db->idselect("status = 'deleted'");
    if (count($items)) {
      $deleted = sprintf('id in (%s)', implode(',', $items));
      $db->delete($deleted);
      $db->table = 'rawcomments';
      $db->delete($deleted);
    }
    
    $items = $db->res2id($db->query("select $db->users.id FROM $db->users
    LEFT JOIN $db->comments ON $db->users.id=$db->comments.author
    WHERE $db->users.status = 'comuser' and $db->comments.author IS NULL"));
    
    if (count($items)) {
      $db->table = 'users';
      $db->delete(sprintf('id in(%s)', implode(',', $items)));
    }
    
    $items = $db->res2id($db->query("select $db->subscribers.post FROM $db->subscribers
    LEFT JOIN $db->posts ON $db->subscribers.post = $db->posts.id
    WHERE $db->posts.id IS NULL"));
    
    if (count($items)) {
      $db->table = 'subscribers';
      $db->delete(sprintf('post in(%s)', implode(',', $items)));
    }
    
    
    $items = $db->res2id($db->query("select $db->subscribers.item FROM $db->subscribers
    LEFT JOIN $db->users ON $db->subscribers.item = $db->users.id
    WHERE $db->users.id IS NULL"));
    
    if (count($items)) {
      $db->table = 'subscribers';
      $db->delete(sprintf('item in(%s)', implode(',', $items)));
    }
    
  }
  
  public function optimize() {
    $this->deletedeleted();
    sleep(2);
    $man = tdbmanager::i();
    $man->optimize();
  }
  
}//class