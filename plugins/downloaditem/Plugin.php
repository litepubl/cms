<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace litepubl\plugins\downloaditem;

use litepubl\plugins\polls\Polls;
use litepubl\post\Post;

class Plugin extends \litepubl\post\Posts
{

    protected function create()
    {
        parent::create();
        $this->childTable = 'downloaditems';
    }

    public function createPoll(int $id): int
    {
        return polls::i()->add('like', $id, 'post');
    }

    public function add(Post $post): int
    {
        $post->updateFiltered();
        $id = parent::add($post);
        $this->createPoll($id);
        return $id;
    }

    public function edit(Post $post)
    {
        $post->updateFiltered();
        return parent::edit($post);
    }
}
