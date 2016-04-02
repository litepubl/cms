<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tuseroptionsInstall($self) {
  $self->defvalues['subscribe'] = 'enabled';
  if (isset(litepublisher::$options->defaultsubscribe)) $self->defvalues['subscribe'] = litepublisher::$options->defaultsubscribe ? 'enabled' : 'disabled';
  $self->defvalues['authorpost_subscribe'] = 'enabled';
  $self->save();

  $manager = tdbmanager::i();
  $manager->CreateTable($self->table, file_get_contents(dirname(__file__) . '/sql/user.options.sql'));
}

function tuseroptionsUninstall($self) {
  tdbmanager::i()->deletetable($self->table);
}