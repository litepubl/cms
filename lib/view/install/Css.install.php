<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

namespace litepubl\view;

function CssInstall($self)
{
    $self->lock();
    $self->items = [];
    $section = 'default';
    $self->add($section, '/js/litepubl/common/css/common.min.css');
    $self->add($section, '/js/litepubl/common/css/filelist.min.css');
    $self->add($section, '/js/litepubl/common/css/form.inline.min.css');

    $list = Css_bootstrap_files($self);
    foreach ($list as $filename) {
        $self->add($section, $filename);
    }

    $section = 'admin';
    $self->add($section, '/js/litepubl/admin/css/calendar.min.css');
    $self->add($section, '/js/litepubl/admin/css/fileman.min.css');
    $self->unlock();
}

function CssUninstall($self)
{
}

function Css_pretty_files($self)
{
    return [
        '/js/prettyphoto/css/prettyPhoto.css',
        '/js/litepubl/pretty/dialog.pretty.min.css',
    ];
}

function Css_deprecated_files($self)
{
    return [
        '/js/litepubl/deprecated/css/align.min.css',
        '/js/litepubl/deprecated/css/button.min.css',
        '/js/litepubl/deprecated/css/table.min.css',
    ];
}

function Css_bootstrap_files($self)
{
    return [
        '/js/litepubl/effects/css/homeimage.min.css',
        '/themes/default/css/logo.min.css'
    ];
}
