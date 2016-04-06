<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tpluginsInstall($self) {
    @mkdir(litepubl::$paths->data . 'plugins', 0777);
    @chmod(litepubl::$paths->data . 'plugins', 0777);
}