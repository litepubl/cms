<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class tabs {
    public $tabs;
    public $panels;
    public $id;
    public $_admintheme;
    private static $index = 0;

    public function __construct($admintheme = null) {
        $this->_admintheme = $admintheme;
        $this->tabs = array();
        $this->panels = array();
    }

    public function getadmintheme() {
        if (!$this->_admintheme) {
            $this->_admintheme = admintheme::i();
        }

        return $this->_admintheme;
    }

    public function get() {
        return strtr($this->getadmintheme()->templates['tabs'], array(
            '$id' => $this->id ? $this->id : 'tabs-' . static ::$index++,
            '$tab' => implode("\n", $this->tabs) ,
            '$panel' => implode("\n", $this->panels) ,
        ));
    }

    public function add($title, $content) {
        $this->addtab('', $title, $content);
    }

    public function ajax($title, $url) {
        $this->addtab($url, $title, '');
    }

    public function addtab($url, $title, $content) {
        $id = static ::$index++;
        $this->tabs[] = $this->gettab($id, $url, $title);
        $this->panels[] = $this->getpanel($id, $content);
    }

    public function gettab($id, $url, $title) {
        return strtr($this->getadmintheme()->templates['tabs.tab'], array(
            '$id' => $id,
            '$title' => $title,
            '$url' => $url,
        ));
    }

    public function getpanel($id, $content) {
        return strtr($this->getadmintheme()->templates['tabs.panel'], array(
            '$id' => $id,
            '$content' => $content,
        ));
    }

} //class