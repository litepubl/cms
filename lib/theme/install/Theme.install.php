<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\theme;
use litepubl\core\litepubl;

function ThemeInstall($self) {
    $dir = litepubl::$paths->data . 'themes';
    if (!is_dir($dir)) {
        mkdir($dir, 0777);
        chmod($dir, 0777);
    }
    $self->name = 'default';
    $self->parsetheme();
}