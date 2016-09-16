<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\sourcefiles;

use litepubl\utils\Filer;

function PluginInstall($self)
{
    $self->getApp()->router->add($self->url, get_class($self), '', 'begin');

    if (!@is_dir($self->dir)) {
        @mkdir($self->dir, 0777);
    }
    @chmod($self->dir, 0777);
}

function PluginUninstall($self)
{
    $self->getApp()->router->delete($self->url);
    Filer::delete($self->dir, true, true);
}

function get_ignore_source()
{
    return [
        'lib/include/class-phpmailer.php',
        'lib/include/class-pop3.php',
        'lib/include/class-smtp.php',
        'plugins/sourcefiles/geshi.php',
        'plugins/sourcefiles/geshi',
        'plugins/sape/sape.php',
        'plugins/sape/' . $self->getApp()->domain . '.links.db',
        'plugins/markdown/markdown.parser.class.php',
        'js/jsibox/jsibox_basic.js',
        'js/audio-player/audio-player.js',
        'js/audio-player/audio-player-noswfobject.js',
        'js/flowplayer'
    ];
}
