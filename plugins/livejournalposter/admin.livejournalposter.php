<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

class tadminlivejournalposter {
  
  public static function i() {
    return getinstance(__class__);
  }
  
  public function getcontent() {
    $plugin = tlivejournalposter::i();
    $dir = dirname(__file__) . DIRECTORY_SEPARATOR;
    if ($plugin->template == '') $plugin->template = file_get_contents($dir. 'livejournalposter.tml');
    $about = tplugins::getabout(tplugins::getname(__file__));
    $lang = tplugins::getlangabout(__file__);
    $html = tadminhtml::i();
    $html->section = $lang->section;
    $args = targs::i();
    $args->add($about);
    $args->add($plugin->data);
    $args->public = 'public' == $plugin->privacy;
    $args->private = 'private' == $plugin->privacy;
    $args->friends = 'friends' == $plugin->privacy;
    return $html->adminform('[text=host] [text=login] [password=password] [text=community]
    <p><strong>$lang.privacy</strong>
    <label><input name="privacy" type="radio" value="public" $public/>$lang.public</label>
    <label><input name="privacy" type="radio" value="private" $private />$lang.private</label>
    <label><input name="privacy" type="radio" value="frinds" $friends/>$lang.friends</label>
    </p>
    
    [editor=template]', $args);
  }
  
  public function processform() {
    extract($_POST, EXTR_SKIP);
    $plugin = tlivejournalposter::i();
    $plugin->lock();
    $plugin->host = $host;
    $plugin->login = $login;
    $plugin->password = $password;
    $plugin->community = $community;
    $plugin->privacy = $privacy;
    $plugin->template = $template;
    $plugin->unlock();
    return '';
  }
  
}