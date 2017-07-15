<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\subcat;

use litepubl\widget\Widgets;

function WidgetInstall($self)
{
    $widgets = Widgets::i();
    $widgets->deleted = $self->widgetDeleted;

    $self->tags->deleted = $self->tagDeleted;
}

function WidgetUninstall($self)
{
    $self->tags->unbind($self);
}
