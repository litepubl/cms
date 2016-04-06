<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tpostfactory extends tdata {

    public function getposts() {
        return tposts::i();
    }

    public function getfiles() {
        return tfiles::i();
    }

    public function gettags() {
        return ttags::i();
    }

    public function getcats() {
        return tcategories::i();
    }

    public function getcategories() {
        return tcategories::i();
    }

    public function gettemplatecomments() {
        return ttemplatecomments::i();
    }

    public function getcomments($id) {
        return tcomments::i($id);
    }

    public function getpingbacks($id) {
        return tpingbacks::i($id);
    }

    public function getmeta($id) {
        return tmetapost::i($id);
    }

    public function gettransform(tpost $post) {
        return tposttransform::i($post);
    }

    public function add(tpost $post) {
        return tposttransform::add($post);
    }

} //class