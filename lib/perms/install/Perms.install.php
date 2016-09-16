<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\perms;

use litepubl\view\Lang;

function PermsInstall($self)
{
    Lang::usefile('install');
    $lang = Lang::i('initgroups');

    $self->lock();
    $single = new Single();
    $single->name = $lang->single;
    $self->add($single);
    $self->addclass($single);

    $pwd = new Password();
    $pwd->name = $lang->pwd;
    $self->add($pwd);
    $self->addclass($pwd);

    $groups = new Groups();
    $groups->name = $lang->groups;
    $self->add($groups);
    $self->addclass($groups);

    $self->unlock();
}

function PermsUninstall($self)
{
}
