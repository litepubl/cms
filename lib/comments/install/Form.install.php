<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\comments;

function FormInstall($self) {
    $url = '/send-comment.php';

     $self->getApp()->router->Add($url, get_class($self) , null);
}

function FormUninstall($self) {
     $self->getApp()->router->unbind($self);
}