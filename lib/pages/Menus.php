<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace litepubl\pages;

use litepubl\core\Event;
use litepubl\utils\LinkGenerator;
use litepubl\view\Args;
use litepubl\view\Schemes;
use litepubl\view\Theme;

/**
 * Holds menu items
 *
 * @property       int $idhome
 * @property       bool $home
 * @property-write callable $edited
 * @property-write callable $onProcessForm
 * @property-write callable $onBeforeMenu
 * @property-write callable $onMenu
 * @property-write callable $onItems
 * @property-write callable $onSubItems
 * @property-write callable $onContent
 * @method         array edited(array $params)
 * @method         array onProcessForm(array $params)
 * @method         array onBeforeMenu(array $params)
 * @method         array onMenu(array $params)
 * @method         array onItems(array $params)
 * @method         array onSubItems(array $params)
 * @method         array onContent(array $params)
 */

class Menus extends \litepubl\core\Items
{
    public $tree;

    protected function create()
    {
        parent::create();
        $this->addEvents('edited', 'onprocessform', 'onbeforemenu', 'onmenu', 'onitems', 'onsubitems', 'oncontent');

        $this->dbversion = false;
        $this->basename = 'menus' . DIRECTORY_SEPARATOR . 'index';
        $this->addmap('tree', []);
        $this->data['idhome'] = 0;
        $this->data['home'] = false;
    }

    public function getLink(int $id): string
    {
        return sprintf('<a href="%1$s%2$s" title="%3$s">%3$s</a>', $this->getApp()->site->url, $this->items[$id]['url'], $this->items[$id]['title']);
    }

    public function getDir(): string
    {
        return $this->getApp()->paths->data . 'menus' . DIRECTORY_SEPARATOR;
    }

    public function add(Menu $item): int
    {
        if ($item instanceof FakeMenu) {
            return $this->addFakeMenu($item);
        }

        //fix null fields
        foreach ($item->get_owner_props() as $prop) {
            if (!isset($item->data[$prop])) {
                $item->data[$prop] = '';
            }
        }

        if ($item instanceof Home) {
            $item->url = '/';
        } else {
            $linkgen = LinkGenerator::i();
            $item->url = $linkgen->addurl($item, 'menu');
        }

        if ($item->idschema == 1) {
            $schemes = Schemes::i();
            if (isset($schemes->defaults['menu'])) {
                $item->data['idschema'] = $schemes->defaults['menu'];
            }
        }

        $id = ++$this->autoid;
        $this->items[$id] = [
            'id' => $id,
            'class' => get_class($item)
        ];
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
        $item->idurl = $this->getApp()->router->Add($item->url, get_class($item), $item->id);
        if ($item->status != 'draft') {
            $item->status = 'published';
        }
        $this->lock();
        $this->sort();
        $item->save();
        $this->unlock();
        $this->added(['id' => $id]);
        $this->getApp()->cache->clear();
        return $id;
    }

    public function addFake(string $url, string $title): int
    {
        if ($id = $this->url2id($url)) {
            return $id;
        }

        $fake = new FakeMenu();
        $fake->title = $title;
        $fake->url = $url;
        $fake->order = $this->autoid;
        return $this->addFakeMenu($fake);
    }

    public function addFakeMenu(Menu $menu): int
    {
        $item = [
            'id' => ++$this->autoid,
            'idurl' => 0,
            'class' => get_class($menu)
        ];

        //fix null fields
        foreach ($menu->get_owner_props() as $prop) {
            if (!isset($menu->data[$prop])) {
                $menu->data[$prop] = '';
            }
            $item[$prop] = $menu->$prop;
            if (array_key_exists($prop, $menu->data)) {
                unset($menu->data[$prop]);
            }
        }

        $menu->id = $this->autoid;
        $this->items[$this->autoid] = $item;
        $this->lock();
        $this->sort();
        $this->added(['id' => $this->autoid]);
        $this->unlock();
        $this->getApp()->cache->clear();
        return $this->autoid;
    }

    public function addItem(array $item): int
    {
        $item['id'] = ++$this->autoid;
        $item['order'] = $this->autoid;
        $item['status'] = 'published';

        if ($idurl = $this->getApp()->router->urlexists($item['url'])) {
            $item['idurl'] = $idurl;
        } else {
            $item['idurl'] = $this->getApp()->router->add($item['url'], $item['class'], $this->autoid, 'get');
        }

        $this->items[$this->autoid] = $item;
        $this->sort();
        $this->save();
        $this->getApp()->cache->clear();
        return $this->autoid;
    }

    public function edit(Menu $item)
    {
        if (!(($item instanceof Home) || ($item instanceof FakeMenu))) {
            $linkgen = LinkGenerator::i();
            $linkgen->editurl($item, 'menu');
        }

        $this->lock();
        $this->sort();
        $item->save();
        $this->unlock();
        $this->edited(['id' => $item->id]);
        $this->getApp()->cache->clear();
    }

    public function delete($id)
    {
        if (!$this->itemExists($id)) {
            return false;
        }

        if ($id == $this->idhome) {
            return false;
        }

        if ($this->haschilds($id)) {
            return false;
        }

        if ($this->items[$id]['idurl'] > 0) {
            $this->getApp()->router->delete($this->items[$id]['url']);
        }
        $this->lock();
        unset($this->items[$id]);
        $this->sort();
        $this->unlock();
        $this->deleted(['id' => $id]);
        $this->getApp()->storage->remove($this->dir . $id);
        $this->getApp()->cache->clear();
        return true;
    }

