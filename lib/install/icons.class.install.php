<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function ticonsInstall($self) {
  $files = tfiles::i();
  $files->lock();
  $files->deleted = $self->filedeleted;
}