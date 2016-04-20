<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

function pollsInstall($self) {
    $name = basename(dirname(__file__));
    $res = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;

    $manager = tdbmanager::i();
    $manager->createtable($self->table, file_get_contents($res . 'polls.sql'));
    $manager->createtable(polls::votes, file_get_contents($res . 'votes.sql'));

    tjsonserver::i()->addevent('polls_sendvote', get_class($self) , 'polls_sendvote');

    $js = tjsmerger::i();
    $js->lock();

    $css = tcssmerger::i();
    $css->lock();

    tplugins::i()->add('ulogin');
    $js->add('default', '/plugins/polls/resource/polls.min.js');
    $js->add('default', '/plugins/polls/resource/' .  $self->getApp()->options->language . '.polls.min.js');

    $css->add('default', 'plugins/polls/resource/polls.min.css');
    $css->unlock();
    $js->unlock();

    $parser = tthemeparser::i();
    $parser->addtags('plugins/polls/resource/theme.txt', 'plugins/polls/resource/themetags.ini');

    Langmerger::i()->addplugin($name);
    tcron::i()->addnightly(get_class($self) , 'optimize', null);
    tposts::i()->deleted = $self->postdeleted;
}

function pollsUninstall($self) {
    tjsonserver::i()->unbind($self);
    Langmerger::i()->deleteplugin(tplugins::getname(__file__));

    $js = tjsmerger::i();
    $js->lock();

    $css = tcssmerger::i();
    $css->lock();

    tplugins::i()->delete('ulogin');

    $js->deletefile('default', '/plugins/polls/resource/polls.min.js');
    $js->deletefile('default', '/plugins/polls/resource/' .  $self->getApp()->options->language . '.polls.min.js');

    $css->deletefile('default', 'plugins/polls/resource/polls.min.css');
    $css->unlock();
    $js->unlock();

    $parser = tthemeparser::i();
    $parser->removetags('plugins/polls/resource/theme.txt', 'plugins/polls/resource/themetags.ini');

    $manager = tdbmanager::i();
    $manager->deletetable($self->table);
    $manager->deletetable(polls::votes);

    tcron::i()->deleteclass(get_class($self));
    tposts::i()->unbind($self);
}