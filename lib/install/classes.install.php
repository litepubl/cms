<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function install_engine($email, $language) {
  //forward create folders
  @mkdir(litepublisher::$paths->data . 'themes', 0777);
  @chmod(litepublisher::$paths->data . 'themes', 0777);
  
  $options = toptions::i();
  $options->lock();
  require_once(dirname(__file__) . DIRECTORY_SEPARATOR. 'options.class.install.php');
  $password = installoptions($email, $language);
  //require_once(dirname(__file__) . DIRECTORY_SEPARATOR. 'local.class.install.php');
  //tlocalInstall(getinstance('tlocal'));
  installClasses();
  $options->unlock();
  return $password;
}

function parse_classes_ini($inifile) {
  $install_dir = litepublisher::$paths->lib.'install' . DIRECTORY_SEPARATOR;
  if (!$inifile) {
    $inifile = $install_dir . 'classes.ini';
  } elseif(file_exists($install_dir . $inifile)) {
    $inifile = $install_dir . $inifile;
  } elseif(file_exists(litepublisher::$paths->home . $inifile)) {
    $inifile = litepublisher::$paths->home . $inifile;
  } elseif(!file_exists($inifile)) {
    $inifile = $install_dir . 'classes.ini';
  }
  
  $ini = parse_ini_file($inifile, true);
  
  $classes = litepublisher::$classes;
  $replace = dbversion ? '.class.db.' : '.class.files.';
  $exclude = !dbversion ? '.class.db.' : '.class.files.';
  foreach ($ini['items'] as $class => $filename) {
    //exclude files
    if (strpos($filename, $exclude)) continue;
    if (!file_exists(litepublisher::$paths->lib . $filename)){
      $filename = str_replace('.class.', $replace, $filename);
      if (!file_exists(litepublisher::$paths->lib . $filename))continue;
    }
    
    $item = array($filename, '');
    
    if (isset($ini['debug'][$class])) {
      $filename = $ini['debug'][$class];
      if (file_exists(litepublisher::$paths->lib . $filename)){
        $item[2] = $filename;
      } else {
        $filename = str_replace('.class.', $replace, $filename);
        if (file_exists(litepublisher::$paths->lib . $filename)){
          $item[2] = $filename;
        }
      }
    }
    
    $classes->items[$class] = $item;
  }
  
  $classes->classes = $ini['classes'];
  $classes->interfaces = $ini['interfaces'];
  $classes->factories = $ini['factories'];
  $classes->Save();
}

function installClasses() {
  litepublisher::$urlmap = turlmap::i();
  litepublisher::$urlmap->lock();
  $posts = tposts::i();
  $posts->lock();
  
  $xmlrpc = TXMLRPC::i();
  $xmlrpc->lock();
  ttheme::$defaultargs = array();
  $theme = ttheme::getinstance('default');
  //  $html = tadminhtml::i();
  //      $html->loadinstall();
  
  foreach(litepublisher::$classes->items as $class => $item) {
    //echo "$class<br>\n";
    if (preg_match('/^(titem|titem_storage|titemspostsowner|tcomment|IXR_Client|IXR_Server|tautoform|tchildpost|tchildposts|tlitememcache)$/', $class)) continue;
    $obj = getinstance($class);
    if (method_exists($obj, 'install')) $obj->install();
  }
  
  $xmlrpc->unlock();
  $posts->unlock();
  litepublisher::$urlmap->unlock();
}

?>