<?php

namespace litepubl\post;
use litepubl\view\Theme;
use litepubl\view\Args;

class Announce extends \litepubl\core\Events
{

    public function keyanounce($postanounce) {
        if (!$postanounce || $postanounce == 'excerpt' || $postanounce == 'default') {
            return 'excerpt';
        }

        if ($postanounce === true || $postanounce === 1 || $postanounce == 'lite') {
            return 'lite';
        }

        return 'card';
    }

    public function getPosts(array $items, $postanounce) {
        if (!count($items)) {
            return '';
        }

        $result = '';
        $tml_key = $this->keyanounce($postanounce);
        Posts::i()->loaditems($items);

        static ::$vars['lang'] = Lang::i('default');
        foreach ($items as $id) {
            $post = Post::i($id);
            $result.= $post->getcontexcerpt($tml_key);
            // has $author.* tags in tml
            if (isset(static ::$vars['author'])) {
                unset(static ::$vars['author']);
            }
        }

        if ($tml = $this->templates['content.excerpts' . ($tml_key == 'excerpt' ? '' : '.' . $tml_key) ]) {
            $result = str_replace('$excerpt', $result, $this->parse($tml));
        }

        unset(static ::$vars['post']);
        return $result;
    }

    public function getPostsnavi(array $items, $url, $count, $postanounce, $perpage) {
        $result = $this->getposts($items, $postanounce);
        if (!$perpage) $perpage =  $this->getApp()->options->perpage;
        $result.= $this->getpages($url,  $this->getApp()->router->page, ceil($count / $perpage));
        return $result;
    }

    public function getLinks($where, $tml) {
        $db = $this->db;
        $t = $db->posts;
        $items = $db->res2assoc($db->query(
"select $t.id, $t.title, $db->urlmap.url as url  from $t, $db->urlmap
    where $t.status = 'published' and $where and $db->urlmap.id  = $t.idurl"
));

        if (!count($items)) {
 return '';
}

        $result = '';
        $args = new Args();
        $theme = Theme::i();
        foreach ($items as $item) {
            $args->add($item);
            $result.= $theme->parsearg($tml, $args);
        }
        return $result;
    }

    public function getAnHead(array $items) {
        if (!count($items)) {
 return '';
}

Posts::i()->loadItems($items);

        $result = '';
$view = new View();
        foreach ($items as $id) {
$view->setPost(Post::i($id));
            $result.= $view->anhead;
        }

        return $result;
    }

}