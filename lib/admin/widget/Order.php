<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\widget;
use litepubl\view\Schema;
use litepubl\view\Schemes;

class Order extends Widget
{
use \litepubl\admin\Params;

    protected function getForm() {
        $idschema = $this->getparam('idschema', 1);
        $schema = Schema::i($idschema);
        $this->args->sidebar = $this->theme->comboItems(Widgets::getSidebarNames($schema) , $this->widget->sidebar);
        $this->args->order = $this->theme->comboItems(range(-1, 10) , $this->widget->order + 1);
        $this->args->ajax = $this->widget->ajax;
        return parent::getForm()
 . '[combo=sidebar]
 [combo=order]
 [checkbox=ajax]';
    }

    protected function doprocessform() {
        $this->widget->sidebar = (int)$_POST['sidebar'];
        $this->widget->order = ((int)$_POST['order'] - 1);
        $this->widget->ajax = isset($_POST['ajax']);
    }

} //class