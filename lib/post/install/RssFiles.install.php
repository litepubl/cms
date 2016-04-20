<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\post;
use litepubl\widget\Meta as MetaWidget;

function RssFilesInstall($self) {
     $self->getApp()->router->add('/rss/multimedia.xml', get_class($self) , '');
     $self->getApp()->router->add('/rss/images.xml', get_class($self) , 'image');
     $self->getApp()->router->add('/rss/audio.xml', get_class($self) , 'audio');
     $self->getApp()->router->add('/rss/video.xml', get_class($self) , 'video');

    $files = Files::i();
    $files->changed = $self->fileschanged;
    $self->save();

    $meta = MetaWidget::i();
    $meta->add('media', '/rss/multimedia.xml', Lang::get('default', 'rssmedia'));
}

function RssFilesUninstall($self) {
     $self->getApp()->router->unbind($self);
    $files = Files::i();
    $files->unbind($self);

    $meta = MetaWidget::i();
    $meta->delete('media');
}