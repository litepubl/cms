<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class ttogglecode extends tplugin {

    public static function i() {
        return getinstance(__class__);
    }

    public function install() {
        tjsmerger::i()->add('default', $this->jsfile);
    }

    public function uninstall() {
        tjsmerger::i()->deletefile('default', $this->jsfile);
    }

    public function getjsfile() {
        return '/plugins/' . basename(dirname(__file__)) . '/togglecode.min.js';
    }

} //class