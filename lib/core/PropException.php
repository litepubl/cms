<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\core;

class PropException extends \UnexpectedValueException
{
    public $propName;
    public $className;

    public function __construct($className, $propName)
    {
        $this->className = $className;
        $this->propName = $propName;

        parent::__construct(sprintf('The requested property "%s" not found in class  %s', $propName, $className), 404);
    }
}
