<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\view;

class Js extends Merger
 {

    protected function create() {
        parent::create();
        $this->basename = 'jsmerger';
}

    public function addlang($section, $key, array $lang) {
        return $this->addtext($section, $key, 'window.lang = window.lang || {};' . sprintf('lang.%s = %s;', $section, json_encode($lang)));
    }

}
