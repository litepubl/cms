<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\options;
use litepubl\view\Parser;
use litepubl\view\AdminParser;
use litepubl\view\Lang;
use litepubl\view\Base;

class ThemeParser extends \litepubl\admin\Menu
{

    public function getcontent() {
        $lang = tlocal::admin('options');
        $args = new Args();
        $tabs = $this->newTabs();

        $themeparser = Parser::i();
        $args->tagfiles = implode("\n", $themeparser->tagfiles);
        $args->themefiles = implode("\n", $themeparser->themefiles);
        $tabs->add($lang->theme, '[editor=tagfiles] [editor=themefiles]');

        $admin = AdminParser::i();
        $args->admintagfiles = implode("\n", $admin->tagfiles);
        $args->adminthemefiles = implode("\n", $admin->themefiles);
        $tabs->add($lang->admin, '[editor=admintagfiles] [editor=adminthemefiles]');

        $args->formtitle = $lang->options;
        return $this->admintheme->form($tabs->get() , $args);
    }

    public function processform() {
        $themeparser = Parser::i();
        $themeparser->tagfiles = strtoarray($_POST['tagfiles']);
        $themeparser->themefiles = strtoarray($_POST['themefiles']);
        $themeparser->save();

        $admin = AdminParser::i();
        $admin->tagfiles = strtoarray($_POST['admintagfiles']);
        $admin->themefiles = strtoarray($_POST['adminthemefiles']);
        $admin->save();

        Base::clearcache();
    }

}