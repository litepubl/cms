<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\post;

function MetaInstall($self) {
        $dir = dirname(__file__) . '/sql/';
        $manager = $self->db->man;
        $manager->CreateTable($self->table, file_get_contents($dir . 'meta.sql'));
}

function MetaUninstall($self) {
}