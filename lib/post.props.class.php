<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tpostprops extends tbasepostprops {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  protected function create() {
    parent::create();
    $this->dataname = 'post';
    $this->table = 'posts';
    $this->defvalues = array(
    'id' => 0,
    'idview' => 1,
    'idurl' => 0,
    'parent' => 0,
    'author' => 0,
    'revision' => 0,
    'icon' => 0,
    'idperm' => 0,
    'class' => 'tpost',,
    'posted' => 0,
    'modified' => 0,
    'url' => '',
    'title' => '',
    'title2' => '',
    'filtered' => '',
    'excerpt' => '',
    'rss' => '',
    'rawcontent' => false,
    'keywords' => '',
    'description' => '',
    'rawhead' => '',
    'moretitle' => '',
    'categories' => array(),
    'tags' => array(),
    'files' => array(),
    'status' => 'published',
    'comstatus' => litepublisher::$options->comstatus,
    'pingenabled' => litepublisher::$options->pingenabled,
    'password' => '',
    'commentscount' => 0,
    'pingbackscount' => 0,
    'pagescount' => 0,
    );
    
    $this->intarray = array('categories', 'tags', 'files');
    $this->intprops= array('id', 'idurl', 'parent', 'author', 'revision', 'icon', 'commentscount', 'pingbackscount', 'pagescount', 'idview', 'idperm');
    $this->boolprops= array('pingenabled');
    $this->datetimeprops = array('posted');
  }
  
}//class