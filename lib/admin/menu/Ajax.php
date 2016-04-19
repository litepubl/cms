<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\menu;
use litepubl\pages\Menus;
use litepubl\pages\Menu;
use litepubl\view\Schema;
use litepubl\view\Schemes;
use litepubl\admin\GetSchema;
use litepubl\view\Args;

class Ajax extends \litepubl\admin\posts\Ajax
{

//to prevent call parent method
    public function install() {
        litepubl::$urlmap->addget('/admin/ajaxmenueditor.htm', get_class($this));
    }

    public function request($arg) {
        if ($err = static ::auth()) return $err;
        return $this->getcontent();
    }

    public function getcontent() {
        $id = $this->idparam();
        $menus = Menus::i();
        if (($id != 0) && !$menus->itemexists($id)) return static ::error403();
        $menu = Menu::i($id);
        if ((litepubl::$options->group == 'author') && (litepubl::$options->user != $menu->author)) return static ::error403();
        if (($id > 0) && !$menus->itemexists($id)) return static ::error403();

        $schemes = Schemes::i();
$schema = Schema::i($schemes->defaults['admin']);
        $theme = $schema->theme;
$admin = $schema->admintheme;

        switch ($_GET['get']) {
            case 'view':
                $result = GetSchema::combo($id == 0 ? $schemes->defaults['menu'] : $menu->idSchema);
                break;


            case 'seo':
                $args = targs::i();
                $args->url = $menu->url;
                $args->keywords = $menu->keywords;
                $args->description = $menu->description;
                $args->head = $menu->data['head'];
                $result = $admin->parsearg('[text=url] [text=description] [text=keywords] [editor=head]', $args);
                break;


            default:
                $result = var_export($_GET, true);
        }
        return turlmap::htmlheader(false) . $result;
    }

}