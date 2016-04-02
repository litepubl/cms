<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

litepubl::$classes = tclasses::i();
litepubl::$options = toptions::i();
litepubl::$site = tsite::i();

if (!defined('litepublisher_mode')) {
  define('litepublisher_mode', 'install');
}

/*
if (litepubl::$debug) {
  require_once(litepubl::$paths->lib . 'filer.class.php');
  if (is_dir(litepubl::$paths->data)) tfiler::delete(litepubl::$paths->data, true, true);
}
*/

require_once (litepubl::$paths->lib . 'installer.class.php');

$installer = new tinstaller();
$installer->install();

if (litepubl::$options instanceof toptions) {
  litepubl::$options->savemodified();
  if (!empty(litepubl::$options->errorlog)) {
    echo litepubl::$options->errorlog;
  }
}

exit();