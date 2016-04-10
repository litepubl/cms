<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\theme;
use litepubl\core\Items;
use litepubl\core\DataStorageTrait;

class Views extends Items
{
use DataStorageTrait;

    public $defaults;

    protected function create() {
        $this->dbversion = false;
        parent::create();
        $this->basename = 'views';
        $this->addevents('themechanged');
        $this->addmap('defaults', array());
    }

    public function add($name) {
        $this->lock();
        $id = ++$this->autoid;
        $view = tview::newitem($id);
        $view->id = $id;
        $view->name = $name;
        $view->data['class'] = get_class($view);
        $this->items[$id] = & $view->data;
        $this->unlock();
        return $id;
    }

    public function addview(View $view) {
        $this->lock();
        $id = ++$this->autoid;
        $view->id = $id;
        if ($view->name == '') $view->name = 'view_' . $id;
        $view->data['class'] = get_class($view);
        $this->items[$id] = & $view->data;
        $this->unlock();
        return $id;
    }

    public function delete($id) {
        if ($id == 1) {
            return $this->error('You cant delete default view');
        }

        foreach ($this->defaults as $name => $iddefault) {
            if ($id == $iddefault) {
                $this->defaults[$name] = 1;
            }
        }

        return parent::delete($id);
    }

    public function get($name) {
        foreach ($this->items as $id => $item) {
            if ($name == $item['name']) {
                return tview::i($id);
            }
        }

        return false;
    }

    public function resetCustom() {
        foreach ($this->items as $id => $item) {
            $view = View::i($id);
            $view->resetCustom();
            $view->save();
        }
    }

    public function widgetdeleted($idwidget) {
        $deleted = false;
        foreach ($this->items as & $viewitem) {
            unset($sidebar);
            foreach ($viewitem['sidebars'] as & $sidebar) {
                for ($i = count($sidebar) - 1; $i >= 0; $i--) {
                    if ($idwidget == $sidebar[$i]['id']) {
                        array_delete($sidebar, $i);
                        $deleted = true;
                    }
                }
            }
        }
        if ($deleted) $this->save();
    }

} //class