<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\pages;

use litepubl\core\CoEvents;
use litepubl\core\Event;
use litepubl\core\Context;
use litepubl\post\Announce;
use litepubl\post\Post;
use litepubl\post\Posts;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Schema;
use litepubl\view\Theme;
use litepubl\view\Vars;

class Home extends SingleMenu
{
    public $cacheposts;
    public $midleposts;
    public $page;

    protected function create()
    {
        parent::create();
        $this->basename = 'homepage';
        $this->data['image'] = '';
        $this->data['smallimage'] = '';
        $this->data['showmidle'] = false;
        $this->data['midlecat'] = 0;
        $this->data['showposts'] = true;
        $this->data['includecats'] = array();
        $this->data['excludecats'] = array();
        $this->data['showpagenator'] = true;
        $this->data['archcount'] = 0;
        $this->data['parsetags'] = false;
        $this->coinstances[] = new CoEvents($this, 'onbeforegetitems', 'ongetitems');
        $this->cacheposts = false;
        $this->midleposts = false;
    }

    public function getIndex_tml()
    {
        $theme = $this->theme;
        if (!empty($theme->templates['index.home'])) {
            return $theme->templates['index.home'];
        }

        return false;
    }

    public function request(Context $context)
    {
        if (!$this->showpagenator && ($context->request->page > 1)) {
            $context->response->status = 404;
            return;
        }

        $this->page = $context->request->page;
        return parent::request($context);
    }

    public function getHead(): string
    {
        $result = parent::gethead();

        $theme = Schema::getSchema($this)->theme;
        $result.= $theme->templates['head.home'];

        if ($this->showposts) {
            $items = $this->getIdPosts();
            $announce = new Announce($theme);
            $result.= $announce->getanHead($items);
        }

        Theme::$vars['home'] = $this;
        return $theme->parse($result);
    }

    public function getTitle(): string
    {
        return '';
    }

    public function getBefore(): string
    {
        if ($result = $this->content) {
            $theme = $this->theme;
            $result = $theme->simple($result);
            if ($this->parsetags || $this->getApp()->options->parsepost) {
                $result = $theme->parse($result);
            }

            return $result;
        }

        return '';
    }

    public function getCont(): string
    {
        $result = '';
        if ($this->page == 1) {
            $result.= $this->getbefore();
            if ($this->showmidle && $this->midlecat) {
                $result.= $this->getmidle();
            }
        }

        if ($this->showposts) {
            $result.= $this->getpostnavi();
        }

        return $result;
    }

    public function getPostnavi(): string
    {
        $items = $this->getIdPosts();
        $schema = Schema::getSchema($this);
        $announce = new Announce($schema->theme);
        $result = $announce->getPosts($items, $schema->postanounce);
        if ($this->showpagenator) {
            $perpage = $schema->perpage ? $schema->perpage : $this->getApp()->options->perpage;
            $result.= $schema->theme->getpages($this->url, $this->page, ceil($this->data['archcount'] / $perpage));
        }

        return $result;
    }

    public function getIdposts(): array
    {
        if (is_array($this->cacheposts)) {
            return $this->cacheposts;
        }

        if ($result = $this->onbeforegetitems()) {
            return $result;
        }

        $posts = Posts::i();
        $schema = Schema::getSchema($this);
        $perpage = $schema->perpage ? $schema->perpage : $this->getApp()->options->perpage;
        $from = ($this->page - 1) * $perpage;
        $order = $schema->invertorder ? 'asc' : 'desc';

        $p = $this->getApp()->db->prefix . 'posts';
        $ci = $this->getApp()->db->prefix . 'categoriesitems';

        if ($where = $this->getwhere()) {
            $result = $posts->db->res2id(
                $posts->db->query(
                    "select $p.id as id, $ci.item as item from $p, $ci
      where    $where and $p.id = $ci.post and $p.status = 'published'
      order by  $p.posted $order limit $from, $perpage"
                )
            );

            $result = array_unique($result);
            $posts->loaditems($result);
        } else {
            $this->data['archcount'] = $posts->archivescount;
            $result = $posts->getpage(0, $this->page, $perpage, $schema->invertorder);
        }

        $this->callevent(
            'ongetitems', array(&$result
            )
        );
        $this->cacheposts = $result;
        return $result;
    }

    public function getWhere(): string
    {
        $result = '';
        $p = $this->getApp()->db->prefix . 'posts';
        $ci = $this->getApp()->db->prefix . 'categoriesitems';
        if ($this->showmidle && $this->midlecat) {
            $ex = $this->getmidleposts();
            if (count($ex)) {
                $result.= sprintf('%s.id not in (%s) ', $p, implode(',', $ex));
            }
        }

        $include = $this->data['includecats'];
        $exclude = $this->data['excludecats'];

        if (count($include) > 0) {
            if ($result) {
                $result.= ' and ';
            }
            $result.= sprintf('%s.item  in (%s)', $ci, implode(',', $include));
        }

        if (count($exclude) > 0) {
            if ($result) {
                $result.= ' and ';
            }
            $result.= sprintf('%s.item  not in (%s)', $ci, implode(',', $exclude));
        }

        return $result;
    }

    public function postsChanged(Event $event)
    {
        if (!$this->showposts || !$this->showpagenator) {
            return;
        }

        if ($where = $this->getwhere()) {
            $db = $this->db;
            $p = $this->getApp()->db->prefix . 'posts';
            $ci = $this->getApp()->db->prefix . 'categoriesitems';

            $res = $db->query(
                "select count(DISTINCT $p.id) as count from $p, $ci
      where    $where and $p.id = $ci.post and $p.status = 'published'"
            );

            if ($r = $res->fetch_assoc()) {
                $this->data['archcount'] = (int)$r['count'];
            }
        } else {
            $this->data['archcount'] = Posts::i()->archivescount;
        }

        $this->save();
    }

    public function getMidletitle(): string
    {
        if ($idcat = $this->midlecat) {
            return $this->getdb('categories')->getvalue($idcat, 'title');
        }

        return '';
    }

    public function getMidleposts(): array
    {
        if (is_array($this->midleposts)) {
            return $this->midleposts;
        }

        $posts = Posts::i();
        $p = $posts->thistable;
        $ci = $this->getApp()->db->prefix . 'categoriesitems';
        $this->midleposts = $posts->db->res2id(
            $posts->db->query(
                "select $p.id as id, $ci.post as post from $p, $ci
    where    $ci.item = $this->midlecat and $p.id = $ci.post and $p.status = 'published'
    order by  $p.posted desc limit " . $this->getApp()->options->perpage
            )
        );

        if (count($this->midleposts)) {
            $posts->loaditems($this->midleposts);
        }
        return $this->midleposts;
    }

    public function getMidle(): string
    {
        $result = '';
        $items = $this->getmidleposts();
        if (!count($items)) {
            return '';
        }

        $vars = new Vars();
        $vars->lang = Lang::i('default');
        $vars->home = $this;
        $theme = $this->theme;
        $tml = $theme->templates['content.home.midle.post'];
        foreach ($items as $id) {
            $vars->post = Post::i($id);
            $result.= $theme->parse($tml);
            // has $author.* tags in tml
            if (isset($vars->author)) {
                unset($vars->author);
            }
        }

        $tml = $theme->templates['content.home.midle'];
        if ($tml) {
            $args = new Args();
            $args->post = $result;
            $args->midletitle = $this->midletitle;
            $result = $theme->parseArg($tml, $args);
        }

        return $result;
    }
}
