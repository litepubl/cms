<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

function pollsInstall($self) {
  $name = basename(dirname(__file__));
  $res = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;

  $manager = tdbmanager::i();
  $manager->createtable($self->table, file_get_contents($res . 'polls.sql'));
  $manager->createtable(polls::votes, file_get_contents($res . 'votes.sql'));

  tjsonserver::i()->addevent('polls_sendvote', get_class($self) , 'polls_sendvote');

  $js = tjsmerger::i();
  $js->lock();

  $css = tcssmerger::i();
  $css->lock();

  tplugins::i()->add('ulogin');
  $js->add('default', '/plugins/polls/resource/polls.min.js');
  $js->add('default', '/plugins/polls/resource/' . litepublisher::$options->language . '.polls.min.js');

  $css->add('default', 'plugins/polls/resource/polls.min.css');
  $css->unlock();
  $js->unlock();

  $parser = tthemeparser::i();
  $parser->addtags('plugins/polls/resource/theme.txt', 'plugins/polls/resource/themetags.ini');

  tlocalmerger::i()->addplugin($name);
  tcron::i()->addnightly(get_class($self) , 'optimize', null);
  tposts::i()->deleted = $self->postdeleted;
}

function pollsUninstall($self) {
  tjsonserver::i()->unbind($self);
  tlocalmerger::i()->deleteplugin(tplugins::getname(__file__));

  $js = tjsmerger::i();
  $js->lock();

  $css = tcssmerger::i();
  $css->lock();

  tplugins::i()->delete('ulogin');

  $js->deletefile('default', '/plugins/polls/resource/polls.min.js');
  $js->deletefile('default', '/plugins/polls/resource/' . litepublisher::$options->language . '.polls.min.js');

  $css->deletefile('default', 'plugins/polls/resource/polls.min.css');
  $css->unlock();
  $js->unlock();

  $parser = tthemeparser::i();
  $parser->removetags('plugins/polls/resource/theme.txt', 'plugins/polls/resource/themetags.ini');

  $manager = tdbmanager::i();
  $manager->deletetable($self->table);
  $manager->deletetable(polls::votes);

  tcron::i()->deleteclass(get_class($self));
  tposts::i()->unbind($self);
}