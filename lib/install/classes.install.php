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
  $install_dir = litepubl::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'ini' . DIRECTORY_SEPARATOR;
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
  $replace = dbversion ? '.class.db.' : '.class.files.';
  $exclude = !dbversion ? '.class.db.' : '.class.files.';
  foreach ($ini['items'] as $class => $filename) {
    //exclude files
    if (strpos($filename, $exclude)) continue;

    if (!file_exists(litepubl::$paths->lib . $filename)) {
      $filename = str_replace('.class.', $replace, $filename);
      if (!file_exists(litepubl::$paths->lib . $filename)) continue;
    }

    $item = array(
      $filename,
      ''
    );

    if (isset($ini['debug'][$class])) {
      $filename = $ini['debug'][$class];
      if (file_exists(litepubl::$paths->lib . $filename)) {
        $item[2] = $filename;
      } else {
        $filename = str_replace('.class.', $replace, $filename);
        if (file_exists(litepubl::$paths->lib . $filename)) {
          $item[2] = $filename;
        }
      }
    }

    $classes->items['litepubl\\' . $class] = $item;
  }

  $classes->classes = $ini['classes'];
  $classes->interfaces = $ini['interfaces'];
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
    $obj = getinstance($class);
    if (method_exists($obj, 'install')) $obj->install();
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