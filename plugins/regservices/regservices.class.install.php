<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

function tregservicesInstall($self) {
    $dir = litepubl::$paths->data . 'regservices';
    @mkdir($dir, 0777);
    @chmod($dir, 0777);
    $name = basename(dirname(__file__));
    $about = tplugins::getabout($name);
    $self->lock();

    $css = tcssmerger::i();
    $css->addstyle("/plugins/$name/regservices.min.css");

    $self->dirname = $name;
    $self->widget_title = sprintf('<h4>%s</h4>', $about['widget_title']);
    litepubl::$classes->add('tregservice', 'service.class.php', $name);
    litepubl::$classes->add('tregserviceuser', 'service.class.php', $name);
    litepubl::$classes->add('tgoogleregservice', 'google.service.php', $name);
    litepubl::$classes->add('tfacebookregservice', 'facebook.service.php', $name);
    litepubl::$classes->add('ttwitterregservice', 'twitter.service.php', $name);
    litepubl::$classes->add('tmailruregservice', 'mailru.service.php', $name);
    litepubl::$classes->add('tyandexregservice', 'yandex.service.php', $name);
    litepubl::$classes->add('tvkontakteregservice', 'vkontakte.service.php', $name);
    litepubl::$classes->add('todnoklassnikiservice', 'odnoklassniki.service.php', $name);

    litepubl::$classes->add('toauth', 'oauth.class.php', $name);

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

    litepubl::$urlmap->addget($self->url, get_class($self));
    tcommentform::i()->oncomuser = $self->oncomuser;
    litepubl::$urlmap->clearcache();
}

function tregservicesUninstall($self) {
    $name = basename(dirname(__file__));
    tcommentform::i()->unbind($self);
    turlmap::unsub($self);
    foreach ($self->items as $id => $classname) {
        litepubl::$classes->delete($classname);
    }

    litepubl::$classes->delete('tregserviceuser');
    litepubl::$classes->delete('toauth');

    tfiler::delete(litepubl::$paths->data . 'regservices', true, true);

    tusers::i()->unbind('tregserviceuser');
    tdbmanager::i()->deletetable('regservices');

    $css = tcssmerger::i();
    $css->deletestyle("/plugins/$name/regservices.min.css");
}