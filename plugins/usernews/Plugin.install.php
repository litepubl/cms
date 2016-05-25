<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\usernews;

use litepubl\view\Filter;
use litepubl\view\LangMerger;
use litepubl\core\UserGroups;
use litepubl\core\Plugins;
use litepubl\admin\AuthorRights;

function PluginInstall($self)
{
$plugins= Plugins::i();
    if (!isset($plugins->items['ulogin'])) {
$plugins->add('ulogin');
}

    $name = basename(dirname(__file__));
    $self->data['dir'] = $name;
    $self->save();

    LangMerger::i()->addplugin($name);

    $filter = Filter::i();
    $filter->phpcode = true;
    $filter->save();

    $self->getApp()->options->parsepost = false;
    $self->getApp()->options->reguser = true;

    $groups = UserGroups::i();
    $groups->defaults = array(
        $groups->getIdGroup('author')
    );
    $groups->save();

    $rights = AuthorRights::i();
    $rights->lock();
    $rights->gethead = $self->gethead;
    $rights->getposteditor = $self->getPostEditor;
    $rights->editpost = $self->editpost;
    $rights->changeposts = $self->changePosts;
    $rights->canupload = $self->canUpload;
    $rights->candeletefile = $self->canDeleteFile;
    $rights->unlock();
}

function PluginUninstall($self)
{
    AuthorRights::i()->unbind($self);
    LangMerger::i()->deleteplugin(basename(dirname(__file__)));
}

