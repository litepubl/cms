<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

function ttoptextinstall($self) {
    $filter = tcontentfilter::i();
    $filter->lock();
    $filter->beforecontent = $self->beforecontent;
    $filter->aftercontent = $self->aftercontent;
    $filter->unlock();
}

function ttoptextuninstall($self) {
    $filter = tcontentfilter::i();
    $filter->unbind($self);
}