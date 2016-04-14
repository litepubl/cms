<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin/pages;
use litepubl\view\Lang;
use litepubl\view\Schemes;
use litepubl\view\Schema;

class Form extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
    protected $formresult;
    protected $title;
    protected $section;

    public function gettitle() {
        return Lang::get($this->section, 'title');
    }

    public function gethead() {
    }

    public function getkeywords() {
    }

    public function getdescription() {
    }

    public function getIdSchema() {
        return Schemes::i()->defaults['admin'];
    }

    public function setIdSchema($id) {
    }

public function gettheme() {
return Schema::getSchema($this->theme;
}

public function getadmintheme() {
return Schema::getSchema($this->admintheme;
}

    public function request($arg) {
        $this->cache = false;
        tlocal::usefile('admin');
        $this->formresult = '';
        if (isset($_POST) && count($_POST)) {
            $this->formresult = $this->processform();
        }
    }

    public function processform() {
        return '';
    }

    public function getcont() {
        $result = $this->formresult;
        $result.= $this->getcontent();
        $theme = $this->theme;
        return $theme->simple($result);
    }

    public function gethtml() {
        $result = tadminhtml::i();
        $lang = tlocal::admin($this->section);
        return $result;
    }

    public function getform() {
        if ($result = litepubl::$cache->getString($this->getbasename())) {
return $result;
}

        $result = $this->createForm();
litepubl::$cache->setString($this->getbasename(), $result);
        return $result;
    }

    public function createForm() {
        return '';
    }

}