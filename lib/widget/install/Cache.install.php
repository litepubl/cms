<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\widget;

function CacheInstall($self) {
     $self->getApp()->router->onclearcache = $self->onclearcache;
}

function CacheUninstall($self) {
     $self->getApp()->router->unbind($self);
}