<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\oldestposts;

use litepubl\core\Str;
use litepubl\post\Posts;
use litepubl\view\Lang;
use litepubl\widget\View;
use litepubl\widget\Widgets;

class Oldestposts extends \litepubl\widget\Depended
{

    protected function create()
    {
        parent::create();
        $this->basename = 'widget.oldestposts';
        $this->template = 'posts';
        $this->adminclass = __NAMESPACE__ . '\Admin';
        $this->cache = 'nocache';
        $this->data['maxcount'] = 10;
    }

    public function getDeftitle(): string
    {
        return Lang::get('default', 'prev');
    }

    public function getContent(int $id, int $sidebar): string
    {
        $post = Widgets::i()->findcontext('litepubl\post\Post');
        if (!$post) {
            return '';
        }

        $posts = Posts::i();
        $items = $posts->finditems("status = 'published' and posted < '$post->sqlDate' ", ' order by posted desc limit ' . $this->maxcount);

        if (!count($items)) {
            return '';
        }

        $view = new View();
        return $view->getPosts($items, $sidebar, '');
    }

}

