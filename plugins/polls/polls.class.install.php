<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

function tpollsInstall($self) {
  $name = basename(dirname(__file__));
  $res = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;

  $manager = tdbmanager::i();
  $manager->createtable($self->table, file_get_contents($res . 'polls.sql'));
  $manager->createtable(tpolls::votes, file_get_contents($res . 'votes.sql'));

  tjsonserver::i()->addevent('polls_sendvote', get_class($self) , 'polls_sendvote');

  $js = tjsmerger::i();
  $js->lock();
  $js->add('default', '/plugins/polls/resource/polls.min.js');
  $js->add('default', '/plugins/polls/resource/' . litepublisher::$options->language . '.polls.min.js');
  $js->unlock();

  tcssmerger::i()->add('default', 'plugins/polls/resource/polls.min.css');

  $parser = tthemeparser::i();
  $parser->lock();
  $parser->add_tagfile('plugins/polls/resource/themetags.ini');
  $parser->themefiles[] = 'plugins/polls/resource/theme.txt';
  $parser->unlock();

  tlocalmerger::i()->addplugin($name);
  $lang = tlocal::admin('poll');
  $lang = tlocal::admin('polls');
  
  litepublisher::$classes->add('tadminpolloptions', 'admin.polloptions.class.php', $name);
  litepublisher::$classes->add('tadminpolls', 'admin.polls.class.php', $name);

  $adminmenus = tadminmenus::i();
  $adminmenus->lock();

  $parent = $adminmenus->createitem($adminmenus->url2id('/admin/plugins/') , 'polls', 'editor', 'tadminpolls');
  $adminmenus->items[$parent]['title'] = $lang->polls;

  $idmenu = $adminmenus->createitem($parent, 'options', 'admin', 'tadminpolloptions');
  $adminmenus->items[$idmenu]['title'] = $lang->options;

  $adminmenus->unlock();
}

function tpollsUninstall($self) {
  tcssmerger::i()->deletefile('default', 'plugins/polls/resource/polls.min.css');

  tjsonserver::i()->unbind($self);
  tlocalmerger::i()->deleteplugin(tplugins::getname(__file__));

  $js= tjsmerger::i();
  $js->lock();
  $js->deletefile('default', '/plugins/polls/resource/polls.min.js');
  $js->deletefile('default', '/plugins/polls/resource/' . litepublisher::$options->language . '.polls.min.js');
  $js->unlock();

  $parser = tthemeparser::i();
  $parser->lock();
  $parser->delete_tagfile('plugins/polls/resource/themetags.ini');
  array_delete_value($parser->themefiles, 'plugins/polls/resource/theme.txt');
  $parser->unlock();

  $adminmenus = tadminmenus::i();
  $adminmenus->deletetree($adminmenus->url2id('/admin/plugins/polls/'));

  litepublisher::$classes->delete('tadminpolloptions');
  litepublisher::$classes->delete('tadminpolls');

  $manager = tdbmanager::i();
  $manager->deletetable($self->table);
  $manager->deletetable(tpolss::votes);
}