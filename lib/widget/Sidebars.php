<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\widget;

use litepubl\core\Arr;
use litepubl\view\Schema;
use litepubl\view\Schemes;

class Sidebars extends \litepubl\core\Data
{
    public $items;

    public static function i(int $id = 0)
    {
        $result = static ::iGet(get_called_class());
        if ($id) {
            $schema = Schema::i($id);
            $result->items = & $schema->sidebars;
        }

        return $result;
    }

    protected function create()
    {
        parent::create();
        $schema = Schema::i();
        $this->items = & $schema->sidebars;
    }

    public function load()
    {
    }

    public function save()
    {
        Schema::i()->save();
    }

    public function add(int $id)
    {
        $this->insert($id, false, 0, -1);
    }

    public function insert(int $id, $ajax, int $index, int $order)
    {
        if (!isset($this->items[$index])) {
            return $this->error("Unknown sidebar $index");
        }

        $item = [
            'id' => $id,
            'ajax' => $ajax
        ];
        if (($order < 0) || ($order > count($this->items[$index]))) {
            $this->items[$index][] = $item;
        } else {
            Arr::insert($this->items[$index], $item, $order);
        }
        $this->save();
    }

    public function remove(int $id)
    {
        if ($pos = static ::getpos($this->items, $id)) {
            Arr::delete($this->items[$pos[0]], $pos[1]);
            $this->save();
            return $pos[0];
        }
    }

    public function delete($id, $index)
    {
        if ($i = $this->indexof($id, $index)) {
            Arr::delete($this->items[$index], $i);
            $this->save();
            return $i;
        }
        return false;
    }

    public function deleteClass(string $classname)
    {
        $widgets = Widgets::i();
        if ($id = $widgets->class2id($classname)) {
            $widgets->deleted(['id' => $id]);
        }
    }

    public function indexOf($id, $index)
    {
        foreach ($this->items[$index] as $i => $item) {
            if ($id == $item['id']) {
                return $i;
            }
        }

        return false;
    }

    public function setAjax($id, $ajax)
    {
        foreach ($this->items as $index => $items) {
            if ($pos = $this->indexof($id, $index)) {
                $this->items[$index][$pos]['ajax'] = $ajax;
            }
        }
    }

    public function move(int $id, int $index, int $newOrder)
    {
        if ($old = $this->indexof($id, $index)) {
            if ($old != $newOrder) {
                Arr::move($this->items[$index], $old, $newOrder);
                $this->save();
            }
        }
    }

    public static function getPos(array & $sidebars, $id)
    {
        foreach ($sidebars as $i => $sidebar) {
            foreach ($sidebar as $j => $item) {
                if ($id == $item['id']) {
                    return [
                        $i,
                        $j
                    ];
                }
            }
        }
        return false;
    }

    public static function setPos(array & $items, $id, $newsidebar, $neworder)
    {
        if ($pos = static ::getpos($items, $id)) {
            list($oldsidebar, $oldorder) = $pos;
            if (($oldsidebar != $newsidebar) || ($oldorder != $neworder)) {
                $item = $items[$oldsidebar][$oldorder];
                Arr::delete($items[$oldsidebar], $oldorder);
                if (($neworder < 0) || ($neworder > count($items[$newsidebar]))) {
                    $neworder = count($items[$newsidebar]);
                }
                Arr::insert($items[$newsidebar], $item, $neworder);
            }
        }
    }

    public static function fix()
    {
        $widgets = Widgets::i();
        foreach ($widgets->classes as $classname => & $items) {
            foreach ($items as $i => $item) {
                if (!isset($widgets->items[$item['id']])) {
                    unset($items[$i]);
                }
            }
        }

        $schemes = Schemes::i();
        foreach ($schemes->items as & $schemaItem) {
            if (($schemaItem['id'] != 1) && !$schemaItem['customsidebar']) {
                continue;
            }

            unset($sidebar);
            foreach ($schemaItem['sidebars'] as & $sidebar) {
                for ($i = count($sidebar) - 1; $i >= 0; $i--) {
                    if (!isset($widgets->items[$sidebar[$i]['id']])) {
                        Arr::delete($sidebar, $i);
                    }
                }
            }
        }
        $schemes->save();
    }
}
