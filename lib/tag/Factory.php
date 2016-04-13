<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\tag;

class Factory
{

public function __get($name) {
return $this->{'get' . $name}();
}

    public function getposts() {
        return \litepubl\post\Posts::i();
    }

    public function getpost($id) {
        return \litepubl\post\Post::i($id);
    }

}
