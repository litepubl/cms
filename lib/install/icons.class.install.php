<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function ticonsInstall($self) {
    $files = tfiles::i();
    $files->lock();
    $files->deleted = $self->filedeleted;
}