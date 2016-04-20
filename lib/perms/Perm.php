<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\perms;

class Perm extends \litepubl\core\Item
{
use \litepubl\core\ItemOwnerTrait;

    protected $adminInstance;
    protected $adminclass;

    public static function i($id = 0) {
        $perms = Perms::i();
        $class = $perms->itemexists($id) ? $perms->items[$id]['class'] : get_called_class();
        return parent::iteminstance($class, $id);
    }

    public static function getInstancename() {
        return 'perm';
    }

    protected function create() {
        parent::create();
        $this->data = array(
            'id' => 0,
            'class' => get_class($this) ,
            'name' => 'permission'
        );
    }

    public function getOwner() {
        return Perms::i();
    }

    public function getAdmin() {
        if (!isset($this->adminInstance)) {
            $class = $this->adminclass;
            $this->adminInstance =  $this->getApp()->classes->newinstance($class);
            $this->adminInstance->perm = $this;
        }
        return $this->adminInstance;
    }

    public function getHeader($obj) {
        return '';
    }

    public function hasperm($obj) {
        return true;
    }

}