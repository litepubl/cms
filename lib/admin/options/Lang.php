<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\options;
use litepubl\view\LangMerger;
use litepubl\view\Lang as Lng;
use litepubl\admin\Html;
use litepubl\utils\Filer;
use litepubl\post\Archives;

class Lang extends \litepubl\admin\Menu
{
    public function getcontent() {
        $merger = LangMerger::i();
        $tabs = $this->newTabs();
        $lang = Lng::admin('options');
        $args = mew Args();
$theme = $this->theme;

        foreach ($merger->items as $section => $items) {
            $tab = $this->newTabs();
            $tab->add($lang->files, $theme->getinput('editor', $section . '_files', $theme->quote(implode("\n", $items['files'])) , $lang->files));
            foreach ($items['texts'] as $key => $text) {
                $tab->add($key, $theme->getinput('editor', $section . '_text_' . $key, $theme->quote($text) , $key));
            }

            $tabs->add($section, $tab->get());
        }

        $args->formtitle = $lang->optionslocal;
        $args->dateformat = litepubl::$options->dateformat;
        $dirs = Filer::getdir(litepubl::$paths->languages);
        $args->language = Html::array2combo(array_combine($dirs, $dirs) , litepubl::$options->language);
        $zones = timezone_identifiers_list();
        $args->timezone = Html::array2combo(array_combine($zones, $zones) , litepubl::$options->timezone);

        return $admin->form('[text=dateformat]
    [combo=language]
    [combo=timezone]' . $tabs->get() , $args);
    }

    public function processform() {
        litepubl::$options->dateformat = $_POST['dateformat'];
        litepubl::$options->language = $_POST['language'];
        if (litepubl::$options->timezone != $_POST['timezone']) {
            litepubl::$options->timezone = $_POST['timezone'];
            $archives = Archives::i();
            turlmap::unsub($archives);
            $archives->PostsChanged();
        }

        $merger = LangMerger::i();
        $merger->lock();
        //$merger->items = array();
        //$merger->install();
        foreach (array_keys($merger->items) as $name) {
            $keys = array_keys($merger->items[$name]['texts']);
            $merger->setfiles($name, $_POST[$name . '_files']);
            foreach ($keys as $key) {
                $merger->addtext($name, $key, $_POST[$name . '_text_' . $key]);
            }
        }

        $merger->unlock();
    }

}