<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\pages;

function MenusInstall($self)
{
    @mkdir($self->getApp()->paths->data . 'menus', 0777);
    if (get_class($self) != 'litepubl\pages\Menus') {
        return;
    }

    @chmod($self->getApp()->paths->data . 'menus', 0777);

    $self->getApp()->classes->onrename = $self->classRenamed;
}

function MenusUninstall($self)
{
    //rmdir(. 'menus');
    $self->getApp()->classes->unbind($self);
}

function MenusGetsitemap($self, $from, $count)
{
    $result = [];
    foreach ($self->items as $id => $item) {
        if ($item['status'] == 'draft') {
            continue;
        }

        $result[] = [
            'url' => $item['url'],
            'title' => $item['title'],
            'pages' => 1
        ];
    }
    return $result;
}
