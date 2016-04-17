<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin;
use litepubl\core\UserGroups;
use litepubl\view\Guard;
use litepubl\view\Lang;
use litepubl\view\Schemes;

class Menu extends \litepubl\pages\Menu
 {
use Factory;
use Params;

    public static $adminownerprops = array(
        'title',
        'url',
        'idurl',
        'parent',
        'order',
        'status',
        'name',
        'group'
    );

    public static function getinstancename() {
        return 'adminmenu';
    }

    public static function getowner() {
        return Menus::i();
    }

    protected function create() {
        parent::create();
        $this->cache = false;
    }

    public function get_owner_props() {
        return static ::$adminownerprops;
    }

    public function load() {
        return true;
    }

    public function save() {
        return true;
    }

    public function gethead() {
        return Menus::i()->heads;
    }

    public function getIdSchema() {
        return Schemes::i()->defaults['admin'];
    }

    public static function auth($group) {
        if ($err = Guard::checkattack()) {
            return $err;
        }

        if (!litepubl::$options->user) {
            turlmap::nocache();
            return litepubl::$urlmap->redir('/admin/login/' . litepubl::$site->q . 'backurl=' . urlencode(litepubl::$urlmap->url));
        }

        if (!litepubl::$options->hasgroup($group)) {
            $url = UserGroups::i()->gethome(litepubl::$options->group);
            turlmap::nocache();
            return litepubl::$urlmap->redir($url);
        }
    }

    public function request($id) {
        error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING);
        ini_set('display_errors', 1);

        if (is_null($id)) {
            $id = $this->owner->class2id(get_class($this));
        }

        $this->data['id'] = (int)$id;
        if ($id > 0) {
            $this->basename = $this->parent == 0 ? $this->name : $this->owner->items[$this->parent]['name'];
        }

        if ($s = static ::auth($this->group)) {
            return $s;
        }

        tlocal::usefile('admin');

        if ($s = $this->canrequest()) {
            return $s;
        }

        $this->doprocessform();
    }

    public function canrequest() {
    }

    protected function doprocessform() {
        if (isset($_POST) && count($_POST)) {
            litepubl::$urlmap->clearcache();
        }

        return parent::doprocessform();
    }

    public function getcont() {
        if (litepubl::$options->admincache) {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $filename = 'adminmenu.' . litepubl::$options->user . '.' . md5($_SERVER['REQUEST_URI'] . '&id=' . $id) . '.php';
            if ($result = litepubl::$urlmap->cache->get($filename)) {
                return $result;
            }

            $result = parent::getcont();
            litepubl::$urlmap->cache->set($filename, $result);
            return $result;
        } else {
            return parent::getcont();
        }
    }

    public function getadminurl() {
        return litepubl::$site->url . $this->url . litepubl::$site->q . 'id';
    }

    public function getlang() {
        return tlocal::i($this->name);
    }

}