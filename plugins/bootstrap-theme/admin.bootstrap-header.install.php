<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function admin_bootstrap_headerInstall($self) {
    $about = tplugins::getabout(tplugins::getname(__file__));

    $admin = tadminmenus::i();
    $admin->additem(array(
        'parent' => $admin->url2id('/admin/views/') ,
        'url' => '/admin/views/bootstrapheader/',
        'title' => $about['header'],
        'name' => 'bootstrapheader',
        'class' => get_class($self) ,
        'group' => 'admin'
    ));
}

function admin_bootstrap_headerUninstall($self) {
    tadminmenus::i()->deleteurl('/admin/views/bootstrapheader/');
}