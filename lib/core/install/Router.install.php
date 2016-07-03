<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\core;

function RouterInstall($self)
{
    $manager = DBManager::i();
    $manager->CreateTable('urlmap', file_get_contents(dirname(__file__) . '/sql/router.sql'));
}
