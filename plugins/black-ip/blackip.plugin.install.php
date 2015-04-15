<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tblackipInstall($self) {
  tcommentmanager::i()->oncreatestatus = $self->filter;
}

function tblackipUninstall($self) {
  tcommentmanager::i()->unbind($self);
}