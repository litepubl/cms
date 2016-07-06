<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\comments;

use litepubl\pages\RobotsTxt;

function ManagerInstall($self)
{
    $self->data['filterstatus'] = true;
    $self->data['checkduplicate'] = true;
    $self->data['defstatus'] = 'approved';

    $self->data['sendnotification'] = true;
    $self->data['trustlevel'] = 2;
    $self->data['hidelink'] = false;
    $self->data['redir'] = true;
    $self->data['nofollow'] = false;
    $self->data['canedit'] = true;
    $self->data['candelete'] = true;

    $self->data['confirmlogged'] = false;
    $self->data['confirmguest'] = true;
    $self->data['confirmcomuser'] = true;
    $self->data['confirmemail'] = false;

    $self->data['comuser_subscribe'] = true;

    $self->data['idguest'] = 0; //create user in installer after create users table
    $groups = $self->getApp()->options->groupnames;
    $self->data['idgroups'] = array(
        $groups['admin'],
        $groups['editor'],
        $groups['moderator'],
        $groups['author'],
        $groups['commentator']
    );

    $self->save();

    $comments = Comments::i();
    $comments->lock();
    $comments->changed = $self->changed;
    $comments->added = $self->sendmail;
    $comments->unlock();

    $self->getApp()->router->addget('/comusers.htm', get_class($self));
    RobotsTxt::i()->AddDisallow('/comusers.htm');
}

function ManagerUninstall($self)
{
    $self->getApp()->router->unbind($self);
}
