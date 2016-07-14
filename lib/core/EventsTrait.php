<?php

namespace litepubl\core;

trait EventsTrait
{
use Callbacks;

    public function __get($name)
    {
        if (method_exists($this, $name)) {
            return [get_class($this) ,                 $name];
        }

        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if (parent::__set($name, $value)) {
            return true;
        }

        $eventName = strtolower($name);
        if (in_array($eventName, $this->eventnames)) {
            $this->addEvent($eventName, $value[0], $value[1]);
            return true;
        }

            throw new PropException(get_class($this), $name);
    }

    public function __call($name, $params)
    {
        $eventName = strtolower($name);
        if (in_array($eventName, $this->eventnames)) {
            return $this->callEvent($eventName, $params[0]);
        }

        return parent::__call($name, $params);
    }

    public function __isset($name)
    {
        return parent::__isset($name) || in_array($name, $this->eventnames);
    }

    public function eventExists(string $name): bool
    {
        return in_array($name, $this->eventnames);
    }

    public function method_exists($name)
    {
        return in_array($name, $this->eventnames);
    }

protected function reIndexEvents()
{
foreach ($this->data['events'] as $name => $events) {
ksort($events);
$sorted = [];
$newIndex = 0;
foreach ($events as $i => $item) {
if (floor($i / 10) == floor($newIndex / 10)) {
$newIndex++;
} else {
$newIndex  = floor($i / 10) * 10;
}

$sorted[$newIndex] = $item;
}

$this->data['events'][$name] = $sorted;
}
}

public function getEventCount(string $name): int
{
return isset($this->data['events'][$name]) ? count($this->data['events'][$name]) : 0;
}

}
