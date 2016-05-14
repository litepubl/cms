<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl;

function tsubcatwidgetInstall($self)
{
    $widgets = twidgets::i();
    $widgets->deleted = $self->widgetdeleted;

    $self->tags->deleted = $self->tagdeleted;
}

function tsubcatwidgetUninstall($self)
{
    $self->tags->unbind($self);
}

