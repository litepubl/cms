<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

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
        $this->adminclass = 'tadminmaxcount';
        $this->data['maxcount'] = 10;
    }

    public function getdeftitle() {
        return Lang::get('default', 'recentposts');
    }

    public function getcontent($id, $sidebar) {
        $posts = PostsItems::i();
        $list = $posts->getpage(0, 1, $this->maxcount, false);
        $theme = Theme::i();
        return $theme->getpostswidgetcontent($list, $sidebar, '');
    }

} //class