<?php
// Here you can initialize variables that will be available to your tests

use Codeception\Util\Autoload;
use test\config;

config::init();
Autoload::addNamespace('litepubl', config::$home . '/lib');
