<?php

use litepubl\updater\StorageIterator;
use litepubl\core\litepubl;

function updateEvents()
{
$map = include (__DIR__ . '/classmap.php');

$iterator = new StorageIterator::(
litepubl::$app->storage,
function(\StdClass $std) use ($map) {
if (isset($std->data['events']) && count($std->data['events'])) {
foreach ($std->data['events'] as $name => $events) {
foreach ($events as $i => $event) {
if (isset($event['class'])) {
}

$events[$i] = $event;
}

unset($std->data['events'][$name]);
$name = strtolower($name);
$std->data['events'][$name] = $events;
echo "$name\n";
}

return true;
}
});

$iterator->dir(litepubl::$app->paths->data);
}