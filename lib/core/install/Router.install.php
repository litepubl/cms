<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\core;

function UrlmapInstall($self) {
    $manager = tdbmanager::i();
    $manager->CreateTable('urlmap', file_get_contents(dirname(__file__) . '/sql/router.sql'));
}