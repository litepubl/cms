<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tuseroptionsInstall($self) {
  $self->defvalues['subscribe'] = 'enabled';
  if (isset(litepublisher::$options->defaultsubscribe)) $self->defvalues['subscribe'] = litepublisher::$options->defaultsubscribe ? 'enabled' : 'disabled';
  $self->defvalues['authorpost_subscribe'] = 'enabled';
  $self->save();
  
  $manager = tdbmanager ::i();
  $manager->CreateTable($self->table, file_get_contents(dirname(__file__) . DIRECTORY_SEPARATOR . 'user.options.sql'));
}

function tuseroptionsUninstall($self) {
  tdbmanager ::i()->deletetable($self->table);
}