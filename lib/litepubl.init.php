<?php

if (version_compare(PHP_VERSION, '5.4', '<')) {
  die('Lite Publisher requires PHP 5.4 or later. You are using PHP ' . PHP_VERSION);
}

\class_alias('litepubl\litepubl', 'litepublisher');

try {
  litepublisher::init();
  if (litepublisher::$debug) {
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING);
    ini_set('display_errors', 1);
  }

  define('dbversion', true);
  /*
  if (class_exists('Memcache')) {
    tfilestorage::$memcache =  new Memcache;
    tfilestorage::$memcache->connect('127.0.0.1', 11211);
  }
  */

  if (!tstorage::loaddata()) {
    if (file_exists(litepublisher::$paths->data . 'storage.php') && filesize(litepublisher::$paths->data . 'storage.php')) die('Storage not loaded');
    require_once (litepublisher::$paths->lib . 'install' . DIRECTORY_SEPARATOR . 'install.php');
  }

  litepublisher::$classes = tclasses::i();
  litepublisher::$options = toptions::i();
  litepublisher::$db = tdatabase::i();
  litepublisher::$site = tsite::i();
  litepublisher::$urlmap = turlmap::i();

  if (!defined('litepublisher_mode')) {
    litepublisher::$urlmap->request(strtolower($_SERVER['HTTP_HOST']) , $_SERVER['REQUEST_URI']);
  }
}
catch(Exception $e) {
  litepublisher::$options->handexception($e);
}
litepublisher::$options->savemodified();
litepublisher::$options->showerrors();