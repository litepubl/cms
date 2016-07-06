<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\perms;

function FilesInstall($self)
{
    $dir = $self->getApp()->paths->files . 'private';
    @mkdir($dir, 0777);
    @chmod($dir, 0777);
    $dir.= DIRECTORY_SEPARATOR;
    $file = $dir . 'index.htm';
    file_put_contents($file, ' ');
    @chmod($file, 0666);

    $file = $dir . '.htaccess';
    file_put_contents($file, 'Deny from all');
    @chmod($file, 0666);
}
