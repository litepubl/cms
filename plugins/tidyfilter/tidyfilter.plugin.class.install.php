<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

function ttidyfilterInstall($self) {
    if (!class_exists('tidy')) die('PHP tidy extension is required');
    $filter = tcontentfilter::i();
    $filter->lock();
    $filter->onaftersimple = $self->filter;
    $filter->onaftercomment = $self->filter;
    $filter->unlock();
}

function ttidyfilterUninstall($self) {
    $filter = tcontentfilter::i();
    $filter->unbind($self);
}