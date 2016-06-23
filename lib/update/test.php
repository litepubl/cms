<?php

namespace litepubl\update;

use litepubl\updater\ChangeStorage;

require (__DIR__ . '/updateEvents.php');
require (dirname(__DIR__) . '/updater/ChangeStorage.php');
require_once (__DIR__ . '/replacer.php');
replacer::$map = include(__DIR__ . '/classmap.php');
$changer = ChangeStorage::create([replacer::class, 'replace']);
$changer->run('data-6.14');