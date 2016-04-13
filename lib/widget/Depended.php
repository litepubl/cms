<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\widget;

class Depended extends Widget
 {
    private $item;

    private function isvalue($name) {
        return in_array($name, array(
            'ajax',
            'order',
            'sidebar'
        ));
    }

    public function __get($name) {
        if ($this->isvalue($name)) {
            if (!$this->item) {
                $widgets = Widgets::i();
                $this->item = & $widgets->finditem($widgets->find($this));
            }
            return $this->item[$name];
        }
        return parent::__get($name);
    }

    public function __set($name, $value) {
        if ($this->isvalue($name)) {
            if (!$this->item) {
                $widgets = Widgets::i();
                $this->item = & $widgets->finditem($widgets->find($this));
            }
            $this->item[$name] = $value;
        } else {
            parent::__set($name, $value);
        }
    }

    public function save() {
        parent::save();
        Widgets::i()->save();
    }

}