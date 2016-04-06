<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tblackipInstall($self) {
    tcommentmanager::i()->oncreatestatus = $self->filter;
}

function tblackipUninstall($self) {
    tcommentmanager::i()->unbind($self);
}