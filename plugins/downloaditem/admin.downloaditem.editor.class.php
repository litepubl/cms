<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tdownloaditemeditor extends tposteditor {
  
  public static function i($id = 0) {
    return parent::iteminstance(__class__, $id);
  }
  
  public function gettitle() {
    if ($this->idpost == 0){
      return parent::gettitle();
    } else {
      return tlocal::admin('downloaditems')->editor;
    }
  }
  
  public function getcontent() {
    $result = '';
    $this->basename = 'downloaditems';
    $html = $this->inihtml();
    $lang = tlocal::admin('downloaditems');
    $lang->ini['downloaditems'] = $lang->ini['downloaditem'] + $lang->ini['downloaditems'];
    $html->push_section('editor');
    
    $downloaditem = tdownloaditem::i($this->idpost);
    ttheme::$vars['downloaditem'] = $downloaditem;
    $args = new targs();
    $this->getpostargs($downloaditem, $args);
    $html->pop_section();
    $args->downloadurl = $downloaditem->downloadurl;
    $args->authorname = $downloaditem->authorname;
    $args->authorurl = $downloaditem->authorurl;
    $args->version = $downloaditem->version;
    
    
    $types = array(
    'theme' => tlocal::get('downloaditem', 'theme'),
    'plugin' => tlocal::get('downloaditem', 'plugin')
    );
    
    $args->type = tadminhtml::array2combo($types, $downloaditem->type);
    
    if ($downloaditem->id > 0) $result .= $html->headeditor ();
    $result .= $html->form($args);
    $result = $html->fixquote($result);
    unset(ttheme::$vars['downloaditem']);
    return $result;
  }
  
  public function processform() {
    /*
    echo "<pre>\n";
    var_dump($_POST);
    echo "</pre>\n";
    return;
    */
    extract($_POST, EXTR_SKIP);
    $this->basename = 'downloaditems';
    $html = $this->html;
    $lang = tlocal::i('editor');
    if (empty($_POST['title'])) return $html->h2->emptytitle;
    $downloaditem = tdownloaditem::i((int)$id);
    $this->set_post($downloaditem);
    $downloaditem->version = $version;
    $downloaditem->type = $type;
    $downloaditem->downloadurl = $downloadurl;
    $downloaditem->authorname = $authorname;
    $downloaditem->authorurl = $authorurl;
    $downloaditems = tdownloaditems::i();
    if ($downloaditem->id == 0) {
      $id = $downloaditems->add($downloaditem);
      $_GET['id'] = $id;
      $_POST['id'] = $id;
    } else {
      $downloaditems->edit($downloaditem);
    }
    $lang = tlocal::i('downloaditems');
    return $html->h2->successedit;
  }
  
}//class