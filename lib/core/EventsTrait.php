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
            $this->addEvent($eventName, $value);
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
        return in_array(strtolower($name), $this->eventnames);
    }

    public function method_exists($name)
    {
        return in_array(strtolower($name), $this->eventnames);
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

protected function newEvent(string $name): Event
{
return new Event($this, $name);
}

    public function callEvent(string $name, array $params): array
    {
$name = strtolower($name);
        if (!$this->getEventCount($name) && !$this->getCallbacksCount($name)) {
            return $params;
        }

$event = $this->newEvent($name);
$event->setParams($params);
$this->triggerCallback($event);
$this->trigger($event);
return $event->getParams();
}

protected function trigger(Event $event)
{
$app = $this->getApp();
$eventName = $event->getName();

        foreach ($this->events[$eventName] as $i => $item) {
if ($event->isPropagationStopped()) {
break;
}

if (class_exists($item[0])) {
                try {
                $callback = [$app->classes->getInstance($item[0]), $item[1]];
call_user_func_array($callback, [$event]);
if ($event->once) {
$event->once = false;
unset($this->events[$eventName][$i]);
$this->save();
}
        } catch (\Throwable $e) {
            $app->logException($e);
                }
} else {
unset($this->events[$eventName][$i]);
if (!count($this->events[$eventName])) {
unset($this->events[$eventName]);
}

$this->save();
$app->getLogger()->warning(sprintf('Event subscriber has been removed from %s:%s', get_class($this), $eventName), $item);
}
        }

    }

    public function addEvent(string $name, $callable, $method = null)
    {
$name = strtolower($name);
        if (!in_array($name, $this->eventnames)) {
            $this->error(sprintf('No such %s event', $name));
        }

if (!is_callable($callable)) {
if (!$method) {
            $this->error(sprintf('Error bind to event %s', $name));
}

$callable = [$callable, $method];
if (!is_callable($callable)) {
            $this->error(sprintf('Error bind to event %s', $name));
}
}

if (!is_array($callable) || !is_string($callable[0])) {
return $this->addCallback($name, $callback);
}

        if (!isset($this->data['events'][$name])) {
            $this->data['events'][$name] = [$callable];
$this->save();
        } else {
        //check if event already added
        foreach ($this->data['events'][$name] as $item) {
            if ($callback == $item) {
                return false;
            }
        }

            $this->data['events'][$name][] = callback;
            $this->save();
        }
    }

    public function delete_event_class($name, $class)
    {
        if (!isset($this->events[$name])) {
            return false;
        }

        $list = & $this->events[$name];
        $deleted = false;
        for ($i = count($list) - 1; $i >= 0; $i--) {
            $event = $list[$i];

            if ((isset($event[0]) && $event[0] == $class) 
                //backward compability
                || (isset($event['class']) && $event['class'] == $class)
            ) {
                array_splice($list, $i, 1);
                $deleted = true;
            }
        }

        if ($deleted) {
            if (count($list) == 0) {
                unset($this->events[$name]);
            }

            $this->save();
        }

        return $deleted;
    }

    public function unsubscribeclass($obj)
    {
        $this->unbind($obj);
    }

    public function unsubscribeclassname($class)
    {
        $this->unbind($class);
    }

    public function unbind($c)
    {
        $class = static ::get_class_name($c);
        foreach ($this->events as $name => $events) {
            foreach ($events as $i => $item) {
                if ((isset($item[0]) && $item[0] == $class) || (isset($item['class']) && $item['class'] == $class)) {
                    array_splice($this->events[$name], $i, 1);
                }
            }
        }

        $this->save();
    }

    public function setEventorder($eventname, $c, $order)
    {
        if (!isset($this->events[$eventname])) {
            return false;
        }

        $events = & $this->events[$eventname];
        $class = static ::get_class_name($c);
        $count = count($events);
        if (($order < 0) || ($order >= $count)) {
            $order = $count - 1;
        }

        foreach ($events as $i => $event) {
            if ((isset($event[0]) && $class == $event[0]) || (isset($event['class']) && $class == $event['class'])) {
                if ($i == $order) {
                    return true;
                }

                array_splice($events, $i, 1);
                array_splice(
                    $events, $order, 0, array(
                    0 => $event
                    )
                );

                $this->save();
                return true;
            }
        }
    }

    private function indexofcoclass($class)
    {
        return array_search($class, $this->coclasses);
    }

    public function addcoclass($class)
    {
        if ($this->indexofcoclass($class) === false) {
            $this->coclasses[] = $class;
            $this->save();
            $this->coinstances = static ::iGet($class);
        }
    }

    public function deletecoclass($class)
    {
        $i = $this->indexofcoclass($class);
        if (is_int($i)) {
            array_splice($this->coclasses, $i, 1);
            $this->save();
        }
    }
}
