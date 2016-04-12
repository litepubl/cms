<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\core;

class CancelEvent extends \Exception {
    public $result;

    public function __construct($message, $code = 0) {
        $this->result = $message;
        parent::__construct('', 0);
    }
}