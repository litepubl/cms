<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\plugins\postwidget;

use litepubl\tag\Cats;
use litepubl\widget\Widgets;

function WidgetInstall($self)
{
    $widgets = Widgets::i();
    $widgets->deleted = $self->widgetDeleted;

    Cats::i()->deleted = $self->tagDeleted;
}

function WidgetUninstall($self)
{
    Cats::i()->unbind($self);
    $widgets = Widgets::i();
    $widgets->unbind($self);
}
