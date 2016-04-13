<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\widget;
use litepubl\tag\Tags as Owner;
use litepubl\view\Lang;

class Tags extends CommonTags
{

    protected function create() {
        parent::create();
        $this->basename = 'widget.tags';
        $this->template = 'tags';
        $this->sortname = 'title';
        $this->showcount = false;
    }

    public function getdeftitle() {
        return Lang::get('default', 'tags');
    }

    public function getowner() {
        return Owner::i();
    }

}