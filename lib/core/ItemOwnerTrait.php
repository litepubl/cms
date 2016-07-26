<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\core;

trait ItemOwnerTrait
{

    public function load()
    {
        $owner = $this->owner;
        if ($owner->itemExists($this->id)) {
            $this->data = & $owner->items[$this->id];
            $this->afterLoad();
            return true;
        }
        return false;
    }

    public function save()
    {
        return $this->owner->save();
    }
}
