<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\widget;
use litepubl\widget\Custom as CustomWidget;

class AddCustom extends \litepubl\admin\Menu
{

    public function getcontent() {
        $widget = CustomWidget::i();
        return $widget->admin->getcontent();
    }

    public function processform() {
        $widget = CustomWidget::i();
        return $widget->admin->processform();
    }

}