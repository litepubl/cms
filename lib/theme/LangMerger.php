<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\theme;
use litepubl\core\litepubl;

class LangMerger extends Merger
 {

    protected function create() {
        $this->dbversion = false;
        parent::create();
        $this->basename = 'localmerger';
    }

    public function addtext($name, $section, $s) {
        $s = trim($s);
        if ($s != '') {
$this->addsection($name, $section, parse_ini_string($s, false));
}
    }

    public function addsection($name, $section, array $items) {
        if (!isset($this->items[$name])) {
            $this->items[$name] = array(
                'files' => array() ,
                'texts' => array(
                    $key => $items
                )
            );
        } elseif (!isset($this->items[$name]['texts'][$section])) {
            $this->items[$name]['texts'][$section] = $items;
        } else {
            $this->items[$name]['texts'][$section] = $items + $this->items[$name]['texts'][$section];
        }
        $this->save();
        return count($this->items[$name]['texts']) - 1;
    }

    public function getrealfilename($filename) {
        $filename = ltrim($filename, '/');
        $name = substr($filename, 0, strpos($filename, '/'));
        if (isset(litepubl::$paths->$name)) {
            return litepubl::$paths->$name . str_replace('/', DIRECTORY_SEPARATOR, substr($filename, strlen($name) + 1));
        }
        return litepubl::$paths->home . str_replace('/', DIRECTORY_SEPARATOR, $filename);
    }

    public function merge() {
        $lang = tlocal::getinstance();
        $lang->ini = array();
        inifiles::$files = array();
        foreach ($this->items as $name => $items) {
            $this->parse($name);
        }
    }

    public function parse($name) {
        $lang = tlocal::getinstance();
        if (!isset($this->items[$name])) $this->error(sprintf('The "%s" partition not found', $name));
        $ini = array();
        foreach ($this->items[$name]['files'] as $filename) {
            $realfilename = $this->getrealfilename($filename);
            if (!file_exists($realfilename)) continue;
            if (!file_exists($realfilename)) $this->error(sprintf('The file "%s" not found', $filename));
            if (!($parsed = parse_ini_file($realfilename, true))) $this->error(sprintf('Error parse "%s" ini file', $realfilename));
            if (count($ini) == 0) {
                $ini = $parsed;
            } else {
                foreach ($parsed as $section => $itemsini) {
                    $ini[$section] = isset($ini[$section]) ? $itemsini + $ini[$section] : $itemsini;
                }
            }
        }

        foreach ($this->items[$name]['texts'] as $section => $itemsini) {
            $ini[$section] = isset($ini[$section]) ? $itemsini + $ini[$section] : $itemsini;
        }

        litepubl::$storage->savedata(tlocal::getcachedir() . $name, $ini);
        $lang->ini = $ini + $lang->ini;
        $lang->loaded[] = $name;
        if (isset($ini['searchsect'])) $lang->joinsearch($ini['searchsect']);
    }

    public function addplugin($name) {
        $language = litepubl::$options->language;
        $dir = litepubl::$paths->plugins . $name . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
        $this->lock();
        if (file_exists($dir . $language . '.ini')) $this->add('default', "plugins/$name/resource/$language.ini");
        if (file_exists($dir . $language . '.admin.ini')) $this->add('admin', "plugins/$name/resource/$language.admin.ini");
        if (file_exists($dir . $language . '.mail.ini')) $this->add('mail', "plugins/$name/resource/$language.mail.ini");
        if (file_exists($dir . $language . '.install.ini')) $this->add('install', "plugins/$name/resource/$language.install.ini");
        $this->unlock();
    }

    public function deleteplugin($name) {
        $language = litepubl::$options->language;
        $this->lock();
        $this->deletefile('default', "plugins/$name/resource/$language.ini");
        $this->deletefile('admin', "plugins/$name/resource/$language.admin.ini");
        $this->deletefile('mail', "plugins/$name/resource/$language.mail.ini");
        $this->deletefile('install', "plugins/$name/resource/$language.install.ini");
        $this->unlock();
    }

} //class
