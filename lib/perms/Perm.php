<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\perms;

use litepubl\core\Response;

class Perm extends \litepubl\core\Item
{
    use \litepubl\core\ItemOwnerTrait;

    protected $adminInstance;
    protected $adminclass;

    public static function i($id = 0)
    {
        $perms = Perms::i();
        $class = $perms->itemExists($id) ? $perms->items[$id]['class'] : get_called_class();
        return parent::iteminstance($class, $id);
    }

    public static function getInstancename()
    {
        return 'perm';
    }

    protected function create()
    {
        parent::create();
        $this->data = [
            'id' => 0,
            'class' => get_class($this) ,
            'name' => 'permission'
        ];
    }

    public function getOwner()
    {
        return Perms::i();
    }

    public function getAdmin()
    {
        if (!isset($this->adminInstance)) {
            $class = $this->adminclass;
            $this->adminInstance = $this->getApp()->classes->newinstance($class);
            $this->adminInstance->perm = $this;
        }
        return $this->adminInstance;
    }

    public function setResponse(Response $response, $obj)
    {
    }

    public function hasPerm($obj): bool
    {
        return true;
    }
}
