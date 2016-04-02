<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function toldestpostsInstall($self) {
  $widgets = twidgets::i();
  $widgets->addclass($self, 'tpost');
}