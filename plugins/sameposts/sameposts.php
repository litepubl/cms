<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\Lang;
use litepubl\widget\View;

class tsameposts extends tclasswidget {

    public static function i() {
        return static::iGet(__class__);
    }

    protected function create() {
        parent::create();
            $this->table = 'sameposts';
        $this->basename = 'widget.sameposts';
        $this->template = 'posts';
        $this->adminclass = 'tadminsameposts';
        $this->cache = 'nocache';
        $this->data['maxcount'] = 10;
    }

    public function getDeftitle() {
        return Lang::get('default', 'sameposts');
    }

    public function postschanged() {
            $this->db->exec("truncate $this->thistable");
    }

    private function findsame($idpost) {
        $posts = tposts::i();
        $post = tpost::i($idpost);
        if (count($post->categories) == 0) {
 return array();
}


        $cats = tcategories::i();
        $cats->loadall();
        $same = array();
        foreach ($post->categories as $idcat) {
            if (!isset($cats->items[$idcat])) {
 continue;
}


            $itemsposts = $cats->itemsposts->getposts($idcat);
            $itemsposts = $posts->stripdrafts($itemsposts);
            foreach ($itemsposts as $id) {
                if ($id == $idpost) {
 continue;
}


                $same[$id] = isset($same[$id]) ? $same[$id] + 1 : 1;
            }
        }

        arsort($same);
        return array_slice(array_keys($same) , 0, $this->maxcount);
    }

    public function getSame($id) {
        $items = $this->db->getvalue($id, 'items');
        if (is_string($items)) {
            return $items == '' ? array() : explode(',', $items);
        } else {
            $result = $this->findsame($id);
            $this->db->add(array(
                'id' => $id,
                'items' => implode(',', $result)
            ));
            return $result;
        }
    }

    public function getContent($id, $sidebar) {
        $post = $this->getcontext('tpost');
        $list = $this->getsame($post->id);
        if (count($list) == 0) {
 return '';
}


        $posts = tposts::i();
        $posts->loaditems($list);
        $view = new View();
        return $view->getPosts($list, $sidebar, '');
    }

}