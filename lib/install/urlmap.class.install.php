<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

function turlmapInstall($self) {
    $manager = tdbmanager::i();
    $manager->CreateTable('urlmap', file_get_contents(dirname(__file__) . '/sql/urlmap.sql'));
}