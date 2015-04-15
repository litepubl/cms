<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function treguserInstall($self) {
  litepublisher::$classes->remap['tregserviceuser'] = get_class($self);
  litepublisher::$classes->save();
  
  $items = $self->getdb('regservices')->getitems('id > 0');
  $db = $self->db;
  foreach ($items as $item) {
    $db->insert($item);
  }
}

function treguserUninstall($self) {
  unset(litepublisher::$classes->remap['tregserviceuser']);
  litepublisher::$classes->save();
}