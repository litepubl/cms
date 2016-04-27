<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\Filter;

function tsmilesInstall($self) {
    $filter = Filter::i();
    $filter->lock();
    $filter->onsimplefilter = $self->filter;
    $filter->oncomment = $self->filter;
    $filter->unlock();

    tposts::i()->addrevision();
}

function tsmilesUninstall($self) {
    Filter::i()->unbind($self);
    tposts::i()->addrevision();
}