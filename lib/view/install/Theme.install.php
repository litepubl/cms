<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\view;

function ThemeInstall($self)
{
    $dir = $self->getApp()->paths->data . 'themes';
    if (!is_dir($dir)) {
        mkdir($dir, 0777);
        chmod($dir, 0777);
    }
    $self->name = 'default';
    $self->parsetheme();
}
