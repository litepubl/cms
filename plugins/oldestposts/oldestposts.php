<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\core\Str;

class toldestposts extends tclasswidget {

    public static function i() {
        return getinstance(__class__);
    }

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
        $post = $this->getcontext('tpost');
        $posts = tposts::i();
        if (dbversion) {
            $items = $posts->finditems("status = 'published' and posted < '$post->Str::sqlDate' ", ' order by posted desc limit ' . $this->maxcount);
        } else {
            $arch = array_keys($posts->archives);
            $i = array_search($post->id, $arch);
            if (!is_int($i)) {
 return '';
}


            $items = array_slice($arch, $i + 1, $this->maxcount);
        }

        if (count($items) == 0) {
 return '';
}



        $theme = ttheme::i();
        return $theme->getpostswidgetcontent($items, $sidebar, '');
    }

} //class