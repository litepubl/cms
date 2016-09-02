<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\plugins\menucategories;

use litepubl\tag\Cats;
use litepubl\view\Args;
use litepubl\view\Theme;

class Plugin extends \litepubl\core\Plugin
{
    public $tree;
    public $exitems;

    protected function create()
    {
        parent::create();
        $this->addmap('tree', []);
        $this->addmap('exitems', []);
    }

    public function getMenu($hover, $current)
    {
        $result = '';
        $categories = Cats::i();
        $categories->loadAll();

        if (count($this->tree)) {
            $theme = Theme::i();
            if ($hover) {
                $items = $this->getSubMenu($this->tree, $current);
            } else {
                $items = '';
                $tml = $theme->templates['menu.item'];
                $args = new Args();
                $args->submenu = '';
                foreach ($this->tree as $id => $subitems) {
                    if ($this->exclude($id)) {
                        continue;
                    }

                    $args->add($categories->items[$id]);
                    $items.= $current == $id ? $theme->parseArg($theme->templates['menu.current'], $args) : $theme->parseArg($tml, $args);
                }
            }

            $result = str_replace('$item', $items, $theme->templates['menu']);
        }
        return $result;
    }

    public function exclude($id)
    {
        return in_array($id, $this->exitems);
    }

    private function getSubMenu(&$tree, $current)
    {
        $result = '';
        $categories = Cats::i();
        $theme = Theme::i();
        $tml = $theme->templates['menu.item'];
        $tml_submenu = $theme->templates['menu.item.submenu'];
        $args = new Args();
        foreach ($tree as $id => $items) {
            if ($this->exclude($id)) {
                continue;
            }

            $submenu = '';
            if ((count($items) > 0) && ($s = $this->getsubmenu($items, $current))) {
                $submenu = str_replace('$items', $s, $tml_submenu);
            }
            $args->submenu = $submenu;
            $args->add($categories->items[$id]);
            $result.= $theme->parseArg($current == $id ? $theme->templates['menu.current'] : $tml, $args);
        }
        return $result;
    }

    public function buildTree()
    {
        $categories = Cats::i();
        $categories->loadAll();
        $this->tree = $this->getSubTree(0);

        $this->exitems = array_intersect(array_keys($categories->items), $this->exitems);
        $this->save();
    }

    private function getSubTree($parent)
    {
        $result = [];
        $categories = Cats::i();
        // first step is a find all childs and sort them
        $sort = [];
        foreach ($categories->items as $id => $item) {
            if ($item['parent'] == $parent) {
                $sort[$id] = (int)$item['customorder'];
            }
        }
        arsort($sort, SORT_NUMERIC);
        $sort = array_reverse($sort, true);

        foreach ($sort as $id => $order) {
            $result[$id] = $this->getsubtree($id);
        }
        return $result;
    }
}
