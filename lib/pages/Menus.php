<?php

namespace litepubl\pages;
use litepubl\view\Theme;
use litepubl\view\Args;
use litepubl\utils\LinkGenerator;

class Menus extends \litepubl\core\Items
{
    public $tree;

    protected function create() {
        parent::create();
        $this->addevents('edited', 'onprocessform', 'onbeforemenu', 'onmenu', 'onitems', 'onsubitems', 'oncontent');

        $this->dbversion = false;
        $this->basename = 'menus' . DIRECTORY_SEPARATOR . 'index';
        $this->addmap('tree', array());
        $this->data['idhome'] = 0;
        $this->data['home'] = false;
    }

    public function getlink($id) {
        return sprintf('<a href="%1$s%2$s" title="%3$s">%3$s</a>', litepubl::$site->url, $this->items[$id]['url'], $this->items[$id]['title']);
    }

    public function getdir() {
        return litepubl::$paths->data . 'menus' . DIRECTORY_SEPARATOR;
    }

    public function add(Menu $item) {
        if ($item instanceof tfakemenu) return $this->addfakemenu($item);
        //fix null fields
        foreach ($item->get_owner_props() as $prop) {
            if (!isset($item->data[$prop])) $item->data[$prop] = '';
        }

        if ($item instanceof thomepage) {
            $item->url = '/';
        } else {
            $linkgen = LinkGenerator::i();
            $item->url = $linkgen->addurl($item, 'menu');
        }

        if ($item->idview == 1) {
            $schemes = Schemes::i();
            if (isset($schemes->defaults['menu'])) {
$item->data['idview'] = $schemes->defaults['menu'];
}
        }

        $id = ++$this->autoid;
        $this->items[$id] = array(
            'id' => $id,
            'class' => get_class($item)
        );
        //move props
        foreach ($item->get_owner_props() as $prop) {
            if (array_key_exists($prop, $item->data)) {
                $this->items[$id][$prop] = $item->data[$prop];
                unset($item->data[$prop]);
            } else {
                $this->items[$id][$prop] = $item->$prop;
            }
        }

        $item->id = $id;
        $item->idurl = litepubl::$urlmap->Add($item->url, get_class($item) , $item->id);
        if ($item->status != 'draft') $item->status = 'published';
        $this->lock();
        $this->sort();
        $item->save();
        $this->unlock();
        $this->added($id);
        litepubl::$urlmap->clearcache();
        return $id;
    }

    public function addfake($url, $title) {
        if ($id = $this->url2id($url)) return $id;

        $fake = new tfakemenu();
        $fake->title = $title;
        $fake->url = $url;
        $fake->order = $this->autoid;
        return $this->addfakemenu($fake);
    }

    public function addfakemenu(Menu $menu) {
        $item = array(
            'id' => ++$this->autoid,
            'idurl' => 0,
            'class' => get_class($menu)
        );

        //fix null fields
        foreach ($menu->get_owner_props() as $prop) {
            if (!isset($menu->data[$prop])) $menu->data[$prop] = '';
            $item[$prop] = $menu->$prop;
            if (array_key_exists($prop, $menu->data)) unset($menu->data[$prop]);
        }

        $menu->id = $this->autoid;
        $this->items[$this->autoid] = $item;
        $this->lock();
        $this->sort();
        $this->added($this->autoid);
        $this->unlock();
        litepubl::$urlmap->clearcache();
        return $this->autoid;
    }

    public function additem(array $item) {
        $item['id'] = ++$this->autoid;
        $item['order'] = $this->autoid;
        $item['status'] = 'published';

        if ($idurl = litepubl::$urlmap->urlexists($item['url'])) {
            $item['idurl'] = $idurl;
        } else {
            $item['idurl'] = litepubl::$urlmap->add($item['url'], $item['class'], $this->autoid, 'get');
        }

        $this->items[$this->autoid] = $item;
        $this->sort();
        $this->save();
        litepubl::$urlmap->clearcache();
        return $this->autoid;
    }

    public function edit(Menu $item) {
        if (!(($item instanceof thomepage) || ($item instanceof tfakemenu))) {
            $linkgen = LinkGenerator::i();
            $linkgen->editurl($item, 'menu');
        }

        $this->lock();
        $this->sort();
        $item->save();
        $this->unlock();
        $this->edited($item->id);
        litepubl::$urlmap->clearcache();
    }

    public function delete($id) {
        if (!$this->itemexists($id)) return false;
        if ($id == $this->idhome) return false;
        if ($this->haschilds($id)) return false;
        if ($this->items[$id]['idurl'] > 0) {
            litepubl::$urlmap->delete($this->items[$id]['url']);
        }
        $this->lock();
        unset($this->items[$id]);
        $this->sort();
        $this->unlock();
        $this->deleted($id);
        litepubl::$storage->remove($this->dir . $id);
        litepubl::$urlmap->clearcache();
        return true;
    }

    public function deleteurl($url) {
        if ($id = $this->url2id($url)) return $this->delete($id);
    }

    public function deletetree($id) {
        if (!$this->itemexists($id)) return false;
        if ($id == $this->idhome) return false;
        $this->lock();
        $childs = $this->getchilds($id);
        foreach ($childs as $child) {
            $this->deletetree($child);
        }
        $this->delete($id);
        $this->unlock();
    }

