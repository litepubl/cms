<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\widget;

class ShowCount extends Widget
{

    protected function getForm() {
        $this->args->showcount = $this->widget->showcount;
        return parent::getForm()
. '[checkbox=showcount]';
    }

    protected function doprocessform() {
        $this->widget->showcount = isset($_POST['showcount']);
    }

} //class