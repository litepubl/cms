<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class tpostcontentplugin extends tplugin {

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->data['before'] = '';
        $this->data['after'] = '';
    }

    public function beforecontent($post, &$content) {
        $content = $this->before . $content;
    }

    public function aftercontent($post, &$content) {
        $content.= $this->after;
    }

} //class