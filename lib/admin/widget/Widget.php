<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin;
use litepubl\core\Data;
use litepubl\core\Lang;
use litepubl\core\Args;
use litepubl\widgets\Widgets;

class Widget extends Data
 {
    public $widget;
    protected $html;
    protected $lang;

    protected function create() {
        $this->html = tadminhtml::i();
        $this->lang = Lang::i('widgets');
    }

    protected function getadminurl() {
        return litepubl::$site->url . '/admin/views/widgets/' . litepubl::$site->q . 'idwidget=';
    }

    protected function dogetcontent(twidget $widget, targs $args) {
return '';
    }

    protected function optionsform($widgettitle, $content) {
        $args = new targs();
        $args->formtitle = $widgettitle . ' ' . $this->lang->widget;
        $args->title = $widgettitle;
        $args->items = $this->theme->getinput('text', 'title', $widgettitle, $this->lang->widgettitle) . $content;
        return $this->admintheme->parsearg(ttheme::i()->templates['content.admin.form'], $args);
    }

    public function getcontent() {
        return $this->optionsform($this->widget->gettitle($this->widget->id) , $this->dogetcontent($this->widget, targs::i()));
    }

    public function processform() {
        $widget = $this->widget;
        $widget->lock();
        if (isset($_POST['title'])) $widget->settitle($widget->id, $_POST['title']);
        $this->doprocessform($widget);
        $widget->unlock();
        return $this->html->h2->updated;
    }

    protected function doprocessform(twidget $widget) {
//nothing
    }

} //class