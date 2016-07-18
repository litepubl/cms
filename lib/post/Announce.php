<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\post;

use litepubl\view\Vars;
use litepubl\view\Lang;
use litepubl\view\Schema;

class Announce extends \litepubl\core\Events
{
use \litepubl\core\PoolStorage;

    protected function create()
    {
        parent::create();
$this->basename = 'announce';
        $this->addEvents('beforeexcerpt', 'afterexcerpt', 'onhead');
    }

    private function getKey(string $postanounce): string
    {
        if (!$postanounce || $postanounce == 'excerpt' || $postanounce == 'default') {
            return 'excerpt';
        }

        if ($postanounce === true || $postanounce === 1 || $postanounce == 'lite') {
            return 'lite';
        }

        return 'card';
    }

    public function getPosts(array $items, Schema $schema): string
    {
        if (!count($items)) {
            return '';
        }

        $result = '';
        Posts::i()->loadItems($items);
$theme = $schema->theme;
$tml = $theme->templates['content.excerpts.' . ($schema->postannounce == 'excerpt' ? 'excerpt' : $schema->postannounce . '.excerpt')];
        $vars = new Vars();
$vars->lang = Lang::i('default');

        foreach ($items as $id) {
            $post = Post::i($id);
$view = $post->view;
$view->setTheme($theme);
            $vars->post = $view;
            $result.= $theme->parse($tml);

            // has $author.* tags in tml
            if (isset($vars->author)) {
                unset($vars->author);
            }
        }

        if ($tmlContainer = $theme->templates['content.excerpts' . ($schema->postannounce == 'excerpt' ? '' : '.' . $schema->postannounce) ]) {
            $result = str_replace('$excerpt', $result, $this->theme->parse($tmlContainer));
        }

        return $result;
    }

    public function getPostsNavi(array $items, string $url, int $count, string $postanounce, int $perpage): string
    {
        $result = $this->getPosts($items, $postanounce);

        $app = $this->getApp();
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

    public function getAnHead(array $items): string
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
