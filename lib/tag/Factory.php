<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\tag;

use litepubl\post\Post;
use litepubl\post\Posts;

class Factory
{
    public function __get($name)
    {
        return $this->{'get' . $name}();
    }

    public function getPosts()
    {
        return Posts::i();
    }

    public function getPost($id)
    {
        return Post::i($id);
    }
}
