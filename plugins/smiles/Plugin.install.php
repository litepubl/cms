<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\smiles;

use litepubl\core\Plugins;
use litepubl\post\Posts;
use litepubl\view\Admin;
use litepubl\view\Filter;

function PluginInstall($self)
{
    $admin = Admin::admin();
    $about = Plugins::getAbout(basename(__DIR__));
    $self->data['smile'] = $admin->getIcon('smile-o', $about['smile']);
    $self->data['sad'] = $admin->getIcon('frown-o', $about['sad']);
    $self->save();

    $filter = Filter::i();
    $filter->lock();
    $filter->onsimplefilter = $self->filter;
    $filter->oncomment = $self->filter;
    $filter->unlock();

    Posts::i()->addRevision();
}

function PluginUninstall($self)
{
    Filter::i()->unbind($self);
    Posts::i()->addRevision();
}
