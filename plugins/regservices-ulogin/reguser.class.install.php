<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl\plugins;
use litepubl;

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