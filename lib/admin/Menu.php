<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

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

    public static function getInstancename() {
        return 'adminmenu';
    }

    public static function getOwner() {
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

    public function getHead() {
        return Menus::i()->heads;
    }

    public function getIdSchema() {
        return Schemes::i()->defaults['admin'];
    }

    public static function auth($group) {
        if ($err = Guard::checkattack()) {
            return $err;
        }

        if (! $this->getApp()->options->user) {
            \litepubl\core\Router::nocache();
            return  $this->getApp()->router->redir('/admin/login/' .  $this->getApp()->site->q . 'backurl=' . urlencode( $this->getApp()->router->url));
        }

        if (! $this->getApp()->options->hasgroup($group)) {
            $url = UserGroups::i()->gethome( $this->getApp()->options->group);
            \litepubl\core\Router::nocache();
            return  $this->getApp()->router->redir($url);
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

        Lang::usefile('admin');

        if ($s = $this->canrequest()) {
            return $s;
        }

        $this->doProcessForm();
    }

    public function canrequest() {
    }

    protected function doProcessForm() {
        if (isset($_POST) && count($_POST)) {
             $this->getApp()->router->clearcache();
        }

        return parent::doProcessForm();
    }

    public function getCont() {
        if ( $this->getApp()->options->admincache) {
            $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
            $filename = 'adminmenu.' .  $this->getApp()->options->user . '.' . md5($_SERVER['REQUEST_URI'] . '&id=' . $id) . '.php';
            if ($result =  $this->getApp()->router->cache->get($filename)) {
                return $result;
            }

            $result = parent::getcont();
             $this->getApp()->router->cache->set($filename, $result);
            return $result;
        } else {
            return parent::getcont();
        }
    }

    public function getAdminurl() {
        return  $this->getApp()->site->url . $this->url .  $this->getApp()->site->q . 'id';
    }

    public function getLang() {
        return Lang::i($this->name);
    }

}