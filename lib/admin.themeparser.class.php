<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

namespace litepubl;

class adminthemeparser extends tadminmenu {

    public static function i($id = 0) {
        return parent::iteminstance(__class__, $id);
    }

    public function getcontent() {
        $html = $this->html;
        $lang = tlocal::i('options');
        $args = targs::i();
        $tabs = new tabs($this->admintheme);

        $themeparser = tthemeparser::i();
        $args->tagfiles = implode("\n", $themeparser->tagfiles);
        $args->themefiles = implode("\n", $themeparser->themefiles);
        $tabs->add($lang->theme, '[editor=tagfiles] [editor=themefiles]');

        $admin = adminparser::i();
        $args->admintagfiles = implode("\n", $admin->tagfiles);
        $args->adminthemefiles = implode("\n", $admin->themefiles);
        $tabs->add($lang->admin, '[editor=admintagfiles] [editor=adminthemefiles]');

        $args->formtitle = $lang->options;
        return $html->adminform($tabs->get() , $args);
    }

    public function processform() {
        $themeparser = tthemeparser::i();
        $themeparser->tagfiles = strtoarray($_POST['tagfiles']);
        $themeparser->themefiles = strtoarray($_POST['themefiles']);
        $themeparser->save();

        $admin = adminparser::i();
        $admin->tagfiles = strtoarray($_POST['admintagfiles']);
        $admin->themefiles = strtoarray($_POST['adminthemefiles']);
        $admin->save();

        basetheme::clearcache();
    }

} //class