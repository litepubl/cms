<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl;

use litepubl\view\Args;
use litepubl\view\Theme;

class tcategoriesmenu extends \litepubl\core\Plugin
{
    public $tree;
    public $exitems;

    public static function i()
    {
        return static ::iGet(__class__);
    }

    protected function create()
    {
        parent::create();
        $this->addmap('tree', array());
        $this->addmap('exitems', array());
    }

    public function getMenu($hover, $current)
    {
        $result = '';
        $categories = tcategories::i();
        $categories->loadall();
        //$this->buildtree();
        //var_dump($this->tree);
        if (count($this->tree) > 0) {
            $theme = Theme::i();
            if ($hover) {
                $items = $this->getsubmenu($this->tree, $current);
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

    private function getSubmenu(&$tree, $current)
    {
        $result = '';
        $categories = tcategories::i();
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

    public function buildtree()
    {
        $categories = tcategories::i();
        $categories->loadall();
        $this->tree = $this->getsubtree(0);
        //var_dump($this->exitems );
        $this->exitems = array_intersect(array_keys($categories->items) , $this->exitems);
        $this->save();
    }

    private function getSubtree($parent)
    {
        $result = array();
        $categories = tcategories::i();
        // first step is a find all childs and sort them
        $sort = array();
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

