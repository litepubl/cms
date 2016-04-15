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
use litepubl\view\Args;
use litepubl\view\Schemes;

class Menu extends \litepubl\pages\Menu
 {
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

    public function idget() {
        return (int)$this->getparam('id', 0);
    }

    public function getparam($name, $default) {
        return !empty($_GET[$name]) ? $_GET[$name] : (!empty($_POST[$name]) ? $_POST[$name] : $default);
    }

    public function idparam() {
        return (int)$this->getparam('id', 0);
    }

    public function getaction() {
        return isset($_REQUEST['action']) ? $_REQUEST['action'] : false;
    }

    public function gethtml($name = '') {
        return Html::i();
    }

    public function getlang() {
        return tlocal::i($this->name);
    }

    public function getadminlang() {
        return tlocal::inifile($this, '.admin.ini');
    }

//factories
public function newTable() {
return new Table($this->admintheme);
}

public function newList() {
return new UList($this->admintheme);
}

public function newTabs() {
return new Tabs($this->admintheme);
}

public function newForm() {
return new Form(new Args());
}


public function newArgs() {
return new Args();
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

    public function confirmDelete($id, $mesg = false) {
        $args = new Args();
        $args->id = $id;
        $args->action = 'delete';
        $args->adminurl = $this->adminurl;
        $args->confirm = $mesg ? $mesg : Lang::i()->confirmdelete;

        $admin = $this->admintheme;
        return $admin->parsearg($admin->templates['confirmform'], $args);
}

    public function confirmDeleteItem($owner) {
        $id = (int)$this->getparam('id', 0);
$admin = $this->admintheme;
$lang = Lang::i();

        if (!$owner->itemexists($id)) {
return $admin->geterr($lang->notfound);
}

        if (isset($_REQUEST['confirm']) && ($_REQUEST['confirm'] == 1)) {
            $owner->delete($id);
            return $admin->success($lang->successdeleted);
        } else {

            $args = new Args();
            $args->id = $id;
            $args->adminurl = $this->adminurl;
            $args->action = 'delete';
            $args->confirm = $lang->confirmdelete;
            return $admin->parsearg($admin->templates['confirmform'], $args);
    }

}