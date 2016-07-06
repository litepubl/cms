<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\plugins\regservices;

use litepubl\comments\Form;
use litepubl\core\DBManager;
use litepubl\core\Plugins;
use litepubl\core\Users;
use litepubl\utils\Filer;
use litepubl\view\AutoVars;
use litepubl\view\Parser;

function PluginInstall($self)
{
    $dir = $self->getApp()->paths->data . 'regservices';
    @mkdir($dir, 0777);
    @chmod($dir, 0777);
    $name = basename(dirname(__file__));
    $about = Plugins::getabout($name);
    $self->lock();
    $self->title = $about['widget_title'];
    Google::i()->install();
    Facebook::i()->install();
    Twitter::i()->install();
    MailRu::i()->install();
    Yandex::i()->install();
    VKontakte::i()->install();
    Odnoklassniki::i()->install();
    $self->unlock();

    Users::i()->deleted = tregserviceuser::i()->delete;

    $names = implode("', '", array_keys($self->items));
    DBManager::i()->createtable(
        'regservices', "id int unsigned NOT NULL default 0,
    service enum('$names') default 'google',
    uid varchar(22) NOT NULL default '',
    
    key `id` (`id`),
    KEY (`service`, `uid`)
    "
    );

    $self->getApp()->router->addget($self->url, get_class($self));
    Form::i()->oncomuser = $self->oncomuser;
    $self->getApp()->cache->clear();

    Parser::i()->addTags('plugins/regservices/resource/theme.txt', 'plugins/regservices/resource/theme.ini');
    $vars = AutoVars::i();
    $vars->items['regservices'] = get_class($self);
    $vars->save();
}

function PluginUninstall($self)
{
    Parser::i()->removeTags('plugins/regservices/resource/theme.txt', 'plugins/regservices/resource/theme.ini');
    AutoVars::i()->delete('regservices');
    $name = basename(dirname(__file__));
    Form::i()->unbind($self);
    $self->getApp()->router->unbind($self);

    Google::i()->install();
    Facebook::i()->install();
    Twitter::i()->install();
    MailRu::i()->install();
    Yandex::i()->install();
    VKontakte::i()->install();
    Odnoklassniki::i()->install();

    Filer::delete($self->getApp()->paths->data . 'regservices', true, true);

    Users::i()->unbind('tregserviceuser');
    DBManager::i()->deletetable('regservices');
}
