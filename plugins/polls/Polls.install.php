<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\plugins\polls;

use litepubl\core\Cron;
use litepubl\core\DBManager;
use litepubl\core\Plugins;
use litepubl\pages\Json;
use litepubl\post\Posts;
use litepubl\view\Css;
use litepubl\view\Js;
use litepubl\view\LangMerger;
use litepubl\view\Parser;

function PollsInstall($self)
{
    $name = basename(dirname(__file__));
    $res = dirname(__file__) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;

    $manager = DBManager::i();
    $manager->createtable($self->table, file_get_contents($res . 'polls.sql'));
    $manager->createTable(polls::votes, file_get_contents($res . 'votes.sql'));

    Json::i()->addevent('polls_sendvote', get_class($self), 'polls_sendvote');

    $js = Js::i();
    $js->lock();

    $css = Css::i();
    $css->lock();

    Plugins::i()->add('ulogin');
    $js->add('default', '/plugins/polls/resource/polls.min.js');
    $js->add('default', '/plugins/polls/resource/' . $self->getApp()->options->language . '.polls.min.js');

    $css->add('default', 'plugins/polls/resource/polls.min.css');
    $css->unlock();
    $js->unlock();

    $parser = Parser::i();
    $parser->addtags('plugins/polls/resource/theme.txt', 'plugins/polls/resource/themetags.ini');

    LangMerger::i()->addplugin($name);
    Cron::i()->addnightly(get_class($self), 'optimize', null);
    Posts::i()->deleted = $self->postdeleted;
}

function PollsUninstall($self)
{
    Json::i()->unbind($self);
    LangMerger::i()->deleteplugin(Plugins::getname(__file__));

    $js = Js::i();
    $js->lock();

    $css = Css::i();
    $css->lock();

    Plugins::i()->delete('ulogin');

    $js->deletefile('default', '/plugins/polls/resource/polls.min.js');
    $js->deletefile('default', '/plugins/polls/resource/' . $self->getApp()->options->language . '.polls.min.js');

    $css->deletefile('default', 'plugins/polls/resource/polls.min.css');
    $css->unlock();
    $js->unlock();

    $parser = Parser::i();
    $parser->removetags('plugins/polls/resource/theme.txt', 'plugins/polls/resource/themetags.ini');

    $manager = DBManager::i();
    $manager->deletetable($self->table);
    $manager->deletetable(polls::votes);

    Cron::i()->deleteclass(get_class($self));
    Posts::i()->unbind($self);
}
