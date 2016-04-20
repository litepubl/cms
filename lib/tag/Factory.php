<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\tag;

class Factory
{

public function __get($name) {
return $this->{'get' . $name}();
}

    public function getPosts() {
        return \litepubl\post\Posts::i();
    }

    public function getPost($id) {
        return \litepubl\post\Post::i($id);
    }

}