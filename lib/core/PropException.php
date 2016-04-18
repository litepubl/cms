<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\core;

class PropNotfound extends \UnexpectedValueException
{
    public $propName;
public $className;

    public function __construct($className, $propName) {
$this->className = $className;
        $this->propName = $propName;

        parent::__construct(sprintf('The requested property "%s" not found in class  %s', $propName, $className), 404);
    }
}