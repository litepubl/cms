<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\post;

class Factory
{
use \litepubl\core\Singleton;

public function __get($name) {
return $this->{'get' . $name}();
}

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
        return \litepubl\comments\Comments::i($id);
    }

    public function getpingbacks($id) {
        return \litepubl\comments\Pingbacks::i($id);
    }

    public function getmeta($id) {
        return Meta::i($id);
    }

public function getMainView() {
return \litepubl\view\MainView::i();
}

public function gettheme() {
return \litepubl\view\Theme::i();
}

public function getusers() {
return \litepubl\core\Users::i();
}

public function getuserpages() {
return \litepubl\pages\Users::i();
}

    public function gettransform(tpost $post) {
        return Transform::i($post);
    }

    public function add(tpost $post) {
        return Transform::add($post);
    }

} //class