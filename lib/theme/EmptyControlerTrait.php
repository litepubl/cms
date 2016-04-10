<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\theme;

trait EmptyControlerTrait 
{

    protected function create() {
        parent::create();
        $this->data['idview'] = 1;
    }

    public function gethead() {
    }

    public function getkeywords() {
    }

    public function getdescription() {
    }

    public function getIdSchema() {
        return $this->data['idview'];
    }

    public function setIdSchema($id) {
        if ($id != $this->IdSchema) {
            $this->data['idview'] = $id;
            $this->save();
        }
    }

    public function getschema() {
        return Schema::getSchema($this);
    }

}