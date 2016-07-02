<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\core;

class TempProps extends \ArrayObject
{
    private $owner;

    public function __construct(Data $owner)
    {
        parent::__construct([], \ArrayObject::ARRAY_AS_PROPS);
        $this->owner = $owner;
        $owner->coinstances[] = $this;
    }

    public function __destruct()
    {
        foreach ($this->owner->coinstances as $i => $obj) {
            if ($this == $obj) {
                unset($this->owner->coinstances[$i]);
            }
        }

        $this->owner = null;
    }
}