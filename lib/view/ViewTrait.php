<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\view;

trait ViewTrait
{

    protected function createData() {
        parent::createData();
        $this->data['idview'] = 1;
        $this->data['keywords'] = '';
        $this->data['description'] = '';
        $this->data['head'] = '';
    }

    public function request($arg) {
}

    public function gethead() {
        return $this->data['head'];
    }

    public function getkeywords() {
        return $this->data['keywords'];
    }

    public function getdescription() {
        return $this->data['description'];
    }

    public function getIdSchema() {
        return $this->data['idview'];
    }

    public function setIdSchema($id) {
        if ($id != $this->data['idview']) {
            $this->data['idview'] = $id;
            $this->save();
        }
    }

    public function getschema() {
        return Schema::getSchema($this);
    }

}