<?php

namespace litepubl\update;

class replacer
{
public static $map;

public static function get(string $class)
{
                if (isset(static::$map[$class])) {
return  static::$map[$class];
}

if ($j = strrpos($class, '\\')) {
$class=substr($class, $j + 1);
                if (isset(static::$map[$class])) {
return static::$map[$class];
}
}

return false;
}

public static function replace(\StdClass $std)
  {
    $result = false;

    if (isset($std->data['events']) && count($std->data['events'])) {
            $result = true;
        foreach ($std->data['events'] as $name => $events) {
            foreach ($events as $i => $event) {
                if (isset($event['class'])) {
                    $event[0] = $event['class'];
                    $event[1] = $event['func'];
                    unset($event['class'], $event['func']);
                }

                if ($class = static::get($event[0])) {
                    $event[0] = $class;
                }

                $events[$i] = $event;
            }

            unset($std->data['events'][$name]);
            $name = strtolower($name);
            $std->data['events'][$name] = $events;
        }
}

            if (isset($std->data['items']) && count($std->data['items'])) {
        foreach ($std->data['items'] as $id => $item) {
            if (isset($item['class']) && ($class = static::get($item['class']))) {
                $item['class'] = $class;
                $result = true;
                } elseif (isset($item['classname']) && ($class = static::get($item['classname']))) {
                    $item['classname'] = $class;
                    $result = true;
                }
            }
        }

        return $result;
    }

}