<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;
use litepubl\admin\GetSchema;

class tadminviewsgroup extends tadminmenu {

    public function getcontent() {
        $views = tviews::i();
$theme = $this->theme;
$admin = $this->admin;
        $lang = tlocal::i('views');
        $args = new targs();

        $args->formtitle = $lang->viewposts;
        $result = $admin->form(GetSchema::combo($views->defaults['post'], 'postview') . '<input type="hidden" name="action" value="posts" />', $args);

        $args->formtitle = $lang->viewmenus;
        $result.= $admin->form(GetSchema::combo($views->defaults['menu'], 'menuview') . '<input type="hidden" name="action" value="menus" />', $args);

        $args->formtitle = $lang->themeviews;
        $view = tview::i();

        $dirlist = tfiler::getdir(litepubl::$paths->themes);
        sort($dirlist);
        $list = array();
        foreach ($dirlist as $dir) {
            if (!strbegin($dir, 'admin')) {
$list[$dir] = $dir;
}
        }

        $result.= $admin->form(
$theme->getinput('combo', 'themeview', $theme->comboItems($list, $view->themename) , $lang->themename) .
 '<input type="hidden" name="action" value="themes" />', $args);

return $result;
    }

    public function processform() {
        switch ($_POST['action']) {
            case 'posts':
                $posts = tposts::i();
                $idview = (int)$_POST['postview'];
                $posts->db->update("idview = '$idview'", 'id > 0');
                break;


            case 'menus':
                $idview = (int)$_POST['menuview'];
                $menus = tmenus::i();
                foreach ($menus->items as $id => $item) {
                    $menu = tmenu::i($id);
                    $menu->idview = $idview;
                    $menu->save();
                }
                break;


            case 'themes':
                $themename = $_POST['themeview'];
                $views = tviews::i();
                $views->lock();
                foreach ($views->items as $id => $item) {
                    $view = tview::i($id);
                    $view->themename = $themename;
                    $view->save();
                }
                $views->unlock();
                break;
        }
    }

} //class