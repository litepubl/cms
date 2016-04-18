<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\widget;
use litepubl\widgets\Widgets;
use litepubl\admin\Link;

class Widget extends \litepubl\admin\Simple
 {
    public $widget;

public function __construct() {
parent::__construct();
        $this->lang->section = 'widgets';
    }

public function __get($name) {
if (method_exists($this, $get = 'get' . $name)) {
return $this->$get();
}

throw new Exception(sprintf('Property %s not found', $name));
}

    protected function getadminurl() {
return Link::url('/admin/views/widgets/?idwidget=');
    }

    protected function getForm() {
$title = $this->widget->gettitle($this->widget->id);
        $this->args->title = $title;
        $this->args->formtitle = $title . ' ' . $this->lang->widget;
return $this->theme->getinput('text', 'title', $title, $this->lang->widgettitle);
    }

    public function getcontent() {
$form = $this->getForm();
        return $this->admin->form($form, $this->args);
    }

    public function processform() {
        $widget = $this->widget;
        $widget->lock();
        if (isset($_POST['title'])) {
$widget->settitle($widget->id, $_POST['title']);
}

        $this->doprocessform();
        $widget->unlock();
        return $this->admin->success($this->lang->updated);
    }

    protected function doProcessForm() {
//nothing
    }

}