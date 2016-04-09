<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\core;

trait templateTrait {

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


    public function getidview() {
        return $this->data['idview'];
    }

    public function setidview($id) {
        if ($id != $this->idview) {
            $this->data['idview'] = $id;
            $this->save();
        }
    }

    public function getview() {
        return tview::getview($this);
    }

}