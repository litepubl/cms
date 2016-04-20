<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

function tsmilesInstall($self) {
    $filter = tcontentfilter::i();
    $filter->lock();
    $filter->onsimplefilter = $self->filter;
    $filter->oncomment = $self->filter;
    $filter->unlock();

    tposts::i()->addrevision();
}

function tsmilesUninstall($self) {
    tcontentfilter::i()->unbind($self);
    tposts::i()->addrevision();
}