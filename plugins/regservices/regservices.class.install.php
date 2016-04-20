<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

function tregservicesInstall($self) {
    $dir =  $self->getApp()->paths->data . 'regservices';
    @mkdir($dir, 0777);
    @chmod($dir, 0777);
    $name = basename(dirname(__file__));
    $about = tplugins::getabout($name);
    $self->lock();

    $css = tcssmerger::i();
    $css->addstyle("/plugins/$name/regservices.min.css");

    $self->dirname = $name;
    $self->widget_title = sprintf('<h4>%s</h4>', $about['widget_title']);
     $self->getApp()->classes->add('tregservice', 'service.class.php', $name);
     $self->getApp()->classes->add('tregserviceuser', 'service.class.php', $name);
     $self->getApp()->classes->add('tgoogleregservice', 'google.service.php', $name);
     $self->getApp()->classes->add('tfacebookregservice', 'facebook.service.php', $name);
     $self->getApp()->classes->add('ttwitterregservice', 'twitter.service.php', $name);
     $self->getApp()->classes->add('tmailruregservice', 'mailru.service.php', $name);
     $self->getApp()->classes->add('tyandexregservice', 'yandex.service.php', $name);
     $self->getApp()->classes->add('tvkontakteregservice', 'vkontakte.service.php', $name);
     $self->getApp()->classes->add('todnoklassnikiservice', 'odnoklassniki.service.php', $name);

     $self->getApp()->classes->add('toauth', 'oauth.class.php', $name);

    $self->add(tgoogleregservice::i());
    $self->add(tfacebookregservice::i());
    $self->add(ttwitterregservice::i());
    $self->add(tmailruregservice::i());
    $self->add(tyandexregservice::i());
    $self->add(tvkontakteregservice::i());
    $self->add(todnoklassnikiservice::i());

    $self->unlock();

    tusers::i()->deleted = tregserviceuser::i()->delete;
    if (dbversion) {
        $names = implode("', '", array_keys($self->items));
        tdbmanager::i()->createtable('regservices', "id int unsigned NOT NULL default 0,
    service enum('$names') default 'google',
    uid varchar(22) NOT NULL default '',
    
    key `id` (`id`),
    KEY (`service`, `uid`)
    ");
    }

     $self->getApp()->router->addget($self->url, get_class($self));
    tcommentform::i()->oncomuser = $self->oncomuser;
     $self->getApp()->router->clearcache();
}

function tregservicesUninstall($self) {
    $name = basename(dirname(__file__));
    tcommentform::i()->unbind($self);
     $self->getApp()->router->unbind($self);
    foreach ($self->items as $id => $classname) {
         $self->getApp()->classes->delete($classname);
    }

     $self->getApp()->classes->delete('tregserviceuser');
     $self->getApp()->classes->delete('toauth');

    tfiler::delete( $self->getApp()->paths->data . 'regservices', true, true);

    tusers::i()->unbind('tregserviceuser');
    tdbmanager::i()->deletetable('regservices');

    $css = tcssmerger::i();
    $css->deletestyle("/plugins/$name/regservices.min.css");
}