<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\view;

class Schemes extends \litepubl\core\Items
{
use \litepubl\core\DataStorageTrait;

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
        $schema = Schema::newitem($id);
        $schema->id = $id;
        $schema->name = $name;
        $schema->data['class'] = get_class($schema);
        $this->items[$id] = & $schema->data;
        $this->unlock();
        return $id;
    }

    public function addSchema(Schema $schema) {
        $this->lock();
        $id = ++$this->autoid;
        $schema->id = $id;
        if (!$schema->name) {
$schema->name = 'schema_' . $id;
}

        $schema->data['class'] = get_class($schema);
        $this->items[$id] = & $schema->data;
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
                return Schema::i($id);
            }
        }

        return false;
    }

    public function resetCustom() {
        foreach ($this->items as $id => $item) {
            $schema= Schema::i($id);
            $schema->resetCustom();
            $this->save();
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
        if ($deleted) {
$this->save();
}
    }

} //class