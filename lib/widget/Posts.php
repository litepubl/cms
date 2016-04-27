<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\widget;
use litepubl\view\Theme;
use litepubl\view\Lang;
use litepubl\post\Posts as PostItems;

class Posts extends Widget
 {

    protected function create() {
        parent::create();
        $this->basename = 'widget.posts';
        $this->template = 'posts';
        $this->adminclass = '\litepubl\admin\widget\MaxCount';
        $this->data['maxcount'] = 10;
    }

    public function getDeftitle() {
        return Lang::get('default', 'recentposts');
    }

    public function getContent($id, $sidebar) {
        $posts = PostsItems::i();
        $list = $posts->getpage(0, 1, $this->maxcount, false);
        $theme = Theme::i();
        return $theme->getpostswidgetcontent($list, $sidebar, '');
    }

}