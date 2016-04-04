<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function install_engine($email, $language) {
  //forward create folders
  @mkdir(litepubl::$paths->data . 'themes', 0777);
  @chmod(litepubl::$paths->data . 'themes', 0777);

  $options = toptions::i();
  $options->lock();
  require_once (dirname(__file__) . DIRECTORY_SEPARATOR . 'options.class.install.php');
  $password = installoptions($email, $language);
  //require_once(dirname(__file__) . DIRECTORY_SEPARATOR. 'local.class.install.php');
  //tlocalInstall(getinstance('tlocal'));
  installClasses();
  $options->unlock();
  return $password;
}

function parse_classes_ini($inifile) {
  $install_dir = litepubl::$paths->lib . 'install/ini/';
  if (!$inifile) {
    $inifile = $install_dir . 'classes.ini';
  } elseif (file_exists($install_dir . $inifile)) {
    $inifile = $install_dir . $inifile;
  } elseif (file_exists(litepubl::$paths->home . $inifile)) {
    $inifile = litepubl::$paths->home . $inifile;
  } elseif (!file_exists($inifile)) {
    $inifile = $install_dir . 'classes.ini';
  }

  $ini = parse_ini_file($inifile, true);
  $classes = litepubl::$classes;
  foreach ($ini['items'] as $class => $filename) {
    $classes->items[$class] = "lib/$filename";
}

$kernel = parse_ini_file(litepubl::$paths->lib . 'install/ini/kernel.ini', false);
  foreach ($kernel as $class => $filename) {
    $classes->kernel[$class] = "lib/$filename";
}

  $classes->classes = $ini['classes'];
  $classes->factories = $ini['factories'];
  $classes->Save();
}

function installClasses() {
  litepubl::$urlmap = turlmap::i();
  litepubl::$urlmap->lock();
  $posts = tposts::i();
  $posts->lock();
  $js = tjsmerger::i();
  $js->lock();

  $css = tcssmerger::i();
  $css->lock();

  $xmlrpc = TXMLRPC::i();
  $xmlrpc->lock();
  ttheme::$defaultargs = array();
  $theme = ttheme::getinstance('default');
  foreach (litepubl::$classes->items as $class => $item) {
    if (preg_match('/^(titem|titem_storage|titemspostsowner|tcomment|IXR_Client|IXR_Server|tautoform|tchildpost|tchildposts|cachestorage_memcache|thtmltag|ECancelEvent)$/', $class)) continue;

//ignore interfaces and traits
if (class_exists('litepubl\\' . $class)) {
//echo "$class<br>";
    $obj = getinstance('litepubl\\' . $class);
    if (method_exists($obj, 'install')) {
$obj->install();
}
}
  }

  //default installed plugins
  $plugins = tplugins::i();
  $plugins->lock();
  $plugins->add('likebuttons');
  $plugins->add('oldestposts');
  $plugins->add('photoswipe');
  $plugins->add('photoswipe-thumbnail');
  $plugins->add('bootstrap-theme');
  $plugins->unlock();

  $xmlrpc->unlock();
  $css->unlock();
  $js->unlock();
  $posts->unlock();
  litepubl::$urlmap->unlock();
}