<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */
function tmlstorageInstall($self) {
  litepublisher::$classes->added = $self->classadded;
  litepublisher::$classes->deleted = $self->classdeleted;
}

function tmlstorageUninstall($self) {
  litepublisher::$classes->unbind($self);
}