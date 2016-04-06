<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class ttagfactory extends tdata {

    public static function i() {
        return getinstance(__class__);
    }

    public function getposts() {
        return tposts::i();
    }

    public function getpost($id) {
        return tpost::i($id);
    }

} //class