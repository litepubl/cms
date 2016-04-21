<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\post;
use litepubl\comments\Comments;
use litepubl\widget\Meta as MetaWidget;
use litepubl\view\Lang;

function RssInstall($self) {
     $self->getApp()->router->add('/rss.xml', get_class($self) , 'posts');
    $self->idcomments =  $self->getApp()->router->add('/comments.xml', get_class($self) , 'comments');
    $self->idpostcomments =  $self->getApp()->router->add('/comments/', get_class($self) , null, 'begin');
     $self->getApp()->router->add('/rss/categories/', get_class($self) , 'categories', 'begin');
     $self->getApp()->router->add('/rss/tags/', get_class($self) , 'tags', 'begin');

    Comments::i()->changed = $self->commentschanged;

    $self->save();

    $meta = MetaWidget::i();
    $meta->lock();
    $meta->add('rss', '/rss.xml', Lang::get('default', 'rss'));
    $meta->add('comments', '/comments.xml', Lang::get('default', 'rsscomments'));
    $meta->unlock();
}

function RssUninstall($self) {
     $self->getApp()->router->unbind($self);
     $self->getApp()->router->updatefilter();
    Comments::i()->unbind($self);
    $meta  = MetaWidget::i();
    $meta->lock();
    $meta->delete('rss');
    $meta->delete('comments');
    $meta->unlock();
}