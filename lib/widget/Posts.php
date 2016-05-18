<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\widget;

use litepubl\post\Posts as PostItems;
use litepubl\view\Lang;

class Posts extends Widget
{

    protected function create()
    {
        parent::create();
        $this->basename = 'widget.posts';
        $this->template = 'posts';
        $this->adminclass = '\litepubl\admin\widget\MaxCount';
        $this->data['maxcount'] = 10;
    }

    public function getDeftitle(): string
    {
        return Lang::get('default', 'recentposts');
    }

    public function getContent(int $id, int $sidebar): string
    {
        $posts = PostItems::i();
        $list = $posts->getpage(0, 1, $this->maxcount, false);
        $view = new View();
        return $view->getPosts($list, $sidebar, '');
    }

}

