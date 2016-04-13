<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\post;

class FileItems extend \litepubl\core\ItemsPosts
{
    protected function create() {
        $this->dbversion = true;
        parent::create();
        $this->basename = 'fileitems';
        $this->table = 'filesitemsposts';
    }

}