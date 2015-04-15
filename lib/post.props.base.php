<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tbasepostprops extends tdata {
  public $dataname;
  public $defvalues;
  public $arrayprops;
  public $intarray;
  public $intprops;
  public $boolprops;
  public $datetimeprops;
  public $allprops;
  public $types;
  
  public function __construct() {
    parent::__coonstruct();
    $this->update_all_props();
  }
  
  protected function create() {
    parent::create();
    $this->dataname = 'postprops';
    $this->table = 'posts';
    $this->defvalues = array();
    $this->arrayprops = array();
    $this->intarray = array();
    $this->intprops = array();
    $this->boolprops = array();
    $this->datetimeprops = array();
  }
  
  protected function update_all_props() {
    $this->allprops =array_keys($this->defvalues);
    $methods = get_class_methods($this);
    foreach ($methods as $name) {
      if ((strlen($name) > 3) && (strbegin($name, 'get') || strbegin($name, 'set'))) {
        if (!in_array($name, $this->allprops)) $this->allprops[] = $name;
      }
    }
    
    $this->types = array();
    foreach ($this->allprops as $name) {
      if (in_array($name, $this->intprops)) {
        $type = 'int';
      } elseif (in_array($name, $this->boolprops)) {
        $type = 'bool;
        if (in_array($name, $this->arrayprops)) {
          $type = 'array';
          if (in_array($name, $this->arrayprops)) {
            $type = 'array';
            if (in_array($name, $this->intarray)) {
              $type = 'intarray';
              if (in_array($name, $this->datetimeprops)) {
                $type = 'datetime';
              } else {
                $type = 'string';
              }
              
              $this->types[$name] = $type;
            }
          }
          
          public function get(tpost $post, $name, &$value) {
            if (!in_array($name, $this->allprops)) return false;
            
            if (!isset($post->propdata[$this->dataname])) $this->load_item($post);
            
            if (method_exists($this, $get = 'get' . $name)) {
              $value = $this->$get($post);
              return true;
            }
            
            $data = &$post->propdata[$this->dataname];
            switch($this->types[$name]) {
              case 'int':
              $value = (int) $data[$name];
              break;
              
              case 'bool':
              $value = $data[$name] == '1';
              break;
              
              case 'datetime':
              $syncdata = &$post->syncdata[$this->dataname];
              if (isset($syncdata([$name])) {
                $value = syncdata[$name];
              } else {
                $value= $data[$name] ? strtotime($data[$name]) : 0;
                $syncdata[$name] = $value;
              }
              break;
              
              case 'array':
              case 'intarray':
              $syncdata = &$post->syncdata[$this->dataname];
              if (isset($syncdata([$name])) {
                $value = syncdata[$name];
              } else {
                $value = array();
                $isint = $this->types[$name] == 'intarray';
                foreach (explode(',', $data[$name]) as $v) {
                  if ($v = trim($v)) {
                    $value[] = $iisint ? (int) $v : $v;
                  }
                }
                
                $syncdata[$name] = $value;
              }
              break;
              
              default:
              $value = $data[$name];
            }
            
            return true;
          }
          
          public function set(tpost $post, $name, $value) {
            if (!in_array($name, $this->allprops)) return false;
            if (!isset($post->propdata[$this->dataname])) $this->load_item($post);
            
            if (method_exists($this, $set = 'set' . $name))  {
              $this->$set($post, $value);
              return true;
            }
            
            $data = &$post->propdata[$this->dataname];
            switch ($this->types[$name]) {
              case 'int':
              $data[$name] = (int) $value;
              break;
              
              case 'bool':
              $data[$name] = $value ? '1' : '0';
              break;
              
              case 'datetime':
              $syncdata = &$post->syncdata[$this->dataname];
              $syncdata[$name] = $value;
              $data[$name] = $value ? sqldate($value) : '';
              break;
              
              case 'array':
              case 'intarray':
              $post->syncdata[$this->dataname][$name] = $value;
              $data[$name] = implode(',', $value);
              break;
              
              default:
              $data[$name] = $value;
            }
            
            return true;
          }
          
          public function load_item(tpost $post) {
            if ($post->id == 0) {
              $post->propdata[$this->dataname] = $this->defvalues;
              $post->syncdata[$this->dataname] = array();
            } else {
              //query items for loaded posts
              $items = array();
              foreach (tpost::$instances['post'] as $id => $post) {
                if (!isset($post->propdata[$this->dataname])) $items[] = $id;
              }
              $list = implode(',', $items);
              $db = litepublisher::$db;
              if ($res = $db->query("select * from $db->prefix$this->table where id in($list)")) {
                while ($r = $db->fetchassoc($res)) {
                  $p = tpost::i((int) $r['id']);
                  $p->propdata[$this->dataname] = $r;
                  $p->syncdata[$this->dataname] = array();
                }
              }
              
              if (!isset($post->propdata[$this->dataname])) $this->error(sprintf('The "%d" post not found in"%s" table", $post->id, $db->prefix . $this->table));
            }
          }
          
          public function add(tpost $post) {
            $this->db->insert($post->propdata[$this->dataname]);
          }
          
          public function save(tpost $post) {
            $this->db->updateassoc($post->propdata[$this->dataname]);
          }
          
        }//class