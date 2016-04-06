<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tcommentswidgetInstall($self) {
    tcomments::i()->changed = $self->changed;
}

function tcommentswidgetUninstall($self) {
    tcomments::i()->unbind($self);
}