<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

interface itemplate {
    public function request($arg);
    public function gettitle();
    public function getkeywords();
    public function getdescription();
    public function gethead();
    public function getcont();
    public function getidview();
    public function setidview($id);
}