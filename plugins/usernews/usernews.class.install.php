<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

function tusernewsInstall($self) {
  $name = basename(dirname(__file__));
  $self->data['dir'] = $name;
  $self->save();

  tlocalmerger::i()->addplugin($name);

  $filter = tcontentfilter::i();
  $filter->phpcode = true;
  $filter->save();

  litepublisher::$options->parsepost = false;
  litepublisher::$options->reguser = true;
  $adminoptions = tadminoptions::i();
  $adminoptions->usersenabled = true;

  $groups = tusergroups::i();
  $groups->defaults = array(
    $groups->getidgroup('author')
  );
  $groups->save();

  $rights = tauthor_rights::i();
  $rights->lock();
  $rights->gethead = $self->gethead;
  $rights->getposteditor = $self->getposteditor;
  $rights->editpost = $self->editpost;
  $rights->changeposts = $self->changeposts;
  $rights->canupload = $self->canupload;
  $rights->candeletefile = $self->candeletefile;
  $rights->unlock();
}

function tusernewsUninstall($self) {
  tauthor_rights::i()->unbind($self);
  tlocalmerger::i()->deleteplugin(basename(dirname(__file__)));
}