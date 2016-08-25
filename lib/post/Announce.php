<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\post;

use litepubl\view\Lang;
use litepubl\view\Schema;
use litepubl\view\Vars;

/**
 * Post announces
 *
 * @property-write callable $before
 * @property-write callable $after
 * @property-write callable $onHead
 * @method         array before(array $params)
 * @method         array after(array $params)
 * @method         array onHead(array $params)
 */

class Announce extends \litepubl\core\Events
{
    use \litepubl\core\PoolStorageTrait;

    protected function create()
    {
        parent::create();
        $this->basename = 'announce';
        $this->addEvents('before', 'after', 'onhead');
    }

    public function getHead(array $items): string
    {
        $result = '';
        if (count($items)) {
            Posts::i()->loadItems($items);

            foreach ($items as $id) {
                $post = Post::i($id);
                $result.= $post->rawhead;
            }
        }

        $r = $this->onHead(['content' => $result, 'items' => $items]);
        return $r['content'];
    }

    public function getPosts(array $items, Schema $schema): string
    {
        $r = $this->before(['content' => '', 'items' => $items, 'schema' => $schema]);
        $result = $r['content'];
            $theme = $schema->theme;
        $items = $r['items'];
        if (count($items)) {
            Posts::i()->loadItems($items);
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
        }

        if ($tmlContainer = $theme->templates['content.excerpts' . ($schema->postannounce == 'excerpt' ? '' : '.' . $schema->postannounce) ]) {
            $result = str_replace('$excerpt', $result, $theme->parse($tmlContainer));
        }

        $r = $this->after(['content' => $result, 'items' => $items, 'schema' => $schema]);
        return $r['content'];
    }

    public function getNavi(array $items, Schema $schema, string $url, int $count): string
    {
        $result = $this->getPosts($items, $schema);
        $result .= $this->getPages($schema, $url, $count);
        return $result;
    }

    public function getPages(Schema $schema, string $url, int $count): string
    {
        $app = $this->getApp();
        if ($schema->perpage) {
            $perpage = $schema->perpage;
        } else { 
            $perpage = $app->options->perpage;
        }
        
        return $schema->theme->getPages($url, $app->context->request->page, ceil($count / $perpage));
    }

    //used in plugins such as singlecat
    public function getLinks(string $where, string $tml): string
    {
        $db = $this->getApp()->db;
        $t = $db->posts;
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
        $theme = Theme::i();
        foreach ($items as $item) {
            $args->add($item);
            $result.= $theme->parseArg($tml, $args);
        }

        return $result;
    }
}
