<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\widget;
use litepubl\theme\Schema;
use litepubl\theme\Schemes;

class Sidebars extends \litepubl\core\Data
 {
    public $items;

    public static function i($id = 0) {
        $result = getinstance(get_called_class());
        if ($id) {
            $schema = Schema::i((int)$id);
            $result->items = & $schema->sidebars;
        }

        return $result;
    }

    protected function create() {
        parent::create();
        $schema = Schema::i();
        $this->items = & $schema->sidebars;
    }

    public function load() {
    }

    public function save() {
        Schema::i()->save();
    }

    public function add($id) {
        $this->insert($id, false, 0, -1);
    }

    public function insert($id, $ajax, $index, $order) {
        if (!isset($this->items[$index])) return $this->error("Unknown sidebar $index");
        $item = array(
            'id' => $id,
            'ajax' => $ajax
        );
        if (($order < 0) || ($order > count($this->items[$index]))) {
            $this->items[$index][] = $item;
        } else {
            array_insert($this->items[$index], $item, $order);
        }
        $this->save();
    }

    public function remove($id) {
        if ($pos = static ::getpos($this->items, $id)) {
            array_delete($this->items[$pos[0]], $pos[1]);
            $this->save();
            return $pos[0];
        }
    }

    public function delete($id, $index) {
        if ($i = $this->indexof($id, $index)) {
            array_delete($this->items[$index], $i);
            $this->save();
            return $i;
        }
        return false;
    }

    public function deleteclass($classname) {
        if ($id = twidgets::i()->class2id($classname)) {
            Schemes::i()->widgetdeleted($id);
        }
    }

    public function indexof($id, $index) {
        foreach ($this->items[$index] as $i => $item) {
            if ($id == $item['id']) return $i;
        }
        return false;
    }

    public function setajax($id, $ajax) {
        foreach ($this->items as $index => $items) {
            if ($pos = $this->indexof($id, $index)) {
                $this->items[$index][$pos]['ajax'] = $ajax;
            }
        }
    }

    public function move($id, $index, $neworder) {
        if ($old = $this->indexof($id, $index)) {
            if ($old != $newindex) {
                array_move($this->items[$index], $old, $neworder);
                $this->save();
            }
        }
    }

    public static function getpos(array & $sidebars, $id) {
        foreach ($sidebars as $i => $sidebar) {
            foreach ($sidebar as $j => $item) {
                if ($id == $item['id']) {
                    return array(
                        $i,
                        $j
                    );
                }
            }
        }
        return false;
    }

    public static function setpos(array & $items, $id, $newsidebar, $neworder) {
        if ($pos = static ::getpos($items, $id)) {
            list($oldsidebar, $oldorder) = $pos;
            if (($oldsidebar != $newsidebar) || ($oldorder != $neworder)) {
                $item = $items[$oldsidebar][$oldorder];
                array_delete($items[$oldsidebar], $oldorder);
                if (($neworder < 0) || ($neworder > count($items[$newsidebar]))) $neworder = count($items[$newsidebar]);
                array_insert($items[$newsidebar], $item, $neworder);
            }
        }
    }

    public static function fix() {
        $widgets = twidgets::i();
        foreach ($widgets->classes as $classname => & $items) {
            foreach ($items as $i => $item) {
                if (!isset($widgets->items[$item['id']])) unset($items[$i]);
            }
        }

        $schemes = Schemes::i();
        foreach ($schemes->items as & $schemaItem) {
            if (($schemaItem['id'] != 1) && !$schemaItem['customsidebar']) continue;
            unset($sidebar);
            foreach ($schemaItem['sidebars'] as & $sidebar) {
                for ($i = count($sidebar) - 1; $i >= 0; $i--) {
                    if (!isset($widgets->items[$sidebar[$i]['id']])) {
                        array_delete($sidebar, $i);
                    }
                }
            }
        }
        $schemes->save();
    }

} //class