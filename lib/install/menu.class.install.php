<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tmenusInstall($self) {
  @mkdir(litepublisher::$paths->data . 'menus', 0777);
  if (get_class($self) != 'tmenus') return;
  @chmod(litepublisher::$paths->data . 'menus', 0777);
}

function  tmenusUninstall($self) {
  //rmdir(. 'menus');
}
function tmenusGetsitemap($self, $from, $count) {
  $result = array();
  foreach ($self->items as $id => $item) {
    if ($item['status'] == 'draft') continue;
    $result[] = array(
    'url' => $item['url'],
    'title' => $item['title'],
    'pages' => 1
    );
  }
  return $result;
}