<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace litepubl\plugins\regservices2ulogin;

function PluginInstall($self)
{
    $self->getApp()->classes->remap['tregserviceuser'] = get_class($self);
    $self->getApp()->classes->save();

    $items = $self->getdb('regservices')->getItems('id > 0');
    $db = $self->db;
    foreach ($items as $item) {
        $db->insert($item);
    }
}

function PluginUninstall($self)
{
    unset($self->getApp()->classes->remap['tregserviceuser']);
    $self->getApp()->classes->save();
}
