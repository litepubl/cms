<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tposttransform  {
  public $post;
  public static $arrayprops= array('categories', 'tags', 'files');
  public static $intprops= array('id', 'idurl', 'parent', 'author', 'revision', 'icon', 'commentscount', 'pingbackscount', 'pagescount', 'idview', 'idperm');
  public static $boolprops= array('pingenabled');
  public static $props = array('id', 'idurl', 'parent', 'author', 'revision', 'class',
  //'created', 'modified',
  'posted',
  'title', 'title2', 'filtered', 'excerpt', 'rss', 'keywords', 'description', 'rawhead', 'moretitle',
  'categories', 'tags', 'files',
  'password', 'idview', 'idperm', 'icon',
  'status', 'comstatus', 'pingenabled',
  'commentscount', 'pingbackscount', 'pagescount',
  );
  
  public static function i(tpost $post) {
    $self = getinstance(__class__);
    $self->post = $post;
    return $self;
  }
  
  public static function add(tpost $post) {
    $self = self::i($post);
    $values = array();
    foreach (self::$props as $name) {
      $values[$name] = $self->__get($name);
    }
    $db = $post->db;
    $id = $db->add($values);
    $post->rawdb->insert(array(
    'id' => $id,
    'created' => sqldate(),
    'modified' => sqldate(),
    'rawcontent' => $post->data['rawcontent']
    ));
    
    $db->table = 'pages';
    foreach ($post->data['pages'] as $i => $content) {
      $db->insert(array('id' => $id, 'page' => $i,         'content' => $content));
    }
    
    return $id;
  }
  
  public function save() {
    $post = $this->post;
    $db = $post->db;
    $list = array();
    foreach (self::$props  As $name) {
      if ($name == 'id') continue;
      $list[] = "$name = " . $db->quote($this->__get($name));
    }
    
    $db->idupdate($post->id, implode(', ', $list));
    
    $raw = array(
    'id' => $post->id,
    'modified' => sqldate()
    );
    if (false !== $post->data['rawcontent']) $raw['rawcontent'] = $post->data['rawcontent'];
    $post->rawdb->updateassoc($raw);
    /*
    $db->table = 'pages';
    $db->iddelete($post->id);
    foreach ($post->data['pages'] as $i => $content) {
      $db->insert(array('id' => $post->id, 'page' => $i, 'content' => $content));
    }
    */
  }
  
  public function setassoc(array $a) {
    foreach ($a as $k => $v) $this->__set($k, $v);
  }
  
  public function __get($name) {
    if ('pagescount' == $name) return $this->post->data[$name];
    if (method_exists($this, $get = "get$name")) return $this->$get();
    if (in_array($name, self::$arrayprops))  return implode(',', $this->post->$name);
    if (in_array($name, self::$boolprops))  return $this->post->$name ? 1 : 0;
    return $this->post->$name;
  }
  
  public function __set($name, $value) {
    if (method_exists($this, $set = "set$name")) return $this->$set($value);
    if (in_array($name, self::$arrayprops)) {
      $this->post->data[$name] = tdatabase::str2array($value);
    } elseif (in_array($name, self::$intprops)) {
      $this->post->$name = (int) $value;
    } elseif (in_array($name, self::$boolprops)) {
      $this->post->data[$name] = $value == '1';
    } else {
      $this->post->$name = $value;
    }
  }
  
  private function getposted() {
    return sqldate($this->post->posted);
  }
  
  private function setposted($value) {
    $this->post->posted = strtotime($value);
  }
  
  private function setrevision($value) {
    $this->post->data['revision'] = $value;
  }
  
}//class