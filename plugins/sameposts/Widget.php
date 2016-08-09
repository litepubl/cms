<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\plugins\sameposts;

use litepubl\core\Event;
use litepubl\post\Post;
use litepubl\post\Posts;
use litepubl\view\Lang;

class Widget extends \litepubl\widget\Contextual
{
    const POSTCLASS = 'litepubl\post\Post';

    protected function create()
    {
        parent::create();
        $this->table = 'sameposts';
        $this->basename = 'widget.sameposts';
        $this->template = 'posts';
        $this->adminclass = __NAMESPACE__ . '\Admin';
        $this->cache = 'nocache';
        $this->data['maxcount'] = 10;
    }

    public function getDeftitle(): string
    {
        return Lang::get('default', 'sameposts');
    }

    public function postsChanged(Event $event)
    {
        $this->db->exec("truncate $this->thistable");
    }

    private function findSame(int $idpost): array
    {
        $posts = Posts::i();
        $post = Post::i($idpost);
        if (count($post->categories) == 0) {
            return [];
        }

        $cats = $post->factory->cats;
        $cats->loadAll();
        $same = [];
        foreach ($post->categories as $idcat) {
            if (!isset($cats->items[$idcat])) {
                continue;
            }

            $itemsposts = $cats->itemsposts->getposts($idcat);
            $itemsposts = $posts->stripDrafts($itemsposts);
            foreach ($itemsposts as $id) {
                if ($id == $idpost) {
                    continue;
                }

                $same[$id] = isset($same[$id]) ? $same[$id] + 1 : 1;
            }
        }

        arsort($same);
        return array_slice(array_keys($same), 0, $this->maxcount);
    }

    public function getSame(int $id): array
    {
        $items = $this->db->getValue($id, 'items');
        if (is_string($items)) {
            return $items == '' ? [] : explode(',', $items);
        } else {
            $result = $this->findSame($id);
            $this->db->add(
                [
                'id' => $id,
                'items' => implode(',', $result)
                ]
            );
            return $result;
        }
    }

    public function getContent(int $id, int $sidebar): string
    {
        $post = $this->getWidgets()->findContext(static::POSTCLASS);
        $list = $this->getSame($post->id);
        if (count($list) == 0) {
            return '';
        }

        $posts = Posts::i();
        $posts->loadItems($list);
        return $this->view->getPosts($list, $sidebar, '');
    }
}
