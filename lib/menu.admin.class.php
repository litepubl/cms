<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class tadminmenu extends tmenu {
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
        return tadminmenus::i();
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
        return tadminmenus::i()->heads;
    }

    public function getidview() {
        return tviews::i()->defaults['admin'];
    }

    public function gettheme() {
        return $this->view->theme;
    }

    public function getadmintheme() {
        return $this->view->admintheme;
    }

    public static function auth($group) {
        if ($err = tguard::checkattack()) {
            return $err;
        }

        if (!litepubl::$options->user) {
            turlmap::nocache();
            return litepubl::$urlmap->redir('/admin/login/' . litepubl::$site->q . 'backurl=' . urlencode(litepubl::$urlmap->url));
        }

        if (!litepubl::$options->hasgroup($group)) {
            $url = tusergroups::i()->gethome(litepubl::$options->group);
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
        if (tguard::post()) {
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

    public static function idget() {
        return (int)tadminhtml::getparam('id', 0);
    }

    public function getaction() {
        return isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
    }

    public function gethtml($name = '') {
        return tadminhtml::i();
    }

    public function getlang() {
        return tlocal::i($this->name);
    }

    public function getadminlang() {
        return tlocal::inifile($this, '.admin.ini');
    }

    public function getconfirmed() {
        return isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1);
    }

    public function getnotfound() {
        return $this->admintheme->geterr(tlocal::i()->notfound);
    }

    public function getadminurl() {
        return litepubl::$site->url . $this->url . litepubl::$site->q . 'id';
    }

    public function getfrom($perpage, $count) {
        if (litepubl::$urlmap->page <= 1) return 0;
        return min($count, (litepubl::$urlmap->page - 1) * $perpage);
    }

} //class