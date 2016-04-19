<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\posts;
use litepubl\tag\Tags as TagItems;
use litepubl\tag\Cats as TatItems;
use litepubl\view\Admin;
use litepubl\view\Lang;
use litepubl\view\Schemes;
use litepubl\view\Schema;
use litepubl\admin\GetSchema;
use litepubl\admin\GetPerm;

class TagAjax extends Ajax
{

    public function install() {
        litepubl::$urlmap->addget('/admin/ajaxtageditor.htm', get_class($this));
    }

    public function request($arg) {
        $this->cache = false;
        turlmap::sendheader(false);

        if ($err = static ::auth()) return $err;
        return $this->getcontent();
    }

    public function getcontent() {
        $type = !empty($_GET['type']) ? $_GET['type'] : (!empty($_POST['type']) ? $_POST['type'] : 'tags');
if ($type != 'tags') {
$type = 'categories';
}

        $tags = $type == 'tags' ? Tagitems::i() : CatItems::i();
        if ($err = static ::auth()) {
            return $err;
        }

        $id = $this->idparam();
        if (($id > 0) && !$tags->itemexists($id)) {
            return static ::error403();
        }

        $theme = Schema::i(Schemes::i()->defaults['admin'])->theme;
        $admin = Admin::admin();
        $lang = tlocal::i('tags');

        if ($id == 0) {
            $schemes = Schemes::i();
            $name = $type == 'tags' ? 'tag' : 'category';
            $item = array(
                'title' => '',
                'idview' => isset($views->defaults[$name]) ? $views->defaults[$name] : 1,
                'idperm' => 0,
                'icon' => 0,
                'includechilds' => $tags->includechilds,
                'includeparents' => $tags->includeparents,
                'url' => '',
                'keywords' => '',
                'description' => '',
                'head' => ''
            );
        } else {
            $item = $tags->getitem($id);
        }

        switch ($_GET['get']) {
            case 'view':
                if ($id > 0) {
                    foreach (array(
                        'includechilds',
                        'includeparents'
                    ) as $prop) {
                        $item[$prop] = ((int)$item[$prop]) > 0;
                    }
                }

                $args = new targs();
                $args->add($item);
                $result = GetSchema::combo($item['idview']);
                $result.= $admin->parsearg('[checkbox=includechilds] [checkbox=includeparents]', $args);
                $result.= GetPerm::combo($item['idperm']);
                break;


            case 'seo':
                $args = targs::i();
                if ($id == 0) {
                    $args->url = '';
                    $args->keywords = '';
                    $args->description = '';
                    $args->head = '';
                } else {
                    $args->add($tags->contents->getitem($id));
                    $args->url = $tags->items[$id]['url'];
                }
                $result = $admin->parsearg('[text=url] [text=description] [text=keywords] [editor=head]', $args);
                break;


            case 'text':
                return $this->gettext($id == 0 ? '' : $tags->contents->getcontent($id));
                break;


            default:
                $result = var_export($_GET, true);
        }
        return turlmap::htmlheader(false) . $result;
    }

} //class