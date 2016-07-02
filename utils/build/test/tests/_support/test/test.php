<?php

namespace test;

require 'config.php';
require 'Utils.php';

config::init();
$s = Utils::getSingleFile(config::$home . '/storage/data/logs/');
$url = Utils::getLine($s, '&confirm=');
var_dump($url);
