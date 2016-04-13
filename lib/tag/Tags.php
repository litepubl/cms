<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\tag;
use litepubl\widget\Tags as TagsWidget;

class Tags extends Common
{

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->table = 'tags';
        $this->basename = 'tags';
        $this->PermalinkIndex = 'tag';
        $this->postpropname = 'tags';
        $this->contents->table = 'tagscontent';
        $this->itemsposts->table = $this->table . 'items';
    }

    public function save() {
        parent::save();
        if (!$this->locked) {
            TagsWidget::i()->expire();
        }
    }

}
