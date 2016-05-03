<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\view;
use litepubl\widget\Widgets;

function SchemesInstall($self) {
    $widgets = Widgets::i();
    $widgets->deleted = $self->widgetdeleted;

    $self->lock();
    $lang = Lang::admin('names');
    $default = $self->add($lang->default);
    $def = Schema::i($default);
    $def->sidebars = array(
        array() ,
        array() ,
        array()
    );

    $idadmin = $self->add($lang->adminpanel);
    $admin = Schema::i($idadmin);
    $admin->menuclass = 'litepubl\admin\Menus';

    $self->defaults = array(
        'post' => $default,
        'menu' => $default,
        'category' => $default,
        'tag' => $default,
        'admin' => $idadmin
    );

    $self->unlock();
}