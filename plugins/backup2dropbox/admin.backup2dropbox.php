<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2013 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminbackup2dropbox {
  
  public function getcontent() {
    $plugin = tbackup2dropbox::i();
    $html = tadminhtml::i();
    $args = targs::i();
    $about = tplugins::getabout(tplugins::getname(__file__));
    tlocal::admin()->ini['dropbox'] = $about;
    $lang = tlocal::i('dropbox');
    $args->add($plugin->data);
    $args->formtitle = $about['head'];
    $form = $html->adminform('[text=email] [password=password] [text=dir] [checkbox=uploadfiles] [checkbox=onlychanged]] [checkbox=useshell]', $args);
    $form .= '<form name="createnowform" action="" method="post" >
    <input type="hidden" name="createnow" value="1" />
    <p><input type="submit" name="create_now" value="' . $lang->createnow . '"/></p>
    </form>';
    
    return $form;
  }
  
  public function processform() {
    $plugin = tbackup2dropbox::i();
    if (!isset($_POST['createnow'])) {
      extract($_POST, EXTR_SKIP);
      $plugin->lock();
      $plugin->email = $email;
      $plugin->password = $password;
      $plugin->dir = $dir;
      $plugin->uploadfiles = isset($uploadfiles);
      $plugin->onlychanged = isset($onlychanged);
      $plugin->useshell = isset($useshell);
      $plugin->unlock();
      return '';
    } else {
      $r = $plugin->send() ;
      if ($r === true)$r = 'Uploaded';
      return sprintf('<h2>%s</h2>', $r);
    }
  }
  
}//class