    public function deleteUrl(string $url)
    {
        if ($id = $this->url2id($url)) {
            return $this->delete($id);
        }

    }

    public function deleteTree(int $id)
    {
        if (!$this->itemExists($id)) {
            return false;
        }

        if ($id == $this->idhome) {
            return false;
        }

        $this->lock();
        $childs = $this->getchilds($id);
        foreach ($childs as $child) {
            $this->deletetree($child);
        }
        $this->delete($id);
        $this->unlock();
    }

    public function url2id(string $url): int
    {
        foreach ($this->items as $id => $item) {
            if ($url == $item['url']) {
                return $id;
            }
        }
        return false;
    }

    public function remove(int $id)
    {
        if (!$this->itemExists($id) || $this->haschilds($id)) {
            return false;
        }

        $this->lock();
        unset($this->items[$id]);
        $this->sort();
        $this->unlock();
        $this->deleted(['id' => $id]);
        $this->getApp()->cache->clear();
        return true;
    }

    public function hasChilds(int $idparent): int
    {
        foreach ($this->items as $id => $item) {
            if ($item['parent'] == $idparent) {
                return $id;
            }
        }

        return 0;
    }

    public function renameClass(Event $event)
    {
        $changed = false;
        foreach ($this->items as $id => $item) {
            if ($event->oldclass == $item['class']) {
                $this->items[$id]['class'] = $event->newclass;
                $changed = true;
            }
        }
    
        if ($changed) {
                $this->save();
        }
    }

    public function sort()
    {
        $this->tree = $this->getsubtree(0);
    }

    private function getSubTree(int $parent): array
    {
        $result = [];
        // first step is a find all childs and sort them
        $sort = [];
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

    public function getParent(int $id): int
    {
        return $this->items[$id]['parent'];
    }

    //return array of id
    public function getParents(int $id): array
    {
        $result = [];
        $id = $this->items[$id]['parent'];
        while ($id != 0) {
            //array_unshift ($result, $id);
            $result[] = $id;
            $id = $this->items[$id]['parent'];
        }
        return $result;
    }

    //���� � ������ ������ �����, ��� ��� ��� ��� �������������
    public function getChilds(int $id): array
    {
        if ($id == 0) {
            $result = [];
            foreach ($this->tree as $iditem => $items) {
                $result[] = $iditem;
            }
            return $result;
        }

        $parents = [
            $id
        ];
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

    public function exclude(int $id): bool
    {
        return !$this->home && ($id == $this->idhome);
    }

    public function getMenu($hover, $current)
    {
        $r = $this->onBeforeMenu(
            [
            'hover' => $hover,
            'current' => $current,
            ]
        );

        if (count($this->tree) > 0) {
            $theme = Theme::i();
            $args = new Args();
            if ($r['hover']) {
                $items = $this->getSubMenu($this->tree, $r['current'], $r['hover'] === 'bootstrap');
            } else {
                $items = '';
                $tml = $theme->templates['menu.item'];
                $args->submenu = '';
                foreach ($this->tree as $id => $subitems) {
                    if ($this->exclude($id)) {
                        continue;
                    }

                    $args->add($this->items[$id]);
                    $items.= $r['current'] == $id ? $theme->parseArg($theme->templates['menu.current'], $args) : $theme->parseArg($tml, $args);
                }
            }

            $r = $this->onItems(['items' => $items]);
            $args->item = $r['items'];
            $result = $theme->parseArg($theme->templates['menu'], $args);
        }

        $r = $this->onMenu(['content' => $result]);
        return $r['content'];
    }

    private function getSubmenu(&$tree, $current, $bootstrap)
    {
        $result = '';
        $theme = Theme::i();
        $tml_item = $theme->templates['menu.item'];
        $tml_submenu = $theme->templates['menu.item.submenu'];
        $tml_single = $theme->templates['menu.single'];
        $tml_current = $theme->templates['menu.current'];

        $args = new Args();
        foreach ($tree as $id => $items) {
            if ($this->exclude($id)) {
                continue;
            }

            $args->add($this->items[$id]);
            $submenu = '';
            if (count($items)) {
                if ($bootstrap) {
                    $args->submenu = '';
                    $submenu = $theme->parseArg($tml_single, $args);
                }

                $submenu.= $this->getsubmenu($items, $current, $bootstrap);
                $submenu = str_replace('$items', $submenu, $tml_submenu);
            }

            $r = $this->onSubItems(['id' => $id, 'submenu' => $submenu]);
            $args->submenu = $r['submenu'];
            $tml = $current == $id ? $tml_current : ($submenu ? $tml_item : $tml_single);
            $result.= $theme->parseArg($tml, $args);
        }

        return $result;
    }

    public function class2id(string $class): int
    {
        foreach ($this->items as $id => $item) {
            if ($class == $item['class']) {
                return $id;
            }
        }
        return false;
    }

    public function getSitemap(int $from, int $count)
    {
        return $this->externalfunc(
            __class__, 'Getsitemap', [
            $from,
            $count
            ]
        );
    }

    public function classRenamed(string $oldclass, string $newclass)
    {
        foreach ($this->items as $id => $item) {
            if ($oldclass == $item['class']) {
                $this->items[$id]['class'] = $newclass;
            }
        }

        $this->save();
    }
}
