<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\widget;

class Meta extends Widget
{

    protected function getForm() {
        $result = parent::getForm();
$theme = $this->theme;
        foreach ($this->widget->items as $name => $item) {
            $result.= $theme->getinput('checkbox', $name, $item['enabled'] ? 'checked="checked"' : '', $item['title']);
        }

        return $result;
    }

    protected function doprocessform() {
        foreach ($this->widget->items as $name => $item) {
            $widget->items[$name]['enabled'] = isset($_POST[$name]);
        }
    }

}