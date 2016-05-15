<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\ulogin;

use litepubl\core\DBManager;
use litepubl\view\Js;
use litepubl\pages\Json;
use litepubl\core\Users;

function UloginInstall($self)
{
    $self->data['nets'] = array(
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
        'googleplus'
    );

    $man = DBManager::i();
    $man->createTable($self->table, str_replace('$names', implode("', '", $self->data['nets']) , file_get_contents(dirname(__file__) . '/resource/ulogin.sql')));
    if (!$man->column_exists('users', 'phone')) {
$man->alter('users', "add phone bigint not null default '0' after status");
}

    Users::i()->deleted = $self->userDeleted;

    $alogin = tadminlogin::i();
    $alogin->widget.= $self->panel;
    $alogin->save();

    $areg = tadminreguser::i();
    $areg->widget.= $self->panel;
    $areg->save();

    $self->getApp()->router->addget($self->url, get_class($self));

    $js = Js::i();
    $js->lock();
    $js->add('default', '/plugins/ulogin/resource/ulogin.popup.min.js');
    $self->getApp()->classes->add('emailauth', 'emailauth.class.php', 'ulogin');

    $js->add('default', '/plugins/ulogin/resource/' . $self->getApp()->options->language . '.authdialog.min.js');
    $js->add('default', '/plugins/ulogin/resource/authdialog.min.js');
    $js->unlock();

    $json = Json::i();
    $json->lock();
    $json->addevent('ulogin_auth', get_class($self) , 'ulogin_auth');
    $json->addevent('check_logged', get_class($self) , 'check_logged');
    $json->unlock();
}

function UloginUninstall($self)
{
    Users::i()->unbind('tregserviceuser');
    $self->getApp()->router->unbind($self);
    $man = DBManager::i();
    $man->deletetable($self->table);
    if ($man->column_exists('users', 'phone')) {
$man->alter('users', "drop phone");
}

    $alogin = tadminlogin::i();
    $alogin->widget = str_replace($self->panel, '', $alogin->widget);
    $alogin->save();

    $areg = tadminreguser::i();
    $areg->widget = str_replace($self->panel, '', $areg->widget);
    $areg->save();

    $js = Js::i();
    $js->lock();
    $js->deletefile('default', '/plugins/ulogin/resource/ulogin.popup.min.js');
    $js->deletefile('default', '/plugins/ulogin/resource/' . $self->getApp()->options->language . '.authdialog.min.js');
    $js->deletefile('default', '/plugins/ulogin/resource/authdialog.min.js');

    $self->getApp()->classes->delete('emailauth');
    $js->unlock();

    Json::i()->unbind($self);
}

