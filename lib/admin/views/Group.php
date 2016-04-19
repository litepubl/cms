<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\views;
use litepubl\view\Schemes as SchemaItems;
use litepubl\view\Schema;
use litepubl\post\Posts;
use litepubl\pages\Menus as StdMenus;
use litepubl\pages\Menu as StdMenu;
use litepubl\admin\GetSchema;

class Group extends \litepubl\admin\Menu
{

    public function getcontent() {
        $schemes = SchemaItems::i();
$theme = $this->theme;
$admin = $this->admin;
        $lang = tlocal::i('schemes');
        $args = $this->newArgs();

        $args->formtitle = $lang->viewposts;
        $result = $admin->form(GetSchema::combo($schemes->defaults['post'], 'postview') . '<input type="hidden" name="action" value="posts" />', $args);

        $args->formtitle = $lang->viewmenus;
        $result.= $admin->form(GetSchema::combo($schemes->defaults['menu'], 'menuview') . '<input type="hidden" name="action" value="menus" />', $args);

        $args->formtitle = $lang->themeviews;
        $schema = Schema::i();

        $dirlist = Filer::getdir(litepubl::$paths->themes);
        sort($dirlist);
        $list = array();
        foreach ($dirlist as $dir) {
            if (!strbegin($dir, 'admin')) {
$list[$dir] = $dir;
}
        }

        $result.= $admin->form(
$theme->getinput('combo', 'themeview', $theme->comboItems($list, $schema->themename) , $lang->themename) .
 '<input type="hidden" name="action" value="themes" />', $args);

return $result;
    }

    public function processform() {
        switch ($_POST['action']) {
            case 'posts':
                $posts = Posts::i();
                $idview = (int)$_POST['postview'];
                $posts->db->update("idview = '$idview'", 'id > 0');
                break;


            case 'menus':
                $idview = (int)$_POST['menuview'];
                $menus = StdMenus::i();
                foreach ($menus->items as $id => $item) {
                    $menu = StdMenu::i($id);
                    $menu->idview = $idview;
                    $menu->save();
                }
                break;


            case 'themes':
                $themename = $_POST['themeview'];
                $schemes = SchemaItems::i();
                $schemes->lock();
                foreach ($schemes->items as $id => $item) {
                    $schema = Schema::i($id);
                    $schema->themename = $themename;
                    $schema->save();
                }
                $schemes->unlock();
                break;
        }
    }

}