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

use litepubl\view\Filter;
use litepubl\view\LangMerger;

function tusernewsInstall($self)
{
    $name = basename(dirname(__file__));
    $self->data['dir'] = $name;
    $self->save();

    LangMerger::i()->addplugin($name);

    $filter = Filter::i();
    $filter->phpcode = true;
    $filter->save();

    $self->getApp()->options->parsepost = false;
    $self->getApp()->options->reguser = true;
    $adminoptions = tadminoptions::i();
    $adminoptions->usersenabled = true;

    $groups = tusergroups::i();
    $groups->defaults = array(
        $groups->getidgroup('author')
    );
    $groups->save();

    $rights = tauthor_rights::i();
    $rights->lock();
    $rights->gethead = $self->gethead;
    $rights->getposteditor = $self->getposteditor;
    $rights->editpost = $self->editpost;
    $rights->changeposts = $self->changeposts;
    $rights->canupload = $self->canupload;
    $rights->candeletefile = $self->candeletefile;
    $rights->unlock();
}

function tusernewsUninstall($self)
{
    tauthor_rights::i()->unbind($self);
    LangMerger::i()->deleteplugin(basename(dirname(__file__)));
}

