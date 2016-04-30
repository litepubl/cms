<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\plugins\oldestposts;
use litepubl\core\Str;
use litepubl\post\Posts;
use litepubl\widget\View;
use litepubl\view\Lang;

class Oldestposts extends \litepubl\widget\Depended
{

    protected function create() {
        parent::create();
        $this->basename = 'widget.oldestposts';
        $this->template = 'posts';
        $this->adminclass = 'tadminoldestposts';
        $this->cache = 'nocache';
        $this->data['maxcount'] = 10;
    }

    public function getDeftitle() {
        return Lang::get('default', 'prev');
    }

    public function getContent($id, $sidebar) {
        $post = $this->getcontext('\litepubl\post\Post');
        $posts = Posts::i();
            $items = $posts->finditems("status = 'published' and posted < '$post->Str::sqlDate' ", ' order by posted desc limit ' . $this->maxcount);
        if (count($items) == 0) {
 return '';
}

        $view = new View();
        return $view->getPosts($items, $sidebar, '');
    }

}