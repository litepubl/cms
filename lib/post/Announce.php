<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\post;

use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Theme;
use litepubl\view\Vars;

class Announce
{
    public $theme;

    public function __construct(Theme $theme = null)
    {
        $this->theme = $theme ? $theme : Theme::context();
    }

    private function getKey($postanounce)
    {
        if (!$postanounce || $postanounce == 'excerpt' || $postanounce == 'default') {
            return 'excerpt';
        }

        if ($postanounce === true || $postanounce === 1 || $postanounce == 'lite') {
            return 'lite';
        }

        return 'card';
    }

    public function getPosts(array $items, $postanounce)
    {
        if (!count($items)) {
            return '';
        }

        $result = '';
        $keyTemplate = $this->getKey($postanounce);
        Posts::i()->loaditems($items);
        $this->theme->setVar('lang', Lang::i('default'));
        $vars = new Vars();
        $view = new View();
        $vars->post = $view;

        foreach ($items as $id) {
            $post = Post::i($id);
            $view->setPost($post);
            $result.= $view->getContExcerpt($keyTemplate);
            // has $author.* tags in tml
            if (isset($vars->author)) {
                unset($vars->author);
            }
        }

        if ($tml = $this->theme->templates['content.excerpts' . ($keyTemplate == 'excerpt' ? '' : '.' . $keyTemplate) ]) {
            $result = str_replace('$excerpt', $result, $this->theme->parse($tml));
        }

        return $result;
    }

    public function getPostsNavi(array $items, $url, $count, $postanounce, $perpage)
    {
        $result = $this->getPosts($items, $postanounce);

        $app = $this->theme->getApp();
        if (!$perpage) {
            $perpage = $app->options->perpage;
        }

        $result.= $this->theme->getPages($url, $app->context->request->page, ceil($count / $perpage));
        return $result;
    }

    public function getLinks($where, $tml)
    {
        $theme = $this->theme;
        $db = $theme->getApp()->db;
        $items = $db->res2assoc(
            $db->query(
                "select $t.id, $t.title, $db->urlmap.url as url  from $t, $db->urlmap
    where $t.status = 'published' and $where and $db->urlmap.id  = $t.idurl"
            )
        );

        if (!count($items)) {
            return '';
        }

        $result = '';
        $args = new Args();
        foreach ($items as $item) {
            $args->add($item);
            $result.= $theme->parseArg($tml, $args);
        }
        return $result;
    }

    public function getAnHead(array $items)
    {
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