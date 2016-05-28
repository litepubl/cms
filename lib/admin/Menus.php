<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\admin;

use litepubl\core\UserGroups;
use litepubl\pages\Menu as StdMenu;
use litepubl\view\Lang;

class Menus extends \litepubl\pages\Menus
{

    protected function create()
    {
        parent::create();
        $this->basename = 'adminmenu';
        $this->addevents('onexclude');
        $this->data['heads'] = '';
    }

    public function setTitle($id, $title)
    {
        if ($id && isset($this->items[$id])) {
            $this->items[$id]['title'] = $title;
            $this->save();
            $this->getApp()->cache->clear();
        }
    }

    public function getDir()
    {
        return $this->getApp()->paths->data . 'adminmenus' . DIRECTORY_SEPARATOR;
    }

    public function getAdmintitle($name)
    {
        $lang = Lang::i();
        $ini = & $lang->ini;
        if (isset($ini[$name]['title'])) {
            return $ini[$name]['title'];
        }

        Lang::usefile('install');
        if (!in_array('adminmenus', $lang->searchsect)) {
            array_unshift($lang->searchsect, 'adminmenus');
        }

        if ($result = $lang->__get($name)) {
            return $result;
        }

        return $name;
    }

    public function createurl($parent, $name)
    {
        return $parent == 0 ? "/admin/$name/" : $this->items[$parent]['url'] . "$name/";
    }

    public function createItem(int $parent, string $name, string $group, string $class): int
    {
        $title = $this->getAdminTitle($name);
        $url = $this->createUrl($parent, $name);
        return $this->addItem(array(
            'parent' => $parent,
            'url' => $url,
            'title' => $title,
            'name' => $name,
            'class' => $class,
            'group' => $group
        ));
    }

    public function addItem(array $item): int
    {
        if (empty($item['group'])) {
            $groups = UserGroups::i();
            $item['group'] = $groups->items[$groups->defaults[0]]['name'];
        }
        return parent::addItem($item);
    }

    public function addFakeMenu(StdMenu $menu): int
    {
        $this->lock();
        $id = parent::addfakemenu($menu);
        if (empty($this->items[$id]['group'])) {
            $groups = UserGroups::i();
            $group = count($groups->defaults) ? $groups->items[$groups->defaults[0]]['name'] : 'commentator';
            $this->items[$id]['group'] = $group;
        }

        $this->unlock();
        return $id;
    }

    public function getChilds(int $id): array
    {
        if ($id == 0) {
            $result = array();
            $options = $this->getApp()->options;
            foreach ($this->tree as $iditem => $items) {
                if ($options->hasgroup($this->items[$iditem]['group'])) $result[] = $iditem;
            }
            return $result;
        }

        $parents = array(
            $id
        );
        $parent = $this->items[$id]['parent'];
        while ($parent != 0) {
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
        if (!$this->getApp()->options->hasGroup($this->items[$id]['group'])) {
            return true;
        }

        return $this->onexclude($id);
    }

}

