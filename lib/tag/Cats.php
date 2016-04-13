<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\tag;
use litepubl\widget\Cats as CatsWidget;

class Cats extends Common
{
    //public  $defaultid;

    protected function create() {
        parent::create();
        $this->table = 'categories';
        $this->contents->table = 'catscontent';
        $this->itemsposts->table = $this->table . 'items';
        $this->basename = 'categories';
        $this->data['defaultid'] = 0;
    }

    public function setdefaultid($id) {
        if (($id != $this->defaultid) && $this->itemexists($id)) {
            $this->data['defaultid'] = $id;
            $this->save();
        }
    }

    public function save() {
        parent::save();
        if (!$this->locked) {
            CatsWidget::i()->expire();
        }
    }

}
