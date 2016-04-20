<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

//used in wikiwords.class.php. todo: remove this trait
namespace litepubl\core;
trait OwnerTrait
{
    private $owner;

    public function __construct($owner = null) {
        if (is_object($owner)) {
        parent::__construct();
        $this->owner = $owner;
        $this->table = $owner->table . 'items';
}
    }

    public function load() {
    }

    public function save() {
        $this->owner->save();
    }

    public function lock() {
        $this->owner->lock();
    }

    public function unlock() {
        $this->owner->unlock();
    }

}