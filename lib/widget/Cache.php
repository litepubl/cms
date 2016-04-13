<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\widget;
use litepubl\view\Theme;

class Cache extends litepubl\core\Items
{
    private $modified;

    protected function create() {
        $this->dbversion = false;
        parent::create();
        $this->modified = false;
    }

    public function getbasename() {
        $theme = Theme::i();
        return 'widgetscache.' . $theme->name;
    }

    public function load() {
        if ($data = litepubl::$cache->get($this->getbasename())) {
            $this->data = $data;
            $this->afterload();
            return true;
        }

        return false;
    }

    public function savemodified() {
        if ($this->modified) {
        $this->modified = false;
            litepubl::$cache->set($this->getbasename() , $this->data);
        }
    }

    public function save() {
        if (!$this->modified) {
            $this->modified = true;
            litepubl::$urlmap->onclose = array(
                $this,
                'savemodified'
            );
        }
    }

    public function getcontent($id, $sidebar, $onlybody = true) {
        if (isset($this->items[$id][$sidebar])) return $this->items[$id][$sidebar];
        return $this->setcontent($id, $sidebar, $onlybody);
    }

    public function setcontent($id, $sidebar, $onlybody = true) {
        $widget = Widgets::i()->getwidget($id);

        if ($onlybody) {
            $result = $widget->getcontent($id, $sidebar);
        } else {
            $result = $widget->getwidget($id, $sidebar);
        }

        $this->items[$id][$sidebar] = $result;
        $this->save();
        return $result;
    }

    public function expired($id) {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            $this->save();
        }
    }

    public function onclearcache() {
        $this->items = array();
        $this->modified = false;
    }

}
 //class