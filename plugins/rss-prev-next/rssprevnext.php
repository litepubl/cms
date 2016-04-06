<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class TRSSPrevNext extends tplugin {

    public static function i() {
        return getinstance(__class__);
    }

    public function beforepost($id, &$content) {
        $post = tpost::i($id);
        $content.= $post->prevnext;
    }

} //class