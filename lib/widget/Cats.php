<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\widget;
use litepubl\tag\Cats as Owner;
use litepubl\view\Lang;

class Cats extends CommonTags
 {

    protected function create() {
        parent::create();
        $this->basename = 'widget.categories';
        $this->template = 'categories';
    }

    public function getdeftitle() {
        return Lang::get('default', 'categories');
    }

    public function getowner() {
        return Owner::i();
    }

}