<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\views;
use litepubl\view\Schemes as SchemaItems;
use litepubl\view\MainView;
use litepubl\admin\Menus;
use litepubl\admin\posts\Ajax;

class Head extends \litepubl\admin\Menu
{

    public function getcontent() {
        $result = '';
        $schemes = SchemaItems::i();
$admin = $this->admintheme;
        $lang = tlocal::i('schemes');
        $args = new targs();

        $tabs = $this->neTabs();
        $args->heads = MainView::i()->heads;
        $tabs->add($lang->headstitle, '[editor=heads]');

        $args->adminheads = Menus::i()->heads;
        $tabs->add($lang->admin, '[editor=adminheads]');

        $ajax = Ajax::i();
        $args->ajaxvisual = $ajax->ajaxvisual;
        $args->visual = $ajax->visual;
        $args->show_file_perm = litepubl::$options->show_file_perm;
        $tabs->add($lang->posteditor, '[checkbox=show_file_perm] [checkbox=ajaxvisual] [text=visual]');

        $args->formtitle = $lang->headstitle;
        return $admin->form($tabs->get() , $args);
    }

    public function processform() {
        $template = MainView::i();
        $template->heads = $_POST['heads'];
        $template->save();

        $adminmenus = Menus::i();
        $adminmenus->heads = $_POST['adminheads'];
        $adminmenus->save();

        $ajax = Ajax::i();
        $ajax->lock();
        $ajax->ajaxvisual = isset($_POST['ajaxvisual']);
        $ajax->visual = trim($_POST['visual']);
        $ajax->unlock();

        litepubl::$options->show_file_perm = isset($_POST['show_file_perm']);
    }

}