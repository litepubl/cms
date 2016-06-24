<?php

namespace litepubl\update;

use litepubl\updater\ChangeStorage;

require (__DIR__ . '/eventUpdater.php');
require (dirname(__DIR__) . '/updater/ChangeStorage.php');

eventUpdater::$map = include(__DIR__ . '/classmap.php');
$changer = ChangeStorage::create(eventUpdater::getCallback());
$changer->run('data-6.14');