    public function url2id($url) {
        foreach ($this->items as $id => $item) {
            if ($url == $item['url']) return $id;
        }
        return false;
    }

    public function remove($id) {
        if (!$this->itemexists($id) || $this->haschilds($id)) {
return false;
}

        $this->lock();
        unset($this->items[$id]);
        $this->sort();
        $this->unlock();
        $this->deleted($id);
        litepubl::$urlmap->clearcache();
        return true;
    }

    public function haschilds($idparent) {
        foreach ($this->items as $id => $item) {
            if ($item['parent'] == $idparent) return $id;
        }
        return false;
    }

public function renameClass($oldclass, $newclass) {
foreach ($this->items as $id => $item) {
if ($oldcalss == $item['class']) {
$this->items[$id]['class'] = $newclass;
}
}
}

    public function sort() {
        $this->tree = $this->getsubtree(0);
    }

    private function getsubtree($parent) {
        $result = array();
        // first step is a find all childs and sort them
        $sort = array();
        foreach ($this->items as $id => $item) {
            if (($item['parent'] == $parent) && ($item['status'] == 'published')) {
                $sort[$id] = (int)$item['order'];
            }
        }
        arsort($sort, SORT_NUMERIC);
        $sort = array_reverse($sort, true);

        foreach ($sort as $id => $order) {
            $result[$id] = $this->getsubtree($id);
        }
        return $result;
    }

    public function getparent($id) {
        return $this->items[$id]['parent'];
    }

    //return array of id
    public function getparents($id) {
        $result = array();
        $id = $this->items[$id]['parent'];
        while ($id != 0) {
            //array_unshift ($result, $id);
            $result[] = $id;
            $id = $this->items[$id]['parent'];
        }
        return $result;
    }

    //ищет в дереве список детей, так как они уже отсортированы
    public function getchilds($id) {
        if ($id == 0) {
            $result = array();
            foreach ($this->tree as $iditem => $items) {
                $result[] = $iditem;
            }
            return $result;
        }

        $parents = array(
            $id
        );
        $parent = $this->items[$id]['parent'];
        // fix of circle bug
        while ($parent && ($parent != $id)) {
            array_unshift($parents, $parent);
            $parent = $this->items[$parent]['parent'];
        }

        $tree = $this->tree;
        foreach ($parents as $parent) {
            foreach ($tree as $iditem => $items) {
                if ($iditem == $parent) {
                    $tree = $items;
                    break;
                }
            }
        }
        return array_keys($tree);
    }

    public function exclude($id) {
        return !$this->home && ($id == $this->idhome);
    }

    public function getmenu($hover, $current) {
        $result = '';
        $this->callevent('onbeforemenu', array(&$result, &$hover,
            $current
        ));
        if (count($this->tree) > 0) {
            $theme = Theme::i();
            $args = new Args();
            if ($hover) {
                $items = $this->getsubmenu($this->tree, $current, $hover === 'bootstrap');
            } else {
                $items = '';
                $tml = $theme->templates['menu.item'];
                $args->submenu = '';
                foreach ($this->tree as $id => $subitems) {
                    if ($this->exclude($id)) continue;
                    $args->add($this->items[$id]);
                    $items.= $current == $id ? $theme->parsearg($theme->templates['menu.current'], $args) : $theme->parsearg($tml, $args);
                }
            }

            $this->callevent('onitems', array(&$items
            ));
            $args->item = $items;
            $result = $theme->parsearg($theme->templates['menu'], $args);
        }
        $this->callevent('onmenu', array(&$result
        ));
        return $result;
    }

    private function getsubmenu(&$tree, $current, $bootstrap) {
        $result = '';
        $theme = Theme::i();
        $tml_item = $theme->templates['menu.item'];
        $tml_submenu = $theme->templates['menu.item.submenu'];
        $tml_single = $theme->templates['menu.single'];
        $tml_current = $theme->templates['menu.current'];

        $args = new targs();
        foreach ($tree as $id => $items) {
            if ($this->exclude($id)) continue;
            $args->add($this->items[$id]);
            $submenu = '';
            if (count($items)) {
                if ($bootstrap) {
                    $args->submenu = '';
                    $submenu = $theme->parsearg($tml_single, $args);
                }
                $submenu.= $this->getsubmenu($items, $current, $bootstrap);
                $submenu = str_replace('$items', $submenu, $tml_submenu);
            }

            $this->callevent('onsubitems', array(
                $id, &$submenu
            ));
            $args->submenu = $submenu;
            $tml = $current == $id ? $tml_current : ($submenu ? $tml_item : $tml_single);
            $result.= $theme->parsearg($tml, $args);
        }

        return $result;
    }

    public function class2id($class) {
        foreach ($this->items as $id => $item) {
            if ($class == $item['class']) return $id;
        }
        return false;
    }

    public function getsitemap($from, $count) {
        return $this->externalfunc(__class__, 'Getsitemap', array(
            $from,
            $count
        ));
    }

    public function classRenamed($oldclass, $newclass) {
        foreach ($this->items as $id => $item) {
            if ($oldclass == $item['class']) {
                $this->items[$id]['class'] = $newclass;
            }
        }

        $this->save();
    }

} //class
