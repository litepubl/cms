<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\widget;

class Tags extends Widget
{

    protected function getForm() {
$args = $this->args;
$widget = $this->widget;

        $args->showcount = $widget->showcount;
        $args->showsubitems = $widget->showsubitems;
        $args->maxcount = $widget->maxcount;
        $args->sort = $this->theme->comboItems(tlocal::i()->ini['sortnametags'], $widget->sortname);

        return parent::getForm()
. '[combo=sort]
 [checkbox=showsubitems]
 [checkbox=showcount]
 [text=maxcount]';
    }

    protected function doprocessform() {
        extract($_POST, EXTR_SKIP);
        $this->widget->maxcount = (int)$maxcount;
        $this->widget->showcount = isset($showcount);
        $this->widget->showsubitems = isset($showsubitems);
        $this->widget->sortname = $sort;
    }

}