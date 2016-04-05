<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class adminparser extends baseparser {

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->basename = 'admimparser';
        $this->tagfiles[] = 'themes/admin/admintags.ini';
    }

    public function loadpaths() {
        if (!count($this->tagfiles)) {
            $this->tagfiles[] = 'themes/admin/admintags.ini';
        }

        return parent::loadpaths();
    }

} //class