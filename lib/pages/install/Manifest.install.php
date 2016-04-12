<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\pages;

function ManifestInstall($self) {
    litepubl::$urlmap->add('/wlwmanifest.xml', get_class($self) , 'manifest');
    litepubl::$urlmap->add('/rsd.xml', get_class($self) , 'rsd');
}

function ManifestUninstall($self) {
    litepubl::$router->unbind($self);
}