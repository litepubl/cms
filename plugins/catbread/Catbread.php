<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\plugins\catbread;

use litepubl\core\Arr;
use litepubl\core\Event;
use litepubl\post\Post;
use litepubl\tag\Cats;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Theme;

class Catbread extends \litepubl\core\Plugin
{

    protected function create()
    {
        parent::create();
        $this->data['showhome'] = true;
        $this->data['showchilds'] = true;
        $this->data['childsortname'] = 'title';
        $this->data['showsimilar'] = false;
        $this->data['breadpos'] = 'replace';
        $this->data['similarpos'] = 'after';
    }

    public function getCats()
    {
        return Cats::i();
    }

    public function beforeCat(Event $event)
    {
        $cats = $event->target;
        $idcat = $cats->id;
        if (!$idcat) {
            return;
        }

        $event->content .= $this->getBread($idcat);

        if ($this->showsimilar) {
            $list = [];
            $idposts = $cats->getidposts($idcat);
            foreach ($idposts as $idpost) {
                $list = array_merge($list, Post::i($idpost)->categories);
            }

            Arr::clean($list);
            Arr::deleteValue($list, $idcat);
            $event->content .= $this->getSimilar($list);
        }
    }

    public function getPost()
    {
        $result = '';
        $post = Theme::$vars['post'];
        if (count($post->categories)) {
            if ($this->breadpos == 'replace') {
                foreach ($post->categories as $idcat) {
                    $result.= $this->getbread($idcat);
                }
            } else {
                $result = $this->getbread($post->categories[0]);
            }
        }

        return $result;
    }

    public function getSim()
    {
        if ($this->showsimilar) {
            $post = Theme::$vars['post'];
            if (count($post->categories)) {
                return $this->getsimilar($post->categories);
            }
        }

        return '';
    }

    public function getBread($idcat)
    {
        if (!$idcat) {
            return '';
        }

        $result = '';
        $cats = $this->cats;
        $cats->loadall();
        $parents = $cats->getparents($idcat);
        $parents = array_reverse($parents);

        $showchilds = false;
        if ($this->showchilds) {
            foreach ($cats->items as $id => $item) {
                if ($idcat == (int)$item['parent']) {
                    $showchilds = true;
                    break;
                }
            }
        }

        $theme = Theme::i();
        $tml = $theme->templates['catbread.items.item'];
        $lang = Lang::i('catbread');
        $args = new Args();
        $items = '';
        $index = 1;

        if ($this->showhome) {
            $args->url = '/';
            $args->title = $lang->home;
            $args->index = $index++;
            $items.= $theme->parseArg($tml, $args);
        }

        foreach ($parents as $id) {
            $args->add($cats->getitem($id));
            $args->index = $index++;
            $items.= $theme->parseArg($tml, $args);
        }

        $args->add($cats->getitem($idcat));
        $args->index = $index++;
        $current = $theme->parseArg($theme->templates['catbread.items.current'], $args);

        $childs = '';
        if ($showchilds) {
            $childs = $this->getchilds($idcat);
        }

        $args->item = $items;
        $args->current = $current;
        $args->childs = $childs;
        $args->items = $theme->parseArg($theme->templates['catbread.items'], $args);
        return $theme->parseArg($theme->templates['catbread'], $args);
    }

    public function getChilds($parent)
    {
        $cats = $this->cats;
        $sorted = $cats->getsorted($parent, $this->childsortname, 0);
        if (!count($sorted)) {
            return '';
        }

        $theme = Theme::i();
        $tml = $theme->templates['catbread.items.childs.item'];
        $args = new Args();
        $args->parent = $parent;

        $items = '';
        foreach ($sorted as $id) {
            $args->add($cats->getitem($id));
            $items.= $theme->parseArg($tml, $args);
        }

        $args->item = $items;
        return $theme->parseArg($theme->templates['catbread.items.childs'], $args);
    }

    public function getSimilar($list)
    {
        if (!$this->showsimilar || !count($list)) {
            return '';
        }

        $cats = $this->cats;
        $cats->loadall();
        $parents = [];
        foreach ($list as $id) {
            $parents[] = $cats->getvalue($id, 'parent');
        }

        Arr::clean($parents);
        if (!count($parents)) {
            return '';
        }

        /* without db cant sort
        $similar = array();
        foreach ($cats->items as $id => $item) {
        if (in_array($item['parent'], $parents)) $similar[] = $id;
        }
        */
        $parents = implode(',', $parents);
        $list = implode(',', $list);
        $similar = $cats->db->idselect("parent in ($parents) and id not in ($list) order by $this->childsortname asc");
        Arr::clean($similar);
        if (!count($similar)) {
            return '';
        }

        $theme = Theme::i();
        $args = new Args();
        $items = '';
        foreach ($similar as $id) {
            $args->add($cats->getitem($id));
            $items.= $theme->parseArg($theme->templates['catbread.similar.item'], $args);
        }

        $args->item = $items;
        $args->items = $theme->parseArg($theme->templates['catbread.similar'], $args);
        return $theme->parseArg($theme->templates['catbread'], $args);
    }

    public function themeParsed(Event $event)
    {
        $this->externalfunc(get_class($this), 'Themeparsed', $event->theme);
    }
}
