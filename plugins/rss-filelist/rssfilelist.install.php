<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

function trssfilelistInstall($self) {
    $rss = trss::i();
    $rss->beforepost = $self->beforepost;

     $self->getApp()->router->clearcache();
}

function trssfilelistUninstall($self) {
    $rss = trss::i();
    $rss->unbind($self);

     $self->getApp()->router->clearcache();
}