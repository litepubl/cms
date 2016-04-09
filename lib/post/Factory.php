<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\post;
use litepubl\core\Data;

class Factory extends Data {

    public function getposts() {
        return Posts::i();
    }

    public function getfiles() {
        return Files::i();
    }

    public function gettags() {
        return \litepubl\tag\Tags::i();
    }

    public function getcats() {
        return \litepubl\tag\Categories::i();
    }

    public function getcategories() {
        return $this->getcats();
    }

    public function gettemplatecomments() {
        return ttemplatecomments::i();
    }

    public function getcomments($id) {
        return Comments::i($id);
    }

    public function getpingbacks($id) {
        return Pingbacks::i($id);
    }

    public function getmeta($id) {
        return Meta::i($id);
    }

    public function gettransform(tpost $post) {
        return Transform::i($post);
    }

    public function add(tpost $post) {
        return Transform::add($post);
    }

} //class