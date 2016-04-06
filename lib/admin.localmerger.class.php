<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class tadminlocalmerger extends tadminmenu {
    public static function i($id = 0) {
        return parent::iteminstance(__class__, $id);
    }

    public function getcontent() {
        $merger = tlocalmerger::i();
        $tabs = new tabs($this->admintheme);
        $html = $this->html;
        $lang = tlocal::i('options');
        $args = targs::i();

        foreach ($merger->items as $section => $items) {
            $tab = new tabs($this->admintheme);
            $tab->add($lang->files, $html->getinput('editor', $section . '_files', tadminhtml::specchars(implode("\n", $items['files'])) , $lang->files));
            foreach ($items['texts'] as $key => $text) {
                $tab->add($key, $html->getinput('editor', $section . '_text_' . $key, tadminhtml::specchars($text) , $key));
            }

            $tabs->add($section, $tab->get());
        }

        $args->formtitle = $lang->optionslocal;
        $args->dateformat = litepubl::$options->dateformat;
        $dirs = tfiler::getdir(litepubl::$paths->languages);
        $args->language = tadminhtml::array2combo(array_combine($dirs, $dirs) , litepubl::$options->language);
        $zones = timezone_identifiers_list();
        $args->timezone = tadminhtml::array2combo(array_combine($zones, $zones) , litepubl::$options->timezone);

        return $html->adminform('[text=dateformat]
    [combo=language]
    [combo=timezone]' . $tabs->get() , $args);
    }

    public function processform() {
        litepubl::$options->dateformat = $_POST['dateformat'];
        litepubl::$options->language = $_POST['language'];
        if (litepubl::$options->timezone != $_POST['timezone']) {
            litepubl::$options->timezone = $_POST['timezone'];
            $archives = tarchives::i();
            turlmap::unsub($archives);
            $archives->PostsChanged();
        }

        $merger = tlocalmerger::i();
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

} //class