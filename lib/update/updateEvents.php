<?php

namespace litepubl\update;

use litepubl\updater\StorageIterator;
use litepubl\core\litepubl;

function updateEvents()
{
$map = include(__DIR__ . '/classmap.php');

$iterator = new StorageIterator(
litepubl::$app->storage,
function (\StdClass $std) use ($map) {
    $result = false;

    if (isset($std->data['events']) && count($std->data['events'])) {
        foreach ($std->data['events'] as $name => $events) {
            foreach ($events as $i => $event) {
                if (isset($event['class'])) {
                    $event[0] = $event['class'];
                    $event[1] = $event['func'];
                    unset($event['class'], $event['func']);
                }

                $class = $event[0];
                if (isset($map[$class])) {
                    $event[0] = $map[$class];
                }

                $events[$i] = $event;
            }

            unset($std->data['events'][$name]);
            $name = strtolower($name);
            $std->data['events'][$name] = $events;
            //echo "$name\n";
            $result = true;
        }

            if (isset($std->data['items']) && count($std->data['items'])) {
        foreach ($std->data['items'] as $id => $item) {
            if (isset($item['class']) && isset($map[$item['class']])) {
                $item['class'] = $map[$item['class']];
                $result = true;
                if (isset($item['classname']) && isset($map[$item['classname']])) {
                    $item['classname'] = $map[$item['classname']];
                    $result = true;
                }
            }
        }
            }

        return $result;
    });

    $iterator->dir(litepubl::$app->paths->data);
}
