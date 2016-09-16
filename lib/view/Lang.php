<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\view;

class Lang
{
    use \litepubl\core\AppTrait;

    const ZERODATE = '0000-00-00 00:00:00';
    const DATEFORMAT = 'd F Y';
    const DATETIMEFORMAT = 'd F Y H:i';

    public static $self;
    public $loaded;
    public $ini;
    public $section;
    public $searchsect;

    public static function i(string $section = '')
    {
        if (!isset(static ::$self)) {
            static ::$self = static ::getInstance();
            static ::$self->loadfile('default');
        }

        if ($section != '') {
            static ::$self->section = $section;
        }
        return static ::$self;
    }

    public static function getInstance()
    {
        return static ::getAppInstance()->classes->getInstance(get_called_class());
    }

    public static function admin(string $section = '')
    {
        $result = static ::i($section);
        $result->check('admin');
        return $result;
    }

    public function __construct()
    {
        $this->ini = [];
        $this->loaded = [];
        $this->searchsect = [
            'common',
            'default'
        ];
    }

    public static function get(string $section, string $key): string
    {
        return static ::i()->ini[$section][$key];
    }

    public function __get($name)
    {
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

    public function __isset($name)
    {
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

    public function __call($name, $args)
    {
        return strtr($this->__get($name), $args->data);
    }

    public function addSearch()
    {
        $this->joinSearch(func_get_args());
    }

    public function joinSearch(array $a)
    {
        foreach ($a as $sect) {
            $sect = trim(trim($sect), "\"',;:.");
            if (!in_array($sect, $this->searchsect)) {
                $this->searchsect[] = $sect;
            }
        }
    }

    public function firstSearch()
    {
        $a = array_reverse(func_get_args());
        foreach ($a as $sect) {
            $i = array_search($sect, $this->searchsect);
            if ($i !== false) {
                array_splice($this->searchsect, $i, 1);
            }
            array_unshift($this->searchsect, $sect);
        }
    }

    public function translate(string $s, string $section = 'default'): string
    {
        return strtr($s, $this->ini[$section]);
    }

    public static function date($date, $format = ''): string
    {
        $self = static ::i();
        if (empty($format)) {
            $format = $self->getDateFormat();
        }

        return $self->translate(date($format, $date), 'datetime');
    }

    public function getDateFormat(): string
    {
        $format = $this->getApp()->options->dateformat;
        return $format ? $format : $this->ini['datetime']['dateformat'];
    }

    public function getDate($date): string
    {
        if ($date == static::ZERODATE) {
            return $this->noword;
        } else {
            return $this->translate(date(static::DATEFORMAT, strtotime($date)), 'datetime');
        }
    }

    public function getDateTime($date): string
    {
        if ($date == Lang::ZERODATE) {
            return $this->noword;
        } else {
            return $this->translate(date(static::DATETIMEFORMAT, strtotime($date)), 'datetime');
        }
    }

    public function check(string $name)
    {
        if (!$name) {
            $name = 'default';
        }

        if (!in_array($name, $this->loaded)) {
            $this->loadFile($name);
        }
    }

    public function loadFile(string $name)
    {
        $this->loaded[] = $name;
        $filename = static ::getcachedir() . $name;
        if (($data = $this->getApp()->storage->loaddata($filename)) && is_array($data)) {
            $this->ini = $data + $this->ini;
            if (isset($data['searchsect'])) {
                $this->joinsearch($data['searchsect']);
            }
        } else {
            $merger = LangMerger::i();
            $merger->parse($name);
        }
    }

    public static function useFile(string $name)
    {
        static ::i()->check($name);
        return static ::$self;
    }

    public static function getCacheDir(): string
    {
        return static ::getAppInstance()->paths->data . 'languages' . DIRECTORY_SEPARATOR;
    }

    public static function clearcache()
    {
        \litepubl\utils\Filer::delete(static ::getcachedir(), false, false);
        static ::i()->loaded = [];
    }
}
