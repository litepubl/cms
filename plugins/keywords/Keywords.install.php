<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\plugins\keywords;

function KeywordsInstall($self)
{
    @mkdir($self->getApp()->paths->data . 'keywords', 0777);
    @chmod($self->getApp()->paths->data . 'keywords', 0777);

    $widget = Widget::i();
    $widget->addToSidebar(1);

    $router = $self->getApp()->router;
    $router->lock();
    $router->afterrequest = $self->parseref;
    $router->deleted = $self->urlDeleted;
    $router->unlock();
}

function KeywordsUninstall($self)
{
    $self->getApp()->router->unbind($self);
    $widget = Widget::i();
    $widget->uninstall();
}
