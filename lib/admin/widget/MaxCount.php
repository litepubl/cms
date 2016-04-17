<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\widget;

class MaxCount extends Widget
{

    protected function getForm() {
        $this->args->maxcount = $this->widget->maxcount;
        return parent::getForm()
 . '[text=maxcount]';
    }

    protected function doprocessform() {
        $this->widget->maxcount = (int)$_POST['maxcount'];
    }

}