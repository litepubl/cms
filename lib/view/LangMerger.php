<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\view;

use litepubl\core\Plugins;

class LangMerger extends Merger
{

    protected function create()
    {
        $this->dbversion = false;
        parent::create();
        $this->basename = 'localmerger';
    }

    public function addtext($name, $section, $s)
    {
        $s = trim($s);
        if ($s != '') {
            $this->addsection($name, $section, parse_ini_string($s, false));
        }
    }

    public function addsection($name, $section, array $items)
    {
        if (!isset($this->items[$name])) {
            $this->items[$name] = [
                'files' => [] ,
                'texts' => [
                    $section => $items
                ]
            ];
        } elseif (!isset($this->items[$name]['texts'][$section])) {
            $this->items[$name]['texts'][$section] = $items;
        } else {
            $this->items[$name]['texts'][$section] = $items + $this->items[$name]['texts'][$section];
        }
        $this->save();
        return count($this->items[$name]['texts']) - 1;
    }

    public function getRealFilename(string $filename): string
    {
        $filename = ltrim($filename, '/');

        $theme = Theme::i();
        $vars = new Vars();
        $vars->plugins = Plugins::i();
        $filename = $theme->parse($filename);
        return $this->getApp()->paths->home . $filename;
    }

    public function merge()
    {
        $lang = Lang::getInstance();
        $lang->ini = [];
        foreach ($this->items as $name => $items) {
            $this->parse($name);
        }
    }

    public function parse($name)
    {
        $lang = Lang::getinstance();
        if (!isset($this->items[$name])) {
            $this->error(sprintf('The "%s" partition not found', $name));
        }
        $ini = [];
        foreach ($this->items[$name]['files'] as $filename) {
            $realfilename = $this->getRealFilename($filename);
            if (!file_exists($realfilename)) {
                continue;
            }

            if (!file_exists($realfilename)) {
                $this->error(sprintf('The file "%s" not found', $filename));
            }
            if (!($parsed = parse_ini_file($realfilename, true))) {
                $this->error(sprintf('Error parse "%s" ini file', $realfilename));
            }
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

        $this->getApp()->storage->savedata(Lang::getcachedir() . $name, $ini);
        $lang->ini = $ini + $lang->ini;
        $lang->loaded[] = $name;
        if (isset($ini['searchsect'])) {
            $lang->joinsearch($ini['searchsect']);
        }
    }

    public function addPlugin(string $name)
    {
        $language = $this->getApp()->options->language;
$plugins = Plugins::i();
        $dir = $this->getApp()->paths->home . $plugins->__get($name) . DIRECTORY_SEPARATOR . 'resource' . DIRECTORY_SEPARATOR;
        $this->lock();
        if (file_exists($dir . $language . '.ini')) {
            $this->add('default', "\$plugins.$name/resource/$language.ini");
        }

foreach (['admin', 'mail', 'install' as $section) {
        if (file_exists($dir . "$language.$section.ini")) {
            $this->add($section, "\$plugins.$name/resource/$language.$section.ini");
        }
}

        $this->unlock();
    }

    public function deleteplugin($name)
    {
        $language = $this->getApp()->options->language;
        $this->lock();
        $this->deletefile('default', "plugins/$name/resource/$language.ini");
        $this->deletefile('admin', "plugins/$name/resource/$language.admin.ini");
        $this->deletefile('mail', "plugins/$name/resource/$language.mail.ini");
        $this->deletefile('install', "plugins/$name/resource/$language.install.ini");
        $this->unlock();
    }
}
