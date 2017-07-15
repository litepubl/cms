<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\ulogin;

use litepubl\core\DBManager;
use litepubl\core\Users;
use litepubl\pages\Json;
use litepubl\view\Js;
use litepubl\view\Parser;

function UloginInstall($self)
{
    $self->data['nets'] = [
        'vkontakte',
        'odnoklassniki',
        'mailru',
        'facebook',
        'twitter',
        'google',
        'yandex',
        'livejournal',
        'openid',
        'flickr',
        'lastfm',
        'linkedin',
        'liveid',
        'soundcloud',
        'steam',
        'vimeo',
        'webmoney',
        'youtube',
        'foursquare',
        'tumblr',
        'googleplus',
    'uid',
    'instagram',
    'wargaming',
    ];

    $man = DBManager::i();
    $man->createTable($self->table, str_replace('$names', implode("', '", $self->data['nets']), file_get_contents(dirname(__file__) . '/resource/ulogin.sql')));
    if (!$man->columnExists('users', 'phone')) {
        $man->alter('users', "add phone bigint not null default '0' after status");
    }

    Users::i()->deleted = $self->userDeleted;
    $self->getApp()->router->unbind($self);
    $self->getApp()->router->addGet($self->url, get_class($self));
    $self->getapp()->options->reguser = true;
    Parser::i()->addTags('plugins/ulogin/resource/theme.txt', false);

    $js = Js::i();
    $js->lock();
    $js->add('default', '/plugins/ulogin/resource/ulogin.popup.min.js');
    EmailAuth::i()->install();

    $js->add('default', '/plugins/ulogin/resource/' . $self->getApp()->options->language . '.authdialog.min.js');
    $js->add('default', '/plugins/ulogin/resource/authdialog.min.js');
    $js->unlock();

    $json = Json::i();
    $json->lock();
    $json->addevent('ulogin_auth', get_class($self), 'ulogin_auth');
    $json->addevent('check_logged', get_class($self), 'check_logged');
    $json->unlock();
}

function UloginUninstall($self)
{
    Users::i()->unbind('tregserviceuser');
    $self->getApp()->router->unbind($self);
    $man = DBManager::i();
    $man->deleteTable($self->table);
    if ($man->columnExists('users', 'phone')) {
        $man->alter('users', "drop phone");
    }

    Parser::i()->removeTags('plugins/ulogin/resource/theme.txt', false);

    $js = Js::i();
    $js->lock();
    $js->deletefile('default', '/plugins/ulogin/resource/ulogin.popup.min.js');
    $js->deletefile('default', '/plugins/ulogin/resource/' . $self->getApp()->options->language . '.authdialog.min.js');
    $js->deletefile('default', '/plugins/ulogin/resource/authdialog.min.js');

    EmailAuth::i()->uninstall();
    $js->unlock();

    Json::i()->unbind($self);
}
