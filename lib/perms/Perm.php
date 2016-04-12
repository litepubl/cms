<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\perms;

class Perm extends \litepubl\core\Item
{
use \litepubl\core\ItemOwnerTrait;

    protected $adminInstance;
    protected $adminclass;

    public static function i($id = 0) {
        $perms = Perms::i();
        $class = $perms->itemexists($id) ? $perms->items[$id]['class'] : get_called_class();
        return parent::iteminstance($class, $id);
    }

    public static function getinstancename() {
        return 'perm';
    }

    protected function create() {
        parent::create();
        $this->data = array(
            'id' => 0,
            'class' => get_class($this) ,
            'name' => 'permission'
        );
    }

    public function getowner() {
        return Perms::i();
    }

    public function getadmin() {
        if (!isset($this->adminInstance)) {
            $class = $this->adminclass;
            $this->adminInstance = litepubl::$classes->newinstance($class);
            $this->adminInstance->perm = $this;
        }
        return $this->adminInstance;
    }

    public function getheader($obj) {
        return '';
    }

    public function hasperm($obj) {
        return true;
    }

}