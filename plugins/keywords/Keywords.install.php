<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\keywords;

function KeywordsInstall($self)
{
    @mkdir($self->getApp()->paths->data . 'keywords', 0777);
    @chmod($self->getApp()->paths->data . 'keywords', 0777);

    $item = $self->getApp()->classes->items[get_class($self) ];
    $self->getApp()->classes->add('tkeywordswidget', 'keywords.widget.php', $item[1]);

    $widget = Widget::i();
    $widgets = Widgets::i();
    $widgets->lock();
    $id = $widgets->add($widget);
    $sidebars = tsidebars::i();
    $sidebars->insert($id, false, 1, -1);
    $widgets->unlock();

    $router = \litepubl\core\Router::i();
    $router->lock();
    $router->afterrequest = $self->parseref;
    $router->deleted = $self->urldeleted;
    $router->unlock();
}

function KeywordsUninstall($self)
{
    $self->getApp()->router->unbind($self);
    $widgets = twidgets::i();
    $widgets->deleteclass('tkeywordswidget');
    $self->getApp()->classes->delete('tkeywordswidget');
  
}

