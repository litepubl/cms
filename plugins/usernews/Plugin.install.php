<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\plugins\usernews;

use litepubl\admin\AuthorRights;
use litepubl\admin\Menus;
use litepubl\core\Plugins;
use litepubl\core\UserGroups;
use litepubl\view\Filter;
use litepubl\view\Lang;
use litepubl\view\LangMerger;

function PluginInstall($self)
{
    $plugins= Plugins::i();
    if (!isset($plugins->items['ulogin'])) {
        $plugins->add('ulogin');
    }

    LangMerger::i()->addPlugin(basename(__DIR__));
    $lang = Lang::admin('usernews');
    $filter = Filter::i();
    $filter->phpcode = true;
    $filter->save();

    $app = $self->getApp();
    $app->options->parsepost = false;
    $app->options->reguser = true;

    $groups = UserGroups::i();
    $groups->defaults = array(
        $groups->getIdGroup('author')
    );
    $groups->save();

    $rights = AuthorRights::i();
    $rights->lock();
    $rights->changeposts = $self->changePosts;
    $rights->canupload = $self->canUpload;
    $rights->candeletefile = $self->canDeleteFile;
    $rights->unlock();

    $menus = Menus::i();
    $menus->lock();
    $menus->addItem(
        [
        'parent' => $menus->url2id('/admin/posts/'),
            'url' => '/admin/posts/usernews/',
            'title' => $lang->createnews,
            'name' => 'usernews',
            'class' => get_class($self),
            'group' => 'author',
        ]
    );

    $id = $menus->url2id('/admin/posts/editor/');
    $menus->items[$id]['group'] = 'editor';
    $menus->unlock();
}

function PluginUninstall($self)
{
    AuthorRights::i()->unbind($self);
    LangMerger::i()->deletePlugin(basename(dirname(__file__)));
    $menus = Menus::i();
    $menus->deleteUrl('/admin/posts/usernews/');
}
