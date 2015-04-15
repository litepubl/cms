<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tcommentswidgetInstall($self) {
  tcomments::i()->changed = $self->changed;
}

function tcommentswidgetUninstall($self) {
  tcomments::i()->unbind($self);
}