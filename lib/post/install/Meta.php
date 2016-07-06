<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
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
