<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tmlstorageInstall($self) {
  litepublisher::$classes->added = $self->classadded;
  litepublisher::$classes->deleted = $self->classdeleted;
}

function tmlstorageUninstall($self) {
  litepublisher::$classes->unbind($self);
}