<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\widget;
use litepubl\pages\RobotsTxt;

function WidgetsInstall($self) {
    litepubl::$urlmap->addget('/getwidget.htm', get_class($self));
    $robot  = RobotsTxt::i();
    $robot->AddDisallow('/getwidget.htm');

    $xmlrpc = TXMLRPC::i();
    $xmlrpc->add('litepublisher.getwidget', 'xmlrpcgetwidget', get_class($self));

    install_std_widgets($self);
}

function WidgetsUninstall($self) {
    turlmap::unsub($self);
    $xmlrpc = TXMLRPC::i();
    $xmlrpc->deleteclass(get_class($self));
}

function install_std_widgets($widgets) {
    $widgets->lock();
    $sidebars = Sidebars::i();

    $id = $widgets->add(Cats::i());
    $sidebars->insert($id, 'inline', 0, -1);

    $id = $widgets->add(Tags::i());

    $id = $widgets->add(Archives::i());
    $sidebars->insert($id, 'inline', 0, -1);

    $id = $widgets->add(Links::i());
    $sidebars->insert($id, 'inline', 0, -1);

    $id = $widgets->add(Posts::i());
    $sidebars->insert($id, 'inline', 1, -1);

    $id = $widgets->add(Comments::i());
    $sidebars->insert($id, true, 1, -1);

    $id = $widgets->add(Meta::i());
    $sidebars->insert($id, 'inline', 1, -1);

    $widgets->unlock();
}