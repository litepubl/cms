<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class tadminwidget extends tdata {
    public $widget;
    protected $html;
    protected $lang;

    protected function create() {
        //parent::i();
        $this->html = tadminhtml::i();
        $this->html->section = 'widgets';
        $this->lang = tlocal::i('widgets');
    }

    protected function getadminurl() {
        return litepubl::$site->url . '/admin/views/widgets/' . litepubl::$site->q . 'idwidget=';
    }

    protected function dogetcontent(twidget $widget, targs $args) {
return '';
    }

    protected function optionsform($widgettitle, $content) {
        $args = targs::i();
        $args->formtitle = $widgettitle . ' ' . $this->lang->widget;
        $args->title = $widgettitle;
        $args->items = $this->html->getedit('title', $widgettitle, $this->lang->widgettitle) . $content;
        return $this->html->parsearg(ttheme::i()->templates['content.admin.form'], $args);
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