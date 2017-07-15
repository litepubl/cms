<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\post;

function MetaInstall($self)
{
    $dir = dirname(__file__) . '/sql/';
    $manager = $self->db->man;
    $manager->CreateTable($self->table, file_get_contents($dir . 'meta.sql'));
}

function MetaUninstall($self)
{
}
