<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tlocalInstall($self) {
  tlocal::$self = $self;
  //check double install
  if (count($self->ini) > 0) return;
  preloadlanguage($self, litepublisher::$options->language);
  litepublisher::$options->timezone = tlocal::get('installation', 'timezone');
}

function tlocalPreinstall($language) {
  $lang = new tlocal();
  tlocal::$self = $lang;
  litepublisher::$classes->instances['tlocal'] = $lang;
  preloadlanguage($lang, $language);
}

function preloadlanguage($lang, $language) {
  $dir = litepublisher::$paths->languages . $language . DIRECTORY_SEPARATOR;
  foreach (array(
    'default',
    'admin',
    'install'
  ) as $name) {
    $ini = parse_ini_file($dir . $name . '.ini', true);
    $lang->ini = $ini + $lang->ini;
    $lang->loaded[] = $name;
  }
  date_default_timezone_set(tlocal::get('installation', 'timezone'));
}