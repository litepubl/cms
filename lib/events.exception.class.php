<?php
/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

class ECancelEvent extends Exception {
  public $result;
  
  public function __construct($message, $code = 0) {
    $this->result = $message;
    parent::__construct('', 0);
  }
}