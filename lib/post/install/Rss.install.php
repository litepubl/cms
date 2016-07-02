<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\post;

use litepubl\comments\Comments;
use litepubl\view\Lang;
use litepubl\widget\Meta as MetaWidget;

function RssInstall($self)
{
    $router = $self->getApp()->router;
    $router->add($self->url, get_class($self), 'posts');
    $router->add($self->commentsUrl, get_class($self), 'comments');
    $router->add($self->postCommentsUrl, get_class($self), null, 'begin');
    $router->add('/rss/categories/', get_class($self), 'categories', 'begin');
    $router->add('/rss/tags/', get_class($self), 'tags', 'begin');

    Comments::i()->changed = $self->commentschanged;

    $self->save();

    $meta = MetaWidget::i();
    $meta->lock();
    $meta->add('rss', '/rss.xml', Lang::get('default', 'rss'));
    $meta->add('comments', '/comments.xml', Lang::get('default', 'rsscomments'));
    $meta->unlock();
}

function RssUninstall($self)
{
    $self->getApp()->router->unbind($self);
    Comments::i()->unbind($self);
    $meta = MetaWidget::i();
    $meta->lock();
    $meta->delete('rss');
    $meta->delete('comments');
    $meta->unlock();
}
