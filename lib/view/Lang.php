<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\view;

class Lang
{
    const ZERODATE = '0000-00-00 00:00:00';
    public static $self;
    public $loaded;
    public $ini;
    public $section;
    public $searchsect;

    public static function i($section = '') {
        if (!isset(static ::$self)) {
            static ::$self = static ::getinstance();
            static ::$self->loadfile('default');
        }

        if ($section != '') static ::$self->section = $section;
        return static ::$self;
    }

    public static function getInstance() {
        return  $this->getApp()->classes->getinstance(get_called_class());
    }

    public static function admin($section = '') {
        $result = static ::i($section);
        $result->check('admin');
        return $result;
    }

    public function __construct() {
        $this->ini = array();
        $this->loaded = array();
        $this->searchsect = array(
            'common',
            'default'
        );
    }

    public static function get($section, $key) {
        return static ::i()->ini[$section][$key];
    }

    public function __get($name) {
        if (isset($this->ini[$this->section][$name])) {
 return $this->ini[$this->section][$name];
}


        foreach ($this->searchsect as $section) {
            if (isset($this->ini[$section][$name])) {
 return $this->ini[$section][$name];
}


        }
        return '';
    }

    public function __isset($name) {
        if (isset($this->ini[$this->section][$name])) {
 return true;
}


        foreach ($this->searchsect as $section) {
            if (isset($this->ini[$section][$name])) {
 return true;
}


        }

        return false;
    }

    public function __call($name, $args) {
        return strtr($this->__get($name) , $args->data);
    }

    public function addsearch() {
        $this->joinsearch(func_get_args());
    }

    public function joinsearch(array $a) {
        foreach ($a as $sect) {
            $sect = trim(trim($sect) , "\"',;:.");
            if (!in_array($sect, $this->searchsect)) $this->searchsect[] = $sect;
        }
    }

    public function firstsearch() {
        $a = array_reverse(func_get_args());
        foreach ($a as $sect) {
            $i = array_search($sect, $this->searchsect);
            if ($i !== false) array_splice($this->searchsect, $i, 1);
            array_unshift($this->searchsect, $sect);
        }
    }

    public static function date($date, $format = '') {
        if (empty($format)) $format = static ::i()->getdateformat();
        return static ::i()->translate(date($format, $date) , 'datetime');
    }

    public function getDateformat() {
        $format =  $this->getApp()->options->dateformat;
        return $format != '' ? $format : $this->ini['datetime']['dateformat'];
    }

    public function translate($s, $section = 'default') {
        return strtr($s, $this->ini[$section]);
    }

    public function check($name) {
        if ($name == '') $name = 'default';
        if (!in_array($name, $this->loaded)) $this->loadfile($name);
    }

    public function loadfile($name) {
        $this->loaded[] = $name;
        $filename = static ::getcachedir() . $name;
        if (($data =  $this->getApp()->storage->loaddata($filename)) && is_array($data)) {
            $this->ini = $data + $this->ini;
            if (isset($data['searchsect'])) {
                $this->joinsearch($data['searchsect']);
            }
        } else {
            $merger = Langmerger::i();
            $merger->parse($name);
        }
    }

    public static function usefile($name) {
        static ::i()->check($name);
        return static ::$self;
    }

    public static function getCachedir() {
        return  $this->getApp()->paths->data . 'languages' . DIRECTORY_SEPARATOR;
    }

    public static function clearcache() {
        \litepubl\utils\Filer::delete(static ::getcachedir() , false, false);
        static ::i()->loaded = array();
    }

} //class