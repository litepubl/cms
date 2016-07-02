<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\plugins\wikiwords;

use litepubl\core\DBManager;
use litepubl\post\Posts;
use litepubl\view\AutoVars;
use litepubl\view\Filter;
use litepubl\view\Parser;

function WikiInstall($self)
{
    $manager = DBManager::i();
    $manager->createTable(
        $self->table, "  `id` int(10) unsigned NOT NULL auto_increment,
    `word` text NOT NULL,
    PRIMARY KEY  (`id`)"
    );

    $manager->createTable($self->itemsposts->table, file_get_contents($self->getApp()->paths->lib . 'core/install/sql/ItemsPosts.sql'));

    $filter = Filter::i();
    $filter->beforecontent = $self->beforeFilter;

    $posts = Posts::i();
    $posts->deleted = $self->postdeleted;

    $vars = AutoVars::i();
    $vars->items['wiki'] = get_class($self);
    $vars->save();

    Parser::i()->addtags('plugins/wikiwords/resource/theme.txt', 'plugins/wikiwords/resource/theme.ini');
}

function WikiUninstall($self)
{
    $vars = AutoVars::i();
    unset($vars->items['wiki']);
    $vars->save();

    $filter = Filter::i();
    $filter->unbind($self);

    Posts::unsub($self);
    $manager = DBManager::i();
    $manager->deletetable($self->table);
    $manager->deletetable($self->itemsposts->table);

    Parser::i()->removeTags('plugins/wikiwords/resource/theme.txt', 'plugins/wikiwords/resource/theme.ini');
}
