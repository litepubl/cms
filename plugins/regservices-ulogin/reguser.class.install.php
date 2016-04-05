<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function treguserInstall($self) {
    litepubl::$classes->remap['tregserviceuser'] = get_class($self);
    litepubl::$classes->save();

    $items = $self->getdb('regservices')->getitems('id > 0');
    $db = $self->db;
    foreach ($items as $item) {
        $db->insert($item);
    }
}

function treguserUninstall($self) {
    unset(litepubl::$classes->remap['tregserviceuser']);
    litepubl::$classes->save();
}