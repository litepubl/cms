<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\view;

interface ViewInterface
 {
    public function request($arg);
    public function gettitle();
    public function getkeywords();
    public function getdescription();
    public function gethead();
    public function getcont();
    public function getIdSchema();
    public function setIdSchema($id);
}