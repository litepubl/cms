<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tadminshowcount extends tadminwidget {

    public static function i() {
        return getinstance(__class__);
    }

    protected function dogetcontent(twidget $widget, targs $args) {
        $args->showcount = $widget->showcount;
        return $this->html->parsearg('[checkbox=showcount]', $args);
    }

    protected function doprocessform(twidget $widget) {
        $widget->showcount = isset($_POST['showcount']);
    }

} //class