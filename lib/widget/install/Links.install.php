<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

function tlinkswidgetInstall($self) {
    if (get_class($self) != 'tlinkswidget') {
 return;
}


    Lang::usefile('admin');
    $lang = Lang::i('installation');
    $self->add($lang->homeurl, $lang->homedescription, $lang->homename);

    $router = \litepubl\core\Router::i();
    $router->add($self->redirlink, get_class($self) , null, 'get');

    $robots = trobotstxt::i();
    $robots->AddDisallow($self->redirlink);
    $robots->save();
}

function tlinkswidgetUninstall($self) {
    if (get_class($self) != 'tlinkswidget') {
 return;
}


     $self->getApp()->router->unbind($self);
}