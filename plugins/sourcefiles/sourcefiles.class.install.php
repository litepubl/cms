<?php
/**
* Lite Publisher
* Copyright (C) 2010, 2012 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function tsourcefilesInstall($self) {
  if (!dbversion) die("Plugin required data base");
  $manager = tdbmanager ::i();
  $manager->CreateTable($self->table, "
  `id` int unsigned NOT NULL auto_increment,
  `idurl` int unsigned NOT NULL default '0',
  `filename` varchar(128) NOT NULL,
  `dir` varchar(128) NOT NULL,
  PRIMARY KEY  (`id`)
  ");
  
  $dir = litepublisher::$paths->data . 'sourcefiles';
  if (!@is_dir($dir)) @mkdir($dir, 0777);
  @chmod($dir, 0777);
  
  $self->ignore = get_ignore_source();
  $self->save();
  litepublisher::$classes->add('tsourcefilesmenu', 'sourcefiles.menu.class.php', basename(dirname(__file__)));
}

function tsourcefilesUninstall($self) {
  //die("Warning! You can lost all tickets!");
  litepublisher::$classes->delete('tsourcefilesmenu');
  $manager = tdbmanager ::i();
  $manager->deletetable($self->table);
  Turlmap::unsub($self);
  tfiler::delete(litepublisher::$paths->data . 'sourcefiles', true, true);
}

function get_ignore_source() {
  return array(
  'lib/include/class-phpmailer.php',
  'lib/include/class-pop3.php',
  'lib/include/class-smtp.php',
  'plugins/sourcefiles/geshi.php',
  'plugins/sourcefiles/geshi',
  'plugins/sape/sape.php',
  'plugins/sape/'. litepublisher::$domain . '.links.db',
  'plugins/markdown/markdown.parser.class.php',
  'plugins/nicedit/nicEdit.js',
  'js/jsibox/jsibox_basic.js',
  'js/audio-player/audio-player.js',
  'js/audio-player/audio-player-noswfobject.js',
  'js/flowplayer'
  );
}