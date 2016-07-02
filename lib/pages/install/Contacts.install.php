<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\pages;

use litepubl\view\Admin;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Theme;

function ContactsInstall($self)
{
    $self->lock();
    ContactsUpdate($self);
    $self->order = 10;
    $self->title = Lang::i()->title;
    $menus = Menus::i();
    $menus->add($self);
    $self->unlock();
}

function ContactsUninstall($self)
{
    $menus = Menus::i();
    $menus->delete($self->id);
}

function ContactsUpdate($self)
{
    $self->lock();
    Lang::usefile('install');
    $lang = Lang::i('contactform');
    $admin = Admin::admin();
$theme = Theme::getTheme('default');

    $self->subject = $lang->subject;
    $self->success = $admin->success($lang->success);
    $self->errmesg = $admin->geterr($lang->errmesg);

    $args = new Args();
    $args->email = '';
    $args->content = '';
    $args->contactvalue = '_contactform<?php echo strtotime (\'+1 hour\'); ?>';
    $args->formtitle = $lang->contactform;
    $self->content = '[html]' . $admin->form('
[email=email]
[editor=content]
[hidden=contactvalue]
', $args);

    $self->unlock();
}
