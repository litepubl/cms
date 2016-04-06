<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class appcache_manifest extends titems {

    public static function i() {
        return getinstance(__class__);
    }

    public function create() {
        parent::create();
        $this->basename = 'appcache.manifest';
        $this->dbversion = false;
        $this->data['url'] = '/manifest.appcache';
        $this->data['idurl'] = 0;
    }

    public function add($value) {
        if (!in_array($value, $this->items)) {
            $this->items[] = $value;
            $this->save();
            litepubl::$urlmap->setexpired($this->idurl);
            $this->added($value);
        }
    }

    public function gettext() {
        return implode("\r\n", $this->items);
    }

    public function settext($value) {
        $this->items = explode("\n", trim(str_replace(array(
            "\r\n",
            "\r"
        ) , "\n", $value)));
        $this->save();
    }

    public function request($arg) {
        $s = '<?php
    header(\'Content-Type: text/cache-manifest\');
    header(\'Last-Modified: ' . date('r') . '\');
    ?>';

        $s.= "CACHE MANIFEST\r\n";
        $s.= ttheme::i()->parse($this->text);
        return $s;
    }

} //class