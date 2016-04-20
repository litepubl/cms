<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

function TRSSPrevNextInstall($self) {
    $rss = trss::i();
    $rss->beforepost = $self->beforepost;

    $router = \litepubl\core\Router::i();
    $router->clearcache();
}

function TRSSPrevNextUninstall($self) {
    $rss = trss::i();
    $rss->unbind($self);

    $router = \litepubl\core\Router::i();
    $router->clearcache();
}