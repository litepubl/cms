<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

litepublisher::$classes = tclasses::i();
litepublisher::$options = toptions::i();
litepublisher::$site = tsite::i();

if (!defined('litepublisher_mode')) define('litepublisher_mode', 'install');
/*
if (litepublisher::$debug) {
  require_once(litepublisher::$paths->lib . 'filer.class.php');
  if (is_dir(litepublisher::$paths->data)) tfiler::delete(litepublisher::$paths->data, true, true);
}
*/

require_once(litepublisher::$paths->lib . 'installer.class.php');
$installer = new tinstaller();
$installer->install();

if (litepublisher::$options instanceof toptions) {
  litepublisher::$options->savemodified();
  if (!empty(litepublisher::$options->errorlog)) {
    echo litepublisher::$options->errorlog;
  }
}
exit();