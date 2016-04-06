<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class tadminform extends tevents implements itemplate {
    protected $formresult;
    protected $title;
    protected $section;

    public function gettitle() {
        return tlocal::get($this->section, 'title');
    }

    public function gethead() {
    }
    public function getkeywords() {
    }
    public function getdescription() {
    }

    public function getidview() {
        return tviews::i()->defaults['admin'];
    }

    public function setidview($id) {
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
        $theme = ttheme::i();
        return $theme->simple($result);
    }

    public function gethtml() {
        $result = tadminhtml::i();
        $result->section = $this->section;
        $lang = tlocal::admin($this->section);
        return $result;
    }

    public function set_cache($content) {
        litepubl::$urlmap->cache->set($this->basename, $content);
    }

    public function get_cache() {
        return litepubl::$urlmap->cache->get($this->basename);
    }

    public function getform() {
        if ($result = $this->get_cache()) return $result;

        $result = $this->createform();
        $this->set_cache($result);
        return $result;
    }

    public function createform() {
        return '';
    }

} //class