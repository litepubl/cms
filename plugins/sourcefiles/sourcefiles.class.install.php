<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tsourcefilesInstall($self) {
  litepubl::$urlmap->add($self->url, get_class($self) , '', 'begin');

  if (!@is_dir($self->dir)) @mkdir($self->dir, 0777);
  @chmod($self->dir, 0777);
}

function tsourcefilesUninstall($self) {
  litepubl::$urlmap->delete($self->url);
  tfiler::delete($self->dir, true, true);
}

function get_ignore_source() {
  return array(
    'lib/include/class-phpmailer.php',
    'lib/include/class-pop3.php',
    'lib/include/class-smtp.php',
    'plugins/sourcefiles/geshi.php',
    'plugins/sourcefiles/geshi',
    'plugins/sape/sape.php',
    'plugins/sape/' . litepubl::$domain . '.links.db',
    'plugins/markdown/markdown.parser.class.php',
    'plugins/nicedit/nicEdit.js',
    'js/jsibox/jsibox_basic.js',
    'js/audio-player/audio-player.js',
    'js/audio-player/audio-player-noswfobject.js',
    'js/flowplayer'
  );
}