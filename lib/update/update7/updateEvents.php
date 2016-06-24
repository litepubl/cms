<?php

namespace litepubl\update;

use litepubl\updater\StorageIterator;
use litepubl\core\litepubl;

function updateEvents()
{
require_once (__DIR__ . '/replacer.php');
replacer::$map = include(__DIR__ . '/classmap.php');

$iterator = new StorageIterator(
litepubl::$app->storage,
[replacer::class, 'replace']
);

    $iterator->dir(litepubl::$app->paths->data);
}
