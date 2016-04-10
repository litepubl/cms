<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\core;

trait DataOwnerTrait
{

    public function load() {
        $owner = $this->owner;
        if ($owner->itemexists($this->id)) {
            $this->data = & $owner->items[$this->id];
            return true;
        }
        return false;
    }

    public function save() {
        return $this->owner->save();
    }